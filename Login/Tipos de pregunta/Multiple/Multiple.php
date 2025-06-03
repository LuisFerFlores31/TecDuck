<?php
require '../../check_session.php';
require '../../config.php';

function sanitize_input($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    $data = $conn->real_escape_string($data);
    return $data;
}

if (!isset($_SESSION["user_id"]) || !isset($_SESSION["email"])) {
    header("Location: ../../login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $pregunta = sanitize_input($_POST["question-text"]);
    $isla = (int)$_POST["island"];
    $nivel = (int)$_POST["level"];
    $usuario = $_SESSION["email"]; 
    $tipo = 2; 
    $estado = 0; 

    if (empty($pregunta) || empty($isla) || empty($nivel) || empty($usuario)) {
        die("Error: Todos los campos requeridos deben estar completos");
    }

    // Validar que al menos haya una respuesta correcta
    $tiene_correcta = false;
    for ($i = 1; $i <= 4; $i++) {
        if (!empty($_POST["respuesta$i"]) && isset($_POST["correcta$i"]) && $_POST["correcta$i"] == 1) {
            $tiene_correcta = true;
            break;
        }
    }
    
    if (!$tiene_correcta) {
        die("Error: Debe marcar al menos una respuesta como correcta");
    }

    $conn->begin_transaction();

    try {
        $sql_pregunta = "INSERT INTO Preguntas (enunciado, isla, nivel, usuario, estado, tipo, id_validador)
                 VALUES (?, ?, ?, ?, ?, ?, ?)";

        $id_validador = null;

        $stmt = $conn->prepare($sql_pregunta);
        $stmt->bind_param("siisisi", $pregunta, $isla, $nivel, $usuario, $estado, $tipo, $id_validador);

        if (!$stmt->execute()) {
            throw new Exception("Error al insertar pregunta: " . $conn->error);
        }

        $id_pregunta = $conn->insert_id;

        // Insert answers
        for ($i = 1; $i <= 4; $i++) {
            if (!empty($_POST["respuesta$i"])) {
                $respuesta = sanitize_input($_POST["respuesta$i"]);
                $correcta = isset($_POST["correcta$i"]) ? (int)$_POST["correcta$i"] : 0;

                $sql_respuesta = "INSERT INTO Respuestas (enunciado, esCorrecta, pregunta_id, numero_respuesta)
                                VALUES (?, ?, ?, ?)";
                
                $stmt = $conn->prepare($sql_respuesta);
                $stmt->bind_param("siii", $respuesta, $correcta, $id_pregunta, $i);
                
                if (!$stmt->execute()) {
                    throw new Exception("Error al insertar respuesta: " . $conn->error);
                }
            }
        }

        $conn->commit();
        
        header("Location: Multiple.html?success=1&user=" . urlencode($usuario));
        exit();

    } catch (Exception $e) {
        $conn->rollback();
        die("Error: " . $e->getMessage());
    }
}

$conn->close();
?>