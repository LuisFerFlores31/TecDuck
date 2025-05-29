<?php
//include __DIR__ . '/../check_session.php';
require __DIR__ . '/../database.php';
$pdo = Database::connect();

$sql = "
  SELECT p.id, p.enunciado, p.isla, p.nivel, p.tipo, p.estado,
         u.name, u.last_name,
         r.enunciado AS respuesta
    FROM Preguntas p
    JOIN Usuarios u ON p.usuario = u.email
    LEFT JOIN Respuestas r
      ON r.pregunta_id = p.id AND r.numero_respuesta = 1
 ORDER BY p.fecha_creacion DESC
";
$preguntas = $pdo->query($sql)->fetchAll();
Database::disconnect();
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
    <section class="gestion-preguntas-section">

      <!-- FILTROS -->
      <form class="filter-section" method="get">
        <label for="filter-isla">Isla:</label>
        <select id="filter-isla" name="filter_isla">
          <option value="">Todas</option>
          <option>1</option>
          <option>2</option>
          <option>3</option>
        </select>

        <label for="filter-nivel">Nivel:</label>
        <select id="filter-nivel" name="filter_nivel">
          <option value="">Todos</option>
          <option>1</option>
          <option>2</option>
          <option>3</option>
          <option>4</option>
          <option>5</option>
        </select>

        <button id="filter-btn" type="button">Filtrar</button>
      </form>

      <!-- TABLA -->
      <div style="overflow-x:auto;">
        <table class="preguntas-tabla">
          <thead>
            <tr>
              <th>Enunciado</th>
              <th>Respuesta</th>
              <th>Isla</th>
              <th>Nivel</th>
              <th>Tipo</th>
              <th>Estado</th>
              <th>Autor</th>
              <th>Acción</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($preguntas as $p): ?>
            <tr>
              <td><?= htmlspecialchars($p['enunciado']) ?></td>
              <td><?= htmlspecialchars($p['respuesta']) ?></td>
              <td><?= htmlspecialchars($p['isla']) ?></td>
              <td><?= htmlspecialchars($p['nivel']) ?></td>
              <td><?= htmlspecialchars($p['tipo']) ?></td>
              <td><?= $p['estado']==1?'Activa':'Inactiva' ?></td>
              <td><?= htmlspecialchars($p['name'].' '.$p['last_name']) ?></td>
              <td>
                <a href="edit_question.php?id=<?= $p['id'] ?>"
                   class="tabla-btn">Editar</a>
                <a href="delete_question.php?id=<?= $p['id'] ?>"
                   class="tabla-btn delete-btn"
                   onclick="return confirm('¿Eliminar esta pregunta?');">
                  Eliminar
                </a>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

    </section>
  </main>

  <script>
    document.getElementById("filter-btn").addEventListener("click",function(){
      const isla  = document.getElementById("filter-isla").value.trim();
      const nivel = document.getElementById("filter-nivel").value.trim();
      document.querySelectorAll(".preguntas-tabla tbody tr")
        .forEach(r => {
          const cIsla  = r.cells[2].textContent.trim();
          const cNivel = r.cells[3].textContent.trim();
          r.style.display = (
            (!isla  || cIsla  === isla) &&
            (!nivel || cNivel === nivel)
          ) ? "" : "none";
        });
    });
  </script>
</body>
</html>