<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        table, th, td { border: 1px solid #000; }
        th, td { padding: 6px; font-size: 14px; }
        h2 { margin-bottom: 0; }
        .info p { margin: 4px 0; }
    </style>
</head>
<body onload="window.print()">

<h2>Накладная приёмки товара № {{ $receipt->document_number }}</h2>

<div class="info">
    <p><b>Дата:</b>
        {{ \Carbon\Carbon::parse($receipt->document_date)->format('d.m.Y H:i') }}
    </p>

    <p><b>Склад:</b> {{ $receipt->warehouse->name }}</p>

    <p><b>Поставщик:</b>
        {{ $receipt->supplier_name ?? '—' }}
    </p>

    <p><b>Пользователь:</b>
        {{ $receipt->user->name ?? '—' }}
    </p>

    @if($receipt->comment)
        <p><b>Комментарий:</b> {{ $receipt->comment }}</p>
    @endif
</div>

<table>
    <thead>
    <tr>
        <th>#</th>
        <th>Товар</th>
        <th>Кол-во</th>
        <th>Цена</th>
        <th>Сумма</th>
        <th>Партия</th>
        <th>Годен до</th>
    </tr>
    </thead>
    <tbody>
    @foreach($receipt->items as $i => $item)
        <tr>
            <td>{{ $i+1 }}</td>
            <td>{{ $item->product->name }}</td>
            <td>{{ $item->quantity }}</td>
            <td>{{ number_format($item->unit_price, 2, ',', ' ') }} ₽</td>
            <td>{{ number_format($item->unit_price * $item->quantity, 2, ',', ' ') }} ₽</td>
            <td>{{ $item->batch }}</td>
            <td>{{ $item->expiry_date }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

</body>
</html>
