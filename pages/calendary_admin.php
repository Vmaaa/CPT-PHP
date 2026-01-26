<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../inc/inc_auth.php';

$activePage = "CALENDARIO_A";
$pageTitle = "Configuración de calendario";
$pageScript = "calendary_admin.js";
$pageStyle = "pages/calendary_admin.css";
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
    <?php require_once __DIR__ . '/../inc/inc_sidebar.php'; ?>
    <div class="main-content">
      <main class="main-content-inner" style="padding: 20px;">
        <div style="margin-bottom: 30px;">
          <h1 style="font-size: 24px; font-weight: bold;"><?php echo $pageTitle; ?></h1>
        </div>
        <button class="btn btn-primary" id="btn-new-stage"><i class="fas fa-plus"></i> Agregar nueva etapa
        </button>
        <div id="stages-container" class="stages-grid"></div>
      </main>
    </div>
  </div>
  <?php require_once __DIR__ . '/../inc/inc_footer_scripts.php'; ?>
  <?php require_once __DIR__ . '/../inc/inc_modals.php'; ?>
</body>

</html>
