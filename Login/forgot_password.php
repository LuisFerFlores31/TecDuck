<?php
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña</title>
    <link rel="stylesheet" href="login.css">
</head>
<body>
    <div class="login-container">
        <div class="login-form">
            <h2>Recuperar Contraseña</h2>
            <form action="forgot_password.php" method="POST">
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <a href="./reset_password.html">
                    <button type="button">Enviar enlace de recuperación</button>
                </a>
            </form>
            <div class="form-links">
                <a href="login.php">Volver al login</a>
            </div>
        </div>
    </div>
</body>
</html>
