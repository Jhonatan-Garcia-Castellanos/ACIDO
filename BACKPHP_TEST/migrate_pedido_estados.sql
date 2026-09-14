/* ============================================================
   migrate_pedido_estados.sql — PROYECTO ACIDO (sobre DBv2.sql)
   Compatible: MariaDB 10.4 / MySQL 5.7 (XAMPP).
   Ejecutar UNA vez en phpMyAdmin o vía mysql CLI sobre proyecto_acido.

   RF 2.8 (reserva al confirmar "Pagado") + RF 2.10 (bloqueo si
   supera stock) + base del ciclo RF 5.10 de Ventas.

   Mapa de estados (flujo obligatorio):
     Pendiente -> Pagado -> Preparando -> En camino -> Entregado
        |            |           |             |
        +--> Cancelado (solo desde Pendiente/Pagado/Preparando)

   Equivalencias con el documento (no se renombran los estados del
   equipo para no romper vistas/JS existentes):
     'Preparando'  = En preparación = "Listo para envío" (RF 2.10)
     'En camino'   = Enviado (RF 5.10)

   Cambios:
   1. ENUM + DEFAULT 'Pendiente' (filas existentes intactas).
   2. trg_secuencia_estados_pedido: matriz completa + Cancelado terminal.
   3. trg_devolver_stock_cancelacion: solo devuelve si hubo descuento
      (OLD en Pagado/Preparando/En camino). Cancelar desde Pendiente
      NO suma stock (aún no se descontó nada).
   4. trg_bloquear_pagado_sin_stock (NUEVO, RF 2.10): impide confirmar
      'Pagado' o 'Preparando' si algún ítem supera el stock actual.
      Debe correrse ANTES del descuento (el checkout confirma Pagado
      antes de insertar el pago) para comparar contra stock real.
   5. v_envios_pendientes incluye Pendiente/Pagado.
   ============================================================ */

-- 1. Nuevos estados (valores existentes: subconjunto, no se pierde nada)
ALTER TABLE pedido
  MODIFY COLUMN Estado_Pedido
  ENUM('Pendiente','Pagado','Preparando','En camino','Entregado','Cancelado')
  NOT NULL DEFAULT 'Pendiente';

-- 2. Secuencia obligatoria (reemplaza la versión DBv2)
DROP TRIGGER IF EXISTS trg_secuencia_estados_pedido;
DELIMITER $$
CREATE TRIGGER trg_secuencia_estados_pedido BEFORE UPDATE ON pedido FOR EACH ROW BEGIN
    IF OLD.Estado_Pedido = 'Pendiente' AND NEW.Estado_Pedido NOT IN ('Pagado', 'Cancelado') THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Desde Pendiente solo a Pagado o Cancelado.';
    ELSEIF OLD.Estado_Pedido = 'Pagado' AND NEW.Estado_Pedido NOT IN ('Preparando', 'Cancelado') THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Desde Pagado solo a Preparando o Cancelado.';
    ELSEIF OLD.Estado_Pedido = 'Preparando' AND NEW.Estado_Pedido NOT IN ('En camino', 'Cancelado') THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Estado no permitido desde Preparando.';
    ELSEIF OLD.Estado_Pedido = 'En camino' AND NEW.Estado_Pedido NOT IN ('Entregado', 'Cancelado') THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Desde En camino solo a Entregado o Cancelado.';
    ELSEIF OLD.Estado_Pedido = 'Cancelado' AND NEW.Estado_Pedido != 'Cancelado' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Pedido cancelado es terminal.';
    END IF;
END$$
DELIMITER ;

-- 3. Devolución solo si el stock ya se había descontado (vía pago)
DROP TRIGGER IF EXISTS trg_devolver_stock_cancelacion;
DELIMITER $$
CREATE TRIGGER trg_devolver_stock_cancelacion AFTER UPDATE ON pedido FOR EACH ROW BEGIN
    IF NEW.Estado_Pedido = 'Cancelado' AND OLD.Estado_Pedido != 'Cancelado'
       AND OLD.Estado_Pedido IN ('Pagado', 'Preparando', 'En camino') THEN
        UPDATE producto p
        JOIN detalle_venta dv ON p.ID_Producto = dv.ID_Producto
        SET p.Stock_Actual = p.Stock_Actual + dv.Cantidad
        WHERE dv.ID_Venta = NEW.ID_Venta;
    END IF;
END$$
DELIMITER ;

-- 4. RF 2.10: bloqueo de Pagado / Preparando (= Listo para envío) sin stock
DROP TRIGGER IF EXISTS trg_bloquear_pagado_sin_stock;
DELIMITER $$
CREATE TRIGGER trg_bloquear_pagado_sin_stock BEFORE UPDATE ON pedido FOR EACH ROW BEGIN
    IF NEW.Estado_Pedido IN ('Pagado', 'Preparando') AND OLD.Estado_Pedido != NEW.Estado_Pedido THEN
        IF EXISTS (
            SELECT 1 FROM detalle_venta dv
            JOIN producto p ON p.ID_Producto = dv.ID_Producto
            WHERE dv.ID_Venta = NEW.ID_Venta AND dv.Cantidad > p.Stock_Actual
        ) THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Stock insuficiente para confirmar el pedido (RF 2.10).';
        END IF;
    END IF;
END$$
DELIMITER ;

-- 5. Pendientes incluye los nuevos estados previos al envío
CREATE OR REPLACE VIEW v_envios_pendientes AS
  SELECT * FROM pedido WHERE Estado_Pedido IN ('Pendiente', 'Pagado', 'Preparando', 'En camino');

-- Verificación rápida
SELECT 'mig_pedido_ok' AS chk,
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'pedido' AND COLUMN_NAME = 'Estado_Pedido'
      AND COLUMN_TYPE LIKE '%Pagado%') AS enum_ok,
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.TRIGGERS
    WHERE TRIGGER_SCHEMA = DATABASE() AND TRIGGER_NAME = 'trg_bloquear_pagado_sin_stock') AS guard_ok;
