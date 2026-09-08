<?php
// Usamos la ruta raíz del servidor local de XAMPP para evitar problemas de carpetas relativas
require_once $_SERVER['DOCUMENT_ROOT'] . '/ACIDO/BACKPHP/model/CrudModel.php';

class CrudController {
    private $model;

    public function __construct() {
        if (class_exists('CrudModel')) {
            $this->model = new CrudModel();
        } else {
            die("Error: No se pudo cargar la clase CrudModel.");
        }
    }

    public function listar() {
        return $this->model->obtenerTodos();
    }

    public function guardar($datos) {
        $id = $datos['id'] ?? '';
        $nombre = $datos['nombre'] ?? '';
        $categoria = $datos['categoria'] ?? '';
        $estado = $datos['estado'] ?? '';

        if (!empty($id)) {
            $this->model->actualizar($id, $nombre, $categoria, $estado);
        } else {
            $this->model->insertar($nombre, $categoria, $estado);
        }
        
        header("Location: index.php?action=crud");
        exit();
    }

    public function eliminar($id) {
        if (!empty($id)) {
            $this->model->eliminar($id);
        }
        
        header("Location: index.php?action=crud");
        exit();
    }
}
?>
