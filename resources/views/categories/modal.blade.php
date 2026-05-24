<div class="modal fade" id="categoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header">
                <h5 id="modalTitle" class="modal-title">Категория</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <input type="hidden" id="catId">

                <label class="form-label">Название категории</label>
                <input type="text" id="catName" class="form-control mb-3">

                <label class="form-label">Родительская категория</label>
                <select id="catParent" class="form-select"></select>
            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
                <button class="btn btn-success" id="saveCategory">💾 Сохранить</button>
            </div>

        </div>
    </div>
</div>
