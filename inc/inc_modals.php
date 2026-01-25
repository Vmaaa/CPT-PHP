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

<div
  id="modal-assignment"
  class="modal-backdrop"
  aria-hidden="true"
  style="display:none">

  <div class="modal">
    <div class="modal-header">
      <h3 id="assignment-modal-title">Nueva asignación</h3>
      <button class="modal-close" aria-label="Cerrar">×</button>
    </div>

    <div class="modal-body">
      <input type="hidden" id="assignment-id" />
      <input type="hidden" id="assignment-class-id" />

      <label>Título *</label>
      <input
        type="text"
        id="assignment-title"
        class="form-control"
        required />

      <label>Descripción</label>
      <textarea
        id="assignment-description"
        class="form-control"
        rows="3"></textarea>

      <label>Fecha de entrega *</label>
      <input
        type="datetime-local"
        id="assignment-due-date"
        class="form-control"
        required />

      <label>
        Archivo (PDF, máx 5 MB)
        <span id="current-assignment-file" class="text-muted"></span>
      </label>

      <input
        type="file"
        id="assignment-file"
        class="form-control"
        accept="application/pdf" />

      <small class="text-muted">
        En edición, subir un archivo reemplazará el actual
      </small>
    </div>

    <div class="modal-footer">
      <button id="cancel-assignment" class="btn btn-secondary">
        Cancelar
      </button>
      <button class="btn btn-primary" onclick="submitAssignment()">
        Guardar
      </button>
    </div>
  </div>
</div>

<div
  id="modal-edit-students"
  class="modal-backdrop"
  aria-hidden="true"
  style="display:none">

  <div class="modal">
    <div class="modal-header">
      <h3>Editar alumnos</h3>
      <button class="modal-close" aria-label="Cerrar">×</button>
    </div>

    <div class="modal-body">
      <input type="hidden" id="edit-students-class-id" />

      <label>Alumnos actuales</label>
      <select
        id="current-students"
        multiple
        class="form-select-multiple">
      </select>

      <label>Alumnos disponibles</label>
      <select
        id="available-students"
        multiple
        class="form-select-multiple">
      </select>

      <small class="text-muted">
        Selecciona los alumnos que pertenecerán a la clase
      </small>
    </div>

    <div class="modal-footer">
      <button id="cancel-edit-students" class="btn btn-secondary">
        Cancelar
      </button>
      <button class="btn btn-primary" onclick="saveClassStudents()">
        Guardar cambios
      </button>
    </div>
  </div>
</div>
