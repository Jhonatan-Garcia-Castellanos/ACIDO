<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cambiar Contraseña - Ácido Colombia</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="/ACIDO/BACKPHP_TEST/public/css/global.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="/ACIDO/BACKPHP_TEST/public/css/auth.css?v=<?php echo time(); ?>">
    <style>
        /* Ajuste solo para esta vista: 4 campos necesitan tarjeta más alta */
        .login-wrapper { height: auto !important; min-height: 600px; }
        .brand-panel { min-height: 600px; }
        .form-panel { padding: 28px 40px; justify-content: flex-start; }
        .form-panel .input-group { margin-bottom: 10px; }
        #passHelpText { margin: 2px 0 8px; }
    </style>
</head>

<body>

    <div class="main-screen">
        <div class="outer-circle"></div>

        <div class="login-wrapper">
            <!-- Panel Izquierdo -->
            <div class="brand-panel">
                <div class="circle circle-top"></div>
                <div class="circle circle-bottom-left"></div>
                <div class="circle circle-bottom-right"></div>

                <div class="brand-content">
                    <h1>SEGURIDAD</h1>
                    <p>Actualiza tu contraseña para mantener tu cuenta protegida en la plataforma.</p>
                </div>
            </div>

            <!-- Panel Derecho -->
            <div class="form-panel">
                <img src="/ACIDO/BACKPHP_TEST/public/img/iconooo.png" alt="icon" class="logoaci">
                <h3>Cambiar Contraseña</h3>
                <p class="subtitle">Ingresa tus credenciales y la nueva clave</p>

                <?php if (isset($_GET['error'])): ?>
                    <div id="alertMsg" class="alert-msg alert-error" style="display: block;"><?= htmlspecialchars($_GET['error']) ?></div>
                <?php else: ?>
                    <div id="alertMsg" class="alert-msg" style="display: none;"></div>
                <?php endif; ?>

                <form id="passForm" action="/ACIDO/BACKPHP_TEST/controller/change_password.php" method="POST">
                    <div class="input-group">
                        <input type="email" name="email" placeholder="Correo electrónico" required autocomplete="email">
                    </div>

                    <div class="input-group input-group-pass">
                        <input type="password" name="actual_password" id="actualInput" placeholder="Contraseña actual" required autocomplete="current-password">
                        <span class="show-btn" onclick="togglePass('actualInput', this)">VER</span>
                    </div>

                    <div class="input-group input-group-pass">
                        <input type="password" name="nueva_password" id="nuevaInput" placeholder="Nueva contraseña" required autocomplete="new-password">
                        <span class="show-btn" onclick="togglePass('nuevaInput', this)">VER</span>
                    </div>

                    <p id="passHelpText" class="pass-requirements-text">
                        8 a 20 caracteres, mayúscula, minúscula, número y símbolo (@$!%*?&._-#)
                    </p>

                    <div class="input-group input-group-pass">
                        <input type="password" name="confirmar_password" id="confirmInput" placeholder="Confirmar nueva contraseña" required autocomplete="new-password">
                        <span class="show-btn" onclick="togglePass('confirmInput', this)">VER</span>
                    </div>

                    <button type="submit" class="btn-primary">Guardar Contraseña</button>
                </form>

                <div class="divider">
                    <span>O</span>
                </div>

                <p class="signup-text">
                    ¿Recuerdas tu clave? <a href="/ACIDO/BACKPHP_TEST/index.php?action=login">Inicia Sesión</a>
                </p>
            </div>
        </div>
    </div>

    <script>
        function togglePass(inputId, btn) {
            const pass = document.getElementById(inputId);
            if (pass.type === 'password') {
                pass.type = 'text';
                btn.textContent = 'OCULTAR';
            } else {
                pass.type = 'password';
                btn.textContent = 'VER';
            }
        }

        const nuevaInput = document.getElementById('nuevaInput');
        const confirmInput = document.getElementById('confirmInput');
        const passHelpText = document.getElementById('passHelpText');
        const alertBox = document.getElementById('alertMsg');
        const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&._\-#])[A-Za-z\d@$!%*?&._\-#]{8,20}$/;

        nuevaInput.addEventListener('input', function () {
            const value = this.value;
            if (value.length === 0) {
                passHelpText.classList.remove('valid', 'invalid');
            } else if (passwordRegex.test(value)) {
                passHelpText.classList.remove('invalid');
                passHelpText.classList.add('valid');
            } else {
                passHelpText.classList.remove('valid');
                passHelpText.classList.add('invalid');
            }
        });

        document.getElementById('passForm').addEventListener('submit', function (e) {
            const nueva = nuevaInput.value;
            const confirmar = confirmInput.value;
            alertBox.style.display = 'none';
            alertBox.className = 'alert-msg';

            if (!passwordRegex.test(nueva)) {
                e.preventDefault();
                alertBox.textContent = 'La nueva contraseña no cumple con los requisitos indicados.';
                alertBox.classList.add('alert-error');
                alertBox.style.display = 'block';
                return;
            }
            if (nueva !== confirmar) {
                e.preventDefault();
                alertBox.textContent = 'Las nuevas contraseñas no coinciden.';
                alertBox.classList.add('alert-error');
                alertBox.style.display = 'block';
            }
        });
    </script>
</body>

</html>
