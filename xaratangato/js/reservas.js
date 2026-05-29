// ============================================================
//  XARATANGATO - JS Reservas (Habitaciones)
// ============================================================

let precioNoche = 0;

function abrirModal(tipo, id, nombre, precio, unidad) {
  precioNoche = precio;
  document.getElementById('itemId').value = id;
  document.getElementById('modalTitulo').textContent = nombre;
  document.getElementById('modalSubtitulo').textContent =
    '$' + precio.toLocaleString('es-MX', {minimumFractionDigits:2}) + ' por ' + unidad;
  document.getElementById('resumenPrecio').style.display = 'none';
  document.getElementById('fechaInicio').value = '';
  document.getElementById('fechaFin').value    = '';
  document.getElementById('modalReserva').classList.add('open');
}

function cerrarModal() {
  document.getElementById('modalReserva').classList.remove('open');
}

function calcularTotal() {
  const fi = document.getElementById('fechaInicio').value;
  const ff = document.getElementById('fechaFin').value;

  if (!fi || !ff) return;

  const inicio = new Date(fi);
  const fin    = new Date(ff);
  const dias   = Math.round((fin - inicio) / (1000 * 60 * 60 * 24));

  if (dias <= 0) {
    document.getElementById('resumenPrecio').style.display = 'none';
    return;
  }

  const total = dias * precioNoche;
  document.getElementById('totalHidden').value = total;
  document.getElementById('resumenDetalle').textContent =
    dias + ' noche(s) × $' + precioNoche.toLocaleString('es-MX', {minimumFractionDigits:2});
  document.getElementById('resumenTotal').textContent =
    '$' + total.toLocaleString('es-MX', {minimumFractionDigits:2});
  document.getElementById('resumenPrecio').style.display = 'block';

  // Ajustar mínimo de fecha fin
  const minFin = new Date(inicio);
  minFin.setDate(minFin.getDate() + 1);
  document.getElementById('fechaFin').min = minFin.toISOString().split('T')[0];
}

function validarFechas() {
  const fi = document.getElementById('fechaInicio').value;
  const ff = document.getElementById('fechaFin').value;
  if (!fi || !ff) { alert('Por favor selecciona fechas de llegada y salida.'); return false; }
  const inicio = new Date(fi), fin = new Date(ff);
  if (fin <= inicio) { alert('La fecha de salida debe ser posterior a la de llegada.'); return false; }
  const total = document.getElementById('totalHidden').value;
  if (!total || total <= 0) { alert('Error calculando el total. Por favor recalcula.'); return false; }
  return true;
}

// Cerrar al hacer clic fuera del modal
document.addEventListener('DOMContentLoaded', function() {
  const overlay = document.getElementById('modalReserva');
  if (overlay) {
    overlay.addEventListener('click', function(e) {
      if (e.target === this) cerrarModal();
    });
  }
});
