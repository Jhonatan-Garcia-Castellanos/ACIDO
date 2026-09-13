<?php
// config/mail.php — Lee credenciales SMTP del .env (ver .env.example).
// 1. Copia .env.example como .env y completa tus datos de Mailtrap
//    (https://mailtrap.io → Email Testing → Inboxes → SMTP Settings → PHPMailer).
// 2. Este archivo YA NO contiene secretos: no hay nada que ocultar en git.
// 3. Sin .env configurado, Mailer::isConfigured() es false y la app
//    muestra el link de recuperación en pantalla con ?debug=1.
require_once __DIR__ . "/../lib/Env.php";
Env::load(dirname(__DIR__));

return [
    'host'       => Env::get('MAIL_HOST', ''),
    'port'       => (int)Env::get('MAIL_PORT', 2525),
    'username'   => Env::get('MAIL_USERNAME', ''),
    'password'   => Env::get('MAIL_PASSWORD', ''),
    'encryption' => Env::get('MAIL_ENCRYPTION', 'tls'),
    'from_email' => Env::get('MAIL_FROM', 'no-reply@acido.local'),
    'from_name'  => Env::get('MAIL_FROM_NAME', 'ACIDO Colombia'),
    'app_url'    => rtrim(Env::get('APP_URL', 'http://localhost/ACIDO/BACKPHP_TEST'), '/'),
];
