<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compra #{{ $compra->numero_folio }}</title>
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
    @if($compra->estado === 'Eliminado')
        <div class="watermark">CANCELADA</div>
    @endif
    <div class="header">
        <h1>COMPRA #{{ $compra->numero_folio }}</h1>
        <p style="margin: 5px 0;">{{ config('app.name', 'TPV') }}</p>
    </div>

    <div class="info-section">
        <div class="info-row">
            <div class="info-label">Estado:</div>
            <div class="info-value">
                <span class="badge {{ $compra->estado === 'Completo' ? 'badge-success' : ($compra->estado === 'Eliminado' ? 'badge-danger' : 'badge-warning') }}">
                    {{ $compra->estado }}
                </span>
            </div>
        </div>
        <div class="info-row">
            <div class="info-label">Proveedor:</div>
            <div class="info-value">{{ $compra->proveedor->nombre ?? 'Sin proveedor' }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Usuario:</div>
            <div class="info-value">{{ $compra->user->name ?? 'Usuario' }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Fecha:</div>
            <div class="info-value">{{ $compra->created_at->format('d/m/Y H:i') }}</div>
        </div>
    </div>

    <table>
        <colgroup>
            <col>
            <col width="50">
            <col width="80">
            <col width="110">
        </colgroup>
        <thead>
            <tr>
                <th>Producto</th>
                <th class="text-center" width="40" >Cantidad</th>
                <th class="text-right" width="60" >Precio</th>
                <th class="text-right" width="80" >Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($compra->compraItems as $item)
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
                    Bs. {{ number_format($compra->efectivo + $compra->credito, 2) }}
                </td>
            </tr>
            @if ($compra->efectivo > 0)
                <tr>
                    <td colspan="3" class="text-right">Efectivo:</td>
                    <td class="text-right">Bs. {{ number_format($compra->efectivo, 2) }}</td>
                </tr>
            @endif
            @if ($compra->credito > 0)
                <tr>
                    <td colspan="3" class="text-right">Crédito:</td>
                    <td class="text-right text-danger">Bs. {{ number_format($compra->credito, 2) }}</td>
                </tr>
            @endif
        </tfoot>
    </table>

    <div class="footer">
        <p>Generado el {{ now()->format('d/m/Y H:i:s') }} | MiSocio - Sistema de Gestión</p>
    </div>
</body>
</html>
