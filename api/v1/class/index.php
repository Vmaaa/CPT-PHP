<?php
$AVALIABLE_METHODS = ['GET', 'POST', 'PUT'];

header('Content-Type: application/json');

if (!in_array($_SERVER['REQUEST_METHOD'], $AVALIABLE_METHODS)) {
  http_response_code(405);
  echo json_encode(['error' => 'Método HTTP no soportado']);
  exit;
}

require_once __DIR__ . "/../../../config/cors.php";
require_once __DIR__ . "/../../../utils/token/pre_validate.php";
require_once __DIR__ . "/../../../utils/input/input_parser.php";

if (!in_array($AUTH['acco_role'], ['admin', 'professor'])) {
  http_response_code(403);
  echo json_encode(['error' => 'Acceso denegado']);
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
  $id_class  = isset($_GET['id_class']) ? (int) $_GET['id_class'] : null;
  $id_career = isset($_GET['id_career']) ? (int) $_GET['id_career'] : null;
  $from_admin_panel = isset($_GET['from_admin_panel']) ? (int) $_GET['from_admin_panel'] : 0;

  $conds = [];
  $params = [];
  $types = '';

  if ($id_class !== null) {
    $conds[] = "id_class = ?";
    $params[] = $id_class;
    $types .= 'i';
  }

  if ($id_career !== null) {
    $conds[] = "id_career = ?";
    $params[] = $id_career;
    $types .= 'i';
  }

  if ($from_admin_panel === 0 || $AUTH['acco_role'] === 'professor') {
    $conds[] = "id_class IN (
      SELECT cp.id_class
      FROM class_professor cp
      WHERE cp.id_professor = ?
    )";
    $params[] = $AUTH['id_professor'];
    $types .= 'i';
  }

  $query = "SELECT * FROM class";

  if ($conds) {
    $query .= " WHERE " . implode(" AND ", $conds);
  }

  $stmt = mysqli_prepare($DB_T, $query);
  if ($params) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
  }

  mysqli_stmt_execute($stmt);
  $result = mysqli_stmt_get_result($stmt);

  $classes = [];

  while ($row = mysqli_fetch_assoc($result)) {
    $row['professors'] = [];
    $row['students'] = [];
    $classes[$row['id_class']] = $row;
  }

  foreach ($classes as $id_class => &$class) {
    //petición a la api de professors
    $ch = curl_init($API_URL . "/class/professor/?id_class=" . $id_class);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
      'Cookie: jwt=' . ($_COOKIE['jwt'] ?? '')
    ]);
    $response = curl_exec($ch);
    $professors_data = json_decode($response, true);
    foreach ($professors_data['data'] as $prof) {
      $class['professors'][] = $prof;
    }
    //peticion a la api de students
    $ch = curl_init($API_URL . "/class/student/?id_class=" . $id_class);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
      'Cookie: jwt=' . ($_COOKIE['jwt'] ?? '')
    ]);
    $response = curl_exec($ch);
    $students_data = json_decode($response, true);
    foreach ($students_data['data'] as $stud) {
      $class['students'][] = $stud;
    }
  }

  http_response_code(200);
  echo json_encode([
    'data' => array_values($classes),
    'count' => count($classes),
  ]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $required = ['name', 'id_career'];
  $missing = [];

  foreach ($required as $r) {
    if (!isset($_POST[$r])) {
      $missing[] = $r;
    }
  }

  if ($missing) {
    http_response_code(400);
    echo json_encode(['error' => 'Faltan parámetros: ' . implode(', ', $missing)]);
    exit;
  }

  $query = "INSERT INTO class (name, id_career) VALUES (?, ?)";
  $stmt = mysqli_prepare($DB_T, $query);
  mysqli_stmt_bind_param(
    $stmt,
    'si',
    $_POST['name'],
    $_POST['id_career']
  );

  if (mysqli_stmt_execute($stmt)) {
    http_response_code(201);
    echo json_encode(['message' => 'Clase creada correctamente', 'id_class' => mysqli_insert_id($DB_T)]);
  } else {
    http_response_code(500);
    echo json_encode(['error' => 'Error al crear la clase']);
  }
}

if ($_SERVER['REQUEST_METHOD'] === 'PUT') {

  $_PUT = fnt_parseInputMultiPart();

  if (!isset($_PUT['id_class'])) {
    http_response_code(400);
    echo json_encode(['error' => 'id_class es requerido']);
    exit;
  }

  $fields = [];
  $params = [];
  $types = '';

  if (isset($_PUT['name'])) {
    $fields[] = "name = ?";
    $params[] = $_PUT['name'];
    $types .= 's';
  }

  if (!$fields) {
    http_response_code(200);
    echo json_encode(['message' => 'No se realizaron cambios']);
    exit;
  }

  $params[] = (int) $_PUT['id_class'];
  $types .= 'i';

  $query = "UPDATE class SET " . implode(', ', $fields) . " WHERE id_class = ?";
  $stmt = mysqli_prepare($DB_T, $query);
  mysqli_stmt_bind_param($stmt, $types, ...$params);

  mysqli_stmt_execute($stmt);
  http_response_code(200);
  echo json_encode(['message' => 'Clase actualizada correctamente']);
}
