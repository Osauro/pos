<?php

namespace App\Http\Controllers;

use App\Helpers\TenantHelper;
use App\Models\Movimiento;
use App\Models\Turno;
use App\Models\Venta;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

class ReporteTurnoController extends Controller
{
    public function pdf(Turno $turno)
    {
        // Seguridad: el turno debe pertenecer al tenant activo
        abort_unless($turno->tenant_id === TenantHelper::currentId(), 403);

        $turno->load('encargado');

        // Ventas del turno con sus items
        $ventas = Venta::with(['items.producto', 'usuario'])
            ->where('turno_id', $turno->id)
            ->where('estado', '!=', 'cancelada')
            ->orderBy('fecha_hora')
            ->get();

        // Movimientos del turno
        $movimientos = Movimiento::with('usuario')
            ->where('turno_id', $turno->id)
            ->orderBy('created_at')
            ->get();

        // Resumen de ventas
        $totalVentas    = $ventas->sum('total');
        $totalEfectivo  = $ventas->sum('efectivo');
        $totalOnline    = $ventas->sum('online');
        $totalCredito   = $ventas->sum('credito');
        $cantidadVentas = $ventas->count();

        // Resumen de movimientos
        $totalIngresos = $movimientos->sum('ingreso');
        $totalEgresos  = $movimientos->sum('egreso');
        $saldoFinal    = $movimientos->last()?->saldo ?? 0;

        // Productos más vendidos en el turno
        $topProductos = DB::table('venta_items')
            ->join('ventas', 'ventas.id', '=', 'venta_items.venta_id')
            ->join('productos', 'productos.id', '=', 'venta_items.producto_id')
            ->where('ventas.turno_id', $turno->id)
            ->where('ventas.estado', '!=', 'cancelada')
            ->selectRaw('productos.nombre, SUM(venta_items.cantidad) as total_uds, SUM(venta_items.subtotal) as total_importe')
            ->groupBy('productos.id', 'productos.nombre')
            ->orderByDesc('total_uds')
            ->limit(10)
            ->get();

        $tenant  = TenantHelper::current();
        $negocio = $tenant?->nombre ?? 'Mi Negocio';

        $pdf = Pdf::loadView('reportes.turno-pdf', compact(
            'turno',
            'ventas',
            'movimientos',
            'totalVentas',
            'totalEfectivo',
            'totalOnline',
            'totalCredito',
            'cantidadVentas',
            'totalIngresos',
            'totalEgresos',
            'saldoFinal',
            'topProductos',
            'negocio',
            'tenant',
        ))->setPaper('a4', 'portrait')
          ->setOption(['dpi' => 96, 'isHtml5ParserEnabled' => true, 'isRemoteEnabled' => false]);

        $nombre = 'reporte-turno-' . $turno->fecha_inicio->format('Y-m-d') . '.pdf';

        return $pdf->stream($nombre);
    }
}
