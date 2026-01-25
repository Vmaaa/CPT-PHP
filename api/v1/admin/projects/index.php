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
      c.career
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
    'error' => 'Error al obtener proyectos'
  ]);
}
