<?php

include 'check_session.php';

?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Matecduck - Administrador</title>
  <link rel="stylesheet" href="admin_profesor.css" />
</head>
<body>

  
  <header>
    <nav>
      <div class="nav-left">
        <h1 class="logo">Matecduck</h1>
      </div>
      <div class="nav-right">
        <ul>
          <li><a href="#">Gestión de Preguntas</a></li>
          <li><a href="#">Vista de Profesor</a></li>
          <li><a href="#">Base de Datos</a></li>
          <li><a href="logout.php">Salir de sesión</a></li>
        </ul>
      </div>
    </nav>
  </header>

  <main>
    <section class="admin-section">

      
      <h2>Admin</h2>

      <div class="user-info">
        <p>Bienvenido: <?php echo $_SESSION["email"]; ?></p>
      </div>
      
      <div class="admin-actions">
        <h3>¿Qué quieres hacer?</h3>
        <div class="actions-buttons">
          
          <button>Gestionar</button>
        </div>
      </div>

      <br>

      </br>

      
      <div class="register-profesor">
        <h3>Registrar Profesor</h3>
        <form action="#" method="POST">
          <label for="firstname">Primer Nombre: </label>
          <input type="text" id="firstname" name="firstname" required />

          <label for="lastname">Apellidos: </label>
          <input type="text" id="lastname" name="lastname" required />
          
          <br>
          <label for="email">Email: </label>
          <input type="email" id="email" name="email" required />
          </br>

          <label for="password">Contraseña: </label>
          <input type="password" id="password" name="password" required />
          
          <br>
          <button type="submit">Enviar: </button>
          </br>
        </form>
      </div>
    </section>
  </main>
</body>
</html>

