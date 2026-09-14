/* ============================================================
   DB.sql v2.0 — PROYECTO ACIDO
   Mejoras: collation, control_accesos FK, soft deletes,
   índices compuestos, particionado, vistas actualizadas
   ============================================================ */
DROP DATABASE IF EXISTS proyecto_acido;
CREATE DATABASE proyecto_acido DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
USE proyecto_acido;

-- ==============================================================================
-- 1. TABLAS BASE Y ESTRUCTURA RELACIONAL (3NF)
-- ==============================================================================

CREATE TABLE departamento (
  ID_Departamento INT AUTO_INCREMENT PRIMARY KEY,
  Nombre_Departamento VARCHAR(100) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE ciudad (
  ID_Ciudad INT AUTO_INCREMENT PRIMARY KEY,
  Nombre_Ciudad VARCHAR(100) NOT NULL,
  ID_Departamento INT NOT NULL,
  FOREIGN KEY (ID_Departamento) REFERENCES departamento(ID_Departamento)
) ENGINE=InnoDB;

CREATE TABLE cargo (
  ID_Cargo INT AUTO_INCREMENT PRIMARY KEY,
  Nombre_Cargo VARCHAR(50) NOT NULL,
  Salario_Base DECIMAL(10,2) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE empleado (
  ID_Empleado INT AUTO_INCREMENT PRIMARY KEY,
  Nombres VARCHAR(50) NOT NULL,
  Apellidos VARCHAR(50) NOT NULL,
  ID_Cargo INT NOT NULL,
  ID_Ciudad INT NOT NULL,
  deleted_at DATETIME NULL DEFAULT NULL,
  FOREIGN KEY (ID_Cargo) REFERENCES cargo(ID_Cargo),
  FOREIGN KEY (ID_Ciudad) REFERENCES ciudad(ID_Ciudad)
) ENGINE=InnoDB;

CREATE TABLE cliente (
  ID_Cliente INT AUTO_INCREMENT PRIMARY KEY,
  Nombres VARCHAR(50) NOT NULL,
  Apellidos VARCHAR(50) NOT NULL,
  Telefono VARCHAR(15) NULL,
  deleted_at DATETIME NULL DEFAULT NULL
) ENGINE=InnoDB;

CREATE TABLE usuario (
  ID_Usuario INT AUTO_INCREMENT PRIMARY KEY,
  Email VARCHAR(100) NOT NULL UNIQUE,
  Password_Hash VARCHAR(255) NOT NULL,
  Rol ENUM('Cliente', 'Empleado', 'Administrador') NOT NULL DEFAULT 'Cliente',
  ID_Empleado INT NULL UNIQUE,
  ID_Cliente INT NULL UNIQUE,
  deleted_at DATETIME NULL DEFAULT NULL,
  FOREIGN KEY (ID_Empleado) REFERENCES empleado(ID_Empleado) ON DELETE CASCADE,
  FOREIGN KEY (ID_Cliente) REFERENCES cliente(ID_Cliente) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE control_accesos (
  ID_Control INT AUTO_INCREMENT PRIMARY KEY,
  ID_Usuario INT NOT NULL UNIQUE,
  Email VARCHAR(100) NOT NULL UNIQUE,
  Intentos_Fallidos INT DEFAULT 0,
  Ultimo_Intento DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  Bloqueado_Hasta DATETIME NULL,
  FOREIGN KEY (ID_Usuario) REFERENCES usuario(ID_Usuario) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE libreta_direcciones (
  ID_Direccion INT AUTO_INCREMENT PRIMARY KEY,
  ID_Cliente INT NOT NULL,
  Alias VARCHAR(50) NOT NULL,
  Ciudad_Id INT NOT NULL,
  Codigo_Postal VARCHAR(10) NULL,
  Direccion_Exacta VARCHAR(255) NOT NULL,
  Referencias VARCHAR(255) NULL,
  Es_Principal TINYINT(1) DEFAULT 0,
  FOREIGN KEY (ID_Cliente) REFERENCES cliente(ID_Cliente) ON DELETE CASCADE,
  FOREIGN KEY (Ciudad_Id) REFERENCES ciudad(ID_Ciudad)
) ENGINE=InnoDB;

CREATE TABLE categoria (
  ID_Categoria INT AUTO_INCREMENT PRIMARY KEY,
  Nombre_Categoria VARCHAR(50) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE proveedor (
  ID_Proveedor INT AUTO_INCREMENT PRIMARY KEY,
  Nombre_Empresa VARCHAR(100) NOT NULL,
  ID_Ciudad INT NOT NULL,
  FOREIGN KEY (ID_Ciudad) REFERENCES ciudad(ID_Ciudad)
) ENGINE=InnoDB;

CREATE TABLE producto (
  ID_Producto INT AUTO_INCREMENT PRIMARY KEY,
  Nombre_Producto VARCHAR(100) NOT NULL,
  Precio_Actual DECIMAL(10,2) NOT NULL,
  Stock_Actual INT NOT NULL DEFAULT 0,
  ID_Categoria INT NOT NULL,
  ID_Proveedor INT NOT NULL,
  Imagen_URL VARCHAR(512) NULL,
  QR_Code_URL VARCHAR(512) NULL,
  deleted_at DATETIME NULL DEFAULT NULL,
  FOREIGN KEY (ID_Categoria) REFERENCES categoria(ID_Categoria),
  FOREIGN KEY (ID_Proveedor) REFERENCES proveedor(ID_Proveedor)
) ENGINE=InnoDB;

CREATE TABLE auditoria_precios (
  ID_Auditoria INT AUTO_INCREMENT PRIMARY KEY,
  ID_Producto INT NOT NULL,
  Precio_Anterior DECIMAL(10,2) NOT NULL,
  Precio_Nuevo DECIMAL(10,2) NOT NULL,
  Fecha_Cambio DATETIME DEFAULT CURRENT_TIMESTAMP,
  Usuario_Responsable VARCHAR(100) DEFAULT 'Sistema',
  FOREIGN KEY (ID_Producto) REFERENCES producto(ID_Producto)
) ENGINE=InnoDB;

CREATE TABLE movimiento_inventario (
  ID_Movimiento INT AUTO_INCREMENT PRIMARY KEY,
  ID_Producto INT NOT NULL,
  Tipo_Movimiento ENUM('Entrada','Salida','Ajuste') NOT NULL,
  Cantidad INT NOT NULL,
  Motivo VARCHAR(255) NOT NULL,
  Fecha_Movimiento DATETIME DEFAULT CURRENT_TIMESTAMP,
  ID_Empleado INT NOT NULL,
  FOREIGN KEY (ID_Producto) REFERENCES producto(ID_Producto),
  FOREIGN KEY (ID_Empleado) REFERENCES empleado(ID_Empleado)
) ENGINE=InnoDB;

CREATE TABLE lista_espera_stock (
  ID_Espera INT AUTO_INCREMENT PRIMARY KEY,
  Nombre_Completo VARCHAR(150) NOT NULL,
  Email VARCHAR(100) NOT NULL,
  ID_Producto INT NOT NULL,
  Fecha_Registro DATETIME DEFAULT CURRENT_TIMESTAMP,
  Notificado TINYINT(1) DEFAULT 0,
  FOREIGN KEY (ID_Producto) REFERENCES producto(ID_Producto)
) ENGINE=InnoDB;

CREATE TABLE lista_deseos (
  ID_Wishlist INT AUTO_INCREMENT PRIMARY KEY,
  ID_Cliente INT NOT NULL,
  ID_Producto INT NOT NULL,
  Fecha_Agregado DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (ID_Cliente) REFERENCES cliente(ID_Cliente) ON DELETE CASCADE,
  FOREIGN KEY (ID_Producto) REFERENCES producto(ID_Producto) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE resena_valoracion (
  ID_Resena INT AUTO_INCREMENT PRIMARY KEY,
  ID_Cliente INT NOT NULL,
  ID_Producto INT NOT NULL,
  Calificacion INT NOT NULL CHECK (Calificacion BETWEEN 1 AND 5),
  Comentario TEXT NULL,
  Fecha_Publicacion DATETIME DEFAULT CURRENT_TIMESTAMP,
  Estado_Moderacion ENUM('Pendiente','Aprobado','Rechazado') DEFAULT 'Pendiente',
  UNIQUE KEY uk_cliente_producto (ID_Cliente, ID_Producto),
  FOREIGN KEY (ID_Cliente) REFERENCES cliente(ID_Cliente),
  FOREIGN KEY (ID_Producto) REFERENCES producto(ID_Producto)
) ENGINE=InnoDB;

CREATE TABLE venta (
  ID_Venta INT AUTO_INCREMENT PRIMARY KEY,
  Fecha_Venta DATETIME DEFAULT CURRENT_TIMESTAMP,
  ID_Cliente INT NOT NULL,
  ID_Empleado INT NULL,
  FOREIGN KEY (ID_Cliente) REFERENCES cliente(ID_Cliente),
  FOREIGN KEY (ID_Empleado) REFERENCES empleado(ID_Empleado)
) ENGINE=InnoDB;

CREATE TABLE detalle_venta (
  ID_Detalle INT AUTO_INCREMENT PRIMARY KEY,
  ID_Venta INT NOT NULL,
  ID_Producto INT NOT NULL,
  Cantidad INT NOT NULL CHECK (Cantidad <= 10),
  Precio_Venta_Historico DECIMAL(10,2) NOT NULL,
  FOREIGN KEY (ID_Venta) REFERENCES venta(ID_Venta) ON DELETE CASCADE,
  FOREIGN KEY (ID_Producto) REFERENCES producto(ID_Producto)
) ENGINE=InnoDB;

CREATE TABLE metodo_pago (
  ID_Metodo INT AUTO_INCREMENT PRIMARY KEY,
  Tipo_Metodo VARCHAR(50) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE pago (
  ID_Pago INT AUTO_INCREMENT PRIMARY KEY,
  ID_Venta INT NOT NULL,
  ID_Metodo INT NOT NULL,
  Monto_Pagado DECIMAL(10,2) NOT NULL,
  Fecha_Pago DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (ID_Venta) REFERENCES venta(ID_Venta),
  FOREIGN KEY (ID_Metodo) REFERENCES metodo_pago(ID_Metodo)
) ENGINE=InnoDB;

CREATE TABLE factura (
  ID_Factura INT AUTO_INCREMENT PRIMARY KEY,
  Numero_Factura VARCHAR(20) NOT NULL UNIQUE,
  Fecha_Emision DATETIME DEFAULT CURRENT_TIMESTAMP,
  ID_Venta INT NOT NULL UNIQUE,
  FOREIGN KEY (ID_Venta) REFERENCES venta(ID_Venta)
) ENGINE=InnoDB;

CREATE TABLE pedido (
  ID_Pedido INT AUTO_INCREMENT PRIMARY KEY,
  ID_Venta INT NOT NULL UNIQUE,
  Direccion_Envio VARCHAR(255) NOT NULL,
  Ciudad_Envio INT NOT NULL,
  Tipo_Envio ENUM('Estándar','Express','Recogida en tienda') NOT NULL,
  Estado_Pedido ENUM('Preparando','En camino','Entregado','Cancelado') DEFAULT 'Preparando',
  Guia_Seguimiento VARCHAR(50) NULL,
  FOREIGN KEY (ID_Venta) REFERENCES venta(ID_Venta),
  FOREIGN KEY (Ciudad_Envio) REFERENCES ciudad(ID_Ciudad)
) ENGINE=InnoDB;

CREATE TABLE pqr (
  ID_Pqr INT AUTO_INCREMENT PRIMARY KEY,
  Fecha_Registro DATETIME DEFAULT CURRENT_TIMESTAMP,
  Descripcion TEXT NOT NULL,
  Estado ENUM('Abierto','En Proceso','Cerrado') DEFAULT 'Abierto',
  ID_Cliente INT NOT NULL,
  ID_Empleado INT NULL,
  FOREIGN KEY (ID_Cliente) REFERENCES cliente(ID_Cliente),
  FOREIGN KEY (ID_Empleado) REFERENCES empleado(ID_Empleado)
) ENGINE=InnoDB;

CREATE TABLE alertas_sistema (
  ID_Alerta INT AUTO_INCREMENT PRIMARY KEY,
  Tipo VARCHAR(50) NOT NULL,
  Mensaje TEXT NOT NULL,
  Fecha_Creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
  Leido TINYINT(1) DEFAULT 0
) ENGINE=InnoDB;

CREATE TABLE password_resets (
  ID_Reset INT AUTO_INCREMENT PRIMARY KEY,
  ID_Usuario INT NOT NULL,
  token_hash CHAR(64) NOT NULL UNIQUE,
  expira_en DATETIME NOT NULL,
  usado_en DATETIME NULL,
  creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_reset_usuario FOREIGN KEY (ID_Usuario) REFERENCES usuario(ID_Usuario) ON DELETE CASCADE,
  INDEX idx_reset_hash (token_hash),
  INDEX idx_reset_usuario (ID_Usuario)
) ENGINE=InnoDB;

-- ==============================================================================
-- 1.1 ÍNDICES COMPUESTOS CRÍTICOS
-- ==============================================================================

CREATE INDEX idx_venta_fecha_cliente ON venta(Fecha_Venta, ID_Cliente);
CREATE INDEX idx_movimiento_producto_fecha ON movimiento_inventario(ID_Producto, Fecha_Movimiento);
CREATE INDEX idx_alerta_tipo_fecha ON alertas_sistema(Tipo, Fecha_Creacion);
CREATE INDEX idx_detalle_venta_venta ON detalle_venta(ID_Venta);
CREATE INDEX idx_pago_venta ON pago(ID_Venta);
CREATE INDEX idx_pedido_estado ON pedido(Estado_Pedido);
CREATE INDEX idx_resena_producto ON resena_valoracion(ID_Producto);
CREATE INDEX idx_pedido_ciudad ON pedido(Ciudad_Envio);
CREATE INDEX idx_libreta_cliente_principal ON libreta_direcciones(ID_Cliente, Es_Principal);
CREATE INDEX idx_cliente_deleted ON cliente(deleted_at);
CREATE INDEX idx_empleado_deleted ON empleado(deleted_at);
CREATE INDEX idx_producto_deleted ON producto(deleted_at);
CREATE INDEX idx_usuario_deleted ON usuario(deleted_at);

-- ==============================================================================
-- 1.2 PARTICIONADO DE TABLAS DE ALTA ROTACIÓN
-- ==============================================================================

ALTER TABLE movimiento_inventario
PARTITION BY RANGE COLUMNS(Fecha_Movimiento) (
  PARTITION p2024_01 VALUES LESS THAN ('2024-02-01'),
  PARTITION p2024_02 VALUES LESS THAN ('2024-03-01'),
  PARTITION p2024_03 VALUES LESS THAN ('2024-04-01'),
  PARTITION p2024_04 VALUES LESS THAN ('2024-05-01'),
  PARTITION p2024_05 VALUES LESS THAN ('2024-06-01'),
  PARTITION p2024_06 VALUES LESS THAN ('2024-07-01'),
  PARTITION p2024_07 VALUES LESS THAN ('2024-08-01'),
  PARTITION p2024_08 VALUES LESS THAN ('2024-09-01'),
  PARTITION p2024_09 VALUES LESS THAN ('2024-10-01'),
  PARTITION p2024_10 VALUES LESS THAN ('2024-11-01'),
  PARTITION p2024_11 VALUES LESS THAN ('2024-12-01'),
  PARTITION p2024_12 VALUES LESS THAN ('2025-01-01'),
  PARTITION p2025_01 VALUES LESS THAN ('2025-02-01'),
  PARTITION p2025_02 VALUES LESS THAN ('2025-03-01'),
  PARTITION p2025_03 VALUES LESS THAN ('2025-04-01'),
  PARTITION p2025_04 VALUES LESS THAN ('2025-05-01'),
  PARTITION p2025_05 VALUES LESS THAN ('2025-06-01'),
  PARTITION p2025_06 VALUES LESS THAN ('2025-07-01'),
  PARTITION p2025_07 VALUES LESS THAN ('2025-08-01'),
  PARTITION p2025_08 VALUES LESS THAN ('2025-09-01'),
  PARTITION p2025_09 VALUES LESS THAN ('2025-10-01'),
  PARTITION p2025_10 VALUES LESS THAN ('2025-11-01'),
  PARTITION p2025_11 VALUES LESS THAN ('2025-12-01'),
  PARTITION p2025_12 VALUES LESS THAN ('2026-01-01'),
  PARTITION p2026_01 VALUES LESS THAN ('2026-02-01'),
  PARTITION p2026_02 VALUES LESS THAN ('2026-03-01'),
  PARTITION p2026_03 VALUES LESS THAN ('2026-04-01'),
  PARTITION p2026_04 VALUES LESS THAN ('2026-05-01'),
  PARTITION p2026_05 VALUES LESS THAN ('2026-06-01'),
  PARTITION p2026_06 VALUES LESS THAN ('2026-07-01'),
  PARTITION p2026_07 VALUES LESS THAN ('2026-08-01'),
  PARTITION p2026_08 VALUES LESS THAN ('2026-09-01'),
  PARTITION p2026_09 VALUES LESS THAN ('2026-10-01'),
  PARTITION p2026_10 VALUES LESS THAN ('2026-11-01'),
  PARTITION p2026_11 VALUES LESS THAN ('2026-12-01'),
  PARTITION p2026_12 VALUES LESS THAN ('2027-01-01'),
  PARTITION p_future VALUES LESS THAN MAXVALUE
);

ALTER TABLE alertas_sistema
PARTITION BY RANGE COLUMNS(Fecha_Creacion) (
  PARTITION p2024_01 VALUES LESS THAN ('2024-02-01'),
  PARTITION p2024_02 VALUES LESS THAN ('2024-03-01'),
  PARTITION p2024_03 VALUES LESS THAN ('2024-04-01'),
  PARTITION p2024_04 VALUES LESS THAN ('2024-05-01'),
  PARTITION p2024_05 VALUES LESS THAN ('2024-06-01'),
  PARTITION p2024_06 VALUES LESS THAN ('2024-07-01'),
  PARTITION p2024_07 VALUES LESS THAN ('2024-08-01'),
  PARTITION p2024_08 VALUES LESS THAN ('2024-09-01'),
  PARTITION p2024_09 VALUES LESS THAN ('2024-10-01'),
  PARTITION p2024_10 VALUES LESS THAN ('2024-11-01'),
  PARTITION p2024_11 VALUES LESS THAN ('2024-12-01'),
  PARTITION p2024_12 VALUES LESS THAN ('2025-01-01'),
  PARTITION p2025_01 VALUES LESS THAN ('2025-02-01'),
  PARTITION p2025_02 VALUES LESS THAN ('2025-03-01'),
  PARTITION p2025_03 VALUES LESS THAN ('2025-04-01'),
  PARTITION p2025_04 VALUES LESS THAN ('2025-05-01'),
  PARTITION p2025_05 VALUES LESS THAN ('2025-06-01'),
  PARTITION p2025_06 VALUES LESS THAN ('2025-07-01'),
  PARTITION p2025_07 VALUES LESS THAN ('2025-08-01'),
  PARTITION p2025_08 VALUES LESS THAN ('2025-09-01'),
  PARTITION p2025_09 VALUES LESS THAN ('2025-10-01'),
  PARTITION p2025_10 VALUES LESS THAN ('2025-11-01'),
  PARTITION p2025_11 VALUES LESS THAN ('2025-12-01'),
  PARTITION p2025_12 VALUES LESS THAN ('2026-01-01'),
  PARTITION p2026_01 VALUES LESS THAN ('2026-02-01'),
  PARTITION p2026_02 VALUES LESS THAN ('2026-03-01'),
  PARTITION p2026_03 VALUES LESS THAN ('2026-04-01'),
  PARTITION p2026_04 VALUES LESS THAN ('2026-05-01'),
  PARTITION p2026_05 VALUES LESS THAN ('2026-06-01'),
  PARTITION p2026_06 VALUES LESS THAN ('2026-07-01'),
  PARTITION p2026_07 VALUES LESS THAN ('2026-08-01'),
  PARTITION p2026_08 VALUES LESS THAN ('2026-09-01'),
  PARTITION p2026_09 VALUES LESS THAN ('2026-10-01'),
  PARTITION p2026_10 VALUES LESS THAN ('2026-11-01'),
  PARTITION p2026_11 VALUES LESS THAN ('2026-12-01'),
  PARTITION p2026_12 VALUES LESS THAN ('2027-01-01'),
  PARTITION p_future VALUES LESS THAN MAXVALUE
);

ALTER TABLE auditoria_precios
PARTITION BY RANGE COLUMNS(Fecha_Cambio) (
  PARTITION p2024_01 VALUES LESS THAN ('2024-02-01'),
  PARTITION p2024_02 VALUES LESS THAN ('2024-03-01'),
  PARTITION p2024_03 VALUES LESS THAN ('2024-04-01'),
  PARTITION p2024_04 VALUES LESS THAN ('2024-05-01'),
  PARTITION p2024_05 VALUES LESS THAN ('2024-06-01'),
  PARTITION p2024_06 VALUES LESS THAN ('2024-07-01'),
  PARTITION p2024_07 VALUES LESS THAN ('2024-08-01'),
  PARTITION p2024_08 VALUES LESS THAN ('2024-09-01'),
  PARTITION p2024_09 VALUES LESS THAN ('2024-10-01'),
  PARTITION p2024_10 VALUES LESS THAN ('2024-11-01'),
  PARTITION p2024_11 VALUES LESS THAN ('2024-12-01'),
  PARTITION p2024_12 VALUES LESS THAN ('2025-01-01'),
  PARTITION p2025_01 VALUES LESS THAN ('2025-02-01'),
  PARTITION p2025_02 VALUES LESS THAN ('2025-03-01'),
  PARTITION p2025_03 VALUES LESS THAN ('2025-04-01'),
  PARTITION p2025_04 VALUES LESS THAN ('2025-05-01'),
  PARTITION p2025_05 VALUES LESS THAN ('2025-06-01'),
  PARTITION p2025_06 VALUES LESS THAN ('2025-07-01'),
  PARTITION p2025_07 VALUES LESS THAN ('2025-08-01'),
  PARTITION p2025_08 VALUES LESS THAN ('2025-09-01'),
  PARTITION p2025_09 VALUES LESS THAN ('2025-10-01'),
  PARTITION p2025_10 VALUES LESS THAN ('2025-11-01'),
  PARTITION p2025_11 VALUES LESS THAN ('2025-12-01'),
  PARTITION p2025_12 VALUES LESS THAN ('2026-01-01'),
  PARTITION p2026_01 VALUES LESS THAN ('2026-02-01'),
  PARTITION p2026_02 VALUES LESS THAN ('2026-03-01'),
  PARTITION p2026_03 VALUES LESS THAN ('2026-04-01'),
  PARTITION p2026_04 VALUES LESS THAN ('2026-05-01'),
  PARTITION p2026_05 VALUES LESS THAN ('2026-06-01'),
  PARTITION p2026_06 VALUES LESS THAN ('2026-07-01'),
  PARTITION p2026_07 VALUES LESS THAN ('2026-08-01'),
  PARTITION p2026_08 VALUES LESS THAN ('2026-09-01'),
  PARTITION p2026_09 VALUES LESS THAN ('2026-10-01'),
  PARTITION p2026_10 VALUES LESS THAN ('2026-11-01'),
  PARTITION p2026_11 VALUES LESS THAN ('2026-12-01'),
  PARTITION p2026_12 VALUES LESS THAN ('2027-01-01'),
  PARTITION p_future VALUES LESS THAN MAXVALUE
);

-- ==============================================================================
-- 2. BLOQUE DE 50 VISTAS DEL SISTEMA
-- ==============================================================================

-- Módulo Administrativo y Ventas (1 a 15)
CREATE OR REPLACE VIEW v_ventas_diarias AS SELECT DATE(Fecha_Venta) AS Fecha, COUNT(*) AS Total_Ventas FROM venta GROUP BY DATE(Fecha_Venta);
CREATE OR REPLACE VIEW v_ventas_mensuales AS SELECT DATE_FORMAT(Fecha_Venta, '%Y-%m') AS Mes, COUNT(*) AS Total_Ventas FROM venta GROUP BY Mes;
CREATE OR REPLACE VIEW v_top_productos AS SELECT p.Nombre_Producto, SUM(dv.Cantidad) AS Vendidos FROM detalle_venta dv JOIN producto p ON dv.ID_Producto = p.ID_Producto WHERE p.deleted_at IS NULL GROUP BY p.ID_Producto ORDER BY Vendidos DESC;
CREATE OR REPLACE VIEW v_ingresos_categoria AS SELECT c.Nombre_Categoria, SUM(dv.Cantidad * dv.Precio_Venta_Historico) AS Total FROM detalle_venta dv JOIN producto p ON dv.ID_Producto = p.ID_Producto JOIN categoria c ON p.ID_Categoria = c.ID_Categoria WHERE p.deleted_at IS NULL GROUP BY c.ID_Categoria;
CREATE OR REPLACE VIEW v_rendimiento_empleados AS SELECT e.Nombres, e.Apellidos, COUNT(v.ID_Venta) AS Atendidos FROM venta v JOIN empleado e ON v.ID_Empleado = e.ID_Empleado WHERE e.deleted_at IS NULL GROUP BY e.ID_Empleado;
CREATE OR REPLACE VIEW v_auditoria_precios AS SELECT * FROM auditoria_precios ORDER BY Fecha_Cambio DESC;
CREATE OR REPLACE VIEW v_clientes_top AS SELECT c.ID_Cliente, c.Nombres, c.Apellidos, COUNT(v.ID_Venta) AS Compras FROM cliente c JOIN venta v ON c.ID_Cliente = v.ID_Cliente WHERE c.deleted_at IS NULL GROUP BY c.ID_Cliente ORDER BY Compras DESC;
CREATE OR REPLACE VIEW v_metodos_pago_uso AS SELECT mp.Tipo_Metodo, COUNT(p.ID_Pago) AS Uso FROM pago p JOIN metodo_pago mp ON p.ID_Metodo = mp.ID_Metodo GROUP BY mp.ID_Metodo;
CREATE OR REPLACE VIEW v_envios_pendientes AS SELECT * FROM pedido WHERE Estado_Pedido IN ('Preparando', 'En camino');
CREATE OR REPLACE VIEW v_pedidos_entregados AS SELECT * FROM pedido WHERE Estado_Pedido = 'Entregado';
CREATE OR REPLACE VIEW v_facturacion_global AS SELECT f.Numero_Factura, f.Fecha_Emision, SUM(p.Monto_Pagado) AS Total FROM factura f JOIN pago p ON f.ID_Venta = p.ID_Venta GROUP BY f.ID_Factura;
CREATE OR REPLACE VIEW v_tickets_promedio AS SELECT AVG(Monto_Pagado) AS Promedio_Venta FROM pago;
CREATE OR REPLACE VIEW v_ventas_por_ciudad AS SELECT ci.Nombre_Ciudad, COUNT(v.ID_Venta) AS Ventas FROM venta v JOIN cliente c ON v.ID_Cliente = c.ID_Cliente JOIN libreta_direcciones ld ON c.ID_Cliente = ld.ID_Cliente JOIN ciudad ci ON ld.Ciudad_Id = ci.ID_Ciudad WHERE ld.Es_Principal = 1 AND c.deleted_at IS NULL GROUP BY ci.ID_Ciudad;
CREATE OR REPLACE VIEW v_impuestos_recaudados AS SELECT SUM(Monto_Pagado * 0.19) AS IVA_Total FROM pago;
CREATE OR REPLACE VIEW v_cancelaciones_mes AS SELECT * FROM pedido WHERE Estado_Pedido = 'Cancelado';

-- Módulo Cliente y Autogestión (16 a 30)
CREATE OR REPLACE VIEW v_catalogo_optimizado AS SELECT ID_Producto, Nombre_Producto, Precio_Actual, Stock_Actual, Imagen_URL FROM producto WHERE Stock_Actual > 0 AND deleted_at IS NULL;
CREATE OR REPLACE VIEW v_catalogo_agotados AS SELECT ID_Producto, Nombre_Producto, Precio_Actual FROM producto WHERE Stock_Actual = 0 AND deleted_at IS NULL;
CREATE OR REPLACE VIEW v_mis_pedidos_activos AS SELECT v.ID_Cliente, p.* FROM pedido p JOIN venta v ON p.ID_Venta = v.ID_Venta WHERE p.Estado_Pedido != 'Entregado';
CREATE OR REPLACE VIEW v_mi_historial_compras AS SELECT v.ID_Cliente, v.ID_Venta, v.Fecha_Venta, f.Numero_Factura FROM venta v LEFT JOIN factura f ON v.ID_Venta = f.ID_Venta;
CREATE OR REPLACE VIEW v_mis_direcciones AS SELECT * FROM libreta_direcciones;
CREATE OR REPLACE VIEW v_mi_direccion_principal AS SELECT * FROM libreta_direcciones WHERE Es_Principal = 1;
CREATE OR REPLACE VIEW v_mi_wishlist AS SELECT w.ID_Cliente, p.Nombre_Producto, p.Precio_Actual, IF(p.Stock_Actual > 0, 'Disponible', 'Agotado') AS Estado FROM lista_deseos w JOIN producto p ON w.ID_Producto = p.ID_Producto WHERE p.deleted_at IS NULL;
CREATE OR REPLACE VIEW v_mis_resenas AS SELECT * FROM resena_valoracion;
CREATE OR REPLACE VIEW v_mis_pqrs AS SELECT * FROM pqr;
CREATE OR REPLACE VIEW v_facturas_cliente AS SELECT v.ID_Cliente, f.* FROM factura f JOIN venta v ON f.ID_Venta = v.ID_Venta;
CREATE OR REPLACE VIEW v_carrito_actual AS SELECT v.ID_Cliente, dv.* FROM detalle_venta dv JOIN venta v ON dv.ID_Venta = v.ID_Venta;
CREATE OR REPLACE VIEW v_novedades AS SELECT * FROM producto WHERE deleted_at IS NULL ORDER BY ID_Producto DESC LIMIT 10;
CREATE OR REPLACE VIEW v_ofertas AS SELECT * FROM producto WHERE Precio_Actual < 50000 AND deleted_at IS NULL;
CREATE OR REPLACE VIEW v_mejor_calificados AS SELECT p.Nombre_Producto, AVG(rv.Calificacion) AS Promedio FROM resena_valoracion rv JOIN producto p ON rv.ID_Producto = p.ID_Producto WHERE p.deleted_at IS NULL GROUP BY p.ID_Producto HAVING Promedio >= 4;
CREATE OR REPLACE VIEW v_mis_metodos_pago AS SELECT * FROM metodo_pago;

-- Módulo Inventario y Logística (31 a 40)
CREATE OR REPLACE VIEW v_inventario_general AS SELECT p.ID_Producto, p.Nombre_Producto, p.Stock_Actual, c.Nombre_Categoria FROM producto p JOIN categoria c ON p.ID_Categoria = c.ID_Categoria WHERE p.deleted_at IS NULL;
CREATE OR REPLACE VIEW v_valorizacion_stock AS SELECT SUM(Stock_Actual * Precio_Actual) AS Valor_Total_Inventario FROM producto WHERE deleted_at IS NULL;
CREATE OR REPLACE VIEW v_proveedores_activos AS SELECT * FROM proveedor;
CREATE OR REPLACE VIEW v_productos_por_proveedor AS SELECT pr.Nombre_Empresa, COUNT(p.ID_Producto) AS Total_Productos FROM producto p JOIN proveedor pr ON p.ID_Proveedor = pr.ID_Proveedor WHERE p.deleted_at IS NULL GROUP BY pr.ID_Proveedor;
CREATE OR REPLACE VIEW v_ajustes_kardex AS SELECT * FROM movimiento_inventario WHERE Tipo_Movimiento = 'Ajuste';
CREATE OR REPLACE VIEW v_ciudades_cobertura AS SELECT * FROM ciudad;
CREATE OR REPLACE VIEW v_departamentos_cobertura AS SELECT * FROM departamento;
CREATE OR REPLACE VIEW v_rutas_envio AS SELECT p.ID_Pedido, c.Nombre_Ciudad, d.Nombre_Departamento FROM pedido p JOIN ciudad c ON p.Ciudad_Envio = c.ID_Ciudad JOIN departamento d ON c.ID_Departamento = d.ID_Departamento;
CREATE OR REPLACE VIEW v_alertas_stock_cero AS SELECT * FROM producto WHERE Stock_Actual = 0 AND deleted_at IS NULL;
CREATE OR REPLACE VIEW v_lista_espera_activa AS SELECT * FROM lista_espera_stock WHERE Notificado = 0;

-- Módulo Seguridad y Soporte (41 a 50)
CREATE OR REPLACE VIEW v_usuarios_bloqueados AS SELECT ca.ID_Control, ca.ID_Usuario, ca.Intentos_Fallidos, ca.Ultimo_Intento, ca.Bloqueado_Hasta, u.Email FROM control_accesos ca JOIN usuario u ON ca.ID_Usuario = u.ID_Usuario WHERE ca.Bloqueado_Hasta > NOW();
CREATE OR REPLACE VIEW v_intentos_fallidos AS SELECT ca.ID_Control, ca.ID_Usuario, ca.Intentos_Fallidos, ca.Ultimo_Intento, ca.Bloqueado_Hasta, u.Email FROM control_accesos ca JOIN usuario u ON ca.ID_Usuario = u.ID_Usuario WHERE ca.Intentos_Fallidos > 0;
CREATE OR REPLACE VIEW v_log_errores AS SELECT * FROM alertas_sistema WHERE Tipo = 'ERROR';
CREATE OR REPLACE VIEW v_auditoria_roles AS SELECT u.Email, u.Rol, e.Nombres FROM usuario u LEFT JOIN empleado e ON u.ID_Empleado = e.ID_Empleado WHERE u.deleted_at IS NULL;
CREATE OR REPLACE VIEW v_sesiones_activas AS SELECT ca.ID_Usuario, u.Email, ca.Ultimo_Intento FROM control_accesos ca JOIN usuario u ON ca.ID_Usuario = u.ID_Usuario WHERE ca.Ultimo_Intento >= NOW() - INTERVAL 15 MINUTE;
CREATE OR REPLACE VIEW v_empleados_inactivos AS SELECT e.* FROM empleado e LEFT JOIN usuario u ON e.ID_Empleado = u.ID_Empleado WHERE u.ID_Usuario IS NULL AND e.deleted_at IS NULL;
CREATE OR REPLACE VIEW v_clientes_inactivos AS SELECT c.* FROM cliente c LEFT JOIN venta v ON c.ID_Cliente = v.ID_Cliente WHERE v.ID_Venta IS NULL AND c.deleted_at IS NULL;
CREATE OR REPLACE VIEW v_resenas_pendientes AS SELECT * FROM resena_valoracion WHERE Estado_Moderacion = 'Pendiente';
CREATE OR REPLACE VIEW v_resenas_rechazadas AS SELECT * FROM resena_valoracion WHERE Estado_Moderacion = 'Rechazado';
CREATE OR REPLACE VIEW v_pqrs_vencidas AS SELECT * FROM pqr WHERE Estado = 'Abierto' AND Fecha_Registro <= NOW() - INTERVAL 5 DAY;

-- ==============================================================================
-- 3. BLOQUE DE DISPARADORES (TRIGGERS) — v2.0
-- ==============================================================================

DELIMITER $$

-- Stock y Catálogo (1 a 10)
CREATE TRIGGER trg_descontar_stock_pago AFTER INSERT ON pago FOR EACH ROW BEGIN
    UPDATE producto p JOIN detalle_venta dv ON p.ID_Producto = dv.ID_Producto
    SET p.Stock_Actual = p.Stock_Actual - dv.Cantidad WHERE dv.ID_Venta = NEW.ID_Venta;
END$$

CREATE TRIGGER trg_devolver_stock_cancelacion AFTER UPDATE ON pedido FOR EACH ROW BEGIN
    IF NEW.Estado_Pedido = 'Cancelado' AND OLD.Estado_Pedido != 'Cancelado' THEN
        UPDATE producto p JOIN detalle_venta dv ON p.ID_Producto = dv.ID_Producto
        SET p.Stock_Actual = p.Stock_Actual + dv.Cantidad WHERE dv.ID_Venta = NEW.ID_Venta;
    END IF;
END$$

CREATE TRIGGER trg_alerta_stock_minimo AFTER UPDATE ON producto FOR EACH ROW BEGIN
    IF NEW.Stock_Actual < 10 AND OLD.Stock_Actual >= 10 THEN
        INSERT INTO alertas_sistema (Tipo, Mensaje) VALUES ('STOCK_BAJO', CONCAT('Producto ', NEW.Nombre_Producto, ' en nivel crítico.'));
    END IF;
END$$

CREATE TRIGGER trg_impedir_stock_negativo BEFORE UPDATE ON producto FOR EACH ROW BEGIN
    IF NEW.Stock_Actual < 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Stock insuficiente.';
    END IF;
END$$

CREATE TRIGGER trg_auditar_cambio_precio AFTER UPDATE ON producto FOR EACH ROW BEGIN
    IF OLD.Precio_Actual <> NEW.Precio_Actual THEN
        INSERT INTO auditoria_precios (ID_Producto, Precio_Anterior, Precio_Nuevo) VALUES (NEW.ID_Producto, OLD.Precio_Actual, NEW.Precio_Actual);
    END IF;
END$$

CREATE TRIGGER trg_bloquear_borrado_producto BEFORE DELETE ON producto FOR EACH ROW BEGIN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: No se permite eliminar productos del catálogo.';
END$$

CREATE TRIGGER trg_notificar_nuevo_producto AFTER INSERT ON producto FOR EACH ROW BEGIN
    INSERT INTO alertas_sistema (Tipo, Mensaje) VALUES ('NUEVO_PRODUCTO', CONCAT('Producto registrado: ', NEW.Nombre_Producto));
END$$

CREATE TRIGGER trg_validar_precio_positivo BEFORE INSERT ON producto FOR EACH ROW BEGIN
    IF NEW.Precio_Actual <= 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: El precio debe ser positivo.';
    END IF;
END$$

CREATE TRIGGER trg_auto_lista_espera AFTER INSERT ON lista_espera_stock FOR EACH ROW BEGIN
    INSERT INTO alertas_sistema (Tipo, Mensaje) VALUES ('LISTA_ESPERA', CONCAT('Nuevo interesado en producto ID: ', NEW.ID_Producto));
END$$

CREATE TRIGGER trg_notificar_restock AFTER UPDATE ON producto FOR EACH ROW BEGIN
    IF OLD.Stock_Actual = 0 AND NEW.Stock_Actual > 0 THEN
        INSERT INTO alertas_sistema (Tipo, Mensaje) VALUES ('RESTOCK', CONCAT('Producto con nuevo stock: ', NEW.Nombre_Producto));
    END IF;
END$$

-- Ventas y Facturación (11 a 20)
CREATE TRIGGER trg_generar_factura_auto AFTER INSERT ON pago FOR EACH ROW BEGIN
    DECLARE v_num VARCHAR(20);
    SELECT CONCAT('FAC-', LPAD(COALESCE(MAX(ID_Factura),0)+1, 4, '0')) INTO v_num FROM factura;
    INSERT INTO factura (Numero_Factura, ID_Venta) VALUES (v_num, NEW.ID_Venta);
END$$

CREATE TRIGGER trg_limite_10_unidades BEFORE INSERT ON detalle_venta FOR EACH ROW BEGIN
    IF NEW.Cantidad > 10 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Máximo 10 unidades por ítem.';
    END IF;
END$$

CREATE TRIGGER trg_congelar_precio_historico BEFORE INSERT ON detalle_venta FOR EACH ROW BEGIN
    DECLARE v_precio DECIMAL(10,2);
    SELECT Precio_Actual INTO v_precio FROM producto WHERE ID_Producto = NEW.ID_Producto;
    SET NEW.Precio_Venta_Historico = v_precio;
END$$

CREATE TRIGGER trg_impedir_edicion_factura BEFORE UPDATE ON factura FOR EACH ROW BEGIN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Facturas inmutables.';
END$$

CREATE TRIGGER trg_auditar_anulacion BEFORE UPDATE ON venta FOR EACH ROW BEGIN
    INSERT INTO alertas_sistema (Tipo, Mensaje) VALUES ('VENTA_MOD', CONCAT('Venta alterada ID: ', NEW.ID_Venta));
END$$

CREATE TRIGGER trg_bloquear_pago_doble BEFORE INSERT ON pago FOR EACH ROW BEGIN
    IF (SELECT COUNT(*) FROM pago WHERE ID_Venta = NEW.ID_Venta) > 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Venta ya fue pagada.';
    END IF;
END$$

CREATE TRIGGER trg_calcular_subtotal BEFORE INSERT ON detalle_venta FOR EACH ROW BEGIN
    IF NEW.Cantidad <= 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Cantidad inválida.';
    END IF;
END$$

CREATE TRIGGER trg_validar_metodo_pago BEFORE INSERT ON pago FOR EACH ROW BEGIN
    IF NEW.Monto_Pagado <= 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Monto de pago inválido.';
    END IF;
END$$

CREATE TRIGGER trg_registrar_fecha_pago BEFORE INSERT ON pago FOR EACH ROW BEGIN
    SET NEW.Fecha_Pago = NOW();
END$$

CREATE TRIGGER trg_bloquear_compra_sin_stock BEFORE INSERT ON detalle_venta FOR EACH ROW BEGIN
    DECLARE v_stk INT;
    SELECT Stock_Actual INTO v_stk FROM producto WHERE ID_Producto = NEW.ID_Producto;
    IF v_stk < NEW.Cantidad THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Sin disponibilidad en inventario.';
    END IF;
END$$

-- Logística y Pedidos (21 a 30)
CREATE TRIGGER trg_secuencia_estados_pedido BEFORE UPDATE ON pedido FOR EACH ROW BEGIN
    IF OLD.Estado_Pedido = 'Preparando' AND NEW.Estado_Pedido NOT IN ('En camino', 'Cancelado') THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Estado no permitido desde Preparando.';
    END IF;
END$$

CREATE TRIGGER trg_impedir_retroceso_estado BEFORE UPDATE ON pedido FOR EACH ROW BEGIN
    IF OLD.Estado_Pedido = 'Entregado' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Pedido ya entregado.';
    END IF;
END$$

CREATE TRIGGER trg_asignar_guia_auto BEFORE UPDATE ON pedido FOR EACH ROW BEGIN
    IF NEW.Estado_Pedido = 'En camino' AND NEW.Guia_Seguimiento IS NULL THEN
        SET NEW.Guia_Seguimiento = CONCAT('TRK-', LPAD(NEW.ID_Pedido, 6, '0'));
    END IF;
END$$

CREATE TRIGGER trg_auditar_cambio_direccion BEFORE UPDATE ON libreta_direcciones FOR EACH ROW BEGIN
    INSERT INTO alertas_sistema (Tipo, Mensaje) VALUES ('DIR_UPDATE', CONCAT('Dirección actualizada ID: ', NEW.ID_Direccion));
END$$

CREATE TRIGGER trg_validar_cobertura BEFORE INSERT ON pedido FOR EACH ROW BEGIN
    IF NEW.Ciudad_Envio IS NULL THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Ciudad de envío requerida.';
    END IF;
END$$

CREATE TRIGGER trg_notificar_despacho AFTER UPDATE ON pedido FOR EACH ROW BEGIN
    IF NEW.Estado_Pedido = 'En camino' AND OLD.Estado_Pedido != 'En camino' THEN
        INSERT INTO alertas_sistema (Tipo, Mensaje) VALUES ('DESPACHO', CONCAT('Pedido despachado ID: ', NEW.ID_Pedido));
    END IF;
END$$

CREATE TRIGGER trg_marcar_entregado AFTER UPDATE ON pedido FOR EACH ROW BEGIN
    IF NEW.Estado_Pedido = 'Entregado' THEN
        INSERT INTO alertas_sistema (Tipo, Mensaje) VALUES ('ENTREGA', CONCAT('Pedido entregado ID: ', NEW.ID_Pedido));
    END IF;
END$$

CREATE TRIGGER trg_bloquear_cancelacion_enviado BEFORE UPDATE ON pedido FOR EACH ROW BEGIN
    IF NEW.Estado_Pedido = 'Cancelado' AND OLD.Estado_Pedido IN ('En camino', 'Entregado') THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: No se puede cancelar pedido en tránsito o entregado.';
    END IF;
END$$

CREATE TRIGGER trg_calcular_fecha_estimada BEFORE INSERT ON pedido FOR EACH ROW BEGIN
    IF NEW.Tipo_Envio = 'Express' THEN
        SET NEW.Guia_Seguimiento = 'PRIORITARIO';
    END IF;
END$$

CREATE TRIGGER trg_sincronizar_ciudad BEFORE INSERT ON libreta_direcciones FOR EACH ROW BEGIN
    IF NEW.Es_Principal = 1 THEN
        UPDATE libreta_direcciones SET Es_Principal = 0 WHERE ID_Cliente = NEW.ID_Cliente;
    END IF;
END$$

-- Seguridad y Usuarios (31 a 38) — trg_validar_fortaleza_clave ELIMINADA (redundante con BCRYPT en PHP)
CREATE TRIGGER trg_bloqueo_5_intentos BEFORE UPDATE ON control_accesos FOR EACH ROW BEGIN
    IF NEW.Intentos_Fallidos >= 5 THEN
        SET NEW.Bloqueado_Hasta = NOW() + INTERVAL 15 MINUTE;
    END IF;
END$$

CREATE TRIGGER trg_hash_password_insert BEFORE INSERT ON usuario FOR EACH ROW BEGIN
    IF NEW.Email NOT LIKE '%@%.%' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Formato de correo no válido.';
    END IF;
END$$

CREATE TRIGGER trg_hash_password_update BEFORE UPDATE ON usuario FOR EACH ROW BEGIN
    IF NEW.Email NOT LIKE '%@%.%' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Formato de correo no válido.';
    END IF;
END$$

CREATE TRIGGER trg_email_unico_global BEFORE INSERT ON usuario FOR EACH ROW BEGIN
    IF (SELECT COUNT(*) FROM usuario WHERE Email = NEW.Email AND deleted_at IS NULL) > 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Correo ya registrado.';
    END IF;
END$$

CREATE TRIGGER trg_auditar_cambio_rol BEFORE UPDATE ON usuario FOR EACH ROW BEGIN
    IF OLD.Rol <> NEW.Rol THEN
        INSERT INTO alertas_sistema (Tipo, Mensaje) VALUES ('SEGURIDAD', CONCAT('Rol modificado para: ', NEW.Email));
    END IF;
END$$

CREATE TRIGGER trg_limpiar_sesiones BEFORE UPDATE ON control_accesos FOR EACH ROW BEGIN
    IF NEW.Intentos_Fallidos = 0 THEN
        SET NEW.Bloqueado_Hasta = NULL;
    END IF;
END$$

CREATE TRIGGER trg_registrar_acceso AFTER INSERT ON usuario FOR EACH ROW BEGIN
    INSERT INTO control_accesos (ID_Usuario, Email) VALUES (NEW.ID_Usuario, NEW.Email);
END$$

CREATE TRIGGER trg_impedir_borrado_cliente BEFORE DELETE ON cliente FOR EACH ROW BEGIN
    IF (SELECT COUNT(*) FROM venta WHERE ID_Cliente = OLD.ID_Cliente) > 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Cliente tiene ventas históricas asociadas.';
    END IF;
END$$

CREATE TRIGGER trg_auditar_alta_empleado AFTER INSERT ON empleado FOR EACH ROW BEGIN
    INSERT INTO alertas_sistema (Tipo, Mensaje) VALUES ('RRHH', CONCAT('Empleado registrado: ', NEW.Nombres, ' ', NEW.Apellidos));
END$$

-- PQRS y Moderación (39 a 43)
CREATE TRIGGER trg_unicidad_resena_cliente BEFORE INSERT ON resena_valoracion FOR EACH ROW BEGIN
    IF (SELECT COUNT(*) FROM resena_valoracion WHERE ID_Cliente = NEW.ID_Cliente AND ID_Producto = NEW.ID_Producto) > 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: El cliente ya reseñó este producto.';
    END IF;
END$$

CREATE TRIGGER trg_validar_rango_estrellas BEFORE INSERT ON resena_valoracion FOR EACH ROW BEGIN
    IF NEW.Calificacion < 1 OR NEW.Calificacion > 5 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Calificación fuera de rango (1-5).';
    END IF;
END$$

CREATE TRIGGER trg_auto_asignar_pqr BEFORE INSERT ON pqr FOR EACH ROW BEGIN
    SET NEW.Estado = 'Abierto';
END$$

CREATE TRIGGER trg_auditar_respuesta_pqr BEFORE UPDATE ON pqr FOR EACH ROW BEGIN
    IF OLD.Estado <> NEW.Estado THEN
        INSERT INTO alertas_sistema (Tipo, Mensaje) VALUES ('PQR_UPDATE', CONCAT('PQR ID ', NEW.ID_Pqr, ' cambió a estado ', NEW.Estado));
    END IF;
END$$

CREATE TRIGGER trg_bloquear_resena_sin_compra BEFORE INSERT ON resena_valoracion FOR EACH ROW BEGIN
    DECLARE v_compras INT;
    SELECT COUNT(*) INTO v_compras FROM detalle_venta dv JOIN venta v ON dv.ID_Venta = v.ID_Venta WHERE v.ID_Cliente = NEW.ID_Cliente AND dv.ID_Producto = NEW.ID_Producto;
    IF v_compras = 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Debe comprar la prenda para dejar una reseña.';
    END IF;
END$$

DELIMITER ;
