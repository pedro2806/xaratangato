<?php
// Desactivar salida de errores HTML para no romper el JSON
ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once 'includes/config.php';
header('Content-Type: application/json');

$inicio = $_GET['inicio'] ?? null;
$fin    = $_GET['fin']    ?? null;
$categoria = $_GET['categoria'] ?? 0;

if (!$inicio || !$fin) {
    echo json_encode([]);
    exit;
}

try {
    // Construimos la base del Query
    $sql = "SELECT 
                r.id, 
                r.nombre, 
                r.precio_decimal, 
                r.categoria_id,
                r.stock_total,
                r.imagen_url,
                c.nombre as categoria,
                (r.stock_total - IFNULL(
                    (SELECT SUM(rd.cantidad) 
                        FROM reserva_detalles rd 
                        JOIN reservas res ON rd.reserva_id = res.id
                        WHERE rd.recurso_id = r.id 
                        AND res.estado != 'cancelada'
                        AND (rd.fecha_inicio < :fin AND rd.fecha_fin > :inicio)
                    ), 0)
                ) as disponibles
            FROM recursos r
            JOIN categorias c ON r.categoria_id = c.id";

    // Si el usuario eligió una categoría específica (que no sea "0" o "Todos")
    if ($categoria > 0) {
        $sql .= " WHERE r.categoria_id = :categoria";
    }

    $sql .= " HAVING disponibles > 0";

    $stmt = $pdo->prepare($sql);
    
    $params = ['inicio' => $inicio, 'fin' => $fin];
    if ($categoria > 0) $params['categoria'] = $categoria;

    $stmt->execute($params);
    $resultados = $stmt->fetchAll();

    echo json_encode($resultados);

} catch (PDOException $e) {
    // Esto te enviará el error real de MySQL a la consola del navegador
    http_response_code(500);
    echo json_encode([
        'error' => true, 
        'mensaje' => $e->getMessage(),
        'sql_state' => $e->getCode()
    ]);
}