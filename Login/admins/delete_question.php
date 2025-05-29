<?php
// delete_question.php
include __DIR__ . '/../check_session.php';
require __DIR__ . '/../database.php';
$pdo = Database::connect();

$id = intval($_GET['id'] ?? 0);
if ($id > 0) {
    // ID del usuario logueado (debe venir de tu sesión)
    $usuarioId = $_SESSION['id_usuario'];

    // 1) Registrar en Modificar
    $stmt = $pdo->prepare("
      INSERT INTO Modificar (id_usuario, id_pregunta, fecha_modificacion)
      VALUES (:idu, :idp, NOW())
    ");
    $stmt->execute([
      ':idu' => $usuarioId,
      ':idp' => $id
    ]);

    // 2) Registrar en Elimina
    $stmt = $pdo->prepare("
      INSERT INTO Elimina (id_usuario, id_pregunta, fecha_modificacion)
      VALUES (:idu, :idp, NOW())
    ");
    $stmt->execute([
      ':idu' => $usuarioId,
      ':idp' => $id
    ]);

    // 3) Borrar respuestas asociadas
    $stmt = $pdo->prepare("DELETE FROM Respuestas WHERE pregunta_id = :id");
    $stmt->execute([':id' => $id]);

    // 4) Borrar la pregunta
    $stmt = $pdo->prepare("DELETE FROM Preguntas WHERE id = :id");
    $stmt->execute([':id' => $id]);
}

Database::disconnect();
header('Location: gestion_preguntas.php');
exit;
