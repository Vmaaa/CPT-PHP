<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../inc/inc_auth.php';

$activePage = "USUARIOS";
$pageTitle = "Manejo de usuarios";
$pageScript = "user.js";
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <?php require_once __DIR__ . '/../inc/inc_head.php'; ?>
  <title>Manejo de usuarios - <?php echo $SYSTEM_NAME ?> </title>
</head>

<body>
  <div id="app-container" class="app-container">

    <?php require_once __DIR__ . '/../inc/inc_sidebar.php'; ?>

    <div class="main-content">
      <main class="main-content-inner" style="padding: 20px;">

        <div style="margin-bottom: 30px;">
          <h1 style="font-size: 24px; font-weight: bold;"><?php echo $pageTitle; ?></h1>
        </div>

        <div class="table-container">
          <table class="data-table">
            <thead>
              <tr>
                <th class="text-center">Nombre</th>
                <th class="text-center">Email</th>
                <th class="text-center">Academia</th>
                <th class="text-center">Rol</th>
                <th class="text-center">Asesor</th>
                <th class="text-center">Presidente</th>
                <th class="text-center">Acciones</th>
              </tr>
            </thead>
            <tbody id="users-table-body"></tbody>
          </table>
        </div>

      </main>
    </div>
  </div>
</body>

<?php require_once __DIR__ . '/../inc/inc_footer_scripts.php'; ?>

</html>
