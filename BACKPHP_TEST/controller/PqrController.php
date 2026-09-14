<?php
// controller/PqrController.php — Capa fina MVC para PQR + valoraciones (RF 3.1-3.3, 4.8).
require_once __DIR__ . "/../model/Pqr.php";

class PqrController {
    private $model;
    public function __construct() {
        $this->model = new Pqr();
    }
    public function crear($idUsuario, $tipo, $descripcion) {
        return $this->model->crear($idUsuario, $tipo, $descripcion);
    }
    public function misPqr($idUsuario, $page = 1, $per = 10) {
        $idCli = $this->model->idClienteDeUsuario($idUsuario);
        if (empty($idCli)) return [];
        return $this->model->misPqr($idCli, $page, $per);
    }
    public function contarMis($idUsuario) {
        $idCli = $this->model->idClienteDeUsuario($idUsuario);
        if (empty($idCli)) return 0;
        return $this->model->contarMis($idCli);
    }
    public function panel($page = 1, $per = 10, $q = '', $filtro = '', $desde = '', $hasta = '') {
        return $this->model->panel($page, $per, $q, $filtro, $desde, $hasta);
    }
    public function contarPanel($q = '', $filtro = '', $desde = '', $hasta = '') {
        return $this->model->contarPanel($q, $filtro, $desde, $hasta);
    }
    public function responder($idPqr, $respuesta, $estado, $idGestor = null) {
        return $this->model->responder($idPqr, $respuesta, $estado, $idGestor);
    }
    public function porCalificar($idUsuario) {
        $idCli = $this->model->idClienteDeUsuario($idUsuario);
        if (empty($idCli)) return [];
        return $this->model->productosPorCalificar($idCli);
    }
    public function misResenas($idUsuario) {
        $idCli = $this->model->idClienteDeUsuario($idUsuario);
        if (empty($idCli)) return [];
        return $this->model->misResenas($idCli);
    }
    public function calificar($idUsuario, $idProducto, $estrellas, $comentario = '') {
        return $this->model->calificar($idUsuario, $idProducto, $estrellas, $comentario);
    }
}
