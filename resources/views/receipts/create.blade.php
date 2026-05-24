@extends('layouts.app')

@section('content')
    <div class="container">

        <h2 class="mb-4">📥 Приёмка товара</h2>

        <!-- Выбор склада -->
        <div class="mb-3">
            <label class="form-label">Склад</label>
            <select id="warehouse_id" class="form-select">
                @foreach(\App\Models\Warehouse::all() as $w)
                    <option value="{{ $w->id }}">{{ $w->name }}</option>
                @endforeach
            </select>
        </div>

        <!-- Поставщик -->
        <div class="mb-3">
            <label class="form-label">Поставщик</label>
            <input type="text" id="supplier_name" class="form-control" placeholder="Введите название">
        </div>

        <!-- Комментарий -->
        <div class="mb-3">
            <label class="form-label">Комментарий</label>
            <textarea id="comment" class="form-control"></textarea>
        </div>

        <hr>

        <h4>Товары</h4>

        <div class="input-group mb-3">
            <input id="quickBarcode" class="form-control form-control-lg"
                   placeholder="Сканируйте штрихкод товара">
            <button class="btn btn-primary" type="button" onclick="scanIntoNewRow()">Сканировать камерой</button>
        </div>

        <!-- Карточки позиций -->
        <div id="itemsCardWrapper"></div>

        <button class="btn btn-primary w-100 mt-3" onclick="addRow()">➕ Добавить товар</button>

        <!-- Сумма документа -->
        <div class="alert alert-info mt-3 text-end">
            Сумма по документу: <strong id="docTotal">0.00</strong> ₽
        </div>

        <button class="btn btn-success btn-lg w-100" onclick="saveReceipt()">💾 Сохранить документ</button>

    </div>

    <!-- Модальное окно сканера -->
    <div class="modal fade" id="scannerModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">Сканирование штрихкода</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                            onclick="stopScan()"></button>
                </div>

                <div class="modal-body text-center">
                    <video id="videoPreview" style="width:100%; max-height:300px;"></video>
                    <p class="mt-2 text-muted small">Наведите камеру на штрихкод EAN-13.</p>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal" onclick="stopScan()">Закрыть</button>
                </div>

            </div>
        </div>
    </div>
@endsection

@push('head')
    <style>
        .item-card {
            position: relative;
            border: 1px solid #ddd;
            background: #fff;
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 15px;
            box-shadow: 0 3px 6px rgba(0,0,0,0.08);
            transition: transform 0.2s ease;
            overflow: hidden;
        }
        .swipe-delete {
            position: absolute;
            right: -100px;
            top: 0;
            height: 100%;
            width: 100px;
            background: #dc3545;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            border: none;
            transition: right 0.2s ease;
        }
        .item-card.swiped {
            transform: translateX(-100px);
        }
        .item-card.swiped .swipe-delete {
            right: 0;
        }

        .scan-btn {
            font-size: 18px;
            padding: 12px;
            width: 100%;
            border-radius: 10px;
        }

        .row_sum_box {
            background: #f1f3f5;
            border-radius: 6px;
            padding: 6px 10px;
            text-align: right;
            font-weight: 600;
        }
    </style>
@endpush

@push('scripts')
    <script>
        let rowId = 0;
        let touchXStart = 0;
        let touchXEnd = 0;
        let codeReader = null;
        let activeScanRowId = null;

        // Touch events (swipe)
        function touchStart(e, id) { touchXStart = e.touches[0].clientX; }
        function touchMove(e, id) { touchXEnd = e.touches[0].clientX; }
        function touchEnd(e, id) {
            let diff = touchXStart - touchXEnd;
            if (diff > 40) $(`#card_${id}`).addClass("swiped");
            if (diff < -40) $(`#card_${id}`).removeClass("swiped");
        }
        function deleteCard(id) { $(`#card_${id}`).remove(); recalcTotal(); }

        // === Добавление карточки товара ===
        function addRow(productData = null) {
            rowId++;

            let html = `
<div class="item-card" id="card_${rowId}"
     ontouchstart="touchStart(event, ${rowId})"
     ontouchmove="touchMove(event, ${rowId})"
     ontouchend="touchEnd(event, ${rowId})">

    <button type="button" class="swipe-delete" onclick="deleteCard(${rowId})">Удалить</button>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Товар #${rowId}</h5>
        <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteCard(${rowId})">Удалить</button>
    </div>

    <label class="form-label">Штрихкод</label>
    <input class="form-control mb-2 barcode" data-id="${rowId}" placeholder="Сканируйте или введите">

    <button class="btn btn-primary scan-btn my-2" onclick="startScan(${rowId})">📷 Сканировать</button>

    <label class="form-label">Товар</label>
    <select class="form-select mb-3 product_select" data-id="${rowId}"></select>

    <div class="row">
        <div class="col-4">
            <label class="form-label">Кол-во</label>
            <input type="number" class="form-control mb-2 qty" min="0" step="1">
        </div>
        <div class="col-4">
            <label class="form-label">Цена</label>
            <input type="number" class="form-control mb-2 price" min="0" step="0.01">
        </div>
        <div class="col-4">
            <label class="form-label">Сумма</label>
            <div class="row_sum_box">
                <span class="row_sum">0.00</span> ₽
            </div>
        </div>
    </div>

    <label class="form-label mt-2">Годен до</label>
    <input type="date" class="form-control mb-2 expiry_date">

    <div class="form-check my-2">
        <input type="checkbox" class="form-check-input is_used" id="used_${rowId}">
        <label class="form-check-label" for="used_${rowId}">Товар б/у</label>
    </div>

    <label class="form-label">Партия</label>
    <input type="text" class="form-control batch">

</div>`;

            $("#itemsCardWrapper").append(html);
            initSelect2(rowId);
            if (productData) {
                fillProduct(rowId, productData);
            }

            return rowId;
        }

        // === Select2 (поиск + выбор товара) ===
        function initSelect2(id) {
            let selector = `#card_${id} .product_select`;

            $(selector).select2({
                placeholder: "Выберите товар",
                width: "100%",
                ajax: {
                    url: "/api/products/search",
                    dataType: "json",
                    delay: 200,
                    data: p => ({ query: p.term }),
                    processResults: d => d,
                }
            });

            // После выбора: заполняем штрихкод и подтягиваем последнюю цену
            $(selector).on("select2:select", function (e) {
                const data = e.params.data;
                const card = $(`#card_${id}`);
                card.find(".barcode").val(data.barcode);

                loadLastPrice(id, data.id);
            });
        }

        function loadLastPrice(id, productId) {
            const card = $(`#card_${id}`);

            $.get(`/api/products/${productId}/last-price`, function (res) {
                if (res.success && res.price !== null && res.price !== undefined) {
                    card.find(".price").val(parseFloat(res.price).toFixed(2));
                    recalcRow(id);
                }
            });
        }

        function fillProduct(id, item) {
            const card = $(`#card_${id}`);
            const select = card.find(".product_select");
            const text = item.text || `${item.name} (${item.barcode || ""})`;

            card.find(".barcode").val(item.barcode || "");
            select.append(new Option(text, item.id, true, true)).trigger("change");
            loadLastPrice(id, item.id);

            if (!card.find(".qty").val()) {
                card.find(".qty").val(1);
            }

            recalcRow(id);
        }

        function receiveBarcode(barcode, targetRowId = null) {
            barcode = (barcode || "").trim();
            if (!barcode) return;

            const existingInput = $("#itemsCardWrapper .barcode").filter(function () {
                return $(this).val().trim() === barcode;
            }).first();

            if (existingInput.length) {
                const card = existingInput.closest(".item-card");
                const id = card.attr("id").split("_")[1];

                if (!targetRowId || String(id) !== String(targetRowId)) {
                    const qtyInput = card.find(".qty");
                    const qty = parseInt((qtyInput.val() || "0").replace(',', '.'), 10) || 0;
                    qtyInput.val(qty + 1);
                    recalcRow(id);

                    const targetCard = targetRowId ? $(`#card_${targetRowId}`) : $();
                    if (targetCard.length && !targetCard.find(".product_select").val()) {
                        deleteCard(targetRowId);
                    }

                    return;
                }
            }

            $.get("/api/products/barcode/" + encodeURIComponent(barcode), function (res) {
                if (res.success && res.product) {
                    const item = {
                        id: res.product.id,
                        text: res.product.name + " (" + (res.product.barcode || "") + ")",
                        name: res.product.name,
                        barcode: res.product.barcode,
                    };
                    const id = targetRowId || addRow();
                    fillProduct(id, item);
                } else {
                    alert("Товар не найден!");
                }
            });
        }

        function scanIntoNewRow() {
            activeScanRowId = null;
            startScan(null);
        }

        // === Поиск товара по штрихкоду ===
        $(document).on("change", ".barcode", function() {
            let id = $(this).data("id");
            let barcode = $(this).val();

            receiveBarcode(barcode, id);
        });

        $("#quickBarcode").on("change keydown", function(e) {
            if (e.type === "keydown" && e.key !== "Enter") return;
            e.preventDefault();

            receiveBarcode($(this).val());
            $(this).val("").focus();
        });

        // === Пересчёт суммы строки и документа ===
        function recalcRow(id) {
            let card = $(`#card_${id}`);
            let qty   = parseInt((card.find(".qty").val()   || "0").replace(',', '.'), 10) || 0;
            let price = parseFloat((card.find(".price").val() || "0").replace(',', '.')) || 0;
            let sum   = qty * price;
            card.find(".row_sum").text(sum.toFixed(2));
            recalcTotal();
        }

        function recalcTotal() {
            let total = 0;
            $("#itemsCardWrapper .item-card").each(function () {
                let s = parseFloat($(this).find(".row_sum").text().replace(',', '.')) || 0;
                total += s;
            });
            $("#docTotal").text(total.toFixed(2));
        }

        $(document).on("input change", ".qty, .price", function() {
            const id = $(this).closest(".item-card").attr("id").split("_")[1];
            recalcRow(id);
        });

        // === ZXing WebScanner EAN-13 ===
        async function startScan(id) {
            activeScanRowId = id;

            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                alert("Браузер не поддерживает доступ к камере.");
                return;
            }

            if (!codeReader && window.ZXing && ZXing.BrowserMultiFormatReader) {
                codeReader = new ZXing.BrowserMultiFormatReader();
            }

            if (!codeReader) {
                alert("Библиотека ZXing не загружена.");
                return;
            }

            const modalEl = document.getElementById('scannerModal');
            const modal = new bootstrap.Modal(modalEl);
            modal.show();

            codeReader.decodeFromVideoDevice(null, 'videoPreview', (result, err) => {
                if (result) {
                    const code = result.getText();
                    if (activeScanRowId) {
                        const input = $(`#card_${activeScanRowId} .barcode`);
                        input.val(code).trigger("change");
                    } else {
                        receiveBarcode(code);
                        $("#quickBarcode").val("").focus();
                    }
                    stopScan();
                }
            });
        }

        function stopScan() {
            if (codeReader) {
                codeReader.reset();
            }
            const modalEl = document.getElementById('scannerModal');
            const modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) modal.hide();
        }

        // === Сохранение документа ===
        function saveReceipt() {
            let items = [];

            $("#itemsCardWrapper .item-card").each(function () {
                let row = $(this);
                let pid = row.find(".product_select").val();
                if (!pid) return;

                items.push({
                    product_id: pid,
                    barcode: row.find(".barcode").val(),
                    quantity: row.find(".qty").val(),
                    unit_price: row.find(".price").val(),
                    is_used: row.find(".is_used").is(":checked") ? 1 : 0,
                    expiry_date: row.find(".expiry_date").val(),
                    batch: row.find(".batch").val(),
                });
            });

            if (!items.length) {
                alert("Добавьте хотя бы один товар!");
                return;
            }

            $.post("/api/receipts", {
                _token: $('meta[name="csrf-token"]').attr('content'),
                warehouse_id: $("#warehouse_id").val(),
                supplier_name: $("#supplier_name").val(),
                comment: $("#comment").val(),
                items: items
            }, res => {
                if (res.success) {
                    alert("Документ сохранён!");
                    location.reload();
                }
            });
        }

    </script>
@endpush
