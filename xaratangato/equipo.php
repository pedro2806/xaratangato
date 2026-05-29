<?php
require_once 'includes/auth.php';
requireLogin();

$db = getDB();
$result = $db->query("SELECT * FROM equipo WHERE disponible=1 ORDER BY tipo, precio_hora ASC");
$equipos = $result->fetch_all(MYSQLI_ASSOC);

$mensaje = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reservar_equipo'])) {
    $modalidad = $_POST['modalidad'] ?? 'hora';
    $cant      = max(1, (int)($_POST['cantidad'] ?? 1));

    $stmt = $db->prepare("SELECT precio_hora, precio_dia FROM equipo WHERE id=?");
    $stmt->bind_param('i', $_POST['item_id']);
    $stmt->execute();
    $eq = $stmt->get_result()->fetch_assoc();

    $precio = ($modalidad === 'dia') ? $eq['precio_dia'] : $eq['precio_hora'];
    $horas  = ($modalidad === 'dia') ? 1 : max(1,(int)($_POST['horas']??1));
    $total  = $precio * $horas * $cant;

    $data = [
        'usuario_id'  => $_SESSION['user_id'],
        'tipo'        => 'equipo',
        'item_id'     => (int)$_POST['item_id'],
        'fecha_inicio'=> $_POST['fecha_inicio'],
        'fecha_fin'   => $_POST['fecha_inicio'],
        'hora_inicio' => $_POST['hora_inicio'] ?? null,
        'hora_fin'    => null,
        'cantidad'    => $cant,
        'total'       => $total,
        'notas'       => trim($_POST['notas'] ?? ''),
    ];
    $id = crearReserva($data);
    $mensaje = $id ? "success:¡Reserva #$id de equipo creada con éxito!" : "error:Error al crear la reserva.";
}

$emojis = ['bicicleta'=>'🚲','cuatrimoto'=>'🏍️','kayak'=>'🛶','otro'=>'🎒'];
$colores = ['bicicleta'=>'#2d6a4f','cuatrimoto'=>'#8b5e3c','kayak'=>'#1a6fa0','otro'=>'#555'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Equipo & Renta · Xaratangato</title>
<link rel="stylesheet" href="css/styles.css">
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<div class="hero" style="padding:50px 2rem 40px;background:linear-gradient(135deg,#1a3a2a,#1a6fa0,#52b788);">
  <div class="hero-badge">🚲 Renta de Equipo</div>
  <h1 class="hero-title" style="font-size:2.5rem;">Equipo & Aventura</h1>
  <p class="hero-subtitle">Renta bicicletas, cuatrimotos y kayaks para explorar cada rincón de Xaratangato.</p>
</div>

<div class="section-content">

  <?php if ($mensaje):
    [$tipo, $txt] = explode(':', $mensaje, 2);
  ?>
    <div class="alert alert-<?= $tipo === 'success' ? 'success' : 'error' ?>">
      <?= $tipo === 'success' ? '✅' : '⚠️' ?> <?= htmlspecialchars($txt) ?>
    </div>
  <?php endif; ?>

  <!-- Filtros por tipo -->
  <div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:2rem;">
    <button class="tab-btn active" onclick="filtrar('todos',this)">🎒 Todos</button>
    <button class="tab-btn" onclick="filtrar('bicicleta',this)">🚲 Bicicletas</button>
    <button class="tab-btn" onclick="filtrar('cuatrimoto',this)">🏍️ Cuatrimotos</button>
    <button class="tab-btn" onclick="filtrar('kayak',this)">🛶 Kayaks</button>
  </div>

  <div class="cards-grid" id="equipoGrid">
    <?php foreach ($equipos as $e):
      $emoji  = $emojis[$e['tipo']] ?? '🎒';
      $color  = $colores[$e['tipo']] ?? '#555';
    ?>
    <div class="card equipo-card" data-tipo="<?= $e['tipo'] ?>">
      <div class="card-img">
        <div class="img-placeholder" style="background:linear-gradient(135deg,#f0f9ff,#e0f2f1);">
          <?= $emoji ?>
        </div>
        <span class="card-badge" style="background:<?= $color ?>;"><?= ucfirst($e['tipo']) ?></span>
      </div>
      <div class="card-body">
        <h3 class="card-title"><?= htmlspecialchars($e['nombre']) ?></h3>
        <p class="card-desc"><?= htmlspecialchars($e['descripcion']) ?></p>

        <div class="card-meta">
          <div>
            <?php if ($e['precio_hora']): ?>
              <div class="card-price">$<?= number_format($e['precio_hora'],0) ?> <small>/ hora</small></div>
            <?php endif; ?>
            <?php if ($e['precio_dia']): ?>
              <div style="font-size:0.85rem;color:#888;margin-top:2px;">$<?= number_format($e['precio_dia'],0) ?> / día</div>
            <?php endif; ?>
          </div>
          <div class="card-info">
            <span class="tag">📦 <?= $e['cantidad_total'] ?> unidades</span>
          </div>
        </div>

        <button class="btn btn-primary"
          onclick="abrirModalEquipo(
            <?= $e['id'] ?>,
            '<?= addslashes($e['nombre']) ?>',
            <?= $e['precio_hora'] ?? 0 ?>,
            <?= $e['precio_dia']  ?? 0 ?>,
            <?= $e['cantidad_total'] ?>
          )"
          style="background:<?= $color ?>;">
          📅 Rentar ahora
        </button>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<!-- MODAL -->
<div class="modal-overlay" id="modalReserva">
  <div class="modal">
    <div class="modal-header" style="background:linear-gradient(135deg,#1a3a2a,#1a6fa0);">
      <div>
        <h3 id="modalTitulo">Rentar Equipo</h3>
        <p id="modalSubtitulo">Selecciona fecha y duración</p>
      </div>
      <button class="modal-close" onclick="cerrarModal()">✕</button>
    </div>
    <div class="modal-body">
      <form method="POST" action="equipo.php" onsubmit="return prepararForm()">
        <input type="hidden" name="reservar_equipo" value="1">
        <input type="hidden" name="item_id" id="itemId">
        <input type="hidden" name="modalidad" id="modalidadInput" value="hora">

        <div class="form-group">
          <label class="form-label">📅 Fecha</label>
          <input type="date" name="fecha_inicio" id="fechaInicio" class="form-control"
                 min="<?= date('Y-m-d') ?>" required>
        </div>

        <div class="form-group">
          <label class="form-label">🕐 Hora de recogida</label>
          <input type="time" name="hora_inicio" class="form-control" value="09:00">
        </div>

        <!-- Tabs modalidad -->
        <div style="display:flex;gap:8px;margin-bottom:1rem;">
          <button type="button" class="tab-btn active" id="tabHora" onclick="setModalidad('hora')">⏱️ Por hora</button>
          <button type="button" class="tab-btn" id="tabDia" onclick="setModalidad('dia')">📅 Por día</button>
        </div>

        <div id="grupoHoras" class="form-group">
          <label class="form-label">⏱️ Número de horas</label>
          <input type="number" name="horas" id="numHoras" class="form-control"
                 value="2" min="1" max="12" onchange="calcularTotalEquipo()">
        </div>

        <div class="form-group">
          <label class="form-label">🔢 Cantidad de equipos</label>
          <input type="number" name="cantidad" id="cantEquipo" class="form-control"
                 value="1" min="1" max="10" onchange="calcularTotalEquipo()">
        </div>

        <div class="form-group">
          <label class="form-label">📝 Notas</label>
          <textarea name="notas" class="form-control" rows="2"
                    placeholder="Talla de casco, nivel de experiencia..."></textarea>
        </div>

        <div class="price-summary">
          <div class="total-label" id="resumenDetalle">Cálculo de renta</div>
          <div class="total-value" id="resumenTotal">$0.00</div>
        </div>

        <button type="submit" class="btn btn-primary" style="width:100%;padding:13px;margin-top:8px;">
          ✅ Confirmar Renta
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
let phora = 0, pdia = 0, modalidad = 'hora';

function filtrar(tipo, btn) {
  document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  document.querySelectorAll('.equipo-card').forEach(c => {
    c.style.display = (tipo === 'todos' || c.dataset.tipo === tipo) ? '' : 'none';
  });
}

function abrirModalEquipo(id, nombre, precioH, precioD, max) {
  phora = precioH; pdia = precioD;
  document.getElementById('itemId').value = id;
  document.getElementById('modalTitulo').textContent = nombre;
  document.getElementById('cantEquipo').max = max;
  setModalidad('hora');
  calcularTotalEquipo();
  document.getElementById('modalReserva').classList.add('open');
}

function setModalidad(m) {
  modalidad = m;
  document.getElementById('modalidadInput').value = m;
  document.getElementById('tabHora').classList.toggle('active', m === 'hora');
  document.getElementById('tabDia').classList.toggle('active', m === 'dia');
  document.getElementById('grupoHoras').style.display = m === 'hora' ? '' : 'none';
  calcularTotalEquipo();
}

function calcularTotalEquipo() {
  const cant  = parseInt(document.getElementById('cantEquipo').value) || 1;
  const horas = parseInt(document.getElementById('numHoras').value)  || 1;
  let total = 0, detalle = '';
  if (modalidad === 'hora') {
    total   = phora * horas * cant;
    detalle = `${cant} equipo(s) × ${horas}h × $${phora.toFixed(2)}/h`;
  } else {
    total   = pdia * cant;
    detalle = `${cant} equipo(s) × $${pdia.toFixed(2)}/día`;
  }
  document.getElementById('resumenDetalle').textContent = detalle;
  document.getElementById('resumenTotal').textContent   = '$' + total.toLocaleString('es-MX',{minimumFractionDigits:2});
}

function prepararForm() {
  calcularTotalEquipo();
  return true;
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
