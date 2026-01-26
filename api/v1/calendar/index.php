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
require_once __DIR__ . "/../../../utils/general.php";


if ($_SERVER['REQUEST_METHOD'] === 'GET') {
  $query = "SELECT ce.*, c.* FROM calendar_events ce INNER JOIN career c ON ce.id_career = c.id_career";

  $events = [];
  $stmt = mysqli_prepare($DB_T, $query);
  mysqli_stmt_execute($stmt);
  $result = mysqli_stmt_get_result($stmt);
  while ($row = mysqli_fetch_assoc($result)) {
    $row['humanized_stage'] = getHumanizedType($row['stage']);
    $events[] = $row;
  }
  echo json_encode(['data' => $events]);
  http_response_code(200);
  exit;
}
