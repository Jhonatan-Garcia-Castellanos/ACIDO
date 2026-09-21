<?php
// config/conexion.php — Lee credenciales del .env (ver .env.example).
require_once __DIR__ . "/../lib/Env.php";
Env::load(dirname(__DIR__));

class Conexion {
    public $conn;

    public function __construct() {
        $charset = Env::get("DB_CHARSET", "utf8mb4");

        // Configuración principal desde .env
        $primaria = [
            "host" => Env::get("DB_HOST", "localhost"),
            "port" => (int)Env::get("DB_PORT", 3306),
            "user" => Env::get("DB_USER", "root"),
            "pass" => Env::get("DB_PASS", ""),
        ];

        // Fallbacks automáticos: cubre compañeros con MariaDB sin contraseña,
        // en puerto alternativo (3307 = MariaDB XAMPP) o en 3306 (MySQL/MariaDB default).
        $alternativas = [
            ["host" => "localhost", "port" => 3307, "user" => "root", "pass" => ""],
            ["host" => "127.0.0.1", "port" => 3307, "user" => "root", "pass" => ""],
            ["host" => "localhost", "port" => 3306, "user" => "root", "pass" => ""],
            ["host" => "127.0.0.1", "port" => 3306, "user" => "root", "pass" => ""],
        ];

        $candidatos = [];
        foreach (array_merge([$primaria], $alternativas) as $c) {
            if (!in_array($c, $candidatos, true)) $candidatos[] = $c;
        }

        $ultimoError = null;
        foreach ($candidatos as $cfg) {
            $dsn = "mysql:host={$cfg['host']};port={$cfg['port']};dbname=" . Env::get("DB_NAME", "proyecto_acido") . ";charset={$charset}";
            try {
                $cn = new PDO($dsn, $cfg['user'], $cfg['pass'], [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                    PDO::ATTR_TIMEOUT            => 4,
                ]);
                $this->conn = $cn;
                return; // primera conexión válida
            } catch (PDOException $e) {
                $ultimoError = $e->getMessage();
            }
        }
        error_log("Conexion (todas las alternativas): " . $ultimoError);
        die("Error en la conexión. Intenta más tarde.");
    }
}
