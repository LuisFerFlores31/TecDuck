<?php

$dbhost = 'localhost';
$dbuser = 'root';
$dbpass = '';
$dbname = 'tecduck';

$conn = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$nombre = $_POST["username"];
$pass = $_POST["password"];

$query = mysqli_query($conn, "SELECT * FROM login WHERE username='$nombre' AND password='$pass'");
$nr = mysqli_num_rows($query);

if ($nr == 1) {
    echo "Login successful ". $nombre;
} else {
    echo "Invalid username or password";
}

?>