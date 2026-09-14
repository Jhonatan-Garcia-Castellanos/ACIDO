<?php
// controller/NotificacionController.php — API de notificaciones (RF 2.3 + 2.7/2.9).
// Centraliza campana de stock bajo y envío de correos al admin en respuestas
// JSON listas para consumir desde JS, polling o integraciones.
// Uso (index.php): $n = new NotificacionController(); $n->resumen(); etc.
require_once __DIR__ . "/../model/AlertaStock.php";
require_once __DIR__ . "/../model/Movimiento.php";
require_once __DIR__ . "/../lib/Mailer.php";
require_once __DIR__ . "/../config/conexion.php";

class NotificacionController
{
    private $alertas;
    private $mov;
    private $mailer;

    public function __construct()
    {
        $this->alertas = new AlertaStock();
        $this->mov = new Movimiento();
        $this->mailer = new Mailer();
    }

    /** GET api_alertas: campana + stock bajo + total kardex. */
    public function resumen()
    {
        try {
            $nuevas = $this->alertas->sincronizarBajoMinimo();
            return [
                'success' => true,
                'count' => $this->alertas->contarNoLeidas(),
                'items' => $this->alertas->listarNoLeidas(10),
                'nuevas' => $nuevas,
                'bajo' => $this->alertas->listarStockBajo(50),
                'smtp' => $this->mailer->isConfigured(),
            ];
        } catch (Exception $e) {
            error_log("Notificacion::resumen: " . $e->getMessage());
            return ['success' => false, 'count' => 0, 'items' => [], 'nuevas' => [], 'bajo' => []];
        }
    }

    /** POST api_alertas_leida: {id_alerta} o {todas:true}. */
    public function marcar($id = null, $todas = false)
    {
        try {
            if ($todas) {
                $ok = $this->alertas->marcarTodas();
                return ['success' => (bool)$ok, 'message' => $ok ? 'Todas marcadas como leídas' : 'Nada por marcar'];
            }
            if ($id === null || !ctype_digit((string)$id)) {
                return ['success' => false, 'message' => 'id_alerta inválido'];
            }
            $ok = $this->alertas->marcarLeida($id);
            return ['success' => (bool)$ok, 'message' => $ok ? "Alerta $id leída" : "Alerta $id no existe"];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error interno'];
        }
    }

    /** GET api_kardex: ?limit & ?producto. Solo lectura (auditoría inmutable). */
    public function kardex($limit = 50, $idProducto = null)
    {
        try {
            return ['success' => true, 'items' => $this->mov->listar($limit, $idProducto)];
        } catch (Exception $e) {
            return ['success' => false, 'items' => []];
        }
    }

    /** GET api_notif_log: historial auditable de correos (ok/fallo). */
    public function historial($limit = 20)
    {
        $limit = max(1, min(100, (int)$limit));
        try {
            $c = new Conexion();
            $rows = $c->conn->query(
                "SELECT ID_Log, Fecha, Tipo, Destinatarios, Asunto, Resultado, Detalle " .
                "FROM notificacion_log ORDER BY ID_Log DESC LIMIT $limit"
            )->fetchAll(PDO::FETCH_ASSOC);
            return ['success' => true, 'items' => $rows];
        } catch (Exception $e) {
            return ['success' => true, 'items' => [], 'nota' => 'Ejecuta migrate_notif_log.sql para activar el historial'];
        }
    }

    /**
     * POST api_notificar: envía correo al admin.
     * $tipo: 'stock' (solo episodios nuevos, dedup 24h) | 'stock_forzar' (todo lo no leído)
     *        | 'kardex' (requiere $datos del movimiento) | 'resumen' (digest: bajos + últimos kardex).
     */
    public function notificar($tipo, $datos = [], $responsable = 'API')
    {
        try {
            $admins = array_values(array_filter((array)$this->alertas->emailsAdmins()));
            if (empty($admins)) return ['success' => false, 'message' => 'Sin administradores en BD'];
            if (!$this->mailer->isConfigured()) {
                return ['success' => false, 'message' => 'SMTP sin configurar (.env MAIL_*)'];
            }
            if ($tipo === 'stock' || $tipo === 'stock_forzar') {
                if ($tipo === 'stock_forzar') {
                    $items = $this->alertas->listarNoLeidas(50);
                    $items = array_map(function ($a) {
                        return $a + ['Nombre_Producto' => $a['Nombre_Producto'] ?? ($a['Mensaje'] ?? '?'),
                            'Stock_Actual' => $a['Stock_Actual'] ?? 0, 'Stock_Minimo' => $a['Stock_Minimo'] ?? 10];
                    }, $items);
                } else {
                    $items = $this->alertas->sincronizarBajoMinimo();
                }
                if (empty($items)) return ['success' => true, 'message' => 'Sin episodios nuevos, no se envió nada', 'enviados' => 0];
                $r = $this->mailer->enviarStockBajo($admins, $items);
                return $r['ok']
                    ? ['success' => true, 'message' => 'Correo de stock enviado a ' . count($admins) . ' admin(s)', 'enviados' => count($admins)]
                    : ['success' => false, 'message' => 'SMTP: ' . $r['error']];
            }
            if ($tipo === 'kardex') {
                $d = $datos + ['Responsable' => $responsable];
                if (empty($d['Producto']) || empty($d['Motivo'])) {
                    return ['success' => false, 'message' => 'kardex requiere Producto y Motivo'];
                }
                $r = $this->mailer->enviarMovimientoKardex($admins, $d);
                return $r['ok']
                    ? ['success' => true, 'message' => 'Correo Kardex enviado a ' . count($admins) . ' admin(s)', 'enviados' => count($admins)]
                    : ['success' => false, 'message' => 'SMTP: ' . $r['error']];
            }
            if ($tipo === 'resumen') {
                $bajos = $this->alertas->listarStockBajo(50);
                $km = $this->mov->listar(10);
                $lineas = ['DIGEST ACIDO ' . date('Y-m-d H:i')];
                $lineas[] = 'Bajo minimo (' . count($bajos) . '): ' . implode(', ', array_map(function ($b) {
                    return $b['Nombre_Producto'] . ' (' . $b['Stock_Actual'] . '/' . $b['Stock_Minimo'] . ')';
                }, $bajos));
                $lineas[] = 'Ultimos kardex: ' . implode(' | ', array_map(function ($k) {
                    return $k['Fecha_Movimiento'] . ' ' . $k['Tipo_Movimiento'] . ' ' . $k['Cantidad'] . ' ' . $k['Nombre_Producto'];
                }, $km));
                $r = $this->mailer->enviarMovimientoKardex($admins, [
                    'Producto' => 'Resumen digest (' . count($bajos) . ' bajos)',
                    'Tipo' => 'Resumen', 'Cantidad' => 0,
                    'Motivo' => implode("\n", $lineas),
                    'Responsable' => $responsable,
                ]);
                return $r['ok']
                    ? ['success' => true, 'message' => 'Digest enviado a ' . count($admins) . ' admin(s)', 'enviados' => count($admins)]
                    : ['success' => false, 'message' => 'SMTP: ' . $r['error']];
            }
            return ['success' => false, 'message' => "tipo inválido: usa stock|stock_forzar|kardex|resumen"];
        } catch (Exception $e) {
            error_log("Notificacion::notificar: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error interno'];
        }
    }
}
