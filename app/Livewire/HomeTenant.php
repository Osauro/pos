<?php

namespace App\Livewire;

use App\Helpers\TenantHelper;
use App\Models\Tenant;
use App\Models\Venta;
use App\Models\VentaItem;
use App\Traits\WithPermisos;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.theme.app')]
class HomeTenant extends Component
{
    use WithPermisos;

    // Tarjetas resumen
    public int   $ventasHoy  = 0;
    public float $ingresoHoy = 0;
    public int   $ventasMes  = 0;
    public float $ingresoMes = 0;

    // Filtros
    public string $semanaFecha     = '';
    public int    $anioMensual     = 0;
    public array  $anosDisponibles = [];

    // Datos para gráficos (pasados al blade como JSON)
    public array $ventasSemanales      = [];
    public array $productosVendidosHoy = [];
    public array $meses                = [];

    public function mount(): void
    {
        $this->verificarAccesoDashboard();

        $tenantId = currentTenantId();
        $tenant   = TenantHelper::current();

        // Día comercial de hoy y su rango datetime
        $diaHoy             = $this->businessDayHoy($tenant);
        [$inicioHoy, $finHoy] = $this->rangeForDate($tenant, $diaHoy);

        // Tarjetas resumen — usa fecha_hora con el rango del día comercial
        $this->ventasHoy  = Venta::where('tenant_id', $tenantId)
            ->whereBetween('fecha_hora', [$inicioHoy, $finHoy])
            ->count();
        $this->ingresoHoy = (float) Venta::where('tenant_id', $tenantId)
            ->whereBetween('fecha_hora', [$inicioHoy, $finHoy])
            ->sum('total');

        // Mes actual: desde el inicio del día comercial del día 1 del mes hasta ahora
        [$inicioMes] = $this->rangeForDate($tenant, Carbon::now()->startOfMonth());
        $this->ventasMes  = Venta::where('tenant_id', $tenantId)
            ->where('fecha_hora', '>=', $inicioMes)
            ->count();
        $this->ingresoMes = (float) Venta::where('tenant_id', $tenantId)
            ->where('fecha_hora', '>=', $inicioMes)
            ->sum('total');

        // Años disponibles para el filtro mensual
        $this->anosDisponibles = Venta::where('tenant_id', $tenantId)
            ->selectRaw('YEAR(fecha_hora) as anio')
            ->groupBy('anio')
            ->orderByDesc('anio')
            ->pluck('anio')
            ->toArray();

        if (empty($this->anosDisponibles)) {
            $this->anosDisponibles = [Carbon::now()->year];
        }

        $this->anioMensual = $this->anosDisponibles[0];
        $this->semanaFecha = Carbon::now()->format('Y-m-d');

        $this->ventasSemanales      = $this->calcularVentasSemanales();
        $this->productosVendidosHoy = $this->calcularProductosVendidosHoy();
        $this->meses                = $this->calcularMeses();
    }

    public function updatedSemanaFecha(): void
    {
        // El operador (esUser) no puede cambiar la semana — se bloquea a la semana actual
        if ($this->esUser()) {
            $this->semanaFecha = Carbon::now()->format('Y-m-d');
        }

        $this->ventasSemanales = $this->calcularVentasSemanales();
        $this->dispatch('actualizarGraficoSemanal', datos: $this->ventasSemanales);
    }

    public function updatedAnioMensual(): void
    {
        $this->meses = $this->calcularMeses();
        $this->dispatch('actualizarGraficoMensual', datos: $this->meses, anio: $this->anioMensual);
    }

    // ── Helpers de día comercial ─────────────────────────────────────────────

    private function businessDayHoy(?Tenant $tenant): Carbon
    {
        if (!$tenant) return Carbon::today();
        return $tenant->businessDayFor(Carbon::now());
    }

    /**
     * Devuelve [inicio, fin] Carbon del día comercial para la fecha dada.
     */
    private function rangeForDate(?Tenant $tenant, Carbon $date): array
    {
        if (!$tenant) {
            return [$date->copy()->startOfDay(), $date->copy()->endOfDay()];
        }
        return $tenant->businessDayRange($date);
    }

    // ── Gráficos ─────────────────────────────────────────────────────────────

    private function calcularVentasSemanales(): array
    {
        $tenantId  = currentTenantId();
        $tenant    = TenantHelper::current();
        $fechaBase = Carbon::parse($this->semanaFecha);
        $inicio    = $fechaBase->copy()->startOfWeek(Carbon::MONDAY);

        $dias   = [];
        $ventas = [];

        for ($i = 0; $i < 7; $i++) {
            $dia = $inicio->copy()->addDays($i);
            [$rangoInicio, $rangoFin] = $this->rangeForDate($tenant, $dia);

            $dias[]   = $dia->isoFormat('ddd D');
            $ventas[] = (float) Venta::where('tenant_id', $tenantId)
                ->whereBetween('fecha_hora', [$rangoInicio, $rangoFin])
                ->sum('total');
        }

        return [
            'dias'   => $dias,
            'ventas' => $ventas,
        ];
    }

    private function calcularProductosVendidosHoy(): array
    {
        $tenantId = currentTenantId();
        $tenant   = TenantHelper::current();
        $diaHoy   = $this->businessDayHoy($tenant);
        [$rangoInicio, $rangoFin] = $this->rangeForDate($tenant, $diaHoy);

        return VentaItem::join('ventas', 'venta_items.venta_id', '=', 'ventas.id')
            ->join('productos', 'venta_items.producto_id', '=', 'productos.id')
            ->where('ventas.tenant_id', $tenantId)
            ->whereBetween('ventas.fecha_hora', [$rangoInicio, $rangoFin])
            ->select(
                'productos.nombre as nombre',
                DB::raw('SUM(venta_items.cantidad) as cantidad'),
                DB::raw('SUM(venta_items.subtotal) as total')
            )
            ->groupBy('venta_items.producto_id', 'productos.nombre')
            ->orderByDesc('cantidad')
            ->get()
            ->map(fn($i) => [
                'nombre'   => $i->nombre,
                'cantidad' => (int) $i->cantidad,
                'total'    => (float) $i->total,
            ])
            ->toArray();
    }

    private function calcularMeses(): array
    {
        $tenantId = currentTenantId();
        $tenant   = TenantHelper::current();
        $nombres  = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];

        // Para cada mes: rango desde el inicio del día comercial del día 1
        // hasta el fin del día comercial del último día del mes
        $meses  = [];
        $ventas = [];

        for ($m = 1; $m <= 12; $m++) {
            $primerDia = Carbon::create($this->anioMensual, $m, 1);
            $ultimoDia = $primerDia->copy()->endOfMonth();

            [$inicioRango] = $this->rangeForDate($tenant, $primerDia);
            [, $finRango]  = $this->rangeForDate($tenant, $ultimoDia);

            $meses[]  = $nombres[$m - 1];
            $ventas[] = (float) Venta::where('tenant_id', $tenantId)
                ->whereBetween('fecha_hora', [$inicioRango, $finRango])
                ->sum('total');
        }

        return ['meses' => $meses, 'ventas' => $ventas];
    }

    public function render()
    {
        return view('livewire.home-tenant');
    }
}

