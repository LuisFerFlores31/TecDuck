<?php
$host = '127.0.0.1';
$db = 'matecduck'; 
$user = 'root';            
$pass = '';                

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}
?>
