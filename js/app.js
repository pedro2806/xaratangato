/**
 * Xaratangato - Core App Logic
 * Stack: jQuery + AJAX
 */

let recursoSeleccionado = {};

$(document).ready(function() {
    // 1. Inicialización de fechas (Hoy y Mañana)
    const hoy = new Date().toISOString().split('T')[0];
    const mañana = new Date();
    mañana.setDate(mañana.getDate() + 1);
    const mañanaIso = mañana.toISOString().split('T')[0];

    $('#fecha_inicio').val(hoy);
    $('#fecha_fin').val(mañanaIso);

    // 2. Carga inicial de recursos
    cargarDisponibilidad();

    // 3. Eventos para actualizar búsqueda
    $('#category_filters .btn-pill').on('click', function() {
        $('#category_filters .btn-pill').removeClass('active');
        $(this).addClass('active');
        cargarDisponibilidad();
    });

    $('#fecha_inicio, #fecha_fin').on('change', function() {
        cargarDisponibilidad();
    });

    // 4. Navegación Inferior (SPA Style)
    $('.nav-item-custom').on('click', function(e) {
        e.preventDefault();
        $('.nav-item-custom').removeClass('active');
        $(this).addClass('active');

        const vista = $(this).find('span').text();

        if (vista === 'Viajes') {
            cargarMisReservas();
        } else if (vista === 'Explorar') {
            // Restaurar vista de exploración
            $('.search-bar, #category_filters').show();
            cargarDisponibilidad();
        }
    });

    // 5. Confirmación final de reserva
    $('#btnConfirmarFinal').on('click', function() {
        const cantidad = $('#reserva_cantidad').val();
        
        $.post('procesar_reserva.php', {
            recurso_id: recursoSeleccionado.id,
            inicio: recursoSeleccionado.inicio,
            fin: recursoSeleccionado.fin,
            cantidad: cantidad,
            total: (recursoSeleccionado.precio * recursoSeleccionado.noches * cantidad)
        }, function(res) {
            if(res.success) {
                //alert("¡Reserva exitosa en Xaratangato!");
                swal.fire({
                    title: '¡Reserva exitosa!',
                    text: 'Tu viaje ha sido reservado en Xaratangato.', 
                    icon: 'success',
                    confirmButtonText: 'Ver mis viajes'
                }).then(() => {

                $('#modalReserva').modal('hide');
                cargarMisReservas();
                // Activar icono de viajes en la nav
                $('.nav-item-custom').removeClass('active');
                $('.nav-item-custom:contains("Viajes")').addClass('active');
                });
            } else {
                swal.fire({
                    title: 'Error',
                    text: "Error: " + res.mensaje,
                    icon: 'error',
                    confirmButtonText: 'Entendido'
                }); 
            }
        }, 'json');
    });
});

/**
 * Carga los recursos disponibles desde el backend
 */
function cargarDisponibilidad() {
    const inicio = $('#fecha_inicio').val();
    const fin = $('#fecha_fin').val();
    const cat_id = $('#category_filters .btn-pill.active').data('cat');
    
    if (!inicio || !fin) return;

    const $contenedor = $('#contenedor-recursos');
    $contenedor.html('<div class="text-center p-5"><div class="spinner-border text-primary"></div></div>');

    $.getJSON('buscar_disponibilidad.php', { inicio: inicio, fin: fin, categoria: cat_id }, function(data) {
        let html = '';
        
        if (data.length === 0) {
            html = `
                <div class="alert alert-warning rounded-4 border-0 shadow-sm text-center py-4">
                    <i class="material-icons-outlined d-block mb-2" style="font-size: 2rem;">sentiment_dissatisfied</i>
                    No hay disponibilidad para estas fechas en <b>Xaratangato</b>.
                </div>`;
        } else {
            data.forEach(function(item) {
                let img_path = item.imagen_url.startsWith('http') 
                    ? item.imagen_url 
                    : `img/recursos/${item.imagen_url}`;

                html += `
                <div class="card card-pro">
                    <div class="card-pro-img-wrapper">
                        <img src="${img_path}" class="card-pro-img">
                        <span class="card-pro-category">${item.categoria}</span>
                    </div>
                    <div class="card-pro-body">
                        <div class="card-pro-title">${item.nombre}</div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="card-pro-price">$${parseFloat(item.precio_decimal).toLocaleString()} <span>/ ${item.categoria_id == 1 ? 'noche' : 'unid.'}</span></div>
                            <div class="card-pro-stock d-flex align-items-center gap-1">
                                <i class="material-icons-outlined" style="font-size: 0.9rem;">event_available</i>
                                ${item.disponibles} libres
                            </div>
                        </div>
                        <button class="btn btn-primary w-100 rounded-pill btn-lg fs-6 shadow-sm" style="font-weight: 600;" 
                                onclick="abrirReserva(${item.id}, '${item.nombre}', ${item.precio_decimal})">
                            Reservar ahora
                        </button>
                    </div>
                </div>`;
            });
        }
        $contenedor.html(html);
    });
}

/**
 * Prepara y muestra el modal de confirmación
 */
function abrirReserva(id, nombre, precio) {
    const inicio = $('#fecha_inicio').val();
    const fin = $('#fecha_fin').val();
    
    const d1 = new Date(inicio);
    const d2 = new Date(fin);
    const noches = Math.max(1, Math.round((d2 - d1) / (1000 * 60 * 60 * 24)));
    
    recursoSeleccionado = { id, nombre, precio, inicio, fin, noches };

    $('#resumen-reserva').html(`
        <h6 class="text-primary mb-1">${nombre}</h6>
        <p class="small text-muted mb-0">${inicio} al ${fin}</p>
        <h4 class="fw-bold mt-2">$${(precio * noches).toLocaleString()} MXN</h4>
        <p class="small text-muted">Total por ${noches} unidad(es) de tiempo</p>
    `);

    const myModal = new bootstrap.Modal(document.getElementById('modalReserva'));
    myModal.show();
}

/**
 * Carga el historial de reservas del usuario logueado
 */
function cargarMisReservas() {
    $('.search-bar, #category_filters').hide();
    const $contenedor = $('#contenedor-recursos');
    $contenedor.html('<div class="text-center p-5"><div class="spinner-border text-primary"></div></div>');

    $.getJSON('obtener_mis_reservas.php', function(data) {
        let html = '<h5 class="fw-bold mb-4">Mis Reservas</h5>';
        
        if (data.length === 0) {
            html += '<p class="text-muted text-center py-5">Aún no tienes viajes programados.</p>';
        } else {
            data.forEach(function(res) {
                const badgeColor = res.estado === 'confirmada' ? 'bg-success' : (res.estado === 'cancelada' ? 'bg-danger' : 'bg-warning');
                const btnCancelar = res.estado === 'confirmada' 
                    ? `<button class="btn btn-sm btn-outline-danger rounded-pill mt-2" onclick="confirmarCancelacion(${res.reserva_id})">Cancelar Reserva</button>` 
                    : '';

                html += `
                <div class="card card-pro mb-3 ${res.estado === 'cancelada' ? 'opacity-75' : 'border-start border-4 border-primary'}">
                    <div class="card-pro-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <h6 class="fw-bold mb-1">${res.recurso}</h6>
                            <span class="badge ${badgeColor}">${res.estado}</span>
                        </div>
                        <p class="small text-muted mb-2">${res.fecha_inicio} al ${res.fecha_fin}</p>
                        <div class="d-flex justify-content-between align-items-end mt-2">
                            <div>
                                <span class="d-block small text-muted">Cantidad: ${res.cantidad}</span>
                                <span class="fw-bold text-dark">$${parseFloat(res.total).toLocaleString()}</span>
                            </div>
                            ${btnCancelar}
                        </div>
                    </div>
                </div>`;
            });
        }
        $contenedor.html(html);
    });
}

/**
 * Ejecuta la cancelación de una reserva
 */
function confirmarCancelacion(id) {
    //if (confirm("¿Deseas cancelar esta reserva? El inventario se liberará para otros usuarios de Xaratangato.")) {
    swal.fire({
        title: '¿Cancelar esta reserva?',
        text: "El inventario se liberará para otros usuarios de Xaratangato.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, cancelar',
        cancelButtonText: 'No, mantener'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post('cancelar_reserva.php', { id: id }, function(res) {
                if (res.success) {
                    cargarMisReservas();
                } else {
                    //alert(res.mensaje);
                    swal.fire({
                        title: 'Error',
                        text: "Error: " + res.mensaje,
                        icon: 'error',
                        confirmButtonText: 'Entendido'
                    });
                }
            }, 'json');
        }
    });
    
}