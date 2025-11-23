<?php
require_once __DIR__ . '/../../vendor/autoload.php';
use Firebase\JWT\Key;
use Firebase\JWT\JWT;

function fnt_validateJWT_v001($jwt)
{
    $key = getenv("JWT_SEED");

    try {
        $decoded = JWT::decode($jwt, new Key($key, 'HS256'));
        $decoded_array = (array)$decoded;
        if ($decoded_array['exp'] < time())
            return false;
        return (array)$decoded_array['data'];
    } catch (Exception $e) {
        return false;
    }
}

function fnt_decryptToken_v001($encrypted_token)
{
    $cipher_method = 'AES-256-CBC';
    $secret_key = getenv("RECOVERY_TOKEN_SEED");
    $decoded_token = base64_decode($encrypted_token);

    if ($decoded_token === false) {
        return false;
    }

    $iv_size = openssl_cipher_iv_length($cipher_method);
    $iv = substr($decoded_token, 0, $iv_size);
    $encrypted_data = substr($decoded_token, $iv_size);

    if (strlen($iv) !== $iv_size || $encrypted_data === false) {
        return false;
    }

    $decrypted_token = openssl_decrypt($encrypted_data, $cipher_method, $secret_key, 0, $iv);

    if ($decrypted_token === false) {
        return false;
    }

    return $decrypted_token;
}

function fnt_validateRecoveryPasswordToken_v001($DBLINK, $recovery_token)
{
    $decrypted_token = fnt_decryptToken_v001($recovery_token);

    if ($decrypted_token === false) {
        return false;
    }

    $data = explode('|', $decrypted_token);

    if (count($data) < 1) {
        return false;
    }
    $acco_email = $data[0];
    $query = "SELECT a.acco_id 
                  FROM account a 
                  INNER JOIN pending_token p ON a.acco_email = p.acco_email 
                  WHERE p.token = ? AND p.type = 'PASSWORD' AND a.acco_email = ?";

    $stmt = mysqli_prepare($DBLINK, $query);
    mysqli_stmt_bind_param($stmt, 'ss', $recovery_token,$acco_email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $acco_id = $row['acco_id'];
        return array("acco_id" => $acco_id, "acco_email" => $acco_email);
    } else {
        return false;
    }
}


