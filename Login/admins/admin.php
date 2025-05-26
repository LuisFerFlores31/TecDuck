<?php

include '../check_session.php';

$registro_exito = "";
$registro_error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    require '../config.php';

    $nombre = trim($_POST["firstname"]);
    $apellido = trim($_POST["lastname"]);
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);
    $rol = 0; // profesor

    // Cifrar contraseña
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // Verificar si el correo ya existe
    $stmt = $conn->prepare("SELECT id FROM Usuarios WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $registro_error = "El correo ya está registrado.";
    } else {
        // Insertar nuevo profesor
        $stmt = $conn->prepare("INSERT INTO Usuarios (name, last_name, password, email, rol) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssi", $nombre, $apellido, $password_hash, $email, $rol);
        
        if ($stmt->execute()) {
            $registro_exito = "Profesor registrado exitosamente.";
        } else {
            $registro_error = "Error al registrar profesor: " . $stmt->error;
        }
    }

    $stmt->close();
    $conn->close();
}

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
          <li><a href="../admins/CRUD_Q/FOR_ADMIN/manage_q.php">Gestión de Preguntas</a></li> 
          <li><a href="#">Pendientes por Revisar</li>
          <li><a href="../admins/Read_professors/read_prof.php">Profesores</a></li>
          <li><a href="../logout.php">Salir de sesión</a></li>
        </ul>
      </div>
    </nav>
  </header>

  <main>
    <section class="admin-section">

      
      <h2>Admin</h2>

      <div class="user-info">
        <p>Bienvenido: <?php echo htmlspecialchars($_SESSION["email"]); ?></p>
      </div>
      
      <div class="admin-actions">
        <h3>¿Qué quieres hacer?</h3>
        <div class="actions-buttons">
          
        <button onclick="window.location.href='../../Tipos de pregunta/SelectQ.html'">Crear pregunta</button>

        </div>
      </div>

      <br>

      </br>

      
      <div class="register-profesor">
        <h3>Registrar Profesor</h3>
        <?php if (!empty($registro_exito)): ?>
          <p style="color: green;"><?php echo $registro_exito; ?></p>
        <?php elseif (!empty($registro_error)): ?>
          <p style="color: red;"><?php echo $registro_error; ?></p>
        <?php endif; ?>

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

