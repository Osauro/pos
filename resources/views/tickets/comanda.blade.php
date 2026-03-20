<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Comanda #{{ $venta->numero_venta }}</title>
    <style>
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
            font-family: 'Courier New', Courier, monospace;
            font-size: {{ $width === 58 ? '10pt' : '11pt' }};
            margin: 0;
            padding: 1mm 2mm 4mm;
            width: 100%;
            color: #000;
            background: #fff;
        }

        .venta-num {
            font-size: {{ $width === 58 ? '16pt' : '20pt' }};
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
            font-size: {{ $width === 58 ? '13pt' : '16pt' }};
            font-weight: bold;
            line-height: 1.3;
            flex: 1;
        }

        .item-detalle {
            font-size: {{ $width === 58 ? '13pt' : '16pt' }};
            font-weight: bold;
            white-space: nowrap;
        }
    </style>
</head>
<body>

    <div class="venta-num">VENTA #{{ $venta->numero_venta }}</div>

    @forelse($items as $item)
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
        <div class="item">
            <span class="item-nombre">{{ $item->cantidad }} {{ $nombreCorto }}</span>
            @if($fid > 0 || $mix > 0)
                    <span class="item-detalle">{{ $arr > 0 ? 'A:'.$arr.' ' : '' }}{{ $fid > 0 ? 'F:'.$fid.' ' : '' }}{{ $mix > 0 ? 'M:'.$mix : '' }}</span>
            @endif
        </div>
    @empty
        <div style="margin-top:4mm">Sin ítems</div>
    @endforelse

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
