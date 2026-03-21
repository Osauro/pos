<?php

namespace App\Services;

use App\Models\Venta;
use Mike42\Escpos\EscposImage;
use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\DummyPrintConnector;

/**
 * EscposPrintService
 *
 * Genera bytes ESC/POS con mike42 y los cifra para enviarlos
 * en la URL del protocolo print://{encryptedPayload}.
 *
 * Flujo:
 *   1. Generar bytes ESC/POS con mike42 (BufferPrintConnector)
 *   2. bytes → gzip → AES-256-GCM → base64url
 *   3. Devuelve "print://{payload}" listo para window.location.href
 *
 * El print-agent.exe en la PC cajera hace el proceso inverso:
 *   base64url → AES-256-GCM → gzip → bytes ESC/POS → WritePrinter()
 *
 * Al ser bytes crudos, el agente es 100% genérico y puede usarse
 * con cualquier proyecto que envíe ESC/POS cifrado.
 */
class EscposPrintService
{
    // ──────────────────────────────────────────────────────────────────
    // URLs públicas
    // ──────────────────────────────────────────────────────────────────

    public function ticketUrl(Venta $venta): ?string
    {
        try {
            $bytes = $this->buildTicketBytes($venta);
            return 'print://' . $this->encodePayload($bytes);
        } catch (\Throwable $e) {
            \Log::error('EscposPrintService::ticketUrl ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
            return null;
        }
    }

    public function comandaUrl(Venta $venta): ?string
    {
        try {
            $items = $venta->items->filter(
                fn($i) => $i->producto && $i->producto->tipo !== 'Refrescos'
            );
            if ($items->isEmpty()) return null;

            $bytes = $this->buildComandaBytes($venta, $items);
            return 'print://' . $this->encodePayload($bytes);
        } catch (\Throwable) {
            return null;
        }
    }

    // ──────────────────────────────────────────────────────────────────
    // Generación de bytes ESC/POS con mike42
    // ──────────────────────────────────────────────────────────────────

    private function buildTicketBytes(Venta $venta): string
    {
        $items   = $venta->items->filter(fn($i) => $i->producto)->values();
        $width   = (int) config('printer.width', 80);
        $negocio = config('printer.negocio', 'Mi Negocio');
        $cols    = match($width) { 58 => 32, 110 => 56, default => 42 };

        $connector = new DummyPrintConnector();
        $printer   = new Printer($connector);

        // Logo si existe en storage/app/logo.png
        $logoPath = storage_path('app/logo.png');
        if (file_exists($logoPath)) {
            try {
                $logo = EscposImage::load($logoPath, false);
                $printer->setJustification(Printer::JUSTIFY_CENTER);
                $printer->graphics($logo);
                $printer->feed(1);
            } catch (\Throwable) {
                // Sin logo — continuar
            }
        }

        // Cabecera
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

        // Info
        $printer->setJustification(Printer::JUSTIFY_LEFT);
        $fecha = $venta->fecha_hora?->format('d/m/Y') ?? now()->format('d/m/Y');
        $hora  = $venta->fecha_hora?->format('H:i')   ?? now()->format('H:i');
        $printer->text($this->columnas("Fecha:", $fecha, $cols) . "\n");
        $printer->text($this->columnas("Hora:",  $hora,  $cols) . "\n");
        if ($venta->usuario) {
            $printer->text($this->columnas("Cajero:", $venta->usuario->nombre, $cols) . "\n");
        }

        // Detalle
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

        // Total
        $printer->text($this->separador($cols, '=') . "\n");
        $printer->setEmphasis(true);
        $printer->setTextSize(1, 2);
        $total = "Bs. " . number_format((float) $venta->total, 2);
        $printer->text($this->columnas("TOTAL:", $total, $cols) . "\n");
        $printer->setTextSize(1, 1);
        $printer->setEmphasis(false);
        $printer->text($this->separador($cols, '=') . "\n");

        // Pie
        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->setEmphasis(true);
        $printer->text("GRACIAS POR SU COMPRA\n");
        $printer->setEmphasis(false);
        $printer->feed(4);
        $printer->cut(Printer::CUT_PARTIAL);

        $bytes = $connector->getData();
        $printer->close();

        return $bytes;
    }

    private function buildComandaBytes(Venta $venta, $items): string
    {
        $width = (int) config('printer.width', 80);
        $cols  = match($width) { 58 => 32, 110 => 56, default => 42 };

        $connector = new DummyPrintConnector();
        $printer   = new Printer($connector);

        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->setEmphasis(true);
        $printer->setTextSize(2, 2);
        $printer->text("** COMANDA **\n");
        $printer->setTextSize(1, 1);
        $printer->setEmphasis(false);
        $printer->text($this->separador($cols, '=') . "\n");

        $printer->setJustification(Printer::JUSTIFY_LEFT);
        $printer->setEmphasis(true);
        $printer->text("VENTA: {$venta->numero_venta}\n");
        $printer->setEmphasis(false);

        $fecha = $venta->fecha_hora?->format('d/m/Y H:i') ?? now()->format('d/m/Y H:i');
        $printer->text("Fecha: {$fecha}\n");
        $printer->text($this->separador($cols) . "\n");

        foreach ($items as $item) {
            $printer->setTextSize(1, 2);
            $nombre  = $this->nombreCorto($item);
            $detalle = $this->buildDetalle($item);
            $linea   = " {$item->cantidad} x {$nombre}";
            if ($detalle) $linea .= " ({$detalle})";
            $printer->text($linea . "\n");
            $printer->setTextSize(1, 1);
        }

        $printer->text($this->separador($cols) . "\n");
        $printer->feed(4);
        $printer->cut(Printer::CUT_PARTIAL);

        $bytes = $connector->getData();
        $printer->close();

        return $bytes;
    }

    // ──────────────────────────────────────────────────────────────────
    // Cifrado: bytes ESC/POS → gzip → AES-256-GCM → base64url
    // ──────────────────────────────────────────────────────────────────

    private function encodePayload(string $rawBytes): string
    {
        $compressed = gzencode($rawBytes, 9);
        return $this->encrypt($compressed);
    }

    private function encrypt(string $data): string
    {
        $keyHex = config('printer.secret_key', '');

        if (empty($keyHex)) {
            throw new \RuntimeException('PRINTER_SECRET_KEY no configurado en .env');
        }

        $key = hex2bin($keyHex);

        if (strlen($key) !== 32) {
            throw new \RuntimeException('PRINTER_SECRET_KEY debe tener 64 caracteres hex (32 bytes)');
        }

        $nonce      = random_bytes(12);
        $tag        = '';
        $ciphertext = openssl_encrypt($data, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $nonce, $tag, '', 16);

        // Formato: nonce(12) + ciphertext + tag(16)
        $raw = $nonce . $ciphertext . $tag;

        // base64url sin padding
        return rtrim(strtr(base64_encode($raw), '+/', '-_'), '=');
    }

    // ──────────────────────────────────────────────────────────────────
    // Helpers de formato
    // ──────────────────────────────────────────────────────────────────

    private function separador(int $cols, string $char = '-'): string
    {
        return str_repeat($char, $cols);
    }

    private function columnas(string $izq, string $der, int $cols): string
    {
        $espacios = $cols - mb_strlen($izq) - mb_strlen($der);
        return $izq . str_repeat(' ', max(1, $espacios)) . $der;
    }

    private function nombreCorto($item): string
    {
        $nombre = $item->producto->nombre;
        $pos    = strpos($nombre, ' ');
        $cad1   = $pos !== false ? substr($nombre, 0, $pos) : $nombre;
        $cad2   = $pos !== false ? trim(substr($nombre, $pos + 1)) : '';

        if ($item->cantidad > 1) {
            $ult   = mb_strtolower(mb_substr($cad1, -1));
            $cad1 .= in_array($ult, ['a','e','i','o','u']) ? 's' : 'es';
        }

        $sufijo = strcasecmp($cad2, 'sin huevo') === 0 ? ' S/H' : '';
        return strtoupper($cad1 . $sufijo);
    }

    private function buildDetalle($item): string
    {
        if ($item->producto->tipo !== 'Platos' || empty($item->detalle)) {
            return '';
        }

        $arr    = $item->detalle['arroz'] ?? 0;
        $fid    = $item->detalle['fideo'] ?? 0;
        $mix    = $item->detalle['mixto'] ?? 0;
        $partes = [];

        if ($arr > 0) $partes[] = "A:{$arr}";
        if ($fid > 0) $partes[] = "F:{$fid}";
        if ($mix > 0) $partes[] = "M:{$mix}";

        return implode(' ', $partes);
    }
}
