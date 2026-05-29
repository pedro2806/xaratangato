<?php
require_once 'includes/config.php';
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'mensaje' => 'Sesión expirada']);
    exit;
}

try {
    $pdo->beginTransaction();

    // 1. Crear la cabecera
    $stmt = $pdo->prepare("INSERT INTO reservas (usuario_id, total, estado) VALUES (?, ?, 'confirmada')");
    $stmt->execute([$_SESSION['user_id'], $_POST['total']]);
    $reserva_id = $pdo->lastInsertId();

    // 2. Crear el detalle
    $stmt = $pdo->prepare("INSERT INTO reserva_detalles (reserva_id, recurso_id, fecha_inicio, fecha_fin, cantidad) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([
        $reserva_id, 
        $_POST['recurso_id'], 
        $_POST['inicio'], 
        $_POST['fin'], 
        $_POST['cantidad']
    ]);

    $pdo->commit();
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'mensaje' => $e->getMessage()]);
}