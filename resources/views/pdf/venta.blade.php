<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Venta #{{ $venta->numero_folio }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 20px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            color: #333;
        }
        .info-section {
            margin-bottom: 20px;
            display: table;
            width: 100%;
        }
        .info-row {
            display: table-row;
        }
        .info-label {
            display: table-cell;
            font-weight: bold;
            width: 150px;
            padding: 5px 0;
        }
        .info-value {
            display: table-cell;
            padding: 5px 0;
        }
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
        }
        .badge-success {
            background-color: #28a745;
            color: white;
        }
        .badge-warning {
            background-color: #ffc107;
            color: #000;
        }
        .badge-danger {
            background-color: #dc3545;
            color: white;
        }
        .badge-info {
            background-color: #17a2b8;
            color: white;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table thead {
            background-color: #f8f9fa;
        }
        table th, table td {
            border: 1px solid #dee2e6;
            padding: 8px;
            text-align: left;
        }
        table th {
            font-weight: bold;
        }
        table tfoot {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .text-primary {
            color: #007bff;
        }
        .text-success {
            color: #28a745;
        }
        .text-info {
            color: #17a2b8;
        }
        .text-danger {
            color: #dc3545;
        }
        .footer {
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #dee2e6;
            font-size: 10px;
            color: #6c757d;
            text-align: center;
        }
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 120px;
            font-weight: bold;
            color: rgba(220, 53, 69, 0.15);
            z-index: -1;
            white-space: nowrap;
            pointer-events: none;
        }
    </style>
</head>
<body>
    @if($venta->estado === 'Eliminado')
        <div class="watermark">CANCELADA</div>
    @endif
    <div class="header">
        <h1>VENTA #{{ $venta->numero_folio }}</h1>
        <p style="margin: 5px 0;">{{ config('app.name', 'TPV') }}</p>
    </div>

    <div class="info-section">
        <div class="info-row">
            <div class="info-label">Estado:</div>
            <div class="info-value">
                <span class="badge {{ $venta->estado === 'Completo' ? 'badge-success' : ($venta->estado === 'Eliminado' ? 'badge-danger' : ($venta->estado === 'Pendiente' ? 'badge-warning' : 'badge-info')) }}">
                    {{ $venta->estado }}
                </span>
            </div>
        </div>
        <div class="info-row">
            <div class="info-label">Cliente:</div>
            <div class="info-value">{{ $venta->cliente->nombre ?? 'Sin cliente' }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Usuario:</div>
            <div class="info-value">{{ $venta->user->name ?? 'Usuario' }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Fecha:</div>
            <div class="info-value">{{ $venta->created_at->format('d/m/Y H:i') }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Producto</th>
                <th class="text-center" width="40">Cantidad</th>
                <th class="text-right" width="60">Precio</th>
                <th class="text-right" width="80">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($venta->ventaItems as $item)
                <tr>
                    <td>{{ $item->producto->nombre ?? 'Producto' }}</td>
                    <td class="text-center">{{ $item->cantidad_formateada }}</td>
                    <td class="text-right">Bs. {{ number_format($item->precio, 2) }}</td>
                    <td class="text-right">Bs. {{ number_format($item->subtotal, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" class="text-right">Total:</td>
                <td class="text-right text-primary">
                    Bs. {{ number_format($venta->efectivo + $venta->online + $venta->credito, 2) }}
                </td>
            </tr>
            @if ($venta->efectivo > 0)
                <tr>
                    <td colspan="3" class="text-right">Efectivo:</td>
                    <td class="text-right text-success">Bs. {{ number_format($venta->efectivo, 2) }}</td>
                </tr>
            @endif
            @if ($venta->online > 0)
                <tr>
                    <td colspan="3" class="text-right">Online:</td>
                    <td class="text-right text-info">Bs. {{ number_format($venta->online, 2) }}</td>
                </tr>
            @endif
            @if ($venta->credito > 0)
                <tr>
                    <td colspan="3" class="text-right">Crédito:</td>
                    <td class="text-right text-danger">Bs. {{ number_format($venta->credito, 2) }}</td>
                </tr>
            @endif
        </tfoot>
    </table>

    <div class="footer">
        <p>Generado el {{ now()->format('d/m/Y H:i:s') }} | MiSocio - Sistema de Gestión</p>
    </div>
</body>
</html>
