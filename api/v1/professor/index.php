
<?php
$AVALIABLE_METHODS = ['PUT'];

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

if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
  $_PUT = fnt_parseInputMultiPart();
  $acco_id = isset($_PUT['acco_id']) ? (int) $_PUT['acco_id'] : null;
  $is_advisor = isset($_PUT['is_advisor']) ? (int) $_PUT['is_advisor'] : null;
  $is_president = isset($_PUT['is_president']) ? (int) $_PUT['is_president'] : null;
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
  if ($is_advisor !== null) {
    $update_fields[] = "is_advisor = ?";
    $params[] = $is_advisor;
    $types .= 'i';
  }
  if ($is_president !== null) {
    $update_fields[] = "is_president = ?";
    $params[] = $is_president;
    $types .= 'i';
  }
  if (count($update_fields) === 0) {
    http_response_code(200);
    echo json_encode(['message' => 'No se realizaron cambios']);;
    exit;
  }
  $params[] = $acco_id;
  $types .= 'i';
  $query = "UPDATE professor SET " . implode(", ", $update_fields) . " WHERE acco_id = ?";
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
