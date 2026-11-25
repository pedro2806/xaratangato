<?php
require_once 'includes/auth.php';
requireLogin();
if (!isAdmin()) { header('Location: ' . APP_URL . '/index.php'); exit; }

$db  = getDB();
$tab = $_GET['tab'] ?? 'habitaciones';
$msg = '';

// ============================================================
//  HELPERS
// ============================================================
function sanitize($v) { return htmlspecialchars(trim($v), ENT_QUOTES); }

// ============================================================
//  ACCIONES CRUD ajjajajaj
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    $tipo   = $_POST['tipo']   ?? '';

    // ── HABITACIONES ──────────────────────────────────────────
    if ($tipo === 'habitacion') {
        if ($accion === 'crear' || $accion === 'editar') {
            $nombre      = trim($_POST['nombre']      ?? '');
            $descripcion = trim($_POST['descripcion'] ?? '');
            $capacidad   = (int)($_POST['capacidad']  ?? 1);
            $precio      = (float)($_POST['precio']   ?? 0);
            $tipo_hab    = $_POST['tipo_hab']          ?? 'sencilla';
            $disponible  = isset($_POST['disponible']) ? 1 : 0;

            if ($accion === 'crear') {
                $stmt = $db->prepare("INSERT INTO habitaciones (nombre,descripcion,capacidad,precio_noche,tipo,disponible) VALUES (?,?,?,?,?,?)");
                $stmt->bind_param('ssiisi', $nombre,$descripcion,$capacidad,$precio,$tipo_hab,$disponible);
                $stmt->execute();
                $msg = "success:Habitación creada correctamente.";
            } else {
                $id = (int)$_POST['id'];
                $stmt = $db->prepare("UPDATE habitaciones SET nombre=?,descripcion=?,capacidad=?,precio_noche=?,tipo=?,disponible=? WHERE id=?");
                $stmt->bind_param('ssiisii', $nombre,$descripcion,$capacidad,$precio,$tipo_hab,$disponible,$id);
                $stmt->execute();
                $msg = "success:Habitación actualizada.";
            }
        } elseif ($accion === 'eliminar') {
            $id = (int)$_POST['id'];
            $db->query("DELETE FROM habitaciones WHERE id=$id");
            $msg = "info:Habitación eliminada.";
        }
        $tab = 'habitaciones';
    }

    // ── ACTIVIDADES ───────────────────────────────────────────
    if ($tipo === 'actividad') {
        if ($accion === 'crear' || $accion === 'editar') {
            $nombre      = trim($_POST['nombre']      ?? '');
            $descripcion = trim($_POST['descripcion'] ?? '');
            $precio      = (float)($_POST['precio']   ?? 0);
            $duracion    = trim($_POST['duracion']    ?? '');
            $capacidad   = (int)($_POST['capacidad']  ?? 10);
            $disponible  = isset($_POST['disponible']) ? 1 : 0;

            if ($accion === 'crear') {
                $stmt = $db->prepare("INSERT INTO actividades (nombre,descripcion,precio,duracion,capacidad_max,disponible) VALUES (?,?,?,?,?,?)");
                $stmt->bind_param('ssdsi i', $nombre,$descripcion,$precio,$duracion,$capacidad,$disponible);
                // fix bind
                $stmt2 = $db->prepare("INSERT INTO actividades (nombre,descripcion,precio,duracion,capacidad_max,disponible) VALUES (?,?,?,?,?,?)");
                $stmt2->bind_param('ssdsii', $nombre,$descripcion,$precio,$duracion,$capacidad,$disponible);
                $stmt2->execute();
                $msg = "success:Actividad creada correctamente.";
            } else {
                $id = (int)$_POST['id'];
                $stmt = $db->prepare("UPDATE actividades SET nombre=?,descripcion=?,precio=?,duracion=?,capacidad_max=?,disponible=? WHERE id=?");
                $stmt->bind_param('ssdsiii', $nombre,$descripcion,$precio,$duracion,$capacidad,$disponible,$id);
                $stmt->execute();
                $msg = "success:Actividad actualizada.";
            }
        } elseif ($accion === 'eliminar') {
            $id = (int)$_POST['id'];
            $db->query("DELETE FROM actividades WHERE id=$id");
            $msg = "info:Actividad eliminada.";
        }
        $tab = 'actividades';
    }

    // ── EQUIPO ────────────────────────────────────────────────
    if ($tipo === 'equipo') {
        if ($accion === 'crear' || $accion === 'editar') {
            $nombre      = trim($_POST['nombre']      ?? '');
            $descripcion = trim($_POST['descripcion'] ?? '');
            $tipo_eq     = $_POST['tipo_eq']           ?? 'otro';
            $precio_hora = (float)($_POST['precio_hora'] ?? 0);
            $precio_dia  = (float)($_POST['precio_dia']  ?? 0);
            $cantidad    = (int)($_POST['cantidad']    ?? 1);
            $disponible  = isset($_POST['disponible']) ? 1 : 0;

            if ($accion === 'crear') {
                $stmt = $db->prepare("INSERT INTO equipo (nombre,descripcion,tipo,precio_hora,precio_dia,cantidad_total,disponible) VALUES (?,?,?,?,?,?,?)");
                $stmt->bind_param('sssddii', $nombre,$descripcion,$tipo_eq,$precio_hora,$precio_dia,$cantidad,$disponible);
                $stmt->execute();
                $msg = "success:Equipo creado correctamente.";
            } else {
                $id = (int)$_POST['id'];
                $stmt = $db->prepare("UPDATE equipo SET nombre=?,descripcion=?,tipo=?,precio_hora=?,precio_dia=?,cantidad_total=?,disponible=? WHERE id=?");
                $stmt->bind_param('sssddiii', $nombre,$descripcion,$tipo_eq,$precio_hora,$precio_dia,$cantidad,$disponible,$id);
                $stmt->execute();
                $msg = "success:Equipo actualizado.";
            }
        } elseif ($accion === 'eliminar') {
            $id = (int)$_POST['id'];
            $db->query("DELETE FROM equipo WHERE id=$id");
            $msg = "info:Equipo eliminado.";
        }
        $tab = 'equipo';
    }

    header("Location: catalogo.php?tab=$tab&msg=" . urlencode($msg));
    exit;
}

// Recuperar mensaje de redirect
if (isset($_GET['msg'])) $msg = urldecode($_GET['msg']);

// ── Cargar datos ──────────────────────────────────────────────
$habitaciones = $db->query("SELECT * FROM habitaciones ORDER BY id DESC")->fetch_all(MYSQLI_ASSOC);
$actividades  = $db->query("SELECT * FROM actividades  ORDER BY id DESC")->fetch_all(MYSQLI_ASSOC);
$equipos      = $db->query("SELECT * FROM equipo        ORDER BY id DESC")->fetch_all(MYSQLI_ASSOC);

// Item a editar
$editItem = null;
if (isset($_GET['editar']) && isset($_GET['editTipo'])) {
    $eid = (int)$_GET['editar'];
    $et  = $_GET['editTipo'];
    $tablaMap = ['habitacion'=>'habitaciones','actividad'=>'actividades','equipo'=>'equipo'];
    if (isset($tablaMap[$et])) {
        $r = $db->query("SELECT * FROM {$tablaMap[$et]} WHERE id=$eid");
        $editItem = $r->fetch_assoc();
        $editItem['_tipo'] = $et;
        $tab = ($et === 'habitacion') ? 'habitaciones' : ($et === 'actividad' ? 'actividades' : 'equipo');
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Catálogo Admin · Xaratangato</title>
<link rel="stylesheet" href="css/styles.css">
<style>
/* ── TABLA ADMIN ── */
.admin-table { width:100%; border-collapse:collapse; background:white; border-radius:14px; overflow:hidden; box-shadow:0 2px 15px var(--sombra); }
.admin-table th { background:var(--verde-oscuro); color:white; padding:12px 16px; text-align:left; font-size:0.8rem; font-weight:700; text-transform:uppercase; letter-spacing:.5px; }
.admin-table td { padding:11px 16px; border-bottom:1px solid #f0ebe5; font-size:0.88rem; vertical-align:middle; }
.admin-table tr:last-child td { border-bottom:none; }
.admin-table tr:hover td { background:#fafaf8; }
.table-wrap { overflow-x:auto; border-radius:14px; }

/* ── PANEL LATERAL ── */
.layout { display:grid; grid-template-columns:380px 1fr; gap:1.5rem; align-items:start; }
@media(max-width:900px){ .layout { grid-template-columns:1fr; } }

/* ── FORM CARD ── */
.form-card { background:white; border-radius:14px; box-shadow:0 2px 15px var(--sombra); overflow:hidden; position:sticky; top:80px; }
.form-card-header { background:var(--verde-oscuro); color:white; padding:1.2rem 1.5rem; display:flex; align-items:center; justify-content:space-between; }
.form-card-header h3 { font-size:1.1rem; }
.form-card-body { padding:1.5rem; }

/* ── TOGGLE SWITCH ── */
.toggle-wrap { display:flex; align-items:center; gap:10px; }
.toggle { position:relative; display:inline-block; width:44px; height:24px; }
.toggle input { opacity:0; width:0; height:0; }
.slider { position:absolute; cursor:pointer; inset:0; background:#ccc; border-radius:34px; transition:.3s; }
.slider:before { position:absolute; content:""; height:18px; width:18px; left:3px; bottom:3px; background:white; border-radius:50%; transition:.3s; }
input:checked + .slider { background:var(--verde-claro); }
input:checked + .slider:before { transform:translateX(20px); }

/* ── BADGE TIPO ── */
.tipo-badge { display:inline-block; padding:3px 10px; border-radius:20px; font-size:0.75rem; font-weight:700; text-transform:uppercase; }
.tipo-cabaña, .tipo-glamping { background:#e8f5e9; color:#2d6a4f; }
.tipo-suite   { background:#fff8e1; color:#b7791f; }
.tipo-doble,.tipo-sencilla { background:#f3f4f6; color:#374151; }
.tipo-bicicleta { background:#e0f2fe; color:#0369a1; }
.tipo-cuatrimoto { background:#fef3c7; color:#92400e; }
.tipo-kayak { background:#ede9fe; color:#5b21b6; }
.tipo-otro { background:#f3f4f6; color:#374151; }

.actions-cell { display:flex; gap:6px; flex-wrap:wrap; }
.disp-si { color:#059669; font-weight:700; }
.disp-no { color:#dc2626; font-weight:700; }

/* ── SECTION TABS ── */
.page-tabs { display:flex; gap:8px; flex-wrap:wrap; margin-bottom:1.5rem; }
</style>
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<div class="hero" style="padding:44px 2rem 36px;background:linear-gradient(135deg,#0f1f14,#1a3a2a,#2d6a4f);">
  <div class="hero-badge">⚙️ Administración</div>
  <h1 class="hero-title" style="font-size:2.2rem;">Gestión de Catálogo</h1>
  <p class="hero-subtitle" style="margin-bottom:0;">Administra habitaciones, actividades y equipo disponible en el parque.</p>
</div>

<div class="section-content">

  <!-- Mensaje -->
  <?php if ($msg):
    [$tipo_msg, $txt_msg] = explode(':', $msg, 2);
    $cls = $tipo_msg === 'success' ? 'alert-success' : ($tipo_msg === 'info' ? 'alert-info' : 'alert-error');
    $ico = $tipo_msg === 'success' ? '✅' : ($tipo_msg === 'info' ? 'ℹ️' : '⚠️');
  ?>
    <div class="alert <?= $cls ?>"><?= $ico ?> <?= sanitize($txt_msg) ?></div>
  <?php endif; ?>

  <!-- Tabs principales -->
  <div class="page-tabs">
    <a href="?tab=habitaciones" class="tab-btn <?= $tab==='habitaciones'?'active':'' ?>">🛏️ Habitaciones (<?= count($habitaciones) ?>)</a>
    <a href="?tab=actividades"  class="tab-btn <?= $tab==='actividades' ?'active':'' ?>">⛰️ Actividades (<?= count($actividades) ?>)</a>
    <a href="?tab=equipo"       class="tab-btn <?= $tab==='equipo'      ?'active':'' ?>">🚲 Equipo (<?= count($equipos) ?>)</a>
    <a href="admin.php" class="tab-btn" style="margin-left:auto;">📊 Ver Reservas</a>
  </div>

  <!-- ================================================================
       TAB: HABITACIONES
       ================================================================ -->
  <?php if ($tab === 'habitaciones'): ?>
  <div class="layout">

    <!-- FORM -->
    <div class="form-card">
      <div class="form-card-header">
        <h3><?= ($editItem && $editItem['_tipo']==='habitacion') ? '✏️ Editar Habitación' : '➕ Nueva Habitación' ?></h3>
        <?php if ($editItem && $editItem['_tipo']==='habitacion'): ?>
          <a href="?tab=habitaciones" class="btn btn-sm" style="background:rgba(255,255,255,0.2);color:white;">✕ Cancelar</a>
        <?php endif; ?>
      </div>
      <div class="form-card-body">
        <form method="POST" action="catalogo.php">
          <input type="hidden" name="tipo"   value="habitacion">
          <input type="hidden" name="accion" value="<?= ($editItem && $editItem['_tipo']==='habitacion') ? 'editar' : 'crear' ?>">
          <?php if ($editItem && $editItem['_tipo']==='habitacion'): ?>
            <input type="hidden" name="id" value="<?= $editItem['id'] ?>">
          <?php endif; ?>

          <div class="form-group">
            <label class="form-label">Nombre</label>
            <input type="text" name="nombre" class="form-control" required
              placeholder="Ej: Cabaña del Pinar"
              value="<?= sanitize($editItem['nombre'] ?? '') ?>">
          </div>
          <div class="form-group">
            <label class="form-label">Descripción</label>
            <textarea name="descripcion" class="form-control" rows="3"
              placeholder="Descripción de la habitación..."><?= sanitize($editItem['descripcion'] ?? '') ?></textarea>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Tipo</label>
              <select name="tipo_hab" class="form-control">
                <?php foreach (['sencilla','doble','suite','cabaña','glamping'] as $t): ?>
                  <option value="<?= $t ?>" <?= ($editItem['tipo'] ?? '') === $t ? 'selected' : '' ?>><?= ucfirst($t) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">Capacidad (personas)</label>
              <input type="number" name="capacidad" class="form-control" min="1" max="20"
                value="<?= $editItem['capacidad'] ?? 2 ?>">
            </div>
          </div>
          <div class="form-group">
            <label class="form-label">Precio por noche (MXN $)</label>
            <input type="number" name="precio" class="form-control" min="0" step="0.01" required
              placeholder="0.00" value="<?= $editItem['precio_noche'] ?? '' ?>">
          </div>
          <div class="form-group">
            <label class="form-label">Disponibilidad</label>
            <div class="toggle-wrap">
              <label class="toggle">
                <input type="checkbox" name="disponible" value="1"
                  <?= ($editItem['disponible'] ?? 1) ? 'checked' : '' ?>>
                <span class="slider"></span>
              </label>
              <span style="font-size:.9rem;color:#555;">Disponible para reservar</span>
            </div>
          </div>
          <button type="submit" class="btn btn-primary" style="width:100%;padding:12px;">
            <?= ($editItem && $editItem['_tipo']==='habitacion') ? '💾 Guardar cambios' : '➕ Crear habitación' ?>
          </button>
        </form>
      </div>
    </div>

    <!-- TABLA -->
    <div>
      <div class="table-wrap">
        <table class="admin-table">
          <thead>
            <tr>
              <th>#</th><th>Nombre</th><th>Tipo</th><th>Cap.</th>
              <th>Precio/noche</th><th>Disp.</th><th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($habitaciones)): ?>
              <tr><td colspan="7" style="text-align:center;padding:2rem;color:#aaa;">Sin habitaciones registradas</td></tr>
            <?php endif; ?>
            <?php foreach ($habitaciones as $h): ?>
            <tr>
              <td><strong>#<?= $h['id'] ?></strong></td>
              <td><?= sanitize($h['nombre']) ?></td>
              <td><span class="tipo-badge tipo-<?= $h['tipo'] ?>"><?= ucfirst($h['tipo']) ?></span></td>
              <td>👥 <?= $h['capacidad'] ?></td>
              <td><strong>$<?= number_format($h['precio_noche'],2) ?></strong></td>
              <td class="<?= $h['disponible'] ? 'disp-si' : 'disp-no' ?>"><?= $h['disponible'] ? '✔ Sí' : '✘ No' ?></td>
              <td>
                <div class="actions-cell">
                  <a href="?tab=habitaciones&editar=<?= $h['id'] ?>&editTipo=habitacion"
                     class="btn btn-outline btn-sm">✏️ Editar</a>
                  <form method="POST" onsubmit="return confirm('¿Eliminar esta habitación?')">
                    <input type="hidden" name="tipo"   value="habitacion">
                    <input type="hidden" name="accion" value="eliminar">
                    <input type="hidden" name="id"     value="<?= $h['id'] ?>">
                    <button class="btn btn-danger btn-sm">🗑 Eliminar</button>
                  </form>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <?php endif; ?>


  <!-- ================================================================
       TAB: ACTIVIDADES
       ================================================================ -->
  <?php if ($tab === 'actividades'): ?>
  <div class="layout">

    <div class="form-card">
      <div class="form-card-header">
        <h3><?= ($editItem && $editItem['_tipo']==='actividad') ? '✏️ Editar Actividad' : '➕ Nueva Actividad' ?></h3>
        <?php if ($editItem && $editItem['_tipo']==='actividad'): ?>
          <a href="?tab=actividades" class="btn btn-sm" style="background:rgba(255,255,255,0.2);color:white;">✕ Cancelar</a>
        <?php endif; ?>
      </div>
      <div class="form-card-body">
        <form method="POST" action="catalogo.php">
          <input type="hidden" name="tipo"   value="actividad">
          <input type="hidden" name="accion" value="<?= ($editItem && $editItem['_tipo']==='actividad') ? 'editar' : 'crear' ?>">
          <?php if ($editItem && $editItem['_tipo']==='actividad'): ?>
            <input type="hidden" name="id" value="<?= $editItem['id'] ?>">
          <?php endif; ?>

          <div class="form-group">
            <label class="form-label">Nombre</label>
            <input type="text" name="nombre" class="form-control" required
              placeholder="Ej: Tirolesa Nocturna"
              value="<?= sanitize($editItem['nombre'] ?? '') ?>">
          </div>
          <div class="form-group">
            <label class="form-label">Descripción</label>
            <textarea name="descripcion" class="form-control" rows="3"
              placeholder="Describe la actividad..."><?= sanitize($editItem['descripcion'] ?? '') ?></textarea>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Precio por persona (MXN $)</label>
              <input type="number" name="precio" class="form-control" min="0" step="0.01" required
                placeholder="0.00" value="<?= $editItem['precio'] ?? '' ?>">
            </div>
            <div class="form-group">
              <label class="form-label">Duración</label>
              <input type="text" name="duracion" class="form-control"
                placeholder="Ej: 2 horas" value="<?= sanitize($editItem['duracion'] ?? '') ?>">
            </div>
          </div>
          <div class="form-group">
            <label class="form-label">Capacidad máxima (personas)</label>
            <input type="number" name="capacidad" class="form-control" min="1" max="100"
              value="<?= $editItem['capacidad_max'] ?? 10 ?>">
          </div>
          <div class="form-group">
            <label class="form-label">Disponibilidad</label>
            <div class="toggle-wrap">
              <label class="toggle">
                <input type="checkbox" name="disponible" value="1"
                  <?= ($editItem['disponible'] ?? 1) ? 'checked' : '' ?>>
                <span class="slider"></span>
              </label>
              <span style="font-size:.9rem;color:#555;">Disponible para reservar</span>
            </div>
          </div>
          <button type="submit" class="btn btn-gold" style="width:100%;padding:12px;color:#1a1a1a;">
            <?= ($editItem && $editItem['_tipo']==='actividad') ? '💾 Guardar cambios' : '➕ Crear actividad' ?>
          </button>
        </form>
      </div>
    </div>

    <div>
      <div class="table-wrap">
        <table class="admin-table">
          <thead>
            <tr>
              <th>#</th><th>Nombre</th><th>Precio/persona</th>
              <th>Duración</th><th>Cap. máx</th><th>Disp.</th><th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($actividades)): ?>
              <tr><td colspan="7" style="text-align:center;padding:2rem;color:#aaa;">Sin actividades registradas</td></tr>
            <?php endif; ?>
            <?php foreach ($actividades as $a): ?>
            <tr>
              <td><strong>#<?= $a['id'] ?></strong></td>
              <td><?= sanitize($a['nombre']) ?></td>
              <td><strong>$<?= number_format($a['precio'],2) ?></strong></td>
              <td>⏱ <?= sanitize($a['duracion']) ?></td>
              <td>👥 <?= $a['capacidad_max'] ?></td>
              <td class="<?= $a['disponible'] ? 'disp-si' : 'disp-no' ?>"><?= $a['disponible'] ? '✔ Sí' : '✘ No' ?></td>
              <td>
                <div class="actions-cell">
                  <a href="?tab=actividades&editar=<?= $a['id'] ?>&editTipo=actividad"
                     class="btn btn-outline btn-sm">✏️ Editar</a>
                  <form method="POST" onsubmit="return confirm('¿Eliminar esta actividad?')">
                    <input type="hidden" name="tipo"   value="actividad">
                    <input type="hidden" name="accion" value="eliminar">
                    <input type="hidden" name="id"     value="<?= $a['id'] ?>">
                    <button class="btn btn-danger btn-sm">🗑 Eliminar</button>
                  </form>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <?php endif; ?>


  <!-- ================================================================
       TAB: EQUIPO
       ================================================================ -->
  <?php if ($tab === 'equipo'): ?>
  <div class="layout">

    <div class="form-card">
      <div class="form-card-header" style="background:linear-gradient(135deg,#1a3a2a,#1a6fa0);">
        <h3><?= ($editItem && $editItem['_tipo']==='equipo') ? '✏️ Editar Equipo' : '➕ Nuevo Equipo' ?></h3>
        <?php if ($editItem && $editItem['_tipo']==='equipo'): ?>
          <a href="?tab=equipo" class="btn btn-sm" style="background:rgba(255,255,255,0.2);color:white;">✕ Cancelar</a>
        <?php endif; ?>
      </div>
      <div class="form-card-body">
        <form method="POST" action="catalogo.php">
          <input type="hidden" name="tipo"   value="equipo">
          <input type="hidden" name="accion" value="<?= ($editItem && $editItem['_tipo']==='equipo') ? 'editar' : 'crear' ?>">
          <?php if ($editItem && $editItem['_tipo']==='equipo'): ?>
            <input type="hidden" name="id" value="<?= $editItem['id'] ?>">
          <?php endif; ?>

          <div class="form-group">
            <label class="form-label">Nombre</label>
            <input type="text" name="nombre" class="form-control" required
              placeholder="Ej: Cuatrimoto Sport 500cc"
              value="<?= sanitize($editItem['nombre'] ?? '') ?>">
          </div>
          <div class="form-group">
            <label class="form-label">Tipo de equipo</label>
            <select name="tipo_eq" class="form-control">
              <?php foreach (['bicicleta'=>'🚲 Bicicleta','cuatrimoto'=>'🏍️ Cuatrimoto','kayak'=>'🛶 Kayak','otro'=>'🎒 Otro'] as $val => $label): ?>
                <option value="<?= $val ?>" <?= ($editItem['tipo'] ?? '') === $val ? 'selected' : '' ?>><?= $label ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Descripción</label>
            <textarea name="descripcion" class="form-control" rows="2"
              placeholder="Características del equipo..."><?= sanitize($editItem['descripcion'] ?? '') ?></textarea>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Precio por hora (MXN $)</label>
              <input type="number" name="precio_hora" class="form-control" min="0" step="0.01"
                placeholder="0.00" value="<?= $editItem['precio_hora'] ?? '' ?>">
            </div>
            <div class="form-group">
              <label class="form-label">Precio por día (MXN $)</label>
              <input type="number" name="precio_dia" class="form-control" min="0" step="0.01"
                placeholder="0.00" value="<?= $editItem['precio_dia'] ?? '' ?>">
            </div>
          </div>
          <div class="form-group">
            <label class="form-label">Unidades disponibles</label>
            <input type="number" name="cantidad" class="form-control" min="1" max="100"
              value="<?= $editItem['cantidad_total'] ?? 1 ?>">
          </div>
          <div class="form-group">
            <label class="form-label">Disponibilidad</label>
            <div class="toggle-wrap">
              <label class="toggle">
                <input type="checkbox" name="disponible" value="1"
                  <?= ($editItem['disponible'] ?? 1) ? 'checked' : '' ?>>
                <span class="slider"></span>
              </label>
              <span style="font-size:.9rem;color:#555;">Disponible para rentar</span>
            </div>
          </div>
          <button type="submit" class="btn btn-primary" style="width:100%;padding:12px;background:linear-gradient(135deg,#1a3a2a,#1a6fa0);">
            <?= ($editItem && $editItem['_tipo']==='equipo') ? '💾 Guardar cambios' : '➕ Agregar equipo' ?>
          </button>
        </form>
      </div>
    </div>

    <div>
      <div class="table-wrap">
        <table class="admin-table">
          <thead>
            <tr>
              <th>#</th><th>Nombre</th><th>Tipo</th><th>$/hora</th>
              <th>$/día</th><th>Unidades</th><th>Disp.</th><th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($equipos)): ?>
              <tr><td colspan="8" style="text-align:center;padding:2rem;color:#aaa;">Sin equipo registrado</td></tr>
            <?php endif; ?>
            <?php foreach ($equipos as $e): ?>
            <tr>
              <td><strong>#<?= $e['id'] ?></strong></td>
              <td><?= sanitize($e['nombre']) ?></td>
              <td><span class="tipo-badge tipo-<?= $e['tipo'] ?>"><?= ucfirst($e['tipo']) ?></span></td>
              <td><?= $e['precio_hora'] ? '$'.number_format($e['precio_hora'],2) : '—' ?></td>
              <td><?= $e['precio_dia']  ? '$'.number_format($e['precio_dia'],2)  : '—' ?></td>
              <td>📦 <?= $e['cantidad_total'] ?></td>
              <td class="<?= $e['disponible'] ? 'disp-si' : 'disp-no' ?>"><?= $e['disponible'] ? '✔ Sí' : '✘ No' ?></td>
              <td>
                <div class="actions-cell">
                  <a href="?tab=equipo&editar=<?= $e['id'] ?>&editTipo=equipo"
                     class="btn btn-outline btn-sm">✏️ Editar</a>
                  <form method="POST" onsubmit="return confirm('¿Eliminar este equipo?')">
                    <input type="hidden" name="tipo"   value="equipo">
                    <input type="hidden" name="accion" value="eliminar">
                    <input type="hidden" name="id"     value="<?= $e['id'] ?>">
                    <button class="btn btn-danger btn-sm">🗑 Eliminar</button>
                  </form>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <?php endif; ?>

</div><!-- /section-content -->

<footer>
  <strong>Xaratangato</strong> · Panel de Administración<br>
  <small>© <?= date('Y') ?> Todos los derechos reservados</small>
</footer>

</body>
</html>
