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
                $result[$currentKey] = mb_substr(implode('', $currentValue), 0, -2, 'UTF-8');
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
        $result[$currentKey] = mb_substr(implode('', $currentValue), 0, -2, 'UTF-8');
    }
    return $result;
}
