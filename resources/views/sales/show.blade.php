@extends('layouts.app')

@section('content')
    <div class="container">

        <h3>Продажа: {{ $sale->document_number }}</h3>
        <p>Магазин: <b>{{ $sale->store->name }}</b></p>
        <p>Дата: {{ $sale->document_date }}</p>
        <p>Сумма: <b>{{ number_format($sale->total_amount,2) }} ₽</b></p>

        <h4>Товары</h4>

        <table class="table table-bordered">
            <thead>
            <tr>
                <th>Наименование</th>
                <th>Кол-во</th>
                <th>Цена</th>
                <th>Сумма</th>
                <th>Возврат</th>
            </tr>
            </thead>
            <tbody>
            @foreach($sale->items as $i)
                <tr>
                    <td>{{ $i->product->name }}</td>
                    <td>{{ $i->quantity }}</td>
                    <td>{{ $i->unit_price }}</td>
                    <td>{{ $i->line_total }}</td>
                    <td>
                        <input type="number" class="form-control refund_qty"
                               data-id="{{ $i->id }}" max="{{ $i->quantity }}" step="0.001">
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>

        <button class="btn btn-danger w-100" onclick="refund()">🔁 Провести возврат</button>

        <script>
            function refund() {
                let items = [];

                $('.refund_qty').each(function(){
                    let qty = $(this).val();
                    if (qty > 0) {
                        items.push({
                            id: $(this).data('id'),
                            quantity: qty
                        });
                    }
                });

                if (!items.length) return alert("Укажите количество для возврата!");

                $.post("/sales/{{ $sale->id }}/refund", {
                    _token: '{{ csrf_token() }}',
                    items: items
                }, function(res){
                    if (res.success) {
                        alert("Возврат проведен. Сумма: " + res.total.toFixed(2));
                        location.reload();
                    }
                }).fail(res => {
                    alert("Ошибка возврата: " + res.responseJSON.message);
                });
            }
        </script>

    </div>
@endsection
