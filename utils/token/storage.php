<?php
function fnt_insertRecoveryPasswordToken_v001($DBLINK, $user_email, $token, $duration)
{
    $qry_insertToken = "
        INSERT INTO pending_token (acco_email, token, duration, type)
        VALUES (?, ?, ?, 'PASSWORD')
    ";
    $stmt_insertToken = mysqli_prepare($DBLINK, $qry_insertToken);

    if (!$stmt_insertToken) {
        return false;
    }

    mysqli_stmt_bind_param($stmt_insertToken, 'sss', $user_email, $token, $duration);
    $execution_result = mysqli_stmt_execute($stmt_insertToken);
    mysqli_stmt_close($stmt_insertToken);
    return $execution_result;
}
