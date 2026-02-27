<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$AVALIABLE_METHODS = ['GET', 'POST', 'PUT'];

header('Content-Type: application/json');

if (!in_array($_SERVER['REQUEST_METHOD'], $AVALIABLE_METHODS)) {
  http_response_code(405);
  echo json_encode(['error' => 'Método HTTP no soportado']);
  exit;
}

require_once __DIR__ . "/../../../../config/cors.php";
require_once __DIR__ . "/../../../../utils/token/pre_validate.php";
require_once __DIR__ . "/../../../../utils/input/input_parser.php";

$UPLOAD_DIR = realpath(__DIR__ . '/../../../../uploads/assigments');
if ($UPLOAD_DIR === false) {
  http_response_code(500);
  echo json_encode(['error' => 'Directorio base de uploads no encontrado']);
  exit;
}


if ($_SERVER['REQUEST_METHOD'] === 'GET') {
  $id_assignment = isset($_GET['id_assignment']) ? (int) $_GET['id_assignment'] : null;

  $required_fields = ['id_assignment'];
  $missing_fields = [];
  foreach ($required_fields as $field) {
    if (!isset($_GET[$field])) {
      $missing_fields[] = $field;
    }
  }
  if (!empty($missing_fields)) {
    http_response_code(400);
    echo json_encode(['error' => 'Faltan campos requeridos: ' . implode(', ', $missing_fields)]);
    exit;
  }

  $conds = [];
  $types = '';
  $params = [];


  $conds[] = "a.id_assigment = ?";
  $types .= 'i';
  $params[] = $id_assignment;


  if ($AUTH['acco_role'] === 'student') {
    $conds[] = "ac.acco_id = ?";
    $types .= 'i';
    $params[] = $AUTH['acco_id'];
  }

  $query = "SELECT asu.*, ac.acco_id FROM assigment_submission asu
      INNER JOIN assigment a ON asu.id_assigment = a.id_assigment
      INNER JOIN class c ON a.id_class = c.id_class
      INNER JOIN student st ON asu.id_student = st.id_student
      INNER JOIN account ac ON st.acco_id = ac.acco_id
      WHERE " . implode(' AND ', $conds);


  $stmt = mysqli_prepare($DB_T, $query);
  mysqli_stmt_bind_param($stmt, $types, ...$params);
  mysqli_stmt_execute($stmt);
  $result = mysqli_stmt_get_result($stmt);
  $data = [];

  while ($row = mysqli_fetch_assoc($result)) {
    if ($AUTH['acco_role'] === 'student') {
      $row['can_be_edited'] = ($row['acco_id'] === $AUTH['acco_id']);
    }
    //retrieve all the students related 

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $API_URL . "/student/?acco_id=" . $row['acco_id']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
      'Cookie: jwt=' . $_COOKIE['jwt']
    ]);
    $response = curl_exec($ch);
    $students = json_decode($response, true)['data'];
    $row['students'] = $students;
    $data[] = $row;
  }

  http_response_code(200);
  echo json_encode(['data' => $data]);
}
