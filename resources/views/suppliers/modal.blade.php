<div class="modal fade" id="supplierModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header">
                <h5 id="modalTitle" class="modal-title">Поставщик</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <input type="hidden" id="supId">

                <label class="form-label">Название компании</label>
                <input type="text" id="supName" class="form-control mb-2">

                <label class="form-label">Контактное лицо</label>
                <input type="text" id="supContact" class="form-control mb-2">

                <label class="form-label">Телефон</label>
                <input type="text" id="supPhone" class="form-control mb-2">

                <label class="form-label">Email</label>
                <input type="email" id="supEmail" class="form-control mb-2">

                <label class="form-label">Адрес</label>
                <textarea id="supAddress" class="form-control"></textarea>

            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
                <button class="btn btn-success" id="saveSupplier">💾 Сохранить</button>
            </div>

        </div>
    </div>
</div>
