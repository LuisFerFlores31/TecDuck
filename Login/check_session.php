<?php
session_start();

// Verificar si hay sesión activa
if (!isset($_SESSION["user_id"])) {
    header("Location: login.html");
    exit;
}

// Función para obtener el rol del usuario actual
function getUserRole() {
    if (!isset($_SESSION["rol"])) {
        // Si no está en sesión, consultamos la BD
        require_once 'config.php';
        
        $stmt = $conn->prepare("SELECT rol FROM Usuarios WHERE id = ?");
        $stmt->bind_param("i", $_SESSION["user_id"]);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            $_SESSION["rol"] = $user["rol"];
        } else {
            // Usuario no encontrado, destruir sesión
            session_destroy();
            header("Location: login.html");
            exit;
        }
        $stmt->close();
    }
    
    return $_SESSION["rol"];
}

// Función para verificar si es admin
function isAdmin() {
    return getUserRole() == 1;
}

// Función para verificar si es profesor
function isProfesor() {
    return getUserRole() == 0;
}

// Función para obtener la página home según el rol
function getHomePage() {
    return isAdmin() ? 'admin.php' : 'profesor.php';
}

// Función para obtener la ruta completa al home
function getHomeUrl() {
    $homePage = getHomePage();
    // Calcular la ruta relativa desde donde se esté llamando
    $currentDir = dirname($_SERVER['PHP_SELF']);
    
    // Si estamos en subdirectorios, necesitamos subir
    if (strpos($currentDir, '/admins') !== false) {
        return '../admins/' . $homePage;
    } elseif (strpos($currentDir, '/Tipos') !== false) {
        return '../admins/' . $homePage;
    } else {
        return 'admins/' . $homePage;
    }
}

// Función para redirigir al home apropiado
function redirectToHome() {
    header("Location: " . getHomeUrl());
    exit;
}

// Función para verificar permisos específicos
function requireAdmin() {
    if (!isAdmin()) {
        redirectToHome();
    }
}

function requireProfesor() {
    if (!isProfesor()) {
        redirectToHome();
    }
}

// Variables globales útiles para usar en las páginas
$current_user_id = $_SESSION["user_id"];
$current_user_email = $_SESSION["email"] ?? '';
$current_user_role = getUserRole();
$is_admin = isAdmin();
$is_profesor = isProfesor();
$home_url = getHomeUrl();

// Cerrar conexión si existe
if (isset($conn)) {
    $conn->close();
}

?>