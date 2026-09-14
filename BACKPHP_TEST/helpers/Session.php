<?php
// helpers/Session.php — Cierre de sesión por inactividad (lista 1.3).
// Timeout real: 900s (15 min). Para demo del revisor: SESSION_TIMEOUT=60 en .env.
// Uso en index.php (front-controller):
//   require_once "helpers/Session.php";
//   Session::start();
//   Session::checkOrKill(); // mata y redirige/responde JSON si expiró
class Session
{
    const DEFAULT_TIMEOUT = 900; // 15 minutos exigidos por la lista

    public static function timeout()
    {
        $v = null;
        if (class_exists('Env')) {
            $v = Env::get('SESSION_TIMEOUT', null);
        }
        if ($v === null || $v === '') {
            $g = getenv('SESSION_TIMEOUT');
            if ($g !== false && $g !== '') $v = $g;
        }
        if (isset($_ENV['SESSION_TIMEOUT']) && $v === null) {
            $v = $_ENV['SESSION_TIMEOUT'];
        }
        $t = (int)($v ?? self::DEFAULT_TIMEOUT);
        if ($t < 30) $t = 30; // piso de seguridad (evita cierres fantasma)
        if ($t > 3600) $t = 3600;
        return $t;
    }

    public static function start()
    {
        if (session_status() !== PHP_SESSION_NONE) return;
        // Cookie httponly + SameSite Lax sin romper path/domain de XAMPP
        if (!headers_sent() && PHP_VERSION_ID >= 70300) {
            $p = session_get_cookie_params();
            session_set_cookie_params([
                'lifetime' => 0, // cookie de sesión (muere al cerrar navegador)
                'path' => ($p['path'] !== '' ? $p['path'] : '/'),
                'domain' => $p['domain'],
                'secure' => !empty($p['secure']),
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
        }
        session_start();
    }

    public static function touch()
    {
        $_SESSION['last_activity'] = time();
    }

    public static function isExpired()
    {
        if (!isset($_SESSION['user'])) return false;
        if (!isset($_SESSION['last_activity'])) {
            // Sesión vieja sin marca: se marca ahora y se deja pasar una vez
            self::touch();
            return false;
        }
        return (time() - (int)$_SESSION['last_activity']) > self::timeout();
    }

    /** Destruye sesión + cookie + token CSRF. Reusa la lógica del logout manual. */
    public static function kill()
    {
        $_SESSION = [];
        if (ini_get("session.use_cookies") && !headers_sent()) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]);
        }
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }

    public static function isAjax()
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    /**
     * Verifica inactividad. Si expiró: mata y responde (JSON si AJAX, redirect si no).
     * Retorna true si expiró (el llamador ya no debe seguir), false si todo ok.
     * Además hace touch() en cada request válido para renovar los 15 min.
     */
    public static function checkOrKill()
    {
        if (!isset($_SESSION['user'])) return false;
        if (!self::isExpired()) {
            self::touch();
            return false;
        }
        self::kill();
        if (self::isAjax()) {
            while (ob_get_level()) { ob_end_clean(); }
            if (!headers_sent()) header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'expired' => true,
                'message' => 'Sesión cerrada por inactividad (15 minutos). Inicia sesión de nuevo.',
            ]);
            exit();
        }
        $back = 'index.php?action=login&error=' . urlencode('Sesión cerrada por inactividad (15 minutos). Inicia sesión de nuevo.');
        if (!headers_sent()) {
            header("Location: " . $back);
            exit();
        }
        echo '<script>window.location.href=' . json_encode($back) . ';</script>';
        exit();
    }
}
