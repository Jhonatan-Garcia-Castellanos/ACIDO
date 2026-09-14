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
        // RF 5.3/4.6: no agregar inactivos ni agotados, y topar al stock real
        try {
            $det = $this->model->detalleCarrito([$id => 1]);
            if (empty($det['items'])) return false;
            $it = $det['items'][0];
            if ((int)($it['Activo'] ?? 0) !== 1) return false;
            $stock = (int)($it['Stock_Actual'] ?? 0);
            if ($stock <= 0) return false;
            $qty = max(1, min(10, min((int)$qty, $stock)));
        } catch (Exception $e) {
            $qty = max(1, min(10, (int)$qty));
        }
        $cart = $this->cart();
        $cart[$id] = min(10, ($cart[$id] ?? 0) + $qty);
        // Re-topa el acumulado al stock vigente
        try {
            $det2 = $this->model->detalleCarrito([$id => $cart[$id]]);
            if (!empty($det2['items'])) {
                $st2 = (int)($det2['items'][0]['Stock_Actual'] ?? 0);
                if ($st2 > 0 && $cart[$id] > $st2) $cart[$id] = min(10, $st2);
            }
        } catch (Exception $e) {}
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
     * Checkout con detalle de pago (banco/entidad + número + comprobante).
     * Validaciones RF 5.1: tarjeta 13-19 dígitos + Luhn; Nequi/Daviplata
     * exactamente 10 dígitos; transferencia exige comprobante JPG/PDF ≤5MB.
     * El número solo se guarda enmascarado (nunca el número completo).
     */
    public function checkout($idUsuario, $idMetodo, $detalle = [], $comprobante = null) {
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
        $esNequiDavi = strpos($tipo, 'nequi') !== false || strpos($tipo, 'daviplata') !== false || strpos($tipo, 'davi') !== false;
        $esTransfer = strpos($tipo, 'transferencia') !== false || strpos($tipo, 'transfer') !== false;

        if ($esTarjeta) {
            $num = preg_replace('/\D+/', '', (string)($detalle['numero_tarjeta'] ?? ''));
            if (strlen($num) < 13 || strlen($num) > 19) {
                return ['error' => 'Número de tarjeta inválido (debe tener entre 13 y 19 dígitos).'];
            }
            if (!$this->luhn($num)) {
                return ['error' => 'El número de tarjeta no es válido (falla el dígito de verificación).'];
            }
            $referencia = '•••• •••• •••• ' . substr($num, -4);
        } elseif ($esNequiDavi) {
            $num = preg_replace('/\D+/', '', (string)($detalle['cuenta'] ?? ''));
            if (strlen($num) !== 10) {
                return ['error' => 'Nequi/Daviplata: el número debe tener exactamente 10 dígitos.'];
            }
            $referencia = '•••••• ' . substr($num, -4);
        } elseif ($tieneLogo) {
            $num = preg_replace('/\D+/', '', (string)($detalle['cuenta'] ?? ''));
            if (strlen($num) < 7 || strlen($num) > 20) {
                return ['error' => 'Número de cuenta/celular inválido (7 a 20 dígitos).'];
            }
            $referencia = '•••••• ' . substr($num, -4);
        } else {
            $referencia = 'Pago en ' . $tipoMetodo;
        }

        // Transferencia: comprobante JPG/PDF obligatorio
        $rutaComp = null;
        if ($esTransfer) {
            $sub = $this->guardarComprobante($comprobante);
            if (empty($sub['ok'])) return ['error' => $sub['message']];
            $rutaComp = $sub['ruta'];
        }

        $res = $this->model->checkout($idUsuario, $this->cart(), $idMetodo, [
            'entidad' => $entidad,
            'referencia' => $referencia,
            'comprobante' => $rutaComp,
        ]);
        if (isset($res['ID_Venta'])) $this->vaciar();
        elseif ($rutaComp !== null) {
            // Si la venta falló, no dejar el archivo huérfano
            $fs = $this->comprobanteFs($rutaComp);
            if ($fs !== null && is_file($fs)) @unlink($fs);
        }
        return $res;
    }

    /**
     * Guarda comprobante de transferencia: JPG/PNG/WEBP (imagen real) o PDF,
     * máximo 5MB, en public/img/comprobantes/. Retorna ['ok','ruta'=>web].
     */
    public function guardarComprobante($file) {
        if (!is_array($file) || !isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
            return ['ok' => false, 'message' => 'La transferencia exige adjuntar el comprobante (JPG o PDF).'];
        }
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['ok' => false, 'message' => 'No se pudo subir el comprobante. Intenta de nuevo.'];
        }
        if (($file['size'] ?? 0) > 5 * 1024 * 1024) {
            return ['ok' => false, 'message' => 'El comprobante supera 5MB.'];
        }
        $ext = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));
        $esImg = in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true);
        $esPdf = ($ext === 'pdf');
        if (!$esImg && !$esPdf) {
            return ['ok' => false, 'message' => 'Comprobante inválido: solo JPG o PDF.'];
        }
        if ($esImg) {
            $info = @getimagesize($file['tmp_name']);
            $mimes = ['image/jpeg', 'image/png', 'image/webp'];
            if ($info === false || !in_array($info['mime'], $mimes, true)) {
                return ['ok' => false, 'message' => 'El archivo no es una imagen válida.'];
            }
        } else {
            // PDF: verifica cabecera %PDF en los primeros bytes
            $fh = @fopen($file['tmp_name'], 'rb');
            $head = $fh ? @fread($fh, 5) : false;
            if ($fh) @fclose($fh);
            if ($head !== '%PDF-') {
                return ['ok' => false, 'message' => 'El archivo no es un PDF válido.'];
            }
        }
        $dirFs = __DIR__ . '/../public/img/comprobantes';
        if (!is_dir($dirFs) && !@mkdir($dirFs, 0755, true)) {
            return ['ok' => false, 'message' => 'No se pudo guardar el comprobante.'];
        }
        $nuevo = 'comp_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        if (!@move_uploaded_file($file['tmp_name'], $dirFs . '/' . $nuevo)) {
            return ['ok' => false, 'message' => 'No se pudo guardar el comprobante.'];
        }
        return ['ok' => true, 'ruta' => '/ACIDO/BACKPHP_TEST/public/img/comprobantes/' . $nuevo];
    }

    /** Convierte ruta web de comprobante a ruta física (o null si es externa). */
    private function comprobanteFs($rutaWeb) {
        $rutaWeb = (string)$rutaWeb;
        if (strpos($rutaWeb, '/ACIDO/BACKPHP_TEST/') !== 0) return null;
        return __DIR__ . '/../' . ltrim(preg_replace('#^/ACIDO/BACKPHP_TEST/#', '', $rutaWeb), '/');
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
    public function listarPara($rol, $idUsuario, $page = 1, $per = 10, $q = '', $desde = '', $hasta = '', $order = 'ID_Venta', $dir = 'DESC') {
        if (in_array($rol, ['Administrador','Empleado'], true)) {
            return $this->model->listarVentas(null, $page, $per, $q, $desde, $hasta, $order, $dir);
        }
        $idCli = $this->model->idClienteDeUsuario($idUsuario);
        if (empty($idCli)) return [];
        return $this->model->listarVentas($idCli, $page, $per, $q, $desde, $hasta, $order, $dir);
    }
    public function contarPara($rol, $idUsuario, $q = '', $desde = '', $hasta = '') {
        if (in_array($rol, ['Administrador','Empleado'], true)) {
            return $this->model->contarVentas(null, $q, $desde, $hasta);
        }
        $idCli = $this->model->idClienteDeUsuario($idUsuario);
        if (empty($idCli)) return 0;
        return $this->model->contarVentas($idCli, $q, $desde, $hasta);
    }
    public function exportarPara($rol, $idUsuario, $q = '', $desde = '', $hasta = '') {
        if (in_array($rol, ['Administrador','Empleado'], true)) {
            return $this->model->exportarVentas(null, $q, $desde, $hasta);
        }
        $idCli = $this->model->idClienteDeUsuario($idUsuario);
        if (empty($idCli)) return [];
        return $this->model->exportarVentas($idCli, $q, $desde, $hasta);
    }
    public function detalleVenta($idVenta) { return $this->model->detalleVenta($idVenta); }
    public function cambiarEstadoPedido($idPedido, $nuevoEstado, $motivo = '') { return $this->model->cambiarEstadoPedido($idPedido, $nuevoEstado, $motivo); }
    public function detalleCompleto($idVenta) { return $this->model->detalleCompleto($idVenta); }
    public function pendientesPara($rol, $idUsuario, $q = '', $desde = '', $hasta = '') {
        if (in_array($rol, ['Administrador','Empleado'], true)) return $this->model->listarPendientes(null, $q, $desde, $hasta);
        $idCli = $this->model->idClienteDeUsuario($idUsuario);
        return $idCli ? $this->model->listarPendientes($idCli, $q, $desde, $hasta) : [];
    }
    public function entregadasPara($rol, $idUsuario, $q = '', $desde = '', $hasta = '') {
        if (in_array($rol, ['Administrador','Empleado'], true)) return $this->model->listarEntregadas(null, $q, $desde, $hasta);
        $idCli = $this->model->idClienteDeUsuario($idUsuario);
        return $idCli ? $this->model->listarEntregadas($idCli, $q, $desde, $hasta) : [];
    }
    public function facturasPara($rol, $idUsuario, $q = '', $desde = '', $hasta = '') {
        if (in_array($rol, ['Administrador','Empleado'], true)) return $this->model->listarFacturas(null, $q, $desde, $hasta);
        $idCli = $this->model->idClienteDeUsuario($idUsuario);
        return $idCli ? $this->model->listarFacturas($idCli, $q, $desde, $hasta) : [];
    }
    public function resumenHoyPara($rol, $idUsuario) {
        if (in_array($rol, ['Administrador','Empleado'], true)) {
            return $this->model->resumenHoy(null);
        }
        $idCli = $this->model->idClienteDeUsuario($idUsuario);
        if (empty($idCli)) return ['ventas'=>0,'ganancias'=>0,'ticket'=>0];
        return $this->model->resumenHoy($idCli);
    }
}
