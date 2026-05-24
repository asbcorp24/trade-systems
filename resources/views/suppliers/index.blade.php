@extends('layouts.app')

@section('content')
    <div class="container py-4">

        <h2 class="mb-4">🚚 Поставщики</h2>

        <button class="btn btn-primary mb-3" onclick="openAddSupplier()">
            ➕ Добавить поставщика
        </button>

        <table class="table table-bordered" id="suppliersTable">
            <thead>
            <tr>
                <th>Название</th>
                <th>Контактное лицо</th>
                <th>Телефон</th>
                <th>Email</th>
                <th width="150">Действия</th>
            </tr>
            </thead>
            <tbody></tbody>
        </table>

    </div>

    @include('suppliers.modal')

@endsection

@push('scripts')
    <script>
        $(document).ready(function(){
            loadSuppliers();
        });

        function loadSuppliers() {
            $.get("/api/suppliers", function(res){
                let html = "";
                res.forEach(s => {
                    html += `
                <tr>
                    <td>${s.name}</td>
                    <td>${s.contact_person ?? ''}</td>
                    <td>${s.phone ?? ''}</td>
                    <td>${s.email ?? ''}</td>
                    <td>
                        <button class="btn btn-sm btn-warning" onclick="openEditSupplier(${s.id}, '${s.name}', '${s.contact_person}', '${s.phone}', '${s.email}', \`${s.address ?? ''}\`)">✏</button>
                        <button class="btn btn-sm btn-danger" onclick="deleteSupplier(${s.id})">🗑</button>
                    </td>
                </tr>
            `;
                });
                $("#suppliersTable tbody").html(html);
            });
        }

        function openAddSupplier() {
            $("#supId").val("");
            $("#supName").val("");
            $("#supContact").val("");
            $("#supPhone").val("");
            $("#supEmail").val("");
            $("#supAddress").val("");

            $("#modalTitle").text("Добавить поставщика");
            $("#supplierModal").modal("show");
        }

        function openEditSupplier(id, name, contact, phone, email, address) {
            $("#supId").val(id);
            $("#supName").val(name);
            $("#supContact").val(contact);
            $("#supPhone").val(phone);
            $("#supEmail").val(email);
            $("#supAddress").val(address);

            $("#modalTitle").text("Редактировать поставщика");
            $("#supplierModal").modal("show");
        }

        $("#saveSupplier").click(function(){
            let id = $("#supId").val();
            let data = {
                _token: $('meta[name="csrf-token"]').attr('content'),
                name: $("#supName").val(),
                contact_person: $("#supContact").val(),
                phone: $("#supPhone").val(),
                email: $("#supEmail").val(),
                address: $("#supAddress").val(),
            };

            let url = id ? `/api/suppliers/${id}` : "/api/suppliers";

            $.post(url, data, function(){
                $("#supplierModal").modal("hide");
                loadSuppliers();
            });
        });

        function deleteSupplier(id) {
            if (!confirm("Удалить поставщика?")) return;

            $.ajax({
                url: `/api/suppliers/${id}`,
                method: "DELETE",
                data: { _token: $('meta[name="csrf-token"]').attr('content') },
                success: function(){
                    loadSuppliers();
                }
            });
        }
    </script>
@endpush
