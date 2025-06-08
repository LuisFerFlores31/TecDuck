<?php
include '../../check_session.php';
include '../../config.php';

$rol = $_SESSION['rol'];
if ($rol !== 1) {
    echo "No tienes permiso para acceder a esta página.";
    exit;
}

// Obtener preguntas pendientes (estado 0) y modificaciones pendientes (estado 3)
$sql = "SELECT p.*, CONCAT(u.name, ' ', u.last_name) as profesor_nombre 
        FROM Preguntas p 
        LEFT JOIN Usuarios u ON p.usuario = u.email 
        WHERE p.estado IN (0, 3) 
        ORDER BY p.fecha_creacion DESC";
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $question_id = $_POST['question_id'];
    $action = $_POST['action'];
    $comentarios = $_POST['comentarios'] ?? '';
    
    try {
        if ($action === 'approve') {
            // Aprobar pregunta 
            $id_admin_validador = $_SESSION['user_id']; 
            $sql_update = "UPDATE Preguntas SET estado = 1, id_validador = ? WHERE id = ?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("ii", $id_admin_validador, $question_id);
            $stmt_update->execute();
            $message = "Pregunta aprobada exitosamente.";
        } elseif ($action === 'reject') {
            // Rechazar pregunta
            $sql_update = "UPDATE Preguntas SET estado = 2 WHERE id = ?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("i", $question_id);
            $stmt_update->execute();
            $message = "Pregunta rechazada.";
        }
        
        // Recfresh 
        header("Location: " . $_SERVER['PHP_SELF'] . "?msg=" . urlencode($message));
        exit;
    } catch (Exception $e) {
        $error = "Error al procesar la acción: " . $e->getMessage();
    }
}

$message = $_GET['msg'] ?? '';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Preguntas Pendientes</title>
    <link href="../CRUD_Q/CSS/bootstrap.min.css" rel="stylesheet">
    <script src="../CRUD_Q/JS/bootstrap.min.js"></script>
    <style>
        .question-card {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .question-header {
            background-color: #f8f9fa;
            padding: 15px 30px;
            border-bottom: 1px solid #dee2e6;
        }
        .question-body {
            padding: 15px 30px;
        }
        .badge-pending {
            background-color: #ffc107;
            color: #000;
        }
        .badge-modification {
            background-color: #17a2b8;
            color: #fff;
        }
        .imagen-pregunta-pending {
            max-width: 200px;
            max-height: 150px;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-top: 10px;
        }
        .contenido-enunciado-pending {
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #dee2e6;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <h3>Preguntas Pendientes de Aprobación</h3>
        </div>
        
        <?php if ($message): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?php echo htmlspecialchars($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="row mb-3">
            <p>
                <a href="../admin.php" class="btn btn-secondary">Volver al Panel de Admin</a>
            </p>
        </div>
        
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="question-card">
                    <div class="question-header">
                        <div class="row">
                            <div class="col-md-8">
                                <h5 class="mb-1">
                                    <?php 
                                    // Mostrar título basado en el contenido disponible
                                    if (!empty($row['enunciado'])) {
                                        echo htmlspecialchars(substr($row['enunciado'], 0, 100)) . (strlen($row['enunciado']) > 100 ? '...' : '');
                                    } elseif (!empty($row['imagen'])) {
                                        echo 'Pregunta con imagen';
                                    } else {
                                        echo 'Pregunta sin contenido';
                                    }
                                    ?>
                                </h5>
                                <small class="text-muted">
                                    Creada por: <strong><?php echo htmlspecialchars($row['profesor_nombre'] ?? 'Usuario desconocido'); ?></strong> | 
                                    Fecha: <?php echo $row['fecha_creacion']; ?>
                                </small>
                            </div>
                            <div class="col-md-4 text-end">
                                <?php if ($row['estado'] == 0): ?>
                                    <span class="badge badge-pending">Nueva Pregunta</span>
                                <?php else: ?>
                                    <span class="badge badge-modification">Modificación</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="question-body">
                        <div class="row">
                            <div class="col-md-8">
                                <!-- Mostrar contenido del enunciado -->
                                <div class="contenido-enunciado-pending">
                                    <strong>Enunciado:</strong><br>
                                    <?php 
                                    // Mostrar texto si existe
                                    if (!empty($row['enunciado'])): ?>
                                        <div class="mb-2">
                                            <?php echo nl2br(htmlspecialchars($row['enunciado'])); ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php 
                                    // Mostrar imagen si existe
                                    if (!empty($row['imagen'])): ?>
                                        <div class="mb-2">
                                            <img src="data:image/jpeg;base64,<?php echo base64_encode($row['imagen']); ?>" 
                                                 class="imagen-pregunta-pending" 
                                                 alt="Imagen de la pregunta">
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php 
                                    // Si no hay ni texto ni imagen
                                    if (empty($row['enunciado']) && empty($row['imagen'])): ?>
                                        <em>No hay contenido disponible</em>
                                    <?php endif; ?>
                                </div>
                                
                                <p><strong>Tipo:</strong> <?php echo htmlspecialchars($row['tipo'] ?? ''); ?></p>
                                <p><strong>Isla:</strong> <?php echo htmlspecialchars($row['isla'] ?? ''); ?></p>
                                <p><strong>Nivel:</strong> <?php echo htmlspecialchars($row['nivel'] ?? ''); ?></p>
                                
                                <div class="mt-3">
                                    <strong>Respuestas disponibles:</strong>
                                    <?php
                                    $sql_respuestas = "SELECT * FROM Respuestas WHERE pregunta_id = ? ORDER BY numero_respuesta";
                                    $stmt_resp = $conn->prepare($sql_respuestas);
                                    $stmt_resp->bind_param("i", $row['id']);
                                    $stmt_resp->execute();
                                    $respuestas = $stmt_resp->get_result();
                                    
                                    if ($respuestas->num_rows > 0): ?>
                                        <ul>
                                        <?php while ($resp = $respuestas->fetch_assoc()): ?>
                                            <li>
                                                <strong><?php echo $resp['numero_respuesta']; ?>:</strong> 
                                                <?php echo htmlspecialchars($resp['enunciado'] ?? ''); ?>
                                                <?php if ($resp['esCorrecta']): ?>
                                                    <span class="badge bg-success">Correcta</span>
                                                <?php endif; ?>
                                            </li>
                                        <?php endwhile; ?>
                                        </ul>
                                    <?php else: ?>
                                        <p class="text-muted">No hay respuestas registradas para esta pregunta.</p>
                                    <?php endif; 
                                    $stmt_resp->close();
                                    ?>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0">Acciones</h6>
                                    </div>
                                    <div class="card-body">
                                        <form method="POST" class="mb-2">
                                            <input type="hidden" name="question_id" value="<?php echo $row['id']; ?>">
                                            <div class="mb-2">
                                                <label for="comentarios_<?php echo $row['id']; ?>" class="form-label">Comentarios (opcional):</label>
                                                <textarea class="form-control" id="comentarios_<?php echo $row['id']; ?>" name="comentarios" rows="2" placeholder="Escribe comentarios para el profesor..."></textarea>
                                            </div>
                                            <div class="d-grid gap-2">
                                                <button type="submit" name="action" value="approve" class="btn btn-success btn-sm" onclick="return confirm('¿Aprobar esta pregunta?')">
                                                    ✓ Aprobar
                                                </button>
                                                <button type="submit" name="action" value="reject" class="btn btn-danger btn-sm" onclick="return confirm('¿Rechazar esta pregunta?')">
                                                    ✗ Rechazar
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="alert alert-info">
                <h4>No hay preguntas pendientes</h4>
                <p>Todas las preguntas han sido revisadas.</p>
            </div>
        <?php endif; ?>
        
        <?php $stmt->close(); $conn->close(); ?>
    </div>
</body>
</html>