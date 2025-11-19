<table>
    <thead>
        <tr>
            <th style="font-weight: bold;">ID</th>
            <th style="font-weight: bold;">Fecha</th>
            <th style="font-weight: bold;">Tipo</th>
            <th style="font-weight: bold;">Categoría</th>
            <th style="font-weight: bold;">Descripción</th>
            <th style="font-weight: bold;">Monto</th>
            <th style="font-weight: bold;">Usuario</th>
            <th style="font-weight: bold;">Notas</th>
        </tr>
    </thead>
    <tbody>
        @foreach($transactions as $transaction)
        <tr>
            <td>{{ $transaction->id }}</td>
            <td>{{ $transaction->flow_date->format('d/m/Y') }}</td>
            <td>{{ $transaction->type === 'entrada' ? 'Ingreso' : 'Egreso' }}</td>
            <td>{{ $transaction->category_label }}</td>
            <td>{{ $transaction->description }}</td>
            <td>{{ number_format($transaction->amount, 2) }}</td>
            <td>{{ $transaction->user->name ?? 'N/A' }}</td>
            <td>{{ $transaction->notes ?? '' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
