<?php
include __DIR__ . '/../check_session.php';
require __DIR__ . '/../database.php';
$pdo = Database::connect();

$errors = [];
$old = [
  'enunciado' => '',
  'respuesta'  => '',
  'isla'       => '',
  'nivel'      => 1,
  'tipo'       => 'Open',
  'estado'     => 1
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($old as $k => &$v) {
        $v = trim($_POST[$k] ?? $v);
    }
    $old['nivel']  = intval($old['nivel']);
    $old['estado'] = intval($old['estado']);

    if ($old['enunciado'] === '') $errors[] = 'El enunciado es obligatorio.';
    if ($old['respuesta']  === '') $errors[] = 'La respuesta es obligatoria.';
    if ($old['isla']       === '') $errors[] = 'La isla es obligatoria.';
    if ($old['nivel'] < 1)         $errors[] = 'El nivel debe ser ≥ 1.';
    if (!in_array($old['tipo'], ['Open','Multiple','TrueorFalse'], true))
                                   $errors[] = 'Tipo inválido.';
    if (!in_array($old['estado'], [0,1], true))
                                   $errors[] = 'Estado inválido.';

    if (empty($errors)) {
        $sql = "INSERT INTO Preguntas
                (enunciado,isla,nivel,usuario,estado,tipo)
                VALUES
                (:enunciado,:isla,:nivel,:usuario,:estado,:tipo)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
          ':enunciado'=> $old['enunciado'],
          ':isla'     => $old['isla'],
          ':nivel'    => $old['nivel'],
          ':usuario'  => $_SESSION['email'],
          ':estado'   => $old['estado'],
          ':tipo'     => $old['tipo'],
        ]);
        $pid = $pdo->lastInsertId();

        $sql2 = "INSERT INTO Respuestas
                 (enunciado,esCorrecta,pregunta_id,numero_respuesta)
                 VALUES
                 (:resp,1,:pid,1)";
        $stmt2 = $pdo->prepare($sql2);
        $stmt2->execute([
          ':resp'=> $old['respuesta'],
          ':pid' => $pid
        ]);

        Database::disconnect();
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
  <style>
    textarea::placeholder,input::placeholder {
      color: #999; font-style: italic;
    }
  </style>
</head>
<body>
  <header>
    <nav>
      <div class="nav-left"><h1 class="logo">Matecduck</h1></div>
      <div class="nav-right">
        <ul>
          <li><a href="gestion_preguntas.php">Gestión de Preguntas</a></li>
          <li><a href="profesor.php">Vista de Profesor</a></li>
          <li><a href="admin.php">Vista de Administrador</a></li>
          <li><a href="../logout.php">Salir de sesión</a></li>
        </ul>
      </div>
    </nav>
  </header>
  <main>
    <section class="register-profesor">
      <h3>Crear Pregunta</h3>
      <?php if ($errors): ?>
        <div class="errors"><ul>
          <?php foreach ($errors as $e): ?>
            <li><?= htmlspecialchars($e) ?></li>
          <?php endforeach; ?>
        </ul></div>
      <?php endif; ?>
      <form action="create_question.php" method="post">
        <label for="enunciado">Enunciado:</label>
        <textarea id="enunciado" name="enunciado" rows="3"
          placeholder="Escribe la pregunta aquí"
          required></textarea>

        <label for="respuesta">Respuesta:</label>
        <input type="text" id="respuesta" name="respuesta"
          placeholder="Escribe la respuesta para la pregunta"
          required value="">

        <label for="isla">Isla:</label>
        <input type="text" id="isla" name="isla"
          placeholder="Número de isla"
          required value="">

        <label for="nivel">Nivel:</label>
        <input type="number" id="nivel" name="nivel" min="1"
          placeholder="Nivel"
          required value="<?= htmlspecialchars($old['nivel']) ?>">

        <label for="tipo">Tipo:</label>
<select id="tipo" name="tipo" required>
  <option 
    value="Multiple"
    <?= (isset($old['tipo']) && $old['tipo'] === 'Multiple') ? 'selected' : '' ?>
  
    Multiple
  </option>
  <option 
    value="TrueorFalse"
    <?= (isset($old['tipo']) && $old['tipo'] === 'TrueorFalse') ? 'selected' : '' ?>
  
    Verdadero o Falso
  </option>
</select>


        <button type="submit">Crear Pregunta</button>
      </form>
    </section>
  </main>
</body>
</html>