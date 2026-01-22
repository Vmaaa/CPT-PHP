<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../inc/inc_auth.php';

$activePage = "ID_DE_SIDE_BAR";
$pageTitle = "TITULO DE LA PAGINA (SE VERA EN EL TOPBAR)";
$pageScript = "SCRIPT.js";
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <?php require_once __DIR__ . '/../inc/inc_head.php'; ?>
  <title><?= $pageTitle ?> - TeleAdmin</title>
</head>

<body>
  <div class="app-container" style="display:flex;">
    <?php require_once __DIR__ . '/../inc/inc_sidebar.php'; ?>
    <div class="main-content">
      <?php require_once __DIR__ . '/../inc/inc_topbar.php'; ?>

      <div class="content-area">
        <!-- AQUI LA View Correspondiente al HTML -->

      </div>
    </div>
  </div>
  <?php require_once __DIR__ . '/../inc/inc_modals.php'; ?>
  <?php require_once __DIR__ . '/../inc/inc_footer_scripts.php'; ?>
</body>

</html>
