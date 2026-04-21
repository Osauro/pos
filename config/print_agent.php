<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Clave secreta AES-256-GCM compartida con el Print Agent
    |--------------------------------------------------------------------------
    | Formato: 64 caracteres hexadecimales (32 bytes)
    | Esta clave se usa para cifrar las secciones ESC/POS antes de enviarlas.
    | Debe coincidir exactamente con la configurada en el agente Windows.
    */
    'secret_key' => env('PRINT_AGENT_SECRET_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | URL base del agente de impresión local
    |--------------------------------------------------------------------------
    | El agente HTTP corre en el equipo Windows donde están las impresoras.
    | En producción, normalmente es localhost (mismo equipo que el servidor).
    */
    'base_url' => env('PRINT_AGENT_URL', 'http://localhost:9876'),

    /*
    |--------------------------------------------------------------------------
    | Impresoras configuradas en el agente
    |--------------------------------------------------------------------------
    | Estas entradas son solo documentación/referencia para el desarrollador.
    | El nombre exacto que se envía en cada job viene del campo printer_nombre_*
    | del tenant (configurado en el panel de administración).
    |
    | Formato:
    |   'alias' => [
    |       'nombre'  => 'Nombre exacto en Windows',
    |       'papel'   => 80,   // mm
    |       'copias'  => 1,
    |   ]
    */
    'printers' => [
        'cocina' => [
            'nombre' => 'POS-80ccc',
            'papel'  => 80,
            'copias' => 1,
        ],
        'fadi' => [
            'nombre' => 'POS-80C',
            'papel'  => 80,
            'copias' => 1,
        ],
        'inventarios' => [
            'nombre' => 'POS-58',
            'papel'  => 58,
            'copias' => 1,
        ],
    ],

];
