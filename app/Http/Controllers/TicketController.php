<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    /**
     * Comanda de cocina: todos los ítems excepto Refrescos.
     */
    public function comanda(Venta $venta)
    {
        $venta->load(['items.producto', 'turno.encargado']);

        $items = $venta->items->filter(
            fn($item) => $item->producto && $item->producto->tipo !== 'Refrescos'
        )->values();

        $width = config('printer.width', 80);

        return view('tickets.comanda', compact('venta', 'items', 'width'));
    }

    /**
     * Ticket del cliente: todos los ítems con precios, total y datos del turno.
     */
    public function cliente(Venta $venta)
    {
        $venta->load(['items.producto', 'turno.encargado', 'usuario']);

        $items = $venta->items->filter(fn($item) => $item->producto)->values();

        $width = config('printer.width', 80);
        $negocio = config('printer.negocio', 'Mi Negocio');

        return view('tickets.cliente', compact('venta', 'items', 'width', 'negocio'));
    }

    /**
     * Alias de cliente() para compatibilidad con licopos-printer.
     */
    public function venta(Venta $venta)
    {
        return $this->cliente($venta);
    }
}
