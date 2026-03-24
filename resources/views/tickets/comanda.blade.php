<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title>Comanda #{{ $venta->numero_venta }}</title>
    <style>
        @font-face {
            font-family: 'FuenteA';
            font-weight: 400;
            src: url('/fonts/courier-prime-regular.woff2') format('woff2');
        }
        @font-face {
            font-family: 'FuenteA';
            font-weight: 700;
            src: url('/fonts/courier-prime-bold.woff2') format('woff2');
        }

        @php
            $pageWidth = $width === 58 ? '58mm' : '80mm';
        @endphp

        @media print {
            @page {
                size: {{ $pageWidth }} auto;
                margin: 2mm 2mm 0;
            }
        }

        * { box-sizing: border-box; }

        body {
            font-family: 'FuenteA', 'Courier New', monospace;
            font-size: {{ $width === 58 ? '9pt' : '9pt' }};
            margin: 0;
            padding: 1mm 2mm 4mm;
            width: 100%;
            color: #000;
            background: #fff;
        }

        .venta-num {
            font-size: {{ $width === 58 ? '14pt' : '16pt' }};
            font-weight: bold;
            text-align: center;
            margin-bottom: 1mm;
        }

        .item {
            margin: 1mm 0;
            page-break-inside: avoid;
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            gap: 2mm;
        }

        .item-nombre {
            font-size: {{ $width === 58 ? '11pt' : '13pt' }};
            font-weight: bold;
            line-height: 1.3;
            flex: 1;
        }

        .item-detalle {
            font-size: {{ $width === 58 ? '11pt' : '13pt' }};
            font-weight: bold;
            white-space: nowrap;
        }
    </style>
</head>
<body>

    <div class="venta-num">VENTA #{{ $venta->numero_venta }}</div>

    @forelse($items as $item)
        @php
            $arr = 0; $fid = 0; $mix = 0;
            if ($item->producto->tipo === 'Platos' && !empty($item->detalle)) {
                $arr = $item->detalle['arroz'] ?? 0;
                $fid = $item->detalle['fideo'] ?? 0;
                $mix = $item->detalle['mixto'] ?? 0;
            }
            $partes = [];
            if ($arr > 0) $partes[] = "{$arr}A";
            if ($fid > 0) $partes[] = "{$fid}F";
            if ($mix > 0) $partes[] = "{$mix}M";
            $detalle = implode(' - ', $partes);

            $pos  = strpos($item->producto->nombre, ' ');
            $cad1 = $pos !== false ? substr($item->producto->nombre, 0, $pos) : $item->producto->nombre;
            $cad2 = $pos !== false ? trim(substr($item->producto->nombre, $pos + 1)) : '';
            if ($item->cantidad > 1) {
                $ult   = mb_strtolower(mb_substr($cad1, -1));
                $cad1  = $ult === 'z'
                    ? mb_substr($cad1, 0, -1) . 'ces'
                    : $cad1 . (in_array($ult, ['a','e','i','o','u']) ? 's' : 'es');
            }
            $sufijo      = strcasecmp($cad2, 'sin huevo') === 0 ? ' S/H' : '';
            $nombreCorto = $cad1 . $sufijo;
        @endphp
        <div class="item">
            <span class="item-nombre">{{ $item->cantidad }} {{ $nombreCorto }}</span>
            @if($detalle)
                <span class="item-detalle">{{ $detalle }}</span>
            @endif
        </div>
    @empty
        <div style="margin-top:4mm">Sin ítems</div>
    @endforelse

    @if(isset($porciones) && $porciones->count() > 0)
        <div style="text-align:center;font-weight:bold;margin:3mm 0 1mm">----- P O R C I O N E S -----</div>
        @foreach($porciones as $item)
            <div class="item">
                <span class="item-nombre">{{ $item->cantidad }} {{ $item->producto->nombre }}</span>
            </div>
        @endforeach
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

            // Auto-print: espera a que las fuentes estén cargadas
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
