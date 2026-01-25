<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../inc/inc_auth.php';


$activePage = "PROYECTOS_ADMIN";
$pageTitle  = "Proyectos Terminales Registrados";
$pageScript = "projects_admin.js";
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <?php require_once __DIR__ . '/../inc/inc_head.php'; ?>
  <title><?= $pageTitle ?> - <?= $SYSTEM_NAME ?></title>
  <link rel="stylesheet" href="/CPT/assets/css/pages/projects_admin.css">
</head>

<body>
  <div id="app-container" class="app-container">

    <?php require_once __DIR__ . '/../inc/inc_sidebar.php'; ?>

    <div class="main-content">
      <main class="main-content-inner" style="padding:20px">
        <h1 class="page-title"><?= $pageTitle ?></h1>
        <div class="page-header">
          <p class="page-subtitle">
            Administración y asignación de revisores de proyectos.
          </p>
        </div>

        <div id="projects-container" class="cards-grid">
          <p>Cargando proyectos...</p>
        </div>

      </main>
    </div>
  </div>

  <!-- MODAL ASIGNAR REVISORES -->
  <div id="assignModal" class="modal-overlay" style="display:none">
    <div class="modal-card">

      <div class="modal-header">
        <h3>Asignar revisores</h3>
        <button class="modal-close" onclick="closeAssignModal()">×</button>
      </div>

      <div class="modal-body">
        <input type="hidden" id="modal_project_id">

        <div class="form-group">
          <label>Revisor 1</label>
          <select id="reviewer1" class="form-control"></select>
        </div>

        <div class="form-group">
          <label>Revisor 2</label>
          <select id="reviewer2" class="form-control"></select>
        </div>

        <div class="form-group">
          <label>Revisor 3</label>
          <select id="reviewer3" class="form-control"></select>
        </div>
      </div>

      <div class="modal-footer">
        <button class="btn-secondary" onclick="closeAssignModal()">Cancelar</button>
        <button class="btn-primary" onclick="saveReviewers()">Guardar</button>
      </div>

    </div>
  </div>
  <div id="assignModal" class="modal-overlay" style="display:none">
  </div>

  <div id="pdfModal" class="modal-overlay" style="display:none; z-index: 1050;">
    <div class="modal-card" style="width: 85%; height: 90vh; max-width: 1000px; display:flex; flex-direction:column; padding:0;">

      <div class="modal-header" style="padding: 15px 20px; border-bottom:1px solid #eee;">
        <h3 style="margin:0;">Visualización de Protocolo</h3>
        <button class="modal-close" onclick="closePdfModal()">×</button>
      </div>

      <div class="modal-body" style="flex:1; background:#f3f4f6; position:relative;">
        <iframe id="pdfViewer" src="" style="width:100%; height:100%; border:none;"></iframe>
      </div>

      <div class="modal-footer" style="padding: 10px 20px;">
        <button class="btn-secondary" onclick="closePdfModal()">Cerrar</button>
      </div>

    </div>
  </div>

  <?php require_once __DIR__ . '/../inc/inc_footer_scripts.php'; ?>
</body>

</html>
