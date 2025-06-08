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
    header('Location: p_index.php'); 
    exit;
}

// Obtener la pregunta específica
$sql = "SELECT * FROM Preguntas WHERE id = ? AND usuario = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $id, $_SESSION['email']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: p_index.php');
    exit;
}

$pregunta = $result->fetch_assoc();

if ($pregunta['estado'] == 3) {
    echo "Esta pregunta tiene modificaciones pendientes de aprobación. No puedes editarla hasta que sean revisadas.";
    exit;
}

// Obtener respuestas
$resStmt = $conn->prepare("SELECT * FROM Respuestas WHERE pregunta_id = ? ORDER BY numero_respuesta");
$resStmt->bind_param("i", $id);
$resStmt->execute();
$res = $resStmt->get_result();
$respuestas = $res->fetch_all(MYSQLI_ASSOC);

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $enunciado = $_POST['enunciado'] ?? '';
    $isla = $_POST['isla'];
    $nivel = $_POST['nivel'];
    $respuestas_form = $_POST['respuestas'] ?? [];
    $correcta = $_POST['correcta'] ?? -1;

    // Validación 
    if ($isla !== '' && $nivel !== '' && is_numeric($isla) && is_numeric($nivel)) {
        try {
            $conn->begin_transaction();
            
            // Actualizar pregunta con estado pendiente
            $updateStmt = $conn->prepare("UPDATE Preguntas SET enunciado=?, isla=?, nivel=?, estado=3 WHERE id=? AND usuario=?");
            $updateStmt->bind_param("siiis", $enunciado, $isla, $nivel, $id, $_SESSION['email']);
            
            if ($updateStmt->execute()) {
                // Eliminar respuestas existentes
                $deleteStmt = $conn->prepare("DELETE FROM Respuestas WHERE pregunta_id = ?");
                $deleteStmt->bind_param("i", $id);
                $deleteStmt->execute();

                // Insertar nuevas respuestas solo si hay respuestas
                if (!empty($respuestas_form)) {
                    $insertStmt = $conn->prepare("INSERT INTO Respuestas (enunciado, esCorrecta, pregunta_id, numero_respuesta) VALUES (?, ?, ?, ?)");
                    
                    foreach ($respuestas_form as $i => $respuesta) {
                        $respuesta = trim($respuesta);
                        if ($respuesta !== '') {
                            $esCorrecta = ($i == $correcta) ? 1 : 0;
                            $insertStmt->bind_param("siii", $respuesta, $esCorrecta, $id, $i);
                            $insertStmt->execute();
                        }
                    }
                    $insertStmt->close();
                }
                
                $deleteStmt->close();
                $updateStmt->close();
                
                $conn->commit();
                $success = "Pregunta actualizada. Está pendiente de aprobación por el administrador.";
                
            } else {
                throw new Exception("Error al actualizar la pregunta: " . $stmt->error);
            }
        } catch (Exception $e) {
            $conn->rollback();
            $error = "Error al actualizar la pregunta: " . $e->getMessage();
        }
    } else {
        $error = "Por favor, selecciona una isla y un nivel válidos.";
    }
}

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
        .alerta-modificacion {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            color: #856404;
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
            
            <div class="alerta-modificacion">
                <strong>📝 Importante:</strong> Las modificaciones realizadas estarán pendientes de aprobación por el administrador antes de ser aplicadas.
            </div>
            
            <?php if (isset($error) && $error): ?>
                <div class="alert-error">
                    <strong>Error:</strong> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($success) && $success): ?>
                <div class="alert alert-success">
                    <strong>Éxito:</strong> <?php echo htmlspecialchars($success); ?>
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
                                      placeholder="Escriba el enunciado de la pregunta" required><?php echo htmlspecialchars($pregunta['enunciado'] ?? ''); ?></textarea>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="form-section">
                        <h5 class="section-title">Texto Adicional (Opcional)</h5>
                        <div class="mb-3">
                            <label for="enunciado" class="form-label">Texto complementario a la imagen</label>
                            <textarea name="enunciado" id="enunciado" class="form-control" rows="3" 
                                      placeholder="Texto adicional opcional para acompañar la imagen"><?php echo htmlspecialchars($pregunta['enunciado'] ?? ''); ?></textarea>
                            <small class="form-text text-muted">Este campo es opcional ya que la pregunta tiene imagen.</small>
                        </div>
                    </div>
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

                <!-- Campo para preguntas abiertas -->
                <?php if ($pregunta['tipo'] === 'Open'): ?>
                    <div class="form-section">
                        <h5 class="section-title">Respuesta Correcta</h5>
                        <div class="mb-3">
                            <label for="respuesta_abierta" class="form-label">Respuesta Correcta</label>
                            <input type="text" class="form-control" id="respuesta_abierta" name="respuesta_abierta" 
                                   value="<?php 
                                   foreach ($respuestas as $respuesta) {
                                       if ($respuesta['esCorrecta'] == 1) {
                                           echo htmlspecialchars($respuesta['enunciado']);
                                           break;
                                       }
                                   }
                                   ?>" 
                                   placeholder="Escriba la respuesta correcta" required>
                            <input type="hidden" name="respuestas[]" id="hidden_respuesta_abierta">
                            <input type="hidden" name="correcta" value="0">
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Botones de acción -->
                <div class="d-flex gap-2 justify-content-end mt-4">
                    <a href="p_index.php" class="btn btn-secondary">
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
        
        // Sincronizar el campo de respuesta abierta con el array de respuestas
        const respuestaAbierta = document.getElementById('respuesta_abierta');
        const hiddenRespuesta = document.getElementById('hidden_respuesta_abierta');
        
        if (respuestaAbierta && hiddenRespuesta) {
            // Sincronizar valor inicial
            hiddenRespuesta.value = respuestaAbierta.value;
            
            // Sincronizar en tiempo real
            respuestaAbierta.addEventListener('input', function() {
                hiddenRespuesta.value = this.value;
            });
        }
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

<?php
$stmt->close();
$resStmt->close();
$conn->close();
?>