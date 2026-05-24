@extends('layouts.app')

@section('content')
    <div class="container">
        <h3 class="mb-3">🏬 Магазины</h3>

        <button class="btn btn-success mb-3" onclick="openCreateModal()">➕ Добавить магазин</button>

        <div class="card">
            <div class="card-body p-0">
                <table class="table table-striped mb-0" id="storesTable">
                    <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Код</th>
                        <th>Название</th>
                        <th>Адрес</th>
                        <th>Телефон</th>
                        <th>Активен</th>
                        <th width="120">Действия</th>
                    </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Модалка --}}
    <div class="modal fade" id="storeModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title" id="storeModalTitle">Магазин</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <input type="hidden" id="store_id">

                    <div class="mb-2">
                        <label class="form-label">Код</label>
                        <input type="text" id="store_code" class="form-control">
                    </div>

                    <div class="mb-2">
                        <label class="form-label">Название</label>
                        <input type="text" id="store_name" class="form-control">
                    </div>

                    <div class="mb-2">
                        <label class="form-label">Адрес</label>
                        <input type="text" id="store_address" class="form-control">
                    </div>

                    <div class="mb-2">
                        <label class="form-label">Телефон</label>
                        <input type="text" id="store_phone" class="form-control">
                    </div>

                    <div class="form-check mt-2">
                        <input class="form-check-input" type="checkbox" id="store_active" checked>
                        <label class="form-check-label" for="store_active">
                            Активен
                        </label>
                    </div>

                    <div class="alert alert-danger mt-3 d-none" id="storeErrors"></div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <button class="btn btn-primary" onclick="saveStore()">Сохранить</button>
                </div>

            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        let storeModal;

        $(function () {
            storeModal = new bootstrap.Modal(document.getElementById('storeModal'));
            loadStores();
        });

        function loadStores() {
            $.get("/api/stores", function(res){
                if (!res.success) return;

                let tbody = $("#storesTable tbody");
                tbody.empty();

                res.data.forEach(s => {
                    tbody.append(`
<tr data-id="${s.id}">
    <td>${s.id}</td>
    <td>${s.code}</td>
    <td>${s.name}</td>
    <td>${s.address ?? ''}</td>
    <td>${s.phone ?? ''}</td>
    <td>${s.is_active ? '✅' : '⛔'}</td>
    <td>
        <button class="btn btn-sm btn-primary" onclick="editStore(${s.id})">✏</button>
        <button class="btn btn-sm btn-danger" onclick="deleteStore(${s.id})">🗑</button>
    </td>
</tr>`);
                });
            });
        }

        function openCreateModal() {
            $("#store_id").val('');
            $("#store_code").val('');
            $("#store_name").val('');
            $("#store_address").val('');
            $("#store_phone").val('');
            $("#store_active").prop('checked', true);
            $("#storeErrors").addClass('d-none').text('');
            $("#storeModalTitle").text('Новый магазин');
            storeModal.show();
        }

        function editStore(id) {
            let row = $(`#storesTable tbody tr[data-id="${id}"]`);
            $("#store_id").val(id);
            $("#store_code").val(row.children().eq(1).text());
            $("#store_name").val(row.children().eq(2).text());
            $("#store_address").val(row.children().eq(3).text());
            $("#store_phone").val(row.children().eq(4).text());
            $("#store_active").prop('checked', row.children().eq(5).text().trim() === '✅');
            $("#storeErrors").addClass('d-none').text('');
            $("#storeModalTitle").text('Редактировать магазин');
            storeModal.show();
        }

        function saveStore() {
            let id = $("#store_id").val();
            let url = "/api/stores" + (id ? `/${id}` : '');
            let method = "POST";

            $.ajax({
                url: url,
                method: method,
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    code: $("#store_code").val(),
                    name: $("#store_name").val(),
                    address: $("#store_address").val(),
                    phone: $("#store_phone").val(),
                    is_active: $("#store_active").is(':checked') ? 1 : 0,
                },
                success: function(res) {
                    if (res.success) {
                        storeModal.hide();
                        loadStores();
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        let errs = xhr.responseJSON.errors;
                        let msg = [];
                        Object.keys(errs).forEach(k => msg.push(errs[k].join('<br>')));
                        $("#storeErrors").removeClass('d-none').html(msg.join('<br>'));
                    }
                }
            });
        }

        function deleteStore(id) {
            if (!confirm("Удалить магазин?")) return;
            $.ajax({
                url: "/api/stores/" + id,
                method: "DELETE",
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(res){
                    if (res.success) loadStores();
                }
            });
        }
    </script>
@endpush
