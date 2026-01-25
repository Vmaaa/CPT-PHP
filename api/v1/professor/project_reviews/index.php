<?php
$AVALIABLE_METHODS = ['GET'];
header('Content-Type: application/json');

require_once dirname(__DIR__, 4) . "/config/cors.php";
require_once dirname(__DIR__, 4) . "/utils/token/pre_validate.php";
require_once dirname(__DIR__, 4) . "/functions/serverSpecifics.php";

$DB = ServerSpecifics::getInstance()->fnt_getDBConnection();

try {
  $projectId = (int)($_GET['id'] ?? 0);
  if (!$projectId) throw new Exception("ID faltante");

  $sql = "
        SELECT 
            p.name as reviewer_name,
            fcr.grade,
            fcr.comment,
            fcr.created_at
        FROM fp_change_review fcr
        JOIN professor p ON p.id_professor = fcr.id_professor
        JOIN fp_change fc ON fc.id_fp_change = fcr.id_fp_change
        
        /* Subquery para asegurar que es la última versión */
        INNER JOIN (
            SELECT id_final_project, MAX(stage) as max_stage
            FROM fp_change
            GROUP BY id_final_project
        ) latest ON latest.id_final_project = fc.id_final_project 
                AND latest.max_stage = fc.stage
        
        WHERE fc.id_final_project = ?
    ";

  $stmt = $DB->prepare($sql);
  $stmt->bind_param("i", $projectId);
  $stmt->execute();
  $res = $stmt->get_result();

  $data = [];
  while ($row = $res->fetch_assoc()) {
    $data[] = $row;
  }

  echo json_encode(['success' => true, 'data' => $data]);
} catch (Exception $e) {
  http_response_code(500);
  echo json_encode(['error' => $e->getMessage()]);
}
