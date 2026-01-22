<?php
function fnt_parseInputMultiPart(): array
{
  $lines = @file('php://input');
  if ($lines === false) return [];

  $result = [];
  $keyLinePrefix = 'Content-Disposition: form-data; name="';
  $currentKey = null;
  $currentValue = [];
  foreach ($lines as $line) {
    if (strpos($line, $keyLinePrefix) !== false) {
      if ($currentKey !== null) {
        // Save previous key-value
        array_shift($currentValue);
        array_pop($currentValue);
        $value = mb_substr(implode('', $currentValue), 0, -2, 'UTF-8');
        // Handle array keys (variable[])
        if (substr($currentKey, -2) === '[]') {
          $baseKey = substr($currentKey, 0, -2);
          if (!isset($result[$baseKey]) || !is_array($result[$baseKey])) {
            $result[$baseKey] = [];
          }
          $result[$baseKey][] = $value;
        } else {
          $result[$currentKey] = $value;
        }
      }
      // Extract key name
      $start = strpos($line, $keyLinePrefix) + strlen($keyLinePrefix);
      $end = strpos($line, '"', $start);
      $currentKey = substr($line, $start, $end - $start);
      $currentValue = [];
    } elseif ($currentKey !== null) {
      $currentValue[] = $line;
    }
  }
  // Save last key-value
  if ($currentKey !== null) {
    array_shift($currentValue);
    array_pop($currentValue);
    $value = mb_substr(implode('', $currentValue), 0, -2, 'UTF-8');
    if (substr($currentKey, -2) === '[]') {
      $baseKey = substr($currentKey, 0, -2);
      if (!isset($result[$baseKey]) || !is_array($result[$baseKey])) {
        $result[$baseKey] = [];
      }
      $result[$baseKey][] = $value;
    } else {
      $result[$currentKey] = $value;
    }
  }
  return $result;
}
