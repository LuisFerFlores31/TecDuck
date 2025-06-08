<?php
include '../../../check_session.php';
include '../../../config.php';

$rol = $_SESSION['rol'];
$user_id = $_SESSION['user_id'];

if ($rol !== 0) {
    echo "No tienes permiso para acceder a esta página.";
    exit;
}

// Solo mostrar preguntas creadas por el profesor actual
$sql = "SELECT * FROM Preguntas WHERE usuario = ? ORDER BY fecha_creacion DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $_SESSION['email']); 
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mis Preguntas</title>
    <link href="../CSS/bootstrap.min.css" rel="stylesheet">
    <script src="../JS/bootstrap.min.js"></script>
    <style>
        .imagen-pregunta-mini {
            max-width: 60px;
            max-height: 60px;
            border: 1px solid #ddd;
            border-radius: 3px;
            margin-top: 5px;
        }
        .contenido-enunciado-tabla {
            max-width: 300px;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <h3>Mis Preguntas</h3>
        </div>
        <div class="row mb-3">
            <p>
                <a href="../../../Tipos de pregunta/SelectQ.php" class="btn btn-success">Crear Nueva Pregunta</a>
                <a href="../../profesor.php" class="btn btn-secondary">Volver</a>
            </p>
            <?php if ($result->num_rows > 0): ?>
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
                            <td class="contenido-enunciado-tabla">
                                <?php 
                                // Mostrar texto si existe
                                if (!empty($row['enunciado'])): ?>
                                    <div class="mb-1">
                                        <?php echo htmlspecialchars(substr($row['enunciado'], 0, 100)) . (strlen($row['enunciado']) > 100 ? '...' : ''); ?>
                                    </div>
                                <?php endif; ?>
                                
                                <?php 
                                // Mostrar imagen si existe
                                if (!empty($row['imagen'])): ?>
                                    <div class="mb-1">
                                        <img src="data:image/jpeg;base64,<?php echo base64_encode($row['imagen']); ?>" 
                                             class="imagen-pregunta-mini" 
                                             alt="Imagen de la pregunta">
                                    </div>
                                <?php endif; ?>
                                
                                <?php 
                                // Si no hay ni texto ni imagen
                                if (empty($row['enunciado']) && empty($row['imagen'])): ?>
                                    <em>Sin contenido</em>
                                <?php endif; ?>
                            </td>
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
                                        case 2:
                                            echo "<span class='badge bg-danger' style='background-color: #ff0000;'>Rechazada</span>";
                                            break;
                                        case 3:
                                            echo "<span class='badge bg-info'>Modificación Pendiente</span>";
                                            break;
                                        default:
                                            echo "<span class='badge bg-secondary'>Desconocido</span>";
                                    }
                                ?>
                            </td>
                            <td width="250">
                                <a class="btn btn-primary btn-sm" href="p_read.php?id=<?php echo $row['id']; ?>">Detalles</a>
                                <?php if ($row['estado'] != 3): // No permitir editar si hay modificación pendiente ?>
                                    <a class="btn btn-success btn-sm" href="p_update.php?id=<?php echo $row['id']; ?>">Actualizar</a>
                                <?php endif; ?>
                                <a class="btn btn-danger btn-sm" href="p_delete.php?id=<?php echo $row['id']; ?>">Eliminar</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php else: ?>
                <div class="alert alert-info">
                    <h4>No has creado ninguna pregunta aún</h4>
                    <p>Haz clic en "Crear Nueva Pregunta" para empezar.</p>
                </div>
            <?php endif; ?>
            <?php $stmt->close(); $conn->close(); ?>
        </div>
    </div>
</body>
</html>