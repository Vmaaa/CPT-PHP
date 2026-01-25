<?php
$AVALIABLE_METHODS = ['POST'];
header('Content-Type: application/json');

require_once dirname(__DIR__, 4) . "/config/cors.php";
require_once dirname(__DIR__, 4) . "/utils/token/pre_validate.php";
require_once dirname(__DIR__, 4) . "/functions/serverSpecifics.php";

$DB = ServerSpecifics::getInstance()->fnt_getDBConnection();

try {
  if (!isset($AUTH['id_professor'])) {
    $stmt = $DB->prepare("SELECT id_professor FROM professor WHERE acco_id = ?");
    $stmt->bind_param("i", $AUTH['acco_id']);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    if (!$res) throw new Exception("Perfil de profesor no encontrado");
    $idProfessor = $res['id_professor'];
  } else {
    $idProfessor = $AUTH['id_professor'];
  }

  $reviewId = (int)($_POST['id_fp_change_review'] ?? 0);
  $decision = $_POST['decision'] ?? null;
  $comments = $_POST['comments'] ?? '';

  if (!$reviewId || !$decision) {
    throw new Exception("Debes seleccionar un dictamen.");
  }

  $reviewerPdfUrl = null;

  if (isset($_FILES['reviewer_file']) && $_FILES['reviewer_file']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['reviewer_file'];

    if ($file['type'] !== 'application/pdf') {
      throw new Exception("El archivo debe ser PDF.");
    }

    $webBasePath = "/CPT/uploads/reviews";
    $baseUploadDir = $_SERVER['DOCUMENT_ROOT'] . $webBasePath;

    if (!is_dir($baseUploadDir)) {
      mkdir($baseUploadDir, 0777, true);
    }

    $fileName = "dictamen_" . $reviewId . "_" . uniqid() . ".pdf";
    $targetPath = $baseUploadDir . "/" . $fileName;

    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
      throw new Exception("Error al guardar el archivo.");
    }

    $reviewerPdfUrl = $webBasePath . "/" . $fileName;
  }

  $grade = ($decision === 'APPROVED') ? 1 : 0;

  if ($reviewerPdfUrl) {
    $sql = "UPDATE fp_change_review 
                SET grade = ?, comment = ?, reviewer_pdf_url = ? 
                WHERE id_fp_change_review = ? AND id_professor = ?";
    $stmt = $DB->prepare($sql);
    $stmt->bind_param("issii", $grade, $comments, $reviewerPdfUrl, $reviewId, $idProfessor);
  } else {
    $sql = "UPDATE fp_change_review 
                SET grade = ?, comment = ? 
                WHERE id_fp_change_review = ? AND id_professor = ?";
    $stmt = $DB->prepare($sql);
    $stmt->bind_param("isii", $grade, $comments, $reviewId, $idProfessor);
  }

  $stmt->execute();

  if ($stmt->affected_rows === 0 && $stmt->errno === 0) {
    throw new Exception("No se encontró la revisión o no tienes permiso para modificarla.");
  }

  echo json_encode(['success' => true, 'pdf_url' => $reviewerPdfUrl]);
} catch (Exception $e) {
  http_response_code(500);
  echo json_encode(['error' => $e->getMessage()]);
}
