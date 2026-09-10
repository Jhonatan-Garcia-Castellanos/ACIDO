<?php
// lib/Mailer.php — Envío SMTP con PHPMailer (instalación manual, sin composer).
require_once __DIR__ . "/PHPMailer/PHPMailer.php";
require_once __DIR__ . "/PHPMailer/SMTP.php";
require_once __DIR__ . "/PHPMailer/Exception.php";

use PHPMailer\PHPMailer\PHPMailer;

class Mailer
{
    private $cfg;

    public function __construct()
    {
        $this->cfg = require __DIR__ . "/../config/mail.php";
    }

    public function getAppUrl()
    {
        return rtrim($this->cfg['app_url'] ?? 'http://localhost/ACIDO/BACKPHP_TEST', '/');
    }

    public function isConfigured()
    {
        return !empty($this->cfg['username'])
            && strpos($this->cfg['username'], 'TU_MAILTRAP') === false;
    }

    /** Envía el correo de recuperación. Retorna [ok=>bool, error=>?string]. */
    public function enviarRecuperacion($paraEmail, $link)
    {
        if (!$this->isConfigured()) {
            return ['ok' => false, 'error' => 'SMTP sin configurar: completa config/mail.php con tus credenciales de Mailtrap.'];
        }
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = $this->cfg['host'];
            $mail->Port = (int)$this->cfg['port'];
            $mail->SMTPAuth = true;
            $mail->Username = $this->cfg['username'];
            $mail->Password = $this->cfg['password'];
            if (!empty($this->cfg['encryption'])) {
                $mail->SMTPSecure = $this->cfg['encryption'] === 'ssl'
                    ? PHPMailer::ENCRYPTION_SMTPS
                    : PHPMailer::ENCRYPTION_STARTTLS;
            } else {
                $mail->SMTPSecure = false;
                $mail->SMTPAutoTLS = false;
            }
            $mail->CharSet = 'UTF-8';
            $mail->setFrom($this->cfg['from_email'], $this->cfg['from_name']);
            $mail->addAddress($paraEmail);
            $mail->isHTML(true);
            $mail->Subject = 'Recupera tu contraseña — ÁCIDO Colombia';

            // Logo embebido (CID): se ve sin hosting externo ni "mostrar imágenes"
            $logoCid = null;
            foreach (['acidoo.png', 'LOGO2.png', 'iconooo.png'] as $img) {
                $path = __DIR__ . '/../public/img/' . $img;
                if (is_file($path)) {
                    $mail->addEmbeddedImage($path, 'acido_logo', $img);
                    $logoCid = 'cid:acido_logo';
                    break;
                }
            }

            $safeLink = htmlspecialchars($link, ENT_QUOTES, 'UTF-8');
            $safeEmail = htmlspecialchars($paraEmail, ENT_QUOTES, 'UTF-8');
            $logoHtml = $logoCid
                ? '<img src="' . $logoCid . '" alt="ÁCIDO Colombia" width="150" style="display:block;border:0;max-width:150px;height:auto;">'
                : '<span style="font-size:26px;font-weight:bold;letter-spacing:4px;color:#ffffff;">ACIDO</span>';

            // Plantilla compatible con clientes de correo: tablas + estilos en línea
            $mail->Body = '<!DOCTYPE html><html lang="es"><body style="margin:0;padding:0;background-color:#f4f5f7;">'
                . '<div style="display:none;max-height:0;overflow:hidden;opacity:0;">Restablece tu contraseña de ÁCIDO Colombia. El enlace vence en 1 hora.</div>'
                . '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f5f7;padding:24px 12px;">'
                . '<tr><td align="center">'
                . '<table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background-color:#ffffff;border-radius:12px;overflow:hidden;">'
                // Encabezado marca
                . '<tr><td align="center" style="background-color:#1C3B4A;padding:32px 24px 24px;">' . $logoHtml
                . '<div style="margin-top:12px;font-size:12px;letter-spacing:3px;color:#D48A3A;font-weight:bold;">COLOMBIA</div></td></tr>'
                . '<tr><td style="background-color:#D48A3A;height:4px;line-height:4px;font-size:0;">&nbsp;</td></tr>'
                // Cuerpo
                . '<tr><td style="padding:32px 36px 8px;font-family:Arial,Helvetica,sans-serif;color:#2d3748;">'
                . '<h1 style="margin:0 0 12px;font-size:22px;color:#1C3B4A;">Recupera tu contraseña</h1>'
                . '<p style="margin:0 0 12px;font-size:14px;line-height:22px;">Hola, <b>' . $safeEmail . '</b></p>'
                . '<p style="margin:0 0 20px;font-size:14px;line-height:22px;">Recibimos una solicitud para restablecer tu contraseña de <b>ÁCIDO Colombia</b>. Haz clic en el botón para crear una nueva clave:</p>'
                // Botón CTA
                . '<table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr><td align="center" style="padding:8px 0 20px;">'
                . '<a href="' . $safeLink . '" style="display:inline-block;background-color:#D48A3A;color:#ffffff;font-family:Arial,Helvetica,sans-serif;font-size:15px;font-weight:bold;text-decoration:none;padding:14px 36px;border-radius:8px;">Crear nueva contraseña</a>'
                . '</td></tr></table>'
                // Datos del enlace
                . '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f8f9fc;border:1px solid #e3e6f0;border-radius:8px;margin-bottom:16px;">'
                . '<tr>'
                . '<td align="center" style="padding:12px;font-family:Arial,Helvetica,sans-serif;font-size:13px;color:#1C3B4A;"><b>&#9201; Vence en 1 hora</b></td>'
                . '<td align="center" style="padding:12px;font-family:Arial,Helvetica,sans-serif;font-size:13px;color:#1C3B4A;"><b>&#128274; Un solo uso</b></td>'
                . '</tr></table>'
                . '<p style="margin:0 0 6px;font-size:12px;color:#718096;">Si el botón no funciona, copia y pega este enlace en tu navegador:</p>'
                . '<p style="margin:0 0 20px;font-size:12px;word-break:break-all;background-color:#f4f5f7;padding:10px 12px;border-radius:6px;"><a href="' . $safeLink . '" style="color:#004BA0;">' . $safeLink . '</a></p>'
                . '<p style="margin:0;font-size:12px;line-height:20px;color:#718096;">Si no solicitaste este cambio, ignora este mensaje: tu contraseña seguirá igual y puedes reportarlo a soporte.</p>'
                . '</td></tr>'
                // Pie
                . '<tr><td align="center" style="background-color:#1C3B4A;padding:20px 24px;font-family:Arial,Helvetica,sans-serif;">'
                . '<div style="font-size:12px;color:#ffffff;font-weight:bold;letter-spacing:2px;">ACIDO COLOMBIA</div>'
                . '<div style="font-size:11px;color:#a0aec0;margin-top:6px;">Este es un mensaje automático, no lo respondas. &copy; ' . date('Y') . '</div>'
                . '</td></tr>'
                . '</table>'
                . '</td></tr></table>'
                . '</body></html>';
            $mail->AltBody = "Hola $paraEmail\n\nRestablece tu contraseña de ÁCIDO Colombia aquí (vence en 1 hora, un solo uso):\n$link\n\nSi no fuiste tú, ignora este mensaje.";
            $mail->send();
            return ['ok' => true, 'error' => null];
        } catch (Exception $e) {
            error_log("Mailer::enviar: " . $e->getMessage());
            return ['ok' => false, 'error' => 'No se pudo enviar el correo. Revisa config/mail.php y tu inbox de Mailtrap.'];
        }
    }
}
