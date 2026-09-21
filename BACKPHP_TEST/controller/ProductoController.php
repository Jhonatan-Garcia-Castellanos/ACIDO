<?php
require_once __DIR__ . "/../model/Producto.php";
require_once __DIR__ . "/../model/Movimiento.php";

class ProductoController {
    private $model;
    private $mov;
    public function __construct() {
        $this->model = new Producto();
        $this->mov = new Movimiento();
    }
    public function listar($page = 1, $per = 10, $q = '', $order = 'ID_Producto', $dir = 'DESC') { return $this->model->obtenerTodos($page, $per, $q, $order, $dir); }
    public function contar($q = '') { return $this->model->contarProductos($q); }
    public function guardar($datos) { return $this->model->guardar($datos); }

    // =====================================================================
    // IMAGEN DE PRODUCTO: JPG/PNG/WEBP max 5MB -> public/img/productos/
    // Retorna ['ok'=>bool,'message'=>string,'ruta'=>?string]
    // =====================================================================
    public function subirImagen($idProducto, array $file) {
        if (!isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
            return ['ok' => false, 'message' => 'Elige una imagen primero.'];
        }
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['ok' => false, 'message' => 'No se pudo subir la imagen. Intenta de nuevo.'];
        }
        if (($file['size'] ?? 0) > 5 * 1024 * 1024) {
            return ['ok' => false, 'message' => 'La imagen supera 5MB.'];
        }
        $permitidas = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp'];
        $ext = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));
        if (!isset($permitidas[$ext])) {
            return ['ok' => false, 'message' => 'Formato inválido: solo JPG, PNG o WEBP.'];
        }
        $info = @getimagesize($file['tmp_name']);
        if ($info === false || !in_array($info['mime'], $permitidas, true)) {
            return ['ok' => false, 'message' => 'El archivo no es una imagen válida.'];
        }
        $dirFs = __DIR__ . '/../public/img/productos';
        if (!is_dir($dirFs) && !@mkdir($dirFs, 0755, true)) {
            return ['ok' => false, 'message' => 'No se pudo crear la carpeta de imágenes.'];
        }
        // Borra imagen anterior del producto (si vive en productos/) al reemplazar
        $anterior = null;
        if (ctype_digit((string)$idProducto) && (int)$idProducto > 0) {
            $prod = $this->model->obtenerPorId($idProducto);
            $anterior = $prod['Imagen_URL'] ?? null;
        }
        $nuevo = 'prod_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $destFs = $dirFs . '/' . $nuevo;
        if (!@move_uploaded_file($file['tmp_name'], $destFs)) {
            return ['ok' => false, 'message' => 'No se pudo guardar la imagen.'];
        }
        $rutaWeb = '/ACIDO/BACKPHP_TEST/public/img/productos/' . $nuevo;
        if (!empty($anterior) && strpos($anterior, '/public/img/productos/') !== false) {
            $oldFs = __DIR__ . '/../' . ltrim(preg_replace('#^/ACIDO/BACKPHP_TEST/#', '', $anterior), '/');
            if (is_file($oldFs) && realpath($oldFs) !== realpath($destFs)) @unlink($oldFs);
        }
        return ['ok' => true, 'message' => 'Imagen subida.', 'ruta' => $rutaWeb];
    }
    public function cambiarEstado($id, $activo) { return $this->model->cambiarEstado($id, $activo); }
    public function catalogo() { return $this->model->obtenerCatalogo(); }
    public function categorias() { return $this->model->listarCategorias(); }
    public function proveedores() { return $this->model->listarProveedores(); }
    public function resumen() { return $this->model->resumen(); }
    public function anotarEspera($idProducto, $nombre, $email) { return $this->model->registrarEspera($idProducto, $nombre, $email); }
    public function obtenerPorId($id) { return $this->model->obtenerPorId($id); }
    public function kardex($limit = 100, $idProducto = null) { return $this->mov->listar($limit, $idProducto); }
    public function ajustar($idProducto, $tipo, $cantidad, $motivo, $idUsuario) {
        return $this->mov->ajustar($idProducto, $tipo, $cantidad, $motivo, $idUsuario);
    }
}
