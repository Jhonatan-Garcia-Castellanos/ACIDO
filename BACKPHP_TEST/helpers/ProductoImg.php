<?php
// helpers/ProductoImg.php — Normaliza la URL de imagen de producto y evita
// el icono de "imagen rota": si la ruta está vacía, es vieja o el archivo
// ya no existe en disco, retorna null para mostrar el placeholder.

if (!function_exists('app_base_path')) {
    /**
     * Base web del proyecto (ej: /ACIDO/BACKPHP_TEST).
     * Se detecta de SCRIPT_NAME para no hardcodear la carpeta.
     */
    function app_base_path() {
        // 1) APP_URL del .env manda si existe (http://localhost/ACIDO/BACKPHP_TEST)
        try {
            if (class_exists('Env')) {
                $app = (string)Env::get('APP_URL', '');
                if ($app !== '') {
                    $p = parse_url($app, PHP_URL_PATH);
                    if (is_string($p) && $p !== '' && $p !== '/') {
                        return rtrim($p, '/');
                    }
                }
            }
        } catch (Throwable $e) {}
        // 2) Deriva de la URL actual: /ACIDO/BACKPHP_TEST/index.php -> /ACIDO/BACKPHP_TEST
        if (!empty($_SERVER['SCRIPT_NAME'])) {
            $dir = rtrim(str_replace('\\', '/', dirname((string)$_SERVER['SCRIPT_NAME'])), '/');
            if ($dir !== '' && $dir !== '/' && $dir !== '.') {
                return $dir;
            }
        }
        // 3) Fallback histórico
        return '/ACIDO/BACKPHP_TEST';
    }
}

if (!function_exists('imagen_producto_url')) {
    /**
     * Normaliza $raw (valor de producto.Imagen_URL) a URL web válida.
     * Retorna null si no hay imagen mostrable (usar placeholder).
     */
    function imagen_producto_url($raw) {
        $raw = trim((string)($raw ?? ''));
        if ($raw === '') return null;
        // URLs externas o data-URI se respetan tal cual
        if (preg_match('#^(https?://|data:image/)#i', $raw)) return $raw;
        // Solo nos interesan rutas locales; extrae el nombre del archivo
        $base = basename($raw);
        if ($base === '' || $base === '/' || strpos($base, '.') === false) return null;
        // Seguridad: nada de rutas con .. ni separadores
        if (strpos($base, '..') !== false || strpos($base, '/') !== false || strpos($base, '\\') !== false) return null;
        $fs = __DIR__ . '/../public/img/productos/' . $base;
        if (!is_file($fs)) return null; // el archivo se borró -> placeholder, no icono roto
        // Extensión válida de imagen
        $ext = strtolower(pathinfo($base, PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true)) return null;
        return app_base_path() . '/public/img/productos/' . $base;
    }
}
