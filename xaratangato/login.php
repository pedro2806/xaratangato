<?php
require_once 'includes/auth.php';
if (isLoggedIn()) { header('Location: ' . APP_URL . '/index.php'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    if (login($email, $password)) {
        header('Location: ' . APP_URL . '/index.php');
        exit;
    } else {
        $error = 'Correo o contraseña incorrectos.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Iniciar Sesión · Xaratangato</title>
<link rel="stylesheet" href="css/styles.css">
</head>
<body>
<div class="auth-page">
  <div class="auth-card">
    <div class="auth-header">
      <div class="auth-logo">🏔️</div>
      <h1>Xaratangato</h1>
      <p>Inicia sesión para reservar tu aventura</p>
    </div>
    <div class="auth-body">
      <?php if ($error): ?>
        <div class="alert alert-error">⚠️ <?= htmlspecialchars($error) ?></div>
      <?php endif; ?>
      <form method="POST" action="">
        <div class="form-group">
          <label class="form-label">📧 Correo electrónico</label>
          <input type="email" name="email" class="form-control"
                 placeholder="tu@correo.com"
                 value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
        </div>
        <div class="form-group">
          <label class="form-label">🔒 Contraseña</label>
          <input type="password" name="password" class="form-control"
                 placeholder="••••••••" required>
        </div>
        <button type="submit" class="btn btn-primary" style="width:100%;margin-top:8px;padding:13px;">
          Entrar al paraíso 🌿
        </button>
      </form>
    </div>
    <div class="auth-footer">
      ¿No tienes cuenta? <a href="registro.php">Regístrate gratis</a>
      <br><br>
      <small style="color:#bbb;">
        Demo admin: admin@xaratangato.com / admin123
      </small>
    </div>
  </div>
</div>
</body>
</html>
