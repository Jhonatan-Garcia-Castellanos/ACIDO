<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Acido</title>
    <!-- Cargar Tipografía y Alertas Globales -->
    <link rel="stylesheet" href="/ACIDO/BACKPHP/public/css/global.css?v=<?php echo time(); ?>">
    <!-- Cargar Estilos Exclusivos del Login -->
    <link rel="stylesheet" href="/ACIDO/BACKPHP/public/css/auth.css?v=<?php echo time(); ?>">
    <!-- FontAwesome para los iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;700&display=swap" rel="stylesheet">
</head>

<body>

    <div class="main-screen">
        <!-- Esfera flotante exterior en la esquina inferior derecha -->
        <div class="outer-circle"></div>

        <div class="login-wrapper">
            <!-- Panel Izquierdo Azul con Esferas -->
            <div class="brand-panel">
                <div class="circle circle-top"></div>
                <div class="circle circle-bottom-left"></div>
                <div class="circle circle-bottom-right"></div>

                <div class="brand-content">
                    <h1>BIENVENIDOS</h1>
                    <p>Ingresa a tu cuenta para gestionar tus módulos y consultar la información de tu plataforma.</p>
                </div>
            </div>

            <!-- Panel Derecho: Formulario -->
            <div class="form-panel">
                <img src="/ACIDO/BACKPHP/public/iconooo.png" alt="icon" class="logoaci">
                <h3>Iniciar Sesión</h3>
                <p class="subtitle">Ingresa tus credenciales para continuar</p>

                <!-- Mensaje de error para JavaScript -->
                <div id="errorMsg" class="error-msg" style="display: none;"></div>

                <!-- UN SOLO FORMULARIO CON EL ID CORRECTO -->
                <form id="loginForm" method="POST">
                    <input type="hidden" name="action" value="login">

                    <div class="input-group">
                        <input type="email" name="email" placeholder="Correo electrónico" required>
                    </div>

                    <div class="input-group">
                        <input type="password" name="password" id="passInput" placeholder="Contraseña" required>
                        <span class="show-btn" onclick="togglePass()">VER</span>
                    </div>

                    <div class="options-row">
                        <label class="remember">
                            <input type="checkbox"> Recordarme
                        </label>
                        <a href="#" class="forgot">¿Olvidaste tu contraseña?</a>
                    </div>

                    <button type="submit" class="btn-primary">Iniciar Sesión</button>
                </form>

                <div class="divider">
                    <span>O</span>
                </div>

                <p class="signup-text">
                    ¿No tienes una cuenta? <a href="/ACIDO/BACKPHP/index.php?action=register">Regístrate</a>
                </p>
            </div>
        </div>
    </div>

    <script>
        function togglePass() {
            const pass = document.getElementById('passInput');
            const btn = document.querySelector('.show-btn');
            if (pass.type === 'password') {
                pass.type = 'text';
                btn.textContent = 'OCULTAR';
            } else {
                pass.type = 'password';
                btn.textContent = 'VER';
            }
        }

        document.getElementById('loginForm').addEventListener('submit', async function (e) {
            e.preventDefault(); // Detiene el recargo de pantalla

            const formData = new FormData(this);
            const errorContainer = document.getElementById('errorMsg');
            errorContainer.style.display = 'none';

            try {
                const response = await fetch('/ACIDO/BACKPHP/index.php', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    window.location.href = data.redirect || '/ACIDO/BACKPHP/index.php?action=dashboard';
                } else {
                    errorContainer.textContent = data.message || 'Correo o contraseña incorrectos.';
                    errorContainer.style.display = 'block';
                }
            } catch (error) {
                console.error(error);
                errorContainer.textContent = 'Ocurrió un error al procesar la solicitud.';
                errorContainer.style.display = 'block';
            }
        });
    </script>
</body>

</html>