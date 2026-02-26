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
  echo json_encode(['data' => $SS->fnt_getStages()]);
}
