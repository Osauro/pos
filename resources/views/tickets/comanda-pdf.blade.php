<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    @php
        $bSize = $width === 58 ? '10pt' : '13pt';
        $hSize = $width === 58 ? '15pt' : '18pt';
    @endphp
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: Courier, "Courier New", monospace;
            font-size: {{ $bSize }};
            color: #000;
            background: #fff;
            padding: 3pt;
            width: 100%;
        }

        .venta-num {
            font-size: {{ $hSize }};
            font-weight: bold;
            text-align: center;
            margin-bottom: 4pt;
        }

        .items-table {
            width: 100%;
            font-size: {{ $bSize }};
            font-weight: bold;
            border-collapse: collapse;
        }

        .items-table td {
            vertical-align: top;
            padding-bottom: 3pt;
        }

        .item-detalle {
            white-space: nowrap;
            text-align: right;
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
            $pos  = strpos($item->producto->nombre, ' ');
            $cad1 = $pos !== false ? substr($item->producto->nombre, 0, $pos) : $item->producto->nombre;
            $cad2 = $pos !== false ? trim(substr($item->producto->nombre, $pos + 1)) : '';
            if ($item->cantidad > 1) {
                $ult   = mb_strtolower(mb_substr($cad1, -1));
                $cad1 .= in_array($ult, ['a','e','i','o','u']) ? 's' : 'es';
            }
            $sufijo      = strcasecmp($cad2, 'sin huevo') === 0 ? ' S/H' : '';
            $nombreCorto = strtoupper($cad1 . $sufijo);
            $partes = [];
            if ($arr > 0) $partes[] = 'A:' . $arr;
            if ($fid > 0) $partes[] = 'F:' . $fid;
            if ($mix > 0) $partes[] = 'M:' . $mix;
            $detalleStr = implode(' ', $partes);
        @endphp
        <table class="items-table">
            <tr>
                <td>{{ $item->cantidad }} {{ $nombreCorto }}</td>
                @if($detalleStr)
                <td class="item-detalle">{{ $detalleStr }}</td>
                @endif
            </tr>
        </table>
    @empty
        <p>Sin ítems</p>
    @endforelse

</body>
</html>
