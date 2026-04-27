<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Reporte de Turno</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 9pt;
            color: #1a1a2e;
            background: #fff;
            padding: 0;
        }

        /* ── PORTADA / HEADER ─────────────────────────────── */
        .header {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 60%, #0f3460 100%);
            color: #fff;
            padding: 22pt 28pt 18pt;
            margin-bottom: 0;
        }
        .header-top {
            display: table;
            width: 100%;
            margin-bottom: 14pt;
        }
        .header-logo-cell {
            display: table-cell;
            vertical-align: middle;
            width: 60pt;
        }
        .header-logo {
            width: 50pt;
            height: 50pt;
            background: rgba(255,255,255,0.12);
            border-radius: 50%;
            text-align: center;
            line-height: 50pt;
            font-size: 22pt;
            font-weight: bold;
            color: #e94560;
            border: 2pt solid rgba(233,69,96,0.5);
        }
        .header-title-cell {
            display: table-cell;
            vertical-align: middle;
        }
        .header-negocio {
            font-size: 18pt;
            font-weight: bold;
            letter-spacing: 1pt;
            color: #fff;
            line-height: 1.2;
        }
        .header-subtitle {
            font-size: 10pt;
            color: rgba(255,255,255,0.65);
            margin-top: 2pt;
        }
        .header-badge-cell {
            display: table-cell;
            vertical-align: middle;
            text-align: right;
            width: 80pt;
        }
        .badge-estado {
            display: inline-block;
            padding: 4pt 10pt;
            border-radius: 20pt;
            font-size: 8pt;
            font-weight: bold;
            letter-spacing: 0.5pt;
            text-transform: uppercase;
        }
        .badge-activo   { background: #00b894; color: #fff; }
        .badge-finalizado { background: #636e72; color: #fff; }

        .header-info-row {
            display: table;
            width: 100%;
            border-top: 1pt solid rgba(255,255,255,0.15);
            padding-top: 12pt;
        }
        .header-info-cell {
            display: table-cell;
            vertical-align: top;
            width: 33.33%;
            padding-right: 10pt;
        }
        .info-label {
            font-size: 7pt;
            text-transform: uppercase;
            letter-spacing: 0.8pt;
            color: rgba(255,255,255,0.5);
            margin-bottom: 3pt;
        }
        .info-value {
            font-size: 10pt;
            font-weight: bold;
            color: #fff;
        }
        .info-value-sm {
            font-size: 8.5pt;
            color: rgba(255,255,255,0.85);
        }

        /* ── RESUMEN KPIs ─────────────────────────────────── */
        .kpi-row {
            display: table;
            width: 100%;
            background: #f8f9fa;
            border-bottom: 2pt solid #e9ecef;
        }
        .kpi-cell {
            display: table-cell;
            text-align: center;
            padding: 12pt 6pt;
            border-right: 1pt solid #dee2e6;
            width: 20%;
        }
        .kpi-cell:last-child { border-right: none; }
        .kpi-num {
            font-size: 15pt;
            font-weight: bold;
            color: #0f3460;
            line-height: 1;
        }
        .kpi-label {
            font-size: 7pt;
            text-transform: uppercase;
            color: #6c757d;
            letter-spacing: 0.5pt;
            margin-top: 3pt;
        }
        .kpi-green  { color: #00b894; }
        .kpi-blue   { color: #0984e3; }
        .kpi-orange { color: #e17055; }
        .kpi-purple { color: #6c5ce7; }

        /* ── CONTENT ──────────────────────────────────────── */
        .content { padding: 14pt 24pt; }

        /* ── SECTION TITLE ────────────────────────────────── */
        .section-title {
            font-size: 10pt;
            font-weight: bold;
            color: #0f3460;
            padding: 6pt 10pt;
            background: #eef2ff;
            border-left: 3pt solid #0f3460;
            margin-bottom: 8pt;
            margin-top: 14pt;
            letter-spacing: 0.3pt;
        }
        .section-title:first-child { margin-top: 0; }

        /* ── TABLES ───────────────────────────────────────── */
        table.data {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8pt;
            font-size: 8pt;
        }
        table.data thead tr {
            background: #0f3460;
            color: #fff;
        }
        table.data thead th {
            padding: 5pt 7pt;
            text-align: left;
            font-size: 7.5pt;
            font-weight: bold;
            letter-spacing: 0.3pt;
        }
        table.data thead th.right { text-align: right; }
        table.data thead th.center { text-align: center; }

        table.data tbody tr:nth-child(even) { background: #f8f9fa; }
        table.data tbody tr:nth-child(odd)  { background: #fff; }
        table.data tbody td {
            padding: 4.5pt 7pt;
            border-bottom: 0.5pt solid #e9ecef;
            vertical-align: top;
        }
        table.data tbody td.right  { text-align: right; }
        table.data tbody td.center { text-align: center; }
        table.data tbody td.muted  { color: #6c757d; font-size: 7.5pt; }

        table.data tfoot tr { background: #eef2ff; font-weight: bold; }
        table.data tfoot td {
            padding: 5pt 7pt;
            border-top: 1pt solid #0f3460;
            font-size: 8.5pt;
        }
        table.data tfoot td.right { text-align: right; }

        /* ── FORMA DE PAGO BADGES ─────────────────────────── */
        .fp { display: inline-block; padding: 1pt 5pt; border-radius: 10pt; font-size: 6.5pt; font-weight: bold; margin-right: 2pt; }
        .fp-ef  { background: #d1fae5; color: #065f46; }
        .fp-on  { background: #dbeafe; color: #1e3a8a; }
        .fp-cr  { background: #fce7f3; color: #831843; }

        /* ── TOP PRODUCTOS ────────────────────────────────── */
        .rank-num {
            display: inline-block;
            width: 14pt;
            height: 14pt;
            line-height: 14pt;
            text-align: center;
            border-radius: 50%;
            background: #0f3460;
            color: #fff;
            font-size: 7pt;
            font-weight: bold;
        }
        .rank-num.gold   { background: #f39c12; }
        .rank-num.silver { background: #95a5a6; }
        .rank-num.bronze { background: #ca6f1e; }

        /* ── BAR (movimientos) ────────────────────────────── */
        .ing { color: #00b894; font-weight: bold; }
        .eg  { color: #d63031; font-weight: bold; }
        .saldo-val { color: #0984e3; font-weight: bold; }

        /* ── FOOTER ───────────────────────────────────────── */
        .footer {
            margin-top: 18pt;
            padding-top: 8pt;
            border-top: 1pt solid #dee2e6;
            text-align: center;
            color: #adb5bd;
            font-size: 7pt;
        }

        /* ── CANCELADAS NOTE ──────────────────────────────── */
        .note {
            font-size: 7pt;
            color: #adb5bd;
            font-style: italic;
            margin-bottom: 4pt;
        }
    </style>
</head>
<body>

{{-- ═══════════════════════════════════ HEADER ═══════════════════════════════════ --}}
<div class="header">
    <div class="header-top">
        <div class="header-logo-cell">
            <div class="header-logo">{{ strtoupper(substr($negocio, 0, 1)) }}</div>
        </div>
        <div class="header-title-cell">
            <div class="header-negocio">{{ $negocio }}</div>
            <div class="header-subtitle">Reporte de Turno &mdash; Generado el {{ now()->format('d/m/Y H:i') }}</div>
        </div>
        <div class="header-badge-cell">
            @php $esActivo = $turno->estadoReal === 'activo'; @endphp
            <span class="badge-estado {{ $esActivo ? 'badge-activo' : 'badge-finalizado' }}">
                {{ $esActivo ? 'Activo' : 'Finalizado' }}
            </span>
        </div>
    </div>

    <div class="header-info-row">
        <div class="header-info-cell">
            <div class="info-label">Encargado</div>
            <div class="info-value">{{ $turno->encargado->nombre }}</div>
        </div>
        <div class="header-info-cell">
            <div class="info-label">Período</div>
            <div class="info-value">
                {{ $turno->fecha_inicio->format('d/m/Y') }}
                &nbsp;&rarr;&nbsp;
                {{ $turno->fecha_fin->format('d/m/Y') }}
            </div>
            <div class="info-value-sm">
                Semana {{ $turno->fecha_inicio->isoWeek }} &bull;
                {{ $turno->fecha_inicio->diffInDays($turno->fecha_fin) + 1 }} días
            </div>
        </div>
        <div class="header-info-cell">
            <div class="info-label">ID de Turno</div>
            <div class="info-value">#{{ str_pad($turno->id, 5, '0', STR_PAD_LEFT) }}</div>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════ KPIs ═══════════════════════════════════ --}}
<div class="kpi-row">
    <div class="kpi-cell">
        <div class="kpi-num kpi-green">${{ number_format($totalVentas, 2) }}</div>
        <div class="kpi-label">Total Ventas</div>
    </div>
    <div class="kpi-cell">
        <div class="kpi-num">{{ $cantidadVentas }}</div>
        <div class="kpi-label">N.º Ventas</div>
    </div>
    <div class="kpi-cell">
        <div class="kpi-num kpi-blue">${{ number_format($totalEfectivo, 2) }}</div>
        <div class="kpi-label">Efectivo</div>
    </div>
    <div class="kpi-cell">
        <div class="kpi-num kpi-orange">${{ number_format($totalIngresos - $totalEgresos, 2) }}</div>
        <div class="kpi-label">Balance Caja</div>
    </div>
    <div class="kpi-cell">
        <div class="kpi-num kpi-purple">${{ number_format($cantidadVentas > 0 ? $totalVentas / $cantidadVentas : 0, 2) }}</div>
        <div class="kpi-label">Ticket Medio</div>
    </div>
</div>

<div class="content">

{{-- ═══════════════════════════════════ VENTAS ════════════════════════════════ --}}
<div class="section-title">&#128722; Detalle de Ventas</div>
<p class="note">* Las ventas canceladas no se incluyen en los totales.</p>

@if($ventas->isEmpty())
    <p style="color:#6c757d; font-size:8.5pt; margin-bottom:10pt;">No hay ventas registradas en este turno.</p>
@else
<table class="data">
    <thead>
        <tr>
            <th style="width:40pt">#&nbsp;Venta</th>
            <th style="width:62pt">Fecha/Hora</th>
            <th>Productos</th>
            <th class="right" style="width:55pt">Total</th>
            <th style="width:90pt">Forma de pago</th>
        </tr>
    </thead>
    <tbody>
        @foreach($ventas as $venta)
        <tr>
            <td class="center">
                <strong>{{ str_pad($venta->numero_venta, 4, '0', STR_PAD_LEFT) }}</strong>
            </td>
            <td class="muted">{{ \Carbon\Carbon::parse($venta->fecha_hora)->format('d/m H:i') }}</td>
            <td>
                @foreach($venta->items as $item)
                    <span style="display:block; font-size:7.5pt;">
                        {{ $item->cantidad }}&times; {{ $item->producto?->nombre ?? '–' }}
                    </span>
                @endforeach
            </td>
            <td class="right"><strong>${{ number_format($venta->total, 2) }}</strong></td>
            <td>
                @if($venta->efectivo > 0)
                    <span class="fp fp-ef">Efec ${{ number_format($venta->efectivo, 2) }}</span>
                @endif
                @if($venta->online > 0)
                    <span class="fp fp-on">Online ${{ number_format($venta->online, 2) }}</span>
                @endif
                @if($venta->credito > 0)
                    <span class="fp fp-cr">Créd ${{ number_format($venta->credito, 2) }}</span>
                @endif
            </td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="3" class="right">TOTALES</td>
            <td class="right">${{ number_format($totalVentas, 2) }}</td>
            <td>
                <span class="fp fp-ef">Efec ${{ number_format($totalEfectivo, 2) }}</span>
                @if($totalOnline > 0)
                    <span class="fp fp-on">Online ${{ number_format($totalOnline, 2) }}</span>
                @endif
                @if($totalCredito > 0)
                    <span class="fp fp-cr">Créd ${{ number_format($totalCredito, 2) }}</span>
                @endif
            </td>
        </tr>
    </tfoot>
</table>
@endif

{{-- ═══════════════════════════════════ TOP PRODUCTOS ═════════════════════════ --}}
@if($topProductos->isNotEmpty())
<div class="section-title">&#127942; Top Productos del Turno</div>
<table class="data">
    <thead>
        <tr>
            <th style="width:20pt" class="center">#</th>
            <th>Producto</th>
            <th class="right" style="width:55pt">Unidades</th>
            <th class="right" style="width:70pt">Importe</th>
        </tr>
    </thead>
    <tbody>
        @foreach($topProductos as $i => $prod)
        <tr>
            <td class="center">
                @php
                    $cls = $i === 0 ? 'gold' : ($i === 1 ? 'silver' : ($i === 2 ? 'bronze' : ''));
                @endphp
                <span class="rank-num {{ $cls }}">{{ $i + 1 }}</span>
            </td>
            <td>{{ $prod->nombre }}</td>
            <td class="right">{{ $prod->total_uds }}</td>
            <td class="right">${{ number_format($prod->total_importe, 2) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif

{{-- ═══════════════════════════════════ MOVIMIENTOS ═══════════════════════════ --}}
<div class="section-title">&#128200; Movimientos de Caja</div>

@if($movimientos->isEmpty())
    <p style="color:#6c757d; font-size:8.5pt; margin-bottom:10pt;">No hay movimientos registrados en este turno.</p>
@else
<table class="data">
    <thead>
        <tr>
            <th style="width:65pt">Fecha/Hora</th>
            <th>Detalle</th>
            <th style="width:35pt">Usuario</th>
            <th class="right" style="width:60pt">Ingreso</th>
            <th class="right" style="width:60pt">Egreso</th>
            <th class="right" style="width:65pt">Saldo</th>
        </tr>
    </thead>
    <tbody>
        @foreach($movimientos as $mov)
        <tr>
            <td class="muted">{{ $mov->created_at->format('d/m H:i') }}</td>
            <td>{{ $mov->detalle }}</td>
            <td class="muted">{{ $mov->usuario?->nombre ?? '–' }}</td>
            <td class="right">
                @if($mov->ingreso > 0)
                    <span class="ing">${{ number_format($mov->ingreso, 2) }}</span>
                @else
                    <span class="muted">–</span>
                @endif
            </td>
            <td class="right">
                @if($mov->egreso > 0)
                    <span class="eg">${{ number_format($mov->egreso, 2) }}</span>
                @else
                    <span class="muted">–</span>
                @endif
            </td>
            <td class="right"><span class="saldo-val">${{ number_format($mov->saldo, 2) }}</span></td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="3" class="right">TOTALES</td>
            <td class="right ing">${{ number_format($totalIngresos, 2) }}</td>
            <td class="right eg">${{ number_format($totalEgresos, 2) }}</td>
            <td class="right saldo-val">${{ number_format($saldoFinal, 2) }}</td>
        </tr>
    </tfoot>
</table>
@endif

{{-- ═══════════════════════════════════ FOOTER ════════════════════════════════ --}}
<div class="footer">
    {{ $negocio }} &bull; Reporte generado el {{ now()->format('d/m/Y \a \l\a\s H:i') }} &bull; Sistema POS
</div>

</div>{{-- /content --}}
</body>
</html>
