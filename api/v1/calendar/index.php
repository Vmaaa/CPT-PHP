<?php
$AVALIABLE_METHODS = ['GET', 'POST', 'PUT', 'DELETE'];

header('Content-Type: application/json');

if (!in_array($_SERVER['REQUEST_METHOD'], $AVALIABLE_METHODS)) {
  http_response_code(405);
  echo json_encode(['error' => 'Método HTTP no soportado']);
  exit;
}

require_once __DIR__ . "/../../../config/cors.php";
require_once __DIR__ . "/../../../utils/token/pre_validate.php";
require_once __DIR__ . "/../../../utils/input/input_parser.php";
require_once __DIR__ . "/../../../utils/general.php";

/*
'id_calendar_events', 'int(10) unsigned', 'NO', 'PRI', NULL, 'auto_increment'
'stage', 'enum(\'upload_protocols\',\'assign_reviewers\',\'judge_protocols\',\'second_upload_protocols\',\'second_assign_reviewers\',\'second_judge_protocols\',\'upload_final_documents\',\'select_documents\',\'documents_presentations\',\'grade_documents\',\'second_upload_final_documents\',\'second_select_documents\',\'second_documents_presentations\',\'second_grade_documents\')', 'NO', '', NULL, ''
'start_date', 'timestamp', 'NO', '', 'current_timestamp()', 'on update current_timestamp()'
'end_date', 'timestamp', 'NO', '', '0000-00-00 00:00:00', ''
'id_career', 'int(10) unsigned', 'NO', 'MUL', NULL, ''
'first_half', 'tinyint(4)', 'NO', '', NULL, ''
'year', 'int(11)', 'NO', '', NULL, ''
*/

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
  $stages = $SS->fnt_getStages();
  $id_career = $_GET['id_career'] ?? null;
  $stage = $_GET['stage'] ?? null;
  $first_half = $_GET['first_half'] ?? null;
  $year = $_GET['year'] ?? null;


  $parameters = [];
  $conditions = [];
  $types = '';

  if ($id_career) {
    $conditions[] = 'ce.id_career = ?';
    $parameters[] = $id_career;
    $types .= 'i';
  }

  if ($stage) {
    $conditions[] = 'ce.stage = ?';
    $parameters[] = $stage;
    $types .= 's';
  }

  if ($first_half) {
    $conditions[] = 'ce.first_half = ?';
    $parameters[] = $first_half;
    $types .= 'i';
  }

  if ($year) {
    $conditions[] = 'ce.year = ?';
    $parameters[] = $year;
    $types .= 'i';
  }

  $query = "SELECT ce.*, c.* FROM calendar_events ce INNER JOIN career c ON ce.id_career = c.id_career";

  if (!empty($conditions)) {
    $query .= " WHERE " . implode(' AND ', $conditions);
  }

  $events = [];
  $stmt = mysqli_prepare($DB_T, $query);
  if (!empty($parameters)) {
    mysqli_stmt_bind_param($stmt, $types, ...$parameters);
  }
  mysqli_stmt_execute($stmt);
  $result = mysqli_stmt_get_result($stmt);
  while ($row = mysqli_fetch_assoc($result)) {
    $row['humanized_stage'] = $stages[$row['stage']] ?? 'Desconocido';
    $row['active'] = false;
    if ($row['start_date'] <= date('Y-m-d') && $row['end_date'] >= date('Y-m-d')) {
      $row['active'] = true;
    }
    $events[] = $row;
  }
  echo json_encode(['data' => $events]);
  http_response_code(200);
  exit;
}

function isOverlappingStage($stage, $id_career, $first_half, $year, $exclude_id = null)
{
  global $DB_T;
  $query = "SELECT COUNT(*) as count FROM calendar_events WHERE stage = ? AND id_career = ? AND first_half = ? AND year = ?";
  $parameters = [$stage, $id_career, $first_half, $year];
  $types = 'siii';

  if ($exclude_id) {
    $query .= " AND id_calendar_events != ?";
    $parameters[] = $exclude_id;
    $types .= 'i';
  }

  $stmt = mysqli_prepare($DB_T, $query);
  mysqli_stmt_bind_param($stmt, $types, ...$parameters);
  mysqli_stmt_execute($stmt);
  mysqli_stmt_bind_result($stmt, $count);
  mysqli_stmt_fetch($stmt);
  return $count > 0;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $stage = $_POST['stage'] ?? null;
  $start_date = $_POST['start_date'] ?? null;
  $end_date = $_POST['end_date'] ?? null;
  $id_career = $_POST['id_career'] ?? null;
  $first_half = $_POST['first_half'] ?? null;
  $year = $_POST['year'] ?? null;

  $required_fields = ['stage', 'start_date', 'end_date', 'id_career', 'first_half', 'year'];
  $missing_fields = [];
  foreach ($required_fields as $field) {
    if (!isset($_POST[$field]) || empty($_POST[$field])) {
      $missing_fields[] = $field;
    }
  }
  if ($missing_fields) {
    http_response_code(400);
    echo json_encode(['error' => 'Faltan campos requeridos: ' . implode(', ', $missing_fields)]);
    exit;
  }

  if (isOverlappingStage($stage, $id_career, $first_half, $year)) {
    http_response_code(400);
    echo json_encode(['error' => 'Ya existe una etapa activa para esta carrera, semestre y año']);
    exit;
  }

  $query = "INSERT INTO calendar_events (stage, start_date, end_date, id_career, first_half, year) VALUES (?, ?, ?, ?, ?, ?)";
  $stmt = mysqli_prepare($DB_T, $query);
  mysqli_stmt_bind_param(
    $stmt,
    'sssiii',
    $stage,
    $start_date,
    $end_date,
    $id_career,
    $first_half,
    $year
  );
  try {
    mysqli_stmt_execute($stmt);
    $created_id = mysqli_stmt_insert_id($stmt);
    http_response_code(201);
    echo json_encode(['success' => 'Etapa creada exitosamente', 'id_calendar_events' => $created_id]);
  } catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al crear la etapa']);
  }
}
