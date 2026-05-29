<?php
require_once __DIR__ . '/config.php';
session_start();

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin';
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . APP_URL . '/login.php');
        exit;
    }
}

function login($email, $password) {
    $db = getDB();
    $stmt = $db->prepare("SELECT id, nombre, apellido, email, password, rol FROM usuarios WHERE email = ?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id']  = $user['id'];
        $_SESSION['nombre']   = $user['nombre'];
        $_SESSION['apellido'] = $user['apellido'];
        $_SESSION['email']    = $user['email'];
        $_SESSION['rol']      = $user['rol'];
        return true;
    }
    return false;
}

function logout() {
    session_destroy();
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

function register($nombre, $apellido, $email, $password, $telefono = '') {
    $db = getDB();
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $db->prepare("INSERT INTO usuarios (nombre, apellido, email, password, telefono) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param('sssss', $nombre, $apellido, $email, $hash, $telefono);
    return $stmt->execute();
}

function getUserReservas($user_id) {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM reservas WHERE usuario_id = ? ORDER BY created_at DESC");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function crearReserva($data) {
    $db = getDB();
    $stmt = $db->prepare("INSERT INTO reservas (usuario_id, tipo, item_id, fecha_inicio, fecha_fin, hora_inicio, hora_fin, cantidad, total, notas) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param(
        'isissssiis',
        $data['usuario_id'],
        $data['tipo'],
        $data['item_id'],
        $data['fecha_inicio'],
        $data['fecha_fin'],
        $data['hora_inicio'],
        $data['hora_fin'],
        $data['cantidad'],
        $data['total'],
        $data['notas']
    );
    return $stmt->execute() ? $db->insert_id : false;
}

function cancelarReserva($id, $user_id) {
    $db = getDB();
    $stmt = $db->prepare("UPDATE reservas SET estado='cancelada' WHERE id=? AND usuario_id=?");
    $stmt->bind_param('ii', $id, $user_id);
    return $stmt->execute();
}
?>
