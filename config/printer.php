<?php

return [
    /*
     | Ancho del papel térmico en mm.
     | Valores soportados: 58 (≈32 cols) | 80 (≈42 cols) | 110 (≈56 cols)
     */
    'width' => (int) env('PRINTER_WIDTH', 80),

    /*
     | URL base del servicio licopos-printer (impresora local, fallback)
     */
    'licopos_url' => env('PRINTER_LICOPOS_URL', 'http://localhost:1013'),

    /*
     | Imprimir comanda automáticamente al completar una venta en el POS
     */
    'auto_comanda' => (bool) env('PRINTER_AUTO_COMANDA', true),

    /*
     | Imprimir ticket al cliente automáticamente al completar una venta
     */
    'auto_ticket' => (bool) env('PRINTER_AUTO_TICKET', true),

    /*
     | Nombre del negocio que aparece en el ticket del cliente
     */
    'negocio' => env('PRINTER_NEGOCIO', 'Mi Negocio'),

    // ── ESC/POS directo (mike42/escpos-php + protocolo print://) ──────────

    /*
     | Activar modo ESC/POS directo vía protocolo print://
     | true  → el agente print-agent.php recibe la URL y envía bytes ESC/POS
     | false → fallback: ventana del navegador con window.print()
     */
    'escpos_enabled' => (bool) env('PRINTER_ESCPOS_ENABLED', false),

    /*
     | Nombre exacto de la impresora en Windows (Panel de control → Dispositivos)
     | Ejemplo: 'POS-80' / 'XP-80C' / 'EPSON TM-T20'
     */
    'printer_name' => env('PRINTER_NAME', ''),

    /*
     | Nombre de la impresora de cocina (si es distinta).
     | Dejar vacío para usar la misma que 'printer_name'.
     */
    'printer_name_cocina' => env('PRINTER_NAME_COCINA', ''),

    /*
     | Clave secreta AES-256 compartida entre el servidor y el print-agent.exe.
     | Generar con: php artisan print:keygen
     | O en la interfaz del configurador local (agent/abrir-configurador.bat).
     | Formato: 64 caracteres hexadecimales (32 bytes)
     */
    'secret_key' => env('PRINTER_SECRET_KEY', ''),
];

