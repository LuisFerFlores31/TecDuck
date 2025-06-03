<?php
// Incluir sistema de sesiones mejorado
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
    $tipo = 1; 
    $estado = 0; 

    if (empty($pregunta) || empty($isla) || empty($nivel) || empty($usuario)) {
        die("Error: Todos los campos requeridos deben estar completos");
    }

    $respuestas_validas = 0;
    for ($i = 1; $i <= 4; $i++) {
        if (!empty($_POST["answer$i"])) {
            $respuestas_validas++;
        }
    }
    
    if ($respuestas_validas === 0) {
        die("Error: Debe proporcionar al menos una respuesta válida");
    }

    $conn->begin_transaction();

    try {
        $sql_pregunta = "INSERT INTO Preguntas (id, enunciado, isla, nivel, usuario, estado, tipo, fecha_creacion, id_validador)
                        VALUES (null, ?, ?, ?, ?, ?, ?, ?, ?)";
        $fecha_creacion = null;
        $id_validador = null;

        $stmt = $conn->prepare($sql_pregunta);
        $stmt->bind_param("siisiiii", $pregunta, $isla, $nivel, $usuario, $estado, $tipo, $fecha_creacion, $id_validador);
        
        if (!$stmt->execute()) {
            throw new Exception("Error al insertar pregunta: " . $conn->error);
        }

        $id_pregunta = $conn->insert_id;

        // Para preguntas abiertas, todas son correctas
        for ($i = 1; $i <= 4; $i++) {
            if (!empty($_POST["answer$i"])) {
                $respuesta = sanitize_input($_POST["answer$i"]);
                $correcta = 1; 

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
        
        header("Location: OpenQ.html?success=1&user=" . urlencode($usuario));
        exit();

    } catch (Exception $e) {
        $conn->rollback();
        die("Error: " . $e->getMessage());
    }
}

$conn->close();
?>