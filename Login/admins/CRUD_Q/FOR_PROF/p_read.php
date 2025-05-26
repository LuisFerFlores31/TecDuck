<?php
require '../../../config.php';
require '../../../check_session.php';

if ($_SESSION['rol'] != 0) {
    echo "Acceso denegado";
    exit;
}

$id = isset($_GET['id']) ? $_GET['id'] : null;
if (!$id) {
    header("Location: p_index.php");
    exit;
}

$sql = "SELECT * FROM Preguntas WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$pregunta = $result->fetch_assoc();
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <link href="../../../admins/CRUD_Q/CSS/bootstrap.min.css" rel="stylesheet">
    <script src="../../../admins/CRUD_Q/JS/bootstrap.min.js"></script>
    <title>Detalles de Pregunta</title>
</head>
<body>
<div class="container mt-5">
    <h3>Detalles de la Pregunta</h3>
    <div class="form-horizontal">
        <div class="control-group">
            <label class="control-label">ID</label>
            <div class="controls">
                <label class="checkbox"><?php echo $pregunta['id']; ?></label>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label">Enunciado</label>
            <div class="controls">
                <label class="checkbox"><?php echo htmlspecialchars($pregunta['enunciado']); ?></label>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label">Tipo</label>
            <div class="controls">
                <label class="checkbox"><?php echo htmlspecialchars($pregunta['tipo']); ?></label>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label">Isla</label>
            <div class="controls">
                <label class="checkbox"><?php echo htmlspecialchars($pregunta['isla']); ?></label>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label">Nivel</label>
            <div class="controls">
                <label class="checkbox"><?php echo htmlspecialchars($pregunta['nivel']); ?></label>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label">Estado</label>
            <div class="controls">
                <label class="checkbox">
                    <?php
                        switch ($pregunta['estado']) {
                            case 0:
                                echo "Pendiente";
                                break;
                            case 1:
                                echo "Aprobada";
                                break;
                            default:
                                echo "Desconocido";
                        }
                    ?>
                </label>
            </div>
        </div>
        <div class="form-actions mt-3">
            <a class="btn btn-secondary" href="../../CRUD_Q/FOR_ADMIN/manage_q.php">Regresar</a>
        </div>
    </div>
</div>
</body>
</html>
