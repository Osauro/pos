<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Préstamo #{{ $prestamo->numero_folio }}</title>
    @php
        // Configuración dinámica según tamaño de papel (default desde config, pero JS usará localStorage)
        $is58mm = ($config->papel_tamano ?? '80mm') === '58mm';
        $paperWidth = $is58mm ? '58mm' : '80mm';
        $bodyWidth = $is58mm ? '52mm' : '70mm'; // Reducido para evitar cortes
        $logoWidth = $is58mm ? '30mm' : '40mm'; // Logo más pequeño para mejor resolución
        $logoHeight = $is58mm ? '15mm' : '20mm';
        $fontSize = $is58mm ? '11px' : '12px'; // Aumentado
        $lineHeight = $is58mm ? '1.3' : '1.4'; // Aumentado
        $margin = $is58mm ? '2mm' : '4mm';
    @endphp
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        /* El alto es AUTO - se ajusta al contenido */
        @page {
            size: {{ $paperWidth }} auto;
            margin: {{ $margin }};
        }

        body {
            font-family: 'Consolas', 'Monaco', 'Lucida Console', 'Courier New', monospace;
            font-size: {{ $fontSize }};
            line-height: {{ $lineHeight }};
            color: #000;
            width: {{ $bodyWidth }};
            margin: 0;
            padding: 0;
            -webkit-font-smoothing: antialiased;
        }

        .center { text-align: center; }
        .right { text-align: right; }
        .bold { font-weight: bold; }

        .logo-container {
            text-align: center;
            margin-bottom: 4px;
        }

        .logo {
            display: inline-block;
            max-width: {{ $logoWidth }};
            max-height: {{ $logoHeight }};
        }

        .nombre-tienda {
            font-size: 15px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 2px;
        }

        .info-tienda {
            text-align: center;
            font-size: 10px;
            margin-bottom: 2px;
        }

        .linea {
            border: none;
            border-top: 1px dashed #000;
            margin: 4px 0;
        }

        .linea-doble {
            border: none;
            border-top: 2px solid #000;
            margin: 4px 0;
        }

        .titulo-seccion {
            text-align: center;
            font-weight: bold;
            font-size: 13px;
            letter-spacing: 2px;
            margin: 2px 0;
        }

        .datos-prestamo {
            font-size: 11px;
            width: 100%;
            margin: 0 auto;
        }

        .datos-prestamo td {
            padding: 1px 0;
            vertical-align: top;
            text-align: left;
        }

        .datos-prestamo .label {
            font-weight: bold;
            width: 55px;
            text-align: left;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
            margin: 2px 0;
        }

        .items-table td {
            padding: 2px 0;
            vertical-align: top;
        }

        .items-table .producto {
            text-align: left;
            font-weight: 500;
            font-size: 12px;
        }

        .items-table .precio { width: 55px; text-align: right; font-weight: bold; }

        .totales-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }

        .totales-table td { padding: 1px 0; }
        .totales-table .total-label { text-align: right; font-weight: bold; padding-right: 4px; }
        .totales-table .total-valor { text-align: right; width: 55px; }

        .total-principal { font-size: 14px; font-weight: bold; }

        .mensaje-final {
            text-align: center;
            font-weight: bold;
            font-size: 13px;
            margin: 6px 0 2px;
        }

        .pie {
            text-align: center;
            font-size: 12px;
            color: #555;
            margin-top: 4px;
        }

        /* Resaltar información del préstamo */
        .info-prestamo {
            background-color: #f0f0f0;
            border: 1px solid #333;
            padding: 3px;
            margin: 4px 0;
            text-align: center;
            font-weight: bold;
        }

        /* Ocultar controles en impresión */
        .no-print { display: block; text-align: center; margin: 10px 0; }
        @media print {
            body { width: 100%; padding: 0; }
            .no-print { display: none !important; }
            .info-prestamo { background-color: #f0f0f0; }
        }
    </style>
</head>
<body>
    {{-- Logo --}}
    @if($config->logo)
        <div class="logo-container">
            <img src="{{ asset('storage/' . $config->logo) }}" class="logo" alt="Logo">
        </div>
    @endif

    {{-- Nombre de la tienda --}}
    <div class="nombre-tienda">{{ strtoupper($config->nombre_tienda ?? 'MI TIENDA') }}</div>

    {{-- Info de la tienda --}}
    @if($config->direccion)
        <div class="info-tienda">{{ $config->direccion }}</div>
    @endif
    @if($config->telefono)
        <div class="info-tienda">Tel: {{ $config->telefono }}</div>
    @endif
    @if($config->nit)
        <div class="info-tienda">NIT: {{ $config->nit }}</div>
    @endif

    <hr class="linea-doble">
    <div class="titulo-seccion">Préstamo #{{ $prestamo->numero_folio }}</div>
    <hr class="linea-doble">

    {{-- Datos del préstamo --}}
    <table class="datos-prestamo">
        <tr>
            <td class="label">FECHA:</td>
            <td>{{ \Carbon\Carbon::parse($prestamo->fecha_prestamo)->format('d/m/Y H:i:s') }}</td>
        </tr>
        <tr>
            <td class="label">USUARIO:</td>
            <td>{{ strtoupper($prestamo->user->name ?? 'Usuario') }}</td>
        </tr>
        <tr>
            <td class="label">CLIENTE:</td>
            <td>{{ $prestamo->cliente->nombre ?? 'Sin cliente' }}</td>
        </tr>
        <tr>
            <td class="label">ESTADO:</td>
            <td>{{ strtoupper($prestamo->estado) }}</td>
        </tr>
        @if($prestamo->expired_at)
        <tr>
            <td class="label">VENCE:</td>
            <td>{{ \Carbon\Carbon::parse($prestamo->expired_at)->format('d/m/Y') }}</td>
        </tr>
        @endif
    </table>

    <hr class="linea">
    <div class="center bold" style="letter-spacing: 3px; margin: 2px 0;">P R O D U C T O S</div>
    <hr class="linea">

    {{-- Items --}}
    <table class="items-table">
        @foreach($prestamo->prestamoItems as $item)
            @php
                $cantidadTexto = number_format($item->cantidad, 0) . ' ' . ($item->producto->unidad_medida ?? 'und');
                $textoCompleto = $cantidadTexto . ' ' . ($item->producto->nombre ?? 'Producto');
            @endphp
            <tr>
                <td class="producto" data-texto="{{ $textoCompleto }}">{{ $textoCompleto }}</td>
                <td class="precio">{{ number_format($item->subtotal, 2) }}</td>
            </tr>
        @endforeach
    </table>

    <hr class="linea">

    {{-- Total depósito --}}
    <table class="totales-table">
        <tr class="total-principal">
            <td class="total-label">DEPÓSITO:</td>
            <td class="total-valor">{{ number_format($prestamo->total, 2) }}</td>
        </tr>
    </table>

    <hr class="linea-doble">

    {{-- Información importante del préstamo --}}
    <div class="info-prestamo">
        ⚠️ PRÉSTAMO - DEBE DEVOLVERSE
    </div>

    <div class="mensaje-final">{{ $config->mensaje_ticket ?? '¡GRACIAS POR SU PREFERENCIA!' }}</div>

    <div class="pie">
        misocio.bo
    </div>

    {{-- Botones flotantes (visible solo en pantalla) --}}
    <div class="no-print botones-flotantes">
        <button class="boton-flotante" onclick="window.print()" title="Imprimir ticket">
            🖨️
        </button>
        <button class="boton-flotante boton-compartir" onclick="compartirTicket()" title="Compartir / Descargar">
            📥
        </button>
        <button class="boton-flotante boton-cerrar" onclick="window.close()" title="Cerrar ventana">
            ✕
        </button>
    </div>

    <script>
        // Función para truncar texto en el centro
        function truncateMiddle(text, limit) {
            if (!text || text.length <= limit) return text;
            const start = Math.floor((limit - 3) / 2);
            const end = Math.ceil((limit - 3) / 2);
            return text.substring(0, start) + '...' + text.substring(text.length - end);
        }

        // Aplicar truncado a productos según localStorage
        function aplicarTruncado() {
            const papelTamano = localStorage.getItem('papel_tamano') || '58mm';
            const is58mm = papelTamano === '58mm';
            const limite = is58mm ? 24 : 36; // Aumentado para aprovechar espacio disponible

            document.querySelectorAll('.items-table .producto').forEach(td => {
                const textoCompleto = td.getAttribute('data-texto');
                if (textoCompleto) {
                    td.textContent = truncateMiddle(textoCompleto, limite);
                }
            });
        }

        // Aplicar truncado antes de mostrar
        aplicarTruncado();

        // Detectar si es dispositivo móvil
        const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);

        // Auto-imprimir cuando la página cargue completamente (solo en escritorio)
        if (!isMobile) {
            window.addEventListener('load', function() {
                // Dar un pequeño delay para que el contenido se renderice completamente
                setTimeout(function() {
                    window.print();
                }, 250);
            });

            // Auto-cerrar después de imprimir o cancelar (solo en escritorio)
            window.addEventListener('afterprint', function() {
                setTimeout(function() {
                    window.close();
                }, 500);
            });
        }

        // Función para compartir/descargar ticket
        function compartirTicket() {
            if (isMobile) {
                // En móviles: Abrir PDF para que Android muestre "Abrir con"
                const prestamoId = window.location.pathname.split('/')[3];
                window.open(`/ticket/prestamo/${prestamoId}/pdf`, '_blank');
            } else {
                // En escritorio: Intentar usar la API de compartir si está disponible
                if (navigator.share) {
                    navigator.share({
                        title: document.title,
                        url: window.location.href
                    }).catch(err => console.log('Error al compartir:', err));
                } else {
                    // Fallback: Abrir PDF
                    const prestamoId = window.location.pathname.split('/')[3];
                    window.open(`/ticket/prestamo/${prestamoId}/pdf`, '_blank');
                }
            }
        }
    </script>
</body>
</html>
