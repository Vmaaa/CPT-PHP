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

  $stmt = $DB->prepare("SELECT id_student FROM student WHERE acco_id = ? LIMIT 1");
  $stmt->bind_param("i", $AUTH['acco_id']);
  $stmt->execute();
  $student = $stmt->get_result()->fetch_assoc();

  if (!$student) throw new Exception("Alumno no encontrado");
  $idStudent = (int)$student['id_student'];

  if (!isset($_FILES['protocol_file']) || $_FILES['protocol_file']['error'] !== UPLOAD_ERR_OK) {
    throw new Exception("Error al subir el archivo");
  }
  if ($_FILES['protocol_file']['type'] !== 'application/pdf') {
    throw new Exception("El archivo debe ser PDF");
  }
  if ($_FILES['protocol_file']['size'] > 10 * 1024 * 1024) {
    throw new Exception("El archivo excede 10 MB");
  }

  if ($_POST['advisor_1'] === $_POST['advisor_2']) {
    throw new Exception("Los asesores deben ser distintos");
  }

  $webBasePath = "/CPT/uploads/protocols";
  $baseUploadDir = $_SERVER['DOCUMENT_ROOT'] . $webBasePath;

  if (!is_dir($baseUploadDir)) mkdir($baseUploadDir, 0777, true);

  $studentDir = $baseUploadDir . "/" . $idStudent;
  $studentWebUrl = $webBasePath . "/" . $idStudent;

  if (!is_dir($studentDir)) mkdir($studentDir, 0777, true);

  $fileName = uniqid("protocol_", true) . ".pdf";
  $filePathDisk = $studentDir . "/" . $fileName;
  $fileUrlDB = $studentWebUrl . "/" . $fileName;

  if (!move_uploaded_file($_FILES['protocol_file']['tmp_name'], $filePathDisk)) {
    throw new Exception("No se pudo guardar el archivo en el servidor");
  }

  $checkSql = "SELECT id_final_project FROM fp_student WHERE id_student = ?";
  $stmtCheck = $DB->prepare($checkSql);
  $stmtCheck->bind_param("i", $idStudent);
  $stmtCheck->execute();
  $existingRes = $stmtCheck->get_result()->fetch_assoc();

  $idProject = 0;

  if ($existingRes) {
    $idProject = (int)$existingRes['id_final_project'];

    $updSql = "UPDATE final_project 
                   SET title=?, abstract=?, id_career=?, status='PENDING' 
                   WHERE id_final_project=?";
    $stmtUpd = $DB->prepare($updSql);
    $stmtUpd->bind_param("ssii", $_POST['title'], $_POST['abstract'], $_POST['id_career'], $idProject);
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

    // 1. Buscamos cuál fue el cambio anterior (stage - 1)
    $prevStage = $nextStage - 1;

    // 2. Buscamos qué profesores revisaron esa versión anterior
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

    $delAdv = "DELETE FROM fp_advisor WHERE id_final_project = ?";
    $stmtDel = $DB->prepare($delAdv);
    $stmtDel->bind_param("i", $idProject);
    $stmtDel->execute();
  } else {
    $stmt = $DB->prepare("INSERT INTO final_project (title, abstract, id_career, status) VALUES (?, ?, ?, 'PENDING')");
    $stmt->bind_param("ssi", $_POST['title'], $_POST['abstract'], $_POST['id_career']);
    $stmt->execute();
    $idProject = (int)$DB->insert_id;

    $stmt = $DB->prepare("INSERT INTO fp_student (id_student, id_final_project) VALUES (?, ?)");
    $stmt->bind_param("ii", $idStudent, $idProject);
    $stmt->execute();

    $stmt = $DB->prepare("INSERT INTO fp_change (id_final_project, stage, file_url) VALUES (?, 1, ?)");
    $stmt->bind_param("is", $idProject, $fileUrlDB);
    $stmt->execute();
  }

  foreach (['advisor_1', 'advisor_2'] as $advisorKey) {
    $stmt = $DB->prepare("INSERT INTO fp_advisor (id_professor, id_final_project) VALUES (?, ?)");
    $stmt->bind_param("ii", $_POST[$advisorKey], $idProject);
    $stmt->execute();
  }

  $DB->commit();

  echo json_encode([
    'success' => true,
    'id_final_project' => $idProject,
    'mode' => ($existingRes ? 'updated' : 'created')
  ]);
} catch (Exception $e) {
  if (isset($DB)) $DB->rollback();
  http_response_code(500);
  echo json_encode(['error' => $e->getMessage()]);
}
