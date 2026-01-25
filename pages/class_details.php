<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../inc/inc_auth.php';

$pageTitle = "Detalles de la Clase";
$pageScript = 'class_details.js';
$pageStyle = 'pages/class_details.css';
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <?php require_once __DIR__ . '/../inc/inc_head.php'; ?>
  <title><?= $pageTitle ?> -
    <?= $SYSTEM_NAME ?>
  </title>
</head>

<body>

  <div id="app-container" class="app-container">
    <div class="main-content">
      <main class="main-content-inner" style="padding: 10px;">
        <div style="margin-bottom: 20px; display: flex; align-items: center; gap: 15px;">
          <a href="<?php echo url('pages/classes.php'); ?>" class="back-button">
            <i class=" fas fa-arrow-left"></i></a>
          <h1 style="font-size: 24px; font-weight: bold;"><?php echo $pageTitle; ?></h1>
        </div>
        <div class="class-details-layout">
          <!-- COLUMNA IZQUIERDA -->
          <div class="class-left">
            <div id="class-info" class="card"></div>

            <div id="class-professors" class="card"></div>

            <div id="class-students" class="card"></div>

            <div id="class-assignments" class="card"></div>
          </div>

          <!-- COLUMNA DERECHA -->
          <div class="class-right">
            <div id="assignment-detail" class="card assignment-empty">
              <p>Selecciona una actividad para ver el detalle</p>
            </div>
          </div>
        </div>
    </div>
  </div>
  <?php require_once __DIR__ . '/../inc/inc_footer_scripts.php'; ?>
  <?php require_once __DIR__ . '/../inc/inc_modals.php'; ?>
</body>

</html>
