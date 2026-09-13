<?php
// lib/Csrf.php - Token anti-CSRF por sesion (sin composer).
// Uso en vistas (dentro del form): echo Csrf::field();
// Uso en index.php (en cada rama POST): checkCsrf();
class Csrf
{
    public static function token()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function field()
    {
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(self::token(), ENT_QUOTES, 'UTF-8') . '">';
    }

    public static function validate($token)
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['csrf_token']) || !is_string($token) || $token === '') return false;
        return hash_equals($_SESSION['csrf_token'], $token);
    }
}
