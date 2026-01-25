<?php
$AVALIABLE_METHODS = ['GET'];
header('Content-Type: application/json');

require_once dirname(__DIR__, 4) . "/config/cors.php";
require_once dirname(__DIR__, 4) . "/utils/token/pre_validate.php";
require_once dirname(__DIR__, 4) . "/functions/serverSpecifics.php";

$DB = ServerSpecifics::getInstance()->fnt_getDBConnection();

try {
  $sql = "
    SELECT
      fp.id_final_project,
      fp.title,
      fp.abstract,
      fp.status,
      c.career AS career,
      s.name AS student_name,
      fcr.id_fp_change_review,
      fc.file_url,        
      fcr.grade,
      fcr.comment,
      fcr.reviewer_pdf_url,
      fc.stage           
    FROM fp_change_review fcr
    JOIN professor p ON p.id_professor = fcr.id_professor
    JOIN fp_change fc ON fc.id_fp_change = fcr.id_fp_change
    JOIN final_project fp ON fp.id_final_project = fc.id_final_project
    
    INNER JOIN (
        SELECT id_final_project, MAX(stage) as max_stage
        FROM fp_change
        GROUP BY id_final_project
    ) latest ON latest.id_final_project = fc.id_final_project 
            AND latest.max_stage = fc.stage

    JOIN fp_student fs ON fs.id_final_project = fp.id_final_project
    JOIN student s ON s.id_student = fs.id_student
    JOIN career c ON c.id_career = fp.id_career
    
    WHERE p.acco_id = ?
    ORDER BY fc.created_at DESC
  ";

  $stmt = $DB->prepare($sql);
  $stmt->bind_param("i", $AUTH['acco_id']);
  $stmt->execute();

  $res = $stmt->get_result();
  $data = [];

  while ($row = $res->fetch_assoc()) {
    $data[] = $row;
  }

  echo json_encode([
    'success' => true,
    'data' => $data
  ]);
} catch (Exception $e) {
  http_response_code(500);
  echo json_encode([
    'error' => $e->getMessage()
  ]);
}
