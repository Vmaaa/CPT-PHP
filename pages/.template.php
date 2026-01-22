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
  <title><?= $pageTitle ?> -
    <?echo $SYSTEM_NAME ?>
  </title>
</head>

<body>

  <div id="app-container" class="app-container">
    <?php require_once __DIR__ . '/../inc/inc_sidebar.php'; ?>
    <div class="main-content">
      <main class="main-content-inner" style="padding: 20px;">

      </main>
    </div>
  </div>
  <?php require_once __DIR__ . '/../inc/inc_modals.php'; ?>
  <?php require_once __DIR__ . '/../inc/inc_footer_scripts.php'; ?>
</body>

</html>
