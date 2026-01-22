<?php
$AVALIABLE_METHODS = ['GET', 'PUT', 'DELETE'];

header('Content-Type: application/json');

if (!in_array($_SERVER['REQUEST_METHOD'], $AVALIABLE_METHODS)) {
  http_response_code(405);
  echo json_encode(['error' => 'The request resource does not support HTTP method ' . $_SERVER['REQUEST_METHOD']]);
  exit;
}
//  Update the paths as needeed
require_once __DIR__ . "/../../../config/cors.php";
require_once __DIR__ . "/../../../utils/token/pre_validate.php";
require_once __DIR__ . "/../../../utils/input/input_parser.php";
require_once __DIR__ . "/../../../utils/output/parse_custom_request.php";

$valid_acco_roles = ['professor', 'admin', 'student'];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
  $status = isset($_GET['status']) ? (int) $_GET['status'] : null;
  $acco_role = isset($_GET['acco_role']) ? $_GET['acco_role'] : null;
  $acco_role_exclude = isset($_GET['acco_role_exclude']) ? $_GET['acco_role_exclude'] : null;
  $from_admin_panel = isset($_GET['from_admin_panel']) ? (int) $_GET['from_admin_panel'] : 0;

  $conds = [];
  $params = [];
  $types = '';

  if ($status !== null) {
    if ($status !== 0 && $status !== 1) {
      http_response_code(400);
      echo json_encode(['error' => "El parámetro 'status' no es válido"]);
      exit;
    }
    $conds[] = "acco_status = ?";
    $params[] = $status;
    $types .= 'i';
  }

  if ($acco_role !== null) {
    if (!in_array($acco_role, $valid_acco_roles, true)) {
      http_response_code(400);
      echo json_encode(['error' => "El parámetro 'acco_role' no es válido"]);
      exit;
    }
    $conds[] = "acco_role = ?";
    $params[] = $acco_role;
    $types .= 's';
  }

  if ($acco_role_exclude !== null) {
    if (!in_array($acco_role_exclude, $valid_acco_roles, true)) {
      http_response_code(400);
      echo json_encode(['error' => "El parámetro 'acco_role_exclude' no es válido", 'acco_role_exclude' => $acco_role_exclude]);
      exit;
    }
    $conds[] = "acco_role != ?";
    $params[] = $acco_role_exclude;
    $types .= 's';
  }

  if ($from_admin_panel === 0 || $AUTH['acco_role'] !== 'admin') {
    $conds[] = "acco_id = ?";
    $params[] = $AUTH['acco_id'];
    $types .= 'i';
  }

  $query = "SELECT * FROM account";

  if (count($conds) > 0) {
    $query .= " WHERE " . implode(" AND ", $conds);
  }

  $stmt = mysqli_prepare($DB_T, $query);
  if (count($params) > 0) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
  }
  mysqli_stmt_execute($stmt);
  $result = mysqli_stmt_get_result($stmt);
  $accounts = [];
  while ($row = mysqli_fetch_assoc($result)) {
    unset($row['acco_password']);
    unset($row['created_at']);
    unset($row['first_login']);
    $accounts[] = $row;
  }
  foreach ($accounts as &$account) {
    if ($account['acco_role'] === 'student') {
      $student_query = "SELECT * FROM student WHERE acco_id = ?";
      $student_stmt = mysqli_prepare($DB_T, $student_query);
      mysqli_stmt_bind_param($student_stmt, 'i', $account['acco_id']);
      mysqli_stmt_execute($student_stmt);
      $student_result = mysqli_stmt_get_result($student_stmt);
      $student_data = mysqli_fetch_assoc($student_result);
      if ($student_data) {
        $account = array_merge($account, $student_data);
      }
    } else {
      $professor_query = "SELECT * FROM professor WHERE acco_id = ?";
      $professor_stmt = mysqli_prepare($DB_T, $professor_query);
      mysqli_stmt_bind_param($professor_stmt, 'i', $account['acco_id']);
      mysqli_stmt_execute($professor_stmt);
      $professor_result = mysqli_stmt_get_result($professor_stmt);
      $professor_data = mysqli_fetch_assoc($professor_result);
      if ($professor_data) {
        $account = array_merge($account, $professor_data);
      }
    }
  }
  http_response_code(200);
  echo json_encode(['data' => $accounts, 'count' => count($accounts)]);
}


if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
  $_PUT = fnt_parseInputMultiPart();
  $acco_id = isset($_PUT['acco_id']) ? (int) $_PUT['acco_id'] : null;
  $acco_role = isset($_PUT['acco_role']) ? $_PUT['acco_role'] : null;
  $from_admin_panel = isset($_PUT['from_admin_panel']) ? (int) $_PUT['from_admin_panel'] : 0;
  $required_params = ['acco_id'];
  $missing_params = [];
  foreach ($required_params as $param) {
    if (!isset($_PUT[$param])) {
      $missing_params[] = $param;
    }
  }
  if (count($missing_params) > 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Faltan parámetros requeridos: ' . implode(', ', $missing_params)]);
    exit;
  }
  if ($acco_id === $AUTH['acco_id'] && $from_admin_panel === 1) {
    http_response_code(400);
    echo json_encode(['error' => 'No puedes modificar tu propio usuario']);
    exit;
  }
  if ($AUTH['acco_role'] !== 'admin' && $acco_id !== $AUTH['acco_id']) {
    http_response_code(403);
    echo json_encode(['error' => 'No tienes permiso para acceder a este recurso']);
    exit;
  }
  $update_fields = [];
  $params = [];
  $types = '';
  if ($acco_role !== null) {
    if (!in_array($acco_role, $valid_acco_roles, true)) {
      http_response_code(400);
      echo json_encode(['error' => "El parámetro 'acco_role' no es válido"]);
      exit;
    }
    $update_fields[] = "acco_role = ?";
    $params[] = $acco_role;
    $types .= 's';
  }
  if (count($update_fields) === 0) {
    http_response_code(200);
    echo json_encode(['message' => 'No se realizaron cambios']);;
    exit;
  }
  $params[] = $acco_id;
  $types .= 'i';
  $query = "UPDATE account SET " . implode(", ", $update_fields) . " WHERE acco_id = ?";
  $stmt = mysqli_prepare($DB_T, $query);
  mysqli_stmt_bind_param($stmt, $types, ...$params);
  if (mysqli_stmt_execute($stmt)) {
    http_response_code(200);
    echo json_encode(['message' => 'Usuario actualizado correctamente']);
  } else {
    http_response_code(500);
    echo json_encode(['error' => 'Error al actualizar el usuario']);
  }
}

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
  $_DELETE = fnt_parseInputMultiPart();
  $acco_id = isset($_DELETE['acco_id']) ? (int) $_DELETE['acco_id'] : null;
  $required_params = ['acco_id'];
  $missing_params = [];
  foreach ($required_params as $param) {
    if (!isset($_DELETE[$param])) {
      $missing_params[] = $param;
    }
  }
  if (count($missing_params) > 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Faltan parámetros requeridos: ' . implode(', ', $missing_params)]);
    exit;
  }
  if ($acco_id === $AUTH['acco_id']) {
    http_response_code(400);
    echo json_encode(['error' => 'No puedes deshabilitar tu propio usuario']);
    exit;
  }
  if ($AUTH['acco_role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'No tienes permiso para acceder a este recurso']);
    exit;
  }
  //soft delete
  $query = "UPDATE account SET acco_status = 0 WHERE acco_id = ?";
  $stmt = mysqli_prepare($DB_T, $query);
  mysqli_stmt_bind_param($stmt, 'i', $acco_id);
  if (mysqli_stmt_execute($stmt)) {
    http_response_code(200);
    echo json_encode(['message' => 'Usuario deshabilitado correctamente']);
  } else {
    http_response_code(500);
    echo json_encode(['error' => 'Error al deshabilitar el usuario']);
  }
}
