<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../inc/inc_auth.php';

$activePage = "CUENTA";
$pageTitle = "Datos de mi cuenta";
$pageScript = "account.js";
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
        <div class="form-container">
          <form class="account-form">
            <div class="form-group">
              <label for="name">Nombre</label>
              <input type="text" id="name" class="form-control" readonly>
            </div>
            <div class="form-group">
              <label for="email">Email</label>
              <input type="email" id="email" class="form-control" readonly>
            </div>
            <div class="form-group">
              <label for="role">Rol</label>
              <input type="text" id="role" class="form-control" readonly>
            </div>
            <div class="form-group">
              <label for="status">Estado</label>
              <input type="text" id="status" class="form-control" readonly>
            </div>
            <div class="form-group">
              <label for="curp">CURP</label>
              <input type="text" id="curp" class="form-control" readonly>
            </div>
            <div id="teacher-fields" style="display: none;">
              <div class="form-group">
                <label for="academia">Academia</label>
                <input type="text" id="academia" class="form-control" readonly>
              </div>
              <div class="form-group">
                <label for="level_of_education">Nivel de Educación</label>
                <input type="text" id="level_of_education" class="form-control" readonly>
              </div>
              <div class="form-group">
                <label for="is_president">¿Es Presidente?</label>
                <input type="text" id="is_president" class="form-control" readonly>
              </div>
              <div class="form-group">
                <label for="is_advisor">¿Es Asesor?</label>
                <input type="text" id="is_advisor" class="form-control" readonly>
              </div>
            </div>
            <div id="student-fields" style="display: none;">
              <div class="form-group">
                <label for="school_id_number">Matrícula</label>
                <input type="text" id="school_id_number" class="form-control" readonly>
              </div>
              <div class="form-group">
                <label for="id_career">ID Carrera</label>
                <input type="text" id="id_career" class="form-control" readonly>
              </div>
              <div class="form-group">
                <label for="id_class">ID Clase</label>
                <input type="text" id="id_class" class="form-control" readonly>
              </div>
            </div>
          </form>
        </div>
      </main>

    </div>
  </div>
  <?php require_once __DIR__ . '/../inc/inc_footer_scripts.php'; ?>
</body>

</html>
