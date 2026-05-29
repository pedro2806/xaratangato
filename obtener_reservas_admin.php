<?php
require_once 'includes/config.php';
session_start();
// Aquí podrías añadir una validación para que solo TÚ entres
header('Content-Type: application/json');

try {
    $sql = "SELECT 
                r.id as reserva_id,
                u.nombre as cliente,
                res.nombre as recurso,
                rd.fecha_inicio,
                rd.fecha_fin,
                rd.cantidad,
                r.total,
                r.estado
            FROM reservas r
            JOIN usuarios u ON r.usuario_id = u.id
            JOIN reserva_detalles rd ON r.id = rd.reserva_id
            JOIN recursos res ON rd.recurso_id = res.id
            ORDER BY r.fecha_registro DESC";

    $stmt = $pdo->query($sql);
    echo json_encode($stmt->fetchAll());
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}