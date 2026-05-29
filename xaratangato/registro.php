<?php
require_once 'includes/auth.php';
if (isLoggedIn()) { header('Location: ' . APP_URL . '/index.php'); exit; }

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre   = trim($_POST['nombre']   ?? '');
    $apellido = trim($_POST['apellido'] ?? '');
    $email    = trim($_POST['email']    ?? '');
    $tel      = trim($_POST['telefono'] ?? '');
    $pass     = $_POST['password']      ?? '';
    $pass2    = $_POST['password2']     ?? '';

    if (!$nombre || !$apellido || !$email || !$pass) {
        $error = 'Todos los campos son obligatorios.';
    } elseif ($pass !== $pass2) {
        $error = 'Las contraseñas no coinciden.';
    } elseif (strlen($pass) < 6) {
        $error = 'La contraseña debe tener al menos 6 caracteres.';
    } else {
        if (register($nombre, $apellido, $email, $pass, $tel)) {
            login($email, $pass);
            header('Location: ' . APP_URL . '/index.php?welcome=1');
            exit;
        } else {
            $error = 'El correo ya está registrado.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Registro · Xaratangato</title>
<link rel="stylesheet" href="css/styles.css">
</head>
<body>
<div class="auth-page">
  <div class="auth-card">
    <div class="auth-header">
      <div class="auth-logo">🏕️</div>
      <h1>Xaratangato</h1>
      <p>Crea tu cuenta y comienza la aventura</p>
    </div>
    <div class="auth-body">
      <?php if ($error): ?>
        <div class="alert alert-error">⚠️ <?= htmlspecialchars($error) ?></div>
      <?php endif; ?>
      <form method="POST" action="">
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Nombre</label>
            <input type="text" name="nombre" class="form-control"
                   placeholder="Juan" value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>" required>
          </div>
          <div class="form-group">
            <label class="form-label">Apellido</label>
            <input type="text" name="apellido" class="form-control"
                   placeholder="Pérez" value="<?= htmlspecialchars($_POST['apellido'] ?? '') ?>" required>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">📧 Correo electrónico</label>
          <input type="email" name="email" class="form-control"
                 placeholder="tu@correo.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
        </div>
        <div class="form-group">
          <label class="form-label">📱 Teléfono (opcional)</label>
          <input type="tel" name="telefono" class="form-control"
                 placeholder="442 000 0000" value="<?= htmlspecialchars($_POST['telefono'] ?? '') ?>">
        </div>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">🔒 Contraseña</label>
            <input type="password" name="password" class="form-control" placeholder="••••••" required>
          </div>
          <div class="form-group">
            <label class="form-label">🔒 Confirmar</label>
            <input type="password" name="password2" class="form-control" placeholder="••••••" required>
          </div>
        </div>
        <button type="submit" class="btn btn-primary" style="width:100%;margin-top:8px;padding:13px;">
          Crear mi cuenta 🚀
        </button>
      </form>
    </div>
    <div class="auth-footer">
      ¿Ya tienes cuenta? <a href="login.php">Inicia sesión</a>
    </div>
  </div>
</div>
</body>
</html>
