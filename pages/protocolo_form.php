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
</head>

<body>
  <div id="app-container" class="app-container">
    <?php require_once __DIR__ . '/../inc/inc_sidebar.php'; ?>

    <div class="main-content">
      <main class="main-content-inner" style="padding:20px; max-width:900px">

        <h1><?= $pageTitle ?></h1>

        <form id="protocol-form" enctype="multipart/form-data">

          <!-- DATOS DEL PROYECTO -->
          <section class="card">
            <h3>Datos del proyecto</h3>

            <label>Título del proyecto</label>
            <input type="text" name="title" required>

            <label>Carrera</label>
            <select name="id_career" id="career-select" required>
              <option value="">Selecciona una opción</option>
            </select>

            <label>Resumen (abstract)</label>
            <textarea name="abstract" maxlength="1200" required></textarea>

            <label>Protocolo (PDF)</label>
            <input type="file" name="protocol_file" accept="application/pdf" required>
            <small>Solo PDF. Máx 10 MB.</small>
          </section>

          <!-- ASESORES -->
          <section class="card">
            <h3>Asignación de Asesores</h3>

            <label>Asesor 1</label>
            <select name="advisor_1" id="advisor-1" required></select>

            <label>Asesor 2</label>
            <select name="advisor_2" id="advisor-2" required></select>
          </section>

          <button type="submit" class="btn btn-primary">
            Registrar Protocolo
          </button>

        </form>

        <div id="form-message"></div>

      </main>
    </div>
  </div>

  <?php require_once __DIR__ . '/../inc/inc_footer_scripts.php'; ?>
</body>

</html>
