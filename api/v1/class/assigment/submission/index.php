<?php
$AVALIABLE_METHODS = ['GET', 'POST', 'PUT'];

header('Content-Type: application/json');

if (!in_array($_SERVER['REQUEST_METHOD'], $AVALIABLE_METHODS)) {
  http_response_code(405);
  echo json_encode(['error' => 'Método HTTP no soportado']);
  exit;
}

require_once __DIR__ . "/../../../../../config/cors.php";
require_once __DIR__ . "/../../../../../utils/token/pre_validate.php";
require_once __DIR__ . "/../../../../../utils/input/input_parser.php";

$UPLOAD_DIR = realpath($SS->fnt_getUploadDir());
if ($UPLOAD_DIR === false) {
  http_response_code(500);
  echo json_encode(['error' => 'Directorio base de uploads no encontrado']);
  exit;
}


if ($_SERVER['REQUEST_METHOD'] === 'GET') {
  $id_assignment = isset($_GET['id_assignment']) ? (int) $_GET['id_assignment'] : null;

  $required_fields = ['id_assignment'];
  $missing_fields = [];
  foreach ($required_fields as $field) {
    if (!isset($_GET[$field])) {
      $missing_fields[] = $field;
    }
  }
  if (!empty($missing_fields)) {
    http_response_code(400);
    echo json_encode(['error' => 'Faltan campos requeridos: ' . implode(', ', $missing_fields)]);
    exit;
  }

  $conds = [];
  $types = '';
  $params = [];


  $conds[] = "a.id_assigment = ?";
  $types .= 'i';
  $params[] = $id_assignment;


  if ($AUTH['acco_role'] === 'student') {
    $conds[] = "ac.acco_id = ?";
    $types .= 'i';
    $params[] = $AUTH['acco_id'];
  }

  $query = "SELECT asu.*, ac.acco_id FROM assigment_submission asu
      INNER JOIN account ac ON asu.acco_id = ac.acco_id
      INNER JOIN assigment a ON asu.id_assigment = a.id_assigment
      WHERE " . implode(' AND ', $conds);


  $stmt = mysqli_prepare($DB_T, $query);
  mysqli_stmt_bind_param($stmt, $types, ...$params);
  mysqli_stmt_execute($stmt);
  $result = mysqli_stmt_get_result($stmt);
  $data = [];

  while ($row = mysqli_fetch_assoc($result)) {
    if ($AUTH['acco_role'] === 'student') {
      $row['can_be_edited'] = ($row['acco_id'] === $AUTH['acco_id']);
    }
    //retrieve all the students related 

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $API_URL . "/student/?acco_id=" . $row['acco_id']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
      'Cookie: jwt=' . $_COOKIE['jwt']
    ]);
    $response = curl_exec($ch);
    $students = json_decode($response, true)['data'];
    $row['students'] = $students;
    $data[] = $row;
  }

  http_response_code(200);
  echo json_encode(['data' => $data]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if ($AUTH['acco_role'] !== 'student') {
    http_response_code(403);
    echo json_encode(['error' => 'Solo los estudiantes pueden crear entregas']);
    exit;
  }
  $file = isset($_FILES['file']) ? $_FILES['file'] : null;
  $id_assigment = isset($_POST['id_assigment']) ? (int) $_POST['id_assigment'] : null;
  $required_fields = ['file', 'id_assigment'];
  $missing_fields = [];
  foreach ($required_fields as $field) {
    if (!isset($_POST[$field]) && !isset($_FILES[$field])) {
      $missing_fields[] = $field;
    }
  }

  $query_assigment = "SELECT * FROM assigment WHERE id_assigment = ?";
  $stmt_assigment = mysqli_prepare($DB_T, $query_assigment);
  mysqli_stmt_bind_param($stmt_assigment, 'i', $id_assigment);
  mysqli_stmt_execute($stmt_assigment);
  $result_assigment = mysqli_stmt_get_result($stmt_assigment);
  if (mysqli_num_rows($result_assigment) === 0) {
    http_response_code(404);
    echo json_encode(['error' => 'La asignación no existe']);
    exit;
  }

  $assigment = mysqli_fetch_assoc($result_assigment);

  if (!empty($missing_fields)) {
    http_response_code(400);
    echo json_encode(['error' => 'Faltan campos requeridos: ' . implode(', ', $missing_fields)]);
    exit;
  }
  if ($file['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['error' => 'Error al subir el archivo']);
    exit;
  }
  if ($file['size'] > 5 * 1024 * 1024) {
    http_response_code(400);
    echo json_encode(['error' => 'Archivo demasiado grande']);
    exit;
  }
  if ($file['type'] !== 'application/pdf') {
    http_response_code(400);
    echo json_encode(['error' => 'Archivo debe ser un PDF']);
    exit;
  }
  $file_name = uniqid('assignment_submission_', true) . '.pdf';
  $file_path = $UPLOAD_DIR . '/class_' . $assigment['id_class'] . '/assignments/professor_' . $assigment['id_professor'] . '/submissions/' . $file_name;
  $dir = dirname($file_path);
  if (!is_dir($dir) && !mkdir(
    $dir,
    0755,
    true
  )) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al crear el directorio']);
    exit;
  }
  if (!move_uploaded_file($file['tmp_name'], $file_path)) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al guardar el archivo']);
    exit;
  }
  $file_url = $API_URL . '/uploads/assigments/submissions/?' . http_build_query([
    'file_name'    => $file_name,
    'id_class'     => $assigment['id_class'],
    'id_professor' => $assigment['id_professor']
  ]);

  $query = "INSERT INTO assigment_submission (id_assigment, acco_id, file_url) VALUES (?, ?, ?)";
  $stmt = mysqli_prepare($DB_T, $query);
  mysqli_stmt_bind_param($stmt, 'iis', $id_assigment, $AUTH['acco_id'], $file_url);
  try {
    mysqli_stmt_execute($stmt);
    http_response_code(201);
    echo json_encode(['message' => 'Entrega creada exitosamente']);
  } catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al crear la entrega']);
    exit;
  }
}

// professor can only update the grade and feedback, while students can only update the file, and this delete the grade
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
  $_PUT = fnt_parseInputMultiPart();
  $required_fields = ['id_assigment_submission'];
  $missing_fields = [];
  foreach ($required_fields as $field) {
    if (!isset($_PUT[$field])) {
      $missing_fields[] = $field;
    }
  }
  if (!empty($missing_fields)) {
    http_response_code(400);
    echo json_encode(['error' => 'Faltan campos requeridos: ' . implode(', ', $missing_fields)]);
    exit;
  }
  $id_assigment_submission = (int) $_PUT['id_assigment_submission'];
  $params = [];
  $types = '';
  $set_clauses = [];
  $now = date('Y-m-d H:i:s');


  /* Obtener asignación actual */
  $stmt = mysqli_prepare($DB_T, "SELECT a.*,asu.file_url as submission_file_url FROM assigment_submission as asu
    INNER JOIN assigment a ON asu.id_assigment = a.id_assigment
    WHERE asu.id_assigment_submission = ?");
  mysqli_stmt_bind_param($stmt, 'i', $id_assigment_submission);
  mysqli_stmt_execute($stmt);
  $result = mysqli_stmt_get_result($stmt);

  if (mysqli_num_rows($result) === 0) {
    http_response_code(404);
    echo json_encode(['error' => 'La asignación no existe']);
    exit;
  }

  $assigment = mysqli_fetch_assoc($result);

  if ($AUTH['acco_role'] !== 'student') {
    $required_fields = ['grade', 'feedback'];
    $missing_fields = [];
    foreach ($required_fields as $field) {
      if (!isset($_PUT[$field])) {
        $missing_fields[] = $field;
      }
    }

    if (!empty($missing_fields)) {
      http_response_code(400);
      echo json_encode(['error' => 'Faltan campos requeridos: ' . implode(', ', $missing_fields)]);
      exit;
    }

    $grade = isset($_PUT['grade']) ? (float) $_PUT['grade'] : null;
    $feedback = isset($_PUT['feedback']) ? $_PUT['feedback'] : null;
    if ($grade !== null) {
      if (!is_numeric($grade) || $grade < 0 || $grade > 10) {
        http_response_code(400);
        echo json_encode(['error' => 'La calificación debe ser un número entre 0 y 10']);
        exit;
      }
      $set_clauses[] = "grade = ?";
      $types .= 'd';
      $params[] = $grade;

      //add graded_at if grade is set
      $set_clauses[] = "graded_at = ?";
      $types .= 's';
      $params[] = $now;
    }
    if ($feedback !== null) {
      if (strlen($feedback) > 5000) {
        http_response_code(400);
        echo json_encode(['error' => 'La retroalimentación no puede exceder los 5000 caracteres']);
        exit;
      }
      $set_clauses[] = "feedback = ?";
      $types .= 's';
      $params[] = $feedback;
    }
  } else {
    //student logic
    $required_fields = ['file'];
    $missing_fields = [];
    foreach ($required_fields as $field) {
      if (!isset($_PUT[$field])) {
        $missing_fields[] = $field;
      }
    }
    if (!empty($missing_fields)) {
      http_response_code(400);
      echo json_encode(['error' => 'Faltan campos requeridos: ' . implode(', ', $missing_fields)]);
      exit;
    }
    $file = isset($_PUT['file']) ? $_PUT['file'] : null;
    if ($file !== null) {
      $tmpPath = tempnam(sys_get_temp_dir(), 'pdf_');
      file_put_contents($tmpPath, $file);

      if (!is_file($tmpPath) || filesize($tmpPath) === 0) {
        unlink($tmpPath);
        http_response_code(400);
        echo json_encode(['error' => 'Error al subir el archivo']);
        return;
      }

      if (filesize($tmpPath) > 5 * 1024 * 1024) {
        unlink($tmpPath);
        http_response_code(400);
        echo json_encode(['error' => 'Archivo demasiado grande']);
        return;
      }

      $file_name = uniqid('assignment_submission_', true) . '.pdf';

      $file_path =
        $UPLOAD_DIR
        . '/class_' . $assigment['id_class']
        . '/assignments/professor_' . $assigment['id_professor']
        . '/submissions'
        . '/' . $file_name;

      $dir = dirname($file_path);

      if (!is_dir($dir) && !mkdir($dir, 0755, true)) {
        unlink($tmpPath);
        http_response_code(500);
        echo json_encode(['error' => 'Error al crear el directorio']);
        return;
      }

      if (!rename($tmpPath, $file_path)) {
        unlink($tmpPath);
        http_response_code(500);
        echo json_encode(['error' => 'Error al guardar el archivo']);
        return;
      }

      $file_url = $API_URL . '/uploads/assigments/submissions/?' . http_build_query([
        'file_name'    => $file_name,
        'id_class'     => $assigment['id_class'],
        'id_professor' => $assigment['id_professor']
      ]);

      $set_clauses[] = "file_url = ?";
      $types .= 's';
      $params[] = $file_url;


      if ($assigment['file_url']) {
        $parsed = parse_url($assigment['submission_file_url']);
        parse_str($parsed['query'] ?? '', $q);
        $old_file_path =
          $UPLOAD_DIR
          . '/class_' . $q['id_class']
          . '/assignments/professor_' . $assigment['id_professor']
          . '/submissions'
          . '/' . ($q['file_name'] ?? '');
        if (is_file($old_file_path)) {
          unlink($old_file_path);
        }
      }

      $set_clauses[] = "grade = NULL";

      $set_clauses[] = "feedback = NULL";
    }
  }

  if (empty($set_clauses)) {
    http_response_code(200);
    echo json_encode(['changes' => []]);
    exit;
  }

  $query = "UPDATE assigment_submission SET " . implode(', ', $set_clauses) . " WHERE id_assigment_submission = ?";
  $types .= 'i';
  $params[] = $id_assigment_submission;
  $stmt = mysqli_prepare($DB_T, $query);
  mysqli_stmt_bind_param($stmt, $types, ...$params);
  try {
    mysqli_stmt_execute($stmt);
    http_response_code(200);
    echo json_encode(['changes' => $set_clauses, 'q' => $q]);
  } catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al actualizar la entrega: ' . $e->getMessage()]);
  }
}
