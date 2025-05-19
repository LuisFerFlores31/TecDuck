<?php
include __DIR__ . '/../check_session.php';
require __DIR__ . '/../database.php';
$pdo = Database::connect();

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pregunta   = trim($_POST['pregunta']   ?? '');
    $respuesta  = trim($_POST['respuesta']  ?? '');
    $isla       = trim($_POST['isla']       ?? '');
    $nivel      = intval($_POST['nivel']    ?? 0);
    $dificultad = trim($_POST['dificultad'] ?? '');
    $profesor   = trim($_POST['profesor']   ?? '');

    if ($pregunta === '')   $errors[] = 'La pregunta es obligatoria.';
    if ($respuesta === '')  $errors[] = 'La respuesta es obligatoria.';
    if ($isla === '')       $errors[] = 'La isla es obligatoria.';
    if ($nivel < 1)         $errors[] = 'El nivel debe ser entero ≥ 1.';
    if (!in_array($dificultad, ['Fácil','Media','Difícil'])) 
                            $errors[] = 'Dificultad inválida.';
    if ($profesor === '')   $errors[] = 'El nombre del profesor es obligatorio.';

    if (empty($errors)) {
        $sql = "INSERT INTO preguntas
                (pregunta,respuesta,isla,nivel,dificultad,profesor)
                VALUES (:pregunta,:respuesta,:isla,:nivel,:dificultad,:profesor)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
          ':pregunta'   => $pregunta,
          ':respuesta'  => $respuesta,
          ':isla'       => $isla,
          ':nivel'      => $nivel,
          ':dificultad' => $dificultad,
          ':profesor'   => $profesor
        ]);
        header('Location: gestion_preguntas.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Crear Pregunta – Matecduck</title>
  <link rel="stylesheet" href="admin_profesor.css">
</head>
<body>

  <header>
    <nav>
      <div class="nav-left">
        <h1 class="logo">Matecduck</h1>
      </div>
      <div class="nav-right">
        <ul>
          <li><a href="gestion_preguntas.html">Gestión de Preguntas</a></li>
          <li><a href="profesor.html">Vista de Profesor</a></li>
          <li><a href="admin.html">Vista de Administrador</a></li>
          <li><a href="logout.php">Salir de sesión</a></li>
        </ul>
      </div>
    </nav>
  </header>

  <main>
    <section class="register-profesor">
      <h3>Crear Pregunta</h3>
      <form action="create_question.php" method="post">
        <label for="pregunta">Pregunta:</label>
        <textarea id="pregunta" name="pregunta" rows="3" required></textarea>

        <label for="respuesta">Respuesta:</label>
        <input type="text" id="respuesta" name="respuesta" required>

        <label for="isla">Isla:</label>
        <input type="text" id="isla" name="isla" required>

        <label for="nivel">Nivel:</label>
        <input type="number" id="nivel" name="nivel" min="1" required>

        <label for="profesor">Profesor:</label>
        <input type="text" id="profesor" name="profesor" required>

        <button type="submit">Crear Pregunta</button>
      </form>
    </section>
  </main>

</body>
</html>