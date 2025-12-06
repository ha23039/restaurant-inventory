<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte Ejecutivo</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #3b82f6;
            padding-bottom: 10px;
        }
        .header h1 {
            color: #1e40af;
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
            background-color: #3b82f6;
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
        <h1>REPORTE EJECUTIVO</h1>
        <div class="period">
            Periodo: {{ $data['period']['from'] }} - {{ $data['period']['to'] }}
        </div>
    </div>

    @if(isset($data['sales']))
    <div class="section">
        <div class="section-title">VENTAS</div>
        <div class="metric-grid">
            <div class="metric-row">
                <div class="metric-label">Total de Ventas:</div>
                <div class="metric-value">{{ $data['sales']['total_sales'] }}</div>
            </div>
            <div class="metric-row">
                <div class="metric-label">Ingresos Totales:</div>
                <div class="metric-value">${{ number_format($data['sales']['total_revenue'], 2) }}</div>
            </div>
            <div class="metric-row">
                <div class="metric-label">Ticket Promedio:</div>
                <div class="metric-value">${{ number_format($data['sales']['average_ticket'], 2) }}</div>
            </div>
            <div class="metric-row">
                <div class="metric-label">Descuentos Totales:</div>
                <div class="metric-value">${{ number_format($data['sales']['total_discount'], 2) }}</div>
            </div>
        </div>
    </div>
    @endif

    @if(isset($data['cashflow']))
    <div class="section">
        <div class="section-title">FLUJO DE EFECTIVO</div>
        <div class="metric-grid">
            <div class="metric-row">
                <div class="metric-label">Ingresos Totales:</div>
                <div class="metric-value" style="color: #10b981;">${{ number_format($data['cashflow']['total_income'], 2) }}</div>
            </div>
            <div class="metric-row">
                <div class="metric-label">Egresos Totales:</div>
                <div class="metric-value" style="color: #ef4444;">${{ number_format($data['cashflow']['total_expenses'], 2) }}</div>
            </div>
            <div class="metric-row">
                <div class="metric-label">Flujo Neto:</div>
                <div class="metric-value" style="font-weight: bold; color: {{ $data['cashflow']['net_cashflow'] >= 0 ? '#10b981' : '#ef4444' }};">
                    ${{ number_format($data['cashflow']['net_cashflow'], 2) }}
                </div>
            </div>
        </div>
    </div>
    @endif

    @if(isset($data['inventory']))
    <div class="section">
        <div class="section-title">INVENTARIO</div>
        <div class="metric-grid">
            <div class="metric-row">
                <div class="metric-label">Total de Productos:</div>
                <div class="metric-value">{{ $data['inventory']['total_products'] }}</div>
            </div>
            <div class="metric-row">
                <div class="metric-label">Productos con Stock Bajo:</div>
                <div class="metric-value" style="color: {{ $data['inventory']['low_stock_count'] > 0 ? '#ef4444' : '#10b981' }};">
                    {{ $data['inventory']['low_stock_count'] }}
                </div>
            </div>
            <div class="metric-row">
                <div class="metric-label">Valor Total del Inventario:</div>
                <div class="metric-value">${{ number_format($data['inventory']['total_inventory_value'], 2) }}</div>
            </div>
            <div class="metric-row">
                <div class="metric-label">Productos Agotados:</div>
                <div class="metric-value" style="color: {{ $data['inventory']['out_of_stock'] > 0 ? '#ef4444' : '#10b981' }};">
                    {{ $data['inventory']['out_of_stock'] }}
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="footer">
        Generado el {{ date('d/m/Y H:i:s') }} | Sistema de Gestión de Restaurantes
    </div>
</body>
</html>
