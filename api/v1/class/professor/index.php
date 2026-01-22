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

if ($AUTH['acco_role'] !== 'admin') {
  http_response_code(403);
  echo json_encode(['error' => 'Solo admin puede modificar asignaciones']);
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {

  if (!isset($_GET['id_class'])) {
    http_response_code(400);
    echo json_encode(['error' => 'id_class es requerido']);
    exit;
  }

  $query = "
        SELECT 
            cp.id_class_professor,
            p.*
        FROM class_professor cp
        INNER JOIN professor p ON p.id_professor = cp.id_professor
        WHERE cp.id_class = ?
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
  if (!isset($_POST['id_class'])) {
    http_response_code(400);
    echo json_encode(['error' => 'id_class es requerido']);
    exit;
  }
  $id_class = (int) $_POST['id_class'];
  $id_professors = $_POST['id_professor'];
  $query = "INSERT INTO class_professor (id_class, id_professor) VALUES (?, ?)";
  mysqli_begin_transaction($DB_T);
  $inserted_ids = [];
  try {
    $stmt = mysqli_prepare($DB_T, $query);
    foreach ($id_professors as $id_professor) {
      mysqli_stmt_bind_param($stmt, 'ii', $id_class, $id_professor);
      mysqli_stmt_execute($stmt);
      $inserted_ids[] = mysqli_insert_id($DB_T);
    }
    mysqli_stmt_close($stmt);
    http_response_code(201);
  } catch (Exception $e) {
    mysqli_rollback($DB_T);
    http_response_code(500);
    echo json_encode(['error' => 'Error al asignar profesores a la clase']);
    exit;
  }
  mysqli_commit($DB_T);
  echo json_encode(['message' => 'Profesores asignados a la clase exitosamente', 'inserted_ids' => $inserted_ids]);
}

if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
  $_PUT = fnt_parseInputMultiPart();
  if (!isset($_PUT['id_class'])) {
    http_response_code(400);
    echo json_encode(['error' => 'id_class es requerido']);
    exit;
  }
  $id_class = (int) $_PUT['id_class'];
  $id_professors = $_PUT['id_professor'];
  //delete existing assignments and add new ones
  mysqli_begin_transaction($DB_T);
  try {
    $delete_query = "DELETE FROM class_professor WHERE id_class = ?";
    $delete_stmt = mysqli_prepare($DB_T, $delete_query);
    mysqli_stmt_bind_param($delete_stmt, 'i', $id_class);
    mysqli_stmt_execute($delete_stmt);
    mysqli_stmt_close($delete_stmt);
    $insert_query = "INSERT INTO class_professor (id_class, id_professor) VALUES (?, ?)";
    $insert_stmt = mysqli_prepare($DB_T, $insert_query);
    foreach ($id_professors as $id_professor) {
      mysqli_stmt_bind_param($insert_stmt, 'ii', $id_class, $id_professor);
      mysqli_stmt_execute($insert_stmt);
    }
    mysqli_stmt_close($insert_stmt);
  } catch (Exception $e) {
    mysqli_rollback($DB_T);
    http_response_code(500);
    echo json_encode(['error' => 'Error al actualizar asignaciones de profesores']);
    exit;
  }
  mysqli_commit($DB_T);
  http_response_code(200);
  echo json_encode(['message' => 'Asignaciones de profesores actualizadas exitosamente']);
}
