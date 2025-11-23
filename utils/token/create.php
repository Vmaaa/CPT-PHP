<?php

require_once __DIR__ . '/../../vendor/autoload.php';
use Firebase\JWT\JWT;

function fnt_createEncryptedToken_v001($information, $duration)
{
  $timestamp = time();
  $expiration_timestamp = $timestamp + $duration;
  $token_data = $information . '|' . $expiration_timestamp;
  $secret_key = getenv("RECOVERY_TOKEN_SEED");
  $cipher_method = 'AES-256-CBC';
  $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($cipher_method));
  $encrypted_token = openssl_encrypt($token_data, $cipher_method, $secret_key, 0, $iv);
  $result = $iv . $encrypted_token;
  return base64_encode($result);
}


function fnt_createJWT_v001($acco_id, $acco_name, $acco_email, $acco_role, $acco_status, $first_login, $duration = 60 * 60)
{
  $key = getenv("JWT_SEED");
  $payload = [
    'iss' => 'auth.ai_analytics.com',
    'aud' => 'api.ai_analytics.com',
    'sub' => $acco_id,
    'iat' => time(),
    'nbf' => time(),
    'exp' => time() + $duration, // Token válido por 1 hora
    'data' => [
      'acco_id' => $acco_id,
      'acco_name' => $acco_name,
      'acco_email' => $acco_email,
      'acco_role' => $acco_role,
      'acco_status' => $acco_status,
      'first_login' => $first_login
    ]
  ];
  return JWT::encode($payload, $key, 'HS256');
}
