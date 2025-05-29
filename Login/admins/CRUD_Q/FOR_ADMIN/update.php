<?php
require '../../../config.php'; 

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: ../FOR_ADMIN/manage_q.php'); 
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $enunciado = $_POST['enunciado'];
    $isla = $_POST['isla'];
    $nivel = $_POST['nivel'];
    $estado = $_POST['estado'];
    $tipo = $_POST['tipo'];
    $respuestas = $_POST['respuestas'] ?? [];
    $correcta = $_POST['correcta'] ?? -1;

    // Validación 
    if ($enunciado && $isla !== '' && $nivel !== '' && $tipo && $estado !== '') {
        $stmt = $conn->prepare("UPDATE Preguntas SET enunciado=?, isla=?, nivel=?, estado=?, tipo=? WHERE id=?");
        $stmt->bind_param("siiisi", $enunciado, $isla, $nivel, $estado, $tipo, $id);
        $stmt->execute();

        // Eliminar respuestas existentes
        $conn->query("DELETE FROM Respuestas WHERE pregunta_id = $id");

        // Insertar nuevas respuestas
        foreach ($respuestas as $i => $respuesta) {
            $respuesta = trim($respuesta);
            if ($respuesta !== '') {
                $esCorrecta = ($i == $correcta) ? 1 : 0;
                $stmt = $conn->prepare("INSERT INTO Respuestas (enunciado, esCorrecta, pregunta_id, numero_respuesta) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("siii", $respuesta, $esCorrecta, $id, $i);
                $stmt->execute();
            }
        }

        header("Location: ../FOR_ADMIN/manage_q.php");
        exit;
    }
}

// Obtener datos existentes
$stmt = $conn->prepare("SELECT * FROM Preguntas WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$pregunta = $result->fetch_assoc();

// Obtener respuestas
$res = $conn->query("SELECT * FROM Respuestas WHERE pregunta_id = $id ORDER BY numero_respuesta");
$respuestas = $res->fetch_all(MYSQLI_ASSOC);

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Actualizar Pregunta</title>
    <link rel="stylesheet" href="../CSS/bootstrap.min.css">
</head>
<body>
<div class="container">
    <h2>Actualizar Pregunta</h2>
    <form method="post">
        <div class="form-group">
            <label>Enunciado</label>
            <textarea name="enunciado" class="form-control"><?php echo htmlspecialchars($pregunta['enunciado']); ?></textarea>
        </div>

        <div class="form-group">
            <label>Isla</label>
            <input type="number" name="isla" class="form-control" value="<?php echo $pregunta['isla']; ?>">
        </div>

        <div class="form-group">
            <label>Nivel</label>
            <input type="number" name="nivel" class="form-control" value="<?php echo $pregunta['nivel']; ?>">
        </div>

        <div class="form-group">
            <label>Estado</label>
            <select name="estado" class="form-control">
                <option value="1" <?php if ($pregunta['estado'] == 1) echo 'selected'; ?>>Activa</option>
                <option value="0" <?php if ($pregunta['estado'] == 0) echo 'selected'; ?>>Inactiva</option>
            </select>
        </div>

        <div class="form-group">
            <label>Tipo</label>
            <select name="tipo" class="form-control">
                <option value="Open" <?php if ($pregunta['tipo'] === 'Open') echo 'selected'; ?>>Abierta</option>
                <option value="Multiple" <?php if ($pregunta['tipo'] === 'Multiple') echo 'selected'; ?>>Opción Múltiple</option>
                <option value="TrueorFalse" <?php if ($pregunta['tipo'] === 'TrueorFalse') echo 'selected'; ?>>Verdadero o Falso</option>
            </select>
        </div>

        <?php if ($pregunta['tipo'] === 'Multiple' || $pregunta['tipo'] === 'TrueorFalse'): ?>
            <div class="form-group">
                <label>Respuestas</label>
                <?php
                for ($i = 0; $i < 4; $i++):
                    $resp = $respuestas[$i]['enunciado'] ?? '';
                    $esCorrecta = $respuestas[$i]['esCorrecta'] ?? 0;
                    ?>
                    <div class="form-check">
                        <input type="radio" name="correcta" value="<?php echo $i; ?>" <?php if ($esCorrecta) echo 'checked'; ?>>
                        <input type="text" class="form-control" name="respuestas[]" value="<?php echo htmlspecialchars($resp); ?>">
                    </div>
                <?php endfor; ?>
            </div>
        <?php endif; ?>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Actualizar</button>
            <a class="btn btn-secondary" href="../FOR_ADMIN/manage_q.php">Cancelar</a>
        </div>
    </form>
</div>
</body>
</html>
