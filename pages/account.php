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
  <title><?= $pageTitle ?> - <?= $SYSTEM_NAME ?></title>
</head>

<body>

  <div id="app-container" class="app-container">
    <?php require_once __DIR__ . '/../inc/inc_sidebar.php'; ?>

    <div class="main-content">
      <main class="main-content-inner" style="padding:20px;">

        <div style="margin-bottom:25px;">
          <h1 style="font-size:24px;font-weight:bold;">
            <?= $pageTitle ?>
          </h1>
        </div>

        <div class="form-container" id="account-card">

          <!-- Banner ADMIN -->
          <div id="admin-banner"
            style="display:none;margin-bottom:20px;padding:10px;"
            class="badge badge-info">
            Administrador del Sistema
          </div>

          <form class="account-form">

            <!-- ================= CUENTA ================= -->
            <div style="margin-bottom:30px;">
              <h3 style="margin-bottom:15px;">Información de la Cuenta</h3>

              <div class="form-group">
                <label>Email</label>
                <input type="email" id="email" class="form-control" readonly>
              </div>

              <div class="form-group">
                <label>Rol</label>
                <input type="text" id="role" class="form-control" readonly>
              </div>

              <div class="form-group">
                <label>Estado</label>
                <input type="text" id="status" class="form-control" readonly>
              </div>
            </div>

            <!-- ================= PROFESSOR / ADMIN ================= -->
            <div id="teacher-fields" style="display:none;margin-bottom:30px;">
              <h3 style="margin-bottom:15px;">Información Académica</h3>

              <div class="form-group">
                <label>Academia</label>
                <input type="text" id="academia" class="form-control" readonly>
              </div>

              <div class="form-group">
                <label>Nivel de Educación</label>
                <input type="text" id="level_of_education" class="form-control" readonly>
              </div>

              <div style="margin-top:15px;">
                <span id="badge-president" class="badge"></span>

                <span id="badge-advisor" class="badge"></span>
              </div>
            </div>

            <!-- ================= STUDENTS ================= -->
            <div id="student-fields" style="display:none;">
              <h3 style="margin-bottom:15px;">Estudiantes Asociados</h3>

              <div id="students-container"></div>
            </div>

          </form>
        </div>

      </main>
    </div>
  </div>

  <?php require_once __DIR__ . '/../inc/inc_footer_scripts.php'; ?>
  <script src="/assets/js/account.js"></script>
</body>

</html>
