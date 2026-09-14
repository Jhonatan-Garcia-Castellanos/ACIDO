/* ============================================================
   migrate_pago_detalle.sql — PROYECTO ACIDO (sobre DBv2.sql)
   Compatible: MariaDB 10.4 / MySQL 5.7 (XAMPP).
   Ejecutar UNA vez en phpMyAdmin o vía mysql CLI sobre proyecto_acido.

   Contexto: el módulo Ventas (Johan) fusionado en main guarda el
   detalle del pago (banco/entidad + referencia enmascarada) y logos
   por método, pero estas columnas no existen en DBv2 → el checkout
   falla (1054) y el listado de ventas sale vacío. Esta migración
   alinea BD con código. Idempotente a mano: si una columna ya
   existe, phpMyAdmin marcará error 1060 y puedes seguir.

   NOTA: Logo SOLO en Nequi/Daviplata/Transferencia (piden cuenta).
   Efectivo y Contra entrega van sin logo (no piden número).
   ============================================================ */

ALTER TABLE pago ADD COLUMN Entidad_Bancaria VARCHAR(100) NULL;
ALTER TABLE pago ADD COLUMN Numero_Referencia VARCHAR(100) NULL;
ALTER TABLE metodo_pago ADD COLUMN Logo VARCHAR(512) NULL;

UPDATE metodo_pago SET Logo = '/ACIDO/BACKPHP_TEST/public/img/pagos/nequi.svg'
  WHERE Tipo_Metodo = 'Nequi' AND (Logo IS NULL OR Logo = '');
UPDATE metodo_pago SET Logo = '/ACIDO/BACKPHP_TEST/public/img/pagos/daviplata.svg'
  WHERE Tipo_Metodo = 'Daviplata' AND (Logo IS NULL OR Logo = '');
UPDATE metodo_pago SET Logo = '/ACIDO/BACKPHP_TEST/public/img/pagos/transferencia.svg'
  WHERE Tipo_Metodo = 'Transferencia bancaria' AND (Logo IS NULL OR Logo = '');

-- Verificación rápida
SELECT 'mig_pago_ok' AS chk,
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'pago'
      AND COLUMN_NAME IN ('Entidad_Bancaria','Numero_Referencia')) AS pago_cols,
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'metodo_pago'
      AND COLUMN_NAME = 'Logo') AS logo_ok;
