<?php
include '../../../check_session.php';
include '../../../config.php';

$rol = $_SESSION['rol'];
$email = $_SESSION['email'];

if ($rol !== 0) {
    echo "No tienes permiso para acceder a esta página.";
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id || !is_numeric($id)) {
    echo "ID de pregunta no válido.";
    exit;
}

// Validar si el usuario actual es dueño de la pregunta
$sql = "SELECT * FROM Preguntas WHERE id = ? AND usuario = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $id, $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Pregunta no encontrada o no tienes permiso para eliminarla.";
    exit;
}

$pregunta = $result->fetch_assoc();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Primero eliminar respuestas asociadas
        $sql_respuestas = "DELETE FROM Respuestas WHERE pregunta_id = ?";
        $stmt_r = $conn->prepare($sql_respuestas);
        $stmt_r->bind_param("i", $id);
        $stmt_r->execute();
        $stmt_r->close();

        // Luego eliminar la pregunta
        $sql_delete = "DELETE FROM Preguntas WHERE id = ? AND usuario = ?";
        $stmt_delete = $conn->prepare($sql_delete);
        $stmt_delete->bind_param("is", $id, $email);
        
        if ($stmt_delete->execute()) {
            $success = "Pregunta eliminada exitosamente.";
        } else {
            $error = "Error al eliminar la pregunta.";
        }
        $stmt_delete->close();
    } catch (Exception $e) {
        $error = "Error al eliminar la pregunta: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Eliminar Pregunta</title>
    <link href="../CSS/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <div class="row">
        <h3>Eliminar Pregunta</h3>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success">
            <?php echo $success; ?>
            <br><br>
            <a href="p_index.php" class="btn btn-primary">Volver a Mis Preguntas</a>
        </div>
    <?php else: ?>
        <div class="row">
            <div class="col-md-8">
                <div class="alert alert-danger">
                    <h4>¿Estás seguro de que deseas eliminar esta pregunta?</h4>
                    <p><strong>Esta acción no se puede deshacer.</strong></p>
                </div>

                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Información de la Pregunta</h5>
                        <p><strong>Enunciado:</strong> <?php echo htmlspecialchars($pregunta['enunciado']); ?></p>
                        <p><strong>Tipo:</strong> <?php echo htmlspecialchars($pregunta['tipo']); ?></p>
                        <p><strong>Isla:</strong> <?php echo htmlspecialchars($pregunta['isla']); ?></p>
                        <p><strong>Nivel:</strong> <?php echo htmlspecialchars($pregunta['nivel']); ?></p>
                        <p><strong>Estado:</strong>
                            <?php
                            switch ($pregunta['estado']) {
                                case 0:
                                    echo "<span class='badge bg-warning text-dark'>Pendiente</span>";
                                    break;
                                case 1:
                                    echo "<span class='badge' style='background-color: #218838;'>Aprobada</span>";
                                    break;
                                case 2:
                                    echo "<span class='badge bg-danger'>Rechazada</span>";
                                    break;
                                case 3:
                                    echo "<span class='badge bg-info'>Modificación Pendiente</span>";
                                    break;
                                default:
                                    echo "<span class='badge bg-secondary'>Desconocido</span>";
                            }
                            ?>
                        </p>
                        <p><strong>Fecha de Creación:</strong> <?php echo $pregunta['fecha_creacion']; ?></p>
                    </div>
                </div>

                <div class="mt-3">
                    <form method="POST" style="display: inline;">
                        <button type="submit" class="btn btn-danger"
                                onclick="return confirm('¿Estás completamente seguro de eliminar esta pregunta?')">
                            Sí, Eliminar Pregunta
                        </button>
                    </form>
                    <a href="p_index.php" class="btn btn-secondary">Cancelar</a>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
<?php
$stmt->close();
$conn->close();
?>
