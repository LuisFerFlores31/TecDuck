<!-- login.php -->
<?php session_start(); ?>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Matecduck</title>
    <link rel="stylesheet" href="login.css">
    <style>
        .error-message {
            color: red;
            text-align: center;
            margin-bottom: 10px;
        }
        .input-group input.error {
            border: 2px solid red;
        }
    </style>
</head>
<body>
    <?php
    if (isset($_GET['registration_success']) && $_GET['registration_success'] == 'true') {
        echo '<p style="color: green; text-align: center; font-weight: bold;">¡Usuario registrado exitosamente! Ahora puedes iniciar sesión.</p>';
    }
    ?>
    <div class="background"></div>
    <div class="header">
        <h1 class="text-outline">Matecduck</h1>
        <p class="text-outline">Aprende matemáticas de una forma entretenida</p>
    </div>

    <div class="login-container">
        <h2 class="login-title">Iniciar sesión</h2>

        <div id="errorDisplay" class="error-message"></div>

        <form id="loginForm">
            <div class="input-group">
                <label for="email">Correo:</label>
                <input type="email" id="email" name="email" placeholder="Ingresa tu correo" required>
            </div>
            <div class="input-group">
                <label for="password">Contraseña:</label>
                <input type="password" id="password" name="password" placeholder="Ingresa tu contraseña" required>
            </div>
            <button class="starter-btn" type="submit">Comenzar</button>
        </form>

        <p class="recover">¿Olvidaste tu contraseña?</p>
        <a class="recover" href="./forgot_password.php">Recuperar tu contraseña</a>

        <div class="register-section">
            <p>¿No tienes una cuenta?</p>
            <p class="register-title">Registrate</p>
        </div>

        <div class="social-login">
            <button class="email-btn" onClick="window.location.href='./register.html'">Ingresar con correo</button>
        </div>
    </div>

    <script>
        const form = document.getElementById('loginForm');
        const errorDisplay = document.getElementById('errorDisplay');

        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            const email = form.email.value;
            const password = form.password.value;

            const response = await fetch('login_process.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: new URLSearchParams({ email, password })
            });

            const result = await response.json();

            if (result.success) {
                window.location.href = result.redirect;
            } else {
                errorDisplay.textContent = result.message;
                form.email.classList.remove('error');
                form.password.classList.remove('error');

                if (result.type === 'email') {
                    form.email.classList.add('error');
                } else if (result.type === 'password') {
                    form.password.classList.add('error');
                }
            }
        });
    </script>
</body>
</html>
