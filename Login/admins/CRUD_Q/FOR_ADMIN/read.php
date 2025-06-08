<?php
require '../../../config.php';
require '../../../check_session.php';

if ($_SESSION['rol'] != 1) {
    echo "Acceso denegado";
    exit;
}

$id = isset($_GET['id']) ? $_GET['id'] : null;
if (!$id) {
    header("Location: manage_q.php");
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
        <div class="control-group mb-3">
            <label class="control-label"><strong>ID</strong></label>
            <div class="controls">
                <label class="checkbox"><?php echo $pregunta['id']; ?></label>
            </div>
        </div>
        
        <div class="control-group mb-3">
            <label class="control-label"><strong>Enunciado</strong></label>
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
        
        <div class="control-group mb-3">
            <label class="control-label"><strong>Tipo</strong></label>
            <div class="controls">
                <label class="checkbox"><?php echo htmlspecialchars($pregunta['tipo']); ?></label>
            </div>
        </div>
        
        <div class="control-group mb-3">
            <label class="control-label"><strong>Isla</strong></label>
            <div class="controls">
                <label class="checkbox"><?php echo htmlspecialchars($pregunta['isla']); ?></label>
            </div>
        </div>
        
        <div class="control-group mb-3">
            <label class="control-label"><strong>Nivel</strong></label>
            <div class="controls">
                <label class="checkbox"><?php echo htmlspecialchars($pregunta['nivel']); ?></label>
            </div>
        </div>
        
        <div class="control-group mb-3">
            <label class="control-label"><strong>Usuario</strong></label>
            <div class="controls">
                <label class="checkbox"><?php echo htmlspecialchars($pregunta['usuario']); ?></label>
            </div>
        </div>
        
        <div class="control-group mb-3">
            <label class="control-label"><strong>Fecha de Creación</strong></label>
            <div class="controls">
                <label class="checkbox"><?php echo htmlspecialchars($pregunta['fecha_creacion']); ?></label>
            </div>
        </div>
        
        <div class="control-group mb-3">
            <label class="control-label"><strong>Estado</strong></label>
            <div class="controls">
                <label class="checkbox">
                    <?php
                        switch ($pregunta['estado']) {
                            case 0:
                                echo '<span class="badge bg-warning">Pendiente</span>';
                                break;
                            case 1:
                                echo '<span class="badge bg-success">Aprobada</span>';
                                break;
                            case 2:
                                echo '<span class="badge bg-danger">Rechazada</span>';
                                break;
                            default:
                                echo '<span class="badge bg-secondary">Desconocido</span>';
                        }
                    ?>
                </label>
            </div>
        </div>
        
        <div class="form-actions mt-4">
            <a class="btn btn-secondary" href="../../CRUD_Q/FOR_ADMIN/manage_q.php">Regresar</a>
        </div>
    </div>
</div>
</body>
</html>