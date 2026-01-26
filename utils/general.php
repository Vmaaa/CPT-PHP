<?php
function mysql_duplicate_value($message)
{
  if (preg_match("/Duplicate entry '([^']+)'/i", $message, $m)) {
    return $m[1];
  }
  return null;
}
function mysql_duplicate_key($message)
{
  if (preg_match("/for key '([^']+)'/i", $message, $m)) return $m[1];
  return null;
}

function fnt_validateString_v001($value, $min, $max)
{
  if (!is_string($value)) {
    return false;
  }
  $len = mb_strlen($value);
  return $len >= $min && $len <= $max;
}

function fnt_validateDateTime_v001($datetime)
{
  $d = DateTime::createFromFormat('Y-m-d H:i:s', $datetime);
  return $d && $d->format('Y-m-d H:i:s') === $datetime;
}



function gen_strong_password(int $len = 12): string
{
  $u = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
  $l = 'abcdefghijkmnopqrstuvwxyz';
  $d = '23456789';
  $s = '!@#$%^&*()-_=+[]{}<>?';
  $all = $u . $l . $d . $s;
  $pwd = $u[random_int(0, strlen($u) - 1)]
    . $l[random_int(0, strlen($l) - 1)]
    . $d[random_int(0, strlen($d) - 1)]
    . $s[random_int(0, strlen($s) - 1)];
  for ($i = strlen($pwd); $i < $len; $i++) {
    $pwd .= $all[random_int(0, strlen($all) - 1)];
  }
  return str_shuffle($pwd);
}

function  fnt_validateRequiredParams(array $required, array $data): array
{
  $missing = [];
  foreach ($required as $k) {
    if (!isset($data[$k])) {
      $missing[] = $k;
    }
  }
  return $missing;
}

function fnt_validateCURP($curp)
{
  $re = '/^([A-Z][AEIOUX][A-Z]{2}\d{2}(?:0[1-9]|1[0-2])(?:0[1-9]|[12]\d|3[01])[HM](?:AS|B[CS]|C[CLMSH]|D[FG]|G[TR]|HG|JC|M[CNS]|N[ETL]|OC|PL|Q[TR]|S[PLR]|T[CSL]|VZ|YN|ZS)[B-DF-HJ-NP-TV-Z]{3}[A-Z\d])(\d)$/';
  if (!preg_match($re, $curp, $validado)) {
    return false; // No coincide el formato
  }
  return true;
}

function  fnt_validateSchoolIDNumber_v001($id_number)
{
  return preg_match('/^(20[2-9][0-9])\d{6}$/', $id_number) === 1;
}


function getHumanizedType($type)
{
  $types = [
    'upload_protocols' => 'Subir protocolos (1ra fase)',
    'assign_reviewers' => 'Asignar revisores',
    'judge_protocols' => 'Juzgar protocolos (1ra fase)',
    're-upload_protocols' => 'Re-subir protocolos (1ra fase)',
    'select_protocols' => 'Seleccionar protocolos para presentación final (1ra fase)',
    'protocol_presentations' => 'Presentaciones de protocolos (1ra fase)',
    'grade_protocols' => 'Calificar presentaciones de protocolos (1ra fase)',
    'second_protocol_presentations' => 'Presentaciones de protocolos (2da fase)',
    'grade_second_protocols' => 'Calificar presentaciones de protocolos (2da fase)'
  ];
  return $types[$type] ?? null;
}

function getKeyHumanizedType($humanizedType)
{
  $types = [
    'Subir protocolos (1ra fase)' => 'upload_protocols',
    'Asignar revisores' => 'assign_reviewers',
    'Juzgar protocolos (1ra fase)' => 'judge_protocols',
    'Re-subir protocolos (1ra fase)' => 're-upload_protocols',
    'Seleccionar protocolos para presentación final (1ra fase)' => 'select_protocols',
    'Presentaciones de protocolos (1ra fase)' => 'protocol_presentations',
    'Calificar presentaciones de protocolos (1ra fase)' => 'grade_protocols',
    'Presentaciones de protocolos (2da fase)' => 'second_protocol_presentations',
    'Calificar presentaciones de protocolos (2da fase)' => 'grade_second_protocols'
  ];
  return $types[$humanizedType] ?? null;
}
