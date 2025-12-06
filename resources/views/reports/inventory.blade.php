<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventario Valorizado</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #14b8a6;
            padding-bottom: 10px;
        }
        .header h1 {
            color: #0f766e;
            margin: 0;
            font-size: 24px;
        }
        .period {
            color: #6b7280;
            font-size: 14px;
            margin-top: 5px;
        }
        .section {
            margin-bottom: 25px;
        }
        .section-title {
            background-color: #14b8a6;
            color: white;
            padding: 8px 12px;
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .metric-grid {
            display: table;
            width: 100%;
            margin-bottom: 10px;
        }
        .metric-row {
            display: table-row;
        }
        .metric-label {
            display: table-cell;
            padding: 6px;
            font-weight: bold;
            width: 60%;
        }
        .metric-value {
            display: table-cell;
            padding: 6px;
            text-align: right;
            width: 40%;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th {
            background-color: #f3f4f6;
            padding: 8px;
            text-align: left;
            border-bottom: 2px solid #d1d5db;
        }
        td {
            padding: 6px 8px;
            border-bottom: 1px solid #e5e7eb;
        }
        .alert-row {
            background-color: #fef2f2;
        }
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 10px;
            color: #9ca3af;
            padding-top: 10px;
            border-top: 1px solid #e5e7eb;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>INVENTARIO VALORIZADO</h1>
        <div class="period">
            Periodo: {{ $data['period']['from'] }} - {{ $data['period']['to'] }}
        </div>
    </div>

    @if(isset($data['valuation']))
    <div class="section">
        <div class="section-title">VALORIZACIÓN DE INVENTARIO</div>
        <div class="metric-grid">
            <div class="metric-row">
                <div class="metric-label">Total de Productos:</div>
                <div class="metric-value">{{ $data['valuation']['total_products'] }}</div>
            </div>
            <div class="metric-row">
                <div class="metric-label">Valor Total del Inventario:</div>
                <div class="metric-value" style="font-weight: bold; color: #10b981;">
                    ${{ number_format($data['valuation']['total_value'], 2) }}
                </div>
            </div>
        </div>
    </div>
    @endif

    @if(isset($data['movements']))
    <div class="section">
        <div class="section-title">MOVIMIENTOS DE INVENTARIO</div>
        <div class="metric-grid">
            <div class="metric-row">
                <div class="metric-label">Total de Movimientos:</div>
                <div class="metric-value">{{ $data['movements']['total_movements'] }}</div>
            </div>
        </div>
    </div>
    @endif

    @if(isset($data['alerts']))
    <div class="section">
        <div class="section-title">ALERTAS DE INVENTARIO</div>
        <div class="metric-grid">
            <div class="metric-row">
                <div class="metric-label">Productos con Stock Bajo:</div>
                <div class="metric-value" style="color: {{ $data['alerts']['low_stock_count'] > 0 ? '#ef4444' : '#10b981' }};">
                    {{ $data['alerts']['low_stock_count'] }}
                </div>
            </div>
            <div class="metric-row">
                <div class="metric-label">Productos Agotados:</div>
                <div class="metric-value" style="color: {{ $data['alerts']['out_of_stock_count'] > 0 ? '#ef4444' : '#10b981' }};">
                    {{ $data['alerts']['out_of_stock_count'] }}
                </div>
            </div>
        </div>

        @if(isset($data['alerts']['low_stock_products']) && count($data['alerts']['low_stock_products']) > 0)
        <table>
            <thead>
                <tr>
                    <th>Producto</th>
                    <th style="text-align: right;">Stock Actual</th>
                    <th style="text-align: right;">Stock Mínimo</th>
                    <th>Unidad</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['alerts']['low_stock_products'] as $product)
                <tr class="alert-row">
                    <td>{{ $product['name'] }}</td>
                    <td style="text-align: right;">{{ $product['current_stock'] }}</td>
                    <td style="text-align: right;">{{ $product['min_stock'] }}</td>
                    <td>{{ $product['unit_type'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>
    @endif

    <div class="footer">
        Generado el {{ date('d/m/Y H:i:s') }} | Sistema de Gestión de Restaurantes
    </div>
</body>
</html>
