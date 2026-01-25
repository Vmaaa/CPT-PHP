<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../inc/inc_auth.php';

$activePage = "PROYECTO_S";
$pageTitle = "Protocolo de Trabajo Terminal";
$pageScript = "protocolo.js";
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <?php require_once __DIR__ . '/../inc/inc_head.php'; ?>
  <title><?= $pageTitle ?> - <?= $SYSTEM_NAME ?></title>
  <link rel="stylesheet" href="/CPT/assets/css/pages/projects_student.css">
</head>

<body>
  <div id="app-container" class="app-container">
    <?php require_once __DIR__ . '/../inc/inc_sidebar.php'; ?>

    <div class="main-content">
      <main class="main-content-inner" style="padding: 20px;">

        <h1 style="margin-bottom: 30px; color: #1e293b; font-weight: 700;"><?= $pageTitle ?></h1>

        <div id="protocol-container">
          <div class="loading">
            <div class="spinner-border text-primary" role="status"></div>
            <p>Cargando información...</p>
          </div>
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
