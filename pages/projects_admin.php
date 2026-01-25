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

  <!-- CSS específico -->
  <link rel="stylesheet" href="/CPT/assets/css/pages/projects_admin.css">
</head>

<body>
  <div id="app-container" class="app-container">

    <?php require_once __DIR__ . '/../inc/inc_sidebar.php'; ?>

    <div class="main-content">
      <main class="main-content-inner" style="padding:20px">

        <div class="page-header">
          <h1><?= $pageTitle ?></h1>
          <p class="page-subtitle">
            Administración y asignación de revisores de proyectos.
          </p>
        </div>

        <!-- CONTENEDOR -->
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

  <?php require_once __DIR__ . '/../inc/inc_footer_scripts.php'; ?>
</body>

</html>
