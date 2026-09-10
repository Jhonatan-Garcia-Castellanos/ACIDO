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

    <!-- CSS Independientes de Ácido Colombia -->
    <link rel="stylesheet" href="/ACIDO/BACKPHP_TEST/public/css/global.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="/ACIDO/BACKPHP_TEST/public/css/auth.css?v=<?php echo time(); ?>">
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
                    <p>Crea tu cuenta para comenzar a gestionar tus proyectos y acceder a todas las funciones de la
                        plataforma.</p>
                </div>
            </div>

            <!-- Panel Derecho -->
            <div class="form-panel">
                <img src="/ACIDO/BACKPHP_TEST/public/img/iconooo.png" alt="icon" class="logoaci">
                <h3>Crear Cuenta</h3>
                <p class="subtitle">Ingresa tus datos para registrarte</p>

                <!-- Contenedor dinámico de alertas -->
                <div id="alertMsg" class="alert-msg" style="display: none;"></div>

                <form id="registerForm" method="POST">
                    <input type="hidden" name="action" value="register">

                    <div class="input-group">
                        <input type="email" name="email" placeholder="Correo electrónico" required>
                    </div>

                    <div class="input-group input-group-pass">
                        <input type="password" name="password" id="passInput" placeholder="Contraseña" required>
                        <span class="show-btn" onclick="togglePass()">VER</span>
                    </div>

                    <!-- Texto descriptivo controlado por la clase CSS externa -->
                    <p id="passHelpText" class="pass-requirements-text">
                        8 a 20 caracteres, mayúscula, minúscula, número y símbolo (@$!%*?&._-#)
                    </p>

                    <button type="submit" class="btn-primary">Registrarse</button>
                </form>

                <div class="divider">
                    <span>O</span>
                </div>

                <p class="signup-text">
                    ¿Ya tienes una cuenta? <a href="/ACIDO/BACKPHP_TEST/index.php?action=login">Inicia Sesión</a>
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

        // --- VALIDACIÓN EN TIEMPO REAL (FUERA DEL SUBMIT) ---
        const passInput = document.getElementById('passInput');
        const passHelpText = document.getElementById('passHelpText');
        const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&._\-#])[A-Za-z\d@$!%*?&._\-#]{8,20}$/;

        passInput.addEventListener('input', function () {
            const value = this.value;

            if (value.length === 0) {
                // Si borra todo, regresa al estado neutral (Gris)
                passHelpText.classList.remove('valid', 'invalid');
            } else if (passwordRegex.test(value)) {
                // Cumple los requisitos (Verde)
                passHelpText.classList.remove('invalid');
                passHelpText.classList.add('valid');
            } else {
                // No cumple aún (Rojo)
                passHelpText.classList.remove('valid');
                passHelpText.classList.add('invalid');
            }
        });

        // --- MANEJO DEL REGISTRO POR AJAX ---
        document.getElementById('registerForm').addEventListener('submit', async function (e) {
            e.preventDefault();

            const alertBox = document.getElementById('alertMsg');
            alertBox.style.display = 'none';
            alertBox.className = 'alert-msg';

            const password = passInput.value;

            if (!passwordRegex.test(password)) {
                alertBox.textContent = 'La contraseña no cumple con los requisitos indicados.';
                alertBox.classList.add('alert-error');
                alertBox.style.display = 'block';
                return;
            }

            const formData = new FormData(this);

            try {
                const response = await fetch('/ACIDO/BACKPHP_TEST/index.php', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    alertBox.textContent = data.message || '¡Registro exitoso! Redirigiendo...';
                    alertBox.classList.add('alert-success');
                    alertBox.style.display = 'block';

                    this.reset();
                    passHelpText.classList.remove('valid', 'invalid'); // Resetea el color del texto

                    setTimeout(() => {
                        window.location.href = data.redirect || '/ACIDO/BACKPHP_TEST/index.php?action=login';
                    }, 2000);
                } else {
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
