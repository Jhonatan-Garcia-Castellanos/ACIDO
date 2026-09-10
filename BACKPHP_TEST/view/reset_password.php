<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Contraseña - Ácido Colombia</title>
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
                    <h1>NUEVA CLAVE</h1>
                    <p>Crea una contraseña segura de 8 a 20 caracteres con mayúscula, número y símbolo.</p>
                </div>
            </div>
            <div class="form-panel">
                <img src="/ACIDO/BACKPHP_TEST/public/img/iconooo.png" alt="icon" class="logoaci">
                <h3>Restablecer contraseña</h3>
                <p class="subtitle"><?php echo htmlspecialchars($resetEmail ?? ''); ?></p>
                <div id="alertMsg" class="error-msg" style="display:none;"></div>
                <?php $err = $_GET['error'] ?? ''; ?>
                <?php if ($err): ?><div class="error-msg" style="display:block;"><?php echo htmlspecialchars($err); ?></div><?php endif; ?>
                <form id="resetForm" method="POST" action="index.php?action=reset_password&token=<?php echo htmlspecialchars($resetToken ?? ''); ?>">
                    <input type="hidden" name="reset_action" value="reset">
                    <input type="hidden" name="token" value="<?php echo htmlspecialchars($resetToken ?? ''); ?>">
                    <div class="input-group">
                        <input type="password" name="nueva_password" id="nuevaInput" placeholder="Nueva contraseña" required autocomplete="new-password">
                    </div>
                    <p class="pass-requirements-text" id="passHelpText">8 a 20 caracteres, mayúscula, minúscula, número y símbolo (@$!%*?&amp;._-#)</p>
                    <div class="input-group">
                        <input type="password" name="confirmar_password" id="confirmInput" placeholder="Confirmar nueva contraseña" required autocomplete="new-password">
                    </div>
                    <button type="submit" class="btn-primary">Guardar contraseña</button>
                </form>
                <div class="divider"><span>O</span></div>
                <p class="signup-text"><a href="/ACIDO/BACKPHP_TEST/index.php?action=login">Volver al login</a></p>
            </div>
        </div>
    </div>
    <script>
        const rx = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&._\-#])[A-Za-z\d@$!%*?&._\-#]{8,20}$/;
        document.getElementById('resetForm').addEventListener('submit', function (e) {
            const n = document.getElementById('nuevaInput').value;
            const c = document.getElementById('confirmInput').value;
            const box = document.getElementById('alertMsg');
            box.style.display = 'none';
            if (!rx.test(n)) { e.preventDefault(); box.textContent = 'La contraseña no cumple los requisitos.'; box.style.display = 'block'; return; }
            if (n !== c) { e.preventDefault(); box.textContent = 'Las contraseñas no coinciden.'; box.style.display = 'block'; }
        });
    </script>
</body>
</html>
