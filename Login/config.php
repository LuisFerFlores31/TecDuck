<?php
$host = '127.0.0.1';
$db = 'ekisde'; 
$user = 'root';            
$pass = '@Murasakibara23';                

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}
?>
