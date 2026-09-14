/* ============================================================
   migrate_control_accesos.sql
   Migra datos de control_accesos ANTIGUA (PK Email) a la NUEVA
   estructura (FK ID_Usuario + Email).
   EJECUTAR DESPUÉS de cargar DB.sql v2.0.
   ============================================================ */
USE proyecto_acido;

START TRANSACTION;

-- 1. Backfill: registros existentes de usuario sin fila en control_accesos
INSERT IGNORE INTO control_accesos (ID_Usuario, Email, Intentos_Fallidos, Ultimo_Intento, Bloqueado_Hasta)
SELECT u.ID_Usuario,
       u.Email,
       0,
       NOW(),
       NULL
FROM usuario u
WHERE u.deleted_at IS NULL
  AND u.ID_Usuario NOT IN (SELECT ID_Usuario FROM control_accesos);

-- 2. Verificación (debe coincidir con SELECT COUNT(*) FROM usuario WHERE deleted_at IS NULL)
SELECT COUNT(*) AS Total_Control_Accesos FROM control_accesos;

-- 3. Ejemplos de vistas actualizadas
SELECT * FROM v_usuarios_bloqueados LIMIT 5;
SELECT * FROM v_intentos_fallidos LIMIT 5;
SELECT * FROM v_sesiones_activas LIMIT 5;

COMMIT;