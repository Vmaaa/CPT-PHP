<?php
require_once __DIR__ . "/../../../../config/cors.php";
require_once __DIR__ . "/../../../../functions/serverSpecifics.php";
require_once __DIR__ . "/../../../../utils/mail/send.php";
require_once __DIR__ . "/../../../../utils/output/parse_custom_request.php";
require_once __DIR__ . "/../../../../utils/token/create.php";
require_once __DIR__ . "/../../../../utils/general.php";

header('Content-Type: application/json');

function releaseLock($db, $kEmail)
{
    mysqli_query($db, "SELECT RELEASE_LOCK('" . mysqli_real_escape_string($db, $kEmail) . "')");
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => "El recurso actual no soporta el método HTTP: " . $_SERVER['REQUEST_METHOD']]);
    exit;
}

$required = ['acco_name', 'acco_email'];
foreach ($required as $k)
    if (!isset($_POST[$k])) {
        http_response_code(400);
        echo json_encode(['error' => 'Parámetros incompletos, se requiere ' . $k]);
        exit;
    }

$acco_name = trim((string) $_POST['acco_name']);
$acco_email = strtolower(trim((string) $_POST['acco_email']));
$acco_role = $_POST['acco_role'] ?? 'student';
$acco_status = isset($_POST['acco_status']) ? (int) $_POST['acco_status'] : 1;


if(!fnt_validateString_v001($acco_name, 2, 300)) {
    http_response_code(400);
    echo json_encode(['error' => "El 'acco_name' debe tener entre 2 y 300 caracteres"]);
    exit;
}

if (!filter_var($acco_email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['error' => "Formato de 'acco_email' inválido"]);
    exit;
}
if (!in_array($acco_role, ['admin', 'student', 'professor', 'advisor'], true)) {
    http_response_code(400);
    echo json_encode(['error' => 'Rol seleccionado inválido']);
    exit;
}

if (!in_array($acco_status, [0, 1], true)) {
    http_response_code(400);
    echo json_encode(['error' => "El 'acco_status' debe ser 0 o 1"]);
    exit;
}


$plain_password = gen_strong_password(12);
if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/', $plain_password)) {
    http_response_code(500);
    echo json_encode(['error' => 'No se pudo generar una contraseña válida, intenta de nuevo.']);
    exit;
}

$SS = ServerSpecifics::getInstance();
$DB_T = $SS->fnt_getDBConnection();
$WEBPAGE_T = $SS->fnt_getWebPageURL();
$SERVER_URL_T = $SS->fnt_getAPIUrl();

mysqli_begin_transaction($DB_T);
mysqli_set_charset($DB_T, 'utf8mb4');

$lockEmailKey = 'register:email:' . sha1($acco_email);

$ok1 = mysqli_query($DB_T, "SELECT GET_LOCK('" . mysqli_real_escape_string($DB_T, $lockEmailKey) . "', 2) AS l1");
$l1 = $ok1 ? (int) mysqli_fetch_assoc($ok1)['l1'] : -1;

if ($l1 !== 1) {
    mysqli_rollback($DB_T);
    releaseLock($DB_T, $lockEmailKey);
    http_response_code(423);
    echo json_encode(['error' => 'Registro ocupado para este correo electrónico. Por favor, inténtalo de nuevo.']);
    exit;
}

$qry_check = "SELECT acco_email FROM account WHERE acco_email = ? LIMIT 1";
$stmt_check = mysqli_prepare($DB_T, $qry_check);
if (!$stmt_check) {
    mysqli_rollback($DB_T);
    releaseLock($DB_T, $lockEmailKey);
    http_response_code(500);
    echo json_encode(['error' => 'Preparación de consulta fallida (check)']);
    exit;
}
mysqli_stmt_bind_param($stmt_check, 's', $acco_email);
mysqli_stmt_execute($stmt_check);
$res = mysqli_stmt_get_result($stmt_check);
if ($row = mysqli_fetch_assoc($res)) {
    mysqli_stmt_close($stmt_check);
    mysqli_commit($DB_T);
    releaseLock($DB_T, $lockEmailKey);
    http_response_code(409);
    echo json_encode(['error' => "Este 'acco_email' ya fue registrado"]);
    exit;
}
mysqli_stmt_close($stmt_check);

$hashed_password = hash('sha256', $plain_password);

$qry_insert = "INSERT INTO account (acco_name, acco_email, acco_password, acco_status, acco_role, first_login)
               VALUES (?, ?, ?, ?, ?, 1)";
$stmt_insert = mysqli_prepare($DB_T, $qry_insert);
if (!$stmt_insert) {
    mysqli_rollback($DB_T);
    releaseLock($DB_T, $lockEmailKey);
    http_response_code(500);
    echo json_encode(['error' => 'Preparación de consulta fallida (insert)']);
    exit;
}
mysqli_stmt_bind_param($stmt_insert, 'sssis', $acco_name, $acco_email, $hashed_password, $acco_status, $acco_role);
try {
    mysqli_stmt_execute($stmt_insert);
} catch (Exception $e) {
    mysqli_stmt_close($stmt_insert);
    mysqli_rollback($DB_T);
    releaseLock($DB_T, $lockEmailKey);
    http_response_code(500);
    echo json_encode(['error' => 'Error al insertar el nuevo usuario']);
    return;
    
}

mysqli_stmt_close($stmt_insert);
$acco_id = (int) mysqli_insert_id($DB_T);

if (!fnt_sendEmailForNewUser_v001($acco_email, $plain_password, $WEBPAGE_T)) {
    mysqli_rollback($DB_T);
    releaseLock($DB_T, $lockEmailKey);
    http_response_code(500);
    echo json_encode(['error' => 'No se pudo enviar el correo de bienvenida']);
    exit;
}

mysqli_commit($DB_T);
releaseLock($DB_T, $lockEmailKey);

http_response_code(201);
echo json_encode(['success' => true, 'created' => $acco_id, 'strontg_password' => $plain_password]);

