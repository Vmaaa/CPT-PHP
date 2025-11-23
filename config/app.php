<?php
$projectRoot = realpath(__DIR__ . '/..');
$docRoot     = realpath($_SERVER['DOCUMENT_ROOT'] ?? '');
$baseUrl     = '';

if ($projectRoot && $docRoot && str_starts_with($projectRoot, $docRoot)) {
    $baseUrl = rtrim(str_replace($docRoot, '', $projectRoot), '/');
} else {
    $scriptDir = dirname($_SERVER['SCRIPT_NAME'] ?? '') ?: '';
    $baseUrl   = rtrim($scriptDir, '/');
}

define('BASE_PATH', $projectRoot ?: __DIR__ . '/..');
define('BASE_URL',  $baseUrl ?: '');

define('ASSETS_URL', BASE_URL . '/assets');
define('CSS_URL',    ASSETS_URL . '/css');
define('JS_URL',     BASE_URL . '/js');

function url(string $path = ''): string {
    $path = ltrim($path, '/');
    return BASE_URL . ($path ? '/' . $path : '');
}
