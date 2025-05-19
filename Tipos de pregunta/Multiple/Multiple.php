CREATE TABLE Preguntas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    enunciado TEXT NOT NULL,
    isla TINYINT NOT NULL,
    nivel TINYINT NOT NULL,
    usuario VARCHAR(40) NOT NULL,
    estado TINYINT NOT NULL,  -- 1 = activa, 0 = inactiva

    tipo ENUM('Open', 'Multiple', 'TrueorFalse') NOT NULL,

    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    id_validador INT DEFAULT NULL,

    FOREIGN KEY (usuario) REFERENCES Usuarios(email),
    FOREIGN KEY (id_validador) REFERENCES Usuarios(id)
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
// Database configuration
$dbhost = 'localhost';
$dbuser = 'root';
$dbpass = '';
$dbname = 'tecduck2';

// Create connection
$conn = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);

// Check connection
if (!$conn) {
    die("Error de conexión: " . mysqli_connect_error());
}

// Function to sanitize input
function sanitize_input($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    $data = mysqli_real_escape_string($conn, $data);
    return $data;
}

// Validate and sanitize input
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $pregunta = sanitize_input($_POST["question-text"]);
    $isla = (int)$_POST["island"];
    $nivel = (int)$_POST["level"];
    $usuario = "A01738347@tec.mx"; // correo de ejemplo
    $tipo = 2; // tipo de pregunta MULTIPLE
    $estado = 0; // en espera

    // Validate required fields
    if (empty($pregunta) || empty($isla) || empty($nivel) || empty($usuario)) {
        die("Error: Todos los campos requeridos deben estar completos");
    }

    // Start transaction
    mysqli_begin_transaction($conn);

    try {
        // Insert question
        $sql_pregunta = "INSERT INTO Preguntas (id, enunciado, isla, nivel, usuario, estado, tipo, fecha_creacion, id_validador)
                        VALUES (null, ?, ?, ?, ?, ?, ?, ?, ?)";
        $fecha_creacion = null;
        $id_validador = null;

        $stmt = mysqli_prepare($conn, $sql_pregunta);
        mysqli_stmt_bind_param($stmt, "siisiiii", $pregunta, $isla, $nivel, $usuario, $estado, $tipo, $fecha_creacion, $id_validador);
        
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Error al insertar pregunta: " . mysqli_error($conn));
        }

        $id_pregunta = mysqli_insert_id($conn);

        // Insert answers
        for ($i = 1; $i <= 4; $i++) {
            if (!empty($_POST["respuesta$i"])) {
                $respuesta = sanitize_input($_POST["respuesta$i"]);
                $correcta = (int)$_POST["correcta$i"];

                $sql_respuesta = "INSERT INTO Respuestas (enunciado, esCorrecta, pregunta_id, numero_respuesta)
                                VALUES (?, ?, ?, ?)";
                
                $stmt = mysqli_prepare($conn, $sql_respuesta);
                mysqli_stmt_bind_param($stmt, "siii", $respuesta, $correcta, $id_pregunta, $i);
                
                if (!mysqli_stmt_execute($stmt)) {
                    throw new Exception("Error al insertar respuesta: " . mysqli_error($conn));
                }
            }
        }

        // Commit transaction
        mysqli_commit($conn);
        
        // Redirect with success message
        header("Location: Multiple.html?success=1");
        exit();

    } catch (Exception $e) {
        // Rollback transaction on error
        mysqli_rollback($conn);
        die("Error: " . $e->getMessage());
    }
}

mysqli_close($conn);
?>
