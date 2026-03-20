<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Inventario #{{ str_pad($inventario->numero_folio, 6, '0', STR_PAD_LEFT) }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; margin: 20px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .header h1 { margin: 0; font-size: 20px; }
        .header p { margin: 4px 0 0; font-size: 12px; }
        .info-grid { display: table; width: 100%; margin-bottom: 16px; }
        .info-row  { display: table-row; }
        .info-lbl  { display: table-cell; font-weight: bold; width: 130px; padding: 3px 0; }
        .info-val  { display: table-cell; padding: 3px 0; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        thead { background-color: #343a40; color: #fff; }
        th, td { border: 1px solid #dee2e6; padding: 5px 7px; }
        th { font-weight: bold; }
        .text-right  { text-align: right; }
        .text-center { text-align: center; }
        .text-success { color: #198754; }
        .text-danger  { color: #dc3545; }
        .text-muted   { color: #6c757d; }
        .summary { display: table; width: 100%; margin-top: 10px; }
        .sum-row { display: table-row; }
        .sum-cell { display: table-cell; padding: 4px 10px; border: 1px solid #dee2e6; text-align: center; width: 25%; background: #f8f9fa; }
        .sum-cell strong { display: block; font-size: 14px; }
        .footer { margin-top: 24px; border-top: 1px solid #dee2e6; padding-top: 8px; font-size: 9px; color: #6c757d; text-align: center; }
    </style>
</head>
<body>

<div class="header">
    <h1>INVENTARIO #{{ str_pad($inventario->numero_folio, 6, '0', STR_PAD_LEFT) }}</h1>
    <p>{{ config('app.name', 'TPV') }}</p>
</div>

<div class="info-grid">
    <div class="info-row">
        <div class="info-lbl">Estado:</div>
        <div class="info-val">{{ $inventario->estado }}</div>
    </div>
    <div class="info-row">
        <div class="info-lbl">Responsable:</div>
        <div class="info-val">{{ $inventario->user->name ?? 'N/A' }}</div>
    </div>
    <div class="info-row">
        <div class="info-lbl">Fecha:</div>
        <div class="info-val">{{ $inventario->created_at->format('d/m/Y H:i') }}</div>
    </div>
</div>

@php
    $sobra = 0; $sobrante = 0.0;
    $falta = 0; $faltante = 0.0;
@endphp

<table>
    <colgroup>
        <col>
        <col width="60">
        <col width="60">
        <col width="60">
        <col width="70">
        <col width="80">
    </colgroup>
    <thead>
        <tr>
            <th>Producto</th>
            <th class="text-center">Anterior</th>
            <th class="text-center">Diferencia</th>
            <th class="text-center">Actual</th>
            <th class="text-right">Precio</th>
            <th class="text-right">Total</th>
        </tr>
    </thead>
    <tbody>
        @foreach($inventario->items as $item)
            @php
                $prod     = $item->producto;
                $cantidad = $prod ? ($prod->cantidad ?? 1) : 1;
                $medAbrev = $prod ? strtolower(substr($prod->medida ?? 'u', 0, 1)) : 'u';

                $formatStock = function ($stock) use ($cantidad, $medAbrev) {
                    if ($cantidad > 1) {
                        $ent = intdiv($stock, $cantidad);
                        $uni = $stock % $cantidad;
                        if ($ent > 0 && $uni > 0) return $ent . $medAbrev . '-' . $uni . 'u';
                        if ($ent > 0)             return $ent . $medAbrev;
                        return $uni . 'u';
                    }
                    return $stock . $medAbrev;
                };

                $absDif = abs($item->diferencia);
                if ($item->diferencia == 0) {
                    $difDisplay = '=';
                } elseif ($cantidad > 1) {
                    $ent = intdiv($absDif, $cantidad);
                    $uni = $absDif % $cantidad;
                    if ($ent > 0 && $uni > 0) $difDisplay = ($item->diferencia > 0 ? '+' : '-') . $ent . $medAbrev . '-' . $uni . 'u';
                    elseif ($ent > 0)         $difDisplay = ($item->diferencia > 0 ? '+' : '-') . $ent . $medAbrev;
                    else                      $difDisplay = ($item->diferencia > 0 ? '+' : '-') . $uni . 'u';
                } else {
                    $difDisplay = ($item->diferencia > 0 ? '+' : '') . $item->diferencia . $medAbrev;
                }

                $precio = 0;
                $total  = 0;
                if ($item->diferencia != 0 && $prod) {
                    $precio    = $item->diferencia > 0 ? (float)($prod->precio_de_compra ?? 0) : (float)($prod->precio_por_mayor ?? 0);
                    $unidades  = $cantidad > 1 ? ($absDif / $cantidad) : $absDif;
                    $total     = $unidades * $precio;
                    if ($item->diferencia < 0) $total = -$total;
                    if ($item->diferencia > 0) { $sobra++; $sobrante += abs($total); }
                    else                       { $falta++; $faltante += abs($total); }
                }
            @endphp
            <tr>
                <td>{{ $prod->nombre ?? 'Producto eliminado' }}</td>
                <td class="text-center">{{ $formatStock($item->stock_sistema) }}</td>
                <td class="text-center {{ $item->diferencia > 0 ? 'text-success' : ($item->diferencia < 0 ? 'text-danger' : 'text-muted') }}">
                    {{ $difDisplay }}
                </td>
                <td class="text-center">{{ $formatStock($item->stock_contado) }}</td>
                <td class="text-right">
                    @if($item->diferencia != 0) {{ number_format($precio, 2) }} @else — @endif
                </td>
                <td class="text-right {{ $item->diferencia > 0 ? 'text-success' : ($item->diferencia < 0 ? 'text-danger' : '') }}">
                    @if($item->diferencia != 0) {{ number_format(abs($total), 2) }} @else — @endif
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

@php $totalNeto = $sobrante - $faltante; @endphp

<div class="summary">
    <div class="sum-row">
        <div class="sum-cell">
            <span class="text-muted">Productos sobrantes</span>
            <strong class="text-success">{{ $sobra }}</strong>
        </div>
        <div class="sum-cell">
            <span class="text-muted">Total sobrante</span>
            <strong class="text-success">Bs. {{ number_format($sobrante, 2) }}</strong>
        </div>
        <div class="sum-cell">
            <span class="text-muted">Productos faltantes</span>
            <strong class="text-danger">{{ $falta }}</strong>
        </div>
        <div class="sum-cell">
            <span class="text-muted">Total faltante</span>
            <strong class="text-danger">Bs. {{ number_format($faltante, 2) }}</strong>
        </div>
    </div>
    <div class="sum-row" style="margin-top:6px;">
        <div class="sum-cell" style="width:100%; border:2px solid {{ $totalNeto >= 0 ? '#198754' : '#dc3545' }}; background: {{ $totalNeto >= 0 ? '#d1e7dd' : '#f8d7da' }};" colspan="4">
            <span style="color:#333;">Ajuste neto total</span>
            <strong style="font-size:16px; color:{{ $totalNeto >= 0 ? '#198754' : '#dc3545' }};">
                Bs. {{ number_format($totalNeto, 2) }}
            </strong>
        </div>
    </div>
</div>

<div class="footer">
    Generado el {{ now()->format('d/m/Y H:i') }} &mdash; {{ $config->nombre_negocio ?? 'MiSocio' }}
</div>

</body>
</html>
