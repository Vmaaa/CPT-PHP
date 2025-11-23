<?php
header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'El recurso actual no soporta el método HTTP: ' . $_SERVER['REQUEST_METHOD']]);
    exit;
}

if (!isset($_POST['acco_email']) || !isset($_POST['acco_password'])) {
    http_response_code(401);
    echo json_encode(['error' => "Párametros incompletos, se requiere 'acco_email' y 'acco_password'"]);
    exit;
}
require_once __DIR__ . "/../../../../config/cors.php";
require_once __DIR__ . "/../../../../functions/serverSpecifics.php";
require_once __DIR__ . "/../../../../utils/token/create.php";
$SS = ServerSpecifics::getInstance();
$DB_T = $SS->fnt_getDBConnection();
$JWT_DURATION_TELAT = $SS->fnt_getJWTDuration();
$acco_email = $_POST["acco_email"];
$acco_password = $_POST["acco_password"];


$qry_getInformationUser = "
    SELECT * FROM account
    WHERE acco_email = ?
";

$stmt = mysqli_prepare($DB_T, $qry_getInformationUser);
if (!$stmt) {
    http_response_code(401);
    echo json_encode(['error' => 'La consulta a la base de datos falló']);
    exit;
}

mysqli_stmt_bind_param($stmt, "s", $acco_email);
mysqli_stmt_execute($stmt);
$rs_getInformationUser = mysqli_stmt_get_result($stmt);
if (mysqli_num_rows($rs_getInformationUser) === 0) {
    http_response_code(401);
    echo json_encode(['error' => 'Usuario o contraseña incorrectos']);
    mysqli_stmt_close($stmt);
    exit;
}

$row_getInformationUser = mysqli_fetch_array($rs_getInformationUser);
$hashed_input_password = hash('sha256', $acco_password);

if ($hashed_input_password !== $row_getInformationUser['acco_password']) {
    http_response_code(401);
    echo json_encode(['error' => 'Usuario o contraseña incorrectos']);
    mysqli_stmt_close($stmt);
    exit;
}

$acco_id = $row_getInformationUser['acco_id'];
$acco_name = $row_getInformationUser['acco_name'];
$acco_email = $row_getInformationUser['acco_email'];
$acco_role = $row_getInformationUser["acco_role"];
$acco_status = $row_getInformationUser["acco_status"];
$first_login = $row_getInformationUser["first_login"];

mysqli_stmt_close($stmt);

$jwt = fnt_createJWT_v001($acco_id,$acco_name,$acco_email,$acco_role,$acco_status, $first_login, $JWT_DURATION_TELAT);

setcookie("jwt", $jwt, [
    'expires' => time() + $JWT_DURATION_TELAT,
    'path' => '/',
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Strict'
]);

echo json_encode(['success' => true, 'message' => 'Inicio de sesión exitoso']);
http_response_code(200);
exit;
