<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Ventas</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            color: #1f2937;
            line-height: 1.5;
        }

        .container {
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e5e7eb;
        }

        .header h1 {
            font-size: 24px;
            font-weight: bold;
            color: #111827;
            margin-bottom: 5px;
        }

        .header p {
            color: #6b7280;
            font-size: 14px;
        }

        .summary {
            display: table;
            width: 100%;
            margin-bottom: 30px;
        }

        .summary-item {
            display: table-cell;
            width: 33.33%;
            text-align: center;
            padding: 15px;
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
        }

        .summary-item .label {
            font-size: 11px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
        }

        .summary-item .value {
            font-size: 20px;
            font-weight: bold;
            color: #111827;
        }

        .section-title {
            font-size: 16px;
            font-weight: bold;
            color: #111827;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e5e7eb;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th {
            background-color: #f3f4f6;
            padding: 12px 8px;
            text-align: left;
            font-weight: 600;
            color: #374151;
            border-bottom: 2px solid #e5e7eb;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        td {
            padding: 10px 8px;
            border-bottom: 1px solid #e5e7eb;
            color: #4b5563;
        }

        tr:nth-child(even) {
            background-color: #f9fafb;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .change-positive {
            color: #059669;
            font-weight: 600;
        }

        .change-negative {
            color: #dc2626;
            font-weight: 600;
        }

        .change-neutral {
            color: #6b7280;
        }

        .rank {
            font-weight: bold;
            color: #111827;
            width: 30px;
        }

        .product-name {
            font-weight: 500;
            color: #111827;
        }

        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            color: #9ca3af;
            font-size: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Reporte de Ventas</h1>
            <p>Periodo: {{ $startDate }} - {{ $endDate }}</p>
        </div>

        <div class="summary">
            <div class="summary-item">
                <div class="label">Ventas Totales</div>
                <div class="value">${{ number_format($summary['total_sales'], 2) }}</div>
            </div>
            <div class="summary-item">
                <div class="label">Productos Vendidos</div>
                <div class="value">{{ number_format($summary['total_quantity']) }}</div>
            </div>
            <div class="summary-item">
                <div class="label">Total Ordenes</div>
                <div class="value">{{ number_format($summary['total_orders']) }}</div>
            </div>
        </div>

        <div class="section-title">Top Productos Vendidos</div>

        <table>
            <thead>
                <tr>
                    <th class="rank">#</th>
                    <th>Producto</th>
                    <th class="text-right">Cantidad</th>
                    <th class="text-right">Ingresos</th>
                    <th class="text-center">vs Anterior</th>
                </tr>
            </thead>
            <tbody>
                @foreach($topProducts as $index => $product)
                <tr>
                    <td class="rank">{{ $index + 1 }}</td>
                    <td class="product-name">{{ $product['name'] }}</td>
                    <td class="text-right">{{ number_format($product['total_quantity']) }}</td>
                    <td class="text-right">${{ number_format($product['total_revenue'], 2) }}</td>
                    <td class="text-center @if($product['quantity_change'] > 0) change-positive @elseif($product['quantity_change'] < 0) change-negative @else change-neutral @endif">
                        @if($product['quantity_change'] > 0)+@endif{{ $product['quantity_change'] }}%
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="footer">
            Generado el {{ now()->format('d/m/Y H:i') }} | Sistema POS Restaurant
        </div>
    </div>
</body>
</html>
