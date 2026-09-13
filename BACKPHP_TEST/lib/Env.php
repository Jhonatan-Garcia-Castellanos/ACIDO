<?php
// lib/Env.php — Cargador mínimo de .env SIN composer.
// Formato: CLAVE=valor (una por línea). Acepta comillas simples/dobles
// e ignora líneas vacías y comentarios con #.
// Uso: Env::load(__DIR__ . '/..'); $v = Env::get('CLAVE', 'defecto');
class Env
{
    private static $loaded = false;
    private static $vars = [];

    public static function load($dir)
    {
        if (self::$loaded) return;
        self::$loaded = true;
        $file = rtrim($dir, "/\\") . DIRECTORY_SEPARATOR . '.env';
        if (!is_readable($file)) return;
        $lines = @file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (!is_array($lines)) return;
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || $line[0] === '#' || strpos($line, '=') === false) continue;
            list($k, $v) = explode('=', $line, 2);
            $k = trim($k);
            $v = trim($v);
            if ($k === '' || !preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $k)) continue;
            $len = strlen($v);
            if ($len >= 2 && (($v[0] === '"' && $v[$len - 1] === '"') || ($v[0] === "'" && $v[$len - 1] === "'"))) {
                $v = substr($v, 1, -1);
            }
            self::$vars[$k] = $v;
            if (!array_key_exists($k, $_ENV)) $_ENV[$k] = $v;
            if (getenv($k) === false) @putenv($k . '=' . $v);
        }
    }

    public static function get($key, $default = null)
    {
        if (array_key_exists($key, self::$vars)) return self::$vars[$key];
        $v = getenv($key);
        if ($v !== false) return $v;
        if (array_key_exists($key, $_ENV)) return $_ENV[$key];
        return $default;
    }
}
