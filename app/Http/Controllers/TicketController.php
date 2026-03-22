<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    /**
     * Comanda de cocina: solo Platos + Porciones.
     */
    public function comanda(Venta $venta)
    {
        $venta->load(['items.producto', 'turno.encargado']);

        $items = $venta->items->filter(
            fn($item) => $item->producto && $item->producto->tipo === 'Platos'
        )->values();

        $porciones = $venta->items->filter(
            fn($item) => $item->producto && $item->producto->tipo === 'Porciones'
        )->values();

        $width = config('printer.width', 80);

        return view('tickets.comanda', compact('venta', 'items', 'porciones', 'width'));
    }

    /**
     * Ticket del cliente: todos los ítems con precios, total y datos del turno.
     */
    public function cliente(Request $request, Venta $venta)
    {
        $venta->load(['items.producto', 'turno.encargado', 'usuario']);

        $items = $venta->items->filter(fn($item) => $item->producto)->values();

        $width      = config('printer.width', 80);
        $negocio    = config('printer.negocio', 'Mi Negocio');
        $soloTicket = $request->boolean('nocomanda');

        $logoBase64 = null;
        $logoPath   = storage_path('app/logo.png');
        if (file_exists($logoPath)) {
            $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
        }

        return view('tickets.cliente', compact('venta', 'items', 'width', 'negocio', 'soloTicket', 'logoBase64'));
    }

    /**
     * Alias de cliente() para compatibilidad con licopos-printer.
     */
    public function venta(Venta $venta)
    {
        return $this->cliente($venta);
    }

    /**
     * Ticket del cliente en PDF (para móviles).
     */
    public function clientePdf(Request $request, Venta $venta)
    {
        $venta->load(['items.producto', 'turno.encargado', 'usuario']);

        $items   = $venta->items->filter(fn($item) => $item->producto)->values();
        $width   = config('printer.width', 80);
        $negocio = config('printer.negocio', 'Mi Negocio');

        // Ancho papel en puntos tipográficos (1pt = 1/72 in)
        $paperW = $width === 58 ? 164.41 : 226.77;  // 58 mm ó 80 mm
        $paperH = 841.89;                             // 297 mm suficiente para cualquier ticket

        $pdf = Pdf::loadView('tickets.cliente-pdf', compact('venta', 'items', 'width', 'negocio'))
            ->setPaper([0, 0, $paperW, $paperH], 'portrait')
            ->setOption(['dpi' => 96, 'isHtml5ParserEnabled' => true, 'isRemoteEnabled' => false]);

        return $pdf->stream("ticket-{$venta->numero_venta}.pdf");
    }

    /**
     * Comanda de cocina en PDF (para móviles).
     */
    public function comandaPdf(Venta $venta)
    {
        $venta->load(['items.producto', 'turno.encargado']);

        $items = $venta->items->filter(
            fn($item) => $item->producto && $item->producto->tipo === 'Platos'
        )->values();

        $porciones = $venta->items->filter(
            fn($item) => $item->producto && $item->producto->tipo === 'Porciones'
        )->values();

        $width  = config('printer.width', 80);
        $paperW = $width === 58 ? 164.41 : 226.77;
        $paperH = 841.89;

        $pdf = Pdf::loadView('tickets.comanda-pdf', compact('venta', 'items', 'porciones', 'width'))
            ->setPaper([0, 0, $paperW, $paperH], 'portrait')
            ->setOption(['dpi' => 96, 'isHtml5ParserEnabled' => true, 'isRemoteEnabled' => false]);

        return $pdf->stream("comanda-{$venta->numero_venta}.pdf");
    }
}
