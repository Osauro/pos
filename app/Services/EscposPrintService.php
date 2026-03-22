<?php

namespace App\Services;

use App\Models\Venta;
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
            if (!$this->hayPlatos($venta)) return null;
            $bytes = $this->buildTicketBytes($venta);
            return 'print://' . $this->encodePayload($bytes);
        } catch (\Throwable $e) {
            \Log::error('EscposPrintService::ticketUrl ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
            return null;
        }
    }

    /**
     * URL combinada ticket + comanda en un único print://.
     * Protocolo: chr(2) + pack('N', len_ticket) + ticket_bytes + comanda_bytes
     * Si no hay comanda, devuelve solo el ticketUrl.
     */
    public function combinedUrl(Venta $venta): ?string
    {
        try {
            $items = $venta->items->filter(
                fn($i) => $i->producto && $i->producto->tipo === 'Platos'
            );
            if ($items->isEmpty()) return null;

            $porciones    = $venta->items->filter(
                fn($i) => $i->producto && $i->producto->tipo === 'Porciones'
            );
            $ticketBytes  = $this->buildTicketBytes($venta);
            $comandaBytes = $this->buildComandaBytes($venta, $items, $porciones);
            $ticketLen    = strlen($ticketBytes);
            $combined     = chr(2) . pack('N', $ticketLen) . $ticketBytes . $comandaBytes;
            return 'print://' . $this->encodePayload($combined);
        } catch (\Throwable $e) {
            \Log::error('EscposPrintService::combinedUrl ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
            return null;
        }
    }

    public function comandaUrl(Venta $venta): ?string
    {
        try {
            $items = $venta->items->filter(
                fn($i) => $i->producto && $i->producto->tipo === 'Platos'
            );
            if ($items->isEmpty()) return null;

            $porciones = $venta->items->filter(
                fn($i) => $i->producto && $i->producto->tipo === 'Porciones'
            );
            $bytes = $this->buildComandaBytes($venta, $items, $porciones);
            return 'print://' . $this->encodePayload($bytes);
        } catch (\Throwable $e) {
            \Log::error('EscposPrintService::comandaUrl ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
            return null;
        }
    }

    // ──────────────────────────────────────────────────────────────────
    // Helpers de decisión de impresión
    // ──────────────────────────────────────────────────────────────────

    private function hayPlatos(Venta $venta): bool
    {
        return $venta->items->contains(
            fn($i) => $i->producto && $i->producto->tipo === 'Platos'
        );
    }

    /**
     * Pluraliza un nombre de producto en español según la cantidad.
     *
     * Reglas:
     *  1. Si hay preposición (de, sin, con…) → pluralizar solo lo anterior.
     *  2. Si hay token con dígito (190ml, 500cc…) → pluralizar solo la
     *     última palabra alfabética antes del dígito.
     *  3. Sin preposición ni dígito → pluralizar todas las palabras.
     */
    private function pluralizarNombre(string $nombre, int $cantidad): string
    {
        if ($cantidad <= 1) return $nombre;

        $preposiciones = ['de', 'del', 'sin', 'con', 'a', 'al', 'en', 'para', 'por', 'y'];
        $tokens = explode(' ', $nombre);

        $stopIdx = null;
        $digitIdx = null;
        foreach ($tokens as $i => $token) {
            if ($stopIdx === null && in_array(mb_strtolower($token), $preposiciones)) {
                $stopIdx = $i;
                break;
            }
            if ($digitIdx === null && preg_match('/\d/', $token)) {
                $digitIdx = $i;
                break;
            }
        }

        if ($stopIdx !== null) {
            // Pluralizar solo las palabras antes de la preposición
            for ($i = 0; $i < $stopIdx; $i++) {
                $tokens[$i] = $this->pluralES($tokens[$i]);
            }
        } elseif ($digitIdx !== null && $digitIdx > 0) {
            // Pluralizar solo la última palabra alfabética antes del token con dígito
            $tokens[$digitIdx - 1] = $this->pluralES($tokens[$digitIdx - 1]);
        } else {
            // Sin stoppers: pluralizar todas las palabras
            foreach ($tokens as $i => $token) {
                $tokens[$i] = $this->pluralES($token);
            }
        }

        return implode(' ', $tokens);
    }

    /** Aplica las reglas básicas del plural en español. */
    private function pluralES(string $word): string
    {
        if (empty($word)) return $word;
        $ult = mb_strtolower(mb_substr($word, -1));
        if ($ult === 'z') {
            return mb_substr($word, 0, -1) . 'ces';
        }
        if (in_array($ult, ['a', 'e', 'i', 'o', 'u'])) {
            return $word . 's';
        }
        return $word . 'es';
    }

    // ──────────────────────────────────────────────────────────────────
    // Generación de bytes ESC/POS con mike42
    // ──────────────────────────────────────────────────────────────────

    private function buildTicketBytes(Venta $venta): string
    {
        $items   = $venta->items->filter(fn($i) => $i->producto)->values();
        $width   = (int) config('printer.width', 80);
        $cols    = match ($width) {
            58 => 32,
            110 => 56,
            default => 48
        };

        $connector = new DummyPrintConnector();
        $printer   = new Printer($connector);

        // Nombre del negocio en texto (solo cuando no hay logo configurado)
        if (!config('printer.logo')) {
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->setEmphasis(true);
            $printer->setTextSize(2, 1);
            $printer->text(mb_strtoupper(config('printer.negocio', 'MI NEGOCIO')) . "\n");
            $printer->setTextSize(1, 1);
            $printer->setEmphasis(false);
        }

        // Número de venta en grande (salto antes y después)
        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->setEmphasis(true);
        $printer->setTextSize(2, 2);
        $printer->text("\nVENTA #{$venta->numero_venta}\n\n");
        $printer->setTextSize(1, 1);
        $printer->setEmphasis(false);

        // Fecha/Hora en la misma línea, Cajero debajo
        $printer->setJustification(Printer::JUSTIFY_LEFT);
        $fecha = $venta->fecha_hora?->format('d/m/Y') ?? now()->format('d/m/Y');
        $hora  = $venta->fecha_hora?->format('H:i')   ?? now()->format('H:i');
        $printer->text("Fecha: {$fecha}  {$hora}\n");
        if ($venta->usuario) {
            $printer->text("Cajero: {$venta->usuario->nombre}\n");
        }

        // Detalle
        $printer->text("\n");
        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->setEmphasis(true);
        $printer->text("----- D E T A L L E -----\n");
        $printer->setEmphasis(false);
        $printer->text("\n");
        $printer->setJustification(Printer::JUSTIFY_LEFT);

        foreach ($items as $item) {
            $nombre = $this->pluralizarNombre($item->producto->nombre, $item->cantidad);
            $izq = "{$item->cantidad} {$nombre}";
            $der = number_format((float) $item->subtotal, 2);
            $printer->text($this->columnasDots($izq, $der, $cols) . "\n");
        }

        // Total: negrita, tamaño normal, alineado a la derecha
        $printer->setJustification(Printer::JUSTIFY_RIGHT);
        $printer->setEmphasis(true);
        $printer->text("TOTAL: Bs. " . number_format((float) $venta->total, 2) . "\n");
        $printer->setEmphasis(false);

        // Pie: gracias + encargado del turno (todo centrado)
        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->setEmphasis(true);
        $printer->text("\nGRACIAS POR SU COMPRA\n");
        $printer->setEmphasis(false);
        if ($venta->turno && $venta->turno->encargado) {
            $encargado = $venta->turno->encargado;
            $printer->text("Encargado: {$encargado->nombre}\n");
            if (!empty($encargado->celular)) {
                $printer->text("Celular: {$encargado->celular}\n");
            }
        }

        $printer->feed(4);
        $printer->cut(Printer::CUT_PARTIAL);

        $bytes = $connector->getData();
        $printer->close();

        // chr(1) = Go agrega logo; chr(0) = sin logo (ya se imprimió el nombre en texto)
        return (config('printer.logo') ? chr(1) : chr(0)) . $bytes;
    }

    private function buildComandaBytes(Venta $venta, $items, $porciones = null): string
    {
        $width    = (int) config('printer.width', 80);
        $cols     = match ($width) {
            58 => 32,
            110 => 56,
            default => 48
        };
        $colsDobl = intdiv($cols, 2); // cols efectivas con setTextSize(2,2)

        $connector = new DummyPrintConnector();
        $printer   = new Printer($connector);

        // Cabecera: VENTA #{} centrado en doble tamaño
        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->setEmphasis(true);
        $printer->setTextSize(2, 2);
        $printer->text("VENTA #{$venta->numero_venta}\n\n");
        $printer->setTextSize(1, 1);
        $printer->setEmphasis(false);

        // Items: ancho normal, altura doble (1x2) — más caracteres por línea
        $printer->setJustification(Printer::JUSTIFY_LEFT);
        foreach ($items as $item) {
            $nombre  = $this->nombreCorto($item);
            $detalle = $this->buildDetalle($item);
            $izq     = "{$item->cantidad} {$nombre}";
            $printer->setTextSize(1, 2);
            if ($detalle) {
                $printer->text($this->columnasDotsTrunc($izq, $detalle, $cols) . "\n");
            } else {
                $printer->text(mb_substr($izq, 0, $cols) . "\n");
            }
            $printer->setTextSize(1, 1);
        }

        // Sección PORCIONES (si las hay)
        if ($porciones && $porciones->isNotEmpty()) {
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->setEmphasis(true);
            $printer->text("\n----- P O R C I O N E S -----\n");
            $printer->setEmphasis(false);
            $printer->setJustification(Printer::JUSTIFY_LEFT);
            foreach ($porciones as $item) {
                $nombre = $this->pluralizarNombre($item->producto->nombre, $item->cantidad);
                $izq    = "{$item->cantidad} {$nombre}";
                $printer->setTextSize(1, 2);
                $printer->text(mb_substr($izq, 0, $cols) . "\n");
                $printer->setTextSize(1, 1);
            }
        }

        $printer->feed(4);
        $printer->cut(Printer::CUT_PARTIAL);

        $bytes = $connector->getData();
        $printer->close();

        // Byte 0x00 = indicar a Go que NO agregue el logo local
        return chr(0) . $bytes;
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

    private function columnasDots(string $izq, string $der, int $cols): string
    {
        $puntos = $cols - mb_strlen($izq) - mb_strlen($der);
        return $izq . str_repeat('.', max(1, $puntos)) . $der;
    }

    // Igual que columnasDots pero trunca $izq si el texto es demasiado largo
    private function columnasDotsTrunc(string $izq, string $der, int $cols): string
    {
        $maxIzq = $cols - mb_strlen($der) - 1;
        if ($maxIzq > 0 && mb_strlen($izq) > $maxIzq) {
            $izq = mb_substr($izq, 0, $maxIzq);
        }
        $puntos = $cols - mb_strlen($izq) - mb_strlen($der);
        return $izq . str_repeat('.', max(1, $puntos)) . $der;
    }

    private function nombreCorto($item): string
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
        return $cad1 . $sufijo;
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

        if ($arr > 0) $partes[] = "{$arr}A";
        if ($fid > 0) $partes[] = "{$fid}F";
        if ($mix > 0) $partes[] = "{$mix}M";

        return implode(' - ', $partes);
    }
}
