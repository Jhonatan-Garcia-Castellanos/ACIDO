<?php
require_once __DIR__ . "/../model/PasswordReset.php";
require_once __DIR__ . "/../lib/Mailer.php";

class PasswordResetController
{
    private $model;
    private $mailer;
    public function __construct()
    {
        $this->model = new PasswordReset();
        $this->mailer = new Mailer();
    }

    public function passwordRegex()
    {
        return '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&._\-#])[A-Za-z\d@$!%*?&._\-#]{8,20}$/';
    }

    /**
     * Paso 1: solicitar link. Siempre retorna mensaje genérico (anti-enumeración).
     * Retorna ['sent'=>bool] + mensaje para la vista. En modo DEBUG con ?debug=1 expone el link.
     */
    public function solicitar($email)
    {
        $msg = 'Si el correo existe, enviamos un enlace de recuperación (revisa tu inbox de Mailtrap).';
        $user = $this->model->usuarioPorEmail($email);
        if (!$user) return ['sent' => false, 'message' => $msg, 'link' => null];
        $token = $this->model->crearToken($user['ID_Usuario'], 1);
        $link = $this->mailer->getAppUrl() . "/index.php?action=reset_password&token=" . $token;
        $r = $this->mailer->enviarRecuperacion($user['Email'], $link);
        if (!$r['ok']) {
            // En local sin SMTP configurado, permite copiar el link con ?debug=1
            return ['sent' => false, 'message' => $r['error'], 'link' => $link];
        }
        return ['sent' => true, 'message' => $msg, 'link' => null];
    }

    public function validarToken($token)
    {
        return $this->model->validarToken($token);
    }

    /** Paso 2: guardar nueva clave. Retorna ['ok'=>bool,'message'=>string]. */
    public function restablecer($token, $nueva, $confirmar)
    {
        if ($nueva !== $confirmar) return ['ok' => false, 'message' => 'Las contraseñas no coinciden.'];
        if (!preg_match($this->passwordRegex(), $nueva)) {
            return ['ok' => false, 'message' => 'La contraseña debe tener 8-20 caracteres, mayúscula, minúscula, número y símbolo.'];
        }
        $ok = $this->model->consumirToken($token, $nueva);
        return $ok
            ? ['ok' => true, 'message' => 'Contraseña actualizada. Ya puedes iniciar sesión.']
            : ['ok' => false, 'message' => 'Enlace inválido o vencido. Solicita uno nuevo.'];
    }
}
