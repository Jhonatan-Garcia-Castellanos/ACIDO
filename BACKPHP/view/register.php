<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Cuenta - Ácido Colombia</title>

    <!-- Importación de fuentes e íconos -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="/ACIDO/BACKPHP/public/styles.css">
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
                    <h1>ÚNETE A </h1>
                    <h2>ACIDO COLOMBIA</h2>
                    <p>Crea tu cuenta para comenzar a gestionar tus proyectos y acceder a todas las funciones de la plataforma.</p>
                </div>
            </div>

            <!-- Panel Derecho -->
            <div class="form-panel">
                <img src="/ACIDO/BACKPHP/public/iconooo.png" alt="icon" class="logoaci">
                <h3>Crear Cuenta</h3>
                <p class="subtitle">Ingresa tus datos para registrarte</p>

                <!-- Contenedor dinámico de alertas (Éxito / Error) -->
                <div id="alertMsg" class="alert-msg" style="display: none;"></div>

                <form id="registerForm" method="POST">
                    <input type="hidden" name="action" value="register">

                    <div class="input-group">
                        <input type="email" name="email" placeholder="Correo electrónico" required>
                    </div>

                    <div class="input-group">
                        <input type="password" name="password" id="passInput" placeholder="Contraseña" required>
                        <span class="show-btn" onclick="togglePass()">VER</span>
                    </div>

                    <button type="submit" class="btn-primary">Registrarse</button>
                </form>

                <div class="divider">
                    <span>O</span>
                </div>

                <p class="signup-text">
                    ¿Ya tienes una cuenta? <a href="/ACIDO/BACKPHP/index.php?action=login">Inicia Sesión</a>
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

        // Manejo del registro por AJAX
        document.getElementById('registerForm').addEventListener('submit', async function (e) {
            e.preventDefault();

            const alertBox = document.getElementById('alertMsg');
            alertBox.style.display = 'none';
            alertBox.className = 'alert-msg'; // Resetear clases de color

            const formData = new FormData(this);

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
                    // Éxito: Fondo verde
                    alertBox.textContent = data.message || '¡Registro exitoso! Redirigiendo...';
                    alertBox.classList.add('alert-success');
                    alertBox.style.display = 'block';

                    // Limpiar el formulario
                    this.reset();

                    // Redirigir al login tras 2 segundos
                    setTimeout(() => {
                        window.location.href = data.redirect || '/ACIDO/BACKPHP/index.php?action=login';
                    }, 2000);
                } else {
                    // Error: Fondo rojo
                    alertBox.textContent = data.message || 'Error al registrar el usuario.';
                    alertBox.classList.add('alert-error');
                    alertBox.style.display = 'block';
                }
            } catch (error) {
                console.error(error);
                alertBox.textContent = 'Ocurrió un problema al procesar la solicitud.';
                alertBox.classList.add('alert-error');
                alertBox.style.display = 'block';
            }
        });
    </script>
</body>
</html>
