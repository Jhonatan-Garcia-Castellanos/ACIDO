/* ============================================================
   migrate_rf1_auth.sql — RF 1.1 / 1.2 / 1.3 (Jhonatan Garcia)
   Registro completo + login con seudónimo + bloqueo 5 intentos
   EJECUTAR DESPUÉS de DB.sql v2.0 (MariaDB 10.4 / XAMPP)
   Pegar en phpMyAdmin > proyecto_acido > SQL > Continuar
   ============================================================ */
USE proyecto_acido;

-- 1. RF 1.1: nombres/apellidos max 70 caracteres (antes 50)
ALTER TABLE cliente
  MODIFY COLUMN Nombres VARCHAR(70) NOT NULL,
  MODIFY COLUMN Apellidos VARCHAR(70) NOT NULL;

-- 2. RF 1.1: documento unico (obligatorio desde la app; NULL para filas viejas)
ALTER TABLE cliente ADD COLUMN IF NOT EXISTS Documento VARCHAR(20) NULL UNIQUE AFTER Apellidos;

-- 3. RF 1.1: telefono ya existe (VARCHAR 15); se valida 7 o 10 digitos en PHP.
-- Sin cambio estructural.

-- 4. RF 1.2: seudonimo para login con correo/seudonimo
ALTER TABLE usuario ADD COLUMN IF NOT EXISTS Seudonimo VARCHAR(50) NULL UNIQUE AFTER Email;

-- 5. RF 1.3: garantizar fila control_accesos para usuarios viejos
-- (el trigger trg_registrar_acceso solo cubre INSERT nuevos)
INSERT IGNORE INTO control_accesos (ID_Usuario, Email, Intentos_Fallidos, Ultimo_Intento, Bloqueado_Hasta)
SELECT u.ID_Usuario, u.Email, 0, NOW(), NULL
FROM usuario u
WHERE u.deleted_at IS NULL
  AND u.ID_Usuario NOT IN (SELECT ID_Usuario FROM control_accesos);

-- 6. Verificacion
SELECT 'cliente_ok' AS chk, COLUMN_NAME, CHARACTER_MAXIMUM_LENGTH
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = 'proyecto_acido' AND TABLE_NAME = 'cliente'
  AND COLUMN_NAME IN ('Nombres', 'Apellidos', 'Documento', 'Telefono');
SELECT 'usuario_ok' AS chk, COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = 'proyecto_acido' AND TABLE_NAME = 'usuario'
  AND COLUMN_NAME IN ('Email', 'Seudonimo');
SELECT COUNT(*) AS Total_Control_Accesos FROM control_accesos;
