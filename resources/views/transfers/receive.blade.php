@extends('layouts.app')

@section('content')
    <div class="container">
        <h2 class="mb-4">Прием груза {{ $transfer->document_number }}</h2>

        <div class="card mb-3">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <b>Откуда:</b>
                        {{ $transfer->fromWarehouse->name ?? $transfer->fromStore->name ?? '-' }}
                    </div>
                    <div class="col-md-6">
                        <b>Куда:</b>
                        {{ $transfer->toWarehouse->name ?? $transfer->toStore->name ?? '-' }}
                    </div>
                </div>
            </div>
        </div>

        <div class="input-group mb-3">
            <input id="receiveBarcode" class="form-control form-control-lg" placeholder="Сканируйте штрихкод товара">
            <button class="btn btn-primary" type="button" onclick="BarcodeScanner.open('#receiveBarcode')">Сканировать камерой</button>
        </div>

        <div class="table-responsive mb-3">
            <table class="table table-bordered align-middle">
                <thead class="table-light">
                <tr>
                    <th>Товар</th>
                    <th>Штрихкод</th>
                    <th class="text-end">Отгружено</th>
                    <th class="text-end">Уже принято</th>
                    <th class="text-end">Сейчас</th>
                </tr>
                </thead>
                <tbody>
                @foreach($transfer->items as $item)
                    <tr data-product-id="{{ $item->product_id }}" data-barcode="{{ $item->product->barcode }}">
                        <td>{{ $item->product->name }}</td>
                        <td>{{ $item->product->barcode }}</td>
                        <td class="text-end shipped">{{ (int)$item->quantity }}</td>
                        <td class="text-end received">{{ (int)$item->received_quantity }}</td>
                        <td>
                            <input type="number" class="form-control text-end receive_qty" min="0" step="1" value="0">
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        <button class="btn btn-success btn-lg w-100" onclick="saveReceive()">Принять отсканированное</button>
    </div>
@endsection

@push('scripts')
    <script>
        function scanReceiveBarcode(barcode) {
            barcode = (barcode || '').trim();
            if (!barcode) return;

            const row = $(`tr[data-barcode="${barcode}"]`);
            if (!row.length) {
                alert('Товар с таким штрихкодом не входит в этот груз.');
                return;
            }

            const shipped = parseInt(row.find('.shipped').text() || 0, 10);
            const received = parseInt(row.find('.received').text() || 0, 10);
            const qtyInput = row.find('.receive_qty');
            const current = parseInt(qtyInput.val() || 0, 10) || 0;

            if (received + current >= shipped) {
                alert('По этому товару уже принято все отгруженное количество.');
                return;
            }

            qtyInput.val(current + 1);
        }

        $('#receiveBarcode').on('change keydown', function(e) {
            if (e.type === 'keydown' && e.key !== 'Enter') return;
            e.preventDefault();

            scanReceiveBarcode($(this).val());
            $(this).val('').focus();
        });

        function saveReceive() {
            const items = [];

            $('tr[data-product-id]').each(function () {
                const qty = parseInt($(this).find('.receive_qty').val() || 0, 10) || 0;
                if (qty <= 0) return;

                items.push({
                    product_id: $(this).data('product-id'),
                    quantity: qty,
                });
            });

            if (!items.length) {
                alert('Отсканируйте хотя бы один товар.');
                return;
            }

            $.ajax({
                url: '{{ route('transfers.accept', $transfer->id) }}',
                method: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    items: items,
                },
                success: function(res) {
                    if (res.success) {
                        alert('Груз принят.');
                        location.href = '{{ route('transfers.show', $transfer->id) }}';
                    }
                },
                error: function(xhr) {
                    alert(xhr.responseJSON?.message || 'Ошибка приемки груза.');
                }
            });
        }
    </script>
@endpush
