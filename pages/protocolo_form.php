<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../inc/inc_auth.php';

$activePage = "PROYECTO";
$pageTitle  = "Registro de Protocolo";
$pageScript = "protocolo_form.js";
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
        <div class="form-container-card">
          <p class="subtitle">Completa la información para registrar tu proyecto.</p>
          <form id="protocol-form" enctype="multipart/form-data">

            <div class="form-section">
              <h3><i class="fas fa-file-alt"></i> Datos del proyecto</h3>

              <div class="form-group">
                <label>Título del proyecto</label>
                <input type="text" name="title" required placeholder="Ej. Sistema de Control de Inventarios...">
              </div>

              <div class="form-group">
                <label>Carrera</label>
                <select name="id_career" id="career-select" required>
                  <option value="">Elige tu carrera</option>
                </select>
              </div>

              <div class="form-group">
                <label>Resumen (Abstract)</label>
                <textarea name="abstract" maxlength="1200" required placeholder="Describe brevemente de qué trata tu proyecto..."></textarea>
              </div>

              <div class="form-group">
                <label>Archivo del Protocolo (PDF)</label>
                <div class="file-input-wrapper">
                  <input type="file" name="protocol_file" accept="application/pdf" required>
                  <small>Formato PDF. Máximo 10 MB.</small>
                </div>
              </div>
            </div>

            <hr class="divider">

            <div class="form-section">
              <h3><i class="fas fa-users"></i> Asignación de Asesores</h3>

              <div class="row-two">
                <div class="form-group">
                  <label>Asesor 1</label>
                  <select name="advisor_1" id="advisor-1" required>
                    <option value="">Seleccionar...</option>
                  </select>
                </div>

                <div class="form-group">
                  <label>Asesor 2</label>
                  <select name="advisor_2" id="advisor-2" required>
                    <option value="">Seleccionar...</option>
                  </select>
                </div>
              </div>
            </div>

            <div class="form-actions">
              <button type="submit" class="btn-submit">
                Registrar Protocolo
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
