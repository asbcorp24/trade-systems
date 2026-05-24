<div class="modal fade" id="warehouseModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header">
                <h5 id="modalTitle" class="modal-title">Склад</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <input type="hidden" id="whId">

                <label>Название склада</label>
                <input type="text" id="whName" class="form-control mb-3">

                <label>Код склада</label>
                <input type="text" id="whCode" class="form-control">
            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
                <button class="btn btn-success" id="saveWarehouse">💾 Сохранить</button>
            </div>

        </div>
    </div>
</div>
