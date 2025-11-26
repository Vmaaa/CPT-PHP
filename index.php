<?php
require_once __DIR__ . '/config/app.php';
$pageScript = "index.js";
$pageStyle = "pages/login.css";
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <?php
  require_once __DIR__ . '/inc/inc_head.php';
  ?>
  <title>Iniciar sesión - Sales360</title>
</head>

<body>

  <div id="login-screen" class="login-container">
    <div class="login-left">
      <div class="login-card">
        <p class="login-title">Inicia sesión en tu cuenta</p>
        <p class="login-subtitle">Ingresa tu correo y contraseña para continuar</p>
        <div class="login-body">
          <div class="form-group">
            <label for="email">Correo electrónico</label>
            <input type="text" id="email" class="form-control" placeholder="tu.correo@alumno.ipn.mx">
          </div>
          <div class="form-group">
            <label for="password">Contraseña</label>
            <input type="password" id="password" class="form-control" placeholder="Ingresa tu contraseña">
          </div>
          <button id="login-btn" class="btn btn-primary" style="width: 100%; justify-content: center;">
            <i class="fas fa-sign-in-alt"></i> Iniciar sesión
          </button>
          <p class="recovery-password"> ¿Olvidaste tu contraseña? <a href="/pages/recovery_password.php" class="main-text-gradient">Recupérala</a></p>
          <p class="recovery-password"> ¿No tienes cuenta? <a href="/pages/recovery_password.php" class="main-text-gradient">Registrate</a></p>
        </div>
      </div>
    </div>
    <div class="login-right">
      <img src="<?= url('/img/login-bg.png') ?>" alt="Imagen UPIIT" class="login-bg-image">
    </div>
  </div>


  <?php require_once __DIR__ . '/inc/inc_footer_scripts.php'  ?>

</body>

</html>
