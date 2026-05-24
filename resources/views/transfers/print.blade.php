<html>
<head>
    <style>
        body { font-family: DejaVu Sans, sans-serif; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        table, th, td { border: 1px solid #000; }
        th, td { padding: 6px; }
        h2 { margin-bottom: 0; }
        .info { margin-top: 10px; }
    </style>
</head>
<body onload="window.print()">

<h2>Накладная на перемещение {{ $transfer->document_number }}</h2>
<div class="info">
    <p><b>Дата:</b> {{ $transfer->document_date->format('d.m.Y H:i') }}</p>
    <p><b>Откуда:</b>
        @if($transfer->fromWarehouse)
            {{ $transfer->fromWarehouse->name }}
        @else
            {{ $transfer->fromStore->name }}
        @endif
    </p>
    <p><b>Куда:</b>
        @if($transfer->toWarehouse)
            {{ $transfer->toWarehouse->name }}
        @else
            {{ $transfer->toStore->name }}
        @endif
    </p>
    <p><b>Пользователь:</b> {{ $transfer->user->name }}</p>
</div>

<table>
    <thead>
    <tr>
        <th>#</th>
        <th>Товар</th>
        <th>Кол-во</th>
        <th>Цена</th>
        <th>Партия</th>
        <th>Годен до</th>
    </tr>
    </thead>
    <tbody>
    @foreach($transfer->items as $i => $item)
        <tr>
            <td>{{ $i+1 }}</td>
            <td>{{ $item->product->name }}</td>
            <td>{{ $item->quantity }}</td>
            <td>{{ $item->unit_price }}</td>
            <td>{{ $item->batch }}</td>
            <td>{{ $item->expiry_date }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

</body>
</html>
