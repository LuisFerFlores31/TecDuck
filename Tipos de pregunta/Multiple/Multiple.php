CREATE TABLE Preguntas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    enunciado TEXT NOT NULL,
    isla TINYINT NOT NULL,
    nivel TINYINT NOT NULL,
    usuario VARCHAR(40) NOT NULL,
    estado TINYINT NOT NULL DEFAULT 0,             -- 1 = aprobada, 0 = en espera de aprobación, 2 = rechazada
    tipo TINYINT NOT NULL,               -- 1 = opción múltiple, 2 = respuesta abierta, 3 = verdadero/falso
    FOREIGN KEY (usuario) REFERENCES Usuarios(email)
);


CREATE TABLE Respuestas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    enunciado VARCHAR(255) NOT NULL,
    esCorrecta BOOLEAN NOT NULL DEFAULT 0,
    pregunta_id INT NOT NULL,
    numero_respuesta TINYINT NOT NULL,
    UNIQUE (pregunta_id, numero_respuesta),
    FOREIGN KEY (pregunta_id) REFERENCES Preguntas(id)
);

<?php

$dbhost = 'localhost';
$dbuser = 'root';
$dbpass = '';
$dbname = 'tecduck';

$conn = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
if (!$conn) {
    die("Error de conexión: " . mysqli_connect_error());
}

// Recoger datos
$pregunta = $_POST["question-text"];
$isla = (int)$_POST["island"];
$nivel = (int)$_POST["level"];
$usuario = $_POST["usuario"];
$tipo = (int)$_POST["tipo"];
$estado = 0; // en espera

// Insertar pregunta
$sql_pregunta = "INSERT INTO Preguntas (enunciado, isla, nivel, usuario, estado, tipo)
                 VALUES ('$pregunta', $isla, $nivel, '$usuario', $estado, $tipo)";

if (mysqli_query($conn, $sql_pregunta)) {
    $id_pregunta = mysqli_insert_id($conn);

    // Insertar respuestas
    for ($i = 1; $i <= 4; $i++) {
        if (!empty($_POST["respuesta$i"])) {
            $respuesta = $_POST["respuesta$i"];
            $correcta = (int)$_POST["correcta$i"];

            $sql_respuesta = "INSERT INTO Respuestas (enunciado, esCorrecta, pregunta_id, numero_respuesta)
                              VALUES ('$respuesta', $correcta, $id_pregunta, $i)";
            mysqli_query($conn, $sql_respuesta);
        }
    }

    // Confirmación
    echo "<script>alert('Pregunta guardada con éxito'); window.location.href='Multiple.html';</script>";
} else {
    echo "Error al insertar pregunta: " . mysqli_error($conn);
}

mysqli_close($conn);
?>
