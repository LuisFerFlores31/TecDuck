<?php
include __DIR__ . '/../check_session.php';
require __DIR__ . '/../database.php';
$pdo = Database::connect();

$id = intval($_GET['id'] ?? 0);
if ($id > 0) {
    $stmt = $pdo->prepare("DELETE FROM preguntas WHERE id = ?");
    $stmt->execute([$id]);
}

header('Location: gestion_preguntas.php');
exit;
?>
