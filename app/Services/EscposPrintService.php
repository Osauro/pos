<?php

namespace App\Services;

use App\Models\Venta;
use Illuminate\Support\Facades\Log;

/**
 * EscposPrintService - Servicio de impresión con agente print://
 *
 * Modo único: Agente (print://{payload_cifrado})
 * - Genera JSON → gzip → AES-256-GCM → base64url → print://
 * - El print-agent.exe descifra y procesa
 * - Fallback HTML para navegadores sin soporte del protocolo
 */
class EscposPrintService
{
    // ============================================================================
    // URLs del protocolo print://
    // ============================================================================

    /**
     * Genera URL print:// para ticket de venta (respeta printer_auto_ticket del tenant)
     */
    public function ticketUrl(Venta $venta): ?string
    {
        try {
            $tenant = \App\Helpers\TenantHelper::current();
            if (!$tenant || !$tenant->printer_auto_ticket) return null;
            return $this->buildTicketUrl($venta, $tenant);
        } catch (\Throwable $e) {
            Log::error('EscposPrintService::ticketUrl ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Genera URL print:// para ticket sin importar printer_auto_ticket (reimpresión manual)
     */
    public function ticketUrlForced(Venta $venta): ?string
    {
        try {
            $tenant = \App\Helpers\TenantHelper::current();
            if (!$tenant) return null;
            return $this->buildTicketUrl($venta, $tenant);
        } catch (\Throwable $e) {
            Log::error('EscposPrintService::ticketUrlForced ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Genera URL print:// para comanda de cocina (respeta printer_auto_comanda del tenant)
     */
    public function comandaUrl(Venta $venta): ?string
    {
        try {
            $tenant = \App\Helpers\TenantHelper::current();
            if (!$tenant || !$tenant->printer_auto_comanda) return null;
            return $this->buildComandaUrlInternal($venta, $tenant);
        } catch (\Throwable $e) {
            Log::error('EscposPrintService::comandaUrl ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Genera URL print:// para comanda sin importar printer_auto_comanda (reimpresión manual)
     */
    public function comandaUrlForced(Venta $venta): ?string
    {
        try {
            $tenant = \App\Helpers\TenantHelper::current();
            if (!$tenant) return null;
            return $this->buildComandaUrlInternal($venta, $tenant);
        } catch (\Throwable $e) {
            Log::error('EscposPrintService::comandaUrlForced ' . $e->getMessage());
            return null;
        }
    }

    private function buildTicketUrl(Venta $venta, $tenant): ?string
    {
        $json = $this->buildTicketJson($venta, $tenant);
        return 'print://' . $this->encodePayload($json, $tenant);
    }

    private function buildComandaUrlInternal(Venta $venta, $tenant): ?string
    {
        $items = $venta->items->filter(fn($i) => $i->producto && $i->producto->tipo === 'Platos');
        if ($items->isEmpty()) return null;
        $porciones = $venta->items->filter(fn($i) => $i->producto && $i->producto->tipo === 'Porciones');
        $json = $this->buildComandaJson($venta, $items, $porciones, $tenant);
        return 'print://' . $this->encodePayload($json, $tenant);
    }

    /**
     * Genera ambas URLs (ticket y comanda si aplica)
     */
    public function combinedUrl(Venta $venta): ?array
    {
        try {
            $urls = [];
            $ticketUrl = $this->ticketUrl($venta);
            if ($ticketUrl) $urls['ticket'] = $ticketUrl;
            $comandaUrl = $this->comandaUrl($venta);
            if ($comandaUrl) $urls['comanda'] = $comandaUrl;
            return !empty($urls) ? $urls : null;
        } catch (\Throwable $e) {
            Log::error('EscposPrintService::combinedUrl ' . $e->getMessage());
            return null;
        }
    }

    // ============================================================================
    // HTML Fallback (para navegadores sin soporte print://)
    // ============================================================================

    /**
     * Genera HTML autoimprimible para ticket
     */
    public function ticketHtml(Venta $venta): ?string
    {
        try {
            $tenant = \App\Helpers\TenantHelper::current();
            if (!$tenant) return null;
            return $this->buildTicketHtml($venta, $tenant);
        } catch (\Throwable $e) {
            Log::error('EscposPrintService::ticketHtml ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Genera HTML autoimprimible para comanda
     */
    public function comandaHtml(Venta $venta): ?string
    {
        try {
            $tenant = \App\Helpers\TenantHelper::current();
            if (!$tenant) return null;
            $items = $venta->items->filter(fn($i) => $i->producto && $i->producto->tipo === 'Platos');
            if ($items->isEmpty()) return null;
            $porciones = $venta->items->filter(fn($i) => $i->producto && $i->producto->tipo === 'Porciones');
            return $this->buildComandaHtml($venta, $items, $porciones, $tenant);
        } catch (\Throwable $e) {
            Log::error('EscposPrintService::comandaHtml ' . $e->getMessage());
            return null;
        }
    }

    // ============================================================================
    // Construcción JSON para print-agent (formato TicketData estructurado)
    // ============================================================================

    /**
     * Construye JSON del ticket en formato TicketData
     */
    private function buildTicketJson(Venta $venta, $tenant): string
    {
        $items = $venta->items->filter(fn($i) => $i->producto)->values();

        // Formatear items con cantidad y nombre pluralizado
        $ticketItems = [];
        foreach ($items as $item) {
            $nombre = $this->pluralizarNombre($item->producto->nombre, $item->cantidad);
            $ticketItems[] = [
                'text' => "{$item->cantidad} {$nombre}",
                'price' => (float) $item->subtotal
            ];
        }

        // Footer con datos del encargado
        $footer = "¡Gracias por su compra!";
        if ($venta->turno && $venta->turno->encargado) {
            $encargado = $venta->turno->encargado;
            $footer .= "\nEncargado: {$encargado->nombre}";
            if (!empty($encargado->celular)) {
                $footer .= "\nCelular: {$encargado->celular}";
            }
        }

        $ticket = [
            'printer' => $tenant->printer_nombre_ticket ?? '',
            'show_logo' => (bool) ($tenant->printer_logo ?? false),
            'show_business_name' => true,
            'business_name' => mb_strtoupper($tenant->nombre ?? 'MI NEGOCIO'),
            'title' => "VENTA #{$venta->numero_venta}",
            'date' => $venta->fecha_hora?->format('d/m/Y H:i') ?? now()->format('d/m/Y H:i'),
            'user' => $venta->usuario?->nombre ?? '',
            'client' => '',
            'items' => $ticketItems,
            'total' => (float) $venta->total,
            'cash' => (float) ($venta->efectivo ?? 0),
            'online' => (float) ($venta->online ?? 0),
            'credit' => (float) ($venta->credito ?? 0),
            'footer' => $footer
        ];

        return json_encode($ticket, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Construye JSON de comanda (formato con lines para mayor control)
     */
    private function buildComandaJson(Venta $venta, $items, $porciones, $tenant): string
    {
        $lines = [];

        // Título
        $lines[] = ['type' => 'text', 'value' => "VENTA #{$venta->numero_venta}", 'bold' => true, 'size' => 'triple', 'align' => 'center'];
        $lines[] = ['type' => 'feed', 'height' => 1];

        // Items de platos
        foreach ($items as $item) {
            $nombre  = $this->nombreCorto($item);
            $detalle = $this->buildDetalle($item);
            $inicio  = "{$item->cantidad} {$nombre}";

            if ($detalle) {
                $anchoTotal = 12;
                $puntosNecesarios = max(1, $anchoTotal - mb_strlen($inicio) - mb_strlen($detalle));
                $texto = $inicio . str_repeat('.', $puntosNecesarios) . $detalle;
            } else {
                $texto = $inicio;
            }

            $lines[] = ['type' => 'text', 'value' => $texto, 'bold' => false, 'size' => 'double', 'align' => 'left'];
        }

        // Porciones
        if ($porciones && $porciones->isNotEmpty()) {
            $lines[] = ['type' => 'feed', 'height' => 1];
            $lines[] = ['type' => 'text', 'value' => "----- P O R C I O N E S -----", 'bold' => true, 'size' => 'normal', 'align' => 'center'];
            $lines[] = ['type' => 'feed', 'height' => 1];

            foreach ($porciones as $item) {
                $nombre = $this->pluralizarNombre($item->producto->nombre, $item->cantidad);
                $lines[] = ['type' => 'text', 'value' => "{$item->cantidad} {$nombre}", 'bold' => false, 'size' => 'double', 'align' => 'left'];
            }
        }

        $lines[] = ['type' => 'feed', 'height' => 3];
        $lines[] = ['type' => 'cut', 'value' => 'partial'];

        return json_encode(['printer' => $tenant->printer_nombre_comanda ?? '', 'lines' => $lines], JSON_UNESCAPED_UNICODE);
    }

    // ============================================================================
    // Construcción HTML Fallback
    // ============================================================================

    private function buildTicketHtml(Venta $venta, $tenant): string
    {
        $items = $venta->items->filter(fn($i) => $i->producto)->values();

        $html = '<!DOCTYPE html><html><head><meta charset="utf-8">';
        $html .= '<style>';
        $html .= 'body{font-family:monospace;width:300px;margin:20px auto;font-size:12px}';
        $html .= '.center{text-align:center}.right{text-align:right}.bold{font-weight:bold}';
        $html .= '.large{font-size:18px}.item{display:flex;justify-content:space-between}';
        $html .= '@media print{body{margin:0}}';
        $html .= '</style>';
        $html .= '<script>window.onload=function(){window.print();}</script>';
        $html .= '</head><body>';

        // Encabezado
        $html .= '<div class="center bold large">' . htmlspecialchars(mb_strtoupper($tenant->nombre ?? 'MI NEGOCIO')) . '</div>';
        $html .= '<div class="center bold large">VENTA #' . $venta->numero_venta . '</div><br>';

        // Info
        $html .= 'Fecha: ' . ($venta->fecha_hora?->format('d/m/Y H:i') ?? now()->format('d/m/Y H:i')) . '<br>';
        if ($venta->usuario) $html .= 'Cajero: ' . htmlspecialchars($venta->usuario->nombre) . '<br>';
        $html .= '<br>--- D E T A L L E ---<br><br>';

        // Items
        foreach ($items as $item) {
            $nombre = $this->pluralizarNombre($item->producto->nombre, $item->cantidad);
            $html .= '<div class="item">';
            $html .= '<span>' . $item->cantidad . ' ' . htmlspecialchars($nombre) . '</span>';
            $html .= '<span>' . number_format($item->subtotal, 2) . '</span>';
            $html .= '</div>';
        }

        // Total
        $html .= '<br><div class="right bold">TOTAL: Bs. ' . number_format($venta->total, 2) . '</div>';

        // Métodos de pago
        if (($venta->efectivo ?? 0) > 0 && (float)$venta->efectivo != (float)$venta->total) {
            $html .= '<div class="right">Efectivo: ' . number_format($venta->efectivo, 2) . '</div>';
        }
        if (($venta->online ?? 0) > 0) {
            $html .= '<div class="right">Online: ' . number_format($venta->online, 2) . '</div>';
        }
        if (($venta->credito ?? 0) > 0) {
            $html .= '<div class="right">Crédito: ' . number_format($venta->credito, 2) . '</div>';
        }

        // Footer
        $html .= '<br><div class="center bold">¡Gracias por su compra!</div>';
        if ($venta->turno && $venta->turno->encargado) {
            $html .= '<div class="center">' . htmlspecialchars($venta->turno->encargado->nombre) . '</div>';
            if (!empty($venta->turno->encargado->celular)) {
                $html .= '<div class="center">' . htmlspecialchars($venta->turno->encargado->celular) . '</div>';
            }
        }

        $html .= '</body></html>';
        return $html;
    }

    private function buildComandaHtml(Venta $venta, $items, $porciones, $tenant): string
    {
        $html = '<!DOCTYPE html><html><head><meta charset="utf-8">';
        $html .= '<style>';
        $html .= 'body{font-family:monospace;width:200px;margin:20px auto;font-size:16px}';
        $html .= '.center{text-align:center}.bold{font-weight:bold}.large{font-size:24px}';
        $html .= '@media print{body{margin:0}}';
        $html .= '</style>';
        $html .= '<script>window.onload=function(){window.print();}</script>';
        $html .= '</head><body>';

        $html .= '<div class="center bold large">VENTA #' . $venta->numero_venta . '</div><br>';

        foreach ($items as $item) {
            $nombre = $this->nombreCorto($item);
            $detalle = $this->buildDetalle($item);
            $html .= '<div class="large">' . $item->cantidad . ' ' . htmlspecialchars($nombre);
            if ($detalle) $html .= ' <span style="float:right">' . htmlspecialchars($detalle) . '</span>';
            $html .= '</div>';
        }

        if ($porciones && $porciones->isNotEmpty()) {
            $html .= '<br><div class="center bold">P O R C I O N E S</div><br>';
            foreach ($porciones as $item) {
                $nombre = $this->pluralizarNombre($item->producto->nombre, $item->cantidad);
                $html .= '<div class="large">' . $item->cantidad . ' ' . htmlspecialchars($nombre) . '</div>';
            }
        }

        $html .= '</body></html>';
        return $html;
    }

    // ============================================================================
    // Cifrado: JSON → gzip → AES-256-GCM → base64url
    // ============================================================================

    private function encodePayload(string $data, $tenant): string
    {
        $compressed = gzencode($data, 9);
        return $this->encrypt($compressed, $tenant);
    }

    private function encrypt(string $data, $tenant): string
    {
        $keyHex = $tenant->printer_secret_key ?? '';
        if (empty($keyHex)) {
            throw new \RuntimeException('La clave de cifrado (printer_secret_key) no está configurada');
        }
        $key = hex2bin($keyHex);
        if (strlen($key) !== 32) {
            throw new \RuntimeException('La clave de cifrado debe tener 64 caracteres hexadecimales');
        }
        $nonce      = random_bytes(12);
        $tag        = '';
        $ciphertext = openssl_encrypt($data, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $nonce, $tag, '', 16);
        $raw = $nonce . $ciphertext . $tag;
        return rtrim(strtr(base64_encode($raw), '+/', '-_'), '=');
    }

    // ============================================================================
    // Helpers (adaptados de FADI V2)
    // ============================================================================

    /**
     * Pluralizar nombres de productos (adaptado de FADI V2)
     * - Respeta preposiciones: "Paquete de galletas" → "Paquetes de galletas"
     * - Detecta números: "Coca Cola 250ml" → "Coca Colas 250ml"
     */
    private function pluralizarNombre(string $nombre, int $cantidad): string
    {
        if ($cantidad <= 1) return $nombre;

        $preposiciones = ['de', 'del', 'sin', 'con', 'a', 'al', 'en', 'para', 'por', 'y'];
        $tokens = explode(' ', $nombre);
        $stopIdx = null;
        $digitIdx = null;

        // Buscar preposición o número
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

        // Pluralizar hasta la preposición
        if ($stopIdx !== null) {
            for ($i = 0; $i < $stopIdx; $i++) {
                $tokens[$i] = $this->pluralES($tokens[$i]);
            }
        }
        // Pluralizar solo la palabra antes del número
        elseif ($digitIdx !== null && $digitIdx > 0) {
            $tokens[$digitIdx - 1] = $this->pluralES($tokens[$digitIdx - 1]);
        }
        // Pluralizar todas las palabras
        else {
            foreach ($tokens as $i => $token) {
                $tokens[$i] = $this->pluralES($token);
            }
        }

        return implode(' ', $tokens);
    }

    /**
     * Reglas de pluralización en español
     */
    private function pluralES(string $word): string
    {
        if (empty($word)) return $word;

        $ult = mb_strtolower(mb_substr($word, -1));

        if ($ult === 'z') return mb_substr($word, 0, -1) . 'ces';
        if (in_array($ult, ['a', 'e', 'i', 'o', 'u'])) return $word . 's';

        return $word . 'es';
    }

    /**
     * Nombre corto para comanda (primera palabra + pluralización + sufijo)
     */
    private function nombreCorto($item): string
    {
        $nombre = $item->producto->nombre;
        $pos    = strpos($nombre, ' ');
        $cad1   = $pos !== false ? substr($nombre, 0, $pos) : $nombre;
        $cad2   = $pos !== false ? trim(substr($nombre, $pos + 1)) : '';

        // Pluralizar primera palabra
        if ($item->cantidad > 1) {
            $ult   = mb_strtolower(mb_substr($cad1, -1));
            $cad1 .= in_array($ult, ['a', 'e', 'i', 'o', 'u']) ? 's' : 'es';
        }

        // Abreviar "sin huevo"
        $sufijo = strcasecmp($cad2, 'sin huevo') === 0 ? ' S/H' : '';

        return $cad1 . $sufijo;
    }

    /**
     * Construir detalle de arroz/fideo/mixto para comanda
     */
    private function buildDetalle($item): string
    {
        if ($item->producto->tipo !== 'Platos' || empty($item->detalle)) return '';

        $arr    = $item->detalle['arroz'] ?? 0;
        $fid    = $item->detalle['fideo'] ?? 0;
        $mix    = $item->detalle['mixto'] ?? 0;
        $partes = [];

        if ($arr > 0) $partes[] = "{$arr}A";
        if ($fid > 0) $partes[] = "{$fid}F";
        if ($mix > 0) $partes[] = "{$mix}M";

        return implode(' - ', $partes);
    }

    /**
     * Sanitizar texto para compatibilidad ESC/POS
     */
    private function sanitize(string $text): string
    {
        return strtr($text, [
            'ñ' => 'n', 'Ñ' => 'N',
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
            'Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U',
            'ü' => 'u', 'Ü' => 'U',
            '¡' => '!', '¿' => '?',
        ]);
    }

    /**
     * Truncar texto en el centro para que quepa en el ancho disponible
     * Ejemplo: "Coca Cola, Fanta y Sprite 190ml" -> "Coca Cola, ... 190ml"
     */
    private function truncarEnCentro(string $texto, int $maxLength): string
    {
        if (mb_strlen($texto) <= $maxLength) {
            return $texto;
        }

        $ellipsis = '...';
        $ellipsisLen = 3;
        $availableLen = $maxLength - $ellipsisLen;

        if ($availableLen <= 0) {
            return mb_substr($texto, 0, $maxLength);
        }

        // Dividir el espacio: más al inicio que al final
        $leftLen = (int) ceil($availableLen * 0.6);
        $rightLen = $availableLen - $leftLen;

        $left = mb_substr($texto, 0, $leftLen);
        $right = mb_substr($texto, -$rightLen);

        return trim($left) . $ellipsis . trim($right);
    }

    // ============================================================================
    // AGENTE HTTP — POST http://localhost:9876/api/print/universal
    // ============================================================================

    /**
     * Encripta una sección ESC/POS con AES-256-GCM:
     *   gzip(bytes) → AES-256-GCM → base64url (sin padding)
     *
     * Formato binario: nonce(12 bytes) + ciphertext + tag(16 bytes)
     */
    public function encryptSection(string $hexKey, string $escBytes): string
    {
        $key = hex2bin($hexKey);
        if (strlen($key) !== 32) {
            throw new \RuntimeException('La clave debe tener exactamente 64 caracteres hexadecimales (32 bytes)');
        }

        $compressed = gzencode($escBytes, 9, FORCE_GZIP);
        $nonce      = random_bytes(12);
        $tag        = '';
        $ciphertext = openssl_encrypt($compressed, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $nonce, $tag, '', 16);

        if ($ciphertext === false) {
            throw new \RuntimeException('Error en el cifrado AES-256-GCM: ' . openssl_error_string());
        }

        return rtrim(strtr(base64_encode($nonce . $ciphertext . $tag), '+/', '-_'), '=');
    }

    /**
     * Genera bytes ESC/POS de la cabecera del ticket.
     *
     * $data acepta:
     *   - 'business_name' : nombre del negocio (opcional, se imprime grande)
     *   - 'title'         : título del ticket, ej. "VENTA #23721"
     *   - 'date'          : fecha y hora, ej. "20/04/2026 17:18"
     *   - 'user'          : nombre del cajero (opcional)
     *   - 'client'        : nombre del cliente (opcional)
     */
    public function buildEscHeader(array $data): string
    {
        $esc = "\x1B\x40"; // Inicializar impresora

        // Nombre del negocio (doble alto+ancho, centrado)
        if (!empty($data['business_name'])) {
            $esc .= "\x1B\x61\x01";                                          // Centro
            $esc .= "\x1D\x21\x11";                                          // Doble alto+ancho
            $esc .= "\x1B\x45\x01";                                          // Negrita ON
            $esc .= $this->sanitize(mb_strtoupper($data['business_name'])) . "\x0A";
            $esc .= "\x1B\x45\x00";                                          // Negrita OFF
            $esc .= "\x1D\x21\x00";                                          // Tamaño normal
            $esc .= "\x0A";
        }

        // Título del ticket (solo doble alto, centrado)
        if (!empty($data['title'])) {
            $esc .= "\x1B\x61\x01";                                          // Centro
            $esc .= "\x1D\x21\x01";                                          // Solo doble alto
            $esc .= "\x1B\x45\x01";                                          // Negrita ON
            $esc .= $this->sanitize($data['title']) . "\x0A";
            $esc .= "\x1B\x45\x00";                                          // Negrita OFF
            $esc .= "\x1D\x21\x00";                                          // Tamaño normal
        }

        // Fecha, cajero, cliente — izquierda, tamaño normal
        $esc .= "\x1B\x61\x00";                                              // Izquierda
        $esc .= "\x0A";

        if (!empty($data['date'])) {
            $esc .= $this->sanitize('Fecha:   ' . $data['date']) . "\x0A";
        }
        if (!empty($data['user'])) {
            $esc .= $this->sanitize('Cajero:  ' . $data['user']) . "\x0A";
        }
        if (!empty($data['client'])) {
            $esc .= $this->sanitize('Cliente: ' . $data['client']) . "\x0A";
        }

        $esc .= "\x0A";

        return $esc;
    }

    /**
     * Genera bytes ESC/POS del detalle de ítems.
     *
     * Cada ítem: ['text' => '2 Pollos con arroz', 'price' => 23.50]
     * Resultado: "2 Pollos con arroz...............23.50"
     */
    public function buildEscBody(array $items, int $cols = 48): string
    {
        $esc = "\x1B\x61\x00"; // Izquierda

        // Encabezado de columnas
        $col1   = 'DESCRIPCION';
        $col2   = 'IMPORTE';
        $espacio = max(1, $cols - mb_strlen($col1) - mb_strlen($col2));
        $esc .= "\x1B\x45\x01"; // Negrita ON
        $esc .= $col1 . str_repeat(' ', $espacio) . $col2 . "\x0A";
        $esc .= "\x1B\x45\x00"; // Negrita OFF

        foreach ($items as $item) {
            $texto  = $this->sanitize((string) ($item['text'] ?? ''));
            $precio = number_format((float) ($item['price'] ?? 0), 2);

            // Truncar nombre si es demasiado largo (deja al menos 1 punto + precio)
            $maxTexto = $cols - mb_strlen($precio) - 2;
            if (mb_strlen($texto) > $maxTexto) {
                $texto = mb_substr($texto, 0, $maxTexto - 3) . '...';
            }

            $puntos = max(1, $cols - mb_strlen($texto) - mb_strlen($precio));
            $esc .= $texto . str_repeat('.', $puntos) . $precio . "\x0A";
        }

        return $esc;
    }

    /**
     * Genera bytes ESC/POS del total del ticket.
     * Solo imprime: TOTAL: 51.00 — alineado a la derecha, negrita + doble alto.
     */
    public function buildEscTotals(array $totals, int $cols = 48): string
    {
        $total = (float) ($totals['TOTAL'] ?? reset($totals));
        $valor = number_format($total, 2);
        $linea = 'TOTAL: ' . $valor;

        $esc  = "\x1B\x61\x00"; // Izquierda
        $esc .= str_repeat('-', $cols) . "\x0A";

        // TOTAL alineado a la derecha, negrita, doble alto
        $padding = max(0, $cols - mb_strlen($linea));
        $esc .= "\x1B\x61\x02";  // Alinear derecha
        $esc .= "\x1D\x21\x01";  // Doble alto
        $esc .= "\x1B\x45\x01";  // Negrita ON
        $esc .= $this->sanitize($linea) . "\x0A";
        $esc .= "\x1B\x45\x00";  // Negrita OFF
        $esc .= "\x1D\x21\x00";  // Tamaño normal
        $esc .= "\x1B\x61\x00";  // Volver a izquierda

        return $esc;
    }

    /**
     * Genera bytes ESC/POS del pie de página, feeds, corte y apertura de caja.
     *
     * REGLA CRÍTICA: El corte y la apertura de caja van aquí dentro.
     * El agente NO agrega nada automáticamente.
     */
    public function buildEscFooter(string $message, bool $cut = true, bool $cashDrawer = false): string
    {
        $esc = "\x1B\x61\x01"; // Centro
        $esc .= "\x0A";
        $esc .= $this->sanitize($message) . "\x0A";
        $esc .= "\x0A\x0A\x0A"; // 3 saltos de línea para separar del corte

        if ($cut) {
            $esc .= "\x1D\x56\x41\x00"; // Corte parcial
        }

        if ($cashDrawer) {
            $esc .= "\x1B\x70\x00\x32\xFA"; // Apertura de caja registradora
        }

        return $esc;
    }

    /**
     * Envía un UniversalJob al agente de impresión HTTP local.
     *
     * $job puede contener (todos encriptados con encryptSection):
     *   logo, header, body, totals, qr, footer
     *
     * @return array ['ok' => bool, 'status'? => int, 'error'? => string]
     */
    public function print(string $printerName, array $job): array
    {
        $baseUrl = config('print_agent.base_url', 'http://localhost:9876');
        $payload = array_merge(['printer' => $printerName], $job);

        try {
            $response = \Illuminate\Support\Facades\Http::timeout(8)
                ->post("{$baseUrl}/api/print/universal", $payload);

            if ($response->successful()) {
                return ['ok' => true, 'status' => $response->status()];
            }

            Log::warning('EscposPrintService::print respuesta no exitosa', [
                'printer' => $printerName,
                'status'  => $response->status(),
                'body'    => $response->body(),
            ]);
            return ['ok' => false, 'error' => "HTTP {$response->status()}: " . $response->body()];

        } catch (\Throwable $e) {
            Log::error('EscposPrintService::print error: ' . $e->getMessage());
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Conveniencia: construye y envía el ticket completo de una Venta al agente HTTP.
     * Usa la configuración del tenant activo (clave, impresora, ancho, logo).
     */
    public function printVentaAgent(Venta $venta, ?string $printerName = null, bool $cashDrawer = false): array
    {
        try {
        $tenant = \App\Helpers\TenantHelper::current();
        $key    = $tenant?->printer_secret_key ?? config('print_agent.secret_key', '');

        if (empty($key)) {
            return ['ok' => false, 'error' => 'No hay clave de cifrado configurada (printer_secret_key)'];
        }

        $nombre = $printerName ?? $tenant?->printer_nombre_ticket ?? '';
        if (empty($nombre)) {
            return ['ok' => false, 'error' => 'No hay impresora de ticket configurada'];
        }

        $cols = ((int) ($tenant?->printer_width ?? config('printer.width', 80))) >= 80 ? 48 : 32;
        $showNombre = (bool) ($tenant?->printer_show_nombre ?? true);
        $showLogo   = (bool) ($tenant?->printer_logo ?? false);

        // Formatear ítems
        $items = $venta->items
            ->filter(fn($i) => $i->producto)
            ->map(fn($i) => [
                'text'  => $i->cantidad . ' ' . $this->pluralizarNombre($i->producto->nombre, (int) $i->cantidad),
                'price' => (float) $i->subtotal,
            ])->values()->toArray();

        // Solo TOTAL — sin desglose de métodos de pago
        $totals = ['TOTAL' => (float) $venta->total];

        // Footer: encargado (si existe) + 1 salto + corte
        $footer = "\x1B\x61\x01"; // Centro
        if ($venta->turno && $venta->turno->encargado) {
            $enc = $venta->turno->encargado;
            $footer .= $this->sanitize($enc->nombre) . "\x0A";
            if (!empty($enc->celular)) {
                $footer .= $this->sanitize($enc->celular) . "\x0A";
            }
        }
        $footer .= "\x0A"; // 1 salto de línea
        if ($cashDrawer) {
            $footer .= "\x1B\x70\x00\x32\xFA"; // Apertura de caja
        }
        $footer .= "\x1D\x56\x41\x00"; // Corte parcial

        return $this->print($nombre, [
            'logo'   => $showLogo,
            'header' => $this->encryptSection($key, $this->buildEscHeader([
                'business_name' => $showNombre ? mb_strtoupper($tenant?->nombre ?? '') : '',
                'title'         => 'VENTA #' . $venta->numero_venta,
                'date'          => $venta->fecha_hora?->format('d/m/Y H:i') ?? now()->format('d/m/Y H:i'),
                'user'          => $venta->usuario?->nombre ?? '',
            ])),
            'body'   => $this->encryptSection($key, $this->buildEscBody($items, $cols)),
            'totals' => $this->encryptSection($key, $this->buildEscTotals($totals, $cols)),
            'footer' => $this->encryptSection($key, $footer),
        ]);
        } catch (\Throwable $e) {
            Log::error('EscposPrintService::printVentaAgent ' . $e->getMessage());
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Conveniencia: construye y envía la comanda de cocina de una Venta al agente HTTP.
     */
    public function printComandaAgent(Venta $venta, ?string $printerName = null): array
    {
        try {
        $tenant = \App\Helpers\TenantHelper::current();
        $key    = $tenant?->printer_secret_key ?? config('print_agent.secret_key', '');

        if (empty($key)) {
            return ['ok' => false, 'error' => 'No hay clave de cifrado configurada'];
        }

        // Usar impresora de cocina; si no está configurada, usar la de ticket como fallback
        $nombre = $printerName
            ?? ((!empty($tenant?->printer_nombre_comanda)) ? $tenant->printer_nombre_comanda : null)
            ?? $tenant?->printer_nombre_ticket
            ?? '';
        if (empty($nombre)) {
            return ['ok' => false, 'error' => 'No hay impresora configurada para la comanda'];
        }

        $cols = ((int) ($tenant?->printer_width ?? config('printer.width', 80))) >= 80 ? 48 : 32;

        // Todos los ítems de la venta (sin filtrar por tipo)
        $todosItems = $venta->items->filter(fn($i) => $i->producto);

        if ($todosItems->isEmpty()) {
            return ['ok' => false, 'error' => 'No hay ítems para imprimir en la comanda'];
        }

        // Separar platos (con detalle de acompañamiento) del resto
        $platos    = $todosItems->filter(fn($i) => $i->producto->tipo === 'Platos');
        $porciones = $todosItems->filter(fn($i) => $i->producto->tipo === 'Porciones');
        $otros     = $todosItems->filter(fn($i) => !in_array($i->producto->tipo, ['Platos', 'Porciones']));

        // Cuerpo de la comanda (fuente grande, legible desde lejos)
        $body = "\x1B\x40";           // Inicializar
        $body .= "\x1B\x61\x01";     // Centro
        $body .= "\x1D\x21\x11";     // Doble alto+ancho
        $body .= "\x1B\x45\x01";     // Negrita ON
        $body .= 'VENTA #' . $venta->numero_venta . "\x0A";
        $body .= "\x1B\x45\x00";     // Negrita OFF
        $body .= "\x1D\x21\x00";     // Tamaño normal
        $body .= "\x0A";

        // Ítems de cocina (platos con acompañamiento)
        foreach ($platos as $item) {
            $nombre2 = $this->nombreCorto($item);
            $detalle = $this->buildDetalle($item);
            $texto   = $item->cantidad . ' ' . $nombre2;
            if ($detalle) {
                $puntos = max(1, $cols - mb_strlen($texto) - mb_strlen($detalle));
                $texto .= str_repeat('.', $puntos) . $detalle;
            }
            $body .= "\x1B\x61\x00";     // Izquierda
            $body .= "\x1D\x21\x01";     // Doble alto
            $body .= $this->sanitize($texto) . "\x0A";
            $body .= "\x1D\x21\x00";     // Normal
        }

        // Porciones
        if ($porciones->isNotEmpty()) {
            $body .= "\x0A";
            $body .= "\x1B\x61\x01";     // Centro
            $body .= $this->sanitize('----- P O R C I O N E S -----') . "\x0A";
            $body .= "\x0A";
            foreach ($porciones as $item) {
                $n = $this->pluralizarNombre($item->producto->nombre, (int) $item->cantidad);
                $body .= "\x1B\x61\x00";     // Izquierda
                $body .= "\x1D\x21\x01";     // Doble alto
                $body .= $this->sanitize($item->cantidad . ' ' . $n) . "\x0A";
                $body .= "\x1D\x21\x00";     // Normal
            }
        }

        // Otros ítems (refrescos, bebidas, etc.)
        if ($otros->isNotEmpty()) {
            if ($platos->isNotEmpty() || $porciones->isNotEmpty()) {
                $body .= "\x0A";
                $body .= "\x1B\x61\x01";     // Centro
                $body .= $this->sanitize('------------ + ------------') . "\x0A";
                $body .= "\x0A";
            }
            foreach ($otros as $item) {
                $n = $this->pluralizarNombre($item->producto->nombre, (int) $item->cantidad);
                $body .= "\x1B\x61\x00";     // Izquierda
                $body .= "\x1D\x21\x01";     // Doble alto
                $body .= $this->sanitize($item->cantidad . ' ' . $n) . "\x0A";
                $body .= "\x1D\x21\x00";     // Normal
            }
        }

        // Footer comanda: feeds + corte parcial
        $footer = "\x0A\x0A\x0A" . "\x1D\x56\x41\x00"; // 3 feeds + corte parcial

        return $this->print($nombre, [
            'logo'   => false,
            'body'   => $this->encryptSection($key, $body),
            'footer' => $this->encryptSection($key, $footer),
        ]);
        } catch (\Throwable $e) {
            Log::error('EscposPrintService::printComandaAgent ' . $e->getMessage());
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }
}
