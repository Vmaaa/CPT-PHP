<?php
require_once __DIR__ . '/../config/app.php';
$pageScript = "change_password.js";
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <?php require_once __DIR__ . '/../inc/inc_head.php'; ?>
  <title>Recuperación de contraseña - Sales360 </title>
  <script>window.APP_BASE_URL = "<?= htmlspecialchars(BASE_URL ?: '') ?>";</script>

  <style>
    .input-wrap {
      position: relative;
    }

    .input-eye {
      position: absolute;
      right: 12px;
      top: 50%;
      transform: translateY(-50%);
      cursor: pointer;
      opacity: .6;
    }

    .input-eye:hover {
      opacity: 1;
    }

    .form-row.vertical {
      display: flex !important;
      flex-direction: column !important;
      gap: 12px;
    }

    .password-req {
      width: 100%;
      max-width: none;
      align-self: stretch;
      background: #f8fafc;
      border: 1px solid #e5e7eb;
      border-radius: 8px;
      padding: 14px 16px;
      font-size: 12px;
      margin-top: 4px;
    }

    .password-req .req-title {
      font-weight: 600;
      margin-bottom: 8px;
      color: #374151;
    }

    .password-req ul {
      list-style: none;
      margin: 0;
      padding: 0;
    }

    .password-req li {
      position: relative;
      padding-left: 24px;
      margin: 6px 0;
      color: #374151;
      transition: color .15s ease;
      text-align: justify;
    }

    .password-req li::before {
      content: "\f00c";
      font-family: "Font Awesome 5 Free";
      font-weight: 900;
      position: absolute;
      left: 0;
      top: 2px;
      opacity: .15;
    }

    .password-req li.ok {
      color: #16a34a;
      text-decoration: line-through;
    }

    .password-req li.ok::before {
      opacity: 1;
    }

    #back-btn {
      position: absolute;
      top: 10px;
      left: 20px;
      font-size: 34px;
      color: #ffffff;
      text-decoration: none;
      transition: color .15s ease;
      display: none;
    }
  </style>
</head>

<body>  
  <a id="back-btn" title="Volver al inicio de sesión" href="<?= htmlspecialchars(BASE_URL ?: '/pages/') ?>dashboard.php">
    <i class="fas fa-arrow-left"></i>
  </a>
  <div id="change-password-screen" class="login-container">
    <div class="login-card">
      <div class="login-header main-text-gradient">
        <h1><i class="fas fa-lock"></i> Cambiar contraseña</h1>
      </div>

        <p class="login-subtitle">Ingresa tu nueva contraseña</p>
      <div class="login-body">
        <form prevent="submit">
          <div class="form-row vertical">
            <div>
              <div class="form-group">
                <label for="password">Nueva contraseña</label>
                <div class="input-wrap">
                  <input type="password" id="password" name="password" class="form-control"
                    placeholder="Nueva contraseña" required>
                  <i class="far fa-eye input-eye" data-toggle="#password" title="Mostrar/Ocultar"></i>
                </div>
              </div>

              <div class="form-group">
                <label for="password_confirm">Confirmar contraseña</label>
                <div class="input-wrap">
                  <input type="password" id="password-confirm" name="password-confirm" class="form-control"
                    placeholder="Repite la contraseña" required>
                  <i class="far fa-eye input-eye" data-toggle="#password-confirm" title="Mostrar/Ocultar"></i>
                </div>
              </div>
            </div>

            <div class="password-req">
              <div class="req-title">Requisitos de contraseña:</div>
              <ul id="pw-rules">
                <li data-rule="len">Al menos 8 caracteres</li>
                <li data-rule="digit">Al menos 1 carácter numérico</li>
                <li data-rule="upper">Al menos 1 letra mayúscula</li>
                <li data-rule="lower">Al menos 1 letra minúscula</li>
                <li data-rule="symbol">Al menos 1 símbolo</li>
                <li data-rule="match">Las contraseñas coinciden</li>
              </ul>
            </div>
          </div>

          <button type="button" class="btn btn-primary" style="width: 100%; justify-content: center; margin-top: 2rem;"
            id="change-password-btn">
            <i class="fas fa-save"></i> Guardar nueva contraseña
          </button>
        </form>
      </div>
    </div>
  </div>

  <?php require_once __DIR__ . '/../inc/inc_footer_scripts.php'; ?>
</body>
<script>
<?php 
if (isset($AUTH['acco_name'])) {
  echo 'const is_logged_in = true;';
} else {
  echo 'const is_logged_in = false;';
}
?>
</script>
</html>
