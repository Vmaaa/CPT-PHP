<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$AVALIABLE_METHODS = ['GET', 'PUT'];

header('Content-Type: application/json');

if (!in_array($_SERVER['REQUEST_METHOD'], $AVALIABLE_METHODS)) {
  http_response_code(405);
  echo json_encode(['error' => 'Método HTTP no soportado']);
  exit;
}

require_once __DIR__ . "/../../../../config/cors.php";
require_once __DIR__ . "/../../../../utils/token/pre_validate.php";
require_once __DIR__ . "/../../../../utils/input/input_parser.php";

if ($_SERVER['REQUEST_METHOD'] === 'GET') {

  if (!isset($_GET['id_class'])) {
    http_response_code(400);
    echo json_encode(['error' => 'id_class es requerido']);
    exit;
  }

  $query = "
        SELECT 
            s.*
        FROM student s
        WHERE s.id_class = ?
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
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
  $_PUT = fnt_parseInputMultiPart();
  $id_class = isset($_PUT['id_class']) ? intval($_PUT['id_class']) : null;
  $students_ids = isset($_PUT['students_ids']) ? $_PUT['students_ids'] : null;

  $required_params = ['id_class', 'students_ids'];
  $missing_params = [];
  foreach ($required_params as $param) {
    if (is_null($$param)) {
      $missing_params[] = $param;
    }
  }
  if (count($missing_params) > 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Parámetros faltantes: ' . implode(', ', $missing_params)]);
    exit;
  }
  //validate that students_ids is an array
  if (!is_array($students_ids)) {
    http_response_code(400);
    echo json_encode(['error' => 'students_ids debe ser un arreglo']);
    exit;
  }
  //retrieve current students in class
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $API_URL . "/class/student/?id_class=" . $id_class);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Cookie: jwt=' . $_COOKIE['jwt']
  ]);

  $response = curl_exec($ch);
  $current_students = json_decode($response, true)['data'];
  $current_student_ids = array_map(function ($student) {
    return $student['id_student'];
  }, $current_students);
  //determine which students to add
  $students_to_add = array_diff($students_ids, $current_student_ids);
  $students_to_remove = array_diff($current_student_ids, $students_ids);
  //add new students
  mysqli_begin_transaction($DB_T);
  try {
    //update students
    $update_query = "
            UPDATE student
            SET id_class = ?
            WHERE id_student = ?
        ";
    $update_stmt = mysqli_prepare($DB_T, $update_query);
    foreach ($students_to_add as $student_id) {
      mysqli_stmt_bind_param($update_stmt, 'ii', $id_class, $student_id);
      mysqli_stmt_execute($update_stmt);
    }
    mysqli_stmt_close($update_stmt);
    //remove students
    //set id_class to NULL
    //update students
    $remove_query = "
            UPDATE student
            SET id_class = NULL
            WHERE id_student = ?
        ";
    $remove_stmt = mysqli_prepare($DB_T, $remove_query);
    foreach ($students_to_remove as $student_id) {
      mysqli_stmt_bind_param($remove_stmt, 'i', $student_id);
      mysqli_stmt_execute($remove_stmt);
    }
    mysqli_stmt_close($remove_stmt);
    mysqli_commit($DB_T);
    http_response_code(200);
    echo json_encode(['message' => 'Estudiantes actualizados correctamente']);
  } catch (Exception $e) {
    mysqli_rollBack($DB_T);
    http_response_code(500);
    echo json_encode(['error' => 'Error al actualizar los estudiantes']);
  }
}
