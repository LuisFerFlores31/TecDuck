<?php
require '../../../config.php'; 

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: ../FOR_ADMIN/manage_q.php'); 
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $enunciado = $_POST['enunciado'] ?? '';
    $isla = $_POST['isla'];
    $nivel = $_POST['nivel'];
    $respuestas = $_POST['respuestas'] ?? [];
    $correcta = $_POST['correcta'] ?? -1;

    // Debug - agregar esto temporalmente para ver qué se está enviando
    error_log("POST data: " . print_r($_POST, true));
    error_log("ID: " . $id);
    error_log("Isla: " . $isla);
    error_log("Nivel: " . $nivel);
    error_log("Correcta: " . $correcta);
    error_log("Respuestas: " . print_r($respuestas, true));

    // Validación 
    if ($isla !== '' && $nivel !== '' && is_numeric($isla) && is_numeric($nivel)) {
        // Actualizar pregunta
        $stmt = $conn->prepare("UPDATE Preguntas SET enunciado=?, isla=?, nivel=? WHERE id=?");
        $stmt->bind_param("siii", $enunciado, $isla, $nivel, $id);
        
        if ($stmt->execute()) {
            error_log("Pregunta actualizada correctamente");
            
            // Eliminar respuestas existentes usando prepared statement
            $deleteStmt = $conn->prepare("DELETE FROM Respuestas WHERE pregunta_id = ?");
            $deleteStmt->bind_param("i", $id);
            $deleteStmt->execute();
            error_log("Respuestas eliminadas");

            // Insertar nuevas respuestas solo si hay respuestas
            if (!empty($respuestas)) {
                $insertStmt = $conn->prepare("INSERT INTO Respuestas (enunciado, esCorrecta, pregunta_id, numero_respuesta) VALUES (?, ?, ?, ?)");
                
                foreach ($respuestas as $i => $respuesta) {
                    $respuesta = trim($respuesta);
                    if ($respuesta !== '') {
                        $esCorrecta = ($i == $correcta) ? 1 : 0;
                        $insertStmt->bind_param("siii", $respuesta, $esCorrecta, $id, $i);
                        $insertStmt->execute();
                        error_log("Respuesta $i insertada: $respuesta (correcta: $esCorrecta)");
                    }
                }
                $insertStmt->close();
            }
            
            $deleteStmt->close();
            $stmt->close();
            
            header("Location: ../FOR_ADMIN/manage_q.php");
            exit;
        } else {
            error_log("Error al actualizar pregunta: " . $stmt->error);
            $error = "Error al actualizar la pregunta: " . $stmt->error;
        }
    } else {
        error_log("Validación fallida - Isla: '$isla', Nivel: '$nivel'");
        $error = "Por favor, selecciona una isla y un nivel válidos.";
    }
}

// Obtener datos existentes
$stmt = $conn->prepare("SELECT * FROM Preguntas WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$pregunta = $result->fetch_assoc();

if (!$pregunta) {
    header('Location: ../FOR_ADMIN/manage_q.php');
    exit;
}

// Obtener respuestas
$resStmt = $conn->prepare("SELECT * FROM Respuestas WHERE pregunta_id = ? ORDER BY numero_respuesta");
$resStmt->bind_param("i", $id);
$resStmt->execute();
$res = $resStmt->get_result();
$respuestas = $res->fetch_all(MYSQLI_ASSOC);

$tieneImagen = !empty($pregunta['imagen']);
$tieneTexto = !empty($pregunta['enunciado']);

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Actualizar Pregunta</title>
    <link rel="stylesheet" href="../CSS/bootstrap.min.css">
    <style>
        .imagen-pregunta {
            max-width: 100%;
            max-height: 400px;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-top: 10px;
        }
        .contenido-visual {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            border: 1px solid #dee2e6;
            margin-bottom: 20px;
        }
        .alerta-imagen {
            background-color: #e3f2fd;
            border-left: 4px solid #2196f3;
            color: #1565c0;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .form-section {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            border: 1px solid #e9ecef;
            margin-bottom: 20px;
        }
        .section-title {
            color: #495057;
            font-weight: 600;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 2px solid #e9ecef;
        }
        .alert-error {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <h2 class="mb-4">Actualizar Pregunta</h2>
            
            <?php if (isset($error)): ?>
                <div class="alert-error">
                    <strong>Error:</strong> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <!-- Mostrar contenido visual de la pregunta -->
            <?php if ($tieneImagen || $tieneTexto): ?>
                <div class="contenido-visual">
                    <h5 class="section-title">Contenido Actual de la Pregunta</h5>
                    
                    <?php if ($tieneImagen): ?>
                        <div class="alerta-imagen mb-3">
                            <strong>📷 Pregunta con Imagen</strong> - La imagen no se puede modificar desde aquí
                        </div>
                        <div class="text-center">
                            <img src="data:image/jpeg;base64,<?php echo base64_encode($pregunta['imagen']); ?>" 
                                 class="imagen-pregunta" 
                                 alt="Imagen de la pregunta">
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($tieneTexto): ?>
                        <div class="mt-3">
                            <strong>Texto:</strong><br>
                            <p class="mt-2 mb-0"><?php echo nl2br(htmlspecialchars($pregunta['enunciado'] ?? '')); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <!-- Formulario de actualización -->
            <form method="post" class="needs-validation" novalidate>
                
                <!-- Solo mostrar editor de texto si NO hay imagen -->
                <?php if (!$tieneImagen): ?>
                    <div class="form-section">
                        <h5 class="section-title">Enunciado de la Pregunta</h5>
                        <div class="mb-3">
                            <label for="enunciado" class="form-label">Texto de la pregunta</label>
                            <textarea name="enunciado" id="enunciado" class="form-control" rows="4" 
                                      placeholder="Escriba el enunciado de la pregunta"><?php echo htmlspecialchars($pregunta['enunciado'] ?? ''); ?></textarea>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Campo oculto para mantener el enunciado existente -->
                    <input type="hidden" name="enunciado" value="<?php echo htmlspecialchars($pregunta['enunciado'] ?? ''); ?>">
                <?php endif; ?>

                <!-- Configuración de la pregunta -->
                <div class="form-section">
                    <h5 class="section-title">Configuración</h5>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="isla" class="form-label">Isla</label>
                            <select name="isla" id="isla" class="form-select" required>
                                <option value="">Seleccionar isla...</option>
                                <option value="1" <?php if ($pregunta['isla'] == 1) echo 'selected'; ?>>Isla 1</option>
                                <option value="2" <?php if ($pregunta['isla'] == 2) echo 'selected'; ?>>Isla 2</option>
                                <option value="3" <?php if ($pregunta['isla'] == 3) echo 'selected'; ?>>Isla 3</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="nivel" class="form-label">Nivel</label>
                            <select name="nivel" id="nivel" class="form-select" required>
                                <option value="">Seleccionar nivel...</option>
                                <option value="1" <?php if ($pregunta['nivel'] == 1) echo 'selected'; ?>>Nivel 1</option>
                                <option value="2" <?php if ($pregunta['nivel'] == 2) echo 'selected'; ?>>Nivel 2</option>
                                <option value="3" <?php if ($pregunta['nivel'] == 3) echo 'selected'; ?>>Nivel 3</option>
                                <option value="4" <?php if ($pregunta['nivel'] == 4) echo 'selected'; ?>>Nivel 4</option>
                                <option value="5" <?php if ($pregunta['nivel'] == 5) echo 'selected'; ?>>Nivel 5</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Respuestas (solo para Multiple Choice y True/False) -->
                <div id="respuestasSection" class="form-section" style="<?php echo ($pregunta['tipo'] === 'Open') ? 'display: none;' : ''; ?>">
                    <h5 class="section-title">Opciones de Respuesta</h5>
                    <p class="text-muted mb-3">Selecciona la respuesta correcta marcando el círculo correspondiente:</p>
                    
                    <div id="respuestasContainer">
                        <?php
                        $numRespuestas = ($pregunta['tipo'] === 'TrueorFalse') ? 2 : 4;
                        for ($i = 0; $i < $numRespuestas; $i++):
                            $resp = '';
                            $esCorrecta = false;
                            
                            // Buscar la respuesta correspondiente
                            foreach ($respuestas as $respuesta) {
                                if ($respuesta['numero_respuesta'] == $i) {
                                    $resp = $respuesta['enunciado'];
                                    $esCorrecta = $respuesta['esCorrecta'] == 1;
                                    break;
                                }
                            }
                            
                            // Para verdadero/falso, establecer valores predeterminados
                            if ($pregunta['tipo'] === 'TrueorFalse') {
                                if ($i == 0) $resp = 'Verdadero';
                                if ($i == 1)$resp = 'Falso';
                            }
                        ?>
                            <div class="respuesta-item mb-3" data-index="<?php echo $i; ?>">
                                <div class="row align-items-center">
                                    <div class="col-auto">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="correcta" 
                                                   value="<?php echo $i; ?>" id="correcta<?php echo $i; ?>"
                                                   <?php if ($esCorrecta) echo 'checked'; ?>>
                                            <label class="form-check-label fw-bold text-success" for="correcta<?php echo $i; ?>">
                                                Correcta
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="input-group">
                                            <span class="input-group-text bg-light">Opción <?php echo ($i + 1); ?></span>
                                            <input type="text" class="form-control" name="respuestas[]" 
                                                   value="<?php echo htmlspecialchars($resp ?? ''); ?>" 
                                                   placeholder="Escriba la opción <?php echo ($i + 1); ?>"
                                                   <?php if ($pregunta['tipo'] === 'TrueorFalse') echo 'readonly'; ?>>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endfor; ?>
                    </div>
                </div>

                <!-- Botones de acción -->
                <div class="d-flex gap-2 justify-content-end mt-4">
                    <a href="../FOR_ADMIN/manage_q.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Volver
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i> Actualizar Pregunta
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// El tipo ya no se puede cambiar, así que solo manejamos la visualización inicial
document.addEventListener('DOMContentLoaded', function() {
    const tipoActual = '<?php echo $pregunta['tipo']; ?>';
    const respuestasSection = document.getElementById('respuestasSection');
    
    if (tipoActual === 'Open') {
        respuestasSection.style.display = 'none';
    } else {
        respuestasSection.style.display = 'block';
    }
});

// Validación del formulario
(function() {
    'use strict';
    window.addEventListener('load', function() {
        var forms = document.getElementsByClassName('needs-validation');
        var validation = Array.prototype.filter.call(forms, function(form) {
            form.addEventListener('submit', function(event) {
                if (form.checkValidity() === false) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    }, false);
})();

// Debug: Mostrar datos del formulario antes de enviar
document.querySelector('form').addEventListener('submit', function(e) {
    console.log('Formulario enviado');
    const formData = new FormData(this);
    for (let [key, value] of formData.entries()) {
        console.log(key + ': ' + value);
    }
});
</script>

</body>
</html>