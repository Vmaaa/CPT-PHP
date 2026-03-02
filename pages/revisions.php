<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../inc/inc_auth.php';

$activePage = "REVISIONES";
$pageTitle  = "Revisión de Documento Final";
$pageScript = "revisions.js";
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
      <main class="main-content-inner" style="padding: 40px 20px;">
        <h1 class="page-main-title"><?= $pageTitle ?></h1>
        <p style="color: #64748b; margin-bottom: 20px;">Aquí evalúas si el documento final está listo para ser presentado.</p>

        <div id="projects-container" class="projects-grid">
        </div>
      </main>
    </div>
  </div>


  <div id="pdfModal" class="modal-overlay" style="display:none">
    <div class="modal-card">
      <div class="modal-header">
        <h3 style="margin:0">Visualizador de Documentos</h3>
        <button class="modal-close" onclick="closePdfModal()">×</button>
      </div>
      <div class="modal-body">
        <iframe id="pdfViewer" src="" style="width:100%; height:100%; border:none;"></iframe>
      </div>
      <div class="modal-footer">
        <button class="btn-secondary" onclick="closePdfModal()">Cerrar</button>
      </div>
    </div>
  </div>

  <?php require_once __DIR__ . '/../inc/inc_footer_scripts.php'; ?>
</body>


</html>
