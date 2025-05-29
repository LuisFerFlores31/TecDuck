<?php
include __DIR__ . '/../check_session.php';
require __DIR__ . '/../database.php';
$pdo = Database::connect();

$id = intval($_GET['id'] ?? 0);
if ($id > 0) {
    $usuarioId = $_SESSION['id_usuario'];

    $stmt = $pdo->prepare("
      INSERT INTO Modificar (id_usuario, id_pregunta, fecha_modificacion)
      VALUES (:idu, :idp, NOW())
    ");
    $stmt->execute([
      ':idu' => $usuarioId,
      ':idp' => $id
    ]);

    $stmt = $pdo->prepare("
      INSERT INTO Elimina (id_usuario, id_pregunta, fecha_modificacion)
      VALUES (:idu, :idp, NOW())
    ");
    $stmt->execute([
      ':idu' => $usuarioId,
      ':idp' => $id
    ]);

    $stmt = $pdo->prepare("DELETE FROM Respuestas WHERE pregunta_id = :id");
    $stmt->execute([':id' => $id]);

    $stmt = $pdo->prepare("DELETE FROM Preguntas WHERE id = :id");
    $stmt->execute([':id' => $id]);
}

Database::disconnect();
header('Location: gestion_preguntas.php');
exit;
