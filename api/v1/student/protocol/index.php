<?php
$AVALIABLE_METHODS = ['POST'];
header('Content-Type: application/json');

require_once dirname(__DIR__, 4) . "/config/cors.php";
require_once dirname(__DIR__, 4) . "/utils/token/pre_validate.php";
require_once dirname(__DIR__, 4) . "/functions/serverSpecifics.php";

$SS = ServerSpecifics::getInstance();
$DB = $SS->fnt_getDBConnection();

/* ===== Validación de sesión ===== */
if (!$AUTH || $AUTH['acco_role'] !== 'student') {
  http_response_code(403);
  echo json_encode(['error' => 'Unauthorized']);
  exit;
}

try {
  $DB->begin_transaction();

  /* ===== 1. Obtener estudiante ===== */
  $stmt = $DB->prepare(
    "SELECT id_student FROM student WHERE acco_id = ? LIMIT 1"
  );
  $stmt->bind_param("i", $AUTH['acco_id']);
  $stmt->execute();
  $student = $stmt->get_result()->fetch_assoc();

  if (!$student) {
    throw new Exception("Alumno no encontrado");
  }

  $idStudent = (int)$student['id_student'];

  /* ===== 2. Validar archivo ===== */
  if (
    !isset($_FILES['protocol_file']) ||
    $_FILES['protocol_file']['error'] !== UPLOAD_ERR_OK
  ) {
    throw new Exception("Error al subir el archivo");
  }

  if ($_FILES['protocol_file']['type'] !== 'application/pdf') {
    throw new Exception("El archivo debe ser PDF");
  }

  if ($_FILES['protocol_file']['size'] > 10 * 1024 * 1024) {
    throw new Exception("El archivo excede 10 MB");
  }

  /* ===== 3. Guardar PDF ===== */
  $baseUploadDir = $_SERVER['DOCUMENT_ROOT'] . "/CPT/uploads/protocols";

  if (!is_dir($baseUploadDir)) {
    mkdir($baseUploadDir, 0777, true);
  }

  $studentDir = $baseUploadDir . "/" . $idStudent;

  if (!is_dir($studentDir)) {
    mkdir($studentDir, 0777, true);
  }

  $fileName = uniqid("protocol_", true) . ".pdf";
  $filePath = $studentDir . "/" . $fileName;

  if (!is_uploaded_file($_FILES['protocol_file']['tmp_name'])) {
    throw new Exception("Archivo no válido");
  }

  if (!move_uploaded_file($_FILES['protocol_file']['tmp_name'], $filePath)) {
    throw new Exception("No se pudo guardar el archivo");
  }

  /* ===== 4. Crear final_project ===== */
  $stmt = $DB->prepare(
    "INSERT INTO final_project (title, abstract, id_career, status)
     VALUES (?, ?, ?, 'PENDING')"
  );
  $stmt->bind_param(
    "ssi",
    $_POST['title'],
    $_POST['abstract'],
    $_POST['id_career']
  );
  $stmt->execute();

  $idProject = (int)$DB->insert_id;

  /* ===== 5. Relación alumno-proyecto ===== */
  $stmt = $DB->prepare(
    "INSERT INTO fp_student (id_student, id_final_project)
     VALUES (?, ?)"
  );
  $stmt->bind_param("ii", $idStudent, $idProject);
  $stmt->execute();

  /* ===== 6. Crear fp_change (primer envío) ===== */
  $stmt = $DB->prepare(
    "INSERT INTO fp_change (id_final_project, stage, file_url)
     VALUES (?, 1, ?)"
  );
  $stmt->bind_param("is", $idProject, $filePath);
  $stmt->execute();

  /* ===== 7. Asignar asesores ===== */
  if ($_POST['advisor_1'] === $_POST['advisor_2']) {
    throw new Exception("Los asesores deben ser distintos");
  }

  foreach (['advisor_1', 'advisor_2'] as $advisorKey) {
    $stmt = $DB->prepare(
      "INSERT INTO fp_advisor (id_professor, id_final_project)
       VALUES (?, ?)"
    );
    $stmt->bind_param(
      "ii",
      $_POST[$advisorKey],
      $idProject
    );
    $stmt->execute();
  }

  /* ===== Commit ===== */
  $DB->commit();

  echo json_encode([
    'success' => true,
    'id_final_project' => $idProject
  ]);
} catch (Exception $e) {
  $DB->rollback();
  http_response_code(500);
  echo json_encode([
    'error' => $e->getMessage()
  ]);
}
