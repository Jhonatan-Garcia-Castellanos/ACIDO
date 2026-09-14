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
    public function listar() { return $this->model->obtenerTodos(); }
    public function guardar($datos) { return $this->model->guardar($datos); }
    public function cambiarEstado($id, $activo) { return $this->model->cambiarEstado($id, $activo); }
    public function catalogo() { return $this->model->obtenerCatalogo(); }
    public function categorias() { return $this->model->listarCategorias(); }
    public function proveedores() { return $this->model->listarProveedores(); }
    public function resumen() { return $this->model->resumen(); }
    public function kardex($limit = 100, $idProducto = null) { return $this->mov->listar($limit, $idProducto); }
    public function ajustar($idProducto, $tipo, $cantidad, $motivo, $idUsuario) {
        return $this->mov->ajustar($idProducto, $tipo, $cantidad, $motivo, $idUsuario);
    }
}
