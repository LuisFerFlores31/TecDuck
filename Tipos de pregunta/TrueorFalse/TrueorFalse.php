<?php
// Database configuration
$dbhost = 'localhost';
$dbuser = 'root';
$dbpass = '';
$dbname = 'tecduck';

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
    $isla = (int)$_POST["isla-select"];
    $nivel = (int)$_POST["level-select"];
    $usuario = "A01738347@tec.mx"; // correo de ejemplo
    $tipo = 3; // Tipo 3 for True or False
    $estado = 0; // en espera
    $respuesta_correcta = ($_POST["isla-answer"] === "true") ? 1 : 0;

    // Validate required fields
    if (empty($pregunta) || empty($isla) || empty($nivel) || empty($usuario)) {
        die("Error: Todos los campos requeridos deben estar completos");
    }

    // Start transaction
    mysqli_begin_transaction($conn);

    try {
        // Insert question
        $sql_pregunta = "INSERT INTO Preguntas (id, enunciado, isla, nivel, usuario, estado, tipo)
                        VALUES (null, ?, ?, ?, ?, ?, ?)";
        
        $stmt = mysqli_prepare($conn, $sql_pregunta);
        mysqli_stmt_bind_param($stmt, "siisii", $pregunta, $isla, $nivel, $usuario, $estado, $tipo);
        
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Error al insertar pregunta: " . mysqli_error($conn));
        }

        $id_pregunta = mysqli_insert_id($conn);

        // Insert True answer
        $sql_respuesta = "INSERT INTO Respuestas (enunciado, esCorrecta, pregunta_id, numero_respuesta)
                          VALUES (?, ?, ?, ?)";
        
        $stmt_respuesta = mysqli_prepare($conn, $sql_respuesta);
        $respuesta_true = "Verdadero";
        $numero_true = 1;
        mysqli_stmt_bind_param($stmt_respuesta, "siii", $respuesta_true, $respuesta_correcta, $id_pregunta, $numero_true);
        
        if (!mysqli_stmt_execute($stmt_respuesta)) {
            throw new Exception("Error al insertar respuesta Verdadero: " . mysqli_error($conn));
        }

        // Insert False answer
        $respuesta_false = "Falso";
        $numero_false = 2;
        $respuesta_correcta_false = ($respuesta_correcta === 1) ? 0 : 1; // Opposite of the correct answer
        mysqli_stmt_bind_param($stmt_respuesta, "siii", $respuesta_false, $respuesta_correcta_false, $id_pregunta, $numero_false);
        
        if (!mysqli_stmt_execute($stmt_respuesta)) {
            throw new Exception("Error al insertar respuesta Falso: " . mysqli_error($conn));
        }

        // Commit transaction
        mysqli_commit($conn);
        
        // Redirect with success message
        //header("Location: TrueorFalse.html?success=1");
        //exit();

    } catch (Exception $e) {
        // Rollback transaction on error
        mysqli_rollback($conn);
        die("Error: " . $e->getMessage());
    }
}

mysqli_close($conn);
?>
