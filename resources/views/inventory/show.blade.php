@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>Инвентаризация {{ $inventory->document_number }}</h2>
        <p class="text-muted">
            {{ $inventory->document_date->format('d.m.Y H:i') }},
            склад: {{ $inventory->warehouse->name ?? '—' }},
            статус:
            @if($inventory->isDraft())
                <span class="badge bg-secondary">Черновик</span>
            @elseif($inventory->isApplied())
                <span class="badge bg-success">Проведена</span>
            @else
                <span class="badge bg-danger">Отменена</span>
            @endif
        </p>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger">
                @foreach($errors->all() as $e)
                    <div>{{ $e }}</div>
                @endforeach
            </div>
        @endif

        {{-- Кнопки действий --}}
        <div class="mb-3">
            <a href="{{ route('inventory.print', $inventory->id) }}" class="btn btn-outline-secondary" target="_blank">
                🖨 Печатная форма
            </a>

            @if($inventory->isDraft())
                <form action="{{ route('inventory.apply', $inventory->id) }}" method="POST" class="d-inline">
                    @csrf
                    <button class="btn btn-success" onclick="return confirm('Провести инвентаризацию?')">
                        ✔ Провести
                    </button>
                </form>

                <form action="{{ route('inventory.cancel', $inventory->id) }}" method="POST" class="d-inline">
                    @csrf
                    <button class="btn btn-danger" onclick="return confirm('Отменить инвентаризацию?')">
                        ✖ Отменить
                    </button>
                </form>
            @endif
        </div>

        {{-- Загрузка CSV --}}
        @if($inventory->isDraft())
            <div class="card mb-3">
                <div class="card-header">Загрузка фактических остатков из CSV (product_id;actual_qty)</div>
                <div class="card-body">
                    <form action="{{ route('inventory.importCsv', $inventory->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="file" name="file" class="form-control mb-2" required>
                        <button class="btn btn-outline-primary btn-sm">Загрузить</button>
                    </form>
                </div>
            </div>
        @endif

        {{-- Таблица позиций --}}
        <div class="card">
            <div class="card-header">Позиции инвентаризации</div>
            <div class="card-body p-0">
                <form action="{{ route('inventory.updateItems', $inventory->id) }}" method="POST">
                    @csrf
                    @if($inventory->isDraft())
                        <div class="p-3 border-bottom">
                            <div class="input-group">
                                <input id="inventoryBarcode" class="form-control form-control-lg" placeholder="Сканируйте штрихкод товара">
                                <button class="btn btn-outline-primary" type="button" onclick="BarcodeScanner.open('#inventoryBarcode')">Камера</button>
                            </div>
                        </div>
                    @endif
                    <div class="table-responsive mb-0">
                        <table class="table table-striped table-hover mb-0">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>Товар</th>
                                <th class="text-end">Ожидаемый</th>
                                <th class="text-end">Фактический</th>
                                <th class="text-end">Отклонение</th>
                                <th class="text-end">Цена</th>
                                <th class="text-end">Отклонение, ₽</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($inventory->items as $item)
                                <tr data-barcode="{{ $item->product->barcode ?? '' }}">
                                    <td>{{ $item->id }}</td>
                                    <td>{{ $item->product->name ?? ('Товар #'.$item->product_id) }}</td>
                                    <td class="text-end">{{ number_format($item->expected_qty, 3, ',', ' ') }}</td>
                                    <td class="text-end">
                                        @if($inventory->isDraft())
                                            <input type="number" step="1"
                                                   name="items[{{ $item->id }}][actual_qty]"
                                                   value="{{ $item->actual_qty }}"
                                                   class="form-control form-control-sm text-end actual_qty">
                                        @else
                                            {{ number_format($item->actual_qty, 3, ',', ' ') }}
                                        @endif
                                    </td>
                                    <td class="text-end">{{ number_format($item->diff_qty, 3, ',', ' ') }}</td>
                                    <td class="text-end">
                                        @if($item->unit_price)
                                            {{ number_format($item->unit_price, 2, ',', ' ') }} ₽
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="text-end">{{ number_format($item->diff_value, 2, ',', ' ') }} ₽</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if($inventory->isDraft())
                        <div class="p-3">
                            <button class="btn btn-primary">💾 Сохранить фактические</button>
                        </div>
                    @endif
                </form>
            </div>
        </div>

    </div>
@endsection

@push('scripts')
    <script>
        $('#inventoryBarcode').on('change keydown', function(e) {
            if (e.type === 'keydown' && e.key !== 'Enter') return;
            e.preventDefault();

            const barcode = ($(this).val() || '').trim();
            if (!barcode) return;

            const row = $(`tr[data-barcode="${barcode}"]`);
            if (!row.length) {
                alert('Товар с таким штрихкодом не найден в этой инвентаризации.');
                $(this).val('').focus();
                return;
            }

            const input = row.find('.actual_qty');
            const qty = parseInt(input.val() || 0, 10) || 0;
            input.val(qty + 1);
            $(this).val('').focus();
        });
    </script>
@endpush
