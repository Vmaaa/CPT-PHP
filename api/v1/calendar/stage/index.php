<?php
$AVALIABLE_METHODS = ['GET'];

header('Content-Type: application/json');

if (!in_array($_SERVER['REQUEST_METHOD'], $AVALIABLE_METHODS)) {
  http_response_code(405);
  echo json_encode(['error' => 'Método HTTP no soportado']);
  exit;
}

require_once __DIR__ . "/../../../../config/cors.php";
require_once __DIR__ . "/../../../../utils/token/pre_validate.php";
require_once __DIR__ . "/../../../../utils/input/input_parser.php";
require_once __DIR__ . "/../../../../utils/general.php";


if ($_SERVER['REQUEST_METHOD'] === 'GET') {
  //retrieve all humanized types
  $humanizedTypes = [];
  foreach (
    [
      'upload_protocols',
      'assign_reviewers',
      'judge_protocols',
      're-upload_protocols',
      'select_protocols',
      'protocol_presentations',
      'grade_protocols',
      'second_protocol_presentations',
      'grade_second_protocols'
    ] as $type
  ) {
    $humanizedTypes[$type] = getHumanizedType($type);
  }
  echo json_encode(['data' => $humanizedTypes]);
}
