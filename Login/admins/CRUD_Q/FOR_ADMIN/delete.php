<?php
require '../../../config.php'; // Ajusta si la ruta cambia
$id = $_GET['id'] ?? null;

if (!$id) {
    header('Location: ../FOR_ADMIN/manage_q.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Eliminar respuestas relacionadas primero
    $stmt = $conn->prepare("DELETE FROM Respuestas WHERE pregunta_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    // Eliminar la pregunta
    $stmt = $conn->prepare("DELETE FROM Preguntas WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    header('Location: ../FOR_ADMIN/manage_q.php');
    exit;
}

// Obtener datos para mostrar en la confirmación
$stmt = $conn->prepare("SELECT enunciado, tipo FROM Preguntas WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$pregunta = $result->fetch_assoc();

if (!$pregunta) {
    echo "Pregunta no encontrada.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Eliminar Pregunta</title>
    <link rel="stylesheet" href="../CSS/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <h2 class="mb-4">Eliminar Pregunta</h2>

    <div class="alert alert-warning">
        <p><strong>Enunciado:</strong> <?php echo htmlspecialchars($pregunta['enunciado']); ?></p>
        <p><strong>Tipo:</strong> <?php echo htmlspecialchars($pregunta['tipo']); ?></p>
        <p>¿Estás seguro de que deseas eliminar esta pregunta? Esta acción no se puede deshacer.</p>
    </div>

    <form method="post">
        <input type="hidden" name="id" value="<?php echo $id; ?>">
        <button type="submit" class="btn btn-danger">Sí, eliminar</button>
        <a href="manage_q.php" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
</body>
</html>
