@extends('layouts.app')

@section('content')
    <div class="container">

        <h2 class="mb-4">📤 Перемещение товара</h2>

        {{-- Локации --}}
        <div class="card mb-3">
            <div class="card-body">

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <h5>Откуда</h5>
                        <select id="from_type" class="form-select mb-2">
                            <option value="warehouse">Склад</option>
                            <option value="store">Магазин</option>
                        </select>

                        <select id="from_id" class="form-select"></select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <h5>Куда</h5>
                        <select id="to_type" class="form-select mb-2">
                            <option value="warehouse">Склад</option>
                            <option value="store">Магазин</option>
                        </select>

                        <select id="to_id" class="form-select"></select>
                    </div>
                </div>

                <label class="form-label mt-2">Комментарий</label>
                <textarea id="comment" class="form-control"></textarea>

            </div>
        </div>

        <h4 class="mb-3">Товары</h4>

        <div id="itemsCardWrapper"></div>

        <button class="btn btn-primary w-100 mb-3" onclick="addRow()">➕ Добавить товар</button>

        <div class="card mb-3">
            <div class="card-body d-flex justify-content-between">
                <strong>Итого по документу:</strong>
                <strong id="docTotal">0.00</strong>
            </div>
        </div>

        <button class="btn btn-success w-100 btn-lg" onclick="saveTransfer()">💾 Сохранить документ</button>

    </div>
@endsection

@push('head')
    <style>
        .item-card {
            border: 1px solid #ddd;
            border-radius: 10px;
            padding: 12px;
            margin-bottom: 12px;
            background: #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", function () {

            @if(isset($presetProductId) && $presetProductId)
            addPresetProduct({{ $presetProductId }}, "{{ addslashes($presetProductName) }}", {{ $presetQtyLeft ?? 0 }});
            @endif
            @if(isset($presetFromId) && $presetFromId)
            $('#from_type').val('warehouse').change();
            $('#from_id').val('{{ $presetFromId }}').change();
            @endif

        });

        // Функция: добавляет строку с выбранным товаром

        function addPresetProduct(productId, productName, qtyLeft) {
            if (typeof addRow === 'function') {
                addRow();
            } else {
                console.error("addRow() не найдена");
                return;
            }

            setTimeout(() => {
                let lastRow = $("#itemsCardWrapper .item-card").last();

                // удаляем select2
                const originalSelect = lastRow.find('.product_select');
                if (originalSelect.data('select2')) originalSelect.select2('destroy');
                originalSelect.remove();

                // вставляем нормальный селект
                let html = `
            <select class="form-select product_select_locked mb-2" disabled>
                <option value="${productId}" selected>${productName}</option>
            </select>
            <input type="hidden" name="items[${rowId}][product_id]" value="${productId}">
        `;
                lastRow.find('.product_select_locked').remove();
                lastRow.prepend(html);

                // показать остаток
                lastRow.find(`#stock_info_${rowId}`).text(`Остаток: ${qtyLeft}`);

                // ВАЖНО! Устанавливаем максимальное значение для проверки
                lastRow.find('.qty').data('max', qtyLeft);

                // количество по умолчанию
                lastRow.find('.qty').val(1).change();

            }, 300);
        }

    </script>
    <script>

        $(document).on('change', '.barcode_input', function () {
            let rid = $(this).data('id');
            let barcode = $(this).val().trim();

            if (!barcode) return;

            $.ajax({
                url: "/api/products/by-barcode",
                data: {
                    barcode: barcode,
                    location_type: $('#from_type').val(),
                    location_id: $('#from_id').val(),
                },
                success: function(res) {
                    if (!res.results || !res.results.length) {
                        alert("Товар с таким штрих-кодом не найден");
                        return;
                    }

                    let d = res.results[0];

                    let select = $(`#row_${rid} .product_select`);

                    let option = new Option(d.text, d.id, true, true);
                    select.append(option).trigger('change');      // визуальная подстановка
                    select.trigger({
                        type: 'select2:select',
                        params: { data: d }                       // ДАННЫЕ для твоей логики
                    });
                }
            });
        });



        let rowId = 0;

        // Передаём списки складов/магазинов из контроллера
        const warehouses = @json($warehouses);
        const stores     = @json($stores);

        function fillLocationSelect(type, selectId) {
            const sel = $(selectId);
            sel.empty();

            if (type === 'warehouse') {
                warehouses.forEach(w => sel.append(new Option(w.name, w.id)));
            } else {
                stores.forEach(s => sel.append(new Option(s.name, s.id)));
            }
        }

        $('#from_type').on('change', function(){
            fillLocationSelect(this.value, '#from_id');
        });
        $('#to_type').on('change', function(){
            fillLocationSelect(this.value, '#to_id');
        });

        // Инициализация по умолчанию
        fillLocationSelect('warehouse', '#from_id');
        fillLocationSelect('warehouse', '#to_id');

        // === Добавление карточки товара ===
        function addRow() {
            rowId++;

            let card = `
<div class="item-card" id="row_${rowId}">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h5 class="mb-0">Товар #${rowId}</h5>
        <button type="button" class="btn btn-sm btn-outline-danger" onclick="$('#row_${rowId}').remove(); recalcDocTotal();">✖</button>
    </div>
<label class="form-label small">Штрих-код</label>
<div class="input-group">
<input type="text" class="form-control barcode_input" data-id="${rowId}" placeholder="Сканируйте или введите штрих-код" id="bcd${rowId}">
 <button class="btn btn-outline-primary"
            onclick="BarcodeScanner.open('#bcd${rowId}')">
        📷
    </button>
</div>
    <label class="form-label small">Товар (по остаткам)</label>
    <select class="form-select mb-2 product_select" data-id="${rowId}"></select>
    <div class="text-muted small mb-2" id="stock_info_${rowId}"></div>

    <div class="row">
        <div class="col-6">
            <label class="form-label small">Кол-во</label>
            <input type="number" class="form-control qty" min="0" step="0.001" data-id="${rowId}">
        </div>
        <div class="col-6">
            <label class="form-label small">Цена</label>
            <input type="number" class="form-control price" min="0" step="0.01" data-id="${rowId}">
        </div>
    </div>

    <div class="d-flex justify-content-between mt-2">
        <span class="small text-muted">Сумма строки:</span>
        <strong id="line_total_${rowId}">0.00</strong>
    </div>

    <label class="form-label small mt-2">Годен до</label>
    <input type="date" class="form-control mb-2 expiry_date">

    <label class="form-label small">Партия</label>
    <input type="text" class="form-control batch">
</div>
`;
            $("#itemsCardWrapper").append(card);
            initSelect2(rowId);
        }

        // === Select2 по остаткам ===
        function initSelect2(id) {
            let selector = `#row_${id} .product_select`;

            $(selector).select2({
                placeholder: "Выберите товар по остаткам",
                width: "100%",
                ajax: {
                    url: "/api/stock/by-location",
                    dataType: "json",
                    delay: 250,
                    data: params => ({
                        location_type: $('#from_type').val(),
                        location_id:   $('#from_id').val(),
                        query:         params.term
                    }),
                    processResults: data => data
                }
            });

            $(selector).on("select2:select", function (e) {
                const data = e.params.data;
                const rid  = $(this).data('id');

                // Остаток и лимит
                const max = data.qty_left || 0;
                $(`#stock_info_${rid}`).text(`Остаток: ${max} ${data.unit || ''}`);
                $(`#row_${rid} .qty`).data('max', max);

                // Автоподстановка цены
                if (data.last_price) {
                    $(`#row_${rid} .price`).val(data.last_price.toFixed(2));
                }

                recalcLineTotal(rid);
                recalcDocTotal();
            });
        }

        // Проверка количества и пересчёт сумм
        $(document).on('input', '.qty, .price', function () {
            const rid = $(this).data('id');
            limitQty(rid);
            recalcLineTotal(rid);
            recalcDocTotal();
        });

        function limitQty(rid) {
            const qtyInput = $(`#row_${rid} .qty`);
            const max = parseFloat(qtyInput.data('max') || 0);
            let val   = parseFloat(qtyInput.val() || 0);

            if (max > 0 && val > max) {
                alert(`Нельзя списать больше, чем остаток (${max})`);
                qtyInput.val(max);
            }
        }

        function recalcLineTotal(rid) {
            const qty   = parseFloat($(`#row_${rid} .qty`).val() || 0);
            const price = parseFloat($(`#row_${rid} .price`).val() || 0);
            const sum   = qty * price;
            $(`#line_total_${rid}`).text(sum.toFixed(2));
        }

        function recalcDocTotal() {
            let total = 0;
            $("#itemsCardWrapper .item-card").each(function(){
                const rid = $(this).attr('id').split('_')[1];
                const line = parseFloat($(`#line_total_${rid}`).text() || 0);
                total += line;
            });
            $("#docTotal").text(total.toFixed(2));
        }

        // Сохранение документа
        function saveTransfer() {
            let items = [];

            $("#itemsCardWrapper .item-card").each(function(){
                const rid = $(this).attr('id').split('_')[1];
                const pid = $(this).find('.product_select').val();
                if (!pid) return;

                items.push({
                    product_id: pid,
                    quantity:   $(this).find('.qty').val(),
                    unit_price: $(this).find('.price').val(),
                    expiry_date:$(this).find('.expiry_date').val(),
                    batch:      $(this).find('.batch').val(),
                });
            });

            if (!items.length) {
                alert("Добавьте хотя бы один товар");
                return;
            }

            $.ajax({
                url: "/api/stock/transfers",
                method: "POST",
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    from_type: $('#from_type').val(),
                    from_id:   $('#from_id').val(),
                    to_type:   $('#to_type').val(),
                    to_id:     $('#to_id').val(),
                    comment:   $('#comment').val(),
                    items:     items
                },
                success: function(res) {
                    if (res.success) {
                        alert("Перемещение сохранено");
                        location.reload();
                    }
                },
                error: function(xhr){
                    if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.message) {
                        alert(xhr.responseJSON.message);
                    } else {
                        alert("Ошибка сохранения");
                    }
                }
            });
        }
    </script>
@endpush
