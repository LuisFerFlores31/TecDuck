
<?php
include '../check_session.php';


?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Matecduck - Profesor</title>
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

  
      <div class="estado-preguntas">
        <h3>Estado de preguntas</h3>
        <div class="preguntas-container">
          <div class="pregunta-card">
            <h4>"P1, I2 N2"</h4>
            <p>Estado: ACEPTADO</p>
            <p>Tipo: Opción múltiple</p>
          </div>
      
          <div class="pregunta-card">
            <h4>"P2 I1 N1"</h4>
            <p>Estado: En revisión</p>
            <p>Tipo: Opción múltiple</p>
          </div>
      
          <div class="pregunta-card">
            <h4>"P3 I3 N5"</h4>
            <p>Estado: Aceptada</p>
            <p>Tipo: Abierta</p>
          </div>
        </div>
      </div>
      
    </section>
  </main>
</body>
</html>