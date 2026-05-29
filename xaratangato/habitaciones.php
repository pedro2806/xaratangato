<?php
require_once 'includes/auth.php';
requireLogin();

$db = getDB();
$result = $db->query("SELECT * FROM habitaciones WHERE disponible=1 ORDER BY precio_noche ASC");
$habitaciones = $result->fetch_all(MYSQLI_ASSOC);

$mensaje = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reservar_hab'])) {
    $data = [
        'usuario_id'   => $_SESSION['user_id'],
        'tipo'         => 'habitacion',
        'item_id'      => (int)$_POST['item_id'],
        'fecha_inicio' => $_POST['fecha_inicio'],
        'fecha_fin'    => $_POST['fecha_fin'],
        'hora_inicio'  => null,
        'hora_fin'     => null,
        'cantidad'     => 1,
        'total'        => (float)$_POST['total'],
        'notas'        => trim($_POST['notas'] ?? ''),
    ];
    $id = crearReserva($data);
    if ($id) {
        $mensaje = "success:¡Reserva #$id creada! Te contactaremos para confirmar.";
    } else {
        $mensaje = "error:Ocurrió un error. Intenta nuevamente.";
    }
}

$tipos = ['cabaña'=>'🏕️','glamping'=>'✨','suite'=>'👑','doble'=>'🛏️','sencilla'=>'🛖'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Habitaciones · Xaratangato</title>
<link rel="stylesheet" href="css/styles.css">
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<div class="hero" style="padding:50px 2rem 40px;">
  <div class="hero-badge">🛏️ Alojamiento</div>
  <h1 class="hero-title" style="font-size:2.5rem;">Nuestras Habitaciones</h1>
  <p class="hero-subtitle">Desde acogedoras cabañas hasta glamping de lujo, encuentra el espacio perfecto para descansar.</p>
</div>

<div class="section-content">

  <?php if ($mensaje): 
    [$tipo, $txt] = explode(':', $mensaje, 2);
  ?>
    <div class="alert alert-<?= $tipo === 'success' ? 'success' : 'error' ?>">
      <?= $tipo === 'success' ? '✅' : '⚠️' ?> <?= htmlspecialchars($txt) ?>
    </div>
  <?php endif; ?>

  <h2 class="section-title">Elige tu alojamiento</h2>
  <p class="section-desc"><?= count($habitaciones) ?> opciones disponibles · Precios por noche (MXN)</p>

  <div class="cards-grid">
    <?php foreach ($habitaciones as $h):
      $emoji = $tipos[$h['tipo']] ?? '🏠';
    ?>
    <div class="card">
      <div class="card-img">
        <div class="img-placeholder"><?= $emoji ?></div>
        <span class="card-badge"><?= ucfirst($h['tipo']) ?></span>
      </div>
      <div class="card-body">
        <h3 class="card-title"><?= htmlspecialchars($h['nombre']) ?></h3>
        <p class="card-desc"><?= htmlspecialchars($h['descripcion']) ?></p>
        <div class="card-meta">
          <div class="card-price">$<?= number_format($h['precio_noche'],0) ?> <small>/ noche</small></div>
          <div class="card-info">
            <span class="tag">👥 <?= $h['capacidad'] ?> personas</span>
          </div>
        </div>
        <button class="btn btn-primary"
          onclick="abrirModal('habitacion',<?= $h['id'] ?>,'<?= addslashes($h['nombre']) ?>',<?= $h['precio_noche'] ?>,'noche')">
          📅 Reservar ahora
        </button>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<!-- MODAL RESERVA -->
<div class="modal-overlay" id="modalReserva">
  <div class="modal">
    <div class="modal-header">
      <div>
        <h3 id="modalTitulo">Reservar</h3>
        <p id="modalSubtitulo">Selecciona tus fechas</p>
      </div>
      <button class="modal-close" onclick="cerrarModal()">✕</button>
    </div>
    <div class="modal-body">
      <form method="POST" action="habitaciones.php" onsubmit="return validarFechas()">
        <input type="hidden" name="reservar_hab" value="1">
        <input type="hidden" name="item_id" id="itemId">
        <input type="hidden" name="total" id="totalHidden">

        <div class="form-row">
          <div class="form-group">
            <label class="form-label">📅 Fecha llegada</label>
            <input type="date" name="fecha_inicio" id="fechaInicio" class="form-control"
                   min="<?= date('Y-m-d') ?>" required onchange="calcularTotal()">
          </div>
          <div class="form-group">
            <label class="form-label">📅 Fecha salida</label>
            <input type="date" name="fecha_fin" id="fechaFin" class="form-control"
                   min="<?= date('Y-m-d', strtotime('+1 day')) ?>" required onchange="calcularTotal()">
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">📝 Notas especiales (opcional)</label>
          <textarea name="notas" class="form-control" rows="2"
                    placeholder="Alergias, celebraciones, preferencias..."></textarea>
        </div>

        <div class="price-summary" id="resumenPrecio" style="display:none;">
          <div class="total-label" id="resumenDetalle"></div>
          <div class="total-value" id="resumenTotal">$0.00</div>
        </div>

        <button type="submit" class="btn btn-primary" style="width:100%;padding:13px;margin-top:8px;">
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

<script src="js/reservas.js"></script>
</body>
</html>
