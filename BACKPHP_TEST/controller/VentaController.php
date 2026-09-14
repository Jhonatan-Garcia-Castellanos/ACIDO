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
    /**
     * Checkout con detalle de pago (banco/entidad + número).
     * Validaciones: tarjeta 13-19 dígitos + Luhn; cuenta/celular 7-20 dígitos.
     * El número solo se guarda enmascarado (nunca el número completo).
     */
    public function checkout($idUsuario, $idMetodo, $detalle = []) {
        $detalle = is_array($detalle) ? $detalle : [];
        $metodos = $this->model->metodosPago();
        $tipoMetodo = '';
        $tieneLogo = false;
        foreach ($metodos as $m) {
            if ((string)$m['ID_Metodo'] === (string)$idMetodo) {
                $tipoMetodo = $m['Tipo_Metodo'];
                $tieneLogo = !empty($m['Logo']);
                break;
            }
        }
        if ($tipoMetodo === '') return ['error' => 'Selecciona un método de pago.'];

        $entidad = trim($detalle['entidad'] ?? '');
        if ($entidad === '') $entidad = $tipoMetodo;

        $tipo = strtolower($tipoMetodo);
        $esTarjeta = strpos($tipo, 'tarjeta') !== false || strpos($tipo, 'visa') !== false || strpos($tipo, 'card') !== false;

        if ($esTarjeta) {
            $num = preg_replace('/\D+/', '', (string)($detalle['numero_tarjeta'] ?? ''));
            if (strlen($num) < 13 || strlen($num) > 19) {
                return ['error' => 'Número de tarjeta inválido (debe tener entre 13 y 19 dígitos).'];
            }
            if (!$this->luhn($num)) {
                return ['error' => 'El número de tarjeta no es válido (falla el dígito de verificación).'];
            }
            $referencia = '•••• •••• •••• ' . substr($num, -4);
        } elseif ($tieneLogo) {
            $num = preg_replace('/\D+/', '', (string)($detalle['cuenta'] ?? ''));
            if (strlen($num) < 7 || strlen($num) > 20) {
                return ['error' => 'Número de cuenta/celular inválido (7 a 20 dígitos).'];
            }
            $referencia = '•••••• ' . substr($num, -4);
        } else {
            $referencia = 'Pago en ' . $tipoMetodo;
        }

        $res = $this->model->checkout($idUsuario, $this->cart(), $idMetodo, [
            'entidad' => $entidad,
            'referencia' => $referencia,
        ]);
        if (isset($res['ID_Venta'])) $this->vaciar();
        return $res;
    }

    /** Verificación Luhn (dígito de control de tarjetas VISA/Mastercard). */
    private function luhn($num) {
        $sum = 0;
        $alt = false;
        for ($i = strlen($num) - 1; $i >= 0; $i--) {
            $d = (int)$num[$i];
            if ($alt) { $d *= 2; if ($d > 9) $d -= 9; }
            $sum += $d;
            $alt = !$alt;
        }
        return ($sum % 10) === 0;
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
    public function resumenHoyPara($rol, $idUsuario) {
        if (in_array($rol, ['Administrador','Empleado'], true)) {
            return $this->model->resumenHoy(null);
        }
        $idCli = $this->model->idClienteDeUsuario($idUsuario);
        if (empty($idCli)) return ['ventas'=>0,'ganancias'=>0,'ticket'=>0];
        return $this->model->resumenHoy($idCli);
    }
}
