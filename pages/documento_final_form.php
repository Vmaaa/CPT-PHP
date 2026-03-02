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
              <h3><i class="fas fa-file-upload"></i> Archivos requeridos</h3>
              <p style="color: #64748b; font-size: 0.9em; margin-bottom: 20px;">Sube tu documento escrito y el material de apoyo (diapositivas) para tu exposición.</p>

              <div class="row-two" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">

                <div class="form-group">
                  <label for="final_file">Documento Escrito (PDF)</label>
                  <div class="file-input-wrapper" style="border: 2px dashed #cbd5e1; padding: 30px 15px; text-align: center; border-radius: 8px; background-color: #f8fafc;">
                    <i class="fas fa-file-pdf" style="font-size: 2.5rem; color: #ef4444; margin-bottom: 10px;"></i><br>
                    <input type="file" name="final_file" id="final_file" accept="application/pdf" required style="max-width: 100%;">
                    <small style="display: block; margin-top: 5px;">Solo formato PDF. Máx 20 MB.</small>
                  </div>
                </div>

                <div class="form-group">
                  <label for="presentation_file">Presentación (PDF o PowerPoint)</label>
                  <div class="file-input-wrapper" style="border: 2px dashed #cbd5e1; padding: 30px 15px; text-align: center; border-radius: 8px; background-color: #f8fafc;">
                    <i class="fas fa-file-powerpoint" style="font-size: 2.5rem; color: #f97316; margin-bottom: 10px;"></i><br>
                    <input type="file" name="presentation_file" id="presentation_file" accept=".pdf, .ppt, .pptx, application/pdf, application/vnd.ms-powerpoint, application/vnd.openxmlformats-officedocument.presentationml.presentation" required style="max-width: 100%;">
                    <small style="display: block; margin-top: 5px;">Formatos: .pdf, .ppt, .pptx. Máx 20 MB.</small>
                  </div>
                </div>

              </div>
            </div>

            <div class="form-actions" style="display: flex; justify-content: flex-end; align-items: center;">
              <button type="submit" class="btn-submit">
                Enviar Documentos
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
