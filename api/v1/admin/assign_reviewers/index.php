<?php
$AVALIABLE_METHODS = ['POST'];

header('Content-Type: application/json');

if (!in_array($_SERVER['REQUEST_METHOD'], $AVALIABLE_METHODS)) {
  http_response_code(405);
  echo json_encode(['error' => 'Método HTTP no soportado']);
  exit;
}

require_once dirname(__DIR__, 4) . "/config/cors.php";
require_once dirname(__DIR__, 4) . "/utils/token/pre_validate.php";
require_once dirname(__DIR__, 4) . "/functions/serverSpecifics.php";

if (!isset($AUTH) || $AUTH['acco_role'] !== 'admin') {
  http_response_code(403);
  echo json_encode(['error' => 'Acceso denegado']);
  exit;
}

$DB = ServerSpecifics::getInstance()->fnt_getDBConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  try {
    $DB->begin_transaction();

    $required = ['id_final_project', 'reviewer1', 'reviewer2', 'reviewer3'];
    $missing = [];

    foreach ($required as $r) {
      if (!isset($_POST[$r])) {
        $missing[] = $r;
      }
    }

    if ($missing) {
      throw new Exception('Faltan parámetros: ' . implode(', ', $missing));
    }

    $projectId = (int)$_POST['id_final_project'];
    $reviewers = [
      (int)$_POST['reviewer1'],
      (int)$_POST['reviewer2'],
      (int)$_POST['reviewer3']
    ];

    if (count(array_unique($reviewers)) !== 3) {
      throw new Exception("Revisores duplicados");
    }

    // Obtener el cambio más reciente
    $stmt = $DB->prepare(
      "SELECT id_fp_change, file_url FROM fp_change 
             WHERE id_final_project=? 
             ORDER BY created_at DESC LIMIT 1"
    );
    $stmt->bind_param("i", $projectId);
    $stmt->execute();
    $change = $stmt->get_result()->fetch_assoc();

    if (!$change) throw new Exception("No hay envío registrado para este proyecto");

    $idFpChange = $change['id_fp_change'];
    $fileUrl = $change['file_url'];

    // 1. LIMPIAR anteriores para evitar duplicados en la BD
    $stmtDelete = $DB->prepare("DELETE FROM fp_change_review WHERE id_fp_change = ?");
    $stmtDelete->bind_param("i", $idFpChange);
    $stmtDelete->execute();

    // 2. INSERTAR los nuevos revisores
    $stmtInsert = $DB->prepare(
      "INSERT INTO fp_change_review (id_fp_change, id_professor, file_url) 
             VALUES (?,?,?)"
    );

    foreach ($reviewers as $r) {
      $stmtInsert->bind_param("iis", $idFpChange, $r, $fileUrl);
      $stmtInsert->execute();
    }

    // Actualizar estado del proyecto
    $DB->query("UPDATE final_project SET status='UNDER_REVIEW' WHERE id_final_project=$projectId");

    $DB->commit();

    http_response_code(200);
    echo json_encode(['success' => true, 'message' => 'Revisores asignados correctamente']);
  } catch (Exception $e) {
    $DB->rollback();
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
  }
}
