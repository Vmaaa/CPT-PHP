<?php
$AVALIABLE_METHODS = ['GET'];
header('Content-Type: application/json');

require_once dirname(__DIR__, 4) . "/config/cors.php";
require_once dirname(__DIR__, 4) . "/utils/token/pre_validate.php";
require_once dirname(__DIR__, 4) . "/functions/serverSpecifics.php";

$SS = ServerSpecifics::getInstance();
$DB = $SS->fnt_getDBConnection();

try {
  $sql = "
    SELECT 
      fp.id_final_project,
      fp.title,
      fp.abstract,
      fp.status,
      s.name AS student_name,
      c.career,

      (SELECT file_url 
       FROM fp_change 
       WHERE id_final_project = fp.id_final_project 
       ORDER BY id_fp_change DESC LIMIT 1
      ) as file_url,

      (SELECT GROUP_CONCAT(fcr.id_professor SEPARATOR ',')
       FROM fp_change_review fcr
       JOIN fp_change fpc ON fcr.id_fp_change = fpc.id_fp_change
       WHERE fpc.id_final_project = fp.id_final_project
       AND fpc.id_fp_change = (
           SELECT id_fp_change 
           FROM fp_change 
           WHERE id_final_project = fp.id_final_project 
           ORDER BY created_at DESC LIMIT 1
       )
      ) as reviewers_ids

    FROM final_project fp
    JOIN fp_student fps ON fps.id_final_project = fp.id_final_project
    JOIN student s ON s.id_student = fps.id_student
    JOIN career c ON c.id_career = fp.id_career
    ORDER BY fp.id_final_project DESC
  ";

  $result = $DB->query($sql);
  $projects = [];

  while ($row = $result->fetch_assoc()) {
    $projects[] = $row;
  }

  echo json_encode([
    'data' => $projects,
    'count' => count($projects)
  ]);
} catch (Exception $e) {
  http_response_code(500);
  echo json_encode([
    'error' => 'Error al obtener proyectos: ' . $e->getMessage()
  ]);
}
