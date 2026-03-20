<?php

return [
    /*
     | Ancho del papel térmico en mm.
     | Valores soportados: 80 (≈42 cols)  |  58 (≈32 cols)
     */
    'width' => (int) env('PRINTER_WIDTH', 80),

    /*
     | URL base del servicio licopos-printer (impresora local)
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
];
