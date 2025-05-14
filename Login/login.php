<?php
session_start();
include 'config.php';

$email = $_POST["email"];
$password = $_POST["password"];

$sql = "SELECT * FROM Usuarios WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();

    // Verificar la contraseña con password_verify
    if (password_verify($password, $user['password'])) {
        // Guardar en sesión si quieres
        $_SESSION["user_id"] = $user["id"];
        $_SESSION["email"] = $user["email"];
        $_SESSION["rol"] = $user["rol"];

        // Redirigir según rol
        switch ($user["rol"]) {
            case 0:
                header("Location: ../Login/admins/profesor.php");  
                exit;
            case 1:
                header("Location: ../Login/admins/admin.php");
                exit;
            default:
                echo "Rol no reconocido.";
                break;
        }
    } else {
        echo "Contraseña incorrecta.";
    }
} else {
    echo "Usuario no encontrado.";
}

$stmt->close();
$conn->close();
?>