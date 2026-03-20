<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ticket #{{ $venta->numero_venta }}</title>
    <style>
        @php
            $pageWidth = $width === 58 ? '58mm' : '80mm';
            $cols      = $width === 58 ? 28 : 32;
            $sep       = str_repeat('=', $cols);
            $dash      = str_repeat('-', $cols);
        @endphp

        @media print {
            @page {
                size: {{ $pageWidth }} auto;
                margin: 1mm 2mm 0;
            }
        }

        * { box-sizing: border-box; }

        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: {{ $width === 58 ? '10pt' : '11pt' }};
            margin: 0;
            padding: 0 1mm 5mm;
            width: 100%;
            color: #000;
            background: #fff;
        }

        .center { text-align: center; }
        .right   { text-align: right; }
        .bold    { font-weight: bold; }

        .negocio {
            font-size: {{ $width === 58 ? '12pt' : '14pt' }};
            font-weight: bold;
            text-align: center;
            letter-spacing: 1px;
            margin-bottom: 1mm;
        }

        .venta-num {
            font-size: {{ $width === 58 ? '11pt' : '12pt' }};
            font-weight: bold;
            text-align: center;
        }

        .sep-igual {
            text-align: center;
            margin: 2mm 0;
            overflow: hidden;
            white-space: nowrap;
        }

        .sep-dash {
            text-align: center;
            margin: 2mm 0;
            overflow: hidden;
            white-space: nowrap;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5mm;
        }

        .detalle-titulo {
            text-align: center;
            font-weight: bold;
            letter-spacing: 2px;
            margin: 2mm 0;
        }

        .item-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1mm;
        }

        .item-nombre {
            flex: 1;
            padding-right: 2px;
            line-height: 1.3;
        }

        .item-precio {
            white-space: nowrap;
            font-weight: bold;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            font-size: {{ $width === 58 ? '12pt' : '13pt' }};
            font-weight: bold;
            margin: 2mm 0;
        }

        .gracias {
            font-weight: bold;
            text-align: center;
            margin: 3mm 0 1mm;
        }

        /* ── Comanda de cocina (segunda hoja / corte automático) ── */
        .comanda-wrap {
            page-break-before: always;
            padding-top: 0;
        }
        .cmd-titulo {
            font-size: {{ $width === 58 ? '20pt' : '26pt' }};
            font-weight: bold;
            text-align: center;
            letter-spacing: 4px;
            margin-bottom: 1mm;
        }
        .cmd-venta {
            font-size: {{ $width === 58 ? '16pt' : '20pt' }};
            font-weight: bold;
            text-align: center;
            margin-bottom: 2mm;
        }
        .cmd-sep { text-align: center; margin: 2mm 0; }
        .cmd-item {
            margin: 2mm 0;
            page-break-inside: avoid;
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            gap: 2mm;
        }
        .cmd-nombre {
            font-size: {{ $width === 58 ? '13pt' : '16pt' }};
            font-weight: bold;
            line-height: 1.3;
            flex: 1;
        }
        .cmd-detalle {
            font-size: {{ $width === 58 ? '13pt' : '16pt' }};
            font-weight: bold;
            white-space: nowrap;
        }

        .encargado {
            text-align: center;
            margin-top: 1mm;
            line-height: 1.6;
        }
    </style>
</head>
<body>

    <div class="negocio">{{ strtoupper($negocio) }}</div>

    <div class="sep-igual">{{ $sep }}</div>

    <div class="venta-num">VENTA: {{ $venta->numero_venta }}</div>

    <div class="info-row">
        <span>Fecha:</span>
        <span>{{ $venta->fecha_hora?->format('d/m/Y') ?? now()->format('d/m/Y') }}</span>
    </div>
    <div class="info-row">
        <span>Hora:</span>
        <span>{{ $venta->fecha_hora?->format('H:i') ?? now()->format('H:i') }}</span>
    </div>
    @if($venta->usuario)
        <div class="info-row">
            <span>Cajero:</span>
            <span>{{ $venta->usuario->nombre }}</span>
        </div>
    @endif

    <div class="sep-igual">{{ $sep }}</div>
    <div class="detalle-titulo">- D E T A L L E -</div>
    <div class="sep-dash">{{ $dash }}</div>

    @foreach($items as $item)
        <div class="item-row">
            <span class="item-nombre">{{ $item->cantidad }} {{ $item->producto->nombre }}</span>
            <span class="item-precio">{{ number_format($item->subtotal, 2) }}</span>
        </div>
    @endforeach

    <div class="sep-igual">{{ $sep }}</div>

    <div class="total-row">
        <span>TOTAL:</span>
        <span>Bs. {{ number_format($venta->total, 2) }}</span>
    </div>

    <div class="sep-igual">{{ $sep }}</div>

    <div class="gracias">GRACIAS POR SU COMPRA</div>

    @php $encargado = $venta->turno?->encargado; @endphp
    @if($encargado)
        <div class="encargado">
            Encargado: {{ $encargado->nombre }}<br>
            @if($encargado->celular)
            Celular: {{ $encargado->celular }}
            @endif
        </div>
    @endif

    {{-- ══ COMANDA DE COCINA (segunda hoja / corte automático) ══ --}}
    @php
        $comandaItems = $items->filter(fn($i) => $i->producto->tipo !== 'Refrescos')->values();
        $sepCmd = $width === 58 ? str_repeat('-', 24) : str_repeat('-', 32);
    @endphp
    @if(!($soloTicket ?? false) && $comandaItems->count() > 0)
    <div class="comanda-wrap">
        <div class="cmd-venta">VENTA #{{ $venta->numero_venta }}</div>

        @foreach($comandaItems as $item)
            @php
                $arr = 0; $fid = 0; $mix = 0; $tiposUsados = 0;
                if ($item->producto->tipo === 'Platos' && !empty($item->detalle)) {
                    $arr = $item->detalle['arroz'] ?? 0;
                    $fid = $item->detalle['fideo'] ?? 0;
                    $mix = $item->detalle['mixto'] ?? 0;
                    $tiposUsados = ($arr > 0 ? 1 : 0) + ($fid > 0 ? 1 : 0) + ($mix > 0 ? 1 : 0);
                }
                // Nombre corto: split en el primer espacio
                $pos  = strpos($item->producto->nombre, ' ');
                $cad1 = $pos !== false ? substr($item->producto->nombre, 0, $pos) : $item->producto->nombre;
                $cad2 = $pos !== false ? trim(substr($item->producto->nombre, $pos + 1)) : '';
                if ($item->cantidad > 1) {
                    $ult   = mb_strtolower(mb_substr($cad1, -1));
                    $cad1 .= in_array($ult, ['a','e','i','o','u']) ? 's' : 'es';
                }
                $sufijo      = strcasecmp($cad2, 'sin huevo') === 0 ? ' S/H' : '';
                $nombreCorto = strtoupper($cad1 . $sufijo);
            @endphp
            <div class="cmd-item">
                <span class="cmd-nombre">{{ $item->cantidad }} {{ $nombreCorto }}</span>
                @if($fid > 0 || $mix > 0)
                    <span class="cmd-detalle">{{ $arr > 0 ? 'A:'.$arr.' ' : '' }}{{ $fid > 0 ? 'F:'.$fid.' ' : '' }}{{ $mix > 0 ? 'M:'.$mix : '' }}</span>
                @endif
            </div>
        @endforeach
    </div>
    @endif

    <script>
        (function () {
            // Botón de impresión siempre visible como respaldo
            document.addEventListener('DOMContentLoaded', function () {
                var btn = document.createElement('button');
                btn.textContent = '🖨️ Imprimir';
                btn.style.cssText = [
                    'position:fixed','bottom:16px','right:16px','z-index:9999',
                    'padding:12px 24px','background:#29adb2','color:#fff',
                    'border:none','border-radius:8px','font-size:16px',
                    'font-weight:bold','cursor:pointer',
                    'box-shadow:0 2px 8px rgba(0,0,0,.35)'
                ].join(';');
                btn.addEventListener('click', function () { window.print(); });
                window.addEventListener('beforeprint', function () { btn.style.display = 'none'; });
                window.addEventListener('afterprint',  function () { btn.style.display = ''; });
                document.body.appendChild(btn);
            });

            // Auto-print: espera a que las fuentes estén cargadas para evitar
            // que la impresora reciba texto antes de que la tipografía esté lista
            var printed = false;
            function doPrint() {
                if (printed) return;
                printed = true;
                window.print();
            }

            if (document.fonts && document.fonts.ready) {
                document.fonts.ready.then(function () { setTimeout(doPrint, 400); });
            } else {
                window.addEventListener('load', function () { setTimeout(doPrint, 600); });
            }
        })();
    </script>
</body>
</html>
