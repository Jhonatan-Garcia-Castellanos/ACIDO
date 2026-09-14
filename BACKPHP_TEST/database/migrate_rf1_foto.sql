/* ============================================================
   migrate_rf1_foto.sql — Foto de perfil (Jhonatan Garcia)
   EJECUTAR DESPUÉS de migrate_rf1_auth.sql (MariaDB 10.4 / XAMPP)
   Pegar en phpMyAdmin > proyecto_acido > SQL > Continuar
   ============================================================ */
USE proyecto_acido;

-- Foto de perfil: ruta web pública (NULL = inicial del nombre)
ALTER TABLE usuario ADD COLUMN IF NOT EXISTS Foto VARCHAR(512) NULL AFTER Seudonimo;

-- Verificación
SELECT COLUMN_NAME, CHARACTER_MAXIMUM_LENGTH FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = 'proyecto_acido' AND TABLE_NAME = 'usuario'
  AND COLUMN_NAME IN ('Email', 'Seudonimo', 'Foto');
