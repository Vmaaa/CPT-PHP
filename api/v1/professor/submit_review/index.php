<?php
$AVALIABLE_METHODS = ['POST'];
header('Content-Type: application/json');

require_once dirname(__DIR__, 4) . "/config/cors.php";
require_once dirname(__DIR__, 4) . "/utils/token/pre_validate.php";
require_once dirname(__DIR__, 4) . "/functions/serverSpecifics.php";


$DB = ServerSpecifics::getInstance()->fnt_getDBConnection();

try {
  $reviewId = (int)($_POST['id_fp_change_review'] ?? 0);
  $decision = $_POST['decision'] ?? null;
  $comments = $_POST['comments'] ?? null;

  if (!$reviewId || !$decision) {
    throw new Exception("Datos incompletos");
  }

  $stmt = $DB->prepare("
    UPDATE fp_change_review
    SET decision = ?, comments = ?, reviewed_at = NOW()
    WHERE id_fp_change_review = ?
      AND id_professor = ?
  ");

  $stmt->bind_param(
    "ssii",
    $decision,
    $comments,
    $reviewId,
    $AUTH['id_professor']
  );

  $stmt->execute();

  echo json_encode(['success' => true]);
} catch (Exception $e) {
  http_response_code(500);
  echo json_encode(['error' => $e->getMessage()]);
}
