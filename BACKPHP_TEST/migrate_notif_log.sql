/* ============================================================
   migrate_notif_log.sql — PROYECTO ACIDO (sobre DBv2.sql)
   Compatible: MariaDB 10.4 / MySQL 5.7 (XAMPP).
   Ejecutar UNA vez en phpMyAdmin sobre proyecto_acido.

   Historial auditable de correos al admin (stock bajo + Kardex):
   cada intento de envío (ok o fallo) queda registrado con fecha,
   destinatarios, asunto y detalle. Así se sabe desde la app si
   la notificación salió o falló (y por qué).
   Si NO se ejecuta, la app sigue funcionando: el registro se
   omite en silencio (try/catch en Mailer).
   ============================================================ */

CREATE TABLE IF NOT EXISTS notificacion_log (
  ID_Log INT AUTO_INCREMENT PRIMARY KEY,
  Fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  Tipo VARCHAR(20) NOT NULL COMMENT 'stock|kardex|resumen',
  Destinatarios VARCHAR(500) NOT NULL,
  Asunto VARCHAR(255) NOT NULL,
  Resultado ENUM('ok','fallo') NOT NULL DEFAULT 'fallo',
  Detalle VARCHAR(500) NULL,
  INDEX idx_notif_fecha (Fecha),
  INDEX idx_notif_tipo (Tipo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SELECT 'mig_notif_log_ok' AS chk,
  (SELECT COUNT(*) FROM notificacion_log) AS registros;
