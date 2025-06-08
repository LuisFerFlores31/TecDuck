<?php
include '../../../check_session.php';
include '../../../config.php';

$rol = $_SESSION['rol'];
$user_id = $_SESSION['user_id'];

if ($rol !== 0) {
    echo "No tienes permiso para acceder a esta página.";
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) {
    echo "ID de pregunta no válido.";
    exit;
}

$sql = "SELECT * FROM Preguntas WHERE id = ? AND usuario = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $id, $_SESSION['email']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Pregunta no encontrada o no tienes permiso para verla.";
    exit;
}

$pregunta = $result->fetch_assoc();
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Detalles de la Pregunta</title>
    <link href="../../../admins/CRUD_Q/CSS/bootstrap.min.css" rel="stylesheet">
    <script src="../../../admins/CRUD_Q/JS/bootstrap.min.js"></script>
    <style>
        .imagen-pregunta {
            max-width: 100%;
            max-height: 400px;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-top: 10px;
        }
        .contenido-enunciado {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            border: 1px solid #dee2e6;
        }
    </style>
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
                <div class="contenido-enunciado">
                    <?php 
                    // Mostrar texto si existe
                    if (!empty($pregunta['enunciado'])): ?>
                        <div class="mb-2">
                            <strong>Texto:</strong><br>
                            <?php echo nl2br(htmlspecialchars($pregunta['enunciado'])); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php 
                    // Mostrar imagen si existe
                    if (!empty($pregunta['imagen'])): ?>
                        <div class="mb-2">
                            <strong>Imagen:</strong><br>
                            <img src="data:image/jpeg;base64,<?php echo base64_encode($pregunta['imagen']); ?>" 
                                 class="imagen-pregunta" 
                                 alt="Imagen de la pregunta">
                        </div>
                    <?php endif; ?>
                    
                    <?php 
                    // Si no hay ni texto ni imagen
                    if (empty($pregunta['enunciado']) && empty($pregunta['imagen'])): ?>
                        <em>No hay contenido disponible</em>
                    <?php endif; ?>
                </div>
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
                            case 2:
                                echo "Rechazada";
                                break;
                            case 3:
                                echo "Modificación Pendiente";
                                break;
                            default:
                                echo "Desconocido";
                        }
                    ?>
                </label>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label">Fecha de creación</label>
            <div class="controls">
                <label class="checkbox"><?php echo $pregunta['fecha_creacion']; ?></label>
            </div>
        </div>

        <?php if (!empty($pregunta['respuesta_correcta'])): ?>
            <div class="control-group">
                <label class="control-label">Respuesta Correcta</label>
                <div class="controls">
                    <label class="checkbox"><?php echo htmlspecialchars($pregunta['respuesta_correcta']); ?></label>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($pregunta['comentarios_admin'])): ?>
            <div class="control-group">
                <label class="control-label">Comentarios del Administrador</label>
                <div class="controls">
                    <label class="checkbox"><?php echo htmlspecialchars($pregunta['comentarios_admin']); ?></label>
                </div>
            </div>
        <?php endif; ?>

        <div class="form-actions mt-3">
            <a href="p_index.php" class="btn btn-secondary">Volver a Mis Preguntas</a>
            <?php if ($pregunta['estado'] != 3): ?>
                <a href="p_update.php?id=<?php echo $pregunta['id']; ?>" class="btn btn-success">Editar</a>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>