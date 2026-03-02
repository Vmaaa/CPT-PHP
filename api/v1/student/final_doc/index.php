<?php
$AVALIABLE_METHODS = ['POST'];
header('Content-Type: application/json');

require_once dirname(__DIR__, 4) . "/config/cors.php";
require_once dirname(__DIR__, 4) . "/utils/token/pre_validate.php";
require_once dirname(__DIR__, 4) . "/functions/serverSpecifics.php";

$SS = ServerSpecifics::getInstance();
$DB = $SS->fnt_getDBConnection();

try {
  $DB->begin_transaction();

  $accountId = (int)$AUTH['acco_id'];

  $sqlStudentInfo = "SELECT id_career, first_half, year FROM student WHERE acco_id = ? LIMIT 1";
  $stmtStudent = $DB->prepare($sqlStudentInfo);
  $stmtStudent->bind_param("i", $accountId);
  $stmtStudent->execute();
  $studentInfo = $stmtStudent->get_result()->fetch_assoc();

  if (!$studentInfo) throw new Exception("Perfil de estudiante no configurado.");

  $sqlProject = "
    SELECT p.id_final_project, p.status 
    FROM fp_student fs
    JOIN final_project p ON p.id_final_project = fs.id_final_project
    WHERE fs.acco_id = ? LIMIT 1
  ";
  $stmtProject = $DB->prepare($sqlProject);
  $stmtProject->bind_param("i", $accountId);
  $stmtProject->execute();
  $project = $stmtProject->get_result()->fetch_assoc();

  if (!$project || $project['status'] !== 'APPROVED') {
    throw new Exception("Acceso denegado. No tienes un protocolo aprobado pendiente de subir.");
  }

  $idProject = (int)$project['id_final_project'];

  $sqlCalendar = "
      SELECT id_calendar_events FROM calendar_events
      WHERE stage = 'upload_final_documents' AND id_career = ? AND year = ? AND first_half = ?
      AND NOW() BETWEEN start_date AND end_date LIMIT 1
  ";
  $stmtCal = $DB->prepare($sqlCalendar);
  $stmtCal->bind_param("iii", $studentInfo['id_career'], $studentInfo['year'], $studentInfo['first_half']);
  $stmtCal->execute();
  if (!$stmtCal->get_result()->fetch_assoc()) {
    throw new Exception("El periodo para subir documentos finales está cerrado en el calendario académico.");
  }

  if (!isset($_FILES['final_file']) || $_FILES['final_file']['error'] !== UPLOAD_ERR_OK) {
    throw new Exception("Error al subir el archivo. Asegúrate de adjuntar un PDF.");
  }
  if ($_FILES['final_file']['type'] !== 'application/pdf') {
    throw new Exception("El archivo debe ser forzosamente un PDF.");
  }
  if ($_FILES['final_file']['size'] > 20 * 1024 * 1024) {
    throw new Exception("El archivo excede el límite de 20 MB.");
  }

  $webBasePath = "/CPT/uploads/final_docs";
  $baseUploadDir = $_SERVER['DOCUMENT_ROOT'] . $webBasePath;
  if (!is_dir($baseUploadDir)) mkdir($baseUploadDir, 0777, true);

  $accountDir = $baseUploadDir . "/acco_" . $accountId;
  $accountWebUrl = $webBasePath . "/acco_" . $accountId;
  if (!is_dir($accountDir)) mkdir($accountDir, 0777, true);

  $fileName = uniqid("finaldoc_", true) . ".pdf";
  $filePathDisk = $accountDir . "/" . $fileName;
  $fileUrlDB = $accountWebUrl . "/" . $fileName;

  if (!move_uploaded_file($_FILES['final_file']['tmp_name'], $filePathDisk)) {
    throw new Exception("No se pudo guardar el archivo en el servidor.");
  }

  $newStatus = 'FINAL_UNDER_REVIEW';
  $updSql = "UPDATE final_project SET status=? WHERE id_final_project=?";
  $stmtUpd = $DB->prepare($updSql);
  $stmtUpd->bind_param("si", $newStatus, $idProject);
  $stmtUpd->execute();

  $stageSql = "SELECT COALESCE(MAX(stage), 0) + 1 AS next_stage FROM fp_change WHERE id_final_project = ?";
  $stmtStg = $DB->prepare($stageSql);
  $stmtStg->bind_param("i", $idProject);
  $stmtStg->execute();
  $nextStage = $stmtStg->get_result()->fetch_assoc()['next_stage'];

  $insChg = "INSERT INTO fp_change (id_final_project, stage, file_url) VALUES (?, ?, ?)";
  $stmtChg = $DB->prepare($insChg);
  $stmtChg->bind_param("iis", $idProject, $nextStage, $fileUrlDB);
  $stmtChg->execute();
  $idNewChange = $DB->insert_id;

  $prevStage = $nextStage - 1;
  $sqlOldReviewers = "
      SELECT DISTINCT id_professor 
      FROM fp_change_review fcr
      JOIN fp_change fc ON fc.id_fp_change = fcr.id_fp_change
      WHERE fc.id_final_project = ? AND fc.stage = ?
  ";
  $stmtOld = $DB->prepare($sqlOldReviewers);
  $stmtOld->bind_param("ii", $idProject, $prevStage);
  $stmtOld->execute();
  $resOld = $stmtOld->get_result();

  $insNewRev = $DB->prepare("INSERT INTO fp_change_review (id_professor, id_fp_change, file_url) VALUES (?, ?, ?)");
  while ($rowProv = $resOld->fetch_assoc()) {
    $profId = $rowProv['id_professor'];
    $insNewRev->bind_param("iis", $profId, $idNewChange, $fileUrlDB);
    $insNewRev->execute();
  }

  $DB->commit();

  echo json_encode([
    'success' => true,
    'message' => 'Documento final subido correctamente.',
    'new_status' => $newStatus,
    'stage' => $nextStage
  ]);
} catch (Exception $e) {
  if (isset($DB)) $DB->rollback();
  http_response_code(500);
  echo json_encode(['error' => $e->getMessage()]);
}
