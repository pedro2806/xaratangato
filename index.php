<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title>Xaratangato</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">
    
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="app-header">
    <div class="d-flex justify-content-between align-items-center">
        <span class="app-logo">Xaratangato</span>
        <button class="btn btn-sm btn-light rounded-circle p-2">
            <i class="material-icons-outlined" style="font-size: 1.2rem; color: var(--text-muted);">notifications</i>
        </button>
    </div>
</div>

<div class="container-fluid px-3">
    <div class="search-bar">        
        <div class="date-input-wrapper">
            <input type="date" id="fecha_inicio">
            <span class="text-muted small"></span>
            <input type="date" id="fecha_fin">
        </div>
        <button class="btn btn-primary rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 30px; height: 30px;">
            <i class="material-icons-outlined text-white" style="font-size: .75rem;">search</i>
        </button>
    </div>

    <div class="category-pill-container" id="category_filters">
        <button class="btn-pill active" data-cat="0">Todos</button>
        <button class="btn-pill" data-cat="1">Habitaciones</button>
        <button class="btn-pill" data-cat="3">Actividades</button>
        <button class="btn-pill" data-cat="2">Bicis/Equipo</button>
    </div>

    <div id="contenedor-recursos" class="mt-2"></div>
</div>

<div class="nav-bottom">
    <a href="#" class="nav-item-custom active">
        <i class="material-icons-outlined">explore</i>
        <span>Explorar</span>
    </a>
    <a href="#" class="nav-item-custom">
        <i class="material-icons-outlined">confirmation_number</i>
        <span>Viajes</span>
    </a>
    <a href="#" class="nav-item-custom">
        <i class="material-icons-outlined">favorite_border</i>
        <span>Guardados</span>
    </a>
    <a href="#" class="nav-item-custom">
        <i class="material-icons-outlined">person_outline</i>
        <span>Perfil</span>
    </a>
</div>
<!-- Modal de Reserva -->
<div class="modal fade" id="modalReserva" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold">Confirmar Reserva</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="resumen-reserva" class="text-center mb-3">
                    </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Cantidad</label>
                    <input type="number" id="reserva_cantidad" class="form-control rounded-3" value="1" min="1">
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary rounded-pill px-4" id="btnConfirmarFinal">Confirmar y Pagar</button>
            </div>
        </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="js/app.js"></script>

</body>
</html>