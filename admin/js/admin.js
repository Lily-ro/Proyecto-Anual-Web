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
 const days = ['Lun', 'Mar', 'Mie', 'Jue', 'Vie', 'Sab', 'Dom'];
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
drawNivelDiaChart();
drawNivelEdifChart();
drawConsumoChart();

// Redibujar graficos al cambiar tema
if (themeToggle) {
 themeToggle.addEventListener('click', () => {
  setTimeout(drawLineChart, 100);
  setTimeout(drawNivelDiaChart, 100);
  setTimeout(drawNivelEdifChart, 100);
  setTimeout(drawConsumoChart, 100);
 });
}

// LINE CHART - Nivel promedio de agua por dia
function drawNivelDiaChart() {
 const svg = document.getElementById('nivelDiaChart');
 if (!svg) return;
 const days = ['Lun', 'Mar', 'Mie', 'Jue', 'Vie', 'Sab', 'Dom'];
 const values = [72, 68, 75, 71, 82, 78, 76];
 const maxVal = 100;
 const w = 400, h = 180, padL = 35, padR = 15, padT = 15, padB = 35;
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
  html += `<text x="${padL - 6}" y="${y + 4}" text-anchor="end" fill="${textColor}" font-size="9" font-family="Inter,sans-serif">${maxVal - (maxVal / 4) * i}%</text>`;
 }
 const points = values.map((v, i) => {
  const x = padL + (chartW / (values.length - 1)) * i;
  const y = padT + chartH - (v / maxVal) * chartH;
  return { x, y };
 });
 const pathD = points.map((p, i) => `${i === 0 ? 'M' : 'L'}${p.x},${p.y}`).join(' ');
 html += `<defs><linearGradient id="nivelGrad" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="${lineColor}" stop-opacity="0.2"/><stop offset="100%" stop-color="${lineColor}" stop-opacity="0.02"/></linearGradient></defs>`;
 const areaD = pathD + ` L${points[points.length - 1].x},${padT + chartH} L${points[0].x},${padT + chartH} Z`;
 html += `<path d="${areaD}" fill="url(#nivelGrad)" opacity="0"/>`;
 html += `<path d="${pathD}" fill="none" stroke="${lineColor}" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" opacity="0"/>`;
 points.forEach((p, i) => {
  html += `<circle cx="${p.x}" cy="${p.y}" r="4" fill="${dotColor}" stroke="${isDark ? '#111c30' : '#fff'}" stroke-width="2" opacity="0"/>`;
  html += `<text x="${p.x}" y="${h - 10}" text-anchor="middle" fill="${textColor}" font-size="10" font-family="Inter,sans-serif">${days[i]}</text>`;
 });
 svg.innerHTML = html;
 setTimeout(() => {
  svg.querySelector('path[d^="M"]')?.setAttribute('opacity', '1');
  svg.querySelector('defs + path')?.setAttribute('opacity', '1');
  svg.querySelectorAll('circle[r="4"]').forEach(c => c.setAttribute('opacity', '1'));
 }, 100);
}

// HBAR CHART - Nivel promedio por edificio
function drawNivelEdifChart() {
 const svg = document.getElementById('nivelEdifChart');
 if (!svg) return;
 const edificios = [
  { name: 'Ed. Norte', pct: 82, color: '#4caf50' },
  { name: 'Ed. Centro', pct: 68, color: '#4fc3f7' },
  { name: 'Ed. Sur', pct: 55, color: '#ff9800' },
  { name: 'Ed. Este', pct: 91, color: '#4caf50' },
  { name: 'Ed. Oeste', pct: 38, color: '#f44336' }
 ];
 const isDark = !document.body.classList.contains('light-theme');
 const textColor = isDark ? '#7a829a' : '#6b7280';
 const trackColor = isDark ? '#1a2540' : '#e0e3e8';
 const w = 400, h = 180, padL = 80, padR = 50, padT = 10, padB = 10;
 const barH = 18, gap = (h - padT - padB - barH * edificios.length) / (edificios.length - 1);
 let html = '';
 edificios.forEach((d, i) => {
  const y = padT + i * (barH + gap);
  const barW = ((w - padL - padR) * d.pct) / 100;
  html += `<text x="${padL - 10}" y="${y + barH / 2 + 4}" text-anchor="end" fill="${textColor}" font-size="11" font-family="Inter,sans-serif">${d.name}</text>`;
  html += `<rect x="${padL}" y="${y}" width="${w - padL - padR}" height="${barH}" rx="9" fill="${trackColor}"/>`;
  html += `<rect x="${padL}" y="${y}" width="0" height="${barH}" rx="9" fill="${d.color}" opacity="0.85"/>`;
  html += `<text x="${padL + (w - padL - padR) + 8}" y="${y + barH / 2 + 4}" fill="${textColor}" font-size="11" font-weight="600" font-family="Inter,sans-serif" opacity="0">${d.pct}%</text>`;
 });
 svg.innerHTML = html;
 setTimeout(() => {
  const bars = svg.querySelectorAll('rect');
  const vals = svg.querySelectorAll('text[opacity="0"]');
  edificios.forEach((d, i) => {
   const barW = ((w - padL - padR) * d.pct) / 100;
   bars[i * 2 + 1].setAttribute('width', String(barW));
  });
  vals.forEach(v => v.setAttribute('opacity', '1'));
 }, 100);
}

// BAR CHART - Consumo semanal
function drawConsumoChart() {
 const svg = document.getElementById('consumoChart');
 if (!svg) return;
 const days = ['Lun', 'Mar', 'Mie', 'Jue', 'Vie', 'Sab', 'Dom'];
 const values = [120, 95, 140, 110, 155, 85, 130];
 const maxVal = 180;
 const w = 400, h = 180, padL = 35, padR = 15, padT = 15, padB = 35;
 const chartW = w - padL - padR, chartH = h - padT - padB;
 const isDark = !document.body.classList.contains('light-theme');
 const barColor = isDark ? '#2c6cef' : '#1565c0';
 const gridColor = isDark ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.08)';
 const textColor = isDark ? '#7a829a' : '#6b7280';
 const gap = chartW / days.length;
 const barW = gap * 0.5;
 let html = '';
 for (let i = 0; i <= 4; i++) {
  const y = padT + (chartH / 4) * i;
  html += `<line x1="${padL}" y1="${y}" x2="${w - padR}" y2="${y}" stroke="${gridColor}" stroke-width="1"/>`;
  html += `<text x="${padL - 6}" y="${y + 4}" text-anchor="end" fill="${textColor}" font-size="9" font-family="Inter,sans-serif">${Math.round(maxVal - (maxVal / 4) * i)}</text>`;
 }
 values.forEach((v, i) => {
  const x = padL + gap * i + gap / 2;
  const barH = (v / maxVal) * chartH;
  html += `<rect x="${x - barW / 2}" y="${padT + chartH}" width="${barW}" height="0" rx="4" fill="${barColor}" opacity="0.85"/>`;
  html += `<text x="${x}" y="${h - 10}" text-anchor="middle" fill="${textColor}" font-size="10" font-family="Inter,sans-serif">${days[i]}</text>`;
  html += `<text x="${x}" y="${padT + chartH - barH - 6}" text-anchor="middle" fill="${textColor}" font-size="9" font-weight="600" font-family="Inter,sans-serif" opacity="0">${v}L</text>`;
 });
 svg.innerHTML = html;
 setTimeout(() => {
  const bars = svg.querySelectorAll('rect');
  const labels = svg.querySelectorAll('text[opacity="0"]');
  values.forEach((v, i) => {
   const barH = (v / maxVal) * chartH;
   bars[i].setAttribute('y', String(padT + chartH - barH));
   bars[i].setAttribute('height', String(barH));
  });
  labels.forEach(l => l.setAttribute('opacity', '1'));
 }, 100);
}

// ============ AUDITORIAS ============
function filtrarAuditorias() {
 alert('Filtros aplicados (funcionalidad pendiente de backend)');
}
function limpiarFiltros() {
 document.getElementById('auditSearch').value = '';
 document.getElementById('auditDateFrom').value = '';
 document.getElementById('auditDateTo').value = '';
 document.getElementById('auditType').value = '';
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
 alert(id ? `Tanque ${id} actualizado (funcionalidad pendiente de backend)` : 'Tanque creado (funcionalidad pendiente de backend)');
 cerrarModalTanque();
}

function eliminarTanque(id) {
 if (confirm(`Esta seguro de eliminar el tanque ${id}?`)) {
  alert(`Tanque ${id} eliminado (funcionalidad pendiente de backend)`);
 }
}

// ============ COMPRAS ============
function aprobarCompra(id) {
 if (confirm(`Aprobar la compra ${id}?`)) {
  alert(`Compra ${id} aprobada (funcionalidad pendiente de backend)`);
 }
}

function cancelarCompra(id) {
 if (confirm(`Cancelar la compra ${id}? Esta accion no se puede deshacer.`)) {
  alert(`Compra ${id} cancelada (funcionalidad pendiente de backend)`);
 }
}

function cambiarEstado(id, nuevoEstado) {
 const estados = { 'en_camino': 'En entrega', 'completada': 'Completada' };
 if (confirm(`Cambiar estado de ${id} a "${estados[nuevoEstado]}"?`)) {
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
 if (!tipo) { alert('Selecciona un tipo de reporte'); return; }
 if (!desde || !hasta) { alert('Selecciona el rango de fechas'); return; }
 alert(`Generando reporte de "${tipo}"\nPeriodo: ${desde} al ${hasta}\nFormato: ${formato.toUpperCase()}\n\n(funcionalidad pendiente de backend)`);
}

drawBarChart();
drawRolesDonut();
drawDevicesDonut();

if (themeToggle) {
 themeToggle.addEventListener('click', () => {
  setTimeout(drawBarChart, 100);
  setTimeout(drawRolesDonut, 100);
  setTimeout(drawDevicesDonut, 100);
 });
}

// ============ USUARIOS - MODAL ============
function abrirModalUsuario() {
 document.getElementById('modalUsuarioTitle').textContent = 'Nuevo usuario';
 document.getElementById('usuarioId').value = '';
 document.getElementById('formUsuario').reset();
 document.getElementById('grupoContrasena').style.display = '';
 document.getElementById('modalUsuario').classList.add('active');
}

function editarUsuario(id) {
 document.getElementById('modalUsuarioTitle').textContent = 'Editar usuario';
 document.getElementById('usuarioId').value = id;
 document.getElementById('grupoContrasena').style.display = 'none';
 const usuarios = {
  1: { nombre: 'Carlos Méndez', email: 'carlos.mendez@eva.com', rol: 'Cliente', estado: 'Activo' },
  2: { nombre: 'Laura Gutiérrez', email: 'laura.gutierrez@eva.com', rol: 'Tecnico', estado: 'Activo' },
  3: { nombre: 'Roberto Silva', email: 'roberto.silva@eva.com', rol: 'Cliente', estado: 'Activo' },
  4: { nombre: 'Miguel Torres', email: 'miguel.torres@eva.com', rol: 'Tecnico', estado: 'Inactivo' },
  5: { nombre: 'Ana Rodríguez', email: 'ana.rodriguez@eva.com', rol: 'Cliente', estado: 'Activo' },
  6: { nombre: 'Pedro López', email: 'pedro.lopez@eva.com', rol: 'Cliente', estado: 'Inactivo' }
 };
 const u = usuarios[id];
 if (u) {
  document.getElementById('usuarioNombre').value = u.nombre;
  document.getElementById('usuarioEmail').value = u.email;
  document.getElementById('usuarioRol').value = u.rol;
  document.getElementById('usuarioEstado').value = u.estado;
 }
 document.getElementById('modalUsuario').classList.add('active');
}

function cerrarModalUsuario() {
 document.getElementById('modalUsuario').classList.remove('active');
}

function guardarUsuario() {
 const id = document.getElementById('usuarioId').value;
 const nombre = document.getElementById('usuarioNombre').value;
 const email = document.getElementById('usuarioEmail').value;
 const contrasena = document.getElementById('usuarioContrasena').value;
 const rol = document.getElementById('usuarioRol').value;
 if (!nombre || !email || !rol) { alert('Todos los campos son obligatorios'); return; }
 if (!id && !contrasena) { alert('La contraseña es obligatoria para nuevos usuarios'); return; }
 alert(id ? `Usuario ${id} actualizado (funcionalidad pendiente de backend)` : 'Usuario creado (funcionalidad pendiente de backend)');
 cerrarModalUsuario();
}

function eliminarUsuario(id) {
 if (confirm('¿Estás seguro de eliminar el usuario ' + id + '? Esta acción no se puede deshacer.')) {
  alert('Usuario ' + id + ' eliminado (funcionalidad pendiente de backend)');
 }
}

function cambiarContrasena(id) {
 document.getElementById('contrasenaUserId').value = id;
 document.getElementById('nuevaContrasena').value = '';
 document.getElementById('confirmarContrasena').value = '';
 document.getElementById('modalContrasena').classList.add('active');
}

function cerrarModalContrasena() {
 document.getElementById('modalContrasena').classList.remove('active');
}

function guardarContrasena() {
 const id = document.getElementById('contrasenaUserId').value;
 const nueva = document.getElementById('nuevaContrasena').value;
 const confirmar = document.getElementById('confirmarContrasena').value;
 if (!nueva || nueva.length < 6) { alert('La contraseña debe tener al menos 6 caracteres'); return; }
 if (nueva !== confirmar) { alert('Las contraseñas no coinciden'); return; }
 alert('Contraseña del usuario ' + id + ' actualizada (funcionalidad pendiente de backend)');
 cerrarModalContrasena();
}

function toggleEstado(id, estadoActual) {
 const nuevoEstado = estadoActual === 'Activo' ? 'desactivar' : 'activar';
 if (confirm('¿Deseas ' + nuevoEstado + ' al usuario ' + id + '?')) {
  alert('Usuario ' + id + ' ' + nuevoEstado + ' (funcionalidad pendiente de backend)');
 }
}

// ============ USUARIOS - FILTROS ============
(function() {
 const search = document.getElementById('userSearch');
 const filterRol = document.getElementById('filterRol');
 const filterEstado = document.getElementById('filterEstado');
 if (!search) return;
 function filtrarUsuarios() {
  const texto = search.value.toLowerCase();
  const rol = filterRol.value;
  const estado = filterEstado.value;
  const filas = document.querySelectorAll('#tablaUsuarios tbody tr');
  let count = 0;
  filas.forEach(function(fila) {
   const nombre = (fila.dataset.nombre || '').toLowerCase();
   const email = (fila.dataset.email || '').toLowerCase();
   const r = fila.dataset.rol;
   const e = fila.dataset.estado;
   const visible = (!texto || nombre.includes(texto) || email.includes(texto)) && (!rol || r === rol) && (!estado || e === estado);
   fila.style.display = visible ? '' : 'none';
   if (visible) count++;
  });
  document.getElementById('userCount').textContent = count;
 }
 search.addEventListener('input', filtrarUsuarios);
 filterRol.addEventListener('change', filtrarUsuarios);
 filterEstado.addEventListener('change', filtrarUsuarios);
})();
