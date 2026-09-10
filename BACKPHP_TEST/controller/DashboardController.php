<?php
require_once __DIR__ . "/../model/Dashboard.php";

class DashboardController {
    private $model;
    public function __construct() {
        $this->model = new Dashboard();
    }
    public function datos() {
        return [
            'resumen' => $this->model->resumen(),
            'porMes' => $this->model->ventasPorMes(6),
            'porCategoria' => $this->model->ingresosPorCategoria(5),
            'topProductos' => $this->model->topProductos(5),
            'porDia' => $this->model->ventasDiarias(7),
        ];
    }
}
