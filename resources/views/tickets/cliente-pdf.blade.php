<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    @php
        $cols  = $width === 58 ? 24 : 32;
        $sep   = str_repeat('=', $cols);
        $dash  = str_repeat('-', $cols);
        $bSize = $width === 58 ? '9pt'  : '11pt';
        $hSize = $width === 58 ? '13pt' : '15pt';
        $vSize = $width === 58 ? '11pt' : '12pt';
        $tSize = $width === 58 ? '13pt' : '14pt';
        $iSize = $width === 58 ? '8pt'  : '10pt';
    @endphp
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: Courier, "Courier New", monospace;
            font-size: {{ $bSize }};
            color: #000;
            background: #fff;
            padding: 3pt 3pt 6pt;
            width: 100%;
        }

        .center { text-align: center; }
        .right  { text-align: right; }
        .bold   { font-weight: bold; }

        .negocio {
            font-size: {{ $hSize }};
            font-weight: bold;
            text-align: center;
            letter-spacing: 2px;
            margin-bottom: 2pt;
        }

        .venta-num {
            font-size: {{ $vSize }};
            font-weight: bold;
            text-align: center;
            margin-bottom: 2pt;
        }

        .sep {
            text-align: center;
            font-size: 7pt;
            margin: 2pt 0;
            overflow: hidden;
            white-space: nowrap;
        }

        .detalle-titulo {
            font-size: {{ $iSize }};
            font-weight: bold;
            text-align: center;
            letter-spacing: 2px;
            margin: 3pt 0;
        }

        .info-table {
            width: 100%;
            font-size: {{ $iSize }};
            margin-bottom: 1pt;
            border-collapse: collapse;
        }

        .items-table {
            width: 100%;
            font-size: {{ $iSize }};
            border-collapse: collapse;
            margin: 2pt 0;
        }

        .items-table td {
            vertical-align: top;
            padding-bottom: 1pt;
        }

        .total-table {
            width: 100%;
            font-size: {{ $tSize }};
            font-weight: bold;
            border-collapse: collapse;
            margin: 3pt 0;
        }

        .gracias {
            font-size: {{ $bSize }};
            font-weight: bold;
            text-align: center;
            margin: 4pt 0 2pt;
        }

        .encargado {
            font-size: 8pt;
            text-align: center;
            margin-top: 2pt;
            line-height: 1.5;
        }
    </style>
</head>
<body>

    <div class="negocio">{{ strtoupper($negocio) }}</div>

    <div class="sep">{{ $sep }}</div>

    <div class="venta-num">VENTA: {{ $venta->numero_venta }}</div>

    <table class="info-table">
        <tr>
            <td>Fecha:</td>
            <td class="right">{{ $venta->fecha_hora?->format('d/m/Y') ?? now()->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <td>Hora:</td>
            <td class="right">{{ $venta->fecha_hora?->format('H:i') ?? now()->format('H:i') }}</td>
        </tr>
        @if($venta->usuario)
        <tr>
            <td>Cajero:</td>
            <td class="right">{{ $venta->usuario->nombre }}</td>
        </tr>
        @endif
    </table>

    <div class="sep">{{ $sep }}</div>
    <div class="detalle-titulo">- D E T A L L E -</div>
    <div class="sep">{{ $dash }}</div>

    <table class="items-table">
        @foreach($items as $item)
        <tr>
            <td>{{ $item->cantidad }} {{ $item->producto->nombre }}</td>
            <td class="right bold" style="white-space:nowrap;">{{ number_format($item->subtotal, 2) }}</td>
        </tr>
        @endforeach
    </table>

    <div class="sep">{{ $sep }}</div>

    <table class="total-table">
        <tr>
            <td>TOTAL:</td>
            <td class="right">Bs. {{ number_format($venta->total, 2) }}</td>
        </tr>
    </table>

    <div class="sep">{{ $sep }}</div>

    <div class="gracias">GRACIAS POR SU COMPRA</div>

    @if($venta->turno?->encargado)
    <div class="encargado">
        Encargado: {{ $venta->turno->encargado->nombre }}<br>
        @if($venta->turno->encargado->celular)
        Celular: {{ $venta->turno->encargado->celular }}
        @endif
    </div>
    @endif

</body>
</html>
