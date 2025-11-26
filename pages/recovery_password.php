<?php
require_once __DIR__ . '/../config/app.php';
$pageScript = "recovery_password.js";
$pageStyle = "pages/recovery_password.css";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <?php require_once __DIR__ . '/../inc/inc_head.php'; ?>
    <title>Recuperación de contraseña - CPT</title>
</head>
<body>
<div class="background-image">
    <img src="<?= url('/img/login-bg.png') ?>" alt="Imagen edificio UPIIT hecha a mano" class="bg-image">
  </div>
  <div class="center-card-container">
    <div class="recovery-card">
      <p class="card-title">Recupera tu contraseña</p>
      <p class="card-subtitle">Ingresa tu correo para recibir instrucciones</p>
      <div class="form-group">
        <label for="email">Correo electrónico</label>
        <input type="text" id="email" class="form-control" placeholder="tu.correo@alumno.ipn.mx">
      </div>
      <button type="button" class="btn btn-primary" id="recovery-btn">
        <i class="fas fa-sign-in-alt"></i> Enviar correo de recuperación
      </button>
    </div>
  </div>
  <?php require_once __DIR__ . '/../inc/inc_footer_scripts.php'; ?>
  <script src="../js/recovery_password.js"></script>
</body>
</html>
