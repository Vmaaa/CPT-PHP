<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$AVALIABLE_METHODS = ['GET'];

header('Content-Type: application/json');

if (!in_array($_SERVER['REQUEST_METHOD'], $AVALIABLE_METHODS)) {
  http_response_code(405);
  echo json_encode(['error' => 'Method not allowed']);
  exit;
}
require_once dirname(__DIR__, 4) . "/config/cors.php";
require_once dirname(__DIR__, 4) . "/utils/token/pre_validate.php";
require_once dirname(__DIR__, 4) . "/utils/input/input_parser.php";
require_once dirname(__DIR__, 4) . "/utils/output/parse_custom_request.php";
require_once dirname(__DIR__, 4) . "/functions/serverSpecifics.php";

$SS = ServerSpecifics::getInstance();
$MAIN_DB = $SS->fnt_getDBConnection();

/*
 * predefined:
 * $MAIN_DB
 * $SS
 * $AUTH
 */

if (!$AUTH) {
  http_response_code(401);
  echo json_encode(['error' => 'Unauthorized']);
  exit;
}

if ($AUTH['acco_role'] !== 'student') {
  http_response_code(403);
  echo json_encode(['error' => 'Forbidden']);
  exit;
}

try {

  // 1. Obtener id_student
  $sql = "
    SELECT id_student
    FROM student
    WHERE acco_id = ?
    LIMIT 1
  ";
  $stmt = $MAIN_DB->prepare($sql);
  $stmt->bind_param("i", $AUTH['acco_id']);
  $stmt->execute();
  $res = $stmt->get_result();
  $student = $res->fetch_assoc();

  if (!$student) {
    echo json_encode([
      'hasProject' => false
    ]);
    exit;
  }

  $idStudent = (int)$student['id_student'];

  // 2. Proyecto del alumno
  $sql = "
    SELECT 
      fp.id_final_project,
      p.title,
      p.abstract,
      p.status,
      p.id_career
    FROM fp_student fp
    JOIN final_project p ON p.id_final_project = fp.id_final_project
    WHERE fp.id_student = ?
    LIMIT 1
  ";
  $stmt = $MAIN_DB->prepare($sql);
  $stmt->bind_param("i", $idStudent);
  $stmt->execute();
  $project = $stmt->get_result()->fetch_assoc();

  if (!$project) {
    echo json_encode([
      'hasProject' => false
    ]);
    exit;
  }

  $idFinalProject = (int)$project['id_final_project'];

  // 3. Último cambio
  $sql = "
    SELECT id_fp_change, stage, file_url, created_at
    FROM fp_change
    WHERE id_final_project = ?
    ORDER BY created_at DESC
    LIMIT 1
  ";
  $stmt = $MAIN_DB->prepare($sql);
  $stmt->bind_param("i", $idFinalProject);
  $stmt->execute();
  $change = $stmt->get_result()->fetch_assoc();

  // 4. Revisiones
  $reviews = [];
  $completedReviews = 0;

  if ($change) {
    $sql = "
      SELECT 
        r.id_professor,
        pr.name AS professor_name,
        r.comment,
        r.file_url,
        r.grade,
        r.created_at
      FROM fp_change_review r
      JOIN professor pr ON pr.id_professor = r.id_professor
      WHERE r.id_fp_change = ?
    ";
    $stmt = $MAIN_DB->prepare($sql);
    $stmt->bind_param("i", $change['id_fp_change']);
    $stmt->execute();
    $res = $stmt->get_result();

    while ($row = $res->fetch_assoc()) {
      if ($row['grade'] !== null) {
        $completedReviews++;
      }
      $reviews[] = $row;
    }
  }

  echo json_encode([
    'hasProject' => true,
    'project' => $project,
    'change' => $change,
    'reviews' => $reviews,
    'completedReviews' => $completedReviews
  ]);
} catch (Exception $e) {
  http_response_code(500);
  echo json_encode([
    'error' => 'Internal server error',
    'detail' => $e->getMessage()
  ]);
}
