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
<header><?php include 'partials/nav.php'; ?></header>
<main>
  <h2>Crear nueva pregunta</h2>
  <?php if($errors): ?>
    <div class="errors">
      <ul>
      <?php foreach($errors as $e): ?>
        <li><?=htmlspecialchars($e)?></li>
      <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>
  <form method="post">
    <label>Pregunta:</label>
    <textarea name="pregunta"><?=htmlspecialchars($_POST['pregunta'] ?? '')?></textarea>
    <label>Respuesta:</label>
    <input type="text" name="respuesta" value="<?=htmlspecialchars($_POST['respuesta'] ?? '')?>">

    <label>Isla:</label>
    <input type="text" name="isla" value="<?=htmlspecialchars($_POST['isla'] ?? '')?>">

    <label>Nivel:</label>
    <input type="number" name="nivel" min="1" value="<?=htmlspecialchars($_POST['nivel'] ?? 1)?>">

    <label>Dificultad:</label>
    <select name="dificultad">
      <?php foreach(['Fácil','Media','Difícil'] as $d): ?>
        <option value="<?=$d?>"<?=isset($_POST['dificultad']) && $_POST['dificultad']===$d?' selected':''?>>
          <?=$d?>
        </option>
      <?php endforeach; ?>
    </select>

    <label>Profesor:</label>
    <input type="text" name="profesor" value="<?=htmlspecialchars($_POST['profesor'] ?? '')?>">

    <button type="submit">Crear Pregunta</button>
  </form>
</main>
</body>
</html>
<?php Database::disconnect(); ?>