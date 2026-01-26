<?php
$AVALIABLE_METHODS = ['GET'];

header('Content-Type: application/json');

if (!in_array($_SERVER['REQUEST_METHOD'], $AVALIABLE_METHODS)) {
  http_response_code(405);
  echo json_encode(['error' => 'Método HTTP no soportado']);
  exit;
}

require_once __DIR__ . "/../../../config/cors.php";
require_once __DIR__ . "/../../../utils/token/pre_validate.php";
require_once __DIR__ . "/../../../utils/input/input_parser.php";

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
  $without_class = isset($_GET['without_class']) ? (int) $_GET['without_class'] : 0;
  $id_career = isset($_GET['id_career']) ? (int) $_GET['id_career'] : null;

  $conds = [];
  $params = [];
  $types = '';


  $query = "SELECT s.* FROM student s";

  if ($without_class === 1) {
    $conds[] = "s.id_class IS NULL";
  }

  if ($id_career !== null) {
    $conds[] = "s.id_career = ?";
    $params[] = $id_career;
    $types .= 'i';
  }

  if ($conds) {
    $query .= " WHERE " . implode(" AND ", $conds);
  }

  $stmt = mysqli_prepare($DB_T, $query);
  if ($params) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
  }
  mysqli_stmt_execute($stmt);

  $result = mysqli_stmt_get_result($stmt);
  $data = [];

  while ($row = mysqli_fetch_assoc($result)) {
    $data[] = $row;
  }

  http_response_code(200);
  echo json_encode(['data' => $data]);
}
