<?php
require_once __DIR__ . "/../model/Venta.php";

class VentaController {
    private $model;
    public function __construct() {
        $this->model = new Venta();
    }

    // ---- Carrito en sesión ----
    private function cart() {
        if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) $_SESSION['cart'] = [];
        return $_SESSION['cart'];
    }
    public function agregar($id, $qty = 1) {
        if (!ctype_digit((string)$id)) return false;
        $qty = max(1, min(10, (int)$qty));
        $cart = $this->cart();
        $cart[$id] = min(10, ($cart[$id] ?? 0) + $qty);
        $_SESSION['cart'] = $cart;
        return true;
    }
    public function actualizar($id, $qty) {
        if (!ctype_digit((string)$id)) return false;
        $cart = $this->cart();
        $qty = (int)$qty;
        if ($qty <= 0) unset($cart[$id]);
        else $cart[$id] = min(10, $qty);
        $_SESSION['cart'] = $cart;
        return true;
    }
    public function quitar($id) {
        $cart = $this->cart();
        unset($cart[$id]);
        $_SESSION['cart'] = $cart;
        return true;
    }
    public function vaciar() { $_SESSION['cart'] = []; }
    public function contar() { return array_sum($this->cart()); }
    public function obtenerCart() { return $this->cart(); }
    public function detalle() { return $this->model->detalleCarrito($this->cart()); }

    // ---- Ventas ----
    public function checkout($idUsuario, $idMetodo) {
        $res = $this->model->checkout($idUsuario, $this->cart(), $idMetodo);
        if (isset($res['ID_Venta'])) $this->vaciar();
        return $res;
    }
    public function metodos() { return $this->model->metodosPago(); }
    public function listarPara($rol, $idUsuario) {
        if (in_array($rol, ['Administrador','Empleado'], true)) {
            return $this->model->listarVentas(null);
        }
        $idCli = $this->model->idClienteDeUsuario($idUsuario);
        if (empty($idCli)) return [];
        return $this->model->listarVentas($idCli);
    }
    public function detalleVenta($idVenta) { return $this->model->detalleVenta($idVenta); }
}
