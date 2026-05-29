<?php
// includes/navbar.php
if (!function_exists('isLoggedIn')) require_once __DIR__ . '/auth.php';
$current = basename($_SERVER['PHP_SELF']);
?>
<nav class="navbar">
  <a href="<?= APP_URL ?>/index.php" class="navbar-brand">
    <span class="brand-icon">🏔️</span> Xaratangato
  </a>

  <button class="nav-toggle" onclick="document.querySelector('.nav-links').classList.toggle('open')" aria-label="Menú">
    <span></span><span></span><span></span>
  </button>

  <ul class="nav-links" id="navLinks">
    <?php if (isLoggedIn()): ?>
      <li><a href="index.php" class="<?= $current==='index.php'?'active':'' ?>">🏠 Inicio</a></li>
      <li><a href="habitaciones.php" class="<?= $current==='habitaciones.php'?'active':'' ?>">🛏️ Habitaciones</a></li>
      <li><a href="actividades.php" class="<?= $current==='actividades.php'?'active':'' ?>">⛰️ Actividades</a></li>
      <li><a href="equipo.php" class="<?= $current==='equipo.php'?'active':'' ?>">🚲 Equipo</a></li>
      <li><a href="mis-reservas.php" class="<?= $current==='mis-reservas.php'?'active':'' ?>">📋 Mis Reservas</a></li>
      <?php if (isAdmin()): ?>
        <li><a href="admin.php"    class="<?= $current==='admin.php'   ?'active':'' ?>">📊 Reservas</a></li>
        <li><a href="catalogo.php" class="<?= $current==='catalogo.php'?'active':'' ?>">🗂 Catálogo</a></li>
      <?php endif; ?>
      <li>
        <div class="user-badge">
          <div class="avatar"><?= strtoupper(substr($_SESSION['nombre'],0,1)) ?></div>
          <span><?= htmlspecialchars($_SESSION['nombre']) ?></span>
        </div>
      </li>
      <li><a href="logout.php">🚪 Salir</a></li>
    <?php else: ?>
      <li><a href="login.php">Iniciar sesión</a></li>
      <li><a href="registro.php" class="btn-reservar">Registro</a></li>
    <?php endif; ?>
  </ul>
</nav>
