<?php
include __DIR__ . '/../check_session.php';
require __DIR__ . '/../database.php';
$pdo = Database::connect();

$id = intval($_GET['id'] ?? 0);
if ($id < 1) header('Location: gestion_preguntas.php') && exit;
$stmt = $pdo->prepare("SELECT * FROM Preguntas WHERE id = ?");
$stmt->execute([$id]);
$preg = $stmt->fetch();
if (!$preg) header('Location: gestion_preguntas.php') && exit;

$stmt2 = $pdo->prepare("
  SELECT enunciado FROM Respuestas
   WHERE pregunta_id=? AND numero_respuesta=1
");
$stmt2->execute([$id]);
$resp = $stmt2->fetch();

$errors = [];
$old = [
  'enunciado' => $_POST['enunciado'] ?? $preg['enunciado'],
  'respuesta' => $_POST['respuesta'] ?? $resp['enunciado'],
  'isla'      => $_POST['isla']      ?? $preg['isla'],
  'nivel'     => $_POST['nivel']     ?? $preg['nivel'],
  'tipo'      => $_POST['tipo']      ?? $preg['tipo'],
  'estado'    => $_POST['estado']    ?? $preg['estado'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($old as $k=>&$v) $v = trim($v);
    $old['nivel']  = intval($old['nivel']);
    $old['estado'] = intval($old['estado']);

    if ($old['enunciado'] === '') $errors[] = 'Enunciado obligatorio.';
    if ($old['respuesta']  === '') $errors[] = 'Respuesta obligatoria.';
    if ($old['isla']       === '') $errors[] = 'Isla obligatoria.';
    if ($old['nivel'] < 1)         $errors[] = 'Nivel ≥ 1.';
    if (!in_array($old['tipo'], ['Multiple','TrueorFalse'], true))
                                    $errors[] = 'Tipo inválido.';
    if (!in_array($old['estado'], [0,1], true))
                                    $errors[] = 'Estado inválido.';

    if (empty($errors)) {
        $u1 = $pdo->prepare("
          UPDATE Preguntas SET
            enunciado = :enunciado,
            isla      = :isla,
            nivel     = :nivel,
            tipo      = :tipo,
            estado    = :estado
          WHERE id = :id
        ");
        $u1->execute([
          ':enunciado' => $old['enunciado'],
          ':isla'      => $old['isla'],
          ':nivel'     => $old['nivel'],
          ':tipo'      => $old['tipo'],
          ':estado'    => $old['estado'],
          ':id'        => $id,
        ]);

        
        $u2 = $pdo->prepare("
          UPDATE Respuestas
          SET enunciado = :resp
          WHERE pregunta_id = :id
            AND numero_respuesta = 1
        ");
        $u2->execute([
          ':resp' => $old['respuesta'],
          ':id'   => $id,
        ]);

        $aud = $pdo->prepare("
          INSERT INTO Modificar
            (id_usuario, id_pregunta, fecha_modificacion)
          VALUES
            (:idu, :idp, NOW())
        ");
        $aud->execute([
          ':idu' => $_SESSION['id_usuario'],
          ':idp' => $id,
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
  <title>Editar Pregunta – Matecduck</title>
  <link rel="stylesheet" href="admin_profesor.css">
  <style>
    textarea::placeholder, input::placeholder {
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
          <li><a href="../logout.php">Salir</a></li>
        </ul>
      </div>
    </nav>
  </header>
  <main>
    <section class="register-profesor">
      <h3>Editar Pregunta</h3>
      <?php if ($errors): ?>
        <div class="errors"><ul>
          <?php foreach ($errors as $e): ?>
            <li><?= htmlspecialchars($e) ?></li>
          <?php endforeach; ?>
        </ul></div>
      <?php endif; ?>
      <form action="edit_question.php?id=<?= $id ?>" method="post">
        <label for="enunciado">Enunciado:</label>
        <textarea
          id="enunciado"
          name="enunciado"
          rows="3"
          placeholder="Modifica el enunciado"
          required
        ><?= htmlspecialchars($old['enunciado']) ?></textarea>

        <label for="respuesta">Respuesta:</label>
        <input
          type="text"
          id="respuesta"
          name="respuesta"
          placeholder="Nueva respuesta para el enunciado"
          required
          value="<?= htmlspecialchars($old['respuesta']) ?>"
        >

        <label for="isla">Isla:</label>
        <input
          type="text"
          id="isla"
          name="isla"
          placeholder="Número de isla"
          required
          value="<?= htmlspecialchars($old['isla']) ?>"
        >

        <label for="nivel">Nivel:</label>
        <input
          type="number"
          id="nivel"
          name="nivel"
          min="1"
          placeholder="Nivel"
          required
          value="<?= htmlspecialchars($old['nivel']) ?>"
        >

        <label for="tipo">Tipo:</label>
        <select
          id="tipo"
          name="tipo"
          required
        >
          <option
            value="Multiple"
            <?= ($old['tipo'] === 'Multiple') ? 'selected' : '' ?>
          >Multiple</option>
          <option
            value="TrueorFalse"
            <?= ($old['tipo'] === 'TrueorFalse') ? 'selected' : '' ?>
          >Verdadero o Falso</option>
        </select>

        <label for="estado">Estado:</label>
        <select
          id="estado"
          name="estado"
          required
        >
          <option
            value="1"
            <?= ($old['estado'] === 1) ? 'selected' : '' ?>
          >Activa</option>
          <option
            value="0"
            <?= ($old['estado'] === 0) ? 'selected' : '' ?>
          >Inactiva</option>
        </select>

        <button type="submit">Guardar Cambios</button>
      </form>
    </section>
  </main>
</body>
</html>
