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

  $query .= " ORDER BY ce.year DESC, ce.first_half DESC, ce.start_date DESC";

  $events = [];
  $stmt = mysqli_prepare($DB_T, $query);
  if (!empty($parameters)) {
    mysqli_stmt_bind_param($stmt, $types, ...$parameters);
  }
  mysqli_stmt_execute($stmt);
  $result = mysqli_stmt_get_result($stmt);
  $now = date('Y-m-d H:i:s');
  while ($row = mysqli_fetch_assoc($result)) {
    $row['humanized_stage'] = $stages[$row['stage']] ?? 'Desconocido';
    $row['active'] = false;
    if ($row['start_date'] <= $now && $row['end_date'] >= $now) {
      $row['active'] = true;
    }
    $row['now'] = $now;
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
    if ($_POST[$field] === null) {
      $missing_fields[] = $field;
    }
  }
  if ($missing_fields) {
    http_response_code(400);
    echo json_encode(['error' => 'Faltan campos requeridos: ' . implode(', ', $missing_fields), 'provided_fields' => $_POST]);
    exit;
  }

  if (!fnt_validateDateTime_v001($start_date, 'Y-m-d') || !fnt_validateDateTime_v001($end_date, 'Y-m-d')) {
    http_response_code(400);
    echo json_encode(['error' => 'Formato de fecha inválido. Se requiere YYYY-MM-DD HH:MM:SS']);
    exit;
  }

  if (!in_array($stage, $SS->fnt_getStagesKeys())) {
    http_response_code(400);
    echo json_encode(['error' => 'Etapa inválida: ' . $stage . '. Etapas válidas: ' . implode(', ', $SS->fnt_getStagesKeys())]);
    exit;
  }

  if ($year < 2000 || $year > 2100) {
    http_response_code(400);
    echo json_encode(['error' => 'Año inválido. Se requiere un año entre 2000 y 2100']);
    exit;
  }

  if ($first_half != 0 && $first_half != 1) {
    http_response_code(400);
    echo json_encode(['error' => 'Valor inválido para first_half. Se requiere 0 o 1']);
    exit;
  }

  if (strtotime($start_date) >= strtotime($end_date)) {
    http_response_code(400);
    echo json_encode(['error' => 'La fecha de inicio debe ser anterior a la fecha de fin']);
    exit;
  }

  if (isOverlappingStage($stage, $id_career, $first_half, $year)) {
    http_response_code(400);
    echo json_encode(['error' => 'Ya existe una etapa activa para esta carrera, semestre y año']);
    exit;
  }

  $start_date .= ' 00:00:00';
  $end_date .= ' 23:59:59';

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
    // foreign key for id_career, error code 
    if ($e->getCode() == 1452) {
      echo json_encode(['error' => 'La carrera especificada no existe']);
    } else {
      echo json_encode(['error' => 'Error al crear la etapa']);
    }
    exit;
  }
}

if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
  $PUT = fnt_parseInputMultiPart();
  $id_calendar_events = $PUT['id_calendar_events'] ?? null;
  $start_date = $PUT['start_date'] ?? null;
  $end_date = $PUT['end_date'] ?? null;
  $year = $PUT['year'] ?? null;
  $first_half = $PUT['first_half'] ?? null;

  if (!$id_calendar_events) {
    http_response_code(400);
    echo json_encode(['error' => 'Falta el campo id_calendar_events']);
    exit;
  }

  $existing_event_query = "SELECT * FROM calendar_events WHERE id_calendar_events = ?";
  $stmt = mysqli_prepare($DB_T, $existing_event_query);
  mysqli_stmt_bind_param($stmt, 'i', $id_calendar_events);
  mysqli_stmt_execute($stmt);
  $result = mysqli_stmt_get_result($stmt);
  if (mysqli_num_rows($result) === 0) {
    http_response_code(404);
    echo json_encode(['error' => 'No se encontró la etapa especificada']);
    exit;
  }
  $existing_event = mysqli_fetch_assoc($result);

  if (($start_date && !fnt_validateDateTime_v001($start_date, 'Y-m-d')) || ($end_date && !fnt_validateDateTime_v001($end_date, 'Y-m-d'))) {
    http_response_code(400);
    echo json_encode(['error' => 'Formato de fecha inválido. Se requiere YYYY-MM-DD HH:MM:SS']);
    exit;
  }

  $fields = [];
  $parameters = [];
  $types = '';

  if ($start_date) {
    $fields[] = 'start_date = ?';
    $parameters[] = $start_date . ' 00:00:00';
    $types .= 's';
  }
  if ($end_date) {
    $fields[] = 'end_date = ?';
    $parameters[] = $end_date . ' 23:59:59';
    $types .= 's';
  }
  if ($year) {
    $fields[] = 'year = ?';
    $parameters[] = $year;
    $types .= 'i';
  }
  if ($first_half !== null) {
    $fields[] = 'first_half = ?';
    $parameters[] = $first_half;
    $types .= 'i';
  }

  if (empty($fields)) {
    http_response_code(200);
    echo json_encode(['changes' => []]);
    exit;
  }

  $parameters[] = $id_calendar_events;
  $types .= 'i';

  if (isOverlappingStage($existing_event['stage'], $existing_event['id_career'], $first_half ?? $existing_event['first_half'], $year ?? $existing_event['year'], $id_calendar_events)) {
    http_response_code(400);
    echo json_encode(['error' => 'Ya existe una etapa activa para esta carrera, semestre y año']);
    exit;
  }

  $query = "UPDATE calendar_events SET " . implode(', ', $fields) . " WHERE id_calendar_events = ?";
  $stmt = mysqli_prepare($DB_T, $query);
  mysqli_stmt_bind_param($stmt, $types, ...$parameters);
  try {
    mysqli_stmt_execute($stmt);
    http_response_code(200);
    echo json_encode(['changes' => $fields]);
  } catch (Exception $e) {
    http_response_code(500);
    if ($e->getCode() == 1452) {
      echo json_encode(['error' => 'La carrera especificada no existe']);
    } else {
      echo json_encode(['error' => 'Error al actualizar la etapa']);
    }
    exit;
  }
}


if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
  $DELETE = fnt_parseInputMultiPart();
  $id_calendar_events = $DELETE['id_calendar_events'] ?? null;
  if (!$id_calendar_events) {
    http_response_code(400);
    echo json_encode(['error' => 'Falta el campo id_calendar_events']);
    exit;
  }

  $query = "DELETE FROM calendar_events WHERE id_calendar_events = ?";
  $stmt = mysqli_prepare($DB_T, $query);
  mysqli_stmt_bind_param($stmt, 'i', $id_calendar_events);
  try {
    mysqli_stmt_execute($stmt);
    if (mysqli_stmt_affected_rows($stmt) > 0) {
      http_response_code(200);
      echo json_encode(['success' => 'Etapa eliminada exitosamente']);
    } else {
      http_response_code(404);
      echo json_encode(['error' => 'No se encontró la etapa especificada']);
    }
  } catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al eliminar la etapa']);
  }
}
