<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'El recurso actual no soporta el método HTTP: ' . $_SERVER['REQUEST_METHOD']]);
    exit;
}

require_once __DIR__ . "/../../../../config/cors.php";
require_once __DIR__ . "/../../../../functions/serverSpecifics.php";
require_once __DIR__ . "/../../../../utils/token/validate.php";
require_once __DIR__ . "/../../../../utils/token/delete.php";
require_once __DIR__ . "/../../../../utils/mail/send.php";

if ((!isset($_POST["recovery_token"]) && !isset($_COOKIE['jwt'])) || !isset($_POST["new_password"])) {
    http_response_code(401);
    echo json_encode(['error' => "Parámetros incompletos, se requiere 'recovery_token' o 'Authorization' y 'new_password'"]);
    echo json_encode($_COOKIE);
    echo json_encode($_POST);
    exit;
}

$SS = ServerSpecifics::getInstance();
$WEBPAGE_T = $SS->fnt_getWebPageURL();
$DB_T = $SS->fnt_getDBConnection();

$new_password = (string)($_POST['new_password'] ?? '');

if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/', $new_password)) {
    http_response_code(400);
    echo json_encode(['error' => 'La contraseña no cumple con los requisitos: mínimo 8 caracteres e incluir mayúscula, minúscula, número y símbolo.']);
    exit;
}

if (isset($_POST["recovery_token"])) {
    $recovery_token = $_POST["recovery_token"];
    $validation = fnt_validateRecoveryPasswordToken_v001($DB_T, $recovery_token);
    if ($validation === false) {
        http_response_code(401);
        echo json_encode(["error" => "Token inválido o expirado"]);
        exit;
    }
    $acco_id = (int)$validation['acco_id'];
    $acco_email = $validation['acco_email'];
} else {
    $jwt = $_COOKIE['jwt'];
    $decoded_jwt = fnt_validateJWT_v001($jwt);
    if ($decoded_jwt === false) {
        http_response_code(401);
        echo json_encode(["error" => "Token inválido o expirado"]);
        exit;
    }
    $acco_id = (int)$decoded_jwt['acco_id'];
    $acco_email = $decoded_jwt['acco_email'];
}

mysqli_begin_transaction($DB_T);

$qGet = "SELECT acco_password FROM account WHERE acco_id = ? LIMIT 1";
$stGet = mysqli_prepare($DB_T, $qGet);
if (!$stGet) {
    mysqli_rollback($DB_T);
    http_response_code(500);
    echo json_encode(['error' => 'Error preparando consulta (lectura)']);
    exit;
}
mysqli_stmt_bind_param($stGet, 'i', $acco_id);
mysqli_stmt_execute($stGet);
$rsGet = mysqli_stmt_get_result($stGet);
$curr = mysqli_fetch_assoc($rsGet);
mysqli_stmt_close($stGet);

if (!$curr) {
    mysqli_rollback($DB_T);
    http_response_code(404);
    echo json_encode(['error' => 'Cuenta no encontrada']);
    exit;
}

$hashed_new_password = hash('sha256', $new_password);
if (hash_equals($curr['acco_password'], $hashed_new_password)) {
    mysqli_rollback($DB_T);
    http_response_code(400);
    echo json_encode(['error' => 'La nueva contraseña no puede ser igual a la anterior.']);
    exit;
}

$qry_updatePassword = "
    UPDATE account
    SET acco_password = ?, first_login = 0
    WHERE acco_id = ?
";
$stmt = mysqli_prepare($DB_T, $qry_updatePassword);
if ($stmt === false) {
    mysqli_rollback($DB_T);
    http_response_code(500);
    echo json_encode(['error' => 'Error preparando consulta (update)']);
    exit;
}
mysqli_stmt_bind_param($stmt, 'si', $hashed_new_password, $acco_id);
if (!mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);
    mysqli_rollback($DB_T);
    http_response_code(500);
    echo json_encode(['error' => 'Error ejecutando actualización de contraseña']);
    exit;
}
mysqli_stmt_close($stmt);

if (!fnt_sendEmailForChangedPassword_v001($acco_email, $WEBPAGE_T)) {
    mysqli_rollback($DB_T);
    http_response_code(500);
    echo json_encode(['error' => 'Error al enviar el correo de notificación']);
    exit;
}

if (isset($recovery_token)) {
    if (!fnt_deleteRecoveryPasswordToken_v001($DB_T, $recovery_token)) {
        mysqli_rollback($DB_T);
        http_response_code(500);
        echo json_encode(['error' => 'Error al eliminar el token de recuperación']);
        exit;
    }
}

mysqli_commit($DB_T);
http_response_code(200);
echo json_encode(['success' => true]);
