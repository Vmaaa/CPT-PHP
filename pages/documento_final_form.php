<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../inc/inc_auth.php';

$activePage = "PROYECTO";
$pageTitle  = "Subir Documento Final";
$pageScript = "documento_final_form.js";
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <?php require_once __DIR__ . '/../inc/inc_head.php'; ?>
  <title><?= $pageTitle ?> - <?= $SYSTEM_NAME ?></title>
  <link rel="stylesheet" href="/CPT/assets/css/pages/protocolo_form.css">
</head>

<body>
  <div id="app-container" class="app-container">
    <?php require_once __DIR__ . '/../inc/inc_sidebar.php'; ?>

    <div class="main-content">
      <main class="main-content-inner" style="padding: 40px 20px;">

        <h1 class="page-main-title"><?= $pageTitle ?></h1>

        <div id="project-summary" style="display: none; margin-bottom: 25px;"></div>

        <div class="form-container-card">
          <p class="subtitle">Sube la versión definitiva de tu Trabajo Terminal para la evaluación del jurado.</p>

          <form id="final-document-form" enctype="multipart/form-data">

            <div class="form-section">
              <h3><i class="fas fa-file-pdf"></i> Archivo del Documento</h3>

              <div class="form-group">
                <label for="final_file">Documento Final (PDF)</label>
                <div class="file-input-wrapper">
                  <input type="file" name="final_file" id="final_file" accept="application/pdf" required>
                  <small>Formato PDF. Máximo 20 MB.</small>
                </div>
              </div>
            </div>

            <div class="form-actions" style="display: flex; justify-content: flex-end; align-items: center;">
              <button type="submit" class="btn-submit">
                Enviar Documento
              </button>
            </div>

          </form>
        </div>

        <div id="form-message"></div>

      </main>
    </div>
  </div>

  <?php require_once __DIR__ . '/../inc/inc_footer_scripts.php'; ?>
</body>

</html>
