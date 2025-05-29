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

$sql = "SELECT * FROM Preguntas WHERE usuario = ? ORDER BY fecha_creacion DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $_SESSION['email']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Pregunta no encontrada o no tienes permiso para editarla.";
    exit;
}

$pregunta = $result->fetch_assoc();

// Verificar que no haya modificación pendiente
if ($pregunta['estado'] == 3) {
    echo "Esta pregunta tiene modificaciones pendientes de aprobación. No puedes editarla hasta que sean revisadas.";
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $enunciado = trim($_POST['enunciado']);
    $isla = $_POST['isla'];
    $nivel = $_POST['nivel'];
    
    if (empty($enunciado)) {
        $error = "El enunciado es obligatorio.";
    } else {
        try {
            if ($pregunta['tipo'] === 'multiple') {
                $opcion_a = trim($_POST['opcion_a']);
                $opcion_b = trim($_POST['opcion_b']);
                $opcion_c = trim($_POST['opcion_c']);
                $opcion_d = trim($_POST['opcion_d']);
                $respuesta_correcta = $_POST['respuesta_correcta'];
                
                if (empty($opcion_a) || empty($opcion_b) || empty($opcion_c) || empty($opcion_d)) {
                    $error = "Todas las opciones son obligatorias.";
                } else {
                    // Actualizar pregunta y cambiar estado a "Modificación Pendiente" (3)
                    $sql_update = "UPDATE Preguntas SET enunciado = ?, isla = ?, nivel = ?, opcion_a = ?, opcion_b = ?, opcion_c = ?, opcion_d = ?, respuesta_correcta = ?, estado = 3 WHERE id = ? AND usuario = ?";
                    $stmt_update = $conn->prepare($sql_update);
                    $stmt_update->bind_param("ssssssssii", $enunciado, $isla, $nivel, $opcion_a, $opcion_b, $opcion_c, $opcion_d, $respuesta_correcta, $id, $user_id);
                    $stmt_update->execute();
                    $success = "Pregunta actualizada. Está pendiente de aprobación por el administrador.";
                }
            } elseif ($pregunta['tipo'] === 'true_false') {
                $respuesta_correcta = $_POST['respuesta_correcta'];
                
                $sql_update = "UPDATE Preguntas SET enunciado = ?, isla = ?, nivel = ?, respuesta_correcta = ?, estado = 3 WHERE id = ? AND usuario = ?";
                $stmt_update = $conn->prepare($sql_update);
                $stmt_update->bind_param("ssssii", $enunciado, $isla, $nivel, $respuesta_correcta, $id, $user_id);
                $stmt_update->execute();
                $success = "Pregunta actualizada. Está pendiente de aprobación por el administrador.";
            } else { // open
                $respuesta_correcta = trim($_POST['respuesta_correcta']);
                
                if (empty($respuesta_correcta)) {
                    $error = "La respuesta correcta es obligatoria.";
                } else {
                    $sql_update = "UPDATE Preguntas SET enunciado = ?, isla = ?, nivel = ?, respuesta_correcta = ?, estado = 3 WHERE id = ? AND usuario = ?";
                    $stmt_update = $conn->prepare($sql_update);
                    $stmt_update->bind_param("ssssii", $enunciado, $isla, $nivel, $respuesta_correcta, $id, $user_id);
                    $stmt_update->execute();
                    $success = "Pregunta actualizada. Está pendiente de aprobación por el administrador.";
                }
            }
        } catch (Exception $e) {
            $error = "Error al actualizar la pregunta: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Actualizar Pregunta</title>
    <link href="../CSS/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <h3>Actualizar Pregunta</h3>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <div class="row">
            <div class="col-md-8">
                <form method="POST">
                    <div class="mb-3">
                        <label for="enunciado" class="form-label">Enunciado de la Pregunta</label>
                        <textarea class="form-control" id="enunciado" name="enunciado" rows="3" required><?php echo htmlspecialchars($pregunta['enunciado']); ?></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="isla" class="form-label">Isla</label>
                        <select class="form-select" id="isla" name="isla" required>
                            <option value="Suma" <?php echo ($pregunta['isla'] === 'Suma') ? 'selected' : ''; ?>>Suma</option>
                            <option value="Resta" <?php echo ($pregunta['isla'] === 'Resta') ? 'selected' : ''; ?>>Resta</option>
                            <option value="Multiplicacion" <?php echo ($pregunta['isla'] === 'Multiplicacion') ? 'selected' : ''; ?>>Multiplicación</option>
                            <option value="Division" <?php echo ($pregunta['isla'] === 'Division') ? 'selected' : ''; ?>>División</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="nivel" class="form-label">Nivel</label>
                        <select class="form-select" id="nivel" name="nivel" required>
                            <option value="1" <?php echo ($pregunta['nivel'] == 1) ? 'selected' : ''; ?>>Nivel 1</option>
                            <option value="2" <?php echo ($pregunta['nivel'] == 2) ? 'selected' : ''; ?>>Nivel 2</option>
                            <option value="3" <?php echo ($pregunta['nivel'] == 3) ? 'selected' : ''; ?>>Nivel 3</option>
                        </select>
                    </div>
                    
                    <?php if ($pregunta['tipo'] === 'multiple'): ?>
                        <div class="mb-3">
                            <label for="opcion_a" class="form-label">Opción A</label>
                            <input type="text" class="form-control" id="opcion_a" name="opcion_a" value="<?php echo htmlspecialchars($pregunta['opcion_a']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="opcion_b" class="form-label">Opción B</label>
                            <input type="text" class="form-control" id="opcion_b" name="opcion_b" value="<?php echo htmlspecialchars($pregunta['opcion_b']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="opcion_c" class="form-label">Opción C</label>
                            <input type="text" class="form-control" id="opcion_c" name="opcion_c" value="<?php echo htmlspecialchars($pregunta['opcion_c']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="opcion_d" class="form-label">Opción D</label>
                            <input type="text" class="form-control" id="opcion_d" name="opcion_d" value="<?php echo htmlspecialchars($pregunta['opcion_d']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="respuesta_correcta" class="form-label">Respuesta Correcta</label>
                            <select class="form-select" id="respuesta_correcta" name="respuesta_correcta" required>
                                <option value="A" <?php echo ($pregunta['respuesta_correcta'] === 'A') ? 'selected' : ''; ?>>A</option>
                                <option value="B" <?php echo ($pregunta['respuesta_correcta'] === 'B') ? 'selected' : ''; ?>>B</option>
                                <option value="C" <?php echo ($pregunta['respuesta_correcta'] === 'C') ? 'selected' : ''; ?>>C</option>
                                <option value="D" <?php echo ($pregunta['respuesta_correcta'] === 'D') ? 'selected' : ''; ?>>D</option>
                            </select>
                        </div>
                    <?php elseif ($pregunta['tipo'] === 'true_false'): ?>
                        <div class="mb-3">
                            <label for="respuesta_correcta" class="form-label">Respuesta Correcta</label>
                            <select class="form-select" id="respuesta_correcta" name="respuesta_correcta" required>
                                <option value="1" <?php echo ($pregunta['respuesta_correcta'] === '1') ? 'selected' : ''; ?>>Verdadero</option>
                                <option value="0" <?php echo ($pregunta['respuesta_correcta'] === '0') ? 'selected' : ''; ?>>Falso</option>
                            </select>
                        </div>
                    <?php else: ?>
                        <div class="mb-3">
                            <label for="respuesta_correcta" class="form-label">Respuesta Correcta</label>
                            <input type="text" class="form-control" id="respuesta_correcta" name="respuesta_correcta" value="<?php echo htmlspecialchars($pregunta['respuesta_correcta'] ?? ''); ?>" required>
                        </div>
                    <?php endif; ?>
                    
                    <button type="submit" class="btn btn-success">Actualizar Pregunta</button>
                    <a href="p_index.php" class="btn btn-secondary">Cancelar</a>
                </form>
            </div>
        </div>
    </div>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>