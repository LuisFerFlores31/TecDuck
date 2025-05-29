<?php
include '../../../check_session.php';
include '../../../config.php';

$rol = $_SESSION['rol'];
if ($rol !== 1) {
    echo "No tienes permiso para acceder a esta página.";
    exit;
}


$sql = "SELECT * FROM Preguntas ORDER BY fecha_creacion DESC";
$stmt = $conn->prepare($sql);
$stmt->execute();
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
        <div class="row mb-3">
            <p>
                <a href="../../../Tipos de pregunta/SelectQ.php" class="btn btn-success">Crear Nueva Pregunta</a>
                <a href="../../admin.php" class="btn btn-secondary">Volver</a>
            </p>
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
                        <tr>
                            <td><?php echo htmlspecialchars($row['enunciado']); ?></td>
                            <td><?php echo htmlspecialchars($row['tipo']); ?></td>
                            <td><?php echo htmlspecialchars($row['isla']); ?></td>
                            <td><?php echo htmlspecialchars($row['nivel']); ?></td>
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
                                            echo "<span class='badge bg-secondary'style='background-color: #ff0000;'>Rechazada</span>";
                                    }
                                ?>
                            </td>
                            <td width="250">
                                <a class="btn btn-primary btn-sm" href="read.php?id=<?php echo $row['id']; ?>">Detalles</a>
                                <a class="btn btn-success btn-sm" href="update.php?id=<?php echo $row['id']; ?>">Actualizar</a>
                                <a class="btn btn-danger btn-sm" href="delete.php?id=<?php echo $row['id']; ?>">Eliminar</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    <?php $stmt->close(); $conn->close(); ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
