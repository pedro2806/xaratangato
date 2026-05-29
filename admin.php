<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Xaratangato - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">
    <style>
        body { background-color: #f4f7f6; font-family: 'Poppins', sans-serif; }
        .sidebar { height: 100vh; background: #2d3436; color: white; padding-top: 20px; }
        .nav-link { color: #b2bec3; margin-bottom: 10px; }
        .nav-link.active { color: white; font-weight: 600; }
        .main-content { padding: 30px; }
        .card-stats { border: none; border-radius: 15px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-2 sidebar">
            <h4 class="text-center mb-4 text-primary">Xaratangato</h4>
            <nav class="nav flex-column">
                <a class="nav-link active" href="#"><i class="material-icons-outlined align-middle me-2">dashboard</i> Dashboard</a>
                <a class="nav-link" href="#"><i class="material-icons-outlined align-middle me-2">inventory_2</i> Recursos</a>
                <a class="nav-link" href="index.php"><i class="material-icons-outlined align-middle me-2">visibility</i> Ver App</a>
            </nav>
        </div>

        <div class="col-md-10 main-content">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3>Panel de Control</h3>
                <span class="badge bg-primary px-3 py-2">Administrador: <?php session_start(); echo $_SESSION['user_name']; ?></span>
            </div>

            <div class="card card-stats">
                <div class="card-body">
                    <h5 class="card-title mb-4 fw-bold">Reservas Recientes</h5>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Cliente</th>
                                    <th>Recurso</th>
                                    <th>Fechas</th>
                                    <th>Cantidad</th>
                                    <th>Total</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="tabla-reservas">
                                </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalDetalle" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title">Detalle de la Reserva</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detalle-contenido">
                </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary rounded-pill" onclick="window.print()">Imprimir Ticket</button>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="modalRecurso" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="form-recurso">
                <div class="modal-header">
                    <h5 class="modal-title" id="tituloModal">Gestionar Recurso</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="recurso_id" id="recurso_id">
                    <div class="mb-3">
                        <label class="form-label">Nombre del Recurso</label>
                        <input type="text" name="nombre" id="form_nombre" class="form-control" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Categoría</label>
                            <select name="categoria_id" id="form_categoria" class="form-select">
                                <option value="1">Habitaciones</option>
                                <option value="2">Mobiliario/Bicis</option>
                                <option value="3">Actividades</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Precio ($)</label>
                            <input type="number" step="0.01" name="precio_decimal" id="form_precio" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Stock Total</label>
                        <input type="number" name="stock_total" id="form_stock" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">URL de Imagen</label>
                        <input type="text" name="imagen_url" id="form_img" class="form-control" placeholder="nombre-archivo.jpg o URL">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Descripción</label>
                        <textarea name="descripcion" id="form_desc" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        cargarReservasAdmin();
    });

    let todasLasReservas = []; // Variable global para guardar los datos

    function cargarReservasAdmin() {
        $.getJSON('obtener_reservas_admin.php', function(data) {
            todasLasReservas = data; // Guardamos los datos aquí
            let html = '';
            data.forEach(res => {
                const badge = res.estado === 'confirmada' ? 'bg-success' : 'bg-danger';
                html += `
                <tr>
                    <td>#${res.reserva_id}</td>
                    <td class="fw-bold">${res.cliente}</td>
                    <td>${res.recurso}</td>
                    <td><small>${res.fecha_inicio} a ${res.fecha_fin}</small></td>
                    <td>${res.cantidad}</td>
                    <td class="fw-bold text-primary">$${parseFloat(res.total).toLocaleString()}</td>
                    <td><span class="badge ${badge}">${res.estado}</span></td>
                    <td>
                        <button class="btn btn-sm btn-outline-dark" onclick="verDetalle(${res.reserva_id})">
                            <i class="material-icons-outlined" style="font-size: 14px;">visibility</i>
                        </button>
                    </td>
                </tr>`;
            });
            $('#tabla-reservas').html(html);
        });
    }
    // Función para abrir el modal vacío para un nuevo recurso
    function nuevoRecurso() {
        $('#form-recurso')[0].reset();
        $('#recurso_id').val('');
        $('#tituloModal').text('Nuevo Recurso para Xaratangato');
        $('#modalRecurso').modal('show');
    }

    // Función para cargar datos en el modal y editar (puedes llamarla desde una lista de recursos)
    function editarRecurso(datos) {
        $('#recurso_id').val(datos.id);
        $('#form_nombre').val(datos.nombre);
        $('#form_categoria').val(datos.categoria_id);
        $('#form_precio').val(datos.precio_decimal);
        $('#form_stock').val(datos.stock_total);
        $('#form_img').val(datos.imagen_url);
        $('#form_desc').val(datos.descripcion);
        $('#tituloModal').text('Editar Recurso');
        $('#modalRecurso').modal('show');
    }

    // Envío del formulario por AJAX
    $('#form-recurso').on('submit', function(e) {
        e.preventDefault();
        $.post('gestion_recursos.php', $(this).serialize(), function(res) {
            if(res.success) {
                alert(res.mensaje);
                $('#modalRecurso').modal('hide');
                // Aquí podrías recargar una tabla de recursos si la tuvieras
            } else {
                alert("Error: " + res.mensaje);
            }
        }, 'json');
    });


    function verDetalle(id) {
        // Buscamos los datos en el array local
        const res = todasLasReservas.find(item => item.reserva_id == id);

        if(!res) {
            alert("No se encontró la información de la reserva.");
            return;
        }

        // Mostramos el modal
        const $modal = new bootstrap.Modal(document.getElementById('modalDetalle'));
        
        let html = `
            <div class="text-center mb-4">
                <h3 class="fw-bold text-primary">Xaratangato</h3>
                <p class="text-muted small">Folio de Reserva: #${res.reserva_id}</p>
            </div>
            <hr>
            <div class="row mb-3">
                <div class="col-12">
                    <label class="text-muted small d-block">Cliente</label>
                    <span class="fw-bold">${res.cliente}</span>
                </div>
            </div>
            <div class="bg-light p-3 rounded-4 mb-3">
                <h6 class="fw-bold border-bottom pb-2">${res.recurso}</h6>
                <div class="d-flex justify-content-between mt-2">
                    <span>Cantidad: ${res.cantidad}</span>
                    <span class="fw-bold">$${parseFloat(res.total).toLocaleString()}</span>
                </div>
                <div class="mt-2 small text-muted">
                    Período: <strong>${res.fecha_inicio}</strong> al <strong>${res.fecha_fin}</strong>
                </div>
            </div>
            <div class="d-flex justify-content-between align-items-center">
                <span class="badge bg-${res.estado === 'confirmada' ? 'success' : 'danger'} p-2">
                    ${res.estado.toUpperCase()}
                </span>
                <div class="text-end">
                    <small class="text-muted d-block">Total de Venta</small>
                    <h4 class="fw-bold text-primary mb-0">$${parseFloat(res.total).toLocaleString()}</h4>
                </div>
            </div>
        `;

        $('#detalle-contenido').html(html);
        $modal.show();
    }
</script>
</body>
</html>