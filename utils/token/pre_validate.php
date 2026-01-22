<?php
require_once __DIR__ . '/validate.php';
require_once __DIR__ . '/../../functions/serverSpecifics.php';
require_once __DIR__ . '/../general.php';

$jwt = $_COOKIE['jwt'] ?? null;
if (!$jwt) {
  http_response_code(401);
  echo json_encode(['error' => 'Missing token']);
  exit;
}

$decoded_jwt = fnt_validateJWT_v001($jwt);
if ($decoded_jwt === false) {
  http_response_code(401);
  header('Content-Type: application/json');
  echo json_encode(["error" => "Invalid or expired token"]);
  exit;
}

$AUTH = [
  'acco_id' => (int) $decoded_jwt['acco_id'],
  'acco_email' => $decoded_jwt['acco_email'],
  'acco_name' => $decoded_jwt['acco_name'],
  'acco_role' => $decoded_jwt['acco_role'],
  'acco_status' => $decoded_jwt['acco_status'],
  'first_login' => $decoded_jwt['first_login'],
];

$SS = ServerSpecifics::getInstance();
$DB_T = $SS->fnt_getDBConnection();
$API_URL = $SS->fnt_getAPIUrl();

//retrieve specfic student / professor info
if (in_array($AUTH['acco_role'], ['student', 'professor', 'admin'])) {
  $table = $AUTH['acco_role'] === 'student' ? 'student' : 'professor';
  $query = "SELECT * FROM $table WHERE acco_id = ?";
  $stmt = mysqli_prepare($DB_T, $query);
  mysqli_stmt_bind_param($stmt, 'i', $AUTH['acco_id']);
  mysqli_stmt_execute($stmt);
  $result = mysqli_stmt_get_result($stmt);
  $specific_info = mysqli_fetch_assoc($result);
  mysqli_stmt_close($stmt);

  if ($specific_info) {
    $AUTH = array_merge($AUTH, $specific_info);
  } else {
    http_response_code(401);
    echo json_encode(['error' => 'Account information not found']);
    exit;
  }
}
