<?php
$AVALIABLE_METHODS = ['GET'];
header('Content-Type: application/json');

require_once dirname(__DIR__, 4) . "/config/cors.php";
require_once dirname(__DIR__, 4) . "/utils/token/pre_validate.php";
require_once dirname(__DIR__, 4) . "/functions/serverSpecifics.php";

$DB = ServerSpecifics::getInstance()->fnt_getDBConnection();

try {
  $stmt = $DB->prepare("SELECT id_professor FROM professor WHERE acco_id = ?");
  $stmt->bind_param("i", $AUTH['acco_id']);
  $stmt->execute();
  $prof = $stmt->get_result()->fetch_assoc();
  if (!$prof) throw new Exception("Perfil no encontrado");
  $idProfessor = $prof['id_professor'];

  $sql = "
        SELECT 
            fp.id_final_project,
            fp.title,
            fp.status,
            s.name AS student_name,
            c.career,
            fc.file_url,
            fc.stage
        FROM fp_advisor fa
        JOIN final_project fp ON fp.id_final_project = fa.id_final_project
        JOIN fp_student fps ON fps.id_final_project = fp.id_final_project
        JOIN student s ON s.id_student = fps.id_student
        JOIN career c ON c.id_career = fp.id_career
        
        /* JOIN para obtener el último PDF */
        JOIN fp_change fc ON fc.id_final_project = fp.id_final_project
        INNER JOIN (
            SELECT id_final_project, MAX(stage) as max_stage
            FROM fp_change
            GROUP BY id_final_project
        ) latest ON latest.id_final_project = fc.id_final_project 
                AND latest.max_stage = fc.stage
        
        WHERE fa.id_professor = ?
        ORDER BY fp.status ASC, s.name ASC
    ";

  $stmt = $DB->prepare($sql);
  $stmt->bind_param("i", $idProfessor);
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
