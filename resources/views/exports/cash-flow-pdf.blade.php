<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Flujo de Efectivo</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            color: #333;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #3B82F6;
            padding-bottom: 15px;
        }

        .header h1 {
            font-size: 20px;
            color: #1F2937;
            margin-bottom: 5px;
        }

        .header p {
            font-size: 11px;
            color: #6B7280;
        }

        .summary {
            display: table;
            width: 100%;
            margin-bottom: 20px;
            border: 1px solid #E5E7EB;
            border-radius: 4px;
        }

        .summary-row {
            display: table-row;
        }

        .summary-cell {
            display: table-cell;
            padding: 10px;
            border-bottom: 1px solid #E5E7EB;
        }

        .summary-cell:first-child {
            font-weight: bold;
            color: #374151;
            width: 30%;
        }

        .summary-cell.positive {
            color: #059669;
        }

        .summary-cell.negative {
            color: #DC2626;
        }

        .summary-cell.neutral {
            color: #3B82F6;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        thead {
            background-color: #3B82F6;
            color: white;
        }

        th {
            padding: 8px;
            text-align: left;
            font-weight: bold;
            font-size: 9px;
        }

        tbody tr {
            border-bottom: 1px solid #E5E7EB;
        }

        tbody tr:nth-child(even) {
            background-color: #F9FAFB;
        }

        tbody tr:hover {
            background-color: #F3F4F6;
        }

        td {
            padding: 6px 8px;
            font-size: 9px;
        }

        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
        }

        .badge-success {
            background-color: #D1FAE5;
            color: #065F46;
        }

        .badge-danger {
            background-color: #FEE2E2;
            color: #991B1B;
        }

        .badge-info {
            background-color: #DBEAFE;
            color: #1E40AF;
        }

        .badge-warning {
            background-color: #FEF3C7;
            color: #92400E;
        }

        .badge-secondary {
            background-color: #F3F4F6;
            color: #374151;
        }

        .amount-positive {
            color: #059669;
            font-weight: bold;
        }

        .amount-negative {
            color: #DC2626;
            font-weight: bold;
        }

        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #E5E7EB;
            text-align: center;
            font-size: 8px;
            color: #6B7280;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $restaurantName ?? 'Restaurante' }}</h1>
        <p>Reporte de Flujo de Efectivo</p>
        @if($dateFrom && $dateTo)
            <p>Período: {{ \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($dateTo)->format('d/m/Y') }}</p>
        @endif
        <p>Generado: {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>

    @if($summary)
    <div class="summary">
        <div class="summary-row">
            <div class="summary-cell">Total Ingresos:</div>
            <div class="summary-cell positive">${{ number_format($summary['income']['total'], 2) }}</div>
            <div class="summary-cell">Transacciones:</div>
            <div class="summary-cell">{{ $summary['income']['count'] }}</div>
        </div>
        <div class="summary-row">
            <div class="summary-cell">Total Egresos:</div>
            <div class="summary-cell negative">${{ number_format($summary['expenses']['total'], 2) }}</div>
            <div class="summary-cell">Transacciones:</div>
            <div class="summary-cell">{{ $summary['expenses']['count'] }}</div>
        </div>
        <div class="summary-row">
            <div class="summary-cell">Balance:</div>
            <div class="summary-cell neutral">${{ number_format($summary['balance'], 2) }}</div>
            <div class="summary-cell">Margen de Ganancia:</div>
            <div class="summary-cell">{{ number_format($summary['profit_margin'], 2) }}%</div>
        </div>
    </div>
    @endif

    <table>
        <thead>
            <tr>
                <th style="width: 8%;">Fecha</th>
                <th style="width: 10%;">Tipo</th>
                <th style="width: 15%;">Categoría</th>
                <th style="width: 32%;">Descripción</th>
                <th style="width: 12%;">Monto</th>
                <th style="width: 13%;">Usuario</th>
                <th style="width: 10%;">Venta</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transactions as $transaction)
            <tr>
                <td>{{ $transaction->flow_date->format('d/m/Y') }}</td>
                <td>
                    @if($transaction->type === 'entrada')
                        <span class="badge badge-success">Ingreso</span>
                    @else
                        <span class="badge badge-danger">Egreso</span>
                    @endif
                </td>
                <td>
                    @php
                        $badgeClass = 'badge-secondary';
                        if ($transaction->category === 'ventas') $badgeClass = 'badge-success';
                        elseif ($transaction->category === 'compras') $badgeClass = 'badge-info';
                        elseif (in_array($transaction->category, ['gastos_operativos', 'gastos_admin'])) $badgeClass = 'badge-warning';
                    @endphp
                    <span class="badge {{ $badgeClass }}">{{ $transaction->category_label }}</span>
                </td>
                <td>{{ $transaction->description }}</td>
                <td class="{{ $transaction->type === 'entrada' ? 'amount-positive' : 'amount-negative' }}">
                    {{ $transaction->type === 'entrada' ? '+' : '-' }}${{ number_format($transaction->amount, 2) }}
                </td>
                <td>{{ $transaction->user->name ?? 'N/A' }}</td>
                <td>
                    @if($transaction->sale)
                        #{{ $transaction->sale->sale_number }}
                    @else
                        -
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Este documento fue generado automáticamente por el Sistema de Gestión de Restaurantes</p>
        <p>Página {{ $loop ?? 1 }} - {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>
</body>
</html>
