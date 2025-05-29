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
    header("Location: ../../login.html");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $pregunta = sanitize_input($_POST["question-text"]);
    $isla = (int)$_POST["isla-select"];
    $nivel = (int)$_POST["level-select"];
    $usuario = $_SESSION["email"]; 
    $tipo = 3; 
    $estado = 0; 

    $respuesta_correcta = isset($_POST["isla-answer"]) && $_POST["isla-answer"] === "true" ? 1 : 0;

    if (empty($pregunta) || empty($isla) || empty($nivel) || empty($usuario)) {
        die("Error: Todos los campos requeridos deben estar completos");
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

        
        $sql_respuesta = "INSERT INTO Respuestas (enunciado, esCorrecta, pregunta_id, numero_respuesta)
                          VALUES (?, ?, ?, ?)";
        $stmt_resp = $conn->prepare($sql_respuesta);

        $enunciado_verdadero = "Verdadero";
        $es_correcta_verdadero = $respuesta_correcta;
        $numero_verdadero = 1;
        $stmt_resp->bind_param("siii", $enunciado_verdadero, $es_correcta_verdadero, $id_pregunta, $numero_verdadero);
        if (!$stmt_resp->execute()) {
            throw new Exception("Error al insertar respuesta Verdadero: " . $conn->error);
        }

        
        $enunciado_falso = "Falso";
        $es_correcta_falso = $respuesta_correcta ? 0 : 1;
        $numero_falso = 2;
        $stmt_resp->bind_param("siii", $enunciado_falso, $es_correcta_falso, $id_pregunta, $numero_falso);
        if (!$stmt_resp->execute()) {
            throw new Exception("Error al insertar respuesta Falso: " . $conn->error);
        }

        $conn->commit();
        
        header("Location: TrueorFalse.html?success=1&user=" . urlencode($usuario));
        exit();

    } catch (Exception $e) {
        $conn->rollback();
        die("Error: " . $e->getMessage());
    }
}

$conn->close();
?>