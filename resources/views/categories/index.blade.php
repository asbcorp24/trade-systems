@extends('layouts.app')

@section('content')
    <div class="container py-4">

        <h2 class="mb-4">Категории товаров (дерево)</h2>

        <button class="btn btn-primary mb-3" onclick="openAddModal()">➕ Добавить категорию</button>

        <div id="categoriesTree"></div>

    </div>

    @include('categories.modal')

@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            loadCategories();
        });

        // Загрузка дерева категорий
        function loadCategories() {
            $.get("/api/categories", function (cats) {
                renderTree(cats, $("#categoriesTree"));
            });
        }

        // Рендер дерева
        function renderTree(categories, container) {
            let html = "<ul>";
            categories.forEach(cat => {
                html += `
            <li>
                <b>${cat.name}</b>

                <button class="btn btn-sm btn-warning ms-2" onclick="openEditModal(${cat.id}, '${cat.name}', ${cat.parent_id})">✏</button>
                <button class="btn btn-sm btn-danger" onclick="deleteCategory(${cat.id})">🗑</button>
        `;

                if (cat.children && cat.children.length > 0) {
                    html += renderSub(cat.children);
                }

                html += "</li>";
            });
            html += "</ul>";

            container.html(html);
        }

        function renderSub(children) {
            let html = "<ul>";
            children.forEach(ch => {
                html += `
            <li>
                <b>${ch.name}</b>

                <button class="btn btn-sm btn-warning ms-2" onclick="openEditModal(${ch.id}, '${ch.name}', ${ch.parent_id})">✏</button>
                <button class="btn btn-sm btn-danger" onclick="deleteCategory(${ch.id})">🗑</button>
        `;
                if (ch.children && ch.children.length > 0) {
                    html += renderSub(ch.children);
                }
                html += "</li>";
            });
            html += "</ul>";
            return html;
        }

        // Открыть модал добавления
        function openAddModal() {
            $("#catId").val("");
            $("#catName").val("");
            $("#catParent").val("");
            $("#modalTitle").text("Добавить категорию");
            loadParentSelect();
            $("#categoryModal").modal("show");
            setTimeout(() => $("#catName").focus(), 300);
        }

        // Открыть модал редактирования
        function openEditModal(id, name, parent) {
            $("#catId").val(id);
            $("#catName").val(name);
            $("#catParent").val(parent);
            $("#modalTitle").text("Редактировать категорию");
            loadParentSelect(id);
            $("#categoryModal").modal("show");
            setTimeout(() => $("#catName").focus(), 300);
        }

        // Загрузка списка категорий в select родителя
        function loadParentSelect(currentId = null) {
            $.get("/api/categories", function (categories) {
                let flat = flatten(categories);
                let html = '<option value="">(без родителя)</option>';

                flat.forEach(cat => {
                    if (cat.id !== currentId) {
                        html += `<option value="${cat.id}">${cat.prefix}${cat.name}</option>`;
                    }
                });

                $("#catParent").html(html);
                setTimeout(() => $("#catName").focus(), 300);
            });
        }

        function flatten(nodes, prefix = "") {
            let list = [];
            nodes.forEach(n => {
                list.push({id: n.id, name: n.name, prefix});
                if (n.children) {
                    list = list.concat(flatten(n.children, prefix + "— "));
                }
            });
            return list;
        }

        // Сохранить категорию
        $("#saveCategory").click(function () {
            let id = $("#catId").val();
            let name = $("#catName").val();
            let parent_id = $("#catParent").val();

            let url = id ? `/api/categories/${id}` : `/api/categories`;

            $.post(url, {
                _token: $('meta[name="csrf-token"]').attr('content'),
                name,
                parent_id
            }, function () {
                $("#categoryModal").modal("hide");
                loadCategories();
            });
        });

        // Удаление категории
        function deleteCategory(id) {
            if (!confirm("Удалить категорию?")) return;

            $.ajax({
                url: `/api/categories/${id}`,
                method: "DELETE",
                data: {_token: $('meta[name="csrf-token"]').attr('content')},
                success: function () {
                    loadCategories();
                }
            });
        }
    </script>
@endpush
