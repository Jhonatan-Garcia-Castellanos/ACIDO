/* ============================================================
   migrate_stock_minimo.sql — PROYECTO ACIDO (sobre DBv2.sql)
   Compatible: MariaDB 10.4 / MySQL 5.7 (XAMPP).
   Ejecutar UNA vez en phpMyAdmin (NO re-ejecutar DBv2.sql,
   ese hace DROP DATABASE y borraría datos).

   Qué hace (RF 2.3 stock bajo por producto):
   1. producto.Stock_Minimo INT DEFAULT 10 (umbral por producto,
      reemplaza el 10 hardcodeado en PHP/triggers).
   2. alertas_sistema.ID_Producto NULL + FK + índice de no-leídas
      (para saber QUÉ producto está bajo y no duplicar).
   3. Reemplaza trg_alerta_stock_minimo: compara contra
      NEW.Stock_Minimo (no 10 fijo), cubre INSERT y UPDATE,
      y no repite alerta si ya hay una no-leída del mismo
      producto en las últimas 24h.
   ============================================================ */

-- 1. Umbral por producto (idempotente a mano: si ya existe, phpMyAdmin
--    marcará error 1060 y puedes seguir al paso 2).
ALTER TABLE producto ADD COLUMN Stock_Minimo INT NOT NULL DEFAULT 10;
UPDATE producto SET Stock_Minimo = 10 WHERE Stock_Minimo IS NULL OR Stock_Minimo <= 0;

-- 2. Enlazar alerta con producto + índice para la campana.
ALTER TABLE alertas_sistema ADD COLUMN ID_Producto INT NULL;
ALTER TABLE alertas_sistema ADD CONSTRAINT fk_alerta_producto
  FOREIGN KEY (ID_Producto) REFERENCES producto(ID_Producto);
CREATE INDEX idx_alerta_noleida ON alertas_sistema(Leido, Tipo, Fecha_Creacion);
CREATE INDEX idx_alerta_producto ON alertas_sistema(ID_Producto);

-- 3. Trigger UPDATE: cruza su propio Stock_Minimo, con antispam 24h.
DROP TRIGGER IF EXISTS trg_alerta_stock_minimo;
DELIMITER $$
CREATE TRIGGER trg_alerta_stock_minimo AFTER UPDATE ON producto FOR EACH ROW BEGIN
    IF NEW.Stock_Actual <= NEW.Stock_Minimo AND OLD.Stock_Actual > OLD.Stock_Minimo THEN
        IF NOT EXISTS (SELECT 1 FROM alertas_sistema
                       WHERE ID_Producto = NEW.ID_Producto
                         AND Tipo = 'STOCK_BAJO' AND Leido = 0
                         AND Fecha_Creacion > NOW() - INTERVAL 24 HOUR) THEN
            INSERT INTO alertas_sistema (Tipo, Mensaje, ID_Producto)
            VALUES ('STOCK_BAJO',
                    CONCAT('Stock bajo: ', NEW.Nombre_Producto,
                           ' (', NEW.Stock_Actual, '/', NEW.Stock_Minimo, ')'),
                    NEW.ID_Producto);
        END IF;
    END IF;
END$$
DELIMITER ;

-- 4. Trigger INSERT: producto creado ya bajo su mínimo también avisa.
DROP TRIGGER IF EXISTS trg_alerta_stock_minimo_insert;
DELIMITER $$
CREATE TRIGGER trg_alerta_stock_minimo_insert AFTER INSERT ON producto FOR EACH ROW BEGIN
    IF NEW.Stock_Actual <= NEW.Stock_Minimo THEN
        INSERT INTO alertas_sistema (Tipo, Mensaje, ID_Producto)
        VALUES ('STOCK_BAJO',
                CONCAT('Stock bajo: ', NEW.Nombre_Producto,
                       ' (', NEW.Stock_Actual, '/', NEW.Stock_Minimo, ')'),
                NEW.ID_Producto);
    END IF;
END$$
DELIMITER ;

-- Verificación rápida
SELECT 'mig_stock_minimo_ok' AS chk,
  (SELECT COUNT(*) FROM producto WHERE Stock_Actual <= Stock_Minimo AND deleted_at IS NULL) AS en_bajo,
  (SELECT COUNT(*) FROM alertas_sistema WHERE Tipo = 'STOCK_BAJO' AND Leido = 0) AS alertas_pendientes;
