<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estado Financiero</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #10b981;
            padding-bottom: 10px;
        }
        .header h1 {
            color: #047857;
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
            background-color: #10b981;
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
        <h1>ESTADO FINANCIERO</h1>
        <div class="period">
            Periodo: {{ $data['period']['from'] }} - {{ $data['period']['to'] }}
        </div>
    </div>

    @if(isset($data['balance']))
    <div class="section">
        <div class="section-title">BALANCE GENERAL</div>
        <div class="metric-grid">
            <div class="metric-row">
                <div class="metric-label">Ingresos Totales:</div>
                <div class="metric-value" style="color: #10b981;">${{ number_format($data['balance']['income'], 2) }}</div>
            </div>
            <div class="metric-row">
                <div class="metric-label">Egresos Totales:</div>
                <div class="metric-value" style="color: #ef4444;">${{ number_format($data['balance']['expenses'], 2) }}</div>
            </div>
            <div class="metric-row">
                <div class="metric-label">Balance Neto:</div>
                <div class="metric-value" style="font-weight: bold; color: {{ $data['balance']['net'] >= 0 ? '#10b981' : '#ef4444' }};">
                    ${{ number_format($data['balance']['net'], 2) }}
                </div>
            </div>
            <div class="metric-row">
                <div class="metric-label">Margen de Utilidad:</div>
                <div class="metric-value" style="font-weight: bold;">
                    {{ number_format($data['balance']['profit_margin'], 2) }}%
                </div>
            </div>
        </div>
    </div>
    @endif

    @if(isset($data['income']))
    <div class="section">
        <div class="section-title">INGRESOS</div>
        <div class="metric-grid">
            <div class="metric-row">
                <div class="metric-label">Total:</div>
                <div class="metric-value">${{ number_format($data['income']['total'], 2) }}</div>
            </div>
            <div class="metric-row">
                <div class="metric-label">Número de Transacciones:</div>
                <div class="metric-value">{{ $data['income']['count'] }}</div>
            </div>
        </div>

        @if(count($data['income']['by_category']) > 0)
        <table>
            <thead>
                <tr>
                    <th>Categoría</th>
                    <th style="text-align: right;">Monto</th>
                    <th style="text-align: right;">Transacciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['income']['by_category'] as $category)
                <tr>
                    <td>{{ $category['category'] }}</td>
                    <td style="text-align: right;">${{ number_format($category['amount'], 2) }}</td>
                    <td style="text-align: right;">{{ $category['count'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>
    @endif

    @if(isset($data['expenses']))
    <div class="section">
        <div class="section-title">EGRESOS</div>
        <div class="metric-grid">
            <div class="metric-row">
                <div class="metric-label">Total:</div>
                <div class="metric-value">${{ number_format($data['expenses']['total'], 2) }}</div>
            </div>
            <div class="metric-row">
                <div class="metric-label">Número de Transacciones:</div>
                <div class="metric-value">{{ $data['expenses']['count'] }}</div>
            </div>
        </div>

        @if(count($data['expenses']['by_category']) > 0)
        <table>
            <thead>
                <tr>
                    <th>Categoría</th>
                    <th style="text-align: right;">Monto</th>
                    <th style="text-align: right;">Transacciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['expenses']['by_category'] as $category)
                <tr>
                    <td>{{ $category['category'] }}</td>
                    <td style="text-align: right;">${{ number_format($category['amount'], 2) }}</td>
                    <td style="text-align: right;">{{ $category['count'] }}</td>
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
