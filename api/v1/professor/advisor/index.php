
<?php

$AVALIABLE_METHODS = ['GET', 'PUT'];

header('Content-Type: application/json');

if (!in_array($_SERVER['REQUEST_METHOD'], $AVALIABLE_METHODS)) {
  http_response_code(405);
  echo json_encode(['error' => 'The request resource does not support HTTP method ' . $_SERVER['REQUEST_METHOD']]);
  exit;
}
//  Update the paths as needeed
require_once __DIR__ . "/../../../../config/cors.php";
require_once __DIR__ . "/../../../../utils/token/pre_validate.php";
require_once __DIR__ . "/../../../../utils/input/input_parser.php";
require_once __DIR__ . "/../../../../utils/output/parse_custom_request.php";

$valid_acco_roles = ['professor', 'admin', 'student'];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {

  try {
    $query = "
      SELECT
        id_professor,
        name,
        academia,
        level_of_education
      FROM professor
      WHERE is_advisor = 1
      ORDER BY name ASC
    ";

    $stmt = mysqli_prepare($DB_T, $query);
    if (!$stmt) {
      throw new Exception(mysqli_error($DB_T));
    }

    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $professors = [];
    while ($row = mysqli_fetch_assoc($result)) {
      $professors[] = $row;
    }

    mysqli_stmt_close($stmt);

    echo json_encode([
      'data' => $professors,
      'count' => count($professors)
    ]);
  } catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
      'error' => 'Error al obtener profesores',
      'detail' => $e->getMessage()
    ]);
  }

  exit;
}
