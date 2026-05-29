<?php
require_once 'includes/auth.php';
requireLogin();
if (!isAdmin()) { header('Location: ' . APP_URL . '/index.php'); exit; }

$db = getDB();

// Acciones rápidas de admin
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['cambiar_estado'])) {
        $stmt = $db->prepare("UPDATE reservas SET estado=? WHERE id=?");
        $stmt->bind_param('si', $_POST['nuevo_estado'], $_POST['reserva_id']);
        $stmt->execute();
        header('Location: admin.php?ok=1');
        exit;
    }
}

// Estadísticas globales
$stats = [];
$stats['usuarios']    = $db->query("SELECT COUNT(*) FROM usuarios WHERE rol='cliente'")->fetch_row()[0];
$stats['reservas']    = $db->query("SELECT COUNT(*) FROM reservas")->fetch_row()[0];
$stats['pendientes']  = $db->query("SELECT COUNT(*) FROM reservas WHERE estado='pendiente'")->fetch_row()[0];
$stats['ingresos']    = $db->query("SELECT SUM(total) FROM reservas WHERE estado IN ('confirmada','completada')")->fetch_row()[0] ?? 0;

// Todas las reservas con info
$reservas = $db->query("
    SELECT r.*,
      u.nombre, u.apellido, u.email,
      CASE r.tipo
        WHEN 'habitacion' THEN h.nombre
        WHEN 'actividad'  THEN a.nombre
        WHEN 'equipo'     THEN e.nombre
      END AS item_nombre
    FROM reservas r
    JOIN usuarios u ON r.usuario_id = u.id
    LEFT JOIN habitaciones h ON r.tipo='habitacion' AND r.item_id=h.id
    LEFT JOIN actividades  a ON r.tipo='actividad'  AND r.item_id=a.id
    LEFT JOIN equipo       e ON r.tipo='equipo'     AND r.item_id=e.id
    ORDER BY r.created_at DESC
    LIMIT 100
")->fetch_all(MYSQLI_ASSOC);

$iconos = ['habitacion'=>'🛏️','actividad'=>'⛰️','equipo'=>'🚲'];
$ok = isset($_GET['ok']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Panel Admin · Xaratangato</title>
<link rel="stylesheet" href="css/styles.css">
<style>
  .admin-table { width:100%; border-collapse:collapse; background:white; border-radius:14px; overflow:hidden; box-shadow:0 2px 15px var(--sombra); }
  .admin-table th { background:var(--verde-oscuro); color:white; padding:12px 14px; text-align:left; font-size:0.82rem; font-weight:700; letter-spacing:0.5px; text-transform:uppercase; }
  .admin-table td { padding:11px 14px; border-bottom:1px solid #f0ebe5; font-size:0.88rem; vertical-align:middle; }
  .admin-table tr:last-child td { border-bottom:none; }
  .admin-table tr:hover td { background:#fafaf8; }
  .table-wrap { overflow-x:auto; }
  select.estado-select { padding:5px 10px; border:2px solid #ddd; border-radius:20px; font-family:'Nunito',sans-serif; font-size:0.8rem; font-weight:700; cursor:pointer; background:white; }
</style>
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<div class="hero" style="padding:50px 2rem 40px;background:linear-gradient(135deg,#1a1a2e,#16213e,#0f3460);">
  <div class="hero-badge">⚙️ Administración</div>
  <h1 class="hero-title" style="font-size:2.5rem;">Panel de Administración</h1>
  <p class="hero-subtitle">Gestiona reservas, usuarios y todo el parque desde aquí.</p>
</div>

<div class="section-content">

  <?php if ($ok): ?>
    <div class="alert alert-success">✅ Estado actualizado correctamente.</div>
  <?php endif; ?>

  <!-- KPIs -->
  <div class="stats-row" style="margin-bottom:2.5rem;">
    <div class="stat-card">
      <div class="stat-icon">👥</div>
      <div class="stat-value"><?= $stats['usuarios'] ?></div>
      <div class="stat-label">Clientes registrados</div>
    </div>
    <div class="stat-card">
      <div class="stat-icon">📋</div>
      <div class="stat-value"><?= $stats['reservas'] ?></div>
      <div class="stat-label">Total reservas</div>
    </div>
    <div class="stat-card">
      <div class="stat-icon">⏳</div>
      <div class="stat-value"><?= $stats['pendientes'] ?></div>
      <div class="stat-label">Pendientes de revisar</div>
    </div>
    <div class="stat-card">
      <div class="stat-icon">💵</div>
      <div class="stat-value">$<?= number_format($stats['ingresos'],0) ?></div>
      <div class="stat-label">Ingresos confirmados</div>
    </div>
  </div>

  <h2 class="section-title" style="margin-bottom:1.2rem;">📋 Todas las Reservas</h2>

  <?php if (empty($reservas)): ?>
    <div class="empty-state">
      <div class="empty-icon">📭</div>
      <h3>Sin reservas registradas</h3>
    </div>
  <?php else: ?>
    <div class="table-wrap">
      <table class="admin-table">
        <thead>
          <tr>
            <th>#ID</th>
            <th>Cliente</th>
            <th>Tipo</th>
            <th>Servicio</th>
            <th>Fecha</th>
            <th>Total</th>
            <th>Estado</th>
            <th>Acción</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($reservas as $r):
            $icon = $iconos[$r['tipo']] ?? '📋';
          ?>
          <tr>
            <td><strong>#<?= $r['id'] ?></strong></td>
            <td>
              <div style="font-weight:700;"><?= htmlspecialchars($r['nombre'].' '.$r['apellido']) ?></div>
              <div style="font-size:0.78rem;color:#999;"><?= htmlspecialchars($r['email']) ?></div>
            </td>
            <td><?= $icon ?> <?= ucfirst($r['tipo']) ?></td>
            <td><?= htmlspecialchars($r['item_nombre'] ?? '—') ?></td>
            <td>
              <?= date('d/m/Y', strtotime($r['fecha_inicio'])) ?>
              <?php if ($r['fecha_fin'] && $r['fecha_fin'] !== $r['fecha_inicio']): ?>
                <br><small>→ <?= date('d/m/Y', strtotime($r['fecha_fin'])) ?></small>
              <?php endif; ?>
            </td>
            <td><strong>$<?= number_format($r['total'],2) ?></strong></td>
            <td><span class="status-badge status-<?= $r['estado'] ?>"><?= $r['estado'] ?></span></td>
            <td>
              <form method="POST" style="display:flex;gap:6px;align-items:center;">
                <input type="hidden" name="cambiar_estado" value="1">
                <input type="hidden" name="reserva_id" value="<?= $r['id'] ?>">
                <select name="nuevo_estado" class="estado-select">
                  <?php foreach (['pendiente','confirmada','completada','cancelada'] as $est): ?>
                    <option value="<?= $est ?>" <?= $r['estado']===$est?'selected':'' ?>><?= ucfirst($est) ?></option>
                  <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-primary btn-sm">Guardar</button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<footer>
  <strong>Xaratangato</strong> · Panel de Administración<br>
  <small>© <?= date('Y') ?> Todos los derechos reservados</small>
</footer>

</body>
</html>
