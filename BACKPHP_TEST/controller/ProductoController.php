<?php
require_once __DIR__ . "/../model/Producto.php";

class ProductoController {
    private $model;
    public function __construct() {
        $this->model = new Producto();
    }
    public function listar() { return $this->model->obtenerTodos(); }
    public function guardar($datos) { return $this->model->guardar($datos); }
    public function categorias() { return $this->model->listarCategorias(); }
    public function proveedores() { return $this->model->listarProveedores(); }
    public function resumen() { return $this->model->resumen(); }
}
