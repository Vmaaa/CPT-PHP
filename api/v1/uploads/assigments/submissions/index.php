<?php
$AVALIABLE_METHODS = ['GET'];

header('Content-Type: application/json');

if (!in_array($_SERVER['REQUEST_METHOD'], $AVALIABLE_METHODS)) {
  http_response_code(405);
  echo json_encode(['error' => 'Método HTTP no soportado']);
  exit;
}

require_once __DIR__ . "/../../../../../config/cors.php";
require_once __DIR__ . "/../../../../../utils/token/pre_validate.php";
require_once __DIR__ . "/../../../../../utils/input/input_parser.php";

$UPLOAD_DIR = realpath($SS->fnt_getUploadDir());
if ($UPLOAD_DIR === false) {
  http_response_code(500);
  echo json_encode(['error' => 'Directorio base de uploads no encontrado']);
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
  $file_name = $_GET['file_name'] ?? null;
  $id_class = $_GET['id_class'] ?? null;
  $id_professor = $_GET['id_professor'] ?? null;

  if (!$file_name || !$id_class || !$id_professor) {
    http_response_code(400);
    echo json_encode(['error' => 'Parámetros requeridos: file_name, id_class, id_professor']);
    exit;
  }

  $file_path = $UPLOAD_DIR . '/class_' . $id_class . '/assignments/professor_' . $id_professor . '/submissions/' . basename($file_name);

  if (!file_exists($file_path) || !is_file($file_path)) {
    http_response_code(404);
    echo json_encode(['error' => 'Archivo no encontrado']);
    exit;
  }

  header('Content-Type: application/pdf');
  header('Content-Disposition: inline; filename="' . basename($file_name) . '"');
  header('Content-Length: ' . filesize($file_path));
  readfile($file_path);
  exit;
}
