<?php
$AVALIABLE_METHODS = ['GET'];

require_once dirname(__DIR__, 4) . "/config/cors.php";
require_once dirname(__DIR__, 4) . "/utils/token/pre_validate.php";
require_once dirname(__DIR__, 4) . "/functions/serverSpecifics.php";


if (!isset($_GET['id'])) {
  http_response_code(400);
  exit;
}

$projectId = (int)$_GET['id'];
$DB = ServerSpecifics::getInstance()->fnt_getDBConnection();

$stmt = $DB->prepare("SELECT file_url FROM fp_change WHERE id_final_project = ? ORDER BY created_at DESC LIMIT 1");
$stmt->bind_param("i", $projectId);
$stmt->execute();
$res = $stmt->get_result()->fetch_assoc();

if (!$res || empty($res['file_url'])) {
  http_response_code(404);
  die("Archivo no encontrado.");
}

$webPath = $res['file_url'];
$physicalPath = $_SERVER['DOCUMENT_ROOT'] . $webPath;

if (!file_exists($physicalPath)) {
  http_response_code(404);
  die("El archivo físico no existe.");
}

header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="protocolo.pdf"');
header('Content-Length: ' . filesize($physicalPath));
readfile($physicalPath);
exit;
