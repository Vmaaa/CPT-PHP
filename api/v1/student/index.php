<?php
// each account can have multiple students associated, but each student can only have one account

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

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
  $without_class = isset($_GET['without_class']) ? (int) $_GET['without_class'] : 0;
  $id_career = isset($_GET['id_career']) ? (int) $_GET['id_career'] : null;
  $id_class = isset($_GET['id_class']) ? (int) $_GET['id_class'] : null;
  $acco_id = isset($_GET['acco_id']) ? (int) $_GET['acco_id'] : null;

  $conds = [];
  $params = [];
  $types = '';


  $query = "SELECT s.*, c.name as class_name, ca.career FROM student s LEFT JOIN class c ON s.id_class = c.id_class LEFT JOIN career ca ON s.id_career = ca.id_career";

  if ($without_class === 1) {
    $conds[] = "s.id_class IS NULL";
  }

  if ($id_career !== null) {
    $conds[] = "s.id_career = ?";
    $params[] = $id_career;
    $types .= 'i';
  }

  if ($id_class !== null) {
    $conds[] = "s.id_class = ?";
    $params[] = $id_class;
    $types .= 'i';
  }

  if ($acco_id !== null) {
    $conds[] = "s.acco_id = ?";
    $params[] = $acco_id;
    $types .= 'i';
  }

  if ($conds) {
    $query .= " WHERE " . implode(" AND ", $conds);
  }

  $stmt = mysqli_prepare($DB_T, $query);
  if ($params) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
  }
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
  // this endpoint will always be consumed after creating the account, so we can assume that if the acco role is student, the account already has at least one student associated
  $name = isset($_POST['name']) ? trim($_POST['name']) : null;
  $curp = isset($_POST['curp']) ? trim($_POST['curp']) : null;
  $acco_id = isset($_POST['acco_id']) ? (int) $_POST['acco_id'] : null;
  $school_id_number = isset($_POST['school_id_number']) ? trim($_POST['school_id_number']) : null;

  $requiredParams = ['name', 'curp', 'acco_id', 'school_id_number'];
  $missingParams = [];
  for ($i = 0; $i < count($requiredParams); $i++) {
    if (!isset($_POST[$requiredParams[$i]]) || trim($_POST[$requiredParams[$i]]) === '') {
      $missingParams[] = $requiredParams[$i];
    }
  }
  if ($missingParams) {
    http_response_code(400);
    echo json_encode(['error' => 'Parámetros incompletos, se requiere ' . implode(', ', $missingParams)]);
    exit;
  }


  if (!fnt_validateString_v001($name, 2, 300)) {
    http_response_code(400);
    echo json_encode(['error' => "El 'name' debe tener entre 2 y 300 caracteres"]);
    exit;
  }

  if (!fnt_validateCURP($curp)) {
    http_response_code(400);
    echo json_encode(['error' => "El 'curp' proporcionado no es válido"]);
    exit;
  }


  if (!fnt_validateSchoolIDNumber_v001($school_id_number)) {
    http_response_code(400);
    echo json_encode(['error' => "El 'school_id_number' proporcionado no es válido"]);
    exit;
  }

  //retrieve the corresponding account to check if it exists and has the correct role
  $qry_check_account = "SELECT acco_role FROM account WHERE acco_id = ? LIMIT 1";
  $stmt_check_account = mysqli_prepare($DB_T, $qry_check_account);
  mysqli_stmt_bind_param($stmt_check_account, 'i', $acco_id);
  mysqli_stmt_execute($stmt_check_account);
  $res_check_account = mysqli_stmt_get_result($stmt_check_account);
  if (!$row_check_account = mysqli_fetch_assoc($res_check_account)) {
    mysqli_stmt_close($stmt_check_account);
    http_response_code(400);
    echo json_encode(['error' => 'El acco_id proporcionado no corresponde a ninguna cuenta']);
    exit;
  }
  $acco_role = $row_check_account['acco_role'];
  mysqli_stmt_close($stmt_check_account);
  if ($acco_role !== 'student') {
    http_response_code(400);
    echo json_encode(['error' => 'El acco_id proporcionado no corresponde a una cuenta de estudiante']);
    exit;
  }
  //retrieve id_career from the existing students associated with the account
  $qry_get_career = "SELECT id_career FROM student WHERE acco_id = ? LIMIT 1";
  $stmt_get_career = mysqli_prepare($DB_T, $qry_get_career);
  mysqli_stmt_bind_param($stmt_get_career, 'i', $acco_id);
  mysqli_stmt_execute($stmt_get_career);
  $res_get_career = mysqli_stmt_get_result($stmt_get_career);
  $row_get_career = mysqli_fetch_assoc($res_get_career);
  $id_career = $row_get_career['id_career'];
  mysqli_stmt_close($stmt_get_career);
  //retrieve id_class from the existing students associated with the account (if any)
  $qry_get_class = "SELECT id_class FROM student WHERE acco_id = ? LIMIT 1";
  $stmt_get_class = mysqli_prepare($DB_T, $qry_get_class);
  mysqli_stmt_bind_param($stmt_get_class, 'i', $acco_id);
  mysqli_stmt_execute($stmt_get_class);
  $res_get_class = mysqli_stmt_get_result($stmt_get_class);
  $id_class = null;
  if ($row_get_class = mysqli_fetch_assoc($res_get_class)) {
    $id_class = $row_get_class['id_class'];
  }
  mysqli_stmt_close($stmt_get_class);

  //insert the new student
  $qry_insert_student = "INSERT INTO student (acco_id, name, curp, school_id_number, id_career, id_class) VALUES (?, ?, ?, ?, ?, ?)";
  $stmt_insert_student = mysqli_prepare($DB_T, $qry_insert_student);
  mysqli_stmt_bind_param($stmt_insert_student, 'isssii', $acco_id, $name, $curp, $school_id_number, $id_career, $id_class);
  try {
    mysqli_stmt_execute($stmt_insert_student);
  } catch (Exception $e) {
    mysqli_stmt_close($stmt_insert_student);
    http_response_code(500);
    // foreign key for id_career or id_class, error code 1452
    if ($e->getCode() === 1452) {
      echo json_encode(['error' => 'El id_career o id_class proporcionado no corresponde a un registro existente']);
    }
    // unique error for school_id_number or curp
    if ($e->getCode() === 1062) {
      echo json_encode(['error' => 'El school_id_number o curp proporcionado ya está registrado para otro estudiante']);
    } else {
      echo json_encode(['error' => 'Error al registrar el estudiante']);
    }
    exit;
  }
  mysqli_stmt_close($stmt_insert_student);
  http_response_code(201);
  echo json_encode(['message' => 'Estudiante registrado exitosamente', 'id_student' => (int) mysqli_insert_id($DB_T)]);
}

if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
  //editable fields: name, curp, school_id_number
  $_PUT = fnt_parseInputMultiPart();
  $id_student = isset($_PUT['id_student']) ? (int) $_PUT['id_student'] : null;
  $name = isset($_PUT['name']) ? trim($_PUT['name']) : null;
  $curp = isset($_PUT['curp']) ? trim($_PUT['curp']) : null;
  $school_id_number = isset($_PUT['school_id_number']) ? trim($_PUT['school_id_number']) : null;
  if ($id_student === null) {
    http_response_code(400);
    echo json_encode(['error' => 'El id_student es requerido']);
    exit;
  }

  $params = [];
  $setters = [];
  $types = '';

  if ($name !== null) {
    if (!fnt_validateString_v001($name, 2, 300)) {
      http_response_code(400);
      echo json_encode(['error' => "El 'name' debe tener entre 2 y 300 caracteres"]);
      exit;
    }
    $setters[] = "name = ?";
    $params[] = $name;
    $types .= 's';
  }
  if ($curp !== null) {
    if (!fnt_validateCURP($curp)) {
      http_response_code(400);
      echo json_encode(['error' => "El 'curp' proporcionado no es válido"]);
      exit;
    }
    $setters[] = "curp = ?";
    $params[] = $curp;
    $types .= 's';
  }
  if ($school_id_number !== null) {
    if (!fnt_validateSchoolIDNumber_v001($school_id_number)) {
      http_response_code(400);
      echo json_encode(['error' => "El 'school_id_number' proporcionado no es válido"]);
      exit;
    }
    $setters[] = "school_id_number = ?";
    $params[] = $school_id_number;
    $types .= 's';
  }

  $params[] = $id_student;
  $types .= 'i';

  if (!$setters) {
    http_response_code(200);
    echo json_encode(['changes' => []]);
    exit;
  }

  $qry_update_student = "UPDATE student SET " . implode(", ", $setters) . " WHERE id_student = ?";
  $stmt_update_student = mysqli_prepare($DB_T, $qry_update_student);
  mysqli_stmt_bind_param($stmt_update_student, $types, ...$params);
  try {
    mysqli_stmt_execute($stmt_update_student);
  } catch (Exception $e) {
    mysqli_stmt_close($stmt_update_student);
    http_response_code(500);
    // unique error for school_id_number or curp
    if ($e->getCode() === 1062) {
      echo json_encode(['error' => 'El school_id_number o curp proporcionado ya está registrado para otro estudiante']);
    } else {
      echo json_encode(['error' => 'Error al actualizar el estudiante']);
    }
    exit;
  }
  mysqli_stmt_close($stmt_update_student);
  http_response_code(200);
  echo json_encode(['changes' => $setters]);
}

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
  $_DELETE = fnt_parseInputMultiPart();
  $id_student = isset($_DELETE['id_student']) ? (int) $_DELETE['id_student'] : null;
  if ($id_student === null) {
    http_response_code(400);
    echo json_encode(['error' => 'El id_student es requerido']);
    exit;
  }
  $qry_delete_student = "DELETE FROM student WHERE id_student = ?";
  $stmt_delete_student = mysqli_prepare($DB_T, $qry_delete_student);
  mysqli_stmt_bind_param($stmt_delete_student, 'i', $id_student);
  try {
    mysqli_stmt_execute($stmt_delete_student);
  } catch (Exception $e) {
    mysqli_stmt_close($stmt_delete_student);
    http_response_code(500);
    // foreign key constraint fails, error code 1451
    // this means that there are records in other tables that depend on this student, so we cannot delete it without deleting those records first
    if ($e->getCode() === 1451) {
      echo json_encode(['error' => 'No se puede eliminar el estudiante porque tiene tareas o registros asociados']);
    } else {
      echo json_encode(['error' => 'Error al eliminar el estudiante']);
    }
    exit;
  }
  mysqli_stmt_close($stmt_delete_student);
  http_response_code(200);
  echo json_encode(['message' => 'Estudiante eliminado exitosamente']);
}
