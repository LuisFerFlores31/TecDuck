<?php
include '../../../check_session.php';
include '../../../config.php';

$rol = $_SESSION['rol'];
if ($rol !== 1) {
    echo "No tienes permiso para acceder a esta página.";
    exit;
}

// Filtros
$filtroIsla = isset($_GET['isla']) ? $_GET['isla'] : '';
$filtroNivel = isset($_GET['nivel']) ? $_GET['nivel'] : '';

$sql = "SELECT * FROM Preguntas WHERE 1";
$params = [];

if ($filtroIsla !== '') {
    $sql .= " AND isla = ?";
    $params[] = $filtroIsla;
}

if ($filtroNivel !== '') {
    $sql .= " AND nivel = ?";
    $params[] = $filtroNivel;
}

$sql .= " ORDER BY fecha_creacion DESC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Preguntas</title>
    <link href="../CSS/bootstrap.min.css" rel="stylesheet">
    <script src="../JS/bootstrap.min.js"></script>
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <h3>Gestión de Preguntas</h3>
        </div>

       
        <form class="mb-4" method="get">
            <div class="row g-2 align-items-end">

                <div class="col-auto">
                    <label for="isla" class="form-label mb-1">Isla</label>
                    <select name="isla" id="isla" class="form-select form-select-sm">
                        <option value="">Todas</option>
                        <option value="1" <?= $filtroIsla === '1' ? 'selected' : '' ?>>1</option>
                        <option value="2" <?= $filtroIsla === '2' ? 'selected' : '' ?>>2</option>
                        <option value="3" <?= $filtroIsla === '3' ? 'selected' : '' ?>>3</option>
                    </select>
                </div>

                <div class="col-auto">
                    <label for="nivel" class="form-label mb-1">Nivel</label>
                    <select name="nivel" id="nivel" class="form-select form-select-sm">
                        <option value="">Todos</option>
                        <option value="1" <?= $filtroNivel === '1' ? 'selected' : '' ?>>1</option>
                        <option value="2" <?= $filtroNivel === '2' ? 'selected' : '' ?>>2</option>
                        <option value="3" <?= $filtroNivel === '3' ? 'selected' : '' ?>>3</option>
                        <option value="4" <?= $filtroNivel === '4' ? 'selected' : '' ?>>4</option>
                        <option value="5" <?= $filtroNivel === '5' ? 'selected' : '' ?>>5</option>
                    </select>
                </div>

                <div class="col-auto">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-sm btn-primary">Filtrar</button>
                        <a href="manage_q.php" class="btn btn-sm btn-secondary">Limpiar</a>
                    </div>
                </div>

            </div>
        </form>




        <!-- Botones principales -->
        <div class="row mb-3">
            <p>
                <a href="../../../Tipos de pregunta/SelectQ.php" class="btn btn-success">Crear Nueva Pregunta</a>
                <a href="../../admin.php" class="btn btn-secondary">Volver</a>
            </p>
        </div>

        <!-- Tabla -->
        <div class="row">
            <div class="form-check form-check-inline mb-2">
                <input class="form-check-input" type="checkbox" id="ocultarPngs">
                <label class="form-check-label" for="ocultarPngs">Ocultar <code>.png</code> </label>
            </div>

            <table class="table table-striped table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>Enunciado</th>
                        <th>Tipo</th>
                        <th>Isla</th>
                        <th>Nivel</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr class="<?= str_ends_with(strtolower($row['enunciado']), '.png') ? 'fila-png' : '' ?>">
                            <td><?= htmlspecialchars($row['enunciado']) ?></td>
                            <td><?= htmlspecialchars($row['tipo']) ?></td>
                            <td><?= htmlspecialchars($row['isla']) ?></td>
                            <td><?= htmlspecialchars($row['nivel']) ?></td>
                            <td>
                                <?php
                                    switch ($row['estado']) {
                                        case 0:
                                            echo "<span class='badge bg-warning text-dark'>Pendiente</span>";
                                            break;
                                        case 1:
                                            echo "<span class='badge' style='background-color: #218838;'>Aprobada</span>";
                                            break;
                                        default:
                                            echo "<span class='badge bg-secondary' style='background-color: #ff0000;'>Rechazada</span>";
                                    }
                                ?>
                            </td>
                            <td width="250">
                                <a class="btn btn-primary btn-sm" href="read.php?id=<?= $row['id'] ?>">Detalles</a>
                                <a class="btn btn-success btn-sm" href="update.php?id=<?= $row['id'] ?>">Actualizar</a>
                                <a class="btn btn-danger btn-sm" href="delete.php?id=<?= $row['id'] ?>">Eliminar</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    <?php $stmt->close(); $conn->close(); ?>
                </tbody>
            </table>
        </div>
    </div>
    <script>
        document.getElementById('ocultarPngs').addEventListener('change', function() {
            const pngRows = document.querySelectorAll('.fila-png');
            pngRows.forEach(row => {
                row.style.display = this.checked ? 'none' : '';
            });
        });
    </script>

</body>
</html>
