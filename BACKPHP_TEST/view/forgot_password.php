<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña - Ácido Colombia</title>
    <link rel="stylesheet" href="/ACIDO/BACKPHP_TEST/public/css/global.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="/ACIDO/BACKPHP_TEST/public/css/auth.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="main-screen">
        <div class="outer-circle"></div>
        <div class="login-wrapper">
            <div class="brand-panel">
                <div class="circle circle-top"></div>
                <div class="circle circle-bottom-left"></div>
                <div class="circle circle-bottom-right"></div>
                <div class="brand-content">
                    <h1>RECUPERAR</h1>
                    <p>Te enviamos un enlace a tu correo para crear una nueva contraseña. Vence en 1 hora.</p>
                </div>
            </div>
            <div class="form-panel">
                <img src="/ACIDO/BACKPHP_TEST/public/img/iconooo.png" alt="icon" class="logoaci">
                <h3>¿Olvidaste tu contraseña?</h3>
                <p class="subtitle">Ingresa tu correo y revisa tu inbox de Mailtrap</p>
                <?php $info = $_GET['info'] ?? ''; $err = $_GET['error'] ?? ''; ?>
                <?php if ($info): ?><div class="error-msg" style="display:block;background:#d4edda;color:#155724;border:1px solid #c3e6cb;"><?php echo htmlspecialchars($info); ?></div><?php endif; ?>
                <?php if ($err): ?><div id="errorMsg" class="error-msg" style="display:block;"><?php echo htmlspecialchars($err); ?></div><?php endif; ?>
                <?php if (!empty($debugLink)): ?>
                    <div class="error-msg" style="display:block;background:#fff3cd;color:#856404;border:1px solid #ffeeba;word-break:break-all;">
                        SMTP sin configurar. Link de prueba:<br><a href="<?php echo htmlspecialchars($debugLink); ?>"><?php echo htmlspecialchars($debugLink); ?></a>
                    </div>
                <?php endif; ?>
                <form method="POST" action="index.php?action=forgot_password">
                    <input type="hidden" name="reset_action" value="request">
                    <div class="input-group">
                        <input type="email" name="email" placeholder="Correo electrónico" required autocomplete="email">
                    </div>
                    <button type="submit" class="btn-primary">Enviar enlace</button>
                </form>
                <div class="divider"><span>O</span></div>
                <p class="signup-text"><a href="/ACIDO/BACKPHP_TEST/index.php?action=login">Volver al login</a></p>
            </div>
        </div>
    </div>
</body>
</html>
