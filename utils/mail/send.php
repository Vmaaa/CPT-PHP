<?php

require_once __DIR__ . '/../../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function fnt_sendEmailForChangedPassword_v001($acco_email, $SERVER_URL)
{
    $mail = new PHPMailer(true);

    try {
        $mail->addAddress($acco_email, 'Cliente');
        $mail->setFrom('info.net2alliance@gmail.com', 'SALES360 System');
        $mail->AddEmbeddedImage(__DIR__ . '/../../img/logo.png', 'logo_call');

        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = "SALES360 - Tu contraseña ha sido actualizada";

        $mail->Body = '
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Contraseña cambiada</title>
</head>
<body style="margin:0;padding:0;background:#fafafa;font-family:Arial,sans-serif;">
<center>
<table width="100%" bgcolor="#fafafa" cellpadding="0" cellspacing="0" border="0">
<tr>
<td align="center" style="padding:20px;">
    <table width="600" bgcolor="#ffffff" cellpadding="0" cellspacing="0" border="0" style="border-radius:6px;overflow:hidden;">
        <tr>
            <td align="center" style="padding:20px;">
                <img src="cid:logo_call" width="155" alt="Logo" style="display:block;border:0;margin-bottom:15px;" />
                <h1 style="font-size:24px;color:#333;margin:0 0 15px 0;line-height:32px;">
                    Tu contraseña ha cambiado
                </h1>
                <p style="font-size:16px;color:#555;line-height:24px;margin:0 0 20px 0;">
                    La contraseña de tu cuenta en SALES360 System ha cambiado.<br/>
                    Si fuiste tú, puedes descartar este correo.<br/>
                    Si no reconoces esta actividad, te sugerimos actualizar tu contraseña inmediatamente:
                </p>
                <!-- Botón compatible con Outlook -->
                <!--[if mso]>
                <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" href="' . $SERVER_URL . '/pages/recovery_password.php" style="height:44px;v-text-anchor:middle;width:300px;" arcsize="10%" strokecolor="#008b9d" fillcolor="#008b9d">
                    <w:anchorlock/>
                    <center style="color:#ffffff;font-family:Arial,sans-serif;font-size:16px;font-weight:bold;">Actualizar contraseña</center>
                </v:roundrect>
                <![endif]-->
                <![if !mso]>
                <a href="' . $SERVER_URL . '/pages/recovery_password.php" target="_blank" style="display:inline-block;background:#008b9d;color:#ffffff;padding:12px 30px;border-radius:5px;font-size:16px;font-weight:bold;text-decoration:none;margin:20px 0;">Actualizar contraseña</a>
                <![endif]>
                <p style="font-size:14px;color:#555;line-height:20px;margin:20px 0 0 0;">
                    Si no solicitaste este cambio, contacta al soporte de SALES360 System.
                </p>
            </td>
        </tr>
        <tr>
            <td align="center" style="padding:20px;">
                <p style="font-size:12px;color:#888;">© ' . date("Y") . ' SALES360. Todos los derechos reservados.</p>
            </td>
        </tr>
    </table>
</td>
</tr>
</table>
</center>
</body>
</html>
';

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Error al enviar correo de cambio de contraseña: " . $mail->ErrorInfo);
        return false;
    }
}

function fnt_sendEmailForRecoveryPassword_v001($acco_email, $encoded_token, $SERVER_URL)
{
    $mail = new PHPMailer(true);

    try {
        $mail->addAddress($acco_email, 'Cliente');
        $mail->setFrom('info.net2alliance@gmail.com', 'SALES360 System');
        $mail->AddEmbeddedImage(__DIR__ . '/../../img/logo.png', 'logo_call');

        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = "SALES360 - Solicitud de recuperación de contraseña";

        $mail->Body = '
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Recuperación de contraseña</title>
</head>
<body style="margin:0;padding:0;background:#fafafa;font-family:Arial,sans-serif;">
<center>
<table width="100%" bgcolor="#fafafa" cellpadding="0" cellspacing="0" border="0">
<tr>
<td align="center" style="padding:20px;">
    <table width="600" bgcolor="#ffffff" cellpadding="0" cellspacing="0" border="0" style="border-radius:6px;overflow:hidden;">
        <tr>
            <td align="center" style="padding:20px;">
                <img src="cid:logo_call" width="155" alt="Logo" style="display:block;border:0;margin-bottom:15px;" />
                <h1 style="font-size:24px;color:#333;margin:0 0 15px 0;line-height:32px;">
                    Se ha solicitado el cambio de contraseña en tu cuenta SALES360 System
                </h1>
                <p style="font-size:16px;color:#555;line-height:24px;margin:0 0 20px 0;">
                    Para continuar, haz click en el botón de abajo:
                </p>
                <!-- Botón compatible con Outlook -->
                <!--[if mso]>
                <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" href="' . $SERVER_URL . '/pages/change_password.php?recovery_token=' . $encoded_token . '" style="height:44px;v-text-anchor:middle;width:300px;" arcsize="10%" strokecolor="#008b9d" fillcolor="#008b9d">
                    <w:anchorlock/>
                    <center style="color:#ffffff;font-family:Arial,sans-serif;font-size:16px;font-weight:bold;">Restaurar contraseña</center>
                </v:roundrect>
                <![endif]-->
                <![if !mso]>
                <a href="' . $SERVER_URL . '/pages/change_password.php?recovery_token=' . $encoded_token . '" target="_blank" style="display:inline-block;background:#008b9d;color:#ffffff;padding:12px 30px;border-radius:5px;font-size:16px;font-weight:bold;text-decoration:none;margin:20px 0;">Restaurar contraseña</a>
                <![endif]>
                <p style="font-size:14px;color:#555;line-height:20px;margin:20px 0 0 0;">
                    Siga las instrucciones del sistema para restablecer su contraseña.<br/>
                    * Si no solicitó este cambio, ignore este correo.
                </p>
            </td>
        </tr>
        <tr>
            <td align="center" style="padding:20px;">
                <p style="font-size:12px;color:#888;">© ' . date("Y") . ' SALES360 System. Todos los derechos reservados.</p>
            </td>
        </tr>
    </table>
</td>
</tr>
</table>
</center>
</body>
</html>
';

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Error al enviar correo de recuperación: " . $mail->ErrorInfo);
        return false;
    }
}

function fnt_sendEmailForNewUser_v001($param_email, $param_password, $webpage_url)
{
    $mail = new PHPMailer(true);

    try {
        $mail->addAddress($param_email, 'Nuevo usuario');
        $mail->setFrom('info.net2alliance@gmail.com', 'SALES360 System');
        $mail->AddEmbeddedImage(__DIR__ . '/../../img/logo.png', 'logo_call');

        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = "SALES360 - Acceso a tu cuenta";

        $mail->Body = '
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" style="font-family: Arial, Helvetica, sans-serif;">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <title>Bienvenido a SALES360 System</title>
  <style type="text/css">
    a { text-decoration: none; }
    @media only screen and (max-width: 600px) {
      table[class="content"] { width: 100% !important; }
    }
  </style>
</head>
<body style="margin:0; padding:0; background-color:#fafafa;">
  <center>
    <table width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#fafafa">
      <tr>
        <td align="center" valign="top" style="padding:20px;">
          <table class="content" width="600" cellspacing="0" cellpadding="0" border="0" bgcolor="#ffffff" style="border-collapse:collapse; border-radius:6px; overflow:hidden;">
            <tr>
              <td align="center" style="padding:20px;">
                <img src="cid:logo_call" width="150" alt="Logo" style="display:block; border:0; margin-bottom:15px;" />
                <h1 style="font-size:24px; color:#333333; margin:0 0 10px 0;">¡Bienvenido a SALES360 System!</h1>
                <p style="font-size:15px; color:#555555; line-height:22px; margin:0;">
                  Se ha creado una nueva cuenta para ti en el sistema <strong>SALES360 System</strong>.
                </p>
              </td>
            </tr>

            <tr>
              <td align="center" style="padding:20px;">
                <table width="90%" border="0" cellspacing="0" cellpadding="10" style="background-color:#f7f7f7; border-radius:5px;">
                  <tr>
                    <td align="center" style="font-size:15px; color:#333333;">
                      <strong>Correo</strong> <br/>' . htmlspecialchars($param_email) . '<br/>
                      <strong>Contraseña temporal</strong> <br/>' . htmlspecialchars($param_password) . '
                    </td>
                  </tr>
                </table>
                <p style="font-size:14px; color:#555555; margin-top:15px; line-height:22px;">
                  Es necesario que inicies sesión y cambies tu contraseña por motivos de seguridad.
                </p>
              </td>
            </tr>

            <tr>
              <td align="center" style="padding:20px;">
                <!-- Botón compatible con Outlook -->
                <!--[if mso]>
                  <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml"
                    xmlns:w="urn:schemas-microsoft-com:office:word"
                    href="' . $webpage_url . '/index.php"
                    style="height:45px;v-text-anchor:middle;width:220px;" arcsize="10%" strokecolor="#008b9d" fillcolor="#008b9d">
                    <w:anchorlock/>
                    <center style="color:#ffffff;font-family:Arial, Helvetica, sans-serif;font-size:16px;font-weight:bold;">
                      Iniciar sesión
                    </center>
                  </v:roundrect>
                <![endif]-->
                <![if !mso]>
                  <a href="' . $webpage_url . '/index.php"
                    style="display:inline-block;background-color:#008b9d;color:#ffffff;
                    padding:12px 30px;border-radius:5px;font-size:16px;font-family:Arial, Helvetica, sans-serif;
                    font-weight:bold;">Iniciar sesión</a>
                <![endif]>
              </td>
            </tr>

            <tr>
              <td align="center" style="padding:20px;">
                <p style="font-size:13px; color:#888888; line-height:20px;">
                  Si no esperabas este correo, ignóralo.<br/>
                  © ' . date("Y") . ' SALES360 System. Todos los derechos reservados.
                </p>
              </td>
            </tr>
          </table>
        </td>
      </tr>
    </table>
  </center>
</body>
</html>
        ';

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Error al enviar correo de nuevo usuario: " . $mail->ErrorInfo);
        return false;
    }
}

