<?php
require_once 'includes/config.php';
session_start();
header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

if ($action == 'login') {
    $email = $_POST['email'];
    $pass  = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($pass, $user->password)) {
        $_SESSION['user_id'] = $user->id;
        $_SESSION['user_name'] = $user->nombre;
        echo json_encode(['success' => true, 'mensaje' => '¡Bienvenido a Xaratangato!']);
    } else {
        echo json_encode(['success' => false, 'mensaje' => 'Credenciales incorrectas']);
    }
}

if ($action == 'register') {
    $nombre = $_POST['nombre'];
    $email  = $_POST['email'];
    $pass   = password_hash($_POST['password'], PASSWORD_DEFAULT);

    try {
        $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, email, password) VALUES (?, ?, ?)");
        $stmt->execute([$nombre, $email, $pass]);
        
        $_SESSION['user_id'] = $pdo->lastInsertId();
        $_SESSION['user_name'] = $nombre;
        echo json_encode(['success' => true, 'mensaje' => 'Cuenta creada con éxito']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'mensaje' => 'El email ya está registrado']);
    }
}