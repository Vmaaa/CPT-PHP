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

function fnt_retrieveUserRoleFromEmail_v001($email) {
    $studentDomains = ['alumno.ipn.mx'];
    $domain = substr(strrchr($email, "@"), 1);
    if (in_array($domain, $studentDomains, true)) {
        return 'student';
    }
    return 'professor';
    // TODO: add extra logic for other roles
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => "El recurso actual no soporta el método HTTP: " . $_SERVER['REQUEST_METHOD']]);
    exit;
}

$required = ['acco_name', 'acco_email','curp'];

if ($missingParams = fnt_validateRequiredParams($required, $_POST)) {
    http_response_code(400);
    echo json_encode(['error' => 'Parámetros incompletos, se requiere ' . implode(', ', $missingParams)]);
    exit;
}

$acco_name = trim((string) $_POST['acco_name']);
$acco_email = strtolower(trim((string) $_POST['acco_email']));
$acco_status = isset($_POST['acco_status']) ? (int) $_POST['acco_status'] : 1;
$curp = trim((string) ($_POST['curp']));

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

$acco_role = fnt_retrieveUserRoleFromEmail_v001($acco_email);

if (!in_array($acco_status, [0, 1], true)) {
    http_response_code(400);
    echo json_encode(['error' => "El 'acco_status' debe ser 0 o 1"]);
    exit;
}

if(!fnt_validateCURP($curp)) {
    http_response_code(400);
    echo json_encode(['error' => "El 'curp' proporcionado no es válido"]);
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

$qry_insert = "INSERT INTO account (acco_email, acco_password, acco_status, acco_role, first_login)
               VALUES (?, ?, ?, ?, 1)";
$stmt_insert = mysqli_prepare($DB_T, $qry_insert);
if (!$stmt_insert) {
    mysqli_rollback($DB_T);
    releaseLock($DB_T, $lockEmailKey);
    http_response_code(500);
    echo json_encode(['error' => 'Preparación de consulta fallida (insert)']);
    exit;
}
mysqli_stmt_bind_param($stmt_insert, 'ssis', $acco_email, $hashed_password, $acco_status, $acco_role);
try {
    mysqli_stmt_execute($stmt_insert);
} catch (Exception $e) {
    mysqli_stmt_close($stmt_insert);
    mysqli_rollback($DB_T);
    releaseLock($DB_T, $lockEmailKey);
    http_response_code(500);
    echo json_encode(['error' => 'Error al insertar el nuevo usuario']);
    exit;
}

mysqli_stmt_close($stmt_insert);
$acco_id = (int) mysqli_insert_id($DB_T);

// Register the correspindg profile based on role
if ($acco_role === 'student') {
  $requiredStudentParams = ['school_id_number', 'id_career'];
  if ($missingStudentParams = fnt_validateRequiredParams($requiredStudentParams, $_POST)) {
      mysqli_rollback($DB_T);
      releaseLock($DB_T, $lockEmailKey);
      http_response_code(400);
      echo json_encode(['error' => 'Parámetros incompletos para perfil de estudiante, se requiere ' . implode(', ', $missingStudentParams)]);
      exit;
  }
  $school_id_number = trim((string) $_POST['school_id_number']);
  $id_career = (int) $_POST['id_career'];
  if(!fnt_validateSchoolIDNumber_v001($school_id_number)) {
      mysqli_rollback($DB_T);
      releaseLock($DB_T, $lockEmailKey);
      http_response_code(400);
      echo json_encode(['error' => "El 'school_id_number' proporcionado no es válido"]);
      exit;
  }
  $qry_insert_student = "INSERT INTO student (acco_id, name, curp, school_id_number, id_career)
    VALUES (?, ?, ?, ?, ?)";
  $stmt_insert_student = mysqli_prepare($DB_T, $qry_insert_student);
  if (!$stmt_insert_student) {
    mysqli_rollback($DB_T);
    releaseLock($DB_T, $lockEmailKey);
    http_response_code(500);
    echo json_encode(['error' => 'Preparación de consulta fallida (insert student profile)']);
    exit;
  }
  mysqli_stmt_bind_param($stmt_insert_student, 'isssi', $acco_id, $acco_name, $curp, $school_id_number, $id_career);
  try {
    mysqli_stmt_execute($stmt_insert_student);
  } catch (Exception $e) {
    mysqli_stmt_close($stmt_insert_student);
    mysqli_rollback($DB_T);
    releaseLock($DB_T, $lockEmailKey);
    http_response_code(500);
    echo json_encode(['error' => 'Error al insertar el perfil de estudiante']);
    exit;
  }
  mysqli_stmt_close($stmt_insert_student);
}
else {
  $requriedProfessorParams = ['is_president','academia','level_of_education'];
  if ($missingProfessorParams = fnt_validateRequiredParams($requriedProfessorParams, $_POST)) {
      mysqli_rollback($DB_T);
      releaseLock($DB_T, $lockEmailKey);
      http_response_code(400);
      echo json_encode(['error' => 'Parámetros incompletos para perfil de profesor, se requiere ' . implode(', ', $missingProfessorParams)]);
      exit;
  }
  $is_president = (int) $_POST['is_president'];
  $academia = trim((string) $_POST['academia']);
  $level_of_education = trim((string) $_POST['level_of_education']);

  if (!in_array($is_president, [0, 1], true)) {
      mysqli_rollback($DB_T);
      releaseLock($DB_T, $lockEmailKey);
      http_response_code(400);
      echo json_encode(['error' => "El 'is_president' debe ser 0 o 1"]);
      exit;
  }
  if(!in_array($level_of_education, ['bachelor\'s','master\'s','doctorate'], true)) {
      mysqli_rollback($DB_T);
      releaseLock($DB_T, $lockEmailKey);
      http_response_code(400);
      echo json_encode(['error' => "El 'level_of_education' debe ser 'bachelor\'s', 'master\'s' o 'doctorate'"]);
      exit;

  }

  $qry_insert_professor = "INSERT INTO professor (acco_id, name, curp, is_president, academia, level_of_education)
    VALUES (?, ?, ?, ?, ?, ?)";
  $stmt_insert_professor = mysqli_prepare($DB_T, $qry_insert_professor);
  if (!$stmt_insert_professor) {
    mysqli_rollback($DB_T);
    releaseLock($DB_T, $lockEmailKey);
    http_response_code(500);
    echo json_encode(['error' => 'Preparación de consulta fallida (insert professor profile)']);
    exit;
  }
  mysqli_stmt_bind_param($stmt_insert_professor, 'ississ', $acco_id, $acco_name, $curp, $is_president, $academia, $level_of_education);
  try {
    mysqli_stmt_execute($stmt_insert_professor);
  } catch (Exception $e) {
    mysqli_stmt_close($stmt_insert_professor);
    mysqli_rollback($DB_T);
    releaseLock($DB_T, $lockEmailKey);
    http_response_code(500);
    echo json_encode(['error' => 'Error al insertar el perfil de profesor']);
    exit;
  }
  mysqli_stmt_close($stmt_insert_professor);
}

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

