-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 14-09-2026 a las 23:34:53
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `proyecto_acido`
--
CREATE DATABASE IF NOT EXISTS `proyecto_acido` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE `proyecto_acido`;
-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `alertas_sistema`
--

CREATE TABLE `alertas_sistema` (
  `ID_Alerta` int(11) NOT NULL,
  `Tipo` varchar(50) NOT NULL,
  `Mensaje` text NOT NULL,
  `Fecha_Creacion` datetime DEFAULT current_timestamp(),
  `Leido` tinyint(1) DEFAULT 0,
  `ID_Producto` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `alertas_sistema`
--

INSERT INTO `alertas_sistema` (`ID_Alerta`, `Tipo`, `Mensaje`, `Fecha_Creacion`, `Leido`, `ID_Producto`) VALUES
(1, 'NUEVO_PRODUCTO', 'Producto registrado: Camiseta ACIDO Clásica', '2026-09-14 08:32:15', 0, NULL),
(2, 'NUEVO_PRODUCTO', 'Producto registrado: Pantalón ACIDO Urbano', '2026-09-14 08:32:15', 0, NULL),
(3, 'NUEVO_PRODUCTO', 'Producto registrado: Chaqueta ACIDO Oversize', '2026-09-14 08:32:15', 0, NULL),
(4, 'NUEVO_PRODUCTO', 'Producto registrado: Gorra ACIDO Bordada', '2026-09-14 08:32:15', 0, NULL),
(5, 'STOCK_BAJO', 'Producto Chaqueta ACIDO Oversize en nivel crítico.', '2026-09-14 08:51:00', 0, NULL),
(6, 'STOCK_BAJO', 'Stock bajo: Chaqueta ACIDO Oversize (6/10)', '2026-09-14 10:27:53', 0, 3),
(7, 'STOCK_BAJO', 'Stock bajo: Gorra ACIDO Bordada (0/10)', '2026-09-14 10:27:53', 0, 4),
(9, 'RRHH', 'Empleado registrado: Sistema Kardex', '2026-09-14 10:27:55', 0, NULL),
(10, 'PQR_UPDATE', 'PQR ID 1 cambió a estado En Proceso', '2026-09-14 15:03:02', 0, NULL),
(11, 'PQR_UPDATE', 'PQR ID 1 cambió a estado Cerrado', '2026-09-14 15:03:02', 0, NULL),
(12, 'PQR_UPDATE', 'PQR ID 2 cambió a estado Cerrado', '2026-09-14 15:07:25', 0, NULL),
(13, 'SEGURIDAD', 'Rol modificado para: jhona@gmail.com', '2026-09-14 15:14:36', 0, NULL),
(14, 'RRHH', 'Empleado registrado: Jhona Gar', '2026-09-14 15:14:36', 0, NULL),
(15, 'LISTA_ESPERA', 'Nuevo interesado en producto ID: 4', '2026-09-14 15:32:15', 0, NULL),
(16, 'ENTREGA', 'Pedido entregado ID: 2', '2026-09-14 15:33:43', 0, NULL),
(17, 'LISTA_ESPERA', 'Nuevo interesado en producto ID: 4', '2026-09-14 15:48:37', 0, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `auditoria_precios`
--

CREATE TABLE `auditoria_precios` (
  `ID_Auditoria` int(11) NOT NULL,
  `ID_Producto` int(11) NOT NULL,
  `Precio_Anterior` decimal(10,2) NOT NULL,
  `Precio_Nuevo` decimal(10,2) NOT NULL,
  `Fecha_Cambio` datetime DEFAULT current_timestamp(),
  `Usuario_Responsable` varchar(100) DEFAULT 'Sistema'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cargo`
--

CREATE TABLE `cargo` (
  `ID_Cargo` int(11) NOT NULL,
  `Nombre_Cargo` varchar(50) NOT NULL,
  `Salario_Base` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `cargo`
--

INSERT INTO `cargo` (`ID_Cargo`, `Nombre_Cargo`, `Salario_Base`) VALUES
(1, 'Administrador', 2500000.00),
(2, 'Vendedor', 1300000.00),
(3, 'Gerente', 3000000.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categoria`
--

CREATE TABLE `categoria` (
  `ID_Categoria` int(11) NOT NULL,
  `Nombre_Categoria` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `categoria`
--

INSERT INTO `categoria` (`ID_Categoria`, `Nombre_Categoria`) VALUES
(1, 'Camisetas'),
(2, 'Pantalones'),
(3, 'Chaquetas'),
(4, 'Accesorios');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ciudad`
--

CREATE TABLE `ciudad` (
  `ID_Ciudad` int(11) NOT NULL,
  `Nombre_Ciudad` varchar(100) NOT NULL,
  `ID_Departamento` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `ciudad`
--

INSERT INTO `ciudad` (`ID_Ciudad`, `Nombre_Ciudad`, `ID_Departamento`) VALUES
(1, 'Bogotá', 1),
(2, 'Soacha', 2),
(3, 'Medellín', 3),
(4, 'Cali', 4);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cliente`
--

CREATE TABLE `cliente` (
  `ID_Cliente` int(11) NOT NULL,
  `Nombres` varchar(70) NOT NULL,
  `Apellidos` varchar(70) NOT NULL,
  `Documento` varchar(20) DEFAULT NULL,
  `Telefono` varchar(15) DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `cliente`
--

INSERT INTO `cliente` (`ID_Cliente`, `Nombres`, `Apellidos`, `Documento`, `Telefono`, `deleted_at`) VALUES
(1, 'Admin', 'ACIDO', '1000000001', '6012345', NULL),
(2, 'María', 'González', '2000000001', '3001112233', NULL),
(3, 'Carlos', 'Ramírez', '2000000002', '3004445566', NULL),
(11, 'Jhona', 'Gar', '5252525252', '3202145635', NULL),
(13, 'Jhonatan', 'Garcia', '111111111111', '3202336326', NULL),
(14, 'Jhonatan', 'Garcia', '1010101010', '3204732916', NULL),
(15, 'Testbuy', '', NULL, NULL, NULL);

--
-- Disparadores `cliente`
--
DELIMITER $$
CREATE TRIGGER `trg_impedir_borrado_cliente` BEFORE DELETE ON `cliente` FOR EACH ROW BEGIN
    IF (SELECT COUNT(*) FROM venta WHERE ID_Cliente = OLD.ID_Cliente) > 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Cliente tiene ventas históricas asociadas.';
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `control_accesos`
--

CREATE TABLE `control_accesos` (
  `ID_Control` int(11) NOT NULL,
  `ID_Usuario` int(11) NOT NULL,
  `Email` varchar(100) NOT NULL,
  `Intentos_Fallidos` int(11) DEFAULT 0,
  `Ultimo_Intento` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `Bloqueado_Hasta` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `control_accesos`
--

INSERT INTO `control_accesos` (`ID_Control`, `ID_Usuario`, `Email`, `Intentos_Fallidos`, `Ultimo_Intento`, `Bloqueado_Hasta`) VALUES
(1, 1, 'admin@acido.local', 3, '2026-09-14 16:13:47', NULL),
(27, 10, 'jhona@gmail.com', 0, '2026-09-14 15:15:52', NULL),
(31, 12, 'test.jhona.acido@gmail.com', 0, '2026-09-14 10:19:37', NULL),
(50, 13, 'jhonatan@gmail.com', 0, '2026-09-14 15:36:44', NULL),
(53, 14, 'testbuy@acido.local', 0, '2026-09-14 15:44:08', NULL);

--
-- Disparadores `control_accesos`
--
DELIMITER $$
CREATE TRIGGER `trg_bloqueo_5_intentos` BEFORE UPDATE ON `control_accesos` FOR EACH ROW BEGIN
    IF NEW.Intentos_Fallidos >= 5 THEN
        SET NEW.Bloqueado_Hasta = NOW() + INTERVAL 15 MINUTE;
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_limpiar_sesiones` BEFORE UPDATE ON `control_accesos` FOR EACH ROW BEGIN
    IF NEW.Intentos_Fallidos = 0 THEN
        SET NEW.Bloqueado_Hasta = NULL;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `departamento`
--

CREATE TABLE `departamento` (
  `ID_Departamento` int(11) NOT NULL,
  `Nombre_Departamento` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `departamento`
--

INSERT INTO `departamento` (`ID_Departamento`, `Nombre_Departamento`) VALUES
(1, 'Bogotá D.C.'),
(2, 'Cundinamarca'),
(3, 'Antioquia'),
(4, 'Valle del Cauca');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_venta`
--

CREATE TABLE `detalle_venta` (
  `ID_Detalle` int(11) NOT NULL,
  `ID_Venta` int(11) NOT NULL,
  `ID_Producto` int(11) NOT NULL,
  `Cantidad` int(11) NOT NULL CHECK (`Cantidad` <= 10),
  `Precio_Venta_Historico` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `detalle_venta`
--

INSERT INTO `detalle_venta` (`ID_Detalle`, `ID_Venta`, `ID_Producto`, `Cantidad`, `Precio_Venta_Historico`) VALUES
(1, 1, 1, 3, 59900.00),
(2, 2, 1, 2, 59900.00),
(3, 2, 2, 1, 129900.00),
(4, 3, 2, 2, 129900.00),
(5, 4, 1, 4, 59900.00),
(6, 4, 3, 1, 199900.00),
(7, 5, 2, 3, 129900.00),
(8, 5, 3, 2, 199900.00),
(9, 6, 1, 2, 59900.00),
(10, 7, 1, 1, 59900.00),
(11, 8, 2, 1, 129900.00),
(12, 9, 3, 1, 199900.00),
(13, 10, 1, 2, 59900.00),
(14, 10, 2, 1, 129900.00),
(15, 11, 2, 1, 129900.00),
(16, 12, 1, 1, 59900.00),
(17, 12, 3, 1, 199900.00),
(18, 13, 1, 3, 59900.00),
(19, 14, 2, 2, 129900.00),
(20, 15, 3, 1, 199900.00),
(26, 21, 3, 2, 199900.00),
(27, 22, 2, 1, 129900.00),
(28, 23, 3, 1, 199900.00),
(29, 24, 2, 1, 129900.00),
(32, 27, 3, 1, 199900.00);

--
-- Disparadores `detalle_venta`
--
DELIMITER $$
CREATE TRIGGER `trg_bloquear_compra_sin_stock` BEFORE INSERT ON `detalle_venta` FOR EACH ROW BEGIN
    DECLARE v_stk INT;
    SELECT Stock_Actual INTO v_stk FROM producto WHERE ID_Producto = NEW.ID_Producto;
    IF v_stk < NEW.Cantidad THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Sin disponibilidad en inventario.';
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_calcular_subtotal` BEFORE INSERT ON `detalle_venta` FOR EACH ROW BEGIN
    IF NEW.Cantidad <= 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Cantidad inválida.';
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_congelar_precio_historico` BEFORE INSERT ON `detalle_venta` FOR EACH ROW BEGIN
    DECLARE v_precio DECIMAL(10,2);
    SELECT Precio_Actual INTO v_precio FROM producto WHERE ID_Producto = NEW.ID_Producto;
    SET NEW.Precio_Venta_Historico = v_precio;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_limite_10_unidades` BEFORE INSERT ON `detalle_venta` FOR EACH ROW BEGIN
    IF NEW.Cantidad > 10 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Máximo 10 unidades por ítem.';
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `empleado`
--

CREATE TABLE `empleado` (
  `ID_Empleado` int(11) NOT NULL,
  `Nombres` varchar(50) NOT NULL,
  `Apellidos` varchar(50) NOT NULL,
  `ID_Cargo` int(11) NOT NULL,
  `ID_Ciudad` int(11) NOT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `empleado`
--

INSERT INTO `empleado` (`ID_Empleado`, `Nombres`, `Apellidos`, `ID_Cargo`, `ID_Ciudad`, `deleted_at`) VALUES
(1, 'Sistema', 'Kardex', 1, 1, NULL),
(2, 'Jhona', 'Gar', 1, 1, NULL);

--
-- Disparadores `empleado`
--
DELIMITER $$
CREATE TRIGGER `trg_auditar_alta_empleado` AFTER INSERT ON `empleado` FOR EACH ROW BEGIN
    INSERT INTO alertas_sistema (Tipo, Mensaje)
    VALUES ('RRHH', CONCAT('Empleado registrado: ', NEW.Nombres, ' ', NEW.Apellidos));
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `factura`
--

CREATE TABLE `factura` (
  `ID_Factura` int(11) NOT NULL,
  `Numero_Factura` varchar(20) NOT NULL,
  `Fecha_Emision` datetime DEFAULT current_timestamp(),
  `ID_Venta` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `factura`
--

INSERT INTO `factura` (`ID_Factura`, `Numero_Factura`, `Fecha_Emision`, `ID_Venta`) VALUES
(1, 'FAC-0001', '2026-09-14 08:47:16', 1),
(2, 'FAC-0002', '2026-09-14 08:47:16', 2),
(3, 'FAC-0004', '2026-09-14 08:47:16', 3),
(4, 'FAC-0005', '2026-09-14 08:47:16', 4),
(5, 'FAC-0007', '2026-09-14 08:47:16', 5),
(6, 'FAC-0009', '2026-09-14 08:47:16', 6),
(7, 'FAC-0010', '2026-09-14 08:47:16', 7),
(8, 'FAC-0011', '2026-09-14 08:47:16', 8),
(9, 'FAC-0012', '2026-09-14 08:47:16', 9),
(10, 'FAC-0013', '2026-09-14 08:47:16', 10),
(11, 'FAC-0015', '2026-09-14 08:47:16', 11),
(12, 'FAC-0016', '2026-09-14 08:47:16', 12),
(13, 'FAC-0018', '2026-09-14 08:47:16', 13),
(14, 'FAC-0019', '2026-09-14 08:47:16', 14),
(15, 'FAC-0020', '2026-09-14 08:51:00', 15),
(21, 'FAC-0026', '2026-09-14 09:49:47', 21),
(22, 'FAC-0027', '2026-09-14 09:50:22', 22),
(23, 'FAC-0028', '2026-09-14 10:27:53', 23),
(24, 'FAC-0029', '2026-09-14 10:28:40', 24),
(27, 'FAC-0030', '2026-09-14 15:37:50', 27);

--
-- Disparadores `factura`
--
DELIMITER $$
CREATE TRIGGER `trg_impedir_borrado_factura` BEFORE DELETE ON `factura` FOR EACH ROW BEGIN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Facturas inmutables, no se pueden borrar.';
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_impedir_edicion_factura` BEFORE UPDATE ON `factura` FOR EACH ROW BEGIN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Facturas inmutables.';
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `libreta_direcciones`
--

CREATE TABLE `libreta_direcciones` (
  `ID_Direccion` int(11) NOT NULL,
  `ID_Cliente` int(11) NOT NULL,
  `Alias` varchar(50) NOT NULL,
  `ID_Ciudad` int(11) NOT NULL,
  `Codigo_Postal` varchar(10) DEFAULT NULL,
  `Direccion_Exacta` varchar(255) NOT NULL,
  `Referencias` varchar(255) DEFAULT NULL,
  `Es_Principal` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Disparadores `libreta_direcciones`
--
DELIMITER $$
CREATE TRIGGER `trg_auditar_cambio_direccion` BEFORE UPDATE ON `libreta_direcciones` FOR EACH ROW BEGIN
    INSERT INTO alertas_sistema (Tipo, Mensaje)
    VALUES ('DIR_UPDATE', CONCAT('Dirección actualizada ID: ', NEW.ID_Direccion));
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_sincronizar_ciudad` BEFORE INSERT ON `libreta_direcciones` FOR EACH ROW BEGIN
    IF NEW.Es_Principal = 1 THEN
        UPDATE libreta_direcciones SET Es_Principal = 0 WHERE ID_Cliente = NEW.ID_Cliente;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `lista_deseos`
--

CREATE TABLE `lista_deseos` (
  `ID_Wishlist` int(11) NOT NULL,
  `ID_Cliente` int(11) NOT NULL,
  `ID_Producto` int(11) NOT NULL,
  `Fecha_Agregado` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `lista_espera_stock`
--

CREATE TABLE `lista_espera_stock` (
  `ID_Espera` int(11) NOT NULL,
  `Nombre_Completo` varchar(150) NOT NULL,
  `Email` varchar(100) NOT NULL,
  `ID_Producto` int(11) NOT NULL,
  `Fecha_Registro` datetime DEFAULT current_timestamp(),
  `Notificado` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `lista_espera_stock`
--

INSERT INTO `lista_espera_stock` (`ID_Espera`, `Nombre_Completo`, `Email`, `ID_Producto`, `Fecha_Registro`, `Notificado`) VALUES
(2, 'Jhonatan Garcia', 'jhonatan@gmail.com', 4, '2026-09-14 15:48:37', 0);

--
-- Disparadores `lista_espera_stock`
--
DELIMITER $$
CREATE TRIGGER `trg_auto_lista_espera` AFTER INSERT ON `lista_espera_stock` FOR EACH ROW BEGIN
    INSERT INTO alertas_sistema (Tipo, Mensaje)
    VALUES ('LISTA_ESPERA', CONCAT('Nuevo interesado en producto ID: ', NEW.ID_Producto));
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `metodo_pago`
--

CREATE TABLE `metodo_pago` (
  `ID_Metodo` int(11) NOT NULL,
  `Tipo_Metodo` varchar(50) NOT NULL,
  `Logo` varchar(512) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `metodo_pago`
--

INSERT INTO `metodo_pago` (`ID_Metodo`, `Tipo_Metodo`, `Logo`) VALUES
(1, 'Efectivo', NULL),
(2, 'Nequi', '/ACIDO/BACKPHP_TEST/public/img/pagos/nequi.svg'),
(3, 'Daviplata', '/ACIDO/BACKPHP_TEST/public/img/pagos/daviplata.svg'),
(4, 'Transferencia bancaria', '/ACIDO/BACKPHP_TEST/public/img/pagos/transferencia.svg'),
(5, 'Contra entrega', NULL),
(6, 'Tarjeta de crédito / débito', '/ACIDO/BACKPHP_TEST/public/img/pagos/tarjeta.svg'),
(7, 'Bancolombia', '/ACIDO/BACKPHP_TEST/public/img/pagos/bancolombia.svg');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `movimiento_inventario`
--

CREATE TABLE `movimiento_inventario` (
  `ID_Movimiento` int(11) NOT NULL,
  `ID_Producto` int(11) NOT NULL,
  `Tipo_Movimiento` enum('Entrada','Salida','Ajuste') NOT NULL,
  `Cantidad` int(11) NOT NULL,
  `Motivo` varchar(255) NOT NULL,
  `Fecha_Movimiento` datetime DEFAULT current_timestamp(),
  `ID_Empleado` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `movimiento_inventario`
--

INSERT INTO `movimiento_inventario` (`ID_Movimiento`, `ID_Producto`, `Tipo_Movimiento`, `Cantidad`, `Motivo`, `Fecha_Movimiento`, `ID_Empleado`) VALUES
(1, 3, 'Salida', -1, 'Salida por venta #23', '2026-09-14 10:27:55', 1),
(2, 2, 'Salida', -1, 'Salida por venta #24', '2026-09-14 10:28:40', 1),
(3, 3, 'Salida', -1, 'Salida por venta #27', '2026-09-14 15:37:54', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notificacion_log`
--

CREATE TABLE `notificacion_log` (
  `ID_Log` int(11) NOT NULL,
  `Fecha` datetime NOT NULL DEFAULT current_timestamp(),
  `Tipo` varchar(20) NOT NULL COMMENT 'stock|kardex|resumen',
  `Destinatarios` varchar(500) NOT NULL,
  `Asunto` varchar(255) NOT NULL,
  `Resultado` enum('ok','fallo') NOT NULL DEFAULT 'fallo',
  `Detalle` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `notificacion_log`
--

INSERT INTO `notificacion_log` (`ID_Log`, `Fecha`, `Tipo`, `Destinatarios`, `Asunto`, `Resultado`, `Detalle`) VALUES
(1, '2026-09-14 10:26:49', 'clave', 'merge.tester@acido.local', 'Tu contraseña fue actualizada — ÁCIDO Colombia', 'ok', 'ENVIADO'),
(2, '2026-09-14 10:27:55', 'stock', 'admin@acido.local', 'Stock bajo: 2 producto(s) — ÁCIDO Colombia', 'ok', 'ENVIADO'),
(3, '2026-09-14 11:13:25', 'clave', 'admin@acido.local', 'Tu contraseña fue actualizada — ÁCIDO Colombia', 'ok', 'ENVIADO'),
(4, '2026-09-14 15:30:43', 'compra', 'test.jhona.acido@gmail.com', 'Confirmación de compra #9999 — ÁCIDO Colombia', 'ok', 'ENVIADO'),
(5, '2026-09-14 15:37:54', 'compra', 'jhonatan@gmail.com', 'Confirmación de compra #27 — ÁCIDO Colombia', 'ok', 'ENVIADO'),
(6, '2026-09-14 15:41:29', 'compra', 'testbuy@acido.local', 'Confirmación de compra #28 — ÁCIDO Colombia', 'ok', 'ENVIADO');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pago`
--

CREATE TABLE `pago` (
  `ID_Pago` int(11) NOT NULL,
  `ID_Venta` int(11) NOT NULL,
  `ID_Metodo` int(11) NOT NULL,
  `Monto_Pagado` decimal(10,2) NOT NULL,
  `Entidad_Bancaria` varchar(100) DEFAULT NULL,
  `Numero_Referencia` varchar(100) DEFAULT NULL,
  `Comprobante_URL` varchar(255) DEFAULT NULL,
  `Fecha_Pago` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `pago`
--

INSERT INTO `pago` (`ID_Pago`, `ID_Venta`, `ID_Metodo`, `Monto_Pagado`, `Entidad_Bancaria`, `Numero_Referencia`, `Comprobante_URL`, `Fecha_Pago`) VALUES
(1, 1, 1, 179700.00, NULL, NULL, NULL, '2026-04-10 11:05:00'),
(2, 2, 2, 249700.00, NULL, NULL, NULL, '2026-05-12 15:35:00'),
(3, 3, 3, 259800.00, NULL, NULL, NULL, '2026-06-15 10:05:00'),
(4, 4, 1, 439500.00, NULL, NULL, NULL, '2026-07-20 16:50:00'),
(5, 5, 4, 789500.00, NULL, NULL, NULL, '2026-08-18 12:25:00'),
(6, 6, 5, 119800.00, NULL, NULL, NULL, '2026-09-05 09:20:00'),
(7, 7, 2, 59900.00, NULL, NULL, NULL, '2026-09-08 14:05:00'),
(8, 8, 3, 129900.00, NULL, NULL, NULL, '2026-09-09 11:35:00'),
(9, 9, 1, 199900.00, NULL, NULL, NULL, '2026-09-10 17:05:00'),
(10, 10, 2, 249700.00, NULL, NULL, NULL, '2026-09-11 13:15:00'),
(11, 11, 4, 129900.00, NULL, NULL, NULL, '2026-09-12 10:45:00'),
(12, 12, 5, 259800.00, NULL, NULL, NULL, '2026-09-13 18:30:00'),
(13, 13, 1, 179700.00, NULL, NULL, NULL, '2026-09-14 10:35:00'),
(14, 14, 2, 259800.00, NULL, NULL, NULL, '2026-09-14 12:20:00'),
(15, 15, 1, 199900.00, NULL, NULL, NULL, '2026-09-14 08:51:00'),
(21, 21, 6, 399800.00, 'Tarjeta de crédito / débito', '•••• •••• •••• 1111', NULL, '2026-09-14 09:49:47'),
(22, 22, 2, 129900.00, 'Nequi', '•••••• 0214', NULL, '2026-09-14 09:50:22'),
(23, 23, 5, 199900.00, 'Contra entrega', 'Pago en Contra entrega', NULL, '2026-09-14 10:27:53'),
(24, 24, 5, 129900.00, 'Contra entrega', 'Pago en Contra entrega', NULL, '2026-09-14 10:28:40'),
(27, 27, 6, 199900.00, 'Tarjeta de crédito / débito', '•••• •••• •••• 1111', NULL, '2026-09-14 15:37:50');

--
-- Disparadores `pago`
--
DELIMITER $$
CREATE TRIGGER `trg_bloquear_pago_doble` BEFORE INSERT ON `pago` FOR EACH ROW BEGIN
    IF (SELECT COUNT(*) FROM pago WHERE ID_Venta = NEW.ID_Venta) > 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Venta ya fue pagada.';
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_descontar_stock_pago` AFTER INSERT ON `pago` FOR EACH ROW BEGIN
    UPDATE producto p
    JOIN detalle_venta dv ON p.ID_Producto = dv.ID_Producto
    SET p.Stock_Actual = p.Stock_Actual - dv.Cantidad
    WHERE dv.ID_Venta = NEW.ID_Venta;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_generar_factura_auto` AFTER INSERT ON `pago` FOR EACH ROW BEGIN
    DECLARE v_existe INT;
    DECLARE v_next INT;
    SELECT COUNT(*) INTO v_existe FROM factura WHERE ID_Venta = NEW.ID_Venta;
    IF v_existe = 0 THEN
        SELECT COALESCE(MAX(CAST(SUBSTRING(Numero_Factura, 5) AS UNSIGNED)), 0) + 1 INTO v_next FROM factura FOR UPDATE;
        INSERT INTO factura (Numero_Factura, ID_Venta)
        VALUES (CONCAT('FAC-', LPAD(v_next, 4, '0')), NEW.ID_Venta);
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_validar_metodo_pago` BEFORE INSERT ON `pago` FOR EACH ROW BEGIN
    IF NEW.Monto_Pagado <= 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Monto de pago inválido.';
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `password_resets`
--

CREATE TABLE `password_resets` (
  `ID_Reset` int(11) NOT NULL,
  `ID_Usuario` int(11) NOT NULL,
  `token_hash` char(64) NOT NULL,
  `expira_en` datetime NOT NULL,
  `usado_en` datetime DEFAULT NULL,
  `creado_en` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `password_resets`
--

INSERT INTO `password_resets` (`ID_Reset`, `ID_Usuario`, `token_hash`, `expira_en`, `usado_en`, `creado_en`) VALUES
(1, 1, 'bb0b91d40dfc62423516507f006d2c8555cd62bdc93ba85385d586afa37b37ef', '2026-09-14 08:53:54', '2026-09-14 08:39:13', '2026-09-14 08:38:54'),
(2, 1, 'b8c389e24626285669c441a9fed1d1f9aa97215d4aba185811fb830181f6da12', '2026-09-14 10:19:45', '2026-09-14 10:05:01', '2026-09-14 10:04:45'),
(3, 1, 'a71baa792396d2dce7fcbb063a915b11112c2fa334d830af32cdd6b4e31b3c50', '2026-09-14 10:24:15', '2026-09-14 10:09:36', '2026-09-14 10:09:15'),
(4, 10, 'e55174dcf2fd8f4ef0ec3235d60d1649584d6c9deb9b647726315b77aeb95ba2', '2026-09-14 10:26:24', '2026-09-14 10:11:43', '2026-09-14 10:11:24'),
(5, 12, '43981aeba1799c3cab0b491fe8f71827ef4be227c630426b862717d5ea026c48', '2026-09-14 10:30:59', '2026-09-14 10:16:18', '2026-09-14 10:15:59'),
(6, 1, '71bd0d57a46439ab60d2ced7dd934edb415e0ce88a1f2b401bc12f492787cfe9', '2026-09-14 11:27:02', '2026-09-14 11:12:26', '2026-09-14 11:12:02'),
(7, 1, '8f37ab630aaa54b93b7adcb8ebe9adfae7053165cdbc0a0c95baeb59bc9d4e57', '2026-09-14 14:02:01', '2026-09-14 13:47:37', '2026-09-14 13:47:01'),
(8, 10, 'ea4d485c0d328ee73c24f27b12e25b02a5cfb1f0088bedfd8b5d9a2f29698dda', '2026-09-14 15:30:21', '2026-09-14 15:15:40', '2026-09-14 15:15:21');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedido`
--

CREATE TABLE `pedido` (
  `ID_Pedido` int(11) NOT NULL,
  `ID_Venta` int(11) NOT NULL,
  `Direccion_Envio` varchar(255) NOT NULL,
  `Ciudad_Envio` int(11) NOT NULL,
  `Tipo_Envio` enum('Estándar','Express','Recogida en tienda') NOT NULL,
  `Estado_Pedido` enum('Pendiente','Pagado','Preparando','En camino','Entregado','Cancelado') NOT NULL DEFAULT 'Pendiente',
  `Motivo_Cancelacion` text DEFAULT NULL,
  `Guia_Seguimiento` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `pedido`
--

INSERT INTO `pedido` (`ID_Pedido`, `ID_Venta`, `Direccion_Envio`, `Ciudad_Envio`, `Tipo_Envio`, `Estado_Pedido`, `Motivo_Cancelacion`, `Guia_Seguimiento`) VALUES
(1, 13, 'Calle 45 #12-30, Bogotá', 1, 'Estándar', 'Cancelado', NULL, NULL),
(2, 14, 'Carrera 80 #25-15, Bogotá', 1, 'Express', 'Entregado', NULL, 'PRIORITARIO'),
(3, 12, 'Avenida 68 #90-10, Bogotá', 1, 'Estándar', 'Entregado', NULL, NULL),
(11, 27, 'Dirección por confirmar (Cliente #14)', 1, 'Estándar', 'Pagado', NULL, NULL);

--
-- Disparadores `pedido`
--
DELIMITER $$
CREATE TRIGGER `trg_asignar_guia_auto` BEFORE UPDATE ON `pedido` FOR EACH ROW BEGIN
    IF NEW.Estado_Pedido = 'En camino' AND NEW.Guia_Seguimiento IS NULL THEN
        SET NEW.Guia_Seguimiento = CONCAT('TRK-', LPAD(NEW.ID_Pedido, 6, '0'));
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_bloquear_cancelacion_enviado` BEFORE UPDATE ON `pedido` FOR EACH ROW BEGIN
    IF NEW.Estado_Pedido = 'Cancelado' AND OLD.Estado_Pedido IN ('En camino', 'Entregado') THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: No se puede cancelar pedido en tránsito o entregado.';
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_bloquear_pagado_sin_stock` BEFORE UPDATE ON `pedido` FOR EACH ROW BEGIN
    IF NEW.Estado_Pedido IN ('Pagado', 'Preparando') AND OLD.Estado_Pedido != NEW.Estado_Pedido THEN
        IF EXISTS (
            SELECT 1 FROM detalle_venta dv
            JOIN producto p ON p.ID_Producto = dv.ID_Producto
            WHERE dv.ID_Venta = NEW.ID_Venta AND dv.Cantidad > p.Stock_Actual
        ) THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Stock insuficiente para confirmar el pedido (RF 2.10).';
        END IF;
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_devolver_stock_cancelacion` AFTER UPDATE ON `pedido` FOR EACH ROW BEGIN
    IF NEW.Estado_Pedido = 'Cancelado' AND OLD.Estado_Pedido != 'Cancelado'
       AND OLD.Estado_Pedido IN ('Pagado', 'Preparando', 'En camino') THEN
        UPDATE producto p
        JOIN detalle_venta dv ON p.ID_Producto = dv.ID_Producto
        SET p.Stock_Actual = p.Stock_Actual + dv.Cantidad
        WHERE dv.ID_Venta = NEW.ID_Venta;
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_impedir_retroceso_estado` BEFORE UPDATE ON `pedido` FOR EACH ROW BEGIN
    IF OLD.Estado_Pedido = 'Entregado' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Pedido ya entregado.';
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_marcar_entregado` AFTER UPDATE ON `pedido` FOR EACH ROW BEGIN
    IF NEW.Estado_Pedido = 'Entregado' THEN
        INSERT INTO alertas_sistema (Tipo, Mensaje)
        VALUES ('ENTREGA', CONCAT('Pedido entregado ID: ', NEW.ID_Pedido));
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_marcar_prioritario` BEFORE INSERT ON `pedido` FOR EACH ROW BEGIN
    IF NEW.Tipo_Envio = 'Express' THEN
        SET NEW.Guia_Seguimiento = 'PRIORITARIO';
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_notificar_despacho` AFTER UPDATE ON `pedido` FOR EACH ROW BEGIN
    IF NEW.Estado_Pedido = 'En camino' AND OLD.Estado_Pedido != 'En camino' THEN
        INSERT INTO alertas_sistema (Tipo, Mensaje)
        VALUES ('DESPACHO', CONCAT('Pedido despachado ID: ', NEW.ID_Pedido));
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_secuencia_estados_pedido` BEFORE UPDATE ON `pedido` FOR EACH ROW BEGIN
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
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_validar_cobertura` BEFORE INSERT ON `pedido` FOR EACH ROW BEGIN
    IF NEW.Ciudad_Envio IS NULL THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Ciudad de envío requerida.';
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pqr`
--

CREATE TABLE `pqr` (
  `ID_Pqr` int(11) NOT NULL,
  `Fecha_Registro` datetime DEFAULT current_timestamp(),
  `Descripcion` text NOT NULL,
  `Tipo` enum('Queja','Reclamo','Solicitud') NOT NULL DEFAULT 'Solicitud',
  `Estado` enum('Abierto','En Proceso','Cerrado') DEFAULT 'Abierto',
  `ID_Cliente` int(11) NOT NULL,
  `ID_Empleado` int(11) DEFAULT NULL,
  `Respuesta` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `pqr`
--

INSERT INTO `pqr` (`ID_Pqr`, `Fecha_Registro`, `Descripcion`, `Tipo`, `Estado`, `ID_Cliente`, `ID_Empleado`, `Respuesta`) VALUES
(2, '2026-09-14 15:06:50', 'No muy mala tela', 'Queja', 'Cerrado', 1, NULL, 'TE MANDAMOS UNA CON MEJOR CALIDAD');

--
-- Disparadores `pqr`
--
DELIMITER $$
CREATE TRIGGER `trg_auditar_respuesta_pqr` BEFORE UPDATE ON `pqr` FOR EACH ROW BEGIN
    IF OLD.Estado <> NEW.Estado THEN
        INSERT INTO alertas_sistema (Tipo, Mensaje)
        VALUES ('PQR_UPDATE', CONCAT('PQR ID ', NEW.ID_Pqr, ' cambió a estado ', NEW.Estado));
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_auto_asignar_pqr` BEFORE INSERT ON `pqr` FOR EACH ROW BEGIN
    SET NEW.Estado = 'Abierto';
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `producto`
--

CREATE TABLE `producto` (
  `ID_Producto` int(11) NOT NULL,
  `Nombre_Producto` varchar(100) NOT NULL,
  `Precio_Actual` decimal(10,2) NOT NULL,
  `Stock_Actual` int(11) NOT NULL DEFAULT 0,
  `ID_Categoria` int(11) NOT NULL,
  `ID_Proveedor` int(11) NOT NULL,
  `Imagen_URL` varchar(512) DEFAULT NULL,
  `QR_Code_URL` varchar(512) DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `Stock_Minimo` int(11) NOT NULL DEFAULT 10
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `producto`
--

INSERT INTO `producto` (`ID_Producto`, `Nombre_Producto`, `Precio_Actual`, `Stock_Actual`, `ID_Categoria`, `ID_Proveedor`, `Imagen_URL`, `QR_Code_URL`, `deleted_at`, `Stock_Minimo`) VALUES
(1, 'Camiseta ACIDO Clásica', 59900.00, 38, 1, 1, NULL, NULL, NULL, 10),
(2, 'Pantalón ACIDO Urbano', 129900.00, 17, 2, 1, NULL, NULL, NULL, 10),
(3, 'Chaqueta ACIDO Oversize', 199900.00, 5, 3, 1, NULL, NULL, NULL, 10),
(4, 'Gorra ACIDO Bordada', 39900.00, 0, 4, 1, NULL, NULL, NULL, 10);

--
-- Disparadores `producto`
--
DELIMITER $$
CREATE TRIGGER `trg_alerta_stock_minimo` AFTER UPDATE ON `producto` FOR EACH ROW BEGIN
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
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_alerta_stock_minimo_insert` AFTER INSERT ON `producto` FOR EACH ROW BEGIN
    IF NEW.Stock_Actual <= NEW.Stock_Minimo THEN
        INSERT INTO alertas_sistema (Tipo, Mensaje, ID_Producto)
        VALUES ('STOCK_BAJO',
                CONCAT('Stock bajo: ', NEW.Nombre_Producto,
                       ' (', NEW.Stock_Actual, '/', NEW.Stock_Minimo, ')'),
                NEW.ID_Producto);
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_auditar_cambio_precio` AFTER UPDATE ON `producto` FOR EACH ROW BEGIN
    IF OLD.Precio_Actual <> NEW.Precio_Actual THEN
        INSERT INTO auditoria_precios (ID_Producto, Precio_Anterior, Precio_Nuevo)
        VALUES (NEW.ID_Producto, OLD.Precio_Actual, NEW.Precio_Actual);
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_bloquear_borrado_producto` BEFORE DELETE ON `producto` FOR EACH ROW BEGIN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: No se permite eliminar productos del catálogo.';
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_impedir_stock_negativo` BEFORE UPDATE ON `producto` FOR EACH ROW BEGIN
    IF NEW.Stock_Actual < 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Stock insuficiente.';
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_notificar_nuevo_producto` AFTER INSERT ON `producto` FOR EACH ROW BEGIN
    INSERT INTO alertas_sistema (Tipo, Mensaje)
    VALUES ('NUEVO_PRODUCTO', CONCAT('Producto registrado: ', NEW.Nombre_Producto));
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_notificar_restock` AFTER UPDATE ON `producto` FOR EACH ROW BEGIN
    IF OLD.Stock_Actual = 0 AND NEW.Stock_Actual > 0 THEN
        INSERT INTO alertas_sistema (Tipo, Mensaje)
        VALUES ('RESTOCK', CONCAT('Producto con nuevo stock: ', NEW.Nombre_Producto));
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_validar_precio_positivo` BEFORE INSERT ON `producto` FOR EACH ROW BEGIN
    IF NEW.Precio_Actual <= 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: El precio debe ser positivo.';
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proveedor`
--

CREATE TABLE `proveedor` (
  `ID_Proveedor` int(11) NOT NULL,
  `Nombre_Empresa` varchar(100) NOT NULL,
  `ID_Ciudad` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `proveedor`
--

INSERT INTO `proveedor` (`ID_Proveedor`, `Nombre_Empresa`, `ID_Ciudad`) VALUES
(1, 'Textiles ACIDO S.A.S.', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `resena_valoracion`
--

CREATE TABLE `resena_valoracion` (
  `ID_Resena` int(11) NOT NULL,
  `ID_Cliente` int(11) NOT NULL,
  `ID_Producto` int(11) NOT NULL,
  `Calificacion` int(11) NOT NULL CHECK (`Calificacion` between 1 and 5),
  `Comentario` text DEFAULT NULL,
  `Fecha_Publicacion` datetime DEFAULT current_timestamp(),
  `Estado_Moderacion` enum('Pendiente','Aprobado','Rechazado') DEFAULT 'Pendiente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `resena_valoracion`
--

INSERT INTO `resena_valoracion` (`ID_Resena`, `ID_Cliente`, `ID_Producto`, `Calificacion`, `Comentario`, `Fecha_Publicacion`, `Estado_Moderacion`) VALUES
(2, 1, 1, 1, 'MALA', '2026-09-14 15:06:58', 'Pendiente');

--
-- Disparadores `resena_valoracion`
--
DELIMITER $$
CREATE TRIGGER `trg_bloquear_resena_sin_compra` BEFORE INSERT ON `resena_valoracion` FOR EACH ROW BEGIN
    DECLARE v_compras INT;
    SELECT COUNT(*) INTO v_compras
    FROM detalle_venta dv
    JOIN venta v ON dv.ID_Venta = v.ID_Venta
    WHERE v.ID_Cliente = NEW.ID_Cliente AND dv.ID_Producto = NEW.ID_Producto;
    IF v_compras = 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Debe comprar la prenda para dejar una reseña.';
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_unicidad_resena_cliente` BEFORE INSERT ON `resena_valoracion` FOR EACH ROW BEGIN
    IF (SELECT COUNT(*) FROM resena_valoracion
        WHERE ID_Cliente = NEW.ID_Cliente AND ID_Producto = NEW.ID_Producto) > 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: El cliente ya reseñó este producto.';
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_validar_rango_estrellas` BEFORE INSERT ON `resena_valoracion` FOR EACH ROW BEGIN
    IF NEW.Calificacion < 1 OR NEW.Calificacion > 5 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Calificación fuera de rango (1-5).';
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

CREATE TABLE `usuario` (
  `ID_Usuario` int(11) NOT NULL,
  `Email` varchar(100) NOT NULL,
  `Seudonimo` varchar(50) DEFAULT NULL,
  `Foto` varchar(512) DEFAULT NULL,
  `Password_Hash` varchar(255) NOT NULL,
  `Rol` enum('Cliente','Empleado','Administrador') NOT NULL DEFAULT 'Cliente',
  `ID_Empleado` int(11) DEFAULT NULL,
  `ID_Cliente` int(11) DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`ID_Usuario`, `Email`, `Seudonimo`, `Foto`, `Password_Hash`, `Rol`, `ID_Empleado`, `ID_Cliente`, `deleted_at`) VALUES
(1, 'admin@acido.local', 'admin', NULL, '$2y$12$kbe/HRDKQBI37Qg0c9xjTOZRGckoh35LMEk1x9zPgOKFjxBH7X4DO', 'Administrador', NULL, 1, NULL),
(10, 'jhona@gmail.com', 'jhona', NULL, '$2y$12$J8JpvpAG3EmrIzZ9xP08guiiyT.Jl/rJqdYbfkyExvWktHkheY1Iq', 'Empleado', 2, 11, NULL),
(12, 'test.jhona.acido@gmail.com', 'jhonny', NULL, '$2y$12$oEC9UCfqZDH71k.amMYQveEa8Cs7xGLi.pqNkGJjowu0bBKk8.6dS', 'Cliente', NULL, 13, NULL),
(13, 'jhonatan@gmail.com', 'jhonatan', NULL, '$2y$12$f786Wp/bZrZ2QYDr9IcsAOaogS1xHcAOwctcho5F6GidWtZmCClUu', 'Cliente', NULL, 14, NULL),
(14, 'testbuy@acido.local', NULL, NULL, '$2y$12$XaIHlTy3OyYZnwajsqJA/.fyDxG6m4G9k9V3fwN/pjbRCKPqdYA0u', 'Cliente', NULL, 15, '2026-09-14 16:10:02');

--
-- Disparadores `usuario`
--
DELIMITER $$
CREATE TRIGGER `trg_auditar_cambio_rol` BEFORE UPDATE ON `usuario` FOR EACH ROW BEGIN
    IF OLD.Rol <> NEW.Rol THEN
        INSERT INTO alertas_sistema (Tipo, Mensaje)
        VALUES ('SEGURIDAD', CONCAT('Rol modificado para: ', NEW.Email));
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_hash_password_insert` BEFORE INSERT ON `usuario` FOR EACH ROW BEGIN
    IF NEW.Email NOT LIKE '%@%.%' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Formato de correo no válido.';
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_hash_password_update` BEFORE UPDATE ON `usuario` FOR EACH ROW BEGIN
    IF NEW.Email NOT LIKE '%@%.%' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Formato de correo no válido.';
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_registrar_acceso` AFTER INSERT ON `usuario` FOR EACH ROW BEGIN
    INSERT INTO control_accesos (ID_Usuario, Email) VALUES (NEW.ID_Usuario, NEW.Email);
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `venta`
--

CREATE TABLE `venta` (
  `ID_Venta` int(11) NOT NULL,
  `Fecha_Venta` datetime DEFAULT current_timestamp(),
  `ID_Cliente` int(11) NOT NULL,
  `ID_Empleado` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `venta`
--

INSERT INTO `venta` (`ID_Venta`, `Fecha_Venta`, `ID_Cliente`, `ID_Empleado`) VALUES
(1, '2026-04-10 11:00:00', 1, NULL),
(2, '2026-05-12 15:30:00', 2, NULL),
(3, '2026-06-15 10:00:00', 3, NULL),
(4, '2026-07-20 16:45:00', 2, NULL),
(5, '2026-08-18 12:20:00', 3, NULL),
(6, '2026-09-05 09:15:00', 1, NULL),
(7, '2026-09-08 14:00:00', 2, NULL),
(8, '2026-09-09 11:30:00', 3, NULL),
(9, '2026-09-10 17:00:00', 1, NULL),
(10, '2026-09-11 13:10:00', 2, NULL),
(11, '2026-09-12 10:40:00', 3, NULL),
(12, '2026-09-13 18:25:00', 1, NULL),
(13, '2026-09-14 10:30:00', 2, NULL),
(14, '2026-09-14 12:15:00', 3, NULL),
(15, '2026-09-14 08:51:00', 1, NULL),
(21, '2026-09-14 09:49:47', 1, NULL),
(22, '2026-09-14 09:50:22', 1, NULL),
(23, '2026-09-14 10:27:53', 1, NULL),
(24, '2026-09-14 10:28:40', 1, NULL),
(27, '2026-09-14 15:37:50', 14, NULL);

--
-- Disparadores `venta`
--
DELIMITER $$
CREATE TRIGGER `trg_auditar_anulacion` BEFORE UPDATE ON `venta` FOR EACH ROW BEGIN
    INSERT INTO alertas_sistema (Tipo, Mensaje)
    VALUES ('VENTA_MOD', CONCAT('Venta alterada ID: ', NEW.ID_Venta));
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `v_ajustes_kardex`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `v_ajustes_kardex` (
`ID_Movimiento` int(11)
,`ID_Producto` int(11)
,`Tipo_Movimiento` enum('Entrada','Salida','Ajuste')
,`Cantidad` int(11)
,`Motivo` varchar(255)
,`Fecha_Movimiento` datetime
,`ID_Empleado` int(11)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `v_alertas_stock_cero`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `v_alertas_stock_cero` (
`ID_Producto` int(11)
,`Nombre_Producto` varchar(100)
,`Precio_Actual` decimal(10,2)
,`Stock_Actual` int(11)
,`ID_Categoria` int(11)
,`ID_Proveedor` int(11)
,`Imagen_URL` varchar(512)
,`QR_Code_URL` varchar(512)
,`deleted_at` datetime
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `v_auditoria_precios`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `v_auditoria_precios` (
`ID_Auditoria` int(11)
,`ID_Producto` int(11)
,`Precio_Anterior` decimal(10,2)
,`Precio_Nuevo` decimal(10,2)
,`Fecha_Cambio` datetime
,`Usuario_Responsable` varchar(100)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `v_auditoria_roles`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `v_auditoria_roles` (
`Email` varchar(100)
,`Rol` enum('Cliente','Empleado','Administrador')
,`Nombres` varchar(50)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `v_cancelaciones_mes`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `v_cancelaciones_mes` (
`ID_Pedido` int(11)
,`ID_Venta` int(11)
,`Direccion_Envio` varchar(255)
,`Ciudad_Envio` int(11)
,`Tipo_Envio` enum('Estándar','Express','Recogida en tienda')
,`Estado_Pedido` enum('Pendiente','Pagado','Preparando','En camino','Entregado','Cancelado')
,`Guia_Seguimiento` varchar(50)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `v_carrito_actual`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `v_carrito_actual` (
`ID_Cliente` int(11)
,`ID_Detalle` int(11)
,`ID_Venta` int(11)
,`ID_Producto` int(11)
,`Cantidad` int(11)
,`Precio_Venta_Historico` decimal(10,2)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `v_catalogo_agotados`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `v_catalogo_agotados` (
`ID_Producto` int(11)
,`Nombre_Producto` varchar(100)
,`Precio_Actual` decimal(10,2)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `v_catalogo_optimizado`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `v_catalogo_optimizado` (
`ID_Producto` int(11)
,`Nombre_Producto` varchar(100)
,`Precio_Actual` decimal(10,2)
,`Stock_Actual` int(11)
,`Imagen_URL` varchar(512)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `v_ciudades_cobertura`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `v_ciudades_cobertura` (
`ID_Ciudad` int(11)
,`Nombre_Ciudad` varchar(100)
,`ID_Departamento` int(11)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `v_clientes_inactivos`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `v_clientes_inactivos` (
`ID_Cliente` int(11)
,`Nombres` varchar(70)
,`Apellidos` varchar(70)
,`Documento` varchar(20)
,`Telefono` varchar(15)
,`deleted_at` datetime
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `v_clientes_top`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `v_clientes_top` (
`ID_Cliente` int(11)
,`Nombres` varchar(70)
,`Apellidos` varchar(70)
,`Compras` bigint(21)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `v_departamentos_cobertura`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `v_departamentos_cobertura` (
`ID_Departamento` int(11)
,`Nombre_Departamento` varchar(100)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `v_empleados_inactivos`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `v_empleados_inactivos` (
`ID_Empleado` int(11)
,`Nombres` varchar(50)
,`Apellidos` varchar(50)
,`ID_Cargo` int(11)
,`ID_Ciudad` int(11)
,`deleted_at` datetime
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `v_envios_pendientes`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `v_envios_pendientes` (
`ID_Pedido` int(11)
,`ID_Venta` int(11)
,`Direccion_Envio` varchar(255)
,`Ciudad_Envio` int(11)
,`Tipo_Envio` enum('Estándar','Express','Recogida en tienda')
,`Estado_Pedido` enum('Pendiente','Pagado','Preparando','En camino','Entregado','Cancelado')
,`Motivo_Cancelacion` text
,`Guia_Seguimiento` varchar(50)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `v_facturacion_global`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `v_facturacion_global` (
`Numero_Factura` varchar(20)
,`Fecha_Emision` datetime
,`Total` decimal(32,2)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `v_facturas_cliente`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `v_facturas_cliente` (
`ID_Cliente` int(11)
,`ID_Factura` int(11)
,`Numero_Factura` varchar(20)
,`Fecha_Emision` datetime
,`ID_Venta` int(11)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `v_impuestos_recaudados`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `v_impuestos_recaudados` (
`IVA_Total` decimal(35,4)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `v_ingresos_categoria`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `v_ingresos_categoria` (
`Nombre_Categoria` varchar(50)
,`Total` decimal(42,2)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `v_intentos_fallidos`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `v_intentos_fallidos` (
`ID_Control` int(11)
,`ID_Usuario` int(11)
,`Email` varchar(100)
,`Intentos_Fallidos` int(11)
,`Ultimo_Intento` datetime
,`Bloqueado_Hasta` datetime
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `v_inventario_general`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `v_inventario_general` (
`ID_Producto` int(11)
,`Nombre_Producto` varchar(100)
,`Stock_Actual` int(11)
,`Nombre_Categoria` varchar(50)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `v_lista_espera_activa`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `v_lista_espera_activa` (
`ID_Espera` int(11)
,`Nombre_Completo` varchar(150)
,`Email` varchar(100)
,`ID_Producto` int(11)
,`Fecha_Registro` datetime
,`Notificado` tinyint(1)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `v_log_errores`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `v_log_errores` (
`ID_Alerta` int(11)
,`Tipo` varchar(50)
,`Mensaje` text
,`Fecha_Creacion` datetime
,`Leido` tinyint(1)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `v_mejor_calificados`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `v_mejor_calificados` (
`Nombre_Producto` varchar(100)
,`Promedio` decimal(14,4)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `v_metodos_pago_uso`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `v_metodos_pago_uso` (
`Tipo_Metodo` varchar(50)
,`Uso` bigint(21)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `v_mis_direcciones`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `v_mis_direcciones` (
`ID_Direccion` int(11)
,`ID_Cliente` int(11)
,`Alias` varchar(50)
,`ID_Ciudad` int(11)
,`Codigo_Postal` varchar(10)
,`Direccion_Exacta` varchar(255)
,`Referencias` varchar(255)
,`Es_Principal` tinyint(1)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `v_mis_metodos_pago`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `v_mis_metodos_pago` (
`ID_Metodo` int(11)
,`Tipo_Metodo` varchar(50)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `v_mis_pedidos_activos`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `v_mis_pedidos_activos` (
`ID_Cliente` int(11)
,`ID_Pedido` int(11)
,`ID_Venta` int(11)
,`Direccion_Envio` varchar(255)
,`Ciudad_Envio` int(11)
,`Tipo_Envio` enum('Estándar','Express','Recogida en tienda')
,`Estado_Pedido` enum('Pendiente','Pagado','Preparando','En camino','Entregado','Cancelado')
,`Guia_Seguimiento` varchar(50)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `v_mis_pqrs`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `v_mis_pqrs` (
`ID_Pqr` int(11)
,`Fecha_Registro` datetime
,`Descripcion` text
,`Estado` enum('Abierto','En Proceso','Cerrado')
,`ID_Cliente` int(11)
,`ID_Empleado` int(11)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `v_mis_resenas`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `v_mis_resenas` (
`ID_Resena` int(11)
,`ID_Cliente` int(11)
,`ID_Producto` int(11)
,`Calificacion` int(11)
,`Comentario` text
,`Fecha_Publicacion` datetime
,`Estado_Moderacion` enum('Pendiente','Aprobado','Rechazado')
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `v_mi_direccion_principal`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `v_mi_direccion_principal` (
`ID_Direccion` int(11)
,`ID_Cliente` int(11)
,`Alias` varchar(50)
,`ID_Ciudad` int(11)
,`Codigo_Postal` varchar(10)
,`Direccion_Exacta` varchar(255)
,`Referencias` varchar(255)
,`Es_Principal` tinyint(1)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `v_mi_historial_compras`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `v_mi_historial_compras` (
`ID_Cliente` int(11)
,`ID_Venta` int(11)
,`Fecha_Venta` datetime
,`Numero_Factura` varchar(20)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `v_mi_wishlist`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `v_mi_wishlist` (
`ID_Cliente` int(11)
,`Nombre_Producto` varchar(100)
,`Precio_Actual` decimal(10,2)
,`Estado` varchar(10)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `v_novedades`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `v_novedades` (
`ID_Producto` int(11)
,`Nombre_Producto` varchar(100)
,`Precio_Actual` decimal(10,2)
,`Stock_Actual` int(11)
,`ID_Categoria` int(11)
,`ID_Proveedor` int(11)
,`Imagen_URL` varchar(512)
,`QR_Code_URL` varchar(512)
,`deleted_at` datetime
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `v_ofertas`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `v_ofertas` (
`ID_Producto` int(11)
,`Nombre_Producto` varchar(100)
,`Precio_Actual` decimal(10,2)
,`Stock_Actual` int(11)
,`ID_Categoria` int(11)
,`ID_Proveedor` int(11)
,`Imagen_URL` varchar(512)
,`QR_Code_URL` varchar(512)
,`deleted_at` datetime
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `v_pedidos_entregados`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `v_pedidos_entregados` (
`ID_Pedido` int(11)
,`ID_Venta` int(11)
,`Direccion_Envio` varchar(255)
,`Ciudad_Envio` int(11)
,`Tipo_Envio` enum('Estándar','Express','Recogida en tienda')
,`Estado_Pedido` enum('Pendiente','Pagado','Preparando','En camino','Entregado','Cancelado')
,`Guia_Seguimiento` varchar(50)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `v_pqrs_vencidas`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `v_pqrs_vencidas` (
`ID_Pqr` int(11)
,`Fecha_Registro` datetime
,`Descripcion` text
,`Estado` enum('Abierto','En Proceso','Cerrado')
,`ID_Cliente` int(11)
,`ID_Empleado` int(11)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `v_productos_por_proveedor`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `v_productos_por_proveedor` (
`Nombre_Empresa` varchar(100)
,`Total_Productos` bigint(21)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `v_proveedores_activos`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `v_proveedores_activos` (
`ID_Proveedor` int(11)
,`Nombre_Empresa` varchar(100)
,`ID_Ciudad` int(11)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `v_rendimiento_empleados`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `v_rendimiento_empleados` (
`Nombres` varchar(50)
,`Apellidos` varchar(50)
,`Atendidos` bigint(21)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `v_resenas_pendientes`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `v_resenas_pendientes` (
`ID_Resena` int(11)
,`ID_Cliente` int(11)
,`ID_Producto` int(11)
,`Calificacion` int(11)
,`Comentario` text
,`Fecha_Publicacion` datetime
,`Estado_Moderacion` enum('Pendiente','Aprobado','Rechazado')
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `v_resenas_rechazadas`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `v_resenas_rechazadas` (
`ID_Resena` int(11)
,`ID_Cliente` int(11)
,`ID_Producto` int(11)
,`Calificacion` int(11)
,`Comentario` text
,`Fecha_Publicacion` datetime
,`Estado_Moderacion` enum('Pendiente','Aprobado','Rechazado')
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `v_rutas_envio`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `v_rutas_envio` (
`ID_Pedido` int(11)
,`Nombre_Ciudad` varchar(100)
,`Nombre_Departamento` varchar(100)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `v_sesiones_activas`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `v_sesiones_activas` (
`ID_Usuario` int(11)
,`Email` varchar(100)
,`Ultimo_Intento` datetime
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `v_tickets_promedio`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `v_tickets_promedio` (
`Promedio_Venta` decimal(14,6)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `v_top_productos`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `v_top_productos` (
`Nombre_Producto` varchar(100)
,`Vendidos` decimal(32,0)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `v_usuarios_bloqueados`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `v_usuarios_bloqueados` (
`ID_Control` int(11)
,`ID_Usuario` int(11)
,`Email` varchar(100)
,`Intentos_Fallidos` int(11)
,`Ultimo_Intento` datetime
,`Bloqueado_Hasta` datetime
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `v_valorizacion_stock`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `v_valorizacion_stock` (
`Valor_Total_Inventario` decimal(42,2)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `v_ventas_diarias`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `v_ventas_diarias` (
`Fecha` date
,`Total_Ventas` bigint(21)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `v_ventas_mensuales`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `v_ventas_mensuales` (
`Mes` varchar(7)
,`Total_Ventas` bigint(21)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `v_ventas_por_ciudad`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `v_ventas_por_ciudad` (
`Nombre_Ciudad` varchar(100)
,`Ventas` bigint(21)
);

-- --------------------------------------------------------

--
-- Estructura para la vista `v_ajustes_kardex`
--
DROP TABLE IF EXISTS `v_ajustes_kardex`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_ajustes_kardex`  AS SELECT `movimiento_inventario`.`ID_Movimiento` AS `ID_Movimiento`, `movimiento_inventario`.`ID_Producto` AS `ID_Producto`, `movimiento_inventario`.`Tipo_Movimiento` AS `Tipo_Movimiento`, `movimiento_inventario`.`Cantidad` AS `Cantidad`, `movimiento_inventario`.`Motivo` AS `Motivo`, `movimiento_inventario`.`Fecha_Movimiento` AS `Fecha_Movimiento`, `movimiento_inventario`.`ID_Empleado` AS `ID_Empleado` FROM `movimiento_inventario` WHERE `movimiento_inventario`.`Tipo_Movimiento` = 'Ajuste' ;

-- --------------------------------------------------------

--
-- Estructura para la vista `v_alertas_stock_cero`
--
DROP TABLE IF EXISTS `v_alertas_stock_cero`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_alertas_stock_cero`  AS SELECT `producto`.`ID_Producto` AS `ID_Producto`, `producto`.`Nombre_Producto` AS `Nombre_Producto`, `producto`.`Precio_Actual` AS `Precio_Actual`, `producto`.`Stock_Actual` AS `Stock_Actual`, `producto`.`ID_Categoria` AS `ID_Categoria`, `producto`.`ID_Proveedor` AS `ID_Proveedor`, `producto`.`Imagen_URL` AS `Imagen_URL`, `producto`.`QR_Code_URL` AS `QR_Code_URL`, `producto`.`deleted_at` AS `deleted_at` FROM `producto` WHERE `producto`.`Stock_Actual` = 0 AND `producto`.`deleted_at` is null ;

-- --------------------------------------------------------

--
-- Estructura para la vista `v_auditoria_precios`
--
DROP TABLE IF EXISTS `v_auditoria_precios`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_auditoria_precios`  AS SELECT `auditoria_precios`.`ID_Auditoria` AS `ID_Auditoria`, `auditoria_precios`.`ID_Producto` AS `ID_Producto`, `auditoria_precios`.`Precio_Anterior` AS `Precio_Anterior`, `auditoria_precios`.`Precio_Nuevo` AS `Precio_Nuevo`, `auditoria_precios`.`Fecha_Cambio` AS `Fecha_Cambio`, `auditoria_precios`.`Usuario_Responsable` AS `Usuario_Responsable` FROM `auditoria_precios` ORDER BY `auditoria_precios`.`Fecha_Cambio` DESC ;

-- --------------------------------------------------------

--
-- Estructura para la vista `v_auditoria_roles`
--
DROP TABLE IF EXISTS `v_auditoria_roles`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_auditoria_roles`  AS SELECT `u`.`Email` AS `Email`, `u`.`Rol` AS `Rol`, `e`.`Nombres` AS `Nombres` FROM (`usuario` `u` left join `empleado` `e` on(`u`.`ID_Empleado` = `e`.`ID_Empleado`)) WHERE `u`.`deleted_at` is null ;

-- --------------------------------------------------------

--
-- Estructura para la vista `v_cancelaciones_mes`
--
DROP TABLE IF EXISTS `v_cancelaciones_mes`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_cancelaciones_mes`  AS SELECT `pedido`.`ID_Pedido` AS `ID_Pedido`, `pedido`.`ID_Venta` AS `ID_Venta`, `pedido`.`Direccion_Envio` AS `Direccion_Envio`, `pedido`.`Ciudad_Envio` AS `Ciudad_Envio`, `pedido`.`Tipo_Envio` AS `Tipo_Envio`, `pedido`.`Estado_Pedido` AS `Estado_Pedido`, `pedido`.`Guia_Seguimiento` AS `Guia_Seguimiento` FROM `pedido` WHERE `pedido`.`Estado_Pedido` = 'Cancelado' ;

-- --------------------------------------------------------

--
-- Estructura para la vista `v_carrito_actual`
--
DROP TABLE IF EXISTS `v_carrito_actual`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_carrito_actual`  AS SELECT `v`.`ID_Cliente` AS `ID_Cliente`, `dv`.`ID_Detalle` AS `ID_Detalle`, `dv`.`ID_Venta` AS `ID_Venta`, `dv`.`ID_Producto` AS `ID_Producto`, `dv`.`Cantidad` AS `Cantidad`, `dv`.`Precio_Venta_Historico` AS `Precio_Venta_Historico` FROM (`detalle_venta` `dv` join `venta` `v` on(`dv`.`ID_Venta` = `v`.`ID_Venta`)) ;

-- --------------------------------------------------------

--
-- Estructura para la vista `v_catalogo_agotados`
--
DROP TABLE IF EXISTS `v_catalogo_agotados`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_catalogo_agotados`  AS SELECT `producto`.`ID_Producto` AS `ID_Producto`, `producto`.`Nombre_Producto` AS `Nombre_Producto`, `producto`.`Precio_Actual` AS `Precio_Actual` FROM `producto` WHERE `producto`.`Stock_Actual` = 0 AND `producto`.`deleted_at` is null ;

-- --------------------------------------------------------

--
-- Estructura para la vista `v_catalogo_optimizado`
--
DROP TABLE IF EXISTS `v_catalogo_optimizado`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_catalogo_optimizado`  AS SELECT `producto`.`ID_Producto` AS `ID_Producto`, `producto`.`Nombre_Producto` AS `Nombre_Producto`, `producto`.`Precio_Actual` AS `Precio_Actual`, `producto`.`Stock_Actual` AS `Stock_Actual`, `producto`.`Imagen_URL` AS `Imagen_URL` FROM `producto` WHERE `producto`.`Stock_Actual` > 0 AND `producto`.`deleted_at` is null ;

-- --------------------------------------------------------

--
-- Estructura para la vista `v_ciudades_cobertura`
--
DROP TABLE IF EXISTS `v_ciudades_cobertura`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_ciudades_cobertura`  AS SELECT `ciudad`.`ID_Ciudad` AS `ID_Ciudad`, `ciudad`.`Nombre_Ciudad` AS `Nombre_Ciudad`, `ciudad`.`ID_Departamento` AS `ID_Departamento` FROM `ciudad` ;

-- --------------------------------------------------------

--
-- Estructura para la vista `v_clientes_inactivos`
--
DROP TABLE IF EXISTS `v_clientes_inactivos`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_clientes_inactivos`  AS SELECT `c`.`ID_Cliente` AS `ID_Cliente`, `c`.`Nombres` AS `Nombres`, `c`.`Apellidos` AS `Apellidos`, `c`.`Documento` AS `Documento`, `c`.`Telefono` AS `Telefono`, `c`.`deleted_at` AS `deleted_at` FROM (`cliente` `c` left join `venta` `v` on(`c`.`ID_Cliente` = `v`.`ID_Cliente`)) WHERE `v`.`ID_Venta` is null AND `c`.`deleted_at` is null ;

-- --------------------------------------------------------

--
-- Estructura para la vista `v_clientes_top`
--
DROP TABLE IF EXISTS `v_clientes_top`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_clientes_top`  AS SELECT `c`.`ID_Cliente` AS `ID_Cliente`, `c`.`Nombres` AS `Nombres`, `c`.`Apellidos` AS `Apellidos`, count(`v`.`ID_Venta`) AS `Compras` FROM (`cliente` `c` join `venta` `v` on(`c`.`ID_Cliente` = `v`.`ID_Cliente`)) WHERE `c`.`deleted_at` is null GROUP BY `c`.`ID_Cliente` ORDER BY count(`v`.`ID_Venta`) DESC ;

-- --------------------------------------------------------

--
-- Estructura para la vista `v_departamentos_cobertura`
--
DROP TABLE IF EXISTS `v_departamentos_cobertura`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_departamentos_cobertura`  AS SELECT `departamento`.`ID_Departamento` AS `ID_Departamento`, `departamento`.`Nombre_Departamento` AS `Nombre_Departamento` FROM `departamento` ;

-- --------------------------------------------------------

--
-- Estructura para la vista `v_empleados_inactivos`
--
DROP TABLE IF EXISTS `v_empleados_inactivos`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_empleados_inactivos`  AS SELECT `e`.`ID_Empleado` AS `ID_Empleado`, `e`.`Nombres` AS `Nombres`, `e`.`Apellidos` AS `Apellidos`, `e`.`ID_Cargo` AS `ID_Cargo`, `e`.`ID_Ciudad` AS `ID_Ciudad`, `e`.`deleted_at` AS `deleted_at` FROM (`empleado` `e` left join `usuario` `u` on(`e`.`ID_Empleado` = `u`.`ID_Empleado`)) WHERE `u`.`ID_Usuario` is null AND `e`.`deleted_at` is null ;

-- --------------------------------------------------------

--
-- Estructura para la vista `v_envios_pendientes`
--
DROP TABLE IF EXISTS `v_envios_pendientes`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_envios_pendientes`  AS SELECT `pedido`.`ID_Pedido` AS `ID_Pedido`, `pedido`.`ID_Venta` AS `ID_Venta`, `pedido`.`Direccion_Envio` AS `Direccion_Envio`, `pedido`.`Ciudad_Envio` AS `Ciudad_Envio`, `pedido`.`Tipo_Envio` AS `Tipo_Envio`, `pedido`.`Estado_Pedido` AS `Estado_Pedido`, `pedido`.`Motivo_Cancelacion` AS `Motivo_Cancelacion`, `pedido`.`Guia_Seguimiento` AS `Guia_Seguimiento` FROM `pedido` WHERE `pedido`.`Estado_Pedido` in ('Pendiente','Pagado','Preparando','En camino') ;

-- --------------------------------------------------------

--
-- Estructura para la vista `v_facturacion_global`
--
DROP TABLE IF EXISTS `v_facturacion_global`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_facturacion_global`  AS SELECT `f`.`Numero_Factura` AS `Numero_Factura`, `f`.`Fecha_Emision` AS `Fecha_Emision`, sum(`p`.`Monto_Pagado`) AS `Total` FROM (`factura` `f` join `pago` `p` on(`f`.`ID_Venta` = `p`.`ID_Venta`)) GROUP BY `f`.`ID_Factura` ;

-- --------------------------------------------------------

--
-- Estructura para la vista `v_facturas_cliente`
--
DROP TABLE IF EXISTS `v_facturas_cliente`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_facturas_cliente`  AS SELECT `v`.`ID_Cliente` AS `ID_Cliente`, `f`.`ID_Factura` AS `ID_Factura`, `f`.`Numero_Factura` AS `Numero_Factura`, `f`.`Fecha_Emision` AS `Fecha_Emision`, `f`.`ID_Venta` AS `ID_Venta` FROM (`factura` `f` join `venta` `v` on(`f`.`ID_Venta` = `v`.`ID_Venta`)) ;

-- --------------------------------------------------------

--
-- Estructura para la vista `v_impuestos_recaudados`
--
DROP TABLE IF EXISTS `v_impuestos_recaudados`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_impuestos_recaudados`  AS SELECT sum(`pago`.`Monto_Pagado` * 0.19) AS `IVA_Total` FROM `pago` ;

-- --------------------------------------------------------

--
-- Estructura para la vista `v_ingresos_categoria`
--
DROP TABLE IF EXISTS `v_ingresos_categoria`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_ingresos_categoria`  AS SELECT `c`.`Nombre_Categoria` AS `Nombre_Categoria`, sum(`dv`.`Cantidad` * `dv`.`Precio_Venta_Historico`) AS `Total` FROM ((`detalle_venta` `dv` join `producto` `p` on(`dv`.`ID_Producto` = `p`.`ID_Producto`)) join `categoria` `c` on(`p`.`ID_Categoria` = `c`.`ID_Categoria`)) WHERE `p`.`deleted_at` is null GROUP BY `c`.`ID_Categoria` ;

-- --------------------------------------------------------

--
-- Estructura para la vista `v_intentos_fallidos`
--
DROP TABLE IF EXISTS `v_intentos_fallidos`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_intentos_fallidos`  AS SELECT `ca`.`ID_Control` AS `ID_Control`, `ca`.`ID_Usuario` AS `ID_Usuario`, `u`.`Email` AS `Email`, `ca`.`Intentos_Fallidos` AS `Intentos_Fallidos`, `ca`.`Ultimo_Intento` AS `Ultimo_Intento`, `ca`.`Bloqueado_Hasta` AS `Bloqueado_Hasta` FROM (`control_accesos` `ca` join `usuario` `u` on(`ca`.`ID_Usuario` = `u`.`ID_Usuario`)) WHERE `ca`.`Intentos_Fallidos` > 0 ;

-- --------------------------------------------------------

--
-- Estructura para la vista `v_inventario_general`
--
DROP TABLE IF EXISTS `v_inventario_general`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_inventario_general`  AS SELECT `p`.`ID_Producto` AS `ID_Producto`, `p`.`Nombre_Producto` AS `Nombre_Producto`, `p`.`Stock_Actual` AS `Stock_Actual`, `c`.`Nombre_Categoria` AS `Nombre_Categoria` FROM (`producto` `p` join `categoria` `c` on(`p`.`ID_Categoria` = `c`.`ID_Categoria`)) WHERE `p`.`deleted_at` is null ;

-- --------------------------------------------------------

--
-- Estructura para la vista `v_lista_espera_activa`
--
DROP TABLE IF EXISTS `v_lista_espera_activa`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_lista_espera_activa`  AS SELECT `lista_espera_stock`.`ID_Espera` AS `ID_Espera`, `lista_espera_stock`.`Nombre_Completo` AS `Nombre_Completo`, `lista_espera_stock`.`Email` AS `Email`, `lista_espera_stock`.`ID_Producto` AS `ID_Producto`, `lista_espera_stock`.`Fecha_Registro` AS `Fecha_Registro`, `lista_espera_stock`.`Notificado` AS `Notificado` FROM `lista_espera_stock` WHERE `lista_espera_stock`.`Notificado` = 0 ;

-- --------------------------------------------------------

--
-- Estructura para la vista `v_log_errores`
--
DROP TABLE IF EXISTS `v_log_errores`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_log_errores`  AS SELECT `alertas_sistema`.`ID_Alerta` AS `ID_Alerta`, `alertas_sistema`.`Tipo` AS `Tipo`, `alertas_sistema`.`Mensaje` AS `Mensaje`, `alertas_sistema`.`Fecha_Creacion` AS `Fecha_Creacion`, `alertas_sistema`.`Leido` AS `Leido` FROM `alertas_sistema` WHERE `alertas_sistema`.`Tipo` = 'ERROR' ;

-- --------------------------------------------------------

--
-- Estructura para la vista `v_mejor_calificados`
--
DROP TABLE IF EXISTS `v_mejor_calificados`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_mejor_calificados`  AS SELECT `p`.`Nombre_Producto` AS `Nombre_Producto`, avg(`rv`.`Calificacion`) AS `Promedio` FROM (`resena_valoracion` `rv` join `producto` `p` on(`rv`.`ID_Producto` = `p`.`ID_Producto`)) WHERE `p`.`deleted_at` is null GROUP BY `p`.`ID_Producto` HAVING `Promedio` >= 4 ;

-- --------------------------------------------------------

--
-- Estructura para la vista `v_metodos_pago_uso`
--
DROP TABLE IF EXISTS `v_metodos_pago_uso`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_metodos_pago_uso`  AS SELECT `mp`.`Tipo_Metodo` AS `Tipo_Metodo`, count(`p`.`ID_Pago`) AS `Uso` FROM (`pago` `p` join `metodo_pago` `mp` on(`p`.`ID_Metodo` = `mp`.`ID_Metodo`)) GROUP BY `mp`.`ID_Metodo` ;

-- --------------------------------------------------------

--
-- Estructura para la vista `v_mis_direcciones`
--
DROP TABLE IF EXISTS `v_mis_direcciones`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_mis_direcciones`  AS SELECT `libreta_direcciones`.`ID_Direccion` AS `ID_Direccion`, `libreta_direcciones`.`ID_Cliente` AS `ID_Cliente`, `libreta_direcciones`.`Alias` AS `Alias`, `libreta_direcciones`.`ID_Ciudad` AS `ID_Ciudad`, `libreta_direcciones`.`Codigo_Postal` AS `Codigo_Postal`, `libreta_direcciones`.`Direccion_Exacta` AS `Direccion_Exacta`, `libreta_direcciones`.`Referencias` AS `Referencias`, `libreta_direcciones`.`Es_Principal` AS `Es_Principal` FROM `libreta_direcciones` ;

-- --------------------------------------------------------

--
-- Estructura para la vista `v_mis_metodos_pago`
--
DROP TABLE IF EXISTS `v_mis_metodos_pago`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_mis_metodos_pago`  AS SELECT `metodo_pago`.`ID_Metodo` AS `ID_Metodo`, `metodo_pago`.`Tipo_Metodo` AS `Tipo_Metodo` FROM `metodo_pago` ;

-- --------------------------------------------------------

--
-- Estructura para la vista `v_mis_pedidos_activos`
--
DROP TABLE IF EXISTS `v_mis_pedidos_activos`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_mis_pedidos_activos`  AS SELECT `v`.`ID_Cliente` AS `ID_Cliente`, `p`.`ID_Pedido` AS `ID_Pedido`, `p`.`ID_Venta` AS `ID_Venta`, `p`.`Direccion_Envio` AS `Direccion_Envio`, `p`.`Ciudad_Envio` AS `Ciudad_Envio`, `p`.`Tipo_Envio` AS `Tipo_Envio`, `p`.`Estado_Pedido` AS `Estado_Pedido`, `p`.`Guia_Seguimiento` AS `Guia_Seguimiento` FROM (`pedido` `p` join `venta` `v` on(`p`.`ID_Venta` = `v`.`ID_Venta`)) WHERE `p`.`Estado_Pedido` <> 'Entregado' ;

-- --------------------------------------------------------

--
-- Estructura para la vista `v_mis_pqrs`
--
DROP TABLE IF EXISTS `v_mis_pqrs`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_mis_pqrs`  AS SELECT `pqr`.`ID_Pqr` AS `ID_Pqr`, `pqr`.`Fecha_Registro` AS `Fecha_Registro`, `pqr`.`Descripcion` AS `Descripcion`, `pqr`.`Estado` AS `Estado`, `pqr`.`ID_Cliente` AS `ID_Cliente`, `pqr`.`ID_Empleado` AS `ID_Empleado` FROM `pqr` ;

-- --------------------------------------------------------

--
-- Estructura para la vista `v_mis_resenas`
--
DROP TABLE IF EXISTS `v_mis_resenas`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_mis_resenas`  AS SELECT `resena_valoracion`.`ID_Resena` AS `ID_Resena`, `resena_valoracion`.`ID_Cliente` AS `ID_Cliente`, `resena_valoracion`.`ID_Producto` AS `ID_Producto`, `resena_valoracion`.`Calificacion` AS `Calificacion`, `resena_valoracion`.`Comentario` AS `Comentario`, `resena_valoracion`.`Fecha_Publicacion` AS `Fecha_Publicacion`, `resena_valoracion`.`Estado_Moderacion` AS `Estado_Moderacion` FROM `resena_valoracion` ;

-- --------------------------------------------------------

--
-- Estructura para la vista `v_mi_direccion_principal`
--
DROP TABLE IF EXISTS `v_mi_direccion_principal`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_mi_direccion_principal`  AS SELECT `libreta_direcciones`.`ID_Direccion` AS `ID_Direccion`, `libreta_direcciones`.`ID_Cliente` AS `ID_Cliente`, `libreta_direcciones`.`Alias` AS `Alias`, `libreta_direcciones`.`ID_Ciudad` AS `ID_Ciudad`, `libreta_direcciones`.`Codigo_Postal` AS `Codigo_Postal`, `libreta_direcciones`.`Direccion_Exacta` AS `Direccion_Exacta`, `libreta_direcciones`.`Referencias` AS `Referencias`, `libreta_direcciones`.`Es_Principal` AS `Es_Principal` FROM `libreta_direcciones` WHERE `libreta_direcciones`.`Es_Principal` = 1 ;

-- --------------------------------------------------------

--
-- Estructura para la vista `v_mi_historial_compras`
--
DROP TABLE IF EXISTS `v_mi_historial_compras`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_mi_historial_compras`  AS SELECT `v`.`ID_Cliente` AS `ID_Cliente`, `v`.`ID_Venta` AS `ID_Venta`, `v`.`Fecha_Venta` AS `Fecha_Venta`, `f`.`Numero_Factura` AS `Numero_Factura` FROM (`venta` `v` left join `factura` `f` on(`v`.`ID_Venta` = `f`.`ID_Venta`)) ;

-- --------------------------------------------------------

--
-- Estructura para la vista `v_mi_wishlist`
--
DROP TABLE IF EXISTS `v_mi_wishlist`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_mi_wishlist`  AS SELECT `w`.`ID_Cliente` AS `ID_Cliente`, `p`.`Nombre_Producto` AS `Nombre_Producto`, `p`.`Precio_Actual` AS `Precio_Actual`, if(`p`.`Stock_Actual` > 0,'Disponible','Agotado') AS `Estado` FROM (`lista_deseos` `w` join `producto` `p` on(`w`.`ID_Producto` = `p`.`ID_Producto`)) WHERE `p`.`deleted_at` is null ;

-- --------------------------------------------------------

--
-- Estructura para la vista `v_novedades`
--
DROP TABLE IF EXISTS `v_novedades`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_novedades`  AS SELECT `producto`.`ID_Producto` AS `ID_Producto`, `producto`.`Nombre_Producto` AS `Nombre_Producto`, `producto`.`Precio_Actual` AS `Precio_Actual`, `producto`.`Stock_Actual` AS `Stock_Actual`, `producto`.`ID_Categoria` AS `ID_Categoria`, `producto`.`ID_Proveedor` AS `ID_Proveedor`, `producto`.`Imagen_URL` AS `Imagen_URL`, `producto`.`QR_Code_URL` AS `QR_Code_URL`, `producto`.`deleted_at` AS `deleted_at` FROM `producto` WHERE `producto`.`deleted_at` is null ORDER BY `producto`.`ID_Producto` DESC LIMIT 0, 10 ;

-- --------------------------------------------------------

--
-- Estructura para la vista `v_ofertas`
--
DROP TABLE IF EXISTS `v_ofertas`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_ofertas`  AS SELECT `producto`.`ID_Producto` AS `ID_Producto`, `producto`.`Nombre_Producto` AS `Nombre_Producto`, `producto`.`Precio_Actual` AS `Precio_Actual`, `producto`.`Stock_Actual` AS `Stock_Actual`, `producto`.`ID_Categoria` AS `ID_Categoria`, `producto`.`ID_Proveedor` AS `ID_Proveedor`, `producto`.`Imagen_URL` AS `Imagen_URL`, `producto`.`QR_Code_URL` AS `QR_Code_URL`, `producto`.`deleted_at` AS `deleted_at` FROM `producto` WHERE `producto`.`Precio_Actual` < 50000 AND `producto`.`deleted_at` is null ;

-- --------------------------------------------------------

--
-- Estructura para la vista `v_pedidos_entregados`
--
DROP TABLE IF EXISTS `v_pedidos_entregados`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_pedidos_entregados`  AS SELECT `pedido`.`ID_Pedido` AS `ID_Pedido`, `pedido`.`ID_Venta` AS `ID_Venta`, `pedido`.`Direccion_Envio` AS `Direccion_Envio`, `pedido`.`Ciudad_Envio` AS `Ciudad_Envio`, `pedido`.`Tipo_Envio` AS `Tipo_Envio`, `pedido`.`Estado_Pedido` AS `Estado_Pedido`, `pedido`.`Guia_Seguimiento` AS `Guia_Seguimiento` FROM `pedido` WHERE `pedido`.`Estado_Pedido` = 'Entregado' ;

-- --------------------------------------------------------

--
-- Estructura para la vista `v_pqrs_vencidas`
--
DROP TABLE IF EXISTS `v_pqrs_vencidas`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_pqrs_vencidas`  AS SELECT `pqr`.`ID_Pqr` AS `ID_Pqr`, `pqr`.`Fecha_Registro` AS `Fecha_Registro`, `pqr`.`Descripcion` AS `Descripcion`, `pqr`.`Estado` AS `Estado`, `pqr`.`ID_Cliente` AS `ID_Cliente`, `pqr`.`ID_Empleado` AS `ID_Empleado` FROM `pqr` WHERE `pqr`.`Estado` = 'Abierto' AND `pqr`.`Fecha_Registro` <= current_timestamp() - interval 5 day ;

-- --------------------------------------------------------

--
-- Estructura para la vista `v_productos_por_proveedor`
--
DROP TABLE IF EXISTS `v_productos_por_proveedor`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_productos_por_proveedor`  AS SELECT `pr`.`Nombre_Empresa` AS `Nombre_Empresa`, count(`p`.`ID_Producto`) AS `Total_Productos` FROM (`producto` `p` join `proveedor` `pr` on(`p`.`ID_Proveedor` = `pr`.`ID_Proveedor`)) WHERE `p`.`deleted_at` is null GROUP BY `pr`.`ID_Proveedor` ;

-- --------------------------------------------------------

--
-- Estructura para la vista `v_proveedores_activos`
--
DROP TABLE IF EXISTS `v_proveedores_activos`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_proveedores_activos`  AS SELECT `proveedor`.`ID_Proveedor` AS `ID_Proveedor`, `proveedor`.`Nombre_Empresa` AS `Nombre_Empresa`, `proveedor`.`ID_Ciudad` AS `ID_Ciudad` FROM `proveedor` ;

-- --------------------------------------------------------

--
-- Estructura para la vista `v_rendimiento_empleados`
--
DROP TABLE IF EXISTS `v_rendimiento_empleados`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_rendimiento_empleados`  AS SELECT `e`.`Nombres` AS `Nombres`, `e`.`Apellidos` AS `Apellidos`, count(`v`.`ID_Venta`) AS `Atendidos` FROM (`venta` `v` join `empleado` `e` on(`v`.`ID_Empleado` = `e`.`ID_Empleado`)) WHERE `e`.`deleted_at` is null GROUP BY `e`.`ID_Empleado` ;

-- --------------------------------------------------------

--
-- Estructura para la vista `v_resenas_pendientes`
--
DROP TABLE IF EXISTS `v_resenas_pendientes`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_resenas_pendientes`  AS SELECT `resena_valoracion`.`ID_Resena` AS `ID_Resena`, `resena_valoracion`.`ID_Cliente` AS `ID_Cliente`, `resena_valoracion`.`ID_Producto` AS `ID_Producto`, `resena_valoracion`.`Calificacion` AS `Calificacion`, `resena_valoracion`.`Comentario` AS `Comentario`, `resena_valoracion`.`Fecha_Publicacion` AS `Fecha_Publicacion`, `resena_valoracion`.`Estado_Moderacion` AS `Estado_Moderacion` FROM `resena_valoracion` WHERE `resena_valoracion`.`Estado_Moderacion` = 'Pendiente' ;

-- --------------------------------------------------------

--
-- Estructura para la vista `v_resenas_rechazadas`
--
DROP TABLE IF EXISTS `v_resenas_rechazadas`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_resenas_rechazadas`  AS SELECT `resena_valoracion`.`ID_Resena` AS `ID_Resena`, `resena_valoracion`.`ID_Cliente` AS `ID_Cliente`, `resena_valoracion`.`ID_Producto` AS `ID_Producto`, `resena_valoracion`.`Calificacion` AS `Calificacion`, `resena_valoracion`.`Comentario` AS `Comentario`, `resena_valoracion`.`Fecha_Publicacion` AS `Fecha_Publicacion`, `resena_valoracion`.`Estado_Moderacion` AS `Estado_Moderacion` FROM `resena_valoracion` WHERE `resena_valoracion`.`Estado_Moderacion` = 'Rechazado' ;

-- --------------------------------------------------------

--
-- Estructura para la vista `v_rutas_envio`
--
DROP TABLE IF EXISTS `v_rutas_envio`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_rutas_envio`  AS SELECT `p`.`ID_Pedido` AS `ID_Pedido`, `c`.`Nombre_Ciudad` AS `Nombre_Ciudad`, `d`.`Nombre_Departamento` AS `Nombre_Departamento` FROM ((`pedido` `p` join `ciudad` `c` on(`p`.`Ciudad_Envio` = `c`.`ID_Ciudad`)) join `departamento` `d` on(`c`.`ID_Departamento` = `d`.`ID_Departamento`)) ;

-- --------------------------------------------------------

--
-- Estructura para la vista `v_sesiones_activas`
--
DROP TABLE IF EXISTS `v_sesiones_activas`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_sesiones_activas`  AS SELECT `ca`.`ID_Usuario` AS `ID_Usuario`, `u`.`Email` AS `Email`, `ca`.`Ultimo_Intento` AS `Ultimo_Intento` FROM (`control_accesos` `ca` join `usuario` `u` on(`ca`.`ID_Usuario` = `u`.`ID_Usuario`)) WHERE `ca`.`Ultimo_Intento` >= current_timestamp() - interval 15 minute ;

-- --------------------------------------------------------

--
-- Estructura para la vista `v_tickets_promedio`
--
DROP TABLE IF EXISTS `v_tickets_promedio`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_tickets_promedio`  AS SELECT avg(`pago`.`Monto_Pagado`) AS `Promedio_Venta` FROM `pago` ;

-- --------------------------------------------------------

--
-- Estructura para la vista `v_top_productos`
--
DROP TABLE IF EXISTS `v_top_productos`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_top_productos`  AS SELECT `p`.`Nombre_Producto` AS `Nombre_Producto`, sum(`dv`.`Cantidad`) AS `Vendidos` FROM (`detalle_venta` `dv` join `producto` `p` on(`dv`.`ID_Producto` = `p`.`ID_Producto`)) WHERE `p`.`deleted_at` is null GROUP BY `p`.`ID_Producto` ORDER BY sum(`dv`.`Cantidad`) DESC ;

-- --------------------------------------------------------

--
-- Estructura para la vista `v_usuarios_bloqueados`
--
DROP TABLE IF EXISTS `v_usuarios_bloqueados`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_usuarios_bloqueados`  AS SELECT `ca`.`ID_Control` AS `ID_Control`, `ca`.`ID_Usuario` AS `ID_Usuario`, `u`.`Email` AS `Email`, `ca`.`Intentos_Fallidos` AS `Intentos_Fallidos`, `ca`.`Ultimo_Intento` AS `Ultimo_Intento`, `ca`.`Bloqueado_Hasta` AS `Bloqueado_Hasta` FROM (`control_accesos` `ca` join `usuario` `u` on(`ca`.`ID_Usuario` = `u`.`ID_Usuario`)) WHERE `ca`.`Bloqueado_Hasta` > current_timestamp() ;

-- --------------------------------------------------------

--
-- Estructura para la vista `v_valorizacion_stock`
--
DROP TABLE IF EXISTS `v_valorizacion_stock`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_valorizacion_stock`  AS SELECT sum(`producto`.`Stock_Actual` * `producto`.`Precio_Actual`) AS `Valor_Total_Inventario` FROM `producto` WHERE `producto`.`deleted_at` is null ;

-- --------------------------------------------------------

--
-- Estructura para la vista `v_ventas_diarias`
--
DROP TABLE IF EXISTS `v_ventas_diarias`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_ventas_diarias`  AS SELECT cast(`venta`.`Fecha_Venta` as date) AS `Fecha`, count(0) AS `Total_Ventas` FROM `venta` GROUP BY cast(`venta`.`Fecha_Venta` as date) ;

-- --------------------------------------------------------

--
-- Estructura para la vista `v_ventas_mensuales`
--
DROP TABLE IF EXISTS `v_ventas_mensuales`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_ventas_mensuales`  AS SELECT date_format(`venta`.`Fecha_Venta`,'%Y-%m') AS `Mes`, count(0) AS `Total_Ventas` FROM `venta` GROUP BY date_format(`venta`.`Fecha_Venta`,'%Y-%m') ;

-- --------------------------------------------------------

--
-- Estructura para la vista `v_ventas_por_ciudad`
--
DROP TABLE IF EXISTS `v_ventas_por_ciudad`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_ventas_por_ciudad`  AS SELECT `ci`.`Nombre_Ciudad` AS `Nombre_Ciudad`, count(`v`.`ID_Venta`) AS `Ventas` FROM (((`venta` `v` join `cliente` `c` on(`v`.`ID_Cliente` = `c`.`ID_Cliente`)) join `libreta_direcciones` `ld` on(`c`.`ID_Cliente` = `ld`.`ID_Cliente`)) join `ciudad` `ci` on(`ld`.`ID_Ciudad` = `ci`.`ID_Ciudad`)) WHERE `ld`.`Es_Principal` = 1 AND `c`.`deleted_at` is null GROUP BY `ci`.`ID_Ciudad` ;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `alertas_sistema`
--
ALTER TABLE `alertas_sistema`
  ADD PRIMARY KEY (`ID_Alerta`),
  ADD KEY `idx_alerta_tipo_fecha` (`Tipo`,`Fecha_Creacion`),
  ADD KEY `idx_alerta_noleida` (`Leido`,`Tipo`,`Fecha_Creacion`),
  ADD KEY `idx_alerta_producto` (`ID_Producto`);

--
-- Indices de la tabla `auditoria_precios`
--
ALTER TABLE `auditoria_precios`
  ADD PRIMARY KEY (`ID_Auditoria`),
  ADD KEY `ID_Producto` (`ID_Producto`);

--
-- Indices de la tabla `cargo`
--
ALTER TABLE `cargo`
  ADD PRIMARY KEY (`ID_Cargo`);

--
-- Indices de la tabla `categoria`
--
ALTER TABLE `categoria`
  ADD PRIMARY KEY (`ID_Categoria`);

--
-- Indices de la tabla `ciudad`
--
ALTER TABLE `ciudad`
  ADD PRIMARY KEY (`ID_Ciudad`),
  ADD KEY `ID_Departamento` (`ID_Departamento`);

--
-- Indices de la tabla `cliente`
--
ALTER TABLE `cliente`
  ADD PRIMARY KEY (`ID_Cliente`),
  ADD UNIQUE KEY `Documento` (`Documento`),
  ADD KEY `idx_cliente_deleted` (`deleted_at`);

--
-- Indices de la tabla `control_accesos`
--
ALTER TABLE `control_accesos`
  ADD PRIMARY KEY (`ID_Control`),
  ADD UNIQUE KEY `ID_Usuario` (`ID_Usuario`),
  ADD UNIQUE KEY `Email` (`Email`);

--
-- Indices de la tabla `departamento`
--
ALTER TABLE `departamento`
  ADD PRIMARY KEY (`ID_Departamento`);

--
-- Indices de la tabla `detalle_venta`
--
ALTER TABLE `detalle_venta`
  ADD PRIMARY KEY (`ID_Detalle`),
  ADD KEY `ID_Producto` (`ID_Producto`),
  ADD KEY `idx_detalle_venta_venta` (`ID_Venta`);

--
-- Indices de la tabla `empleado`
--
ALTER TABLE `empleado`
  ADD PRIMARY KEY (`ID_Empleado`),
  ADD KEY `ID_Cargo` (`ID_Cargo`),
  ADD KEY `ID_Ciudad` (`ID_Ciudad`),
  ADD KEY `idx_empleado_deleted` (`deleted_at`);

--
-- Indices de la tabla `factura`
--
ALTER TABLE `factura`
  ADD PRIMARY KEY (`ID_Factura`),
  ADD UNIQUE KEY `Numero_Factura` (`Numero_Factura`),
  ADD UNIQUE KEY `ID_Venta` (`ID_Venta`);

--
-- Indices de la tabla `libreta_direcciones`
--
ALTER TABLE `libreta_direcciones`
  ADD PRIMARY KEY (`ID_Direccion`),
  ADD KEY `ID_Ciudad` (`ID_Ciudad`),
  ADD KEY `idx_libreta_cliente_principal` (`ID_Cliente`,`Es_Principal`);

--
-- Indices de la tabla `lista_deseos`
--
ALTER TABLE `lista_deseos`
  ADD PRIMARY KEY (`ID_Wishlist`),
  ADD KEY `ID_Cliente` (`ID_Cliente`),
  ADD KEY `ID_Producto` (`ID_Producto`);

--
-- Indices de la tabla `lista_espera_stock`
--
ALTER TABLE `lista_espera_stock`
  ADD PRIMARY KEY (`ID_Espera`),
  ADD KEY `ID_Producto` (`ID_Producto`);

--
-- Indices de la tabla `metodo_pago`
--
ALTER TABLE `metodo_pago`
  ADD PRIMARY KEY (`ID_Metodo`);

--
-- Indices de la tabla `movimiento_inventario`
--
ALTER TABLE `movimiento_inventario`
  ADD PRIMARY KEY (`ID_Movimiento`),
  ADD KEY `ID_Empleado` (`ID_Empleado`),
  ADD KEY `idx_movimiento_producto_fecha` (`ID_Producto`,`Fecha_Movimiento`);

--
-- Indices de la tabla `notificacion_log`
--
ALTER TABLE `notificacion_log`
  ADD PRIMARY KEY (`ID_Log`),
  ADD KEY `idx_notif_fecha` (`Fecha`),
  ADD KEY `idx_notif_tipo` (`Tipo`);

--
-- Indices de la tabla `pago`
--
ALTER TABLE `pago`
  ADD PRIMARY KEY (`ID_Pago`),
  ADD KEY `ID_Metodo` (`ID_Metodo`),
  ADD KEY `idx_pago_venta` (`ID_Venta`);

--
-- Indices de la tabla `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`ID_Reset`),
  ADD UNIQUE KEY `token_hash` (`token_hash`),
  ADD KEY `idx_reset_hash` (`token_hash`),
  ADD KEY `idx_reset_usuario` (`ID_Usuario`);

--
-- Indices de la tabla `pedido`
--
ALTER TABLE `pedido`
  ADD PRIMARY KEY (`ID_Pedido`),
  ADD UNIQUE KEY `ID_Venta` (`ID_Venta`),
  ADD KEY `idx_pedido_estado` (`Estado_Pedido`),
  ADD KEY `idx_pedido_ciudad` (`Ciudad_Envio`);

--
-- Indices de la tabla `pqr`
--
ALTER TABLE `pqr`
  ADD PRIMARY KEY (`ID_Pqr`),
  ADD KEY `ID_Cliente` (`ID_Cliente`),
  ADD KEY `ID_Empleado` (`ID_Empleado`);

--
-- Indices de la tabla `producto`
--
ALTER TABLE `producto`
  ADD PRIMARY KEY (`ID_Producto`),
  ADD KEY `ID_Categoria` (`ID_Categoria`),
  ADD KEY `ID_Proveedor` (`ID_Proveedor`),
  ADD KEY `idx_producto_deleted` (`deleted_at`);

--
-- Indices de la tabla `proveedor`
--
ALTER TABLE `proveedor`
  ADD PRIMARY KEY (`ID_Proveedor`),
  ADD KEY `ID_Ciudad` (`ID_Ciudad`);

--
-- Indices de la tabla `resena_valoracion`
--
ALTER TABLE `resena_valoracion`
  ADD PRIMARY KEY (`ID_Resena`),
  ADD UNIQUE KEY `uk_cliente_producto` (`ID_Cliente`,`ID_Producto`),
  ADD KEY `idx_resena_producto` (`ID_Producto`);

--
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`ID_Usuario`),
  ADD UNIQUE KEY `Email` (`Email`),
  ADD UNIQUE KEY `Seudonimo` (`Seudonimo`),
  ADD UNIQUE KEY `ID_Empleado` (`ID_Empleado`),
  ADD UNIQUE KEY `ID_Cliente` (`ID_Cliente`),
  ADD KEY `idx_usuario_deleted` (`deleted_at`);

--
-- Indices de la tabla `venta`
--
ALTER TABLE `venta`
  ADD PRIMARY KEY (`ID_Venta`),
  ADD KEY `ID_Cliente` (`ID_Cliente`),
  ADD KEY `ID_Empleado` (`ID_Empleado`),
  ADD KEY `idx_venta_fecha_cliente` (`Fecha_Venta`,`ID_Cliente`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `alertas_sistema`
--
ALTER TABLE `alertas_sistema`
  MODIFY `ID_Alerta` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de la tabla `auditoria_precios`
--
ALTER TABLE `auditoria_precios`
  MODIFY `ID_Auditoria` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `cargo`
--
ALTER TABLE `cargo`
  MODIFY `ID_Cargo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `categoria`
--
ALTER TABLE `categoria`
  MODIFY `ID_Categoria` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `ciudad`
--
ALTER TABLE `ciudad`
  MODIFY `ID_Ciudad` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `cliente`
--
ALTER TABLE `cliente`
  MODIFY `ID_Cliente` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `control_accesos`
--
ALTER TABLE `control_accesos`
  MODIFY `ID_Control` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT de la tabla `departamento`
--
ALTER TABLE `departamento`
  MODIFY `ID_Departamento` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `detalle_venta`
--
ALTER TABLE `detalle_venta`
  MODIFY `ID_Detalle` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT de la tabla `empleado`
--
ALTER TABLE `empleado`
  MODIFY `ID_Empleado` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `factura`
--
ALTER TABLE `factura`
  MODIFY `ID_Factura` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT de la tabla `libreta_direcciones`
--
ALTER TABLE `libreta_direcciones`
  MODIFY `ID_Direccion` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `lista_deseos`
--
ALTER TABLE `lista_deseos`
  MODIFY `ID_Wishlist` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `lista_espera_stock`
--
ALTER TABLE `lista_espera_stock`
  MODIFY `ID_Espera` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `metodo_pago`
--
ALTER TABLE `metodo_pago`
  MODIFY `ID_Metodo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `movimiento_inventario`
--
ALTER TABLE `movimiento_inventario`
  MODIFY `ID_Movimiento` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `notificacion_log`
--
ALTER TABLE `notificacion_log`
  MODIFY `ID_Log` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `pago`
--
ALTER TABLE `pago`
  MODIFY `ID_Pago` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT de la tabla `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `ID_Reset` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `pedido`
--
ALTER TABLE `pedido`
  MODIFY `ID_Pedido` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `pqr`
--
ALTER TABLE `pqr`
  MODIFY `ID_Pqr` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `producto`
--
ALTER TABLE `producto`
  MODIFY `ID_Producto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `proveedor`
--
ALTER TABLE `proveedor`
  MODIFY `ID_Proveedor` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `resena_valoracion`
--
ALTER TABLE `resena_valoracion`
  MODIFY `ID_Resena` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `ID_Usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de la tabla `venta`
--
ALTER TABLE `venta`
  MODIFY `ID_Venta` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `alertas_sistema`
--
ALTER TABLE `alertas_sistema`
  ADD CONSTRAINT `fk_alerta_producto` FOREIGN KEY (`ID_Producto`) REFERENCES `producto` (`ID_Producto`);

--
-- Filtros para la tabla `auditoria_precios`
--
ALTER TABLE `auditoria_precios`
  ADD CONSTRAINT `auditoria_precios_ibfk_1` FOREIGN KEY (`ID_Producto`) REFERENCES `producto` (`ID_Producto`);

--
-- Filtros para la tabla `ciudad`
--
ALTER TABLE `ciudad`
  ADD CONSTRAINT `ciudad_ibfk_1` FOREIGN KEY (`ID_Departamento`) REFERENCES `departamento` (`ID_Departamento`);

--
-- Filtros para la tabla `control_accesos`
--
ALTER TABLE `control_accesos`
  ADD CONSTRAINT `control_accesos_ibfk_1` FOREIGN KEY (`ID_Usuario`) REFERENCES `usuario` (`ID_Usuario`) ON DELETE CASCADE;

--
-- Filtros para la tabla `detalle_venta`
--
ALTER TABLE `detalle_venta`
  ADD CONSTRAINT `detalle_venta_ibfk_1` FOREIGN KEY (`ID_Venta`) REFERENCES `venta` (`ID_Venta`) ON DELETE CASCADE,
  ADD CONSTRAINT `detalle_venta_ibfk_2` FOREIGN KEY (`ID_Producto`) REFERENCES `producto` (`ID_Producto`);

--
-- Filtros para la tabla `empleado`
--
ALTER TABLE `empleado`
  ADD CONSTRAINT `empleado_ibfk_1` FOREIGN KEY (`ID_Cargo`) REFERENCES `cargo` (`ID_Cargo`),
  ADD CONSTRAINT `empleado_ibfk_2` FOREIGN KEY (`ID_Ciudad`) REFERENCES `ciudad` (`ID_Ciudad`);

--
-- Filtros para la tabla `factura`
--
ALTER TABLE `factura`
  ADD CONSTRAINT `factura_ibfk_1` FOREIGN KEY (`ID_Venta`) REFERENCES `venta` (`ID_Venta`);

--
-- Filtros para la tabla `libreta_direcciones`
--
ALTER TABLE `libreta_direcciones`
  ADD CONSTRAINT `libreta_direcciones_ibfk_1` FOREIGN KEY (`ID_Cliente`) REFERENCES `cliente` (`ID_Cliente`) ON DELETE CASCADE,
  ADD CONSTRAINT `libreta_direcciones_ibfk_2` FOREIGN KEY (`ID_Ciudad`) REFERENCES `ciudad` (`ID_Ciudad`);

--
-- Filtros para la tabla `lista_deseos`
--
ALTER TABLE `lista_deseos`
  ADD CONSTRAINT `lista_deseos_ibfk_1` FOREIGN KEY (`ID_Cliente`) REFERENCES `cliente` (`ID_Cliente`) ON DELETE CASCADE,
  ADD CONSTRAINT `lista_deseos_ibfk_2` FOREIGN KEY (`ID_Producto`) REFERENCES `producto` (`ID_Producto`) ON DELETE CASCADE;

--
-- Filtros para la tabla `lista_espera_stock`
--
ALTER TABLE `lista_espera_stock`
  ADD CONSTRAINT `lista_espera_stock_ibfk_1` FOREIGN KEY (`ID_Producto`) REFERENCES `producto` (`ID_Producto`);

--
-- Filtros para la tabla `movimiento_inventario`
--
ALTER TABLE `movimiento_inventario`
  ADD CONSTRAINT `movimiento_inventario_ibfk_1` FOREIGN KEY (`ID_Producto`) REFERENCES `producto` (`ID_Producto`),
  ADD CONSTRAINT `movimiento_inventario_ibfk_2` FOREIGN KEY (`ID_Empleado`) REFERENCES `empleado` (`ID_Empleado`);

--
-- Filtros para la tabla `pago`
--
ALTER TABLE `pago`
  ADD CONSTRAINT `pago_ibfk_1` FOREIGN KEY (`ID_Venta`) REFERENCES `venta` (`ID_Venta`),
  ADD CONSTRAINT `pago_ibfk_2` FOREIGN KEY (`ID_Metodo`) REFERENCES `metodo_pago` (`ID_Metodo`);

--
-- Filtros para la tabla `password_resets`
--
ALTER TABLE `password_resets`
  ADD CONSTRAINT `fk_reset_usuario` FOREIGN KEY (`ID_Usuario`) REFERENCES `usuario` (`ID_Usuario`) ON DELETE CASCADE;

--
-- Filtros para la tabla `pedido`
--
ALTER TABLE `pedido`
  ADD CONSTRAINT `pedido_ibfk_1` FOREIGN KEY (`ID_Venta`) REFERENCES `venta` (`ID_Venta`),
  ADD CONSTRAINT `pedido_ibfk_2` FOREIGN KEY (`Ciudad_Envio`) REFERENCES `ciudad` (`ID_Ciudad`);

--
-- Filtros para la tabla `pqr`
--
ALTER TABLE `pqr`
  ADD CONSTRAINT `pqr_ibfk_1` FOREIGN KEY (`ID_Cliente`) REFERENCES `cliente` (`ID_Cliente`),
  ADD CONSTRAINT `pqr_ibfk_2` FOREIGN KEY (`ID_Empleado`) REFERENCES `empleado` (`ID_Empleado`);

--
-- Filtros para la tabla `producto`
--
ALTER TABLE `producto`
  ADD CONSTRAINT `producto_ibfk_1` FOREIGN KEY (`ID_Categoria`) REFERENCES `categoria` (`ID_Categoria`),
  ADD CONSTRAINT `producto_ibfk_2` FOREIGN KEY (`ID_Proveedor`) REFERENCES `proveedor` (`ID_Proveedor`);

--
-- Filtros para la tabla `proveedor`
--
ALTER TABLE `proveedor`
  ADD CONSTRAINT `proveedor_ibfk_1` FOREIGN KEY (`ID_Ciudad`) REFERENCES `ciudad` (`ID_Ciudad`);

--
-- Filtros para la tabla `resena_valoracion`
--
ALTER TABLE `resena_valoracion`
  ADD CONSTRAINT `resena_valoracion_ibfk_1` FOREIGN KEY (`ID_Cliente`) REFERENCES `cliente` (`ID_Cliente`),
  ADD CONSTRAINT `resena_valoracion_ibfk_2` FOREIGN KEY (`ID_Producto`) REFERENCES `producto` (`ID_Producto`);

--
-- Filtros para la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD CONSTRAINT `usuario_ibfk_1` FOREIGN KEY (`ID_Empleado`) REFERENCES `empleado` (`ID_Empleado`) ON DELETE CASCADE,
  ADD CONSTRAINT `usuario_ibfk_2` FOREIGN KEY (`ID_Cliente`) REFERENCES `cliente` (`ID_Cliente`) ON DELETE CASCADE;

--
-- Filtros para la tabla `venta`
--
ALTER TABLE `venta`
  ADD CONSTRAINT `venta_ibfk_1` FOREIGN KEY (`ID_Cliente`) REFERENCES `cliente` (`ID_Cliente`),
  ADD CONSTRAINT `venta_ibfk_2` FOREIGN KEY (`ID_Empleado`) REFERENCES `empleado` (`ID_Empleado`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
