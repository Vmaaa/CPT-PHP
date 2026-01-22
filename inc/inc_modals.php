<div
  id="modal-new-class"
  class="modal-backdrop"
  aria-hidden="true"
  style="display:none">
  <div class="modal">
    <div class="modal-header">
      <h3>Nueva clase</h3>
      <button class="modal-close" aria-label="Cerrar">×</button>
    </div>

    <div class="modal-body">
      <label>Nombre de la clase *</label>
      <input type="text" id="new-class-name" class="form-control" required />

      <label>Carrera *</label>
      <select id="new-class-career" class="form-control" required></select>

      <label>Profesores</label>
      <select id="new-class-professors" multiple class="form-select-multiple"></select>
    </div>

    <div class="modal-footer">
      <button id="cancel-new-class" class="btn btn-secondary">
        Cancelar
      </button>
      <button class="btn btn-primary" onclick="createClass()">
        Guardar
      </button>
    </div>
  </div>
</div>

<div
  id="modal-edit-class"
  class="modal-backdrop"
  aria-hidden="true"
  style="display:none">
  <div class="modal">
    <div class="modal-header">
      <h3>Editar clase</h3>
      <button class="modal-close" aria-label="Cerrar">×</button>
    </div>

    <div class="modal-body">
      <input type="hidden" id="edit-class-id" class="form-control" />

      <label>Nombre de la clase *</label>
      <input type="text" id="edit-class-name" class="form-control" required />

      <label>Carrera *</label>
      <select id="edit-class-career" disabled class="form-control" required></select>

      <label>Profesores</label>
      <select id="edit-class-professors" multiple class="form-select-multiple"></select>
    </div>

    <div class="modal-footer">
      <button id="cancel-edit-class" class="btn btn-secondary">
        Cancelar
      </button>
      <button class="btn btn-primary" onclick="updateClass()">
        Guardar cambios
      </button>
    </div>
  </div>
</div>
