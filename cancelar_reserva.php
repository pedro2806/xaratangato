<?php
require_once 'includes/config.php';
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'mensaje' => 'Sesión expirada']);
    exit;
}

$reserva_id = $_POST['id'] ?? null;

try {
    // Verificamos que la reserva pertenezca al usuario antes de cancelarla
    $stmt = $pdo->prepare("UPDATE reservas SET estado = 'cancelada' WHERE id = ? AND usuario_id = ?");
    $stmt->execute([$reserva_id, $_SESSION['user_id']]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'mensaje' => 'No se pudo cancelar o ya estaba cancelada']);
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'mensaje' => $e->getMessage()]);
}