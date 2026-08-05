// ESTADO GLOBAL 
const page = location.pathname.split('/').pop() || 'indextec.php';
let isLight = localStorage.getItem('eva-theme') === 'light';
const themeToggle = document.getElementById('themeToggle');
if (themeToggle) {
 const iconSun = themeToggle.querySelector('.icon-sun'), iconMoon = themeToggle.querySelector('.icon-moon');
 function theme() { document.body.classList.toggle('light-theme', isLight); iconSun.classList.toggle('hidden', isLight); iconMoon.classList.toggle('hidden', !isLight); }
 theme();
 themeToggle.addEventListener('click', () => { isLight = !isLight; localStorage.setItem('eva-theme', isLight ? 'light' : 'dark'); theme(); });
}
const userDropdown = document.getElementById('userDropdown');
const userMenu = document.getElementById('userMenu');
if (userDropdown && userMenu) {
 userDropdown.querySelector('.user-info').addEventListener('click', (e) => { e.stopPropagation(); userMenu.classList.toggle('hidden'); });
 document.addEventListener('click', () => { userMenu.classList.add('hidden'); });
 userMenu.addEventListener('click', (e) => { e.stopPropagation(); });
}
document.querySelectorAll('.sidebar nav li[data-toggle]').forEach(li => {
 li.addEventListener('click', () => {
  li.classList.toggle('open');
  const sub = li.nextElementSibling;
  if (sub && sub.classList.contains('sub-menu')) sub.classList.toggle('open');
 });
});
function drawDonut() {
 const svg = document.getElementById('donutSvg');
 if (!svg) return;
 const data = [
  { pct: 75, color: '#4caf50', label: 'Activos', val: 18, pctText: '75%' },
  { pct: 17, color: '#ff9800', label: 'Advertencias', val: 4, pctText: '17%' },
  { pct: 8, color: '#f44336', label: 'Inactivos', val: 2, pctText: '8%' }
 ];
 const r = 60, c = 2 * Math.PI * r;
 let offset = 0;
 svg.innerHTML = `<circle cx="80" cy="80" r="${r}" class="donut-bg"/>`;
 data.forEach((d, i) => {
  const len = (d.pct / 100) * c;
  const gap = c - len;
  const circle = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
  circle.setAttribute('cx', '80');
  circle.setAttribute('cy', '80');
  circle.setAttribute('r', String(r));
  circle.setAttribute('fill', 'none');
  circle.setAttribute('stroke', d.color);
  circle.setAttribute('stroke-width', '12');
  circle.setAttribute('stroke-linecap', 'round');
  circle.setAttribute('stroke-dasharray', `0 ${c}`);
  circle.setAttribute('stroke-dashoffset', String(-offset));
  circle.style.transition = `stroke-dasharray 1s ${i * 0.2}s cubic-bezier(.4,0,.2,1)`;
  svg.appendChild(circle);
  setTimeout(() => { circle.setAttribute('stroke-dasharray', `${len} ${gap}`); }, 100);
  offset += len;
 });
}
if (page === 'indextec.php') drawDonut();
if (page === 'mediciones.php') graficarMediciones();

// ====== FUNCIONES: MIS DISPOSITIVOS ======
function cambiarEstado(select, nombre) {
 if (!select.value) return;
 const card = select.closest('.dispositivo-card');
 const badge = card.querySelector('.badge');
 const estados = { mantenimiento: 'pendiente', reparado: 'programado', 'fuera-servicio': 'danger' };
 const textos = { mantenimiento: 'En mantenimiento', reparado: 'Reparado', 'fuera-servicio': 'Fuera de servicio' };
 badge.className = 'badge ' + (estados[select.value] || 'completado');
 badge.textContent = textos[select.value] || 'Operativo';
 card.dataset.estado = select.value;
}

function guardarObservaciones(btn) {
 const card = btn.closest('.dispositivo-observaciones');
 const textarea = card.querySelector('textarea');
 if (textarea.value.trim()) {
  textarea.value = '';
  textarea.placeholder = 'Observaciones guardadas correctamente...';
  setTimeout(() => { textarea.placeholder = 'Agregar observaciones tecnicas...'; }, 2000);
 }
}

function abrirHistorial(nombre) {
 document.getElementById('modalHistorialTitulo').textContent = 'Historial: ' + nombre;
 document.getElementById('modalHistorial').classList.remove('hidden');
}

function abrirMediciones(nombre) {
 window.location.href = 'mediciones.php';
}

function cerrarModal(id) {
 document.getElementById(id).classList.add('hidden');
}

// Buscar dispositivos
const buscarDisp = document.getElementById('buscarDispositivos');
if (buscarDisp) {
 buscarDisp.addEventListener('input', function() {
  const q = this.value.toLowerCase();
  document.querySelectorAll('.dispositivo-card').forEach(c => {
   c.style.display = c.textContent.toLowerCase().includes(q) ? '' : 'none';
  });
 });
}

// Filtrar dispositivos por estado
const filtrarEst = document.getElementById('filtrarEstado');
if (filtrarEst) {
 filtrarEst.addEventListener('change', function() {
  const v = this.value;
  document.querySelectorAll('.dispositivo-card').forEach(c => {
   c.style.display = (!v || c.dataset.estado === v) ? '' : 'none';
  });
 });
}

// ====== FUNCIONES: ALERTAS ======
function abrirDetalleAlerta(btn) {
 const tr = btn.closest('tr');
 document.getElementById('detalleAlertaTipo').textContent = tr.cells[0].textContent;
 document.getElementById('detalleAlertaFecha').textContent = tr.cells[1].textContent;
 document.getElementById('detalleAlertaTanque').textContent = tr.cells[2].textContent;
 document.getElementById('detalleAlertaPrioridad').innerHTML = tr.cells[3].innerHTML;
 document.getElementById('modalAlerta').classList.remove('hidden');
}

function marcarAtendida() {
 const comentario = document.getElementById('comentarioTecnico').value;
 const solucion = document.getElementById('solucionAplicada').value;
 if (!comentario.trim() && !solucion.trim()) {
  document.getElementById('comentarioTecnico').style.borderColor = 'var(--rd)';
  return;
 }
 document.getElementById('comentarioTecnico').style.borderColor = '';
 cerrarModal('modalAlerta');
 document.getElementById('comentarioTecnico').value = '';
 document.getElementById('solucionAplicada').value = '';
}

// Filtrar alertas
const filtrarPrio = document.getElementById('filtrarPrioridad');
const filtrarEstAlerta = document.getElementById('filtrarEstadoAlerta');
function filtrarAlertas() {
 const prio = filtrarPrio ? filtrarPrio.value : '';
 const est = filtrarEstAlerta ? filtrarEstAlerta.value : '';
 document.querySelectorAll('#tablaAlertas tbody tr').forEach(tr => {
  const mp = !prio || tr.dataset.prioridad === prio;
  const me = !est || tr.dataset.estado === est;
  tr.style.display = (mp && me) ? '' : 'none';
 });
}
if (filtrarPrio) filtrarPrio.addEventListener('change', filtrarAlertas);
if (filtrarEstAlerta) filtrarEstAlerta.addEventListener('change', filtrarAlertas);

// ====== FUNCIONES: MEDICIONES ======
function graficarMediciones() {
 const canvas = document.getElementById('graficoNivel');
 if (!canvas) return;
 const ctx = canvas.getContext('2d');
 const labels = ['06:00','07:00','08:00','09:00','10:00','11:00','12:00','13:00','14:00'];
 const datos = [65, 68, 71, 73, 75, 74, 72, 70, 73];
 new Chart(ctx, {
  type: 'line',
  data: {
   labels: labels,
   datasets: [{
    label: 'Nivel %',
    data: datos,
    borderColor: '#2c6cef',
    backgroundColor: 'rgba(44,108,239,0.1)',
    fill: true,
    tension: 0.4,
    pointRadius: 4,
    pointBackgroundColor: '#2c6cef'
   }]
  },
  options: {
   responsive: true,
   maintainAspectRatio: false,
   plugins: { legend: { display: false } },
   scales: {
    x: { grid: { color: 'rgba(255,255,255,0.04)' }, ticks: { color: '#7a829a', font: { size: 11 } } },
    y: { min: 0, max: 100, grid: { color: 'rgba(255,255,255,0.04)' }, ticks: { color: '#7a829a', font: { size: 11 }, callback: v => v + '%' } }
   }
  }
 });
}

// Buscar mediciones
const buscarMed = document.getElementById('buscarMediciones');
if (buscarMed) {
 buscarMed.addEventListener('input', function() {
  const q = this.value.toLowerCase();
  document.querySelectorAll('#tablaMediciones tbody tr').forEach(tr => {
   tr.style.display = tr.textContent.toLowerCase().includes(q) ? '' : 'none';
  });
 });
}

// Filtrar mediciones por dispositivo
const filtrarDev = document.getElementById('filtrarDispositivo');
if (filtrarDev) {
 filtrarDev.addEventListener('change', function() {
  const v = this.value;
  document.querySelectorAll('#tablaMediciones tbody tr').forEach(tr => {
   tr.style.display = (!v || tr.cells[2].textContent === v) ? '' : 'none';
  });
 });
}

function exportarMediciones(tipo) {
 alert('Exportando mediciones como ' + tipo.toUpperCase() + '...');
}

// ====== FUNCIONES: NOTIFICACIONES ======
function marcarLeida(btn) {
 const item = btn.closest('.notificacion-item');
 item.classList.remove('no-leida');
 item.classList.add('leida');
 btn.textContent = 'Leida';
 btn.disabled = true;
}

function marcarTodasLeidas() {
 document.querySelectorAll('.notificacion-item.no-leida').forEach(item => {
  item.classList.remove('no-leida');
  item.classList.add('leida');
  const btn = item.querySelector('.btn');
  if (btn) { btn.textContent = 'Leida'; btn.disabled = true; }
 });
}

// Buscar notificaciones
const buscarNotif = document.getElementById('buscarNotificaciones');
if (buscarNotif) {
 buscarNotif.addEventListener('input', function() {
  const q = this.value.toLowerCase();
  document.querySelectorAll('.notificacion-item').forEach(item => {
   item.style.display = item.textContent.toLowerCase().includes(q) ? '' : 'none';
  });
 });
}

// ====== FUNCIONES: MIS TANQUES ======
function consultarNivelTanque(nombre) {
 document.getElementById('modalNivelTanqueTitulo').textContent = 'Nivel: ' + nombre;
 document.getElementById('modalNivelTanque').classList.remove('hidden');
}

function abrirHistorialTanque(nombre) {
 document.getElementById('modalHistorialTanqueTitulo').textContent = 'Historial: ' + nombre;
 document.getElementById('modalHistorialTanque').classList.remove('hidden');
}

function verDispositivoTanque(nombre) {
 document.getElementById('modalDispositivoTanqueTitulo').textContent = 'Dispositivo en ' + nombre;
 document.getElementById('modalDispositivoTanque').classList.remove('hidden');
}

function verSensorTanque(nombre) {
 document.getElementById('modalSensorTanqueTitulo').textContent = 'Sensor en ' + nombre;
 document.getElementById('modalSensorTanque').classList.remove('hidden');
}

// Buscar tanques
const buscarTanques = document.getElementById('buscarTanques');
if (buscarTanques) {
 buscarTanques.addEventListener('input', function() {
  const q = this.value.toLowerCase();
  document.querySelectorAll('.dispositivo-card').forEach(c => {
   c.style.display = c.textContent.toLowerCase().includes(q) ? '' : 'none';
  });
 });
}

// Filtrar tanques por estado
const filtrarEstTanque = document.getElementById('filtrarEstadoTanque');
if (filtrarEstTanque) {
 filtrarEstTanque.addEventListener('change', function() {
  const v = this.value;
  document.querySelectorAll('.dispositivo-card').forEach(c => {
   c.style.display = (!v || c.dataset.estado === v) ? '' : 'none';
  });
 });
}

// ====== FUNCIONES: SENSORES ======
function registrarCalibracion(nombre) {
 document.getElementById('modalCalibrarTitulo').textContent = 'Calibrar: ' + nombre;
 document.getElementById('modalCalibrar').classList.remove('hidden');
}

function guardarCalibracion() {
 const fecha = document.getElementById('fechaCalibracion').value;
 const notas = document.getElementById('notasCalibracion').value;
 if (!fecha) {
  document.getElementById('fechaCalibracion').style.borderColor = 'var(--rd)';
  return;
 }
 document.getElementById('fechaCalibracion').style.borderColor = '';
 cerrarModal('modalCalibrar');
 document.getElementById('fechaCalibracion').value = '';
 document.getElementById('notasCalibracion').value = '';
}

function marcarReparado(nombre) {
 const card = document.querySelector('[data-nombre="' + nombre + '"]');
 if (card) {
  const badge = card.querySelector('.badge');
  badge.className = 'badge completado';
  badge.textContent = 'Operativo';
  card.dataset.estado = 'operativo';
 }
}

function marcarDefectuoso(nombre) {
 const card = document.querySelector('[data-nombre="' + nombre + '"]');
 if (card) {
  const badge = card.querySelector('.badge');
  badge.className = 'badge danger';
  badge.textContent = 'Defectuoso';
  card.dataset.estado = 'defectuoso';
 }
}

function agregarObservacionSensor(btn) {
 const card = btn.closest('.sensor-card-footer');
 const textarea = card.querySelector('textarea');
 if (textarea && textarea.value.trim()) {
  textarea.value = '';
  textarea.placeholder = 'Observaciones guardadas...';
  setTimeout(() => { textarea.placeholder = 'Agregar observaciones...'; }, 2000);
 }
}

// Buscar sensores
const buscarSensores = document.getElementById('buscarSensores');
if (buscarSensores) {
 buscarSensores.addEventListener('input', function() {
  const q = this.value.toLowerCase();
  document.querySelectorAll('.sensor-card').forEach(c => {
   c.style.display = c.textContent.toLowerCase().includes(q) ? '' : 'none';
  });
 });
}

// Filtrar sensores por estado
const filtrarEstSensor = document.getElementById('filtrarEstadoSensor');
if (filtrarEstSensor) {
 filtrarEstSensor.addEventListener('change', function() {
  const v = this.value;
  document.querySelectorAll('.sensor-card').forEach(c => {
   c.style.display = (!v || c.dataset.estado === v) ? '' : 'none';
  });
 });
}

// ====== FUNCIONES: MANTENIMIENTOS ======
function toggleMantCard(header) {
 const card = header.closest('.mant-card');
 card.classList.toggle('open');
}

function iniciarMant(nombre) {
 const card = document.querySelector('.mant-card [data-nombre="' + nombre + '"]');
 if (!card) return;
 const mantCard = card.closest('.mant-card');
 const badge = mantCard.querySelector('.badge');
 badge.className = 'badge pendiente';
 badge.textContent = 'En progreso';
 mantCard.dataset.estado = 'en-progreso';
}

function pausarMant(nombre) {
 const card = document.querySelector('.mant-card [data-nombre="' + nombre + '"]');
 if (!card) return;
 const mantCard = card.closest('.mant-card');
 const badge = mantCard.querySelector('.badge');
 badge.className = 'badge programado';
 badge.textContent = 'Pausado';
 mantCard.dataset.estado = 'pausado';
}

function finalizarMant(nombre) {
 const card = document.querySelector('.mant-card [data-nombre="' + nombre + '"]');
 if (!card) return;
 const mantCard = card.closest('.mant-card');
 const badge = mantCard.querySelector('.badge');
 badge.className = 'badge completado';
 badge.textContent = 'Completado';
 mantCard.dataset.estado = 'completado';
}

function abrirObservacionesMant(nombre) {
 document.getElementById('modalObservacionesMant').classList.remove('hidden');
}

function guardarObservacionesMant() {
 const textarea = document.getElementById('textoObservacionesMant');
 if (textarea && textarea.value.trim()) {
  textarea.value = '';
  cerrarModal('modalObservacionesMant');
 }
}

function guardarRegistroMant(btn) {
 const registros = btn.closest('.mant-registros');
 const inputs = registros.querySelectorAll('input, select');
 let valid = true;
 inputs.forEach(inp => {
  if (inp.required && !inp.value) { inp.style.borderColor = 'var(--rd)'; valid = false; }
  else { inp.style.borderColor = ''; }
 });
 btn.textContent = 'Guardado';
 btn.disabled = true;
 setTimeout(() => { btn.textContent = 'Guardar registro'; btn.disabled = false; }, 2000);
}

// Buscar mantenimientos
const buscarMant = document.getElementById('buscarMantenimientos');
if (buscarMant) {
 buscarMant.addEventListener('input', function() {
  const q = this.value.toLowerCase();
  document.querySelectorAll('.mant-card').forEach(c => {
   c.style.display = c.textContent.toLowerCase().includes(q) ? '' : 'none';
  });
 });
}

// Filtrar mantenimientos
const filtrarEstMant = document.getElementById('filtrarEstadoMant');
const filtrarTipoMant = document.getElementById('filtrarTipoMant');
function filtrarMantenimientos() {
 const est = filtrarEstMant ? filtrarEstMant.value : '';
 const tipo = filtrarTipoMant ? filtrarTipoMant.value : '';
 document.querySelectorAll('.mant-card').forEach(c => {
  const me = !est || c.dataset.estado === est;
  const mt = !tipo || c.dataset.tipo === tipo;
  c.style.display = (me && mt) ? '' : 'none';
 });
}
if (filtrarEstMant) filtrarEstMant.addEventListener('change', filtrarMantenimientos);
if (filtrarTipoMant) filtrarTipoMant.addEventListener('change', filtrarMantenimientos);

// ====== FUNCIONES: INSTALACIONES ======
function toggleInstCard(header) {
 const card = header.closest('.inst-card');
 card.classList.toggle('open');
}

function iniciarInst(nombre) {
 const card = document.querySelector('.inst-card [data-nombre="' + nombre + '"]');
 if (!card) return;
 const instCard = card.closest('.inst-card');
 const badge = instCard.querySelector('.badge');
 badge.className = 'badge pendiente';
 badge.textContent = 'En progreso';
 instCard.dataset.estado = 'en-progreso';
}

function finalizarInst(nombre) {
 const card = document.querySelector('.inst-card [data-nombre="' + nombre + '"]');
 if (!card) return;
 const instCard = card.closest('.inst-card');
 const badge = instCard.querySelector('.badge');
 badge.className = 'badge completado';
 badge.textContent = 'Completada';
 instCard.dataset.estado = 'completada';
}

function abrirObservacionesInst(nombre) {
 document.getElementById('modalObservacionesInst').classList.remove('hidden');
}

function guardarObservacionesInst() {
 const textarea = document.getElementById('textoObservacionesInst');
 if (textarea && textarea.value.trim()) {
  textarea.value = '';
  cerrarModal('modalObservacionesInst');
 }
}

function guardarRegistroInst(btn) {
 const registros = btn.closest('.inst-registros');
 const inputs = registros.querySelectorAll('input, select');
 let valid = true;
 inputs.forEach(inp => {
  if (inp.required && !inp.value) { inp.style.borderColor = 'var(--rd)'; valid = false; }
  else { inp.style.borderColor = ''; }
 });
 btn.textContent = 'Guardado';
 btn.disabled = true;
 setTimeout(() => { btn.textContent = 'Guardar registro'; btn.disabled = false; }, 2000);
}

// Buscar instalaciones
const buscarInst = document.getElementById('buscarInstalaciones');
if (buscarInst) {
 buscarInst.addEventListener('input', function() {
  const q = this.value.toLowerCase();
  document.querySelectorAll('.inst-card').forEach(c => {
   c.style.display = c.textContent.toLowerCase().includes(q) ? '' : 'none';
  });
 });
}

// Filtrar instalaciones por estado
const filtrarEstInst = document.getElementById('filtrarEstadoInst');
if (filtrarEstInst) {
 filtrarEstInst.addEventListener('change', function() {
  const v = this.value;
  document.querySelectorAll('.inst-card').forEach(c => {
   c.style.display = (!v || c.dataset.estado === v) ? '' : 'none';
  });
 });
}
