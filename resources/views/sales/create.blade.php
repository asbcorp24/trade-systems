@extends('layouts.app')

@section('content')
    <div class="container">

        <h2 class="mb-4">🧾 Продажа товара</h2>

        <div class="card mb-3">
            <div class="card-body">

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Магазин</label>
                        <select id="store_id" class="form-select">
                            @foreach($stores as $s)
                                <option value="{{ $s->id }}">{{ $s->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Покупатель</label>
                        <input type="text" id="customer_name" class="form-control" placeholder="ФИО (необязательно)">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Телефон</label>
                        <input type="text" id="customer_phone" class="form-control" placeholder="+7...">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Комментарий</label>
                    <textarea id="comment" class="form-control"></textarea>
                </div>

            </div>
        </div>

        <h4>Товары</h4>

        <div id="itemsWrapper"></div>

        <button class="btn btn-secondary w-100 my-3" onclick="addRow()">➕ Добавить товар</button>

        <div class="card mb-3">
            <div class="card-body">

                <div class="row mb-2">
                    <div class="col-md-4">
                        <label class="form-label">Тип оплаты</label>
                        <select id="payment_type" class="form-select">
                            <option value="cash">Наличные</option>
                            <option value="card" selected>Безнал (эквайринг)</option>
                        </select>
                    </div>
                    <div id="cash_calculator" class="card p-3 mb-3" style="display:none;margin-top: 20px">
                        <label class="form-label">Сколько дал клиент?</label>
                        <input id="cash_given" type="number" class="form-control" step="0.01">

                        <h5 class="mt-2">Сдача: <span id="cash_change">0.00</span> ₽</h5>
                    </div>
                    <div class="col-md-8 text-end align-self-end">
                        <div class="h4 mb-0">
                            Итого: <span id="totalAmount">0.00</span> ₽
                        </div>
                    </div>
                </div>

                <button class="btn btn-success w-100 btn-lg" onclick="saveSale()">✅ Провести продажу</button>

            </div>
        </div>

    </div>
@endsection

@push('head')
    <style>
        .sale-item-card {
            border: 1px solid #ddd;
            border-radius: 10px;
            padding: 12px;
            margin-bottom: 10px;
            background: #fff;
        }
    </style>
@endpush

@push('scripts')
    <script>
        let addedProducts = [];
        let rowId = 0;

        function addRow() {
            rowId++;

            let html = `
<div class="sale-item-card" id="row_${rowId}">
    <div class="d-flex justify-content-between mb-2">
        <strong>Товар #${rowId}</strong>
        <button class="btn btn-sm btn-outline-danger" onclick="removeRow(${rowId})">✖</button>
    </div>

    <div class="mb-2">
        <label class="form-label small">Штрихкод</label>
<div class="input-group">
<input type="text" class="form-control barcode" data-id="${rowId}" id="ig${rowId}" placeholder="Сканируйте или введите">
 <button class="btn btn-outline-primary"
            onclick="BarcodeScanner.open('#ig${rowId}')">
        📷
    </button>
</div>
    </div>

    <div class="mb-2">
        <label class="form-label small">Товар</label>
        <select class="form-select product_select" data-id="${rowId}"></select>
    </div>

    <div class="row mb-2">
        <div class="col-4">
            <label class="form-label small">Кол-во</label>
            <input type="number" class="form-control qty" min="0" step="0.001" value="1">
        </div>
        <div class="col-4">
            <label class="form-label small">Цена</label>
            <input type="number" class="form-control price" min="0" step="0.01" value="0">
        </div>
        <div class="col-4">
            <label class="form-label small">Сумма</label>
            <input type="text" class="form-control line_total" value="0.00" readonly>
        </div>
    </div>
</div>`;

            $('#itemsWrapper').append(html);
            initProductSelect(rowId);
        }

        function removeRow(id) {
            let pid = $(`#row_${id} .product_select`).val();
            addedProducts = addedProducts.filter(x => x !== String(pid)); // ← УДАЛЯЕМ

            $(`#row_${id}`).remove();
            recalcTotal();
        }

        function productAlreadyAdded(productId) {
            return addedProducts.includes(String(productId));
        }

        // Select2 по товарам магазина
        function initProductSelect(id) {
            let selector = `#row_${id} .product_select`;

            $(selector).select2({
                placeholder: 'Выберите товар',
                width: '100%',
                ajax: {
                    url: '/api/sales/products',
                    dataType: 'json',
                    delay: 200,
                    data: params => ({
                        store_id: $('#store_id').val(),
                        query: params.term
                    }),
                    processResults: data => data
                }
            });

            $(selector).on('select2:select', function (e) {
                let p = e.params.data;
                if (productAlreadyAdded(p.id)) {
                    alert("❗ Этот товар уже добавлен.");
                    $(selector).val(null).trigger('change');
                    return;
                }

                addedProducts.push(String(p.id));   // ← ДОБАВЛЯЕМ
                // штрихкод
                $(`#row_${id} .barcode`).val(p.barcode || '');
                // макс. остаток
                $(`#row_${id} .product_select`).data('max', p.qty);
                // цена
                $(`#row_${id} .price`).val(p.unit_price || 0);

                recalcLine(`#row_${id}`);
                recalcTotal();
            });
        }

        // Поиск по штрихкоду
        $(document).on('change', '.barcode', function () {
            let rowId = $(this).data('id');
            let barcode = $(this).val().trim();

            if (!barcode) return;

            $.get('/api/sales/products', {
                store_id: $('#store_id').val(),
                query: barcode
            }, function (data) {

                if (!data.results || !data.results.length) {
                    alert('❗ Товар с таким штрих-кодом в магазине не найден!');
                    return;
                }

                let item = data.results[0]; // <-- найденный товар

                // ==== проверка на дубликат ====
                if (productAlreadyAdded(item.id)) {
                    alert("❗ Этот товар уже добавлен.");
                    return;
                }

              //  addedProducts.push(String(item.id));

                let select = $(`#row_${rowId} .product_select`);

                // ==== корректная вставка в Select2 ====
                let option = new Option(item.text, item.id, true, true);
                select.append(option).trigger('change');

                // ==== триггер select2:select вручную, чтобы сработал весь твой код ====
                select.trigger({
                    type: "select2:select",
                    params: { data: item }
                });

                // === Обновление остатка ===
                $(`#stock_info_${rowId}`).text(`Остаток: ${item.qty}`);

                // === лимит количества ===
                $(`#row_${rowId} .qty`).data('max', parseFloat(item.qty));

                // === цена ===
                if (item.unit_price) {
                    $(`#row_${rowId} .price`).val(parseFloat(item.unit_price).toFixed(2));
                }

                // === пересчёт суммы строки ===
                recalcLineTotal(rowId);
                recalcDocTotal();
            });
        });


        // Пересчёт строки
        function recalcLine(selector) {
            let card = $(selector);
            let max = parseFloat(card.find('.product_select').data('max')) || 0;
            let qtyEl = card.find('.qty');
            let qty = parseFloat(qtyEl.val()) || 0;

            if (max > 0 && qty > max) {
                alert("❗ Нельзя продавать больше, чем есть в наличии: " + max);
                qty = max;
                qtyEl.val(max);
            }

            let price = parseFloat(card.find('.price').val()) || 0;
            let line = qty * price;
            card.find('.line_total').val(line.toFixed(2));
        }

        // Общий пересчёт
        function recalcTotal() {
            let total = 0;
            $('#itemsWrapper .sale-item-card').each(function () {
                let line = parseFloat($(this).find('.line_total').val()) || 0;
                total += line;
            });
            $('#totalAmount').text(total.toFixed(2));
        }

        // Локальный обработчик изменений qty/price
        $(document).on('input', '.qty, .price', function () {
            let card = $(this).closest('.sale-item-card');
            recalcLine('#' + card.attr('id'));
            recalcTotal();
        });

        // Сохранение продажи
        function saveSale() {
            let items = [];
            let payType = $("#payment_type").val();

// === НАЛИЧНЫЕ ===
            if (payType === "cash") {

                let total = parseFloat($("#sum_total").text());
                let given = parseFloat($("#cash_given").val() || 0);

                if (given < total) {
                    alert("Недостаточно денег. Клиент должен дать сумму ≥ стоимости чека.");
                    return;
                }

                // сдача уже рассчитана — можно сохранить в БД если нужно
            }
            $('#itemsWrapper .sale-item-card').each(function () {
                let card = $(this);
                let pid = card.find('.product_select').val();
                let qty = parseFloat(card.find('.qty').val()) || 0;
                let price = parseFloat(card.find('.price').val()) || 0;

                if (!pid || qty <= 0) return;

                items.push({
                    product_id: pid,
                    quantity: qty,
                    unit_price: price
                });
            });

            if (!items.length) {
                alert('Добавьте хотя бы один товар.');
                return;
            }

            $.ajax({
                url: '/api/sales',
                method: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    store_id: $('#store_id').val(),
                    items: items,
                    payment_type: $('#payment_type').val(),
                    customer_name: $('#customer_name').val(),
                    customer_phone: $('#customer_phone').val(),
                    comment: $('#comment').val()
                },
                success: function (res) {
                    if (res.success) {
                        alert(`Продажа проведена.
                        Документ: ${res.document_number}
                        Сумма: ${(res.total ?? 0).toFixed(2)}`);

                        printReceiptKKM({
                            cashier: res.cashier,
                            cashier_inn: res.cashier_inn,
                            customer_name: $('#customer_name').val(),
                            customer_phone: $('#customer_phone').val(),
                            payment_type: $('#payment_type').val(),
                            total: res.total,
                            items: res.items
                        });
                        location.reload();
                    }
                },
                error: function (xhr) {
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        alert('Ошибка: ' + xhr.responseJSON.message);
                    } else {
                        alert('Ошибка при сохранении продажи.');
                    }
                }
            });
        }
    </script>
    <script>
        function printReceiptKKM(sale) {

            let CheckStrings = [];

            sale.items.forEach(item => {
                CheckStrings.push({
                    Register: {
                        Name: item.name,
                        Quantity: item.quantity,
                        Price: item.unit_price,
                        Amount: item.total,
                        Tax: -1,                    // НДС не облагается
                        SignMethodCalculation: 4,   // Полный расчёт
                        SignCalculationObject: 1,   // Товар
                        MeasureOfQuantity: 0        // шт.
                    }
                });
            });
            let uuid = 'xxxx-4xxx-yxxx-xxxx'.replace(/[xy]/g, function(c) {
                let r = Math.random() * 16 | 0;
                let v = c === 'x' ? r : (r & 0x3 | 0x8);
                return v.toString(16);
            });
            let Data = {
                Command: "RegisterCheck",

                Timeout: 30,
                IdCommand: uuid,

                IsFiscalCheck: true,
                TypeCheck: 0, // 0 — продажа

                CashierName: sale.cashier,
                CashierVATIN: sale.cashier_inn || "",

                ClientAddress: sale.customer_phone || "",
                ClientInfo: sale.customer_name || "",

                CheckStrings: CheckStrings,

                Cash: sale.payment_type === 'cash' ? sale.total : 0,
                ElectronicPayment: sale.payment_type === 'card' ? sale.total : 0,
                AdvancePayment: 0,
                Credit: 0,
                CashProvision: 0
            };

            // Удаляем маркеры агентских данных
            Data.AgentSign = null;
            Data.AgentData = null;
            Data.PurveyorData = null;
            Data.CheckStrings.forEach(cs => {
                if (cs.Register) {
                    cs.Register.AgentSign = null;
                    cs.Register.AgentData = null;
                    cs.Register.PurveyorData = null;
                }
            });

            console.log("Отправка в ККМ:", Data);

            $.ajax({
                url: "http://localhost:5893/Execute",  // адрес ККМ-Server
                method: "POST",
                data: JSON.stringify(Data),
                contentType: "application/json",
                success: res => {
                    if (res.Error) {
                        alert("Ошибка печати чека: " + res.Error);
                    } else {
                        alert("Чек напечатан успешно!\n№: " + res.CheckNumber);
                    }
                },
                error: err => {
                    alert("Ошибка связи с ККМ-Server");
                }
            });
        }
        // Переключение режима оплаты
        $(document).on('change', '#payment_type', function() {
            if ($(this).val() === 'cash') {
                $("#cash_calculator").show();
            } else {
                $("#cash_calculator").hide();
            }
        });

        // Калькулятор сдачи
        $(document).on('input', '#cash_given', function() {
            let given = parseFloat($(this).val() || 0);
            let total = parseFloat($("#totalAmount").text() || 0); // сумма чека
            let change = (given - total);

            $("#cash_change").text(change.toFixed(2));
        });
        // Элемент, за которым следим
        const totalAmountEl = document.getElementById("totalAmount");

        // Наблюдатель
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(m) {
                let newValue = totalAmountEl.innerText.trim();
                console.log("НОВАЯ СУММА:", newValue);

                // вызываем любую твою функцию:
                onTotalAmountChanged(parseFloat(newValue) || 0);
            });
        });

        // Настройка наблюдения
        observer.observe(totalAmountEl, {
            characterData: true,
            childList: true,
            subtree: true
        });

        // Твоя функция
        function onTotalAmountChanged(sum) {
            console.log("Функция поймала изменение:", sum);
            $('#cash_given').trigger('input');
        }
    </script>

@endpush
