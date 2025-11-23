<?php
require_once __DIR__ . '/../config/app.php';
$pageScript = "recovery_password.js";
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <?php require_once __DIR__ . '/../inc/inc_head.php'; ?>
    <title>Recuperación de contraseña - Sales360</title>
    <script>window.APP_BASE_URL = "<?= htmlspecialchars(BASE_URL ?: '') ?>";</script>
</head>
<body>
  <div id="recover-screen" class="login-container">
    <div class="login-card">
      <div class="login-header main-text-gradient">
        <h1><i class="fas fa-key"></i> Recuperar Contraseña</h1>
      </div>
        <p class="login-subtitle">Ingresa tu correo electrónico para restablecer tu contraseña</p>
      <div class="login-body">
        <form prevent="submit">
          <div class="form-group">
            <label for="email">Correo Electrónico</label>
            <input type="email" id="email" name="email" class="form-control" placeholder="Ingresa tu correo" required>
          </div>
          <button type="button" class="btn btn-primary" style="width: 100%; justify-content: center;" id="recovery-btn">
            <i class="fas fa-paper-plane"></i> Enviar correo de recuperación
          </button>
        </form>
      </div>
    </div>
  </div>
  <?php require_once __DIR__ . '/../inc/inc_footer_scripts.php'; ?>
</body>
<script src="../js/recovery_password.js"/>
</html>
