<?php
require_once __DIR__ . '/config/app.php';
$pageScript = "index.js";
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
    <div class="login-card">
        <img src="<?= url('/img/logo.png') ?>" alt="Logo" class="login-logo">
        <p class="login-title">Bienvenido al sistema</p>
        <p class="login-subtitle">Ingresa tus credenciales</p>
      <div class="login-body">
        <div class="form-group">
          <label for="email">Correo electrónico</label>
          <input type="text" id="email" class="form-control" placeholder="tu.correo@telat.com">
        </div>
        <div class="form-group">
          <label for="password">Contraseña</label>
          <input type="password" id="password" class="form-control" placeholder="Ingresa tu contraseña">
        </div>
        <button id="login-btn" class="btn btn-primary" style="width: 100%; justify-content: center;">
          <i class="fas fa-sign-in-alt"></i> Ingresar al sistema
        </button>
        <p class="recovery-password"> ¿Olvidaste tu contraseña? <a href="/pages/recovery_password.php" class="main-text-gradient">Recupérala</a></p>
      </div>
      </div>
    </div>
  </div>


  <?php require_once __DIR__ . '/inc/inc_footer_scripts.php'  ?>

</body>

</html>
