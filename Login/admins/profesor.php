
<?php
include '../check_session.php';


?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Matecduck - Profesor</title>
  <link rel="stylesheet" href="admin_profesor.css" />
  <style>
    .imagen-fondo-inferior {
      position: fixed;
      bottom: 100px;
      left: 55%;
      transform: translateX(-50%) rotate(90deg);
      pointer-events: none;
      z-index: 0;
    }

    .imagen-fondo-inferior img {
      width: 600px;
      max-width: 80%;
    }

  </style>
</head>
<body>
  
  <header>
    <nav>
      <div class="nav-left">
        <h1 class="logo">Matecduck</h1>
      </div>
      <div class="nav-right">
        <ul>
          <li><a href="../admins/CRUD_Q/FOR_PROF/p_index.php">Mis Preguntas</a></li>
          <li><a href="../logout.php">Salir de la sesión</a></li>
        </ul>
      </div>
    </nav>
  </header>

  
  <main>
    <section class="prof-section">

      
      <h2>Profesor</h2>

      <div class="user-info">
        <p>Bienvenido: <?php echo htmlspecialchars($_SESSION["email"]); ?></p>
      </div>

      <div class="prof-actions">
        <h3>¿Gustas agregar preguntas?</h3>
        <div class="actions-buttons">
          
          <button onclick="window.location.href='../../Tipos de pregunta/SelectQ.php'">Crear</button>
        </div>
      </div>

      <br>

      </br>
      <div class="imagen-fondo-inferior">
        <img src="../images/blackk.png" alt="imager" />
      </div>
    </section>
  </main>
</body>
</html>