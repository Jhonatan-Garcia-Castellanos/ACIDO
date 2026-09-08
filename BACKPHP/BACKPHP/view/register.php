<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Cuenta - Ácido Colombia</title>

    <!-- Importación directa de la fuente Cinzel -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;700;800&display=swap" rel="stylesheet">

    <!-- Íconos de Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Hoja de estilos principal -->
    <link rel="stylesheet" href="/TAREA/BACKPHP/public/styles.css">
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
                    <h1>ÚNETE A </h1>
                    <h2>ACIDO COLOMBIA</h2>
                    <p>Crea tu cuenta para comenzar a gestionar tus proyectos y acceder a todas las funciones de la plataforma.</p>
                </div>
            </div>

            <!-- Panel Derecho: Formulario de Registro -->
            <div class="form-panel">
                <img src="/TAREA/BACKPHP/public/iconooo.png" alt="icon" class="logoaci">
                <h3>Crear Cuenta</h3>
                <p class="subtitle">Ingresa tus datos para registrarte</p>

                <?php if (isset($error)): ?>
                    <div class="error-msg"><?php echo $error; ?></div>
                <?php endif; ?>

                <form action="/TAREA/BACKPHP/index.php" method="POST">
                    <input type="hidden" name="action" value="register">

                    <div class="input-group">
                        <input type="email" name="email" placeholder="Correo electronico" required>
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
                    ¿Ya tienes una cuenta? <a href="/TAREA/BACKPHP/index.php?action=login">Inicia Sesión</a>
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
    </script>
</body>
</html>
