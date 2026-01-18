<?php
$AVALIABLE_METHODS = ['GET', 'POST', 'PUT', 'DELETE'];

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
  $_GET;
  // try catch block only for sql stmt execution
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $_POST;
  // try catch block only for sql stmt execution
}

if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
  $_PUT = fnt_parseInputMultiPart();
  // try catch block only for sql stmt execution
}

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
  $_DELETE = fnt_parseInputMultiPart();
  // try catch block only for sql stmt execution
}
