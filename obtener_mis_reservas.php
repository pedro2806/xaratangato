<?php
require_once 'includes/config.php';
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode([]);
    exit;
}

try {
    $sql = "SELECT 
                r.id as reserva_id,
                res.nombre as recurso,
                cat.nombre as categoria,
                rd.fecha_inicio,
                rd.fecha_fin,
                rd.cantidad,
                r.total,
                r.estado
            FROM reservas r
            JOIN reserva_detalles rd ON r.id = rd.reserva_id
            JOIN recursos res ON rd.recurso_id = res.id
            JOIN categorias cat ON res.categoria_id = cat.id
            WHERE r.usuario_id = ?
            ORDER BY r.fecha_registro DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$_SESSION['user_id']]);
    echo json_encode($stmt->fetchAll());

} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}