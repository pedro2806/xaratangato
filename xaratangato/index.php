<?php
require_once 'includes/auth.php';
requireLogin();

$db = getDB();
$welcome = isset($_GET['welcome']);

// Estadísticas del usuario
$uid   = $_SESSION['user_id'];
$stats = [];
$r = $db->query("SELECT COUNT(*) c FROM reservas WHERE usuario_id=$uid");
$stats['total'] = $r->fetch_row()[0];
$r = $db->query("SELECT COUNT(*) c FROM reservas WHERE usuario_id=$uid AND estado='pendiente'");
$stats['pendientes'] = $r->fetch_row()[0];
$r = $db->query("SELECT COUNT(*) c FROM reservas WHERE usuario_id=$uid AND estado='confirmada'");
$stats['confirmadas'] = $r->fetch_row()[0];
$r = $db->query("SELECT SUM(total) t FROM reservas WHERE usuario_id=$uid AND estado!='cancelada'");
$stats['gasto'] = $r->fetch_row()[0] ?? 0;

// Próximas reservas
$stmt = $db->prepare("SELECT * FROM reservas WHERE usuario_id=? AND fecha_inicio >= CURDATE() ORDER BY fecha_inicio ASC LIMIT 3");
$stmt->bind_param('i', $uid);
$stmt->execute();
$proximas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Inicio · Xaratangato</title>
<link rel="stylesheet" href="css/styles.css">
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<div class="hero">
  <div class="hero-badge">🌿 Parque Natural · Querétaro</div>
  <h1 class="hero-title">Bienvenido, <?= htmlspecialchars($_SESSION['nombre']) ?> 👋</h1>
  <p class="hero-subtitle">Descubre la magia de la naturaleza. Reserva tu estancia, elige una actividad y vive la aventura en Xaratangato.</p>
</div>

<?php if ($welcome): ?>
<div style="max-width:800px;margin:1.5rem auto;padding:0 1.5rem;">
  <div class="alert alert-success">🎉 ¡Bienvenido a Xaratangato! Tu cuenta fue creada exitosamente.</div>
</div>
<?php endif; ?>

<div class="section-content">

  <!-- Estadísticas -->
  <div class="stats-row">
    <div class="stat-card">
      <div class="stat-icon">📋</div>
      <div class="stat-value"><?= $stats['total'] ?></div>
      <div class="stat-label">Total Reservas</div>
    </div>
    <div class="stat-card">
      <div class="stat-icon">⏳</div>
      <div class="stat-value"><?= $stats['pendientes'] ?></div>
      <div class="stat-label">Pendientes</div>
    </div>
    <div class="stat-card">
      <div class="stat-icon">✅</div>
      <div class="stat-value"><?= $stats['confirmadas'] ?></div>
      <div class="stat-label">Confirmadas</div>
    </div>
    <div class="stat-card">
      <div class="stat-icon">💰</div>
      <div class="stat-value">$<?= number_format($stats['gasto'],0) ?></div>
      <div class="stat-label">Total gastado</div>
    </div>
  </div>

  <!-- Accesos rápidos -->
  <h2 class="section-title">¿Qué deseas reservar?</h2>
  <p class="section-desc">Elige una categoría para ver disponibilidad y hacer tu reservación.</p>

  <div class="cards-grid" style="margin-bottom:3rem;">
    <a href="habitaciones.php" style="text-decoration:none;">
      <div class="card" style="border-top:4px solid #2d6a4f;">
        <div class="card-img">
          <div class="img-placeholder">🛏️</div>
        </div>
        <div class="card-body">
          <h3 class="card-title">Habitaciones</h3>
          <p class="card-desc">Cabañas, glamping, suites y más. Descansa en medio de la naturaleza con todo el confort.</p>
          <span class="btn btn-primary">Ver habitaciones →</span>
        </div>
      </div>
    </a>
    <a href="actividades.php" style="text-decoration:none;">
      <div class="card" style="border-top:4px solid #d4a017;">
        <div class="card-img">
          <div class="img-placeholder">⛰️</div>
        </div>
        <div class="card-body">
          <h3 class="card-title">Actividades</h3>
          <p class="card-desc">Senderismo, tirolesa, taller de cocina, astronomía y mucho más para tu diversión.</p>
          <span class="btn btn-gold">Ver actividades →</span>
        </div>
      </div>
    </a>
    <a href="equipo.php" style="text-decoration:none;">
      <div class="card" style="border-top:4px solid #52b788;">
        <div class="card-img">
          <div class="img-placeholder">🚲</div>
        </div>
        <div class="card-body">
          <h3 class="card-title">Equipo & Renta</h3>
          <p class="card-desc">Bicicletas de montaña, cuatrimotos y kayaks para explorar el parque a tu ritmo.</p>
          <span class="btn btn-outline">Ver equipo →</span>
        </div>
      </div>
    </a>
  </div>

  <!-- Próximas reservas -->
  <h2 class="section-title">Próximas Reservas</h2>
  <p class="section-desc">Tus reservaciones que están por venir.</p>

  <?php if (empty($proximas)): ?>
    <div class="empty-state">
      <div class="empty-icon">📅</div>
      <h3>Sin reservas próximas</h3>
      <p>¡Haz tu primera reserva y comienza la aventura!</p>
      <br>
      <a href="habitaciones.php" class="btn btn-primary" style="display:inline-flex;">Reservar ahora</a>
    </div>
  <?php else: ?>
    <div class="reservas-list">
      <?php
      $iconos = ['habitacion'=>'🛏️','actividad'=>'⛰️','equipo'=>'🚲'];
      foreach ($proximas as $r):
        $icon = $iconos[$r['tipo']] ?? '📋';
      ?>
      <div class="reserva-card <?= $r['estado'] ?>">
        <div class="reserva-icon"><?= $icon ?></div>
        <div class="reserva-info">
          <h4><?= ucfirst($r['tipo']) ?> · ID #<?= $r['id'] ?></h4>
          <p>📅 <?= date('d/m/Y', strtotime($r['fecha_inicio'])) ?>
             <?= $r['fecha_fin'] && $r['fecha_fin'] !== $r['fecha_inicio'] ? ' → ' . date('d/m/Y', strtotime($r['fecha_fin'])) : '' ?>
             · Cantidad: <?= $r['cantidad'] ?></p>
        </div>
        <div class="reserva-meta">
          <div class="reserva-total">$<?= number_format($r['total'],2) ?></div>
          <div><span class="status-badge status-<?= $r['estado'] ?>"><?= $r['estado'] ?></span></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <div style="text-align:center;margin-top:1.5rem;">
      <a href="mis-reservas.php" class="btn btn-outline">Ver todas mis reservas</a>
    </div>
  <?php endif; ?>
</div>

<footer>
  <strong>Xaratangato</strong> · Parque Natural · Querétaro, México<br>
  <small>© <?= date('Y') ?> Todos los derechos reservados</small>
</footer>

</body>
</html>
