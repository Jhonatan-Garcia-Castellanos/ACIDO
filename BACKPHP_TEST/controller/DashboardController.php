<?php
require_once __DIR__ . "/../model/Dashboard.php";

class DashboardController {
    private $model;
    public function __construct() {
        $this->model = new Dashboard();
    }
    public function datos($meses = 6) {
        // Rango permitido para la gráfica mensual (selector 3M/6M/12M del dashboard)
        $meses = in_array((int)$meses, [3, 6, 12], true) ? (int)$meses : 6;
        return [
            'resumen' => $this->model->resumen(),
            'porMes' => $this->model->ventasPorMes($meses),
            'porCategoria' => $this->model->ingresosPorCategoria(5),
            'topProductos' => $this->model->topProductos(5),
            'porDia' => $this->model->ventasDiarias(7),
        ];
    }
}
