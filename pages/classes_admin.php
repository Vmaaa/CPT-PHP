<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../inc/inc_auth.php';

$activePage = "CLASES_A";
$pageTitle = "Configuración de Clases";
$pageScript = "classes_admin.js";
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
        <div style="margin-bottom: 20px;">
          <button id="btn-new-class" class="btn btn-primary">
            <li class="fas fa-plus" style="margin-right: 8px;"></li>
            Nueva Clase
          </button>
        </div>
        <div id="classes-container" class="classes-grid">
        </div>
      </main>
    </div>
  </div>
  <?php require_once __DIR__ . '/../inc/inc_footer_scripts.php'; ?>
  <?php require_once __DIR__ . '/../inc/inc_modals.php'; ?>
</body>

</html>
