<?php
require_once 'includes/auth.php';
requireLogin();

$db = getDB();
$result = $db->query("SELECT * FROM actividades WHERE disponible=1 ORDER BY precio ASC");
$actividades = $result->fetch_all(MYSQLI_ASSOC);

$mensaje = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reservar_act'])) {
    $personas = max(1, (int)($_POST['cantidad'] ?? 1));
    $stmt = $db->prepare("SELECT precio FROM actividades WHERE id=?");
    $stmt->bind_param('i', $_POST['item_id']);
    $stmt->execute();
    $act = $stmt->get_result()->fetch_assoc();
    $total = $act['precio'] * $personas;

    $data = [
        'usuario_id'  => $_SESSION['user_id'],
        'tipo'        => 'actividad',
        'item_id'     => (int)$_POST['item_id'],
        'fecha_inicio'=> $_POST['fecha_inicio'],
        'fecha_fin'   => $_POST['fecha_inicio'],
        'hora_inicio' => $_POST['hora_inicio'] ?? null,
        'hora_fin'    => null,
        'cantidad'    => $personas,
        'total'       => $total,
        'notas'       => trim($_POST['notas'] ?? ''),
    ];
    $id = crearReserva($data);
    $mensaje = $id ? "success:¡Reserva #$id de actividad creada!" : "error:Error al crear la reserva.";
}

$emojis = [
    'Senderismo'  => '🥾',
    'Tirolesa'    => '🎿',
    'Taller'      => '👨‍🍳',
    'Noche'       => '🌌',
    'Tour'        => '🚙',
    'Pesca'       => '🎣',
    'Yoga'        => '🧘',
];

function getEmoji($nombre, $emojis) {
    foreach ($emojis as $key => $emoji) {
        if (stripos($nombre, $key) !== false) return $emoji;
    }
    return '⛰️';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Actividades · Xaratangato</title>
<link rel="stylesheet" href="css/styles.css">
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<div class="hero" style="padding:50px 2rem 40px;background:linear-gradient(135deg,#1a3a2a,#8b5e3c,#d4a017);">
  <div class="hero-badge">⛰️ Aventura</div>
  <h1 class="hero-title" style="font-size:2.5rem;">Actividades & Experiencias</h1>
  <p class="hero-subtitle">Adrenalina, cultura, naturaleza y mucho más. Vive momentos únicos en Xaratangato.</p>
</div>

<div class="section-content">

  <?php if ($mensaje):
    [$tipo, $txt] = explode(':', $mensaje, 2);
  ?>
    <div class="alert alert-<?= $tipo === 'success' ? 'success' : 'error' ?>">
      <?= $tipo === 'success' ? '✅' : '⚠️' ?> <?= htmlspecialchars($txt) ?>
    </div>
  <?php endif; ?>

  <h2 class="section-title">Actividades disponibles</h2>
  <p class="section-desc"><?= count($actividades) ?> experiencias · Precios por persona (MXN)</p>

  <div class="cards-grid">
    <?php foreach ($actividades as $a):
      $emoji = getEmoji($a['nombre'], $emojis);
    ?>
    <div class="card">
      <div class="card-img">
        <div class="img-placeholder" style="background:linear-gradient(135deg,#fef9ee,#fdf0c8);"><?= $emoji ?></div>
        <span class="card-badge" style="background:var(--dorado);color:#1a1a1a;">⏱ <?= htmlspecialchars($a['duracion']) ?></span>
      </div>
      <div class="card-body">
        <h3 class="card-title"><?= htmlspecialchars($a['nombre']) ?></h3>
        <p class="card-desc"><?= htmlspecialchars($a['descripcion']) ?></p>
        <div class="card-meta">
          <div class="card-price">$<?= number_format($a['precio'],0) ?> <small>/ persona</small></div>
          <div class="card-info">
            <span class="tag">👥 Máx <?= $a['capacidad_max'] ?></span>
          </div>
        </div>
        <button class="btn btn-gold"
          onclick="abrirModalActividad(<?= $a['id'] ?>,'<?= addslashes($a['nombre']) ?>',<?= $a['precio'] ?>,<?= $a['capacidad_max'] ?>)"
          style="width:100%;">
          📅 Reservar actividad
        </button>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<!-- MODAL -->
<div class="modal-overlay" id="modalReserva">
  <div class="modal">
    <div class="modal-header" style="background:linear-gradient(135deg,#8b5e3c,#d4a017);">
      <div>
        <h3 id="modalTitulo">Reservar Actividad</h3>
        <p id="modalSubtitulo"></p>
      </div>
      <button class="modal-close" onclick="cerrarModal()">✕</button>
    </div>
    <div class="modal-body">
      <form method="POST" action="actividades.php">
        <input type="hidden" name="reservar_act" value="1">
        <input type="hidden" name="item_id" id="itemId">

        <div class="form-group">
          <label class="form-label">📅 Fecha de la actividad</label>
          <input type="date" name="fecha_inicio" id="fechaInicio" class="form-control"
                 min="<?= date('Y-m-d') ?>" required>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">🕐 Hora inicio</label>
            <input type="time" name="hora_inicio" class="form-control" value="09:00">
          </div>
          <div class="form-group">
            <label class="form-label">👥 Personas</label>
            <input type="number" name="cantidad" id="cantPersonas" class="form-control"
                   value="1" min="1" max="20" onchange="calcularTotalAct()">
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">📝 Notas</label>
          <textarea name="notas" class="form-control" rows="2"
                    placeholder="Nivel de experiencia, restricciones..."></textarea>
        </div>
        <div class="price-summary">
          <div class="total-label" id="resumenDetalle">Precio por persona</div>
          <div class="total-value" id="resumenTotal">$0.00</div>
        </div>
        <button type="submit" class="btn btn-gold" style="width:100%;padding:13px;margin-top:8px;color:#1a1a1a;">
          ✅ Confirmar Reserva
        </button>
      </form>
    </div>
  </div>
</div>

<footer>
  <strong>Xaratangato</strong> · Parque Natural · Querétaro, México<br>
  <small>© <?= date('Y') ?> Todos los derechos reservados</small>
</footer>

<script>
let precioActual = 0;
let maxPersonas  = 20;

function abrirModalActividad(id, nombre, precio, maxP) {
  precioActual = precio;
  maxPersonas  = maxP;
  document.getElementById('itemId').value = id;
  document.getElementById('modalTitulo').textContent = nombre;
  document.getElementById('modalSubtitulo').textContent = '$' + precio.toFixed(2) + ' por persona';
  document.getElementById('cantPersonas').max = maxP;
  calcularTotalAct();
  document.getElementById('modalReserva').classList.add('open');
}

function calcularTotalAct() {
  const cant = parseInt(document.getElementById('cantPersonas').value) || 1;
  const total = precioActual * cant;
  document.getElementById('resumenDetalle').textContent = cant + ' persona(s) × $' + precioActual.toFixed(2);
  document.getElementById('resumenTotal').textContent = '$' + total.toLocaleString('es-MX', {minimumFractionDigits:2});
}

function cerrarModal() {
  document.getElementById('modalReserva').classList.remove('open');
}

document.getElementById('modalReserva').addEventListener('click', function(e) {
  if (e.target === this) cerrarModal();
});
</script>
</body>
</html>
