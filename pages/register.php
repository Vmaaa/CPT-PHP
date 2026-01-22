<?php
require_once __DIR__ . '/../config/app.php';
$pageScript = "register.js";
$pageStyle = "pages/login-register.css";
$pageTitle = "Registro de usuario";
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
  <div id="register-screen" class="login-container">
    <div class="login-left">
      <img src="<?= url('/img/signup-bg.png') ?>" alt="Imagen edificio UPIIT hecha a mano" class="login-bg-image">
    </div>
    <div class="login-right">
      <div class="login-card">
        <p class="login-title">Crea tu cuenta</p>
        <p class="login-subtitle">Usa tu correo institucional para registrarte</p>
        <div id="user-type-text" class="user-type-text"></div>
        <form id="register-form">
          <div class="form-group">
            <label for="email">Correo electrónico</label>
            <input type="text" id="email" name="email" class="form-control" placeholder="tu.correo@alumno.ipn.mx" required>
          </div>
          <div class="form-group">
            <label for="nombres">Nombres</label>
            <input type="text" id="nombres" name="nombres" class="form-control" required>
          </div>
          <div class="form-group">
            <label for="apellidos">Apellidos</label>
            <input type="text" id="apellidos" name="apellidos" class="form-control" required>
          </div>
          <div class="form-group">
            <label for="curp">CURP</label>
            <input type="text" id="curp" name="curp" class="form-control" required>
          </div>
          <div id="student-fields" style="display:none;">
            <div class="form-group">
              <label for="boleta">Boleta</label>
              <input type="text" id="boleta" name="boleta" class="form-control" maxlength="10" pattern="\d{10}">
            </div>
            <div class="form-group">
              <label for="carrera">Carrera</label>
              <select id="carrera" name="carrera" class="form-control"></select>
            </div>
          </div>
          <div id="teacher-fields" style="display:none;">
            <div class="form-group">
              <label for="academia">Academia</label>
              <input type="text" id="academia" name="academia" class="form-control">
            </div>
            <div class="form-group">
              <label for="nivel">Nivel de educación</label>
              <select id="nivel" name="nivel" class="form-control">
                <option value="">Selecciona nivel</option>
                <option value="bachelor's">Licenciatura</option>
                <option value="master's">Maestría</option>
                <option value="doctorate">Doctorado</option>
              </select>
            </div>
          </div>
          <button id="register-btn" class="btn btn-primary" style="width: 100%; justify-content: center;">
            <i class="fas fa-user-plus"></i> Registrarse
          </button>
          <p class="extra-link-p">¿Ya tienes cuenta? <a href="<?php echo url('/index.php') ?>">Inicia sesión aquí</a></p>
        </form>
      </div>
    </div>
  </div>
  <?php require_once __DIR__ . '/../inc/inc_footer_scripts.php'; ?>
</body>

</html>
