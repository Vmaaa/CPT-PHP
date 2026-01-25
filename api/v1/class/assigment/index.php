<?php
$AVALIABLE_METHODS = ['GET', 'POST', 'PUT'];

header('Content-Type: application/json');

if (!in_array($_SERVER['REQUEST_METHOD'], $AVALIABLE_METHODS)) {
  http_response_code(405);
  echo json_encode(['error' => 'Método HTTP no soportado']);
  exit;
}

require_once __DIR__ . "/../../../../config/cors.php";
require_once __DIR__ . "/../../../../utils/token/pre_validate.php";
require_once __DIR__ . "/../../../../utils/input/input_parser.php";

$UPLOAD_DIR = realpath(__DIR__ . '/../../../../uploads/assigments');
if ($UPLOAD_DIR === false) {
  http_response_code(500);
  echo json_encode(['error' => 'Directorio base de uploads no encontrado']);
  exit;
}

if (!is_dir($UPLOAD_DIR)) {
  mkdir($UPLOAD_DIR, 0755, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
  $id_class  = isset($_GET['id_class']) ? (int) $_GET['id_class'] : null;

  if ($id_class === null) {
    http_response_code(400);
    echo json_encode(['error' => 'id_class es requerido']);
    exit;
  }

  $query = "
        SELECT 
            a.*
        FROM assigment a
        WHERE a.id_class = ?
        ORDER BY created_at DESC
    ";

  $stmt = mysqli_prepare($DB_T, $query);
  mysqli_stmt_bind_param($stmt, 'i', $_GET['id_class']);
  mysqli_stmt_execute($stmt);

  $result = mysqli_stmt_get_result($stmt);
  $data = [];

  while ($row = mysqli_fetch_assoc($result)) {
    $data[] = $row;
  }

  http_response_code(200);
  echo json_encode(['data' => $data]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if ($AUTH['acco_role'] !== 'professor' && $AUTH['acco_role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'No tienes permiso para crear una asignación']);
    exit;
  }

  $title = $_POST['title'] ?? null;
  $description = $_POST['description'] ?? null;
  $due_date = $_POST['due_date'] ?? null;
  $id_class = $_POST['id_class'] ?? null;
  $id_professor = $AUTH['id_professor'] ?? null;
  $file = $_FILES['file'] ?? null;
  $required_params = ['title', 'due_date', 'id_class'];
  $missing_params = [];
  foreach ($required_params as $param) {
    if (!isset($_POST[$param]) || empty($_POST[$param])) {
      $missing_params[] = $param;
    }
  }
  if (count($missing_params) > 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Parámetros faltantes: ' . implode(', ', $missing_params)]);
    exit;
  }

  if ($id_professor === null) {
    http_response_code(400);
    echo json_encode(['error' => 'Ocurrior un error al identificar al profesor']);
    exit;
  }
  //valid params
  if (!fnt_validateString_v001($title, 1, 240)) {
    http_response_code(400);
    echo json_encode(['error' => 'Título inválido: debe tener entre 1 y 240 caracteres']);
    exit;
  }
  if (!fnt_validateDateTime_v001($due_date)) {
    http_response_code(400);
    echo json_encode(['error' => 'Fecha de entrega inválida: debe tener el formato YYYY-MM-DD HH:MM:SS']);
    exit;
  }
  if ($file !== null) {
    //error retrieving file
    if ($file && $file['error'] !== UPLOAD_ERR_OK) {
      http_response_code(400);
      echo json_encode(['error' => 'Error al subir el archivo']);
      exit;
    }
    $allowed_types = ['application/pdf'];
    if (!in_array($file['type'], $allowed_types)) {
      http_response_code(400);
      echo json_encode(['error' => 'Tipo de archivo no permitido: solo se permiten archivos PDF']);
      exit;
    }
    if ($file['size'] > 5 * 1024 * 1024) {
      http_response_code(400);
      echo json_encode(['error' => 'El archivo es demasiado grande: el tamaño máximo es de 5MB']);
      exit;
    }
    //file path /uploads/assigments/{class_id}/{professor_id}/file_name
    $file_name = uniqid('assignment_', true) . '.pdf';
    $file_path = $UPLOAD_DIR . '/class_' . $id_class . '/professor_' . $id_professor . '/' . $file_name;
    $file_dir = dirname($file_path);
    if (!is_dir($file_dir)) {
      $created = mkdir($file_dir, 0755, true);
    }
    if (!move_uploaded_file($file['tmp_name'], $file_path)) {
      http_response_code(500);
      echo json_encode(['error' => 'Error al guardar el archivo' . $file_path, 'error_info' => error_get_last()]);
      exit;
    }

    $query_params = [
      'file_name' => $file_name,
      'id_class' => $id_class,
      'id_professor' => $id_professor,
    ];

    $file_url = $API_URL . '/uploads/assigments/?' . http_build_query($query_params);
  } else {
    $file_url = null;
  }
  $query = "
        INSERT INTO assigment (title, description, due_date, file_url, id_class, id_professor)
        VALUES (?, ?, ?, ?, ?, ?)";
  $stmt = mysqli_prepare($DB_T, $query);
  mysqli_stmt_bind_param($stmt, 'ssssii', $title, $description, $due_date, $file_url, $id_class, $id_professor);
  try {
    mysqli_stmt_execute($stmt);
    http_response_code(201);
    echo json_encode(['message' => 'Asignación creada exitosamente']);
  } catch (Exception $e) {
    http_response_code(500);
    // foreign key violation
    if (mysqli_errno($DB_T) === 1452) {
      if (strpos(mysqli_error($DB_T), 'id_class')) {
        echo json_encode(['error' => 'La clase especificada no existe']);
        exit;
      }
      if (strpos(mysqli_error($DB_T), 'id_professor')) {
        echo json_encode(['error' => 'El profesor especificado no existe']);
        exit;
      }
    }
    echo json_encode(['error' => 'Error al crear la asignación']);
  }
}
