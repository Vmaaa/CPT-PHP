<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../inc/inc_auth.php';

$activePage = "ASESORADOS";
$pageTitle = "Mis Asesorados";
$pageScript = "advised.js";
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <?php require_once __DIR__ . '/../inc/inc_head.php'; ?>
  <title><?= $pageTitle ?></title>
  <link rel="stylesheet" href="/CPT/assets/css/pages/advised.css">
</head>

<body>
  <div id="app-container" class="app-container">
    <?php require_once __DIR__ . '/../inc/inc_sidebar.php'; ?>
    <div class="main-content">
      <main class="main-content-inner" style="padding:20px">

        <h1 style="margin-bottom:10px;"><?= $pageTitle ?></h1>
        <p style="color:#64748b; margin-bottom:30px;">
          Seguimiento de alumnos bajo tu asesoría. Revisa sus avances y observaciones de los jueces.
        </p>

        <div id="advised-container"></div>

      </main>
    </div>
  </div>

  <div id="pdfModal" class="modal-overlay" style="display:none; z-index:9999;">
    <div class="modal-card">
      <div class="modal-header">
        <h3>Documento del Alumno</h3>
        <button class="modal-close" onclick="closeModal('pdfModal')">×</button>
      </div>
      <div class="modal-body">
        <iframe id="pdfViewer" src="" style="width:100%; height:100%; border:none;"></iframe>
      </div>
      <div class="modal-footer">
        <button class="btn-secondary" onclick="closeModal('pdfModal')">Cerrar</button>
      </div>
    </div>
  </div>

  <div id="reviewsModal" class="modal-overlay" style="display:none; z-index:9999;">
    <div class="modal-card" style="height:auto; max-height:80vh;">
      <div class="modal-header">
        <h3>Observaciones de los Jueces</h3>
        <button class="modal-close" onclick="closeModal('reviewsModal')">×</button>
      </div>
      <div class="modal-body" style="padding:20px; overflow-y:auto;">
        <div id="reviewsContent"></div>
      </div>
      <div class="modal-footer">
        <button class="btn-secondary" onclick="closeModal('reviewsModal')">Cerrar</button>
      </div>
    </div>
  </div>

  <?php require_once __DIR__ . '/../inc/inc_footer_scripts.php'; ?>
</body>

</html>
