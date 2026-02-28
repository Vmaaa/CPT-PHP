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

try {
  $accountId = (int)$AUTH['acco_id'];

  // 1. Obtener los datos base del estudiante asociado a la cuenta
  $sqlStudentInfo = "
      SELECT id_career, first_half, year 
      FROM student 
      WHERE acco_id = ? 
      LIMIT 1
  ";
  $stmtStudent = $MAIN_DB->prepare($sqlStudentInfo);
  $stmtStudent->bind_param("i", $accountId);
  $stmtStudent->execute();
  $studentInfo = $stmtStudent->get_result()->fetch_assoc();

  if (!$studentInfo) {
    echo json_encode([
      'hasProject' => false,
      'isUploadStageActive' => false,
      'needsConfiguration' => true
    ]);
    exit;
  }

  // 2. Proyecto de la cuenta
  $sql = "
    SELECT 
      fp.id_final_project,
      p.title,
      p.abstract,
      p.status,
      p.id_career
    FROM fp_student fp
    JOIN final_project p ON p.id_final_project = fp.id_final_project
    WHERE fp.acco_id = ?
    LIMIT 1
  ";
  $stmt = $MAIN_DB->prepare($sql);
  $stmt->bind_param("i", $accountId);
  $stmt->execute();
  $project = $stmt->get_result()->fetch_assoc();

  // 3. Verificar primera subida (Si NO tiene proyecto)
  if (!$project) {
    $sqlCalendar = "
          SELECT start_date, end_date
          FROM calendar_events
          WHERE stage = 'upload_protocols'
            AND id_career = ?
            AND year = ?
            AND first_half = ?
            AND NOW() BETWEEN start_date AND end_date
          LIMIT 1
      ";
    $stmtCalendar = $MAIN_DB->prepare($sqlCalendar);
    $stmtCalendar->bind_param("iii", $studentInfo['id_career'], $studentInfo['year'], $studentInfo['first_half']);
    $stmtCalendar->execute();
    $calendarResult = $stmtCalendar->get_result()->fetch_assoc();

    echo json_encode([
      'hasProject' => false,
      'isUploadStageActive' => $calendarResult ? true : false,
      'calendarEvent' => $calendarResult ?: null
    ]);
    exit;
  }

  $idFinalProject = (int)$project['id_final_project'];

  // --- NUEVO: 3.5 Verificar segunda subida (Si SÍ tiene proyecto y fue RECHAZADO) ---
  $isSecondUploadStageActive = false;
  $secondCalendarEvent = null;

  if ($project['status'] === 'REJECTED') {
    $sqlSecondCalendar = "
          SELECT start_date, end_date
          FROM calendar_events
          WHERE stage = 'second_upload_protocols'
            AND id_career = ?
            AND year = ?
            AND first_half = ?
            AND NOW() BETWEEN start_date AND end_date
          LIMIT 1
      ";
    $stmtSecondCal = $MAIN_DB->prepare($sqlSecondCalendar);
    $stmtSecondCal->bind_param("iii", $studentInfo['id_career'], $studentInfo['year'], $studentInfo['first_half']);
    $stmtSecondCal->execute();
    $secondCalResult = $stmtSecondCal->get_result()->fetch_assoc();

    if ($secondCalResult) {
      $isSecondUploadStageActive = true;
      $secondCalendarEvent = $secondCalResult;
    }
  }

  // --- NUEVO: 3.6 Verificar subida de documento final (Si SÍ tiene proyecto y fue APROBADO) ---
  $isFinalUploadStageActive = false;
  $finalCalendarEvent = null;

  if ($project['status'] === 'APPROVED') {
    $sqlFinalCalendar = "
          SELECT start_date, end_date
          FROM calendar_events
          WHERE stage = 'upload_final_documents'
            AND id_career = ?
            AND year = ?
            AND first_half = ?
            AND NOW() BETWEEN start_date AND end_date
          LIMIT 1
      ";
    $stmtFinalCal = $MAIN_DB->prepare($sqlFinalCalendar);
    $stmtFinalCal->bind_param("iii", $studentInfo['id_career'], $studentInfo['year'], $studentInfo['first_half']);
    $stmtFinalCal->execute();
    $finalCalResult = $stmtFinalCal->get_result()->fetch_assoc();

    if ($finalCalResult) {
      $isFinalUploadStageActive = true;
      $finalCalendarEvent = $finalCalResult;
    }
  }


  // 4. Último cambio
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

  // 5. Revisiones
  $reviews = [];
  $completedReviews = 0;

  if ($change) {
    $sql = "
      SELECT 
        r.id_professor,
        pr.name AS professor_name,
        r.comment,
        r.reviewer_pdf_url,
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
    'completedReviews' => $completedReviews,
    'isSecondUploadStageActive' => $isSecondUploadStageActive,
    'secondCalendarEvent' => $secondCalendarEvent,
    'isFinalUploadStageActive' => $isFinalUploadStageActive,
    'finalCalendarEvent' => $finalCalendarEvent
  ]);
} catch (Exception $e) {
  http_response_code(500);
  echo json_encode([
    'error' => 'Internal server error',
    'detail' => $e->getMessage()
  ]);
}
