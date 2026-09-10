<?php
// config/mail.php — Credenciales SMTP (Mailtrap Email Testing).
// 1. Crea cuenta en https://mailtrap.io → Email Testing → Inboxes → SMTP Settings → PHPMailer.
// 2. Copia Host/Port/Username/Password aquí. NO subir este archivo a git con datos reales.
// 3. MAIL_FROM debe ser un remitente de tu dominio/app (en Mailtrap da igual).
return [
    'host'       => 'sandbox.smtp.mailtrap.io', // ej. sandbox.smtp.mailtrap.io
    'port'       => 2525,                        // Mailtrap: 25, 465, 587 o 2525
    'username'   => 'ecc3c3f9295414',      // ← reemplazar
    'password'   => 'fdd40496fb440b',      // ← reemplazar
    'encryption' => 'tls',                       // 'tls' para 587/2525, 'ssl' para 465, '' para 25
    'from_email' => 'no-reply@acido.local',
    'from_name'  => 'ACIDO Colombia',
    'app_url'    => 'http://localhost/ACIDO/BACKPHP_TEST', // base para armar el link de reset
];
