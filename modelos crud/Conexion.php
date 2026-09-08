<?php
class Conexion {
    private static $host = 'localhost';
    private static $db   = 'base_datos_acido'; // El nombre exacto de tu base de datos en phpMyAdmin
    private static $user = 'root';
    private static$pass = '';
    private static $charset = 'utf8mb4';

    public static function conectar() {
        $dsn = "mysql:host=" . self::$host . ";dbname=" . self::$db . ";charset=" . self::$charset;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            return new PDO($dsn, self::$user, self::$pass, $options);
        } catch (\PDOException $e) {
            throw new \PDOException($e->getMessage(), (int)$e->getCode());
        }
    }
}