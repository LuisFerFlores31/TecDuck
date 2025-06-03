<?php
session_start();
include 'config.php';

$email = $_POST["email"] ?? '';
$password = $_POST["password"] ?? '';

$sql = "SELECT * FROM Usuarios WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

$response = ["success" => false];

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();

    if (password_verify($password, $user['password'])) {
        $_SESSION["user_id"] = $user["id"];
        $_SESSION["email"] = $user["email"];
        $_SESSION["rol"] = $user["rol"];

        switch ($user["rol"]) {
            case 0:
                $response = ["success" => true, "redirect" => "admins/profesor.php"];
                break;
            case 1:
                $response = ["success" => true, "redirect" => "admins/admin.php"];
                break;
            default:
                $response = ["success" => false, "message" => "Rol no reconocido.", "type" => "email"];
        }
    } else {
        $response = ["success" => false, "message" => "   Contraseña incorrecta.", "type" => "password"];
    }
} else {
    $response = ["success" => false, "message" => "   Correo no registrado.", "type" => "email"];
}

$stmt->close();
$conn->close();

header('Content-Type: application/json');
echo json_encode($response);
