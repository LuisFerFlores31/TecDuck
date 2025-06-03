<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

function getUserRole() {
    if (!isset($_SESSION["rol"])) {
        require_once 'config.php';
        
        $stmt = $conn->prepare("SELECT rol FROM Usuarios WHERE id = ?");
        $stmt->bind_param("i", $_SESSION["user_id"]);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            $_SESSION["rol"] = $user["rol"];
        } else {
            session_destroy();
            header("Location: login.php");
            exit;
        }
        $stmt->close();
    }
    
    return $_SESSION["rol"];
}

function isAdmin() {
    return getUserRole() == 1;
}

function isProfesor() {
    return getUserRole() == 0;
}

function getHomePage() {
    return isAdmin() ? 'admin.php' : 'profesor.php';
}

function getHomeUrl() {
    $homePage = getHomePage();
    $currentDir = dirname($_SERVER['PHP_SELF']);
    
    if (strpos($currentDir, '/admins') !== false) {
        return '../admins/' . $homePage;
    } elseif (strpos($currentDir, '/Tipos') !== false) {
        return '../admins/' . $homePage;
    } else {
        return 'admins/' . $homePage;
    }
}

function redirectToHome() {
    header("Location: " . getHomeUrl());
    exit;
}

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

$current_user_id = $_SESSION["user_id"];
$current_user_email = $_SESSION["email"] ?? '';
$current_user_role = getUserRole();
$is_admin = isAdmin();
$is_profesor = isProfesor();
$home_url = getHomeUrl();



?>