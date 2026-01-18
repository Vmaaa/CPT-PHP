<?php
$AVALIABLE_METHODS = ['GET'];

header('Content-Type: application/json');

if (!in_array($_SERVER['REQUEST_METHOD'], $AVALIABLE_METHODS)) {
  http_response_code(405);
  echo json_encode(['error' => 'The request resource does not support HTTP method ' . $_SERVER['REQUEST_METHOD']]);
  exit;
}
//  Update the paths as needeed
require_once __DIR__ . "/../../../config/cors.php";
require_once __DIR__ . "/../../../utils/input/input_parser.php";
require_once __DIR__ . "/../../../utils/output/parse_custom_request.php";
require_once __DIR__ . "/../../../functions/serverSpecifics.php";
$SS = ServerSpecifics::getInstance();
$DB_T = $SS->fnt_getDBConnection();

/*   predefined variables
 *   $MAIN_DB : mysqli connection
 *   $SS : ServerSpecifics instance
 *   $AUTH : authenticated user data - array with keys:
 *   - acco_id
 *   - acco_email
 *   - acco_name
 *   - acco_role
 *   - acco_status
 */


if ($_SERVER['REQUEST_METHOD'] === 'GET') {
  $query = "SELECT * FROM career";
  try {
    $stmt = mysqli_prepare($DB_T, $query);
    if (!$stmt) {
      throw new Exception("Failed to prepare statement: " . mysqli_error($DB_T));
    }

    if (!mysqli_stmt_execute($stmt)) {
      throw new Exception("Failed to execute statement: " . mysqli_stmt_error($stmt));
    }

    $result = mysqli_stmt_get_result($stmt);
    $careers = [];
    while ($row = mysqli_fetch_assoc($result)) {
      $careers[] = $row;
    }

    mysqli_stmt_close($stmt);

    echo json_encode(['data' => $careers, 'count' => count($careers)]);
  } catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'There was an error retrieving career data',]);
  }
}
