<?php
session_start();
require_once __DIR__ . '/../../config.php';

// Verificar si es admin (rol 1)
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] != 1) {
    header("Location: ../../login.php?error=access_denied");
    exit();
}

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Panel Admin - Gestión Profesores</title>
    <style>
        .table-responsive {
            overflow-x: auto;
        }
        .teacher-row {
            background-color: #f8f9fa;
        }
    </style>
</head>

<body>
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-8">
                <h3>Usuarios con permiso de Profesores</h3>
            </div>
            <div class="col-md-4 text-end">
                <a href="../../admins/admin.php" class="btn btn-warning">Volver</a>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-12 table-responsive">
                <table class="table table-striped table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Apellido</th>
                            <th>Email</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // SOLO MOSTRAR PROFESORES (rol 0)
                        $sql = "SELECT * FROM Usuarios WHERE rol = 0 ORDER BY id";
                        $result = $conn->query($sql);
                        
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo '<tr class="teacher-row">';
                                echo '<td>' . htmlspecialchars($row['id']) . '</td>';
                                echo '<td>' . htmlspecialchars($row['name']) . '</td>';
                                echo '<td>' . htmlspecialchars($row['last_name']) . '</td>';
                                echo '<td>' . htmlspecialchars($row['email']) . '</td>';
                                echo '<td width="250">';
                                echo '<a href="make_admin.php?id=' . $row['id'] . '" class="btn btn-success btn-sm">Hacer Admin</a> ';
                                echo '<a href="delete_prof.php?id=' . $row['id'] . '" class="btn btn-danger btn-sm">Eliminar</a>';
                                echo '</td>';
                                echo '</tr>';
                            }
                        } else {
                            echo '<tr><td colspan="5" class="text-center">No hay profesores registrados</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
$conn->close();
?>