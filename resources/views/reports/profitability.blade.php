<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Análisis de Rentabilidad</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #a855f7;
            padding-bottom: 10px;
        }
        .header h1 {
            color: #7e22ce;
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
            background-color: #a855f7;
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
            font-size: 11px;
        }
        th {
            background-color: #f3f4f6;
            padding: 6px 4px;
            text-align: left;
            border-bottom: 2px solid #d1d5db;
        }
        td {
            padding: 4px;
            border-bottom: 1px solid #e5e7eb;
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
        <h1>ANÁLISIS DE RENTABILIDAD</h1>
        <div class="period">
            Periodo: {{ $data['period']['from'] }} - {{ $data['period']['to'] }}
        </div>
    </div>

    @if(isset($data['overview']))
    <div class="section">
        <div class="section-title">RESUMEN GENERAL</div>
        <div class="metric-grid">
            <div class="metric-row">
                <div class="metric-label">Ingresos Totales:</div>
                <div class="metric-value" style="color: #10b981;">${{ number_format($data['overview']['total_revenue'], 2) }}</div>
            </div>
            <div class="metric-row">
                <div class="metric-label">Costo Total:</div>
                <div class="metric-value" style="color: #ef4444;">${{ number_format($data['overview']['total_cost'], 2) }}</div>
            </div>
            <div class="metric-row">
                <div class="metric-label">Utilidad Bruta:</div>
                <div class="metric-value" style="font-weight: bold; color: {{ $data['overview']['gross_profit'] >= 0 ? '#10b981' : '#ef4444' }};">
                    ${{ number_format($data['overview']['gross_profit'], 2) }}
                </div>
            </div>
            <div class="metric-row">
                <div class="metric-label">Margen de Utilidad:</div>
                <div class="metric-value" style="font-weight: bold;">
                    {{ number_format($data['overview']['profit_margin'], 2) }}%
                </div>
            </div>
        </div>
    </div>
    @endif

    @if(isset($data['products']) && count($data['products']) > 0)
    <div class="section">
        <div class="section-title">RENTABILIDAD POR PRODUCTO</div>
        <table>
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Tipo</th>
                    <th style="text-align: right;">Cant.</th>
                    <th style="text-align: right;">Ingresos</th>
                    <th style="text-align: right;">Costo</th>
                    <th style="text-align: right;">Utilidad</th>
                    <th style="text-align: right;">Margen %</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['products'] as $product)
                <tr>
                    <td>{{ $product['name'] }}</td>
                    <td>{{ $product['type'] === 'menu' ? 'Platillo' : 'Simple' }}</td>
                    <td style="text-align: right;">{{ $product['quantity_sold'] }}</td>
                    <td style="text-align: right;">${{ number_format($product['revenue'], 2) }}</td>
                    <td style="text-align: right;">${{ number_format($product['cost'], 2) }}</td>
                    <td style="text-align: right; color: {{ $product['profit'] >= 0 ? '#10b981' : '#ef4444' }};">
                        ${{ number_format($product['profit'], 2) }}
                    </td>
                    <td style="text-align: right;">{{ number_format($product['margin'], 2) }}%</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <div class="footer">
        Generado el {{ date('d/m/Y H:i:s') }} | Sistema de Gestión de Restaurantes
    </div>
</body>
</html>
