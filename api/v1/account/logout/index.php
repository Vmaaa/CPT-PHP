<?php

require_once __DIR__ . "/../../../../config/cors.php";
require_once __DIR__ . "/../../../../functions/serverSpecifics.php";
$SS = ServerSpecifics::getInstance();
$JWT_DURATION_TELAT = $SS->fnt_getJWTDuration();

// Borrar la cookie HTTP-only JWT
setcookie("jwt", "", [
    "expires" => time() - $JWT_DURATION_TELAT,
    "path" => "/",
    "httponly" => true,
    "secure" => true,
    "samesite" => "Strict"
]);

header("Content-Type: application/json");
echo json_encode([
    "success" => true,
    "message" => "Usuario deslogueado correctamente"
]);
