<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Venta #{{ $venta->numero_folio }}</title>
    @php
        // Configuración dinámica según tamaño de papel (default desde config, pero JS usará localStorage)
        $is58mm = ($config->papel_tamano ?? '80mm') === '58mm';
        $paperWidth = $is58mm ? '58mm' : '80mm';
        $bodyWidth = $is58mm ? '52mm' : '70mm'; // Reducido para evitar cortes
        $logoWidth = $is58mm ? '30mm' : '40mm'; // Logo más pequeño para mejor resolución
        $logoHeight = $is58mm ? '15mm' : '20mm';
        $fontSize = $is58mm ? '11px' : '12px'; // Aumentado
        $lineHeight = $is58mm ? '1.2' : '1.3';
        $margin = $is58mm ? '1mm' : '2mm';
    @endphp
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        /* El alto es AUTO - se ajusta al contenido */
        @page {
            size: {{ $paperWidth }} auto;
            margin: {{ $margin }} {{ $margin }} 0;
        }

        body {
            font-family: 'Consolas', 'Monaco', 'Lucida Console', 'Courier New', monospace;
            font-size: {{ $fontSize }};
            line-height: {{ $lineHeight }};
            color: #000;
            width: {{ $bodyWidth }};
            margin: 0;
            padding: 0 0 4mm;
            -webkit-font-smoothing: antialiased;
        }

        .center { text-align: center; }
        .right { text-align: right; }
        .bold { font-weight: bold; }

        .logo {
            display: block;
            margin: 0 auto 2px;
            max-width: {{ $logoWidth }};
            max-height: {{ $logoHeight }};
        }

        .nombre-tienda {
            font-size: 15px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 1px;
        }

        .info-tienda {
            text-align: center;
            font-size: 10px;
            margin-bottom: 1px;
        }

        .linea {
            border: none;
            border-top: 1px dashed #000;
            margin: 2px 0;
        }

        .linea-doble {
            border: none;
            border-top: 2px solid #000;
            margin: 2px 0;
        }

        .titulo-seccion {
            text-align: center;
            font-weight: bold;
            font-size: 13px;
            letter-spacing: 2px;
            margin: 1px 0;
        }

        .datos-venta {
            font-size: 11px;
            width: 100%;
            margin: 0 auto;
        }

        .datos-venta td {
            padding: 0;
            vertical-align: top;
            text-align: left;
        }

        .datos-venta .label {
            font-weight: bold;
            width: 55px;
            text-align: left;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
            margin: 1px 0;
        }

        .items-table td {
            padding: 1px 0;
            vertical-align: top;
        }

        .items-table .producto {
            text-align: left;
            font-weight: 500;
            font-size: 12px;
        }

        .items-table .precio {
            width: 55px;
            text-align: right;
            font-weight: bold;
            font-size: 12px;
        }

        .totales-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }

        .totales-table td { padding: 0; }
        .totales-table .total-label { text-align: right; font-weight: bold; padding-right: 4px; }
        .totales-table .total-valor { text-align: right; width: 55px; }

        .total-principal { font-size: 14px; font-weight: bold; }

        .mensaje-final {
            text-align: center;
            font-weight: bold;
            font-size: 13px;
            margin: 3px 0 1px;
        }

        .pie {
            text-align: center;
            font-size: 12px;
            color: #555;
            margin-top: 2px;
        }

        /* Ocultar controles en impresión */
        .no-print { display: block; text-align: center; margin: 10px 0; }
        @media print {
            body { width: 100%; padding: 0; }
            .no-print { display: none !important; }
        }

        /* Botones flotantes en la esquina inferior derecha */
        .botones-flotantes {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .boton-flotante {
            background: #28a745;
            color: white;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            border: none;
            box-shadow: 0 4px 8px rgba(0,0,0,0.3);
            cursor: pointer;
            font-size: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .boton-flotante:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 12px rgba(0,0,0,0.4);
        }

        .boton-flotante:active {
            transform: scale(0.95);
        }

        .boton-compartir {
            background: #007bff;
        }

        .boton-cerrar {
            background: #dc3545;
        }
    </style>
</head>
<body>
    {{-- Logo --}}
    @if($config->logo)
        <img src="{{ asset('storage/' . $config->logo) }}" class="logo" alt="Logo">
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
    <div class="titulo-seccion">Venta #{{ $venta->numero_folio }}</div>
    <hr class="linea-doble">

    {{-- Datos de la venta --}}
    <table class="datos-venta">
        <tr>
            <td class="label">FECHA:</td>
            <td>{{ $venta->created_at->format('d/m/Y H:i:s') }}</td>
        </tr>
        <tr>
            <td class="label">USUARIO:</td>
            <td>{{ strtoupper($venta->user->name ?? 'Usuario') }}</td>
        </tr>
        <tr>
            <td class="label">CLIENTE:</td>
            <td>{{ $venta->cliente->nombre ?? 'Consumidor Final' }}</td>
        </tr>
    </table>

    <hr class="linea">
    <div class="center bold" style="letter-spacing: 3px; margin: 2px 0;">D E T A L L E</div>
    <hr class="linea">

    {{-- Items --}}
    <table class="items-table">
        @foreach($venta->ventaItems as $item)
            @php
                $textoCompleto = $item->cantidad_formateada . ' ' . ($item->producto->nombre ?? 'Producto');
            @endphp
            <tr>
                <td class="producto" data-texto="{{ $textoCompleto }}">{{ $textoCompleto }}</td>
                <td class="precio">{{ number_format($item->subtotal, 2) }}</td>
            </tr>
        @endforeach
    </table>

    <hr class="linea">

    {{-- Totales --}}
    <table class="totales-table">
        <tr class="total-principal">
            <td class="total-label">TOTAL:</td>
            <td class="total-valor">{{ number_format($venta->efectivo + $venta->online + $venta->credito, 2) }}</td>
        </tr>
        @if($venta->efectivo > 0)
            <tr>
                <td class="total-label">EFECTIVO:</td>
                <td class="total-valor">{{ number_format($venta->efectivo, 2) }}</td>
            </tr>
        @endif
        @if($venta->online > 0)
            <tr>
                <td class="total-label">ONLINE:</td>
                <td class="total-valor">{{ number_format($venta->online, 2) }}</td>
            </tr>
        @endif
        @if($venta->credito > 0)
            <tr>
                <td class="total-label">CRÉDITO:</td>
                <td class="total-valor">{{ number_format($venta->credito, 2) }}</td>
            </tr>
        @endif
        @if($venta->cambio > 0)
            <tr>
                <td class="total-label">CAMBIO:</td>
                <td class="total-valor">{{ number_format($venta->cambio, 2) }}</td>
            </tr>
        @endif
    </table>

    <hr class="linea-doble">
    <div class="mensaje-final">GRACIAS POR SU COMPRA</div>

    @if($config->propietario_celular)
        <div class="center" style="font-size: 10px; margin-top: 2px;">
            CEL: {{ $config->propietario_celular }}
        </div>
    @endif

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

        // Auto-imprimir al cargar la página (solo en escritorio)
        if (!isMobile) {
            window.addEventListener('load', function() {
                setTimeout(function() {
                    window.print();
                }, 300);
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
                const ventaId = window.location.pathname.split('/')[3];
                window.open(`/ticket/venta/${ventaId}/pdf`, '_blank');
            } else {
                // En escritorio: Intentar usar la API de compartir si está disponible
                if (navigator.share) {
                    navigator.share({
                        title: document.title,
                        url: window.location.href
                    }).catch(err => console.log('Error al compartir:', err));
                } else {
                    // Fallback: Abrir PDF
                    const ventaId = window.location.pathname.split('/')[3];
                    window.open(`/ticket/venta/${ventaId}/pdf`, '_blank');
                }
            }
        }
    </script>
</body>
</html>
