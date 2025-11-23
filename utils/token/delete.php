<?php
function fnt_deleteRecoveryPasswordToken_v001($DBLINK, $token)
{
    if ($token !== null) {
        $qry_deleteToken = "DELETE FROM pending_token WHERE token = ? AND type = 'PASSWORD'";
        $stmt = mysqli_prepare($DBLINK, $qry_deleteToken);

        if ($stmt === false) {
            return false;
        }

        mysqli_stmt_bind_param($stmt, 's', $token);
        $result = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return $result;
    }

    return true;
}
