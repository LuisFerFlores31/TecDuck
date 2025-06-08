<?php
include 'config.php';

$password = $_POST['password'];
$confirm_password = $_POST['confirm_password'];

if ($password !== $confirm_password) {
    die("Error: Las contraseñas no coinciden.");
}



$nombre = $_POST['nombre'];
$apellido = $_POST['apellido'];
$email = $_POST['email']; 
$password_hashed = password_hash($_POST['password'], PASSWORD_DEFAULT);

$sql = "INSERT INTO Usuarios (name, last_name, password, email) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssss", $nombre, $apellido, $password_hashed, $email);

if ($stmt->execute()) {
    header("Location: login.php?registration_success=true"); 
    exit();
} else {
    die("Error al registrar usuario: " . $stmt->error);
}

$conn->close();
?>

