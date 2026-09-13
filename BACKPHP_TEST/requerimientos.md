# Asignación de Requerimientos Funcionales (RF) e Implícitos (RI) por Participante

Organizados estrictamente por participante, su rol y su módulo asignado dentro del proyecto.

---

## Carlos Javier Figuera Vegas

**Roles:** Líder Desarrollador (LD)

**Módulo:** Inventario (EMI)

### Módulo de Inventario

* **RF 2.1 / RI 2.1:** Notificaciones al gerente sobre vistas de productos, compras, pedidos y envíos dentro de Bogotá (actualización quincenal).
* **RF 2.2 / RI 2.2:** Visualización de productos vendidos (imagen, código numérico, precio) y datos del comprador (nombre, documento, usuario).
* **RF 2.3 / RI 2.3:** Control de stock en tiempo real, reducción automática tras pago y alerta visual en rojo para productos bajo el Stock Mínimo.
* **RF 2.4 / RI 2.4:** Consulta de compras entregadas (datos del cliente, productos, fecha de entrega y estado).
* **RF 2.5 / RI 2.5:** Visualización de ventas pendientes por entregar (datos del cliente, dirección, productos, fecha de compra y estado).
* **RF 2.6 / RI 2.7:** Consulta de facturas generadas (número de factura, cliente, productos, valor total y fecha de emisión).
* **RF 2.7 / RI 2.8:** Generación de Kardex y trazabilidad (entradas por compra, salidas por venta, ajustes por daño) con fecha, cantidad y responsable.
* **RF 2.8 / RI 2.9:** Sincronización automática y bloqueo/reserva de unidades en inventario al confirmar estado "Pagado".
* **RF 2.9 / RI 2.10:** Ajustes manuales de inventario por el gerente (pérdida, daño, devolución) con campo obligatorio "Motivo" y registro de auditoría inalterable.
* **RF 2.10 / RI 2.11:** Validación de stock negativo; bloqueo de estados "Listo para envío" o "Pagado" si la cantidad solicitada supera el stock.

---

## Lahiyonnel Steven Huerfano Zambrano

**Roles:** Diseñador Gráfico (DG) / Arquitecto de Software (ASO)

**Módulo:** Co-responsable de Perfil de Usuario (Apoyo en arquitectura visual y estructural)

*(Responsable de dar soporte visual, flujo de interfaces y estructura general de los datos de usuario dentro de la plataforma).*

---

## Johan Sebastian Garcia Mora

**Rol:** Encargado del Módulo de Ventas (EMV)

**Módulo:** Ventas

### Módulo de Ventas

* **RF 5.1 / RI 5.1:** Selección y gestión de métodos de pago dinámicos (Nequi/Daviplata de 10 dígitos, transferencias bancarias con comprobante JPG/PDF, y contra entrega con validación geográfica).
* **RF 5.2 / RI 5.2:** Generación del resumen consolidado de compra previo a la confirmación (desglose por ítem, límite de 10 unidades por producto, subtotales y total).
* **RF 5.3 / RI 5.3:** Carrito de compras en tiempo real (modificación de cantidades, eliminación/adición de ítems y actualización instantánea de totales con validación de stock).
* **RF 5.4 / RI 5.4:** Notificaciones automatizadas del proceso de pago (instrucciones, total, método y estado inicial "Pendiente de pago").
* **RF 5.5 / RI 5.5:** Compra directa (*Buy Now*) omitiendo el carrito y cargando 1 unidad por defecto.
* **RF 5.6 / RI 5.6:** Visualización estructurada del catálogo de productos en tiempo real (nombre, imagen, descripción, precio, disponibilidad y botones de interacción).
* **RF 5.7 / RI 5.7:** Generación automática de factura electrónica tras transacción exitosa (envío por correo y almacenamiento en el sistema).
* **RF 5.8 / RI 5.8:** Lista de espera para productos agotados (registro de datos del usuario e interés para notificar cuando vuelva a estar disponible).
* **RF 5.9 / RI 5.9:** Registro persistente de ventas en base de datos (ID de venta, fecha/hora, productos, totales, cliente y estado).
* **RF 5.10 / RI 5.10:** Gestión del ciclo de vida del pedido con flujo secuencial obligatorio: *Pendiente → Pagado → En preparación → Enviado → Entregado*.
* **RF 5.11 / RI 5.11:** Validación y confirmación del pago antes de autorizar el procesamiento logístico.
* **RF 5.12 / RI 5.12:** Consulta del historial de compras en tiempo real por parte del usuario.

---

## Jhonatan Garcia Castellanos (Jhonny)

**Rol:** Tester (T)

**Módulo:** Login / Autenticación

### Módulo de Login y Autenticación

* **RF 1.1 / RI 1.1:** Registro de nuevos usuarios mediante formulario con validaciones estrictas (documento, nombres/apellidos de max 70 caracteres, correo válido max 80 caracteres, teléfono de 7 o 10 dígitos, y contraseña con mayúscula, minúscula, número y carácter especial de 8-20 caracteres).
* **RF 1.2 / RI 1.3:** Iniciar sesión con correo/seudónimo y contraseña con validación exacta contra base de datos.
* **RF 1.3 / RI 1.4:** Restricción de acceso con mensaje genérico ("usuario o contraseña incorrectos") y bloqueo temporal tras 5 intentos fallidos.
* **RF 1.4 / RI 1.5:** Recuperación de contraseña mediante envío de enlace único a correo electrónico con vigencia de 15 minutos.
* **RF 1.5 / RI 1.6:** Creación de nueva contraseña que cumpla con los requisitos de seguridad y sea diferente a la anterior.

---

## Jhon Jairo Trilleras Jimenez

**Rol:** Encargado de PQR y Perfil de Usuario

**Módulos:** PQR y Perfil de Usuario

### Módulo de PQR (Quejas, Reclamos y Valoraciones)

* **RF 3.1 / RI 3.1:** Formulario para registrar quejas, reclamos o solicitudes (redirige al login si el usuario no ha iniciado sesión).
* **RF 3.2 / RI 3.2:** Calificación de la página por estrellas (1 a 5) vinculada al usuario con timestamp y restricción de actualización (sin duplicar registros).
* **RF 3.3 / RI 3.3:** Panel administrativo para visualización y gestión de PQR (filtros por estado Pendiente/Resuelto, fechas y exportación a PDF/Excel).
* **RF 4.8 / RI 4.8:** Historial de reseñas y valoraciones del usuario (calificación, comentario, fecha y estado de moderación).

### Módulo de Perfil de Usuario

* **RF 4.1 / RI 4.1:** Visualización y edición de datos personales del usuario.
* **RF 4.2 / RI 4.2:** Consulta del historial de actividad del usuario (compras, favoritos y pedidos activos con detalle de producto y transacciones).
* **RF 4.3 / RI 4.3:** Cancelación de compras únicamente para pedidos con estado "no enviado", exigiendo motivo obligatorio.
* **RF 4.4 / RI 4.4:** Cálculo y muestra de la fecha estimada de entrega basada en el tiempo logístico.
* **RF 4.5 / RI 4.5:** Libreta de direcciones (alias, ciudad, código postal, dirección, referencias) con marcado de dirección principal por defecto.
* **RF 4.6 / RI 4.6:** Lista de deseos (Wishlist) con etiquetado "Agotado" y desactivación del carrito cuando el stock sea 0.
* **RF 4.7 / RI 4.7:** Interfaz de seguridad para cambio de contraseña (requiere clave actual, confirmación, reglas de complejidad y envío de token al correo).
