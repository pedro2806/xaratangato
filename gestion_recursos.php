<?php
require_once 'includes/config.php';
session_start();
header('Content-Type: application/json');

// Validación básica de seguridad (puedes ampliarla después)
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'mensaje' => 'No autorizado']);
    exit;
}

$id = $_POST['recurso_id'] ?? null;
$nombre = $_POST['nombre'];
$categoria = $_POST['categoria_id'];
$precio = $_POST['precio_decimal'];
$stock = $_POST['stock_total'];
$img = $_POST['imagen_url'];
$desc = $_POST['descripcion'];

try {
    if ($id) {
        // Editar recurso existente
        $sql = "UPDATE recursos SET nombre=?, categoria_id=?, precio_decimal=?, stock_total=?, imagen_url=?, descripcion=? WHERE id=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nombre, $categoria, $precio, $stock, $img, $desc, $id]);
        $mensaje = "Recurso actualizado con éxito";
    } else {
        // Crear nuevo recurso
        $sql = "INSERT INTO recursos (nombre, categoria_id, precio_decimal, stock_total, imagen_url, descripcion) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nombre, $categoria, $precio, $stock, $img, $desc]);
        $mensaje = "Recurso creado con éxito";
    }
    echo json_encode(['success' => true, 'mensaje' => $mensaje]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'mensaje' => $e->getMessage()]);
}

?>