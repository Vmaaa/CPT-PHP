<?php
require_once __DIR__ . '/../functions/serverSpecifics.php';
$SS = ServerSpecifics::getInstance();
$SYSTEM_NAME = $SS->fnt_getSystemName();
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
define('API_URL',    BASE_URL . '/api/v1');

function url(string $path = ''): string
{
  $path = ltrim($path, '/');
  return BASE_URL . ($path ? '/' . $path : '');
}
?>

<script>
  const API_URL = '<?php echo API_URL; ?>';
  const BASE_URL = '<?php echo BASE_URL; ?>';
</script>
