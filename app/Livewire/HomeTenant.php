<?php

namespace App\Livewire;

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

        $tenantId  = currentTenantId();
        $hoy       = Carbon::today();
        $inicioMes = Carbon::now()->startOfMonth();

        // Tarjetas resumen
        $this->ventasHoy  = Venta::where('tenant_id', $tenantId)->whereDate('created_at', $hoy)->count();
        $this->ingresoHoy = (float) Venta::where('tenant_id', $tenantId)->whereDate('created_at', $hoy)->sum('total');
        $this->ventasMes  = Venta::where('tenant_id', $tenantId)->where('created_at', '>=', $inicioMes)->count();
        $this->ingresoMes = (float) Venta::where('tenant_id', $tenantId)->where('created_at', '>=', $inicioMes)->sum('total');

        // Años disponibles para el filtro mensual
        $this->anosDisponibles = Venta::where('tenant_id', $tenantId)
            ->selectRaw('YEAR(created_at) as anio')
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

    private function calcularVentasSemanales(): array
    {
        $tenantId  = currentTenantId();
        $fechaBase = Carbon::parse($this->semanaFecha);
        $inicio    = $fechaBase->copy()->startOfWeek(Carbon::MONDAY);

        $dias   = [];
        $ventas = [];

        for ($i = 0; $i < 7; $i++) {
            $dia      = $inicio->copy()->addDays($i);
            $dias[]   = $dia->isoFormat('ddd D');
            $ventas[] = (float) Venta::where('tenant_id', $tenantId)
                ->whereDate('created_at', $dia->toDateString())
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

        return VentaItem::join('ventas', 'venta_items.venta_id', '=', 'ventas.id')
            ->join('productos', 'venta_items.producto_id', '=', 'productos.id')
            ->where('ventas.tenant_id', $tenantId)
            ->whereDate('ventas.created_at', Carbon::today())
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
        $nombres  = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];

        $datos = Venta::where('tenant_id', $tenantId)
            ->whereYear('created_at', $this->anioMensual)
            ->selectRaw('MONTH(created_at) as mes, COUNT(*) as total, SUM(total) as ingreso')
            ->groupBy('mes')
            ->get()
            ->keyBy('mes');

        $meses  = [];
        $ventas = [];

        for ($m = 1; $m <= 12; $m++) {
            $meses[]  = $nombres[$m - 1];
            $ventas[] = (float) ($datos[$m]->ingreso ?? 0);
        }

        return ['meses' => $meses, 'ventas' => $ventas];
    }

    public function render()
    {
        return view('livewire.home-tenant');
    }
}
