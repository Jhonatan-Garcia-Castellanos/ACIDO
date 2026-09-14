# Resumen de Ítems a Tener en Cuenta para la Revisión del Proyecto 
# Este documento establece los criterios técnicos, funcionales y de seguridad exigidos para la evaluación y revisión de la aplicación web.

## 1. Módulo de Autenticación, Roles y Sesiones
**1.1 Hash de Contraseñas**
Criterio: Verificar que las claves almacenadas en la base de datos utilicen algoritmos de cifrado seguros (bcrypt o argon2).

Restricción: No se admite texto plano ni algoritmos obsoletos (ej. MD5, SHA1).

**1.2 Inicio de Sesión y Control de Acceso Basado en Roles (RBAC)**
Validación de Credenciales: Autenticar usuarios activos mediante combinaciones válidas.

Control de Rutas: Comprobar que un usuario no pueda acceder manualmente mediante URL a secciones o módulos restringidos que no correspondan a su rol asignado.

**1.3 Expiración y Gestión de Sesión**
Cierre Automático: Validar la destrucción automática de la sesión/token tras 15 minutos de inactividad.

Cierre Manual (Logout): Asegurar que la acción de cierre de sesión destruya por completo las variables de sesión, cookies y tokens activos.

## 2. Dashboard Administrativo
**2.1 Métricas Dinámicas (KPIs)**
Carga de Datos: Confirmar que los contadores, tarjetas resumidas y métricas principales consuman información real directamente desde la base de datos en tiempo real.

**2.2 Gráficos Estadísticos**
Agregación de Datos: Verificar que las gráficas del panel reflejen información consolidada (consultas con GROUP BY, estadísticas semanales/mensuales, etc.) sobre las actividades y registros de la aplicación.

## 3. Módulos CRUD (Usuarios y Entidad Principal)
**3.1 Creación de Registros (Create)**
Validaciones Frontend: Comprobar las restricciones de campos obligatorios, formatos (email, números, etc.) y tipos de datos directamente en el navegador.

Validaciones Backend: Verificar que el servidor valide de manera independiente la integridad y formato de las entradas antes de insertarlas en la BD.

**3.2 Listado y Consultas (Read)**
Paginación: Validar la correcta fragmentación de resultados en bloques definidos.

Búsqueda y Filtros: Probar el buscador por coincidencia de texto (LIKE) en los campos clave.

Ordenamiento: Confirmar la capacidad de ordenar los registros de forma ascendente y descendente.

**3.3 Modificación (Update)**
Precarga de Datos: Verificar que la vista o modal de edición cargue correctamente los valores existentes del registro.

Actualización: Comprobar que la operación guarde las modificaciones sin alterar los campos que no fueron editados.

**3.4 Eliminación Lógica (Soft Delete)**
Preservación de Datos: Confirmar que la acción de eliminación no borre físicamente la fila (DELETE FROM).

Marcador de Estado: Comprobar que se actualice la columna deleted_at con la marca de tiempo o que la variable de estado cambie a 0/inactivo.

## 4. Módulo de Informes
**4.1 Filtro por Fechas**
Rango Dinámico: Probar que la selección del periodo (Desde / Hasta) filtre con precisión únicamente las transacciones creadas en ese intervalo.

**4.2 Exportación a PDF**
Estructura y Maquetación: Verificar la generación de un documento PDF limpio que incluya encabezado corporativo, tabla de datos paginada y numeración de páginas.

**4.3 Exportación a CSV**
Formato y Codificación: Confirmar que el archivo sea plano, bien delimitado (comas o punto y coma) y con codificación UTF-8 para preservar acentos, tildes y caracteres especiales.

## 5. Pruebas de Seguridad y Requisitos No Funcionales (RNF)
**5.1 Inyección SQL (SQLi)**
Verificación: Introducir caracteres de escape y cadenas maliciosas (ej. ' OR '1'='1) en los campos de búsqueda e inicio de sesión para certificar el uso de sentencias preparadas (PDO / MySQLi).

**5.2 Cross-Site Scripting (XSS)**
Sanitización: Intentar ingresar etiquetas ejecutables como <script>alert('xss')</script> en los formularios para verificar la correcta sanitización de datos de salida (htmlspecialchars / escape de plantillas).

**5.3 Mitigación CSRF**
Protección de Formularios: Comprobar que todos los formularios enviados mediante método POST contengan un token de validación dinámico e irrepetible.

**5.4 Calidad de Software y Arquitectura**
Patrón MVC: Verificar la clara separación entre Modelos (lógica de datos), Vistas (interfaz de usuario) y Controladores (lógica de negocio).

Diseño Adaptativo (Responsive): Probar el correcto acomodo visual e interactivo en dispositivos móviles y tablets.

Rendimiento: Validar que los tiempos de respuesta del servidor se mantengan por debajo de los 2 segundos en las peticiones convencionales.
