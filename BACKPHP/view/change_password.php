<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cambiar Contraseña - ACIDO</title>
    <link rel="stylesheet" href="../public/styles.css">
</head>

<body>
    <div class="container">
        <!-- Panel Izquierdo -->
        <div class="left-panel">
            <div class="shapes">
                <div class="circle circle-1"></div>
                <div class="circle circle-2"></div>
                <div class="circle circle-3"></div>
            </div>
            <div class="welcome-text">
                <h1>SEGURIDAD</h1>
                <p>ACTUALIZA TU CONTRASEÑA PARA MANTENER TU CUENTA PROTEGIDA EN LA PLATAFORMA.</p>
            </div>
        </div>

        <!-- Panel Derecho / Formulario -->
        <div class="right-panel">
            <div class="form-box">
                <img src="../public/acidoo.png" alt="ACIDO Colombia" class="logo">
                <h2>CAMBIAR CONTRASEÑA</h2>
                <p class="subtitle">INGRESA TUS CREDENCIALES Y LA NUEVA CLAVE</p>

                <?php if (isset($_GET['error'])): ?>
                    <div class="alert alert-error">
                        <?= htmlspecialchars($_GET['error']) ?>
                    </div>
                <?php endif; ?>

                <form action="/ACIDO/BACKPHP/controller/change_password.php" method="POST">
                    <div class="input-group">
                        <input type="email" name="email" placeholder="Correo electrónico" required>
                    </div>

                    <div class="input-group">
                        <input type="password" name="actual_password" placeholder="Contraseña actual" required>
                    </div>

                    <div class="input-group">
                        <input type="password" name="nueva_password" placeholder="Nueva contraseña" required>
                    </div>

                    <div class="input-group">
                        <input type="password" name="confirmar_password" placeholder="Confirmar nueva contraseña"
                            required>
                    </div>

                    <button type="submit" class="btn-primary">GUARDAR CONTRASEÑA</button>
                </form>

                <div class="divider">O</div>

                <div class="footer-links">
                    <a href="/ACIDO/BACKPHP/index.php?action=login">¿RECORDAS TE TU CLAVE? INICIA SESIÓN</a>
                </div>
            </div>
        </div>
    </div>
</body>

</html>