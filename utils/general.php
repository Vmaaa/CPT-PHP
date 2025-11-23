<?php
function mysql_duplicate_value($message) {
    if (preg_match("/Duplicate entry '([^']+)'/i", $message, $m)) {
        return $m[1];
    }
    return null;
}
function mysql_duplicate_key($message) {
    if (preg_match("/for key '([^']+)'/i", $message, $m)) return $m[1];
    return null;
}

function fnt_validateString_v001($value, $min, $max) {
  if (!is_string($value)) {
        return false;
  }
    $len = mb_strlen($value);
    return $len >= $min && $len <= $max;
}

function fnt_validateDateTime_v001($datetime) {
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
