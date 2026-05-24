<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <title>Инвентаризация {{ $inventory->document_number }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #000; padding: 4px; }
        th { background: #eee; }
    </style>
</head>
<body onload="window.print()">

<h3>Лист инвентаризации № {{ $inventory->document_number }}</h3>
<p>
    Дата: {{ $inventory->document_date->format('d.m.Y H:i') }}<br>
    Склад: {{ $inventory->warehouse->name ?? '—' }}<br>
    Пользователь: {{ $inventory->user->name ?? '—' }}<br>
    Комментарий: {{ $inventory->comment }}
</p>

<table>
    <thead>
    <tr>
        <th>#</th>
        <th>Товар</th>
        <th>Ожидаемый</th>
        <th>Фактический</th>
        <th>Отклонение</th>
        <th>Цена</th>
        <th>Отклонение, ₽</th>
    </tr>
    </thead>
    <tbody>
    @foreach($inventory->items as $item)
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $item->product->name ?? ('Товар #'.$item->product_id) }}</td>
            <td style="text-align:right">{{ number_format($item->expected_qty, 3, ',', ' ') }}</td>
            <td style="text-align:right">{{ number_format($item->actual_qty, 3, ',', ' ') }}</td>
            <td style="text-align:right">{{ number_format($item->diff_qty, 3, ',', ' ') }}</td>
            <td style="text-align:right">
                @if($item->unit_price)
                    {{ number_format($item->unit_price, 2, ',', ' ') }} ₽
                @else
                    —
                @endif
            </td>
            <td style="text-align:right">{{ number_format($item->diff_value, 2, ',', ' ') }} ₽</td>
        </tr>
    @endforeach
    </tbody>
</table>

</body>
</html>
