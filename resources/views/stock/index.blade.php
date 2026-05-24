@extends('layouts.app')

@section('content')
    <div class="container">

        <h2 class="mb-4">📦 Остатки товара</h2>

        <div class="card mb-3">
            <div class="card-body">

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Локация</label>
                        <select id="location_type" class="form-select">
                            <option value="warehouse">Склад</option>
                            <option value="store">Магазин</option>
                        </select>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Выбор локации</label>
                        <select id="location_id" class="form-select"></select>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Поиск по ШК</label>
                        <div class="input-group">
                        <input id="search" type="text" class="form-control" placeholder="Название / Штрихкод">
                            <button class="btn btn-outline-primary"
                                    onclick="BarcodeScanner.open('#search')">
                                📷
                            </button>
                        </div>
                    </div>
                </div>

                <button class="btn btn-primary w-100" onclick="loadStock()">🔍 Показать остатки</button>

            </div>
        </div>

        <h4>Результаты</h4>
<div class="table-responsive">
        <table class="table table-bordered table-hover" id="stockTable">
            <thead class="table-light">
            <tr>
                <th>Штрихкод</th>
                <th>Наименование</th>
                <th>Остаток</th>
                <th>Ед.</th>
                <th>Статус</th>
                <th>Последнее движение</th>
                <th width="60"></th>
            </tr>
            </thead>
            <tbody></tbody>
        </table>
</div>
    </div>
@endsection


@push('scripts')
    <script>
        const warehouses = @json($warehouses);
        const stores     = @json($stores);

        function fillLocationSelect() {
            let type = $('#location_type').val();
            let sel  = $('#location_id');
            sel.empty();

            let list = type === 'warehouse' ? warehouses : stores;
            list.forEach(item => sel.append(new Option(item.name, item.id)));
        }

        fillLocationSelect();
        $('#location_type').on('change', fillLocationSelect);


        function loadStock() {
            $("#stockTable tbody").html('<tr><td colspan="7" class="text-center">Загрузка...</td></tr>');

            $.get("/api/stock/list", {
                location_type: $('#location_type').val(),
                location_id:   $('#location_id').val(),
                search:        $('#search').val()
            }, function(res){

                let rows = '';
                let data = res.items ?? []; // <--- ПРАВИЛЬНО

                if (!data.length) {
                    rows = '<tr><td colspan="7" class="text-center">Нет данных</td></tr>';
                } else {
                    data.forEach(p => {

                        // вычисляем статус
                        let status = `<span class="badge bg-success">OK</span>`;
                        let qty = parseInt(p.qty || 0, 10);

                        if (qty < parseInt(p.min_stock || 0, 10)) {
                            status = `<span class="badge bg-danger">Мало</span>`;
                        }
                        if (parseInt(p.max_stock || 0, 10) > 0 && qty > parseInt(p.max_stock || 0, 10)) {
                            status = `<span class="badge bg-warning text-dark">Много</span>`;
                        }

                        rows += `
<tr>
    <td>${p.barcode ?? '-'}</td>
    <td>${p.name}</td>
    <td>${parseInt(p.qty || 0, 10)}</td>
    <td>${p.unit}</td>

    <td>${status}</td>

    <td>${p.last_move ?? ''}</td>

    <td>
        <a href="/stock/history/${p.product_id}"
           class="btn btn-sm btn-outline-secondary">👁</a>
    </td>
</tr>`;
                    });
                }

                $("#stockTable tbody").html(rows);
            });
        }
    </script>
@endpush
