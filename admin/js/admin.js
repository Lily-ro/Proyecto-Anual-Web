// TEMA
const themeToggle = document.getElementById('themeToggle');
let isLight = localStorage.getItem('eva-theme') === 'light';
if (themeToggle) {
 const iconSun = themeToggle.querySelector('.icon-sun'), iconMoon = themeToggle.querySelector('.icon-moon');
 function theme() { document.body.classList.toggle('light-theme', isLight); iconSun.classList.toggle('hidden', isLight); iconMoon.classList.toggle('hidden', !isLight); }
 theme();
 themeToggle.addEventListener('click', () => { isLight = !isLight; localStorage.setItem('eva-theme', isLight ? 'light' : 'dark'); theme(); });
}

// DROPDOWN USUARIO
const userDropdown = document.getElementById('userDropdown');
const userMenu = document.getElementById('userMenu');
if (userDropdown && userMenu) {
 userDropdown.querySelector('.user-info').addEventListener('click', (e) => { e.stopPropagation(); userMenu.classList.toggle('hidden'); });
 document.addEventListener('click', () => { userMenu.classList.add('hidden'); });
 userMenu.addEventListener('click', (e) => { e.stopPropagation(); });
}

// SIDEBAR SUBMENUS
document.querySelectorAll('.sidebar nav li[data-toggle]').forEach(li => {
 li.addEventListener('click', () => {
  li.classList.toggle('open');
  const sub = li.nextElementSibling;
  if (sub && sub.classList.contains('sub-menu')) sub.classList.toggle('open');
 });
});

// TABS
document.querySelectorAll('.tabs').forEach(tabsEl => {
 tabsEl.querySelectorAll('.tab').forEach(tab => {
  tab.addEventListener('click', () => {
   const tabId = tab.dataset.tab;
   const container = tabsEl.closest('.tabs-container');
   container.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
   container.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
   tab.classList.add('active');
   const target = container.querySelector('#tab-' + tabId);
   if (target) target.classList.add('active');
  });
 });
});

// DONUT CHART
function drawDonut() {
 const svg = document.getElementById('donutSvg');
 if (!svg) return;
 const data = [
  { pct: 71, color: '#4caf50', label: 'Activos', val: 30, pctText: '71%' },
  { pct: 19, color: '#ff9800', label: 'Advertencias', val: 8, pctText: '19%' },
  { pct: 4, color: '#f44336', label: 'Inactivos', val: 4, pctText: '4%' }
 ];
 const r = 50, c = 2 * Math.PI * r;
 let offset = 0;
 svg.innerHTML = `<circle cx="70" cy="70" r="${r}" class="donut-bg"/>`;
 data.forEach((d, i) => {
  const len = (d.pct / 100) * c;
  const gap = c - len;
  const circle = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
  circle.setAttribute('cx', '70');
  circle.setAttribute('cy', '70');
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

// LINE CHART
function drawLineChart() {
 const svg = document.getElementById('lineChart');
 if (!svg) return;
 const days = ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'];
 const values = [60, 50, 70, 65, 90, 70, 85];
 const maxVal = 100;
 const w = 320, h = 160, padL = 30, padR = 10, padT = 10, padB = 30;
 const chartW = w - padL - padR, chartH = h - padT - padB;

 const isDark = !document.body.classList.contains('light-theme');
 const lineColor = isDark ? '#4fc3f7' : '#1565c0';
 const dotColor = isDark ? '#4fc3f7' : '#1565c0';
 const gridColor = isDark ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.08)';
 const textColor = isDark ? '#7a829a' : '#6b7280';

 let html = '';

 for (let i = 0; i <= 4; i++) {
  const y = padT + (chartH / 4) * i;
  html += `<line x1="${padL}" y1="${y}" x2="${w - padR}" y2="${y}" stroke="${gridColor}" stroke-width="1"/>`;
 }

 const points = values.map((v, i) => {
  const x = padL + (chartW / (values.length - 1)) * i;
  const y = padT + chartH - (v / maxVal) * chartH;
  return { x, y };
 });

 const pathD = points.map((p, i) => `${i === 0 ? 'M' : 'L'}${p.x},${p.y}`).join(' ');
 html += `<path d="${pathD}" fill="none" stroke="${lineColor}" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" opacity="0"/>`;

 const areaD = pathD + ` L${points[points.length - 1].x},${padT + chartH} L${points[0].x},${padT + chartH} Z`;
 html += `<path d="${areaD}" fill="url(#grad)" opacity="0"/>`;
 html += `<defs><linearGradient id="grad" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="${lineColor}" stop-opacity="0.2"/><stop offset="100%" stop-color="${lineColor}" stop-opacity="0.02"/></linearGradient></defs>`;

 points.forEach((p, i) => {
  html += `<circle cx="${p.x}" cy="${p.y}" r="4" fill="${dotColor}" stroke="${isDark ? '#111c30' : '#fff'}" stroke-width="2" opacity="0"/>`;
  html += `<text x="${p.x}" y="${h - 8}" text-anchor="middle" fill="${textColor}" font-size="10" font-family="Inter,sans-serif">${days[i]}</text>`;
 });

 svg.innerHTML = html;

 setTimeout(() => {
  svg.querySelector('path[d^="M"]')?.setAttribute('opacity', '1');
  svg.querySelector('path[d^="M"] + path')?.setAttribute('opacity', '1');
  svg.querySelectorAll('circle[r="4"]').forEach(c => c.setAttribute('opacity', '1'));
 }, 100);
}

drawDonut();
drawLineChart();

// Redibujar gráfico de líneas al cambiar tema
if (themeToggle) {
 themeToggle.addEventListener('click', () => { setTimeout(drawLineChart, 100); });
}

// ============ AUDITORÍAS ============
function filtrarAuditorias() {
 // Placeholder: filtrar tabla de auditorías
 alert('Filtros aplicados (funcionalidad pendiente de backend)');
}
function limpiarFiltros() {
 document.getElementById('auditSearch').value = '';
 document.getElementById('auditDateFrom').value = '';
 document.getElementById('auditDateTo').value = '';
 document.getElementById('auditType').value = '';
}

// ============ TÉCNICOS - HISTORIAL ============
const historialData = {
 'T-001': {
  stats: { total: 12, completados: 9, enProgreso: 2, pendientes: 1, rendimiento: '75%' },
  trabajos: [
   { id: 'TR-001', tipo: 'Instalación', desc: 'Instalación sensor ultrasónico', ubicacion: 'Tanque Norte', fechaAsig: '01/06/2026', fechaComp: '03/06/2026', estado: 'completado', prioridad: 'alta' },
   { id: 'TR-002', tipo: 'Mantenimiento', desc: 'Limpieza de sensores', ubicacion: 'Tanque Centro', fechaAsig: '05/06/2026', fechaComp: '05/06/2026', estado: 'completado', prioridad: 'media' },
   { id: 'TR-003', tipo: 'Reparación', desc: 'Calibración sensor de presión', ubicacion: 'Tanque Sur', fechaAsig: '10/06/2026', fechaComp: '11/06/2026', estado: 'completado', prioridad: 'alta' },
   { id: 'TR-004', tipo: 'Instalación', desc: 'Instalación sensor de nivel', ubicacion: 'Tanque Oeste', fechaAsig: '15/06/2026', fechaComp: '', estado: 'en_progreso', prioridad: 'media' },
   { id: 'TR-005', tipo: 'Mantenimiento', desc: 'Revisión general del sistema', ubicacion: 'Tanque Norte', fechaAsig: '20/06/2026', fechaComp: '', estado: 'en_progreso', prioridad: 'baja' },
  ]
 },
 'T-002': {
  stats: { total: 8, completados: 6, enProgreso: 1, pendientes: 1, rendimiento: '75%' },
  trabajos: [
   { id: 'TR-006', tipo: 'Instalación', desc: 'Instalación medidor de flujo', ubicacion: 'Tanque Centro', fechaAsig: '02/06/2026', fechaComp: '04/06/2026', estado: 'completado', prioridad: 'alta' },
   { id: 'TR-007', tipo: 'Mantenimiento', desc: 'Calibración de flujo', ubicacion: 'Tanque Norte', fechaAsig: '08/06/2026', fechaComp: '08/06/2026', estado: 'completado', prioridad: 'media' },
   { id: 'TR-008', tipo: 'Reparación', desc: 'Reparación sensor de presión', ubicacion: 'Tanque Sur', fechaAsig: '12/06/2026', fechaComp: '', estado: 'en_progreso', prioridad: 'alta' },
   { id: 'TR-009', tipo: 'Instalación', desc: 'Instalación sensor temperatura', ubicacion: 'Tanque Este', fechaAsig: '18/06/2026', fechaComp: '', estado: 'asignado', prioridad: 'baja' },
  ]
 },
 'T-003': {
  stats: { total: 5, completados: 3, enProgreso: 0, pendientes: 2, rendimiento: '60%' },
  trabajos: [
   { id: 'TR-010', tipo: 'Instalación', desc: 'Instalación sistema EVA completo', ubicacion: 'Tanque Norte', fechaAsig: '01/05/2026', fechaComp: '05/05/2026', estado: 'completado', prioridad: 'alta' },
   { id: 'TR-011', tipo: 'Mantenimiento', desc: 'Mantenimiento preventivo', ubicacion: 'Tanque Centro', fechaAsig: '10/05/2026', fechaComp: '10/05/2026', estado: 'completado', prioridad: 'media' },
   { id: 'TR-012', tipo: 'Reparación', desc: 'Reparación cableado', ubicacion: 'Tanque Sur', fechaAsig: '15/05/2026', fechaComp: '', estado: 'asignado', prioridad: 'media' },
   { id: 'TR-013', tipo: 'Instalación', desc: 'Instalación sensor ultrasónico', ubicacion: 'Tanque Oeste', fechaAsig: '20/05/2026', fechaComp: '', estado: 'asignado', prioridad: 'baja' },
  ]
 },
 'T-004': {
  stats: { total: 15, completados: 12, enProgreso: 2, pendientes: 1, rendimiento: '80%' },
  trabajos: [
   { id: 'TR-014', tipo: 'Mantenimiento', desc: 'Mantenimiento general sensores', ubicacion: 'Tanque Norte', fechaAsig: '01/06/2026', fechaComp: '02/06/2026', estado: 'completado', prioridad: 'alta' },
   { id: 'TR-015', tipo: 'Instalación', desc: 'Instalación sensor de flujo', ubicacion: 'Tanque Centro', fechaAsig: '05/06/2026', fechaComp: '07/06/2026', estado: 'completado', prioridad: 'media' },
   { id: 'TR-016', tipo: 'Reparación', desc: 'Reparación sensor nivel', ubicacion: 'Tanque Sur', fechaAsig: '10/06/2026', fechaComp: '', estado: 'en_progreso', prioridad: 'alta' },
   { id: 'TR-017', tipo: 'Mantenimiento', desc: 'Calibración sistema completo', ubicacion: 'Tanque Oeste', fechaAsig: '14/06/2026', fechaComp: '', estado: 'en_progreso', prioridad: 'media' },
   { id: 'TR-018', tipo: 'Instalación', desc: 'Instalación sensor temperatura', ubicacion: 'Tanque Este', fechaAsig: '20/06/2026', fechaComp: '', estado: 'asignado', prioridad: 'baja' },
  ]
 }
};

function verHistorial(tecnicosId) {
 document.getElementById('tecnicoSelect').value = tecnicosId;
 cargarHistorial();
 // Switch to historial tab
 document.querySelectorAll('.tabs .tab').forEach(t => t.classList.remove('active'));
 document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
 document.querySelector('.tab[data-tab="historial"]').classList.add('active');
 document.getElementById('tab-historial').classList.add('active');
}

function cargarHistorial() {
 const id = document.getElementById('tecnicoSelect').value;
 const emptyState = document.getElementById('emptyHistorial');
 const statsDiv = document.getElementById('tecnicoStats');
 const tableDiv = document.getElementById('historialTable');
 const tbody = document.getElementById('historialBody');

 if (!id || !historialData[id]) {
  emptyState.style.display = 'flex';
  statsDiv.style.display = 'none';
  tableDiv.style.display = 'none';
  return;
 }

 const data = historialData[id];
 emptyState.style.display = 'none';
 statsDiv.style.display = 'grid';
 tableDiv.style.display = 'block';

 document.getElementById('statTotal').textContent = data.stats.total;
 document.getElementById('statCompletados').textContent = data.stats.completados;
 document.getElementById('statEnProgreso').textContent = data.stats.enProgreso;
 document.getElementById('statPendientes').textContent = data.stats.pendientes;
 document.getElementById('statRendimiento').textContent = data.stats.rendimiento;

 const estadoMap = {
  'asignado': '<span class="badge pendiente">Asignado</span>',
  'en_progreso': '<span class="badge en_camino">En progreso</span>',
  'completado': '<span class="badge completada">Completado</span>'
 };
 const prioridadMap = {
  'alta': '<span class="badge alta">Alta</span>',
  'media': '<span class="badge media">Media</span>',
  'baja': '<span class="badge baja">Baja</span>'
 };

 tbody.innerHTML = data.trabajos.map(t => `
  <tr>
   <td>${t.id}</td>
   <td>${t.tipo}</td>
   <td>${t.desc}</td>
   <td>${t.ubicacion}</td>
   <td>${t.fechaAsig}</td>
   <td>${t.fechaComp || '--'}</td>
   <td>${estadoMap[t.estado]}</td>
   <td>${prioridadMap[t.prioridad]}</td>
  </tr>
 `).join('');
}

// ============ TANQUES - MODAL ============
function abrirModalTanque() {
 document.getElementById('modalTanqueTitle').textContent = 'Agregar Tanque';
 document.getElementById('tanqueId').value = '';
 document.getElementById('formTanque').reset();
 document.getElementById('modalTanque').classList.add('active');
}

function editarTanque(id) {
 document.getElementById('modalTanqueTitle').textContent = 'Editar Tanque';
 document.getElementById('tanqueId').value = id;
 // Simular carga de datos
 const tanques = {
  'T-001': { nombre: 'Tanque Norte Principal', ubicacion: 'Zona Norte', capacidad: 50000, estado: 'activo', desc: '' },
  'T-002': { nombre: 'Tanque Centro', ubicacion: 'Zona Centro', capacidad: 35000, estado: 'activo', desc: '' },
  'T-003': { nombre: 'Tanque Sur', ubicacion: 'Zona Sur', capacidad: 40000, estado: 'activo', desc: '' },
  'T-004': { nombre: 'Tanque Este Reserva', ubicacion: 'Zona Este', capacidad: 20000, estado: 'inactivo', desc: '' },
  'T-005': { nombre: 'Tanque Oeste Industrial', ubicacion: 'Zona Oeste', capacidad: 60000, estado: 'activo', desc: '' },
 };
 const t = tanques[id];
 if (t) {
  document.getElementById('tanqueNombre').value = t.nombre;
  document.getElementById('tanqueUbicacion').value = t.ubicacion;
  document.getElementById('tanqueCapacidad').value = t.capacidad;
  document.getElementById('tanqueEstado').value = t.estado;
  document.getElementById('tanqueDescripcion').value = t.desc;
 }
 document.getElementById('modalTanque').classList.add('active');
}

function cerrarModalTanque() {
 document.getElementById('modalTanque').classList.remove('active');
}

function guardarTanque() {
 const id = document.getElementById('tanqueId').value;
 const nombre = document.getElementById('tanqueNombre').value;
 if (!nombre) { alert('El nombre es obligatorio'); return; }
 // Placeholder: enviar al backend
 alert(id ? `Tanque ${id} actualizado (funcionalidad pendiente de backend)` : 'Tanque creado (funcionalidad pendiente de backend)');
 cerrarModalTanque();
}

function eliminarTanque(id) {
 if (confirm(`¿Estás seguro de eliminar el tanque ${id}?`)) {
  alert(`Tanque ${id} eliminado (funcionalidad pendiente de backend)`);
 }
}

// ============ COMPRAS ============
function aprobarCompra(id) {
 if (confirm(`¿Aprobar la compra ${id}?`)) {
  alert(`Compra ${id} aprobada (funcionalidad pendiente de backend)`);
 }
}

function cancelarCompra(id) {
 if (confirm(`¿Cancelar la compra ${id}? Esta acción no se puede deshacer.`)) {
  alert(`Compra ${id} cancelada (funcionalidad pendiente de backend)`);
 }
}

function cambiarEstado(id, nuevoEstado) {
 const estados = { 'en_camino': 'En entrega', 'completada': 'Completada' };
 if (confirm(`¿Cambiar estado de ${id} a "${estados[nuevoEstado]}"?`)) {
  alert(`Compra ${id} actualizada a "${estados[nuevoEstado]}" (funcionalidad pendiente de backend)`);
 }
}

// ============ REPORTES ============
function drawBarChart() {
 const svg = document.getElementById('barChart');
 if (!svg) return;
 const w = 400, h = 200, padL = 35, padR = 10, padT = 10, padB = 30;
 const chartW = w - padL - padR, chartH = h - padT - padB;
 const days = 30;
 const isDark = !document.body.classList.contains('light-theme');
 const barColor = isDark ? '#2c6cef' : '#1565c0';
 const barColor2 = isDark ? '#4caf50' : '#2e7d32';
 const gridColor = isDark ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.08)';
 const textColor = isDark ? '#7a829a' : '#6b7280';
 const maxVal = 100;
 const data1 = Array.from({length: days}, () => Math.floor(Math.random() * 60 + 20));
 const data2 = Array.from({length: days}, () => Math.floor(Math.random() * 50 + 10));
 const barW = (chartW / days) * 0.35;
 const gap = chartW / days;
 let html = '';
 for (let i = 0; i <= 4; i++) {
  const y = padT + (chartH / 4) * i;
  html += `<line x1="${padL}" y1="${y}" x2="${w - padR}" y2="${y}" stroke="${gridColor}" stroke-width="1"/>`;
  html += `<text x="${padL - 6}" y="${y + 4}" text-anchor="end" fill="${textColor}" font-size="9" font-family="Inter,sans-serif">${Math.round(maxVal - (maxVal / 4) * i)}</text>`;
 }
 data1.forEach((v, i) => {
  const x = padL + gap * i + gap / 2;
  const barH1 = (v / maxVal) * chartH;
  const barH2 = (data2[i] / maxVal) * chartH;
  html += `<rect x="${x - barW - 1}" y="${padT + chartH - barH1}" width="${barW}" height="${barH1}" rx="2" fill="${barColor}" opacity="0.85"/>`;
  html += `<rect x="${x + 1}" y="${padT + chartH - barH2}" width="${barW}" height="${barH2}" rx="2" fill="${barColor2}" opacity="0.85"/>`;
  if (i % 5 === 0) {
   html += `<text x="${x}" y="${h - 8}" text-anchor="middle" fill="${textColor}" font-size="8" font-family="Inter,sans-serif">${i + 1}</text>`;
  }
 });
 svg.innerHTML = html;
 setTimeout(() => { svg.querySelectorAll('rect').forEach(r => r.setAttribute('opacity', '0.85')); }, 100);
}

function drawRolesDonut() {
 const svg = document.getElementById('rolesDonut');
 if (!svg) return;
 const data = [
  { pct: 9, color: '#2c6cef' },
  { pct: 25, color: '#4fc3f7' },
  { pct: 66, color: '#4caf50' }
 ];
 const r = 50, c = 2 * Math.PI * r;
 let offset = 0;
 svg.innerHTML = `<circle cx="70" cy="70" r="${r}" class="donut-bg"/>`;
 data.forEach((d, i) => {
  const len = (d.pct / 100) * c;
  const circle = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
  circle.setAttribute('cx', '70');
  circle.setAttribute('cy', '70');
  circle.setAttribute('r', String(r));
  circle.setAttribute('fill', 'none');
  circle.setAttribute('stroke', d.color);
  circle.setAttribute('stroke-width', '12');
  circle.setAttribute('stroke-linecap', 'round');
  circle.setAttribute('stroke-dasharray', `0 ${c}`);
  circle.setAttribute('stroke-dashoffset', String(-offset));
  circle.style.transition = `stroke-dasharray 1s ${i * 0.2}s cubic-bezier(.4,0,.2,1)`;
  svg.appendChild(circle);
  setTimeout(() => { circle.setAttribute('stroke-dasharray', `${len} ${c - len}`); }, 100);
  offset += len;
 });
}

function drawDevicesDonut() {
 const svg = document.getElementById('devicesDonut');
 if (!svg) return;
 const data = [
  { pct: 71, color: '#4caf50' },
  { pct: 19, color: '#ff9800' },
  { pct: 4, color: '#f44336' }
 ];
 const r = 50, c = 2 * Math.PI * r;
 let offset = 0;
 svg.innerHTML = `<circle cx="70" cy="70" r="${r}" class="donut-bg"/>`;
 data.forEach((d, i) => {
  const len = (d.pct / 100) * c;
  const circle = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
  circle.setAttribute('cx', '70');
  circle.setAttribute('cy', '70');
  circle.setAttribute('r', String(r));
  circle.setAttribute('fill', 'none');
  circle.setAttribute('stroke', d.color);
  circle.setAttribute('stroke-width', '12');
  circle.setAttribute('stroke-linecap', 'round');
  circle.setAttribute('stroke-dasharray', `0 ${c}`);
  circle.setAttribute('stroke-dashoffset', String(-offset));
  circle.style.transition = `stroke-dasharray 1s ${i * 0.2}s cubic-bezier(.4,0,.2,1)`;
  svg.appendChild(circle);
  setTimeout(() => { circle.setAttribute('stroke-dasharray', `${len} ${c - len}`); }, 100);
  offset += len;
 });
}

function generarReporte() {
 const tipo = document.getElementById('reportTipo').value;
 const desde = document.getElementById('reportDesde').value;
 const hasta = document.getElementById('reportHasta').value;
 const formato = document.querySelector('input[name="formato"]:checked')?.value;
 if (!tipo) { alert('Seleccioná un tipo de reporte'); return; }
 if (!desde || !hasta) { alert('Seleccioná el rango de fechas'); return; }
 alert(`Generando reporte de "${tipo}"\nPeríodo: ${desde} al ${hasta}\nFormato: ${formato.toUpperCase()}\n\n(funcionalidad pendiente de backend)`);
}

// Draw report charts on load
drawBarChart();
drawRolesDonut();
drawDevicesDonut();

if (themeToggle) {
 themeToggle.addEventListener('click', () => {
  setTimeout(drawLineChart, 100);
  setTimeout(drawBarChart, 100);
  setTimeout(drawRolesDonut, 100);
  setTimeout(drawDevicesDonut, 100);
 });
}
