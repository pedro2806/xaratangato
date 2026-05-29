<?php
require_once 'includes/auth.php';
requireLogin();

$db  = getDB();
$uid = $_SESSION['user_id'];

// Cancelar reserva
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancelar_id'])) {
    cancelarReserva((int)$_POST['cancelar_id'], $uid);
    header('Location: mis-reservas.php?msg=cancelada');
    exit;
}

$msg = $_GET['msg'] ?? '';

// Filtro de estado
$filtro = $_GET['estado'] ?? 'todas';
$where  = "WHERE r.usuario_id = $uid";
if ($filtro !== 'todas') $where .= " AND r.estado = '$filtro'";

$reservas = $db->query("
    SELECT r.*,
      CASE r.tipo
        WHEN 'habitacion' THEN h.nombre
        WHEN 'actividad'  THEN a.nombre
        WHEN 'equipo'     THEN e.nombre
      END AS item_nombre
    FROM reservas r
    LEFT JOIN habitaciones h ON r.tipo='habitacion' AND r.item_id=h.id
    LEFT JOIN actividades  a ON r.tipo='actividad'  AND r.item_id=a.id
    LEFT JOIN equipo       e ON r.tipo='equipo'     AND r.item_id=e.id
    $where
    ORDER BY r.created_at DESC
")->fetch_all(MYSQLI_ASSOC);

$iconos = ['habitacion'=>'🛏️','actividad'=>'⛰️','equipo'=>'🚲'];

// Totales
$totalesR = $db->query("SELECT estado, COUNT(*) c, SUM(total) t FROM reservas WHERE usuario_id=$uid GROUP BY estado")->fetch_all(MYSQLI_ASSOC);
$totales  = [];
foreach ($totalesR as $row) $totales[$row['estado']] = $row;
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Mis Reservas · Xaratangato</title>
<link rel="stylesheet" href="css/styles.css">
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<div class="hero" style="padding:50px 2rem 40px;background:linear-gradient(135deg,#1a3a2a,#2d6a4f);">
  <div class="hero-badge">📋 Mi Cuenta</div>
  <h1 class="hero-title" style="font-size:2.5rem;">Mis Reservas</h1>
  <p class="hero-subtitle">Consulta y gestiona todas tus reservaciones en Xaratangato.</p>
</div>

<div class="section-content">

  <?php if ($msg === 'cancelada'): ?>
    <div class="alert alert-success">✅ Reserva cancelada correctamente.</div>
  <?php endif; ?>

  <!-- Resumen -->
  <div class="stats-row" style="margin-bottom:2rem;">
    <div class="stat-card">
      <div class="stat-icon">⏳</div>
      <div class="stat-value"><?= $totales['pendiente']['c'] ?? 0 ?></div>
      <div class="stat-label">Pendientes</div>
    </div>
    <div class="stat-card">
      <div class="stat-icon">✅</div>
      <div class="stat-value"><?= $totales['confirmada']['c'] ?? 0 ?></div>
      <div class="stat-label">Confirmadas</div>
    </div>
    <div class="stat-card">
      <div class="stat-icon">🏁</div>
      <div class="stat-value"><?= $totales['completada']['c'] ?? 0 ?></div>
      <div class="stat-label">Completadas</div>
    </div>
    <div class="stat-card">
      <div class="stat-icon">❌</div>
      <div class="stat-value"><?= $totales['cancelada']['c'] ?? 0 ?></div>
      <div class="stat-label">Canceladas</div>
    </div>
  </div>

  <!-- Filtros -->
  <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:1.5rem;">
    <?php
    $estados = ['todas'=>'🗂 Todas','pendiente'=>'⏳ Pendientes','confirmada'=>'✅ Confirmadas','completada'=>'🏁 Completadas','cancelada'=>'❌ Canceladas'];
    foreach ($estados as $key => $label):
    ?>
    <a href="?estado=<?= $key ?>"
       class="tab-btn <?= $filtro === $key ? 'active' : '' ?>">
      <?= $label ?>
    </a>
    <?php endforeach; ?>
  </div>

  <!-- Lista -->
  <?php if (empty($reservas)): ?>
    <div class="empty-state">
      <div class="empty-icon">📭</div>
      <h3>Sin reservas<?= $filtro !== 'todas' ? ' ' . $filtro . 's' : '' ?></h3>
      <p>¡Haz tu primera reserva y disfruta de Xaratangato!</p>
      <br>
      <a href="habitaciones.php" class="btn btn-primary" style="display:inline-flex;">Reservar ahora</a>
    </div>
  <?php else: ?>
    <div class="reservas-list">
      <?php foreach ($reservas as $r):
        $icon = $iconos[$r['tipo']] ?? '📋';
        $nombre_item = $r['item_nombre'] ?? 'Ítem #' . $r['item_id'];
      ?>
      <div class="reserva-card <?= $r['estado'] ?>">
        <div class="reserva-icon"><?= $icon ?></div>

        <div class="reserva-info" style="flex:1;">
          <h4><?= htmlspecialchars($nombre_item) ?></h4>
          <p style="margin-bottom:3px;">
            📅 <?= date('d/m/Y', strtotime($r['fecha_inicio'])) ?>
            <?php if ($r['fecha_fin'] && $r['fecha_fin'] !== $r['fecha_inicio']): ?>
              → <?= date('d/m/Y', strtotime($r['fecha_fin'])) ?>
            <?php endif; ?>
            <?php if ($r['hora_inicio']): ?>
              · 🕐 <?= substr($r['hora_inicio'],0,5) ?>
            <?php endif; ?>
          </p>
          <p>🔢 Cantidad: <?= $r['cantidad'] ?>
             · 📁 <?= ucfirst($r['tipo']) ?>
             <?php if ($r['notas']): ?>
               · 📝 <?= htmlspecialchars(substr($r['notas'],0,40)) ?>...
             <?php endif; ?>
          </p>
          <p style="font-size:0.78rem;color:#bbb;margin-top:3px;">
            Reservado el <?= date('d/m/Y H:i', strtotime($r['created_at'])) ?>
          </p>
        </div>

        <div class="reserva-meta" style="text-align:right;min-width:130px;">
          <div class="reserva-total">$<?= number_format($r['total'],2) ?></div>
          <span class="status-badge status-<?= $r['estado'] ?>"><?= $r['estado'] ?></span>
          <br>
          <?php if (in_array($r['estado'], ['pendiente','confirmada'])): ?>
            <form method="POST" style="margin-top:8px;"
              onsubmit="return confirm('¿Cancelar esta reserva?')">
              <input type="hidden" name="cancelar_id" value="<?= $r['id'] ?>">
              <button type="submit" class="btn btn-danger btn-sm">✕ Cancelar</button>
            </form>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <div style="text-align:center;margin-top:2.5rem;display:flex;gap:12px;justify-content:center;flex-wrap:wrap;">
    <a href="habitaciones.php" class="btn btn-primary">🛏️ Reservar habitación</a>
    <a href="actividades.php"  class="btn btn-gold"    style="color:#1a1a1a;">⛰️ Reservar actividad</a>
    <a href="equipo.php"       class="btn btn-outline">🚲 Rentar equipo</a>
  </div>
</div>

<footer>
  <strong>Xaratangato</strong> · Parque Natural · Querétaro, México<br>
  <small>© <?= date('Y') ?> Todos los derechos reservados</small>
</footer>

</body>
</html>
