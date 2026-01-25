<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../inc/inc_auth.php';

$activePage = "PROYECTOS_PROFESOR";
$pageTitle = "Mis Revisiones";
$pageScript = "projects_professor.js";
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <?php require_once __DIR__ . '/../inc/inc_head.php'; ?>
  <title><?= $pageTitle ?> - <?= $SYSTEM_NAME ?></title>
  <link rel="stylesheet" href="/CPT/assets/css/pages/projects_professor.css">
</head>

<body>
  <div id="app-container" class="app-container">
    <?php require_once __DIR__ . '/../inc/inc_sidebar.php'; ?>

    <div class="main-content">
      <main class="main-content-inner" style="padding:20px">

        <h1 style="margin-bottom:30px; font-weight:700; color:#1e293b;"><?= $pageTitle ?></h1>

        <div id="projects-container"></div>

      </main>
    </div>
  </div>

  <div id="pdfModal" class="modal-overlay" style="display:none">
    <div class="modal-card">
      <div class="modal-header">
        <h3 style="margin:0">Visualización de Protocolo</h3>
        <button class="modal-close" onclick="closePdfModal()">×</button>
      </div>
      <div class="modal-body">
        <iframe id="pdfViewer" src="" style="width:100%; height:100%; border:none;"></iframe>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" onclick="closePdfModal()">Cerrar</button>
      </div>
    </div>
  </div>

  <?php require_once __DIR__ . '/../inc/inc_footer_scripts.php'; ?>
</body>

</html>
