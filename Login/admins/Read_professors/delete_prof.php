<?php
session_start();
require '../../config.php';


if (!isset($_SESSION['user_id']) || $_SESSION['rol'] != 1) {
    header("Location: ../../login.php?error=access_denied");
    exit();
}

$id = isset($_GET['id']) ? $_GET['id'] : null;
if (!$id) {
    header("Location: read_prof.php");
    exit;
}

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Obtener datos del usuario
$sql = "SELECT * FROM Usuarios WHERE id = ? AND rol = 0";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();

if (!$usuario) {
    header("Location: read_prof.php?error=user_not_found");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['confirm_delete'])) {
    $delete_sql = "DELETE FROM Usuarios WHERE id = ? AND rol = 0";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("i", $id);
    
    if ($delete_stmt->execute()) {
        $delete_stmt->close();
        $conn->close();
        header("Location: read_prof.php?success=user_deleted");
        exit;
    } else {
        $error_message = "Error al eliminar el usuario";
    }
    $delete_stmt->close();
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Eliminar Profesor</title>
    <style>
        .danger-zone {
            border: 2px solid #dc3545;
            border-radius: 8px;
            background-color: #f8d7da;
            padding: 20px;
            margin-top: 20px;
        }
        .user-details {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <div class="row">
        <div class="col-md-8">
            <h3 class="text-danger">
                <i class="bi bi-exclamation-triangle-fill"></i>
                Eliminar Profesor
            </h3>
        </div>
    </div>

    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger mt-3" role="alert">
            <?php echo htmlspecialchars($error_message); ?>
        </div>
    <?php endif; ?>

    <div class="user-details mt-4">
        <h4>Detalles del Profesor a Eliminar</h4>
        <div class="row mt-3">
            <div class="col-md-3">
                <label class="fw-bold">ID:</label>
            </div>
            <div class="col-md-9">
                <span><?php echo htmlspecialchars($usuario['id']); ?></span>
            </div>
        </div>
        <div class="row mt-2">
            <div class="col-md-3">
                <label class="fw-bold">Nombre:</label>
            </div>
            <div class="col-md-9">
                <span><?php echo htmlspecialchars($usuario['name']); ?></span>
            </div>
        </div>
        <div class="row mt-2">
            <div class="col-md-3">
                <label class="fw-bold">Apellido:</label>
            </div>
            <div class="col-md-9">
                <span><?php echo htmlspecialchars($usuario['last_name']); ?></span>
            </div>
        </div>
        <div class="row mt-2">
            <div class="col-md-3">
                <label class="fw-bold">Email:</label>
            </div>
            <div class="col-md-9">
                <span><?php echo htmlspecialchars($usuario['email']); ?></span>
            </div>
        </div>
        <div class="row mt-2">
            <div class="col-md-3">
                <label class="fw-bold">Rol:</label>
            </div>
            <div class="col-md-9">
                <span class="badge bg-info">Profesor</span>
            </div>
        </div>
    </div>

    <div class="danger-zone">
        <h5 class="text-danger mb-3">
            <i class="bi bi-exclamation-triangle"></i>
            ¿Está seguro de que desea eliminar este profesor?
        </h5>
        <p class="mb-3">
            <strong>Esta acción es irreversible.</strong> Al eliminar este profesor:
        </p>
        <ul class="text-danger mb-4">
            <li>Se perderá toda la información del usuario</li>
            <li>No podrá acceder más al sistema</li>
            <li>Todas las preguntas asociadas podrían quedar sin autor</li>
        </ul>
        
        <form method="POST" onsubmit="return confirmDelete()">
            <div class="d-flex gap-3">
                <button type="submit" name="confirm_delete" class="btn btn-danger">
                    <i class="bi bi-trash"></i>
                    Confirmar Eliminación
                </button>
                <a href="read_prof.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i>
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function confirmDelete() {
    return confirm('¿Está completamente seguro de que desea eliminar este profesor? Esta acción NO se puede deshacer.');
}
</script>
</body>
</html>