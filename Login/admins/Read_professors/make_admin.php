<?php
session_start();
require '../../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] != 1) {
    header("Location: ../../login.php?error=access_denied");
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: read_prof.php?error=invalid_id");
    exit();
}

$id_usuario = intval($_GET['id']);

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$sql_check = "SELECT rol FROM Usuarios WHERE id = ?";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("i", $id_usuario);
$stmt_check->execute();
$result = $stmt_check->get_result();

if ($result->num_rows === 0) {
    $stmt_check->close();
    $conn->close();
    header("Location: read_prof.php?error=user_not_found");
    exit();
}

$row = $result->fetch_assoc();
if ($row['rol'] != 0) {
    $stmt_check->close();
    $conn->close();
    header("Location: read_prof.php?error=already_admin_or_invalid_role");
    exit();
}
$stmt_check->close();

// Actualizar el rol a 1 (hacer admin)
$sql_update = "UPDATE Usuarios SET rol = 1 WHERE id = ?";
$stmt_update = $conn->prepare($sql_update);
$stmt_update->bind_param("i", $id_usuario);

if ($stmt_update->execute()) {
    $stmt_update->close();
    $conn->close();
    header("Location: read_prof.php?success=Usuario+convertido+en+admin");
    exit();
} else {
    $stmt_update->close();
    $conn->close();
    header("Location: read_prof.php?error=no_se_pudo_actualizar");
    exit();
}
?>
