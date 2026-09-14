<?php
// lib/Mailer.php — Envío SMTP con PHPMailer (instalación manual, sin composer).
require_once __DIR__ . "/PHPMailer/PHPMailer.php";
require_once __DIR__ . "/PHPMailer/SMTP.php";
require_once __DIR__ . "/PHPMailer/Exception.php";

use PHPMailer\PHPMailer\PHPMailer;

class Mailer
{
    private $cfg;
    private static $lastSend = 0;

    public function __construct()
    {
        $this->cfg = require __DIR__ . "/../config/mail.php";
    }

    /** Espaciado entre envíos del mismo proceso (Mailtrap free limita por segundo). */
    private function throttle()
    {
        $now = microtime(true);
        $wait = 2 - ($now - self::$lastSend);
        if ($wait > 0) usleep((int)($wait * 1000000));
        self::$lastSend = microtime(true);
    }

    /** Envío con 1 reintento ante throttling 5xx de Mailtrap (free). Best-effort. */
    private function sendWithRetry($mail, $tag)
    {
        $this->throttle();
        try {
            $mail->send();
            return ['ok' => true, 'error' => null];
        } catch (Exception $e) {
            $msg = $e->getMessage() . ' ' . $mail->ErrorInfo;
            if (preg_match('/too many|5\.7\.0|4\.7\.\d|temporar|rate/i', $msg)) {
                try { $mail->smtpClose(); } catch (Exception $eC) {}
                sleep(12);
                try {
                    $mail->send();
                    return ['ok' => true, 'error' => null];
                } catch (Exception $e2) {
                    error_log("Mailer::$tag reintento: " . $e2->getMessage());
                }
            }
            throw $e;
        }
    }

    /**
     * Historial auditable de correos (tabla notificacion_log).
     * Best-effort: si la tabla no existe (migración pendiente) se omite en silencio.
     */
    private function registrar($tipo, $destinatarios, $asunto, $ok, $detalle)
    {
        try {
            require_once __DIR__ . "/../config/conexion.php";
            $c = new Conexion();
            $st = $c->conn->prepare(
                "INSERT INTO notificacion_log (Tipo, Destinatarios, Asunto, Resultado, Detalle) " .
                "VALUES (:t, :d, :a, :r, :det)"
            );
            $dest = implode(',', array_map('trim', (array)$destinatarios));
            $st->execute([
                ':t' => substr($tipo, 0, 20),
                ':d' => substr($dest, 0, 500),
                ':a' => substr((string)$asunto, 0, 255),
                ':r' => $ok ? 'ok' : 'fallo',
                ':det' => substr((string)$detalle, 0, 500),
            ]);
        } catch (Exception $e) {}
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
                . '<div style="display:none;max-height:0;overflow:hidden;opacity:0;">Restablece tu contraseña de ÁCIDO Colombia. El enlace vence en 15 minutos.</div>'
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
                . '<td align="center" style="padding:12px;font-family:Arial,Helvetica,sans-serif;font-size:13px;color:#1C3B4A;"><b>&#9201; Vence en 15 minutos</b></td>'
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
            $mail->AltBody = "Hola $paraEmail\n\nRestablece tu contraseña de ÁCIDO Colombia aquí (vence en 15 minutos, un solo uso):\n$link\n\nSi no fuiste tú, ignora este mensaje.";
            $mail->send();
            return ['ok' => true, 'error' => null];
        } catch (Exception $e) {
            error_log("Mailer::enviar: " . $e->getMessage());
            return ['ok' => false, 'error' => 'No se pudo enviar el correo. Revisa config/mail.php y tu inbox de Mailtrap.'];
        }
    }

    /** Aviso de stock bajo a administradores (RF 2.3). $items: [Nombre_Producto, Stock_Actual, Stock_Minimo]. */
    public function enviarStockBajo($paraEmails, $items)
    {
        if (empty($paraEmails) || empty($items)) {
            $this->registrar('stock', $paraEmails, '(sin asunto)', false, 'Sin destinatarios o sin items');
            return ['ok' => false, 'error' => 'Sin destinatarios o sin items'];
        }
        if (!$this->isConfigured()) {
            error_log("Mailer::stockBajo: SMTP sin configurar, se omite envío a " . implode(',', (array)$paraEmails));
            $this->registrar('stock', $paraEmails, '(sin asunto)', false, 'SMTP sin configurar (.env MAIL_*)');
            return ['ok' => false, 'error' => 'SMTP sin configurar'];
        }
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = $this->cfg['host'];
            $mail->Port = (int)$this->cfg['port'];
            $mail->Timeout = 15;
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
            foreach ((array)$paraEmails as $dest) {
                $dest = trim((string)$dest);
                if ($dest !== '') $mail->addAddress($dest);
            }
            if (count($mail->getToAddresses()) === 0) return ['ok' => false, 'error' => 'Sin destinatarios válidos'];
            $mail->isHTML(true);
            $n = count($items);
            $mail->Subject = "Stock bajo: $n producto(s) — ÁCIDO Colombia";
            $rows = '';
            foreach ($items as $it) {
                $nom = htmlspecialchars($it['Nombre_Producto'] ?? ('ID ' . ($it['ID_Producto'] ?? '?')), ENT_QUOTES, 'UTF-8');
                $st = (int)($it['Stock_Actual'] ?? 0);
                $mn = (int)($it['Stock_Minimo'] ?? 10);
                $rows .= "<tr><td style=\"padding:8px 10px;border-bottom:1px solid #e3e6f0;\">$nom</td>"
                    . "<td align=\"center\" style=\"padding:8px 10px;border-bottom:1px solid #e3e6f0;\"><b style=\"color:#c53030;\">$st</b></td>"
                    . "<td align=\"center\" style=\"padding:8px 10px;border-bottom:1px solid #e3e6f0;\">$mn</td></tr>";
            }
            $link = $this->getAppUrl() . '/index.php?action=inventario&filtro=bajo';
            $safeLink = htmlspecialchars($link, ENT_QUOTES, 'UTF-8');
            $mail->Body = '<div style="font-family:Arial,Helvetica,sans-serif;color:#2d3748;max-width:600px;">'
                . '<h2 style="color:#1C3B4A;">Alerta de stock bajo</h2>'
                . "<p>Estos productos llegaron a su mínimo tras una venta o ajuste:</p>"
                . '<table width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #e3e6f0;border-radius:8px;overflow:hidden;">'
                . '<tr style="background:#1C3B4A;color:#fff;"><th align="left" style="padding:10px;">Producto</th><th>Stock</th><th>Mínimo</th></tr>'
                . $rows . '</table>'
                . "<p><a href=\"$safeLink\" style=\"display:inline-block;background:#C53030;color:#fff;padding:12px 24px;border-radius:8px;text-decoration:none;font-weight:bold;margin-top:12px;\">Ver inventario</a></p>"
                . '</div>';
            $mail->AltBody = "Stock bajo ($n): " . implode(', ', array_map(function ($i) {
                return ($i['Nombre_Producto'] ?? '?') . ' (' . ($i['Stock_Actual'] ?? 0) . '/' . ($i['Stock_Minimo'] ?? 10) . ')';
            }, $items)) . ". Ver: $link";
            $r = $this->sendWithRetry($mail, 'stockBajo');
            $this->registrar('stock', $paraEmails, $mail->Subject, !empty($r['ok']), $r['ok'] ? 'ENVIADO' : ($r['error'] ?? 'fallo'));
            return $r;
        } catch (Exception $e) {
            error_log("Mailer::stockBajo: " . $e->getMessage());
            $this->registrar('stock', $paraEmails ?? [], isset($mail) ? $mail->Subject : '(sin asunto)', false, substr($e->getMessage(), 0, 200));
            return ['ok' => false, 'error' => 'No se pudo enviar aviso de stock'];
        }
    }

    /**
     * Aviso de movimiento Kardex a administradores (RF 2.7/2.9).
     * $d: Producto, Tipo (Entrada/Ajuste/Salida), Cantidad (con signo),
     *     Motivo, Responsable, StockNuevo, StockMinimo.
     */
    public function enviarMovimientoKardex($paraEmails, $d)
    {
        if (empty($paraEmails) || empty($d)) {
            $this->registrar('kardex', $paraEmails, '(sin asunto)', false, 'Sin destinatarios o sin datos');
            return ['ok' => false, 'error' => 'Sin destinatarios o sin datos'];
        }
        if (!$this->isConfigured()) {
            error_log("Mailer::kardex: SMTP sin configurar, se omite envío.");
            $this->registrar('kardex', $paraEmails, '(sin asunto)', false, 'SMTP sin configurar (.env MAIL_*)');
            return ['ok' => false, 'error' => 'SMTP sin configurar'];
        }
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = $this->cfg['host'];
            $mail->Port = (int)$this->cfg['port'];
            $mail->Timeout = 15;
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
            foreach ((array)$paraEmails as $dest) {
                $dest = trim((string)$dest);
                if ($dest !== '') $mail->addAddress($dest);
            }
            if (count($mail->getToAddresses()) === 0) return ['ok' => false, 'error' => 'Sin destinatarios válidos'];
            $mail->isHTML(true);
            $tipo = $d['Tipo'] ?? 'Ajuste';
            $prod = $d['Producto'] ?? '?';
            $cant = (int)($d['Cantidad'] ?? 0);
            $mot = $d['Motivo'] ?? '';
            $resp = $d['Responsable'] ?? 'Sistema';
            $stNuevo = array_key_exists('StockNuevo', $d) ? (int)$d['StockNuevo'] : null;
            $stMin = array_key_exists('StockMinimo', $d) ? (int)$d['StockMinimo'] : null;
            $quedoBajo = ($stNuevo !== null && $stMin !== null && $stNuevo <= $stMin);
            $mail->Subject = "Kardex [$tipo]: $prod ($cant) — ÁCIDO Colombia";
            $safe = function ($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); };
            $colorCant = $cant < 0 ? '#c53030' : '#22543d';
            $alertaHtml = $quedoBajo
                ? '<p style="background:#fed7d7;color:#9b2c2c;padding:10px 14px;border-radius:8px;font-weight:bold;">Atención: el producto quedó en stock bajo (' . $stNuevo . '/' . $stMin . '). Revisa la campana de inventario.</p>'
                : '';
            $link = $this->getAppUrl() . '/index.php?action=inventario';
            $safeLink = htmlspecialchars($link, ENT_QUOTES, 'UTF-8');
            $mail->Body = '<div style="font-family:Arial,Helvetica,sans-serif;color:#2d3748;max-width:600px;">'
                . '<h2 style="color:#1C3B4A;">Movimiento de inventario registrado</h2>'
                . $alertaHtml
                . '<table width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #e3e6f0;border-radius:8px;overflow:hidden;">'
                . '<tr style="background:#1C3B4A;color:#fff;"><th align="left" style="padding:10px;">Campo</th><th align="left" style="padding:10px;">Detalle</th></tr>'
                . '<tr><td style="padding:8px 10px;border-bottom:1px solid #e3e6f0;">Producto</td><td style="padding:8px 10px;border-bottom:1px solid #e3e6f0;"><b>' . $safe($prod) . '</b></td></tr>'
                . '<tr><td style="padding:8px 10px;border-bottom:1px solid #e3e6f0;">Tipo</td><td style="padding:8px 10px;border-bottom:1px solid #e3e6f0;">' . $safe($tipo) . '</td></tr>'
                . '<tr><td style="padding:8px 10px;border-bottom:1px solid #e3e6f0;">Cantidad</td><td style="padding:8px 10px;border-bottom:1px solid #e3e6f0;"><b style="color:' . $colorCant . ';">' . $cant . '</b></td></tr>'
                . '<tr><td style="padding:8px 10px;border-bottom:1px solid #e3e6f0;">Motivo</td><td style="padding:8px 10px;border-bottom:1px solid #e3e6f0;">' . $safe($mot) . '</td></tr>'
                . '<tr><td style="padding:8px 10px;border-bottom:1px solid #e3e6f0;">Responsable</td><td style="padding:8px 10px;border-bottom:1px solid #e3e6f0;">' . $safe($resp) . '</td></tr>'
                . ($stNuevo !== null ? '<tr><td style="padding:8px 10px;">Stock resultante</td><td style="padding:8px 10px;"><b>' . $stNuevo . '</b>' . ($stMin !== null ? ' (mínimo ' . $stMin . ')' : '') . '</td></tr>' : '')
                . '</table>'
                . "<p><a href=\"$safeLink\" style=\"display:inline-block;background:#1C3B4A;color:#fff;padding:12px 24px;border-radius:8px;text-decoration:none;font-weight:bold;margin-top:12px;\">Ver Kardex</a></p>"
                . '</div>';
            $mail->AltBody = "Kardex [$tipo]: $prod ($cant). Motivo: $mot. Responsable: $resp."
                . ($stNuevo !== null ? " Stock: $stNuevo." : "") . " Ver: $link";
            $r = $this->sendWithRetry($mail, 'kardex');
            $this->registrar('kardex', $paraEmails, $mail->Subject, !empty($r['ok']), $r['ok'] ? 'ENVIADO' : ($r['error'] ?? 'fallo'));
            return $r;
        } catch (Exception $e) {
            error_log("Mailer::kardex: " . $e->getMessage());
            $this->registrar('kardex', $paraEmails ?? [], isset($mail) ? $mail->Subject : '(sin asunto)', false, substr($e->getMessage(), 0, 200));
            return ['ok' => false, 'error' => 'No se pudo enviar aviso de Kardex'];
        }
    }

    /**
     * RF 4.7: aviso de confirmación tras cambio de clave logueado (no bloqueante).
     * Adaptado a convenciones del equipo: Timeout, sendWithRetry y registrar().
     * Retorna [ok=>bool, error=>?string].
     */
    public function enviarAvisoCambioClave($paraEmail)
    {
        if (trim((string)$paraEmail) === '') {
            return ['ok' => false, 'error' => 'Sin destinatario'];
        }
        if (!$this->isConfigured()) {
            error_log("Mailer::avisoClave: SMTP sin configurar, se omite aviso a $paraEmail.");
            $this->registrar('clave', $paraEmail, '(sin asunto)', false, 'SMTP sin configurar (.env MAIL_*)');
            return ['ok' => false, 'error' => 'SMTP sin configurar.'];
        }
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = $this->cfg['host'];
            $mail->Port = (int)$this->cfg['port'];
            $mail->Timeout = 15;
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
            $mail->Subject = 'Tu contraseña fue actualizada — ÁCIDO Colombia';

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

            $safeEmail = htmlspecialchars($paraEmail, ENT_QUOTES, 'UTF-8');
            $fecha = date('d/m/Y H:i');
            $linkRecup = $this->getAppUrl() . '/index.php?action=forgot_password';
            $logoHtml = $logoCid
                ? '<img src="' . $logoCid . '" alt="ÁCIDO Colombia" width="150" style="display:block;border:0;max-width:150px;height:auto;">'
                : '<span style="font-size:26px;font-weight:bold;letter-spacing:4px;color:#ffffff;">ACIDO</span>';

            $mail->Body = '<!DOCTYPE html><html lang="es"><body style="margin:0;padding:0;background-color:#f4f5f7;">'
                . '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f5f7;padding:24px 12px;">'
                . '<tr><td align="center">'
                . '<table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background-color:#ffffff;border-radius:12px;overflow:hidden;">'
                // Encabezado marca
                . '<tr><td align="center" style="background-color:#1C3B4A;padding:32px 24px 24px;">' . $logoHtml
                . '<div style="margin-top:12px;font-size:12px;letter-spacing:3px;color:#D48A3A;font-weight:bold;">COLOMBIA</div></td></tr>'
                . '<tr><td style="background-color:#1cc88a;height:4px;line-height:4px;font-size:0;">&nbsp;</td></tr>'
                // Cuerpo
                . '<tr><td style="padding:32px 36px 8px;font-family:Arial,Helvetica,sans-serif;color:#2d3748;">'
                . '<h1 style="margin:0 0 12px;font-size:22px;color:#1C3B4A;">Contraseña actualizada</h1>'
                . '<p style="margin:0 0 12px;font-size:14px;line-height:22px;">Hola, <b>' . $safeEmail . '</b></p>'
                . '<p style="margin:0 0 20px;font-size:14px;line-height:22px;">Te confirmamos que la contraseña de tu cuenta de <b>ÁCIDO Colombia</b> fue cambiada el <b>' . $fecha . '</b>. Si fuiste tú, no necesitas hacer nada.</p>'
                // Botón CTA
                . '<table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr><td align="center" style="padding:8px 0 20px;">'
                . '<a href="' . htmlspecialchars($linkRecup, ENT_QUOTES, 'UTF-8') . '" style="display:inline-block;background-color:#e53e3e;color:#ffffff;font-family:Arial,Helvetica,sans-serif;font-size:15px;font-weight:bold;text-decoration:none;padding:14px 36px;border-radius:8px;">No fui yo, recuperar mi cuenta</a>'
                . '</td></tr></table>'
                . '<p style="margin:0;font-size:12px;line-height:20px;color:#718096;">Si no reconoces este cambio, usa el botón de inmediato: el enlace de recuperación vence en 15 minutos.</p>'
                . '</td></tr>'
                // Pie
                . '<tr><td align="center" style="background-color:#1C3B4A;padding:20px 24px;font-family:Arial,Helvetica,sans-serif;">'
                . '<div style="font-size:12px;color:#ffffff;font-weight:bold;letter-spacing:2px;">ACIDO COLOMBIA</div>'
                . '<div style="font-size:11px;color:#a0aec0;margin-top:6px;">Este es un mensaje automático, no lo respondas. &copy; ' . date('Y') . '</div>'
                . '</td></tr>'
                . '</table>'
                . '</td></tr></table>'
                . '</body></html>';
            $mail->AltBody = "Hola $paraEmail\n\nTu contraseña de ÁCIDO Colombia fue cambiada el $fecha. Si no fuiste tú, recupera tu cuenta aquí: $linkRecup";
            $r = $this->sendWithRetry($mail, 'avisoClave');
            $this->registrar('clave', $paraEmail, $mail->Subject, !empty($r['ok']), $r['ok'] ? 'ENVIADO' : ($r['error'] ?? 'fallo'));
            return $r;
        } catch (Exception $e) {
            error_log("Mailer::avisoCambioClave: " . $e->getMessage());
            $this->registrar('clave', $paraEmail ?? '', isset($mail) ? $mail->Subject : '(sin asunto)', false, substr($e->getMessage(), 0, 200));
            return ['ok' => false, 'error' => 'No se pudo enviar el aviso.'];
        }
    }
}
