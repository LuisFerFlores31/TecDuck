<?php

require '../../check_session.php';
require '../../config.php';

function sanitize_input($data) {
    global $conn;

    if ($data === null || $data === '') {
        return null;
    }

    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    $data = $conn->real_escape_string($data);
    return $data;
}

function procesarImagen($archivo) {
    $tiposValidos = ['image/jpeg', 'image/jpg', 'image/png'];
    $extensionesValidas = ['jpg', 'jpeg', 'png'];
    
    if ($archivo['error'] !== UPLOAD_ERR_OK) {
        if ($archivo['error'] === UPLOAD_ERR_NO_FILE) {
            return null; 
        }
        throw new Exception("Error al subir el archivo");
    }
    
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $archivo['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mimeType, $tiposValidos)) {
        throw new Exception("Tipo de archivo no válido. Solo se permiten JPG, JPEG y PNG");
    }
    
    $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, $extensionesValidas)) {
        throw new Exception("Extensión de archivo no válida");
    }
    
    if ($archivo['size'] > 5 * 1024 * 1024) {
        throw new Exception("El archivo es demasiado grande. Máximo 5MB");
    }
    
    return file_get_contents($archivo['tmp_name']);
}

if (!isset($_SESSION["user_id"]) || !isset($_SESSION["email"])) {
    header("Location: ../../login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $pregunta = !empty($_POST["question-text"]) ? sanitize_input($_POST["question-text"]) : null;
    $isla = (int)$_POST["island"];
    $nivel = (int)$_POST["level"];
    
    $usuario = $_SESSION["email"]; 
    $tipo = 'Open'; 

    $rol = $_SESSION['rol']; 

    if ($rol === 1) { 
        $estado = 1; 
        $id_validador = 1;
    } else { 
        $estado = 0; 
        $id_validador = null; 
    }

    // Procesar imagen
    $imagenBinaria = null;
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] !== UPLOAD_ERR_NO_FILE) {
        try {
            $imagenBinaria = procesarImagen($_FILES['imagen']);
        } catch (Exception $e) {
            die("Error con la imagen: " . $e->getMessage());
        }
    }

    // Validaciones
    if (empty($pregunta) && $imagenBinaria === null) {
        die("Error: Debe proporcionar al menos un enunciado de texto o una imagen");
    }

    if (empty($isla) || empty($nivel) || empty($usuario)) {
        die("Error: isla, nivel y usuario son requeridos");
    }

    $respuestas_validas = 0;
    for ($i = 1; $i <= 4; $i++) {
        if (!empty($_POST["answer$i"])) {
            $respuestas_validas++;
        }
    }
    
    if ($respuestas_validas === 0) {
        die("Error: Debe proporcionar al menor una respuesta válida");
    }

    $conn->begin_transaction();

    try {
        if ($imagenBinaria !== null) {
            $sql_pregunta = "INSERT INTO Preguntas (enunciado, imagen, isla, nivel, usuario, estado, tipo, id_validador)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql_pregunta);
            if (!$stmt) {
                throw new Exception("Error al preparar consulta: " . $conn->error);
            }
            
            // CORRECCIÓN: Para LONGBLOB usar NULL en bind_param y luego send_long_data
            $null = null;
            $stmt->bind_param("sbiisisi", $pregunta, $null, $isla, $nivel, $usuario, $estado, $tipo, $id_validador);
            $stmt->send_long_data(1, $imagenBinaria); // índice 1 corresponde al segundo parámetro (imagen)
            
        } else {
            // CORRECCIÓN: Usar campo 'usuario' en lugar de 'usuario_id'
            $sql_pregunta = "INSERT INTO Preguntas (enunciado, isla, nivel, usuario, estado, tipo, id_validador)
                            VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql_pregunta);
            if (!$stmt) {
                throw new Exception("Error al preparar consulta: " . $conn->error);
            }
            $stmt->bind_param("siisisi", $pregunta, $isla, $nivel, $usuario, $estado, $tipo, $id_validador);
        }
        
        if (!$stmt->execute()) {
            throw new Exception("Error al insertar pregunta: " . $stmt->error);
        }

        $id_pregunta = $conn->insert_id;

        // Insertar respuestas
        for ($i = 1; $i <= 4; $i++) {
            if (!empty($_POST["answer$i"])) {
                $respuesta = sanitize_input($_POST["answer$i"]);
                $correcta = 1; 

                $sql_respuesta = "INSERT INTO Respuestas (enunciado, esCorrecta, pregunta_id, numero_respuesta)
                                VALUES (?, ?, ?, ?)";
                
                $stmt = $conn->prepare($sql_respuesta);
                $stmt->bind_param("siii", $respuesta, $correcta, $id_pregunta, $i);
                
                if (!$stmt->execute()) {
                    throw new Exception("Error al insertar respuesta: " . $stmt->error);
                }
            }
        }

        $conn->commit();
        
        header("Location: OpenQ.html?success=1&user=" . urlencode($_SESSION["email"]));
        exit();

    } catch (Exception $e) {
        $conn->rollback();
        die("Error: " . $e->getMessage());
    }
}

$conn->close();



?>

