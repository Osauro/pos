<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\BufferPrintConnector;

class EscposController extends Controller
{
    // ──────────────────────────────────────────────────────────────────
    // GET /escpos/ticket/{venta}
    // Devuelve bytes ESC/POS del ticket del cliente
    // ──────────────────────────────────────────────────────────────────
    public function ticket(Venta $venta): Response
    {
        $venta->load(['items.producto', 'turno.encargado', 'usuario']);

        $items   = $venta->items->filter(fn($i) => $i->producto)->values();
        $width   = config('printer.width', 80);
        $negocio = config('printer.negocio', 'Mi Negocio');
        $cols    = $width === 58 ? 32 : 42;

        $connector = new BufferPrintConnector();
        $printer   = new Printer($connector);

        // ── Cabecera ──────────────────────────────────────────────────
        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->setEmphasis(true);
        $printer->setTextSize(2, 2);
        $printer->text(mb_strtoupper($negocio) . "\n");
        $printer->setTextSize(1, 1);
        $printer->setEmphasis(false);
        $printer->text($this->separador($cols, '=') . "\n");

        $printer->setEmphasis(true);
        $printer->text("VENTA: {$venta->numero_venta}\n");
        $printer->setEmphasis(false);

        // ── Info ──────────────────────────────────────────────────────
        $printer->setJustification(Printer::JUSTIFY_LEFT);
        $fecha = $venta->fecha_hora?->format('d/m/Y') ?? now()->format('d/m/Y');
        $hora  = $venta->fecha_hora?->format('H:i')   ?? now()->format('H:i');
        $printer->text($this->columnas("Fecha:", $fecha, $cols) . "\n");
        $printer->text($this->columnas("Hora:",  $hora,  $cols) . "\n");
        if ($venta->usuario) {
            $printer->text($this->columnas("Cajero:", $venta->usuario->nombre, $cols) . "\n");
        }

        // ── Detalle ───────────────────────────────────────────────────
        $printer->text($this->separador($cols) . "\n");
        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->setEmphasis(true);
        $printer->text("- D E T A L L E -\n");
        $printer->setEmphasis(false);
        $printer->text($this->separador($cols) . "\n");
        $printer->setJustification(Printer::JUSTIFY_LEFT);

        foreach ($items as $item) {
            $nombreLinea = "{$item->cantidad} {$item->producto->nombre}";
            $precio      = number_format((float) $item->subtotal, 2);
            $printer->text($this->columnas($nombreLinea, $precio, $cols) . "\n");
        }

        // ── Total ─────────────────────────────────────────────────────
        $printer->text($this->separador($cols, '=') . "\n");
        $printer->setEmphasis(true);
        $printer->setTextSize(1, 2);
        $total = "Bs. " . number_format((float) $venta->total, 2);
        $printer->text($this->columnas("TOTAL:", $total, $cols) . "\n");
        $printer->setTextSize(1, 1);
        $printer->setEmphasis(false);
        $printer->text($this->separador($cols, '=') . "\n");

        // ── Pie ───────────────────────────────────────────────────────
        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->setEmphasis(true);
        $printer->text("GRACIAS POR SU COMPRA\n");
        $printer->setEmphasis(false);

        $encargado = $venta->turno?->encargado;
        if ($encargado) {
            $printer->text($this->separador($cols) . "\n");
            $printer->text("Encargado: {$encargado->nombre}\n");
            if ($encargado->celular) {
                $printer->text("{$encargado->celular}\n");
            }
        }

        $printer->feed(3);
        $printer->cut();
        $printer->close();

        return response($connector->getData(), 200, [
            'Content-Type'        => 'application/octet-stream',
            'Content-Disposition' => "attachment; filename=\"ticket-{$venta->numero_venta}.bin\"",
        ]);
    }

    // ──────────────────────────────────────────────────────────────────
    // GET /escpos/comanda/{venta}
    // Devuelve bytes ESC/POS de la comanda de cocina
    // ──────────────────────────────────────────────────────────────────
    public function comanda(Venta $venta): Response
    {
        $venta->load(['items.producto', 'turno.encargado']);

        $items = $venta->items->filter(
            fn($i) => $i->producto && $i->producto->tipo !== 'Refrescos'
        )->values();

        $width = config('printer.width', 80);
        $cols  = $width === 58 ? 32 : 42;

        $connector = new BufferPrintConnector();
        $printer   = new Printer($connector);

        // ── Cabecera comanda ──────────────────────────────────────────
        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->setEmphasis(true);
        $printer->setTextSize(2, 2);
        $printer->text("COMANDA\n");
        $printer->setTextSize(1, 2);
        $printer->text("VENTA #{$venta->numero_venta}\n");
        $printer->setTextSize(1, 1);
        $printer->setEmphasis(false);
        $printer->text($this->separador($cols, '=') . "\n");

        // ── Ítems ─────────────────────────────────────────────────────
        $printer->setJustification(Printer::JUSTIFY_LEFT);
        $printer->setTextSize(1, 2);  // Texto grande para cocina

        foreach ($items as $item) {
            $nombreCorto = $this->nombreCortoComanda($item);
            $detalle     = $this->detalleAcompanamiento($item);

            if ($detalle) {
                $printer->text($this->columnas(
                    "{$item->cantidad} {$nombreCorto}",
                    $detalle,
                    $cols
                ) . "\n");
            } else {
                $printer->text("{$item->cantidad} {$nombreCorto}\n");
            }
        }

        $printer->setTextSize(1, 1);
        $printer->text($this->separador($cols, '=') . "\n");

        $printer->feed(3);
        $printer->cut();
        $printer->close();

        return response($connector->getData(), 200, [
            'Content-Type'        => 'application/octet-stream',
            'Content-Disposition' => "attachment; filename=\"comanda-{$venta->numero_venta}.bin\"",
        ]);
    }

    // ──────────────────────────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────────────────────────

    /** Línea con texto izquierda y derecha alineados en $cols caracteres */
    private function columnas(string $izq, string $der, int $cols): string
    {
        $espacios = $cols - mb_strlen($izq) - mb_strlen($der);
        if ($espacios < 1) {
            // Si no cabe en una línea, poner el derecho en línea aparte
            return $izq . "\n" . str_repeat(' ', $cols - mb_strlen($der)) . $der;
        }
        return $izq . str_repeat(' ', $espacios) . $der;
    }

    /** Separador de $cols caracteres con el carácter dado */
    private function separador(int $cols, string $char = '-'): string
    {
        return str_repeat($char, $cols);
    }

    /** Nombre corto para comanda (lógica equivalente a la vista Blade) */
    private function nombreCortoComanda($item): string
    {
        $nombre = $item->producto->nombre;
        $pos    = strpos($nombre, ' ');
        $cad1   = $pos !== false ? substr($nombre, 0, $pos) : $nombre;
        $cad2   = $pos !== false ? trim(substr($nombre, $pos + 1)) : '';

        if ($item->cantidad > 1) {
            $ult   = mb_strtolower(mb_substr($cad1, -1));
            $cad1 .= in_array($ult, ['a', 'e', 'i', 'o', 'u']) ? 's' : 'es';
        }

        $sufijo = strcasecmp($cad2, 'sin huevo') === 0 ? ' S/H' : '';

        return strtoupper($cad1 . $sufijo);
    }

    /** Texto de acompañamiento para platos (A:2 F:1 M:0…) */
    private function detalleAcompanamiento($item): string
    {
        if ($item->producto->tipo !== 'Platos' || empty($item->detalle)) {
            return '';
        }

        $arr  = $item->detalle['arroz']  ?? 0;
        $fid  = $item->detalle['fideo']  ?? 0;
        $mix  = $item->detalle['mixto']  ?? 0;
        $piezas = [];

        if ($arr > 0) $piezas[] = "A:{$arr}";
        if ($fid > 0) $piezas[] = "F:{$fid}";
        if ($mix > 0) $piezas[] = "M:{$mix}";

        return implode(' ', $piezas);
    }
}
