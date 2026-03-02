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

  <div id="pdfModal" class="modal-overlay" style="display:none;">
    <div class="modal-content-pdf">
      <div class="modal-header">
        <h2>Visor de PDF</h2>
        <button class="btn-close-modal" onclick="closePdfModal()">&times;</button>
      </div>
      <div class="modal-body-pdf">
        <iframe id="pdfViewer" src="" frameborder="0"></iframe>
      </div>
    </div>
  </div>

  <?php require_once __DIR__ . '/../inc/inc_footer_scripts.php'; ?>
</body>

</html>
