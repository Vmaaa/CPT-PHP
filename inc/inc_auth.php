<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../utils/token/validate.php';


function current_user_or_redirect(): array {
    if (empty($_COOKIE['jwt']) && basename($_SERVER['PHP_SELF']) !== 'change_password.php') {
        header("Location: " . url("/index.php"), true, 302);
        exit;
    }

    $data = fnt_validateJWT_v001($_COOKIE['jwt']?? '');
    if ($data === false) {
      if (basename($_SERVER['PHP_SELF']) === 'change_password.php') {
        return [];
      }
      else{
        if (PHP_VERSION_ID >= 70300) {
            setcookie('jwt', '', [
                'expires'  => time() - 3600,
                'path'     => '/',
                'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
                'httponly' => true,
                'samesite' => 'Strict',
            ]);
        } else {
            $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? '; Secure' : '';
            header('Set-Cookie: jwt=; Expires=Thu, 01 Jan 1970 00:00:00 GMT; Path=/; SameSite=Strict' . $secure);
        }
        header("Location: " . url("/index.php"), true, 302);
        exit;
      }
    }
    return $data;
}

function role_human_readable(string $role): string {
    return match ($role) {
        'admin' => 'Administrador',
        'student' => 'Estudiante',
        'professor' => 'Profesor',
        'advisor' => 'Asesor',
        default => 'Desconocido',
    };
}

$ignored_pages = [
    'index.php',
    'recovery_password.php',
];
//change_password.php is handled separately in the function

if (in_array(basename($_SERVER['PHP_SELF']), $ignored_pages)) {
  return;
}

$AUTH = current_user_or_redirect();
if (isset($AUTH['acco_status']) && $AUTH['acco_status'] !== 1 && basename($_SERVER['PHP_SELF']) !== 'change_password.php') {
  header("Location: /index.php?show_alert=true", true, 302);
  header("Location: " . url("/index.php?show_alert=true"), true, 302);
  exit;
}

if(isset($AUTH['first_login']) && $AUTH['first_login'] === 1 && basename($_SERVER['PHP_SELF']) !== 'change_password.php') {
  header("Location: " . url("/pages/change_password.php?show_alert=true"), true, 302);
    exit;
}

