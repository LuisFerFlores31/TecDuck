<?php

include __DIR__ . '/../check_session.php';


require __DIR__ . '/../database.php';
$pdo = Database::connect();


$f_isla       = $_GET['filter_isla']       ?? '';
$f_nivel      = $_GET['filter_nivel']      ?? '';
$f_dificultad = $_GET['filter_dificultad'] ?? '';

$where  = [];
$params = [];

if ($f_isla !== '') {
    $where[]         = 'isla = :isla';
    $params[':isla'] = $f_isla;
}
if ($f_nivel !== '') {
    $where[]           = 'nivel = :nivel';
    $params[':nivel']  = $f_nivel;
}
if ($f_dificultad !== '') {
    $where[]                = 'dificultad = :dificultad';
    $params[':dificultad']  = $f_dificultad;
}


$sql = 'SELECT pregunta, respuesta, isla, nivel, dificultad, profesor
        FROM preguntas';
if (!empty($where)) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$preguntas = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Gestionar Preguntas – Matecduck</title>

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
        
        <li><a href="gestion_preguntas.php">Gestión de Preguntas</a></li>
        <li><a href="profesor.php">Vista de Profesor</a></li>
        <li><a href="admin.php">Vista de Administrador</a></li>
        <li><a href="../logout.php">Salir de la sesión</a></li>
      </ul>
    </div>
  </nav>
</header>

<main>
  <section class="gestion-preguntas-section">

    <form class="filter-section" method="get" action="gestion_preguntas.php">
      <label for="filter-isla">Isla:</label>
      <select id="filter-isla" name="filter_isla">
        <option value="">Todas</option>
        <option<?= $f_isla === 'Isla 1' ? ' selected' : '' ?>>Isla 1</option>
        <option<?= $f_isla === 'Isla 2' ? ' selected' : '' ?>>Isla 2</option>
        <option<?= $f_isla === 'Isla 3' ? ' selected' : '' ?>>Isla 3</option>
      </select>

      <label for="filter-nivel">Nivel:</label>
      <select id="filter-nivel" name="filter_nivel">
        <option value="">Todos</option>
        <?php for ($i = 1; $i <= 5; $i++): ?>
          <option<?= ((string)$i === $f_nivel) ? ' selected' : '' ?>><?= $i ?></option>
        <?php endfor; ?>
      </select>

      <label for="filter-dificultad">Dificultad:</label>
      <select id="filter-dificultad" name="filter_dificultad">
        <option value="">Todas</option>
        <?php foreach (['Fácil', 'Media', 'Difícil'] as $d): ?>
          <option<?= ($d === $f_dificultad) ? ' selected' : '' ?>><?= $d ?></option>
        <?php endforeach; ?>
      </select>

      <button id="filter-btn" type="submit">Filtrar</button>
    </form>

   
    <table class="preguntas-tabla">
      <thead>
        <tr>
          <th>Pregunta</th>
          <th>Respuesta</th>
          <th>Isla</th>
          <th>Nivel</th>
          <th>Dificultad</th>
          <th>Acción</th>
          <th>Profesor</th>
        </tr>
      </thead>
      <tbody>
      <?php if (empty($preguntas)): ?>
        <tr><td colspan="7">No hay preguntas que coincidan.</td></tr>
      <?php else: ?>
        <?php foreach ($preguntas as $row): ?>
        <tr>
          <td><?= htmlspecialchars($row['pregunta']) ?></td>
          <td><?= htmlspecialchars($row['respuesta']) ?></td>
          <td><?= htmlspecialchars($row['isla']) ?></td>
          <td><?= htmlspecialchars($row['nivel']) ?></td>
          <td><?= htmlspecialchars($row['dificultad']) ?></td>
          <td><button class="tabla-btn">Editar</button></td>
          <td><?= htmlspecialchars($row['profesor']) ?></td>
        </tr>
        <?php endforeach; ?>
      <?php endif; ?>
      </tbody>
    </table>

  </section>
</main>

<?php

Database::disconnect();
?>

</body>
</html>