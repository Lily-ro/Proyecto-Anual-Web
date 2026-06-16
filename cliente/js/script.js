// ====== ESTADO GLOBAL ======
const CAP = 4000;
let lvl = 78, tmp = 70;
const page = location.pathname.split('/').pop() || 'index.html';

// ====== ACTUALIZAR NIVEL DEL TANQUE ======
function tank(pct) {
 const e = document.getElementById('waterRect');
 if (!e) return;
 const m = 162, h = (pct / 100) * m, y = 34 + m - h;
 e.setAttribute('y', y);
 e.setAttribute('height', h);
 const s = document.getElementById('waterShine');
 if (s) s.setAttribute('y', y - 2);
 const p = document.getElementById('tankPercent');
 if (p) p.textContent = `${Math.round(pct)}%`;
 const v = document.getElementById('tankVolume');
 if (v) v.textContent = `${Math.round(CAP * pct / 100).toLocaleString('es-AR')} L`;
}

// ====== ACTUALIZAR MEDIDOR DE TEMPERATURA ======
function gauge(v) {
 const n = document.getElementById('gaugeNeedle'), a = document.getElementById('gaugeArc'), g = document.getElementById('gaugeValue');
 if (!n) return;
 const p = v / 100, ang = -90 + p * 180, l = p * 314;
 n.setAttribute('transform', `rotate(${ang} 130 140)`);
 if (a) a.setAttribute('stroke-dasharray', `${l} 314`);
 if (g) g.textContent = `${Math.round(v)}°`;
}

// ====== ESTADO DEL TANQUE ======
function status() {
 const e = document.getElementById('estadoText'), d = document.getElementById('estadoDesc');
 if (!e) return;
 if (lvl <= 10) { e.className = 'estado-text alert'; e.textContent = 'Crítico'; d.textContent = 'Nivel de agua peligrosamente bajo'; }
 else if (lvl >= 90) { e.className = 'estado-text warning'; e.textContent = 'Sobrecarga'; d.textContent = 'Nivel de agua por encima del máximo'; }
 else if (lvl <= 25) { e.className = 'estado-text warning'; e.textContent = 'Bajo'; d.textContent = 'Nivel de agua bajo, considerar recarga'; }
 else { e.className = 'estado-text'; e.textContent = 'Normal'; d.textContent = 'Todo funciona correctamente'; }
}

// ====== RELOJ ======
function clock() {
 const e = document.getElementById('lastUpdate');
 if (!e) return;
 const n = new Date();
 e.textContent = `Hoy: ${String(n.getHours()).padStart(2,'0')}:${String(n.getMinutes()).padStart(2,'0')}`;
}

// ====== GRAFICO DE BARRAS (vista Tanque) ======
const bd = [{year:'2012',bottom:100,top:60},{year:'2013',bottom:120,top:50},{year:'2014',bottom:110,top:70},{year:'2015',bottom:130,top:60},{year:'2016',bottom:90,top:50},{year:'2017',bottom:100,top:55},{year:'2018',bottom:115,top:60}];
function bars() {
 const c = document.getElementById('chartBars');
 if (!c) return;
 c.innerHTML = '';
 bd.forEach((d, i) => {
  const g = document.createElement('div'); g.className = 'chart-bar-group';
  const s = document.createElement('div'); s.className = 'bar-stack'; s.style.height = '0px';
  const t = document.createElement('div'); t.className = 'bar-top'; t.style.height = `${(d.top/250)*170}px`;
  const b = document.createElement('div'); b.className = 'bar-bottom'; b.style.height = `${(d.bottom/250)*170}px`;
  s.append(t, b);
  const l = document.createElement('div'); l.className = 'bar-label'; l.textContent = d.year;
  g.append(s, l); c.appendChild(g);
  setTimeout(() => { s.style.height = `${((d.bottom+d.top)/250)*170}px`; }, i * 100 + 200);
 });
}

// ====== GRAFICO DE LINEAS (vista Historial) ======
const hd = {
 semana: { values:[200,950,900,450,350,250,200,500,300,350], labels:['0','1','2','3','4','5','6','7','8','9'], avg:'1.234 L', avgSub:'12% vs la semana anterior', total:'8.750 L', totalSub:'', mayor:'Miércoles', mayorVal:'730 L', menor:'Sábado', menorVal:'3.120 L' },
 dia: { values:[80,120,90,60,40,30,50,200,350,500,450,380,420,500,350,280,300,450,500,400,300,200,150,100], labels:['00','01','02','03','04','05','06','07','08','09','10','11','12','13','14','15','16','17','18','19','20','21','22','23'], avg:'285 L', avgSub:'5% vs el día anterior', total:'6.840 L', totalSub:'Total del día', mayor:'13:00', mayorVal:'500 L', menor:'05:00', menorVal:'30 L' },
 mes: { values:[3200,2800,3500,4100,3800,2900,3100,3600,4000,3400,3000,3300,3700,4200,3900,2700,3000,3500,4000,3600,3200,2800,3100,3400,3800,4100,3500,2900,3200,3600], labels:Array.from({length:30},(_,i)=>`${i+1}`), avg:'3.450 L', avgSub:'8% vs el mes anterior', total:'103.500 L', totalSub:'Total del mes', mayor:'Dia 14', mayorVal:'4.200 L', menor:'Dia 16', menorVal:'2.700 L' },
 anio: { values:[35000,38000,42000,40000,45000,43000,48000,46000,50000,47000,44000,41000], labels:['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'], avg:'42.417 L', avgSub:'Promedio mensual', total:'509.000 L', totalSub:'Total del año', mayor:'Sepiembre', mayorVal:'50.000 L', menor:'Enero', menorVal:'35.000 L' }
};
function lines(p = 'semana') {
 const svg = document.getElementById('lineChartSvg');
 if (!svg) return;
 const d = hd[p];
 const L = 40, R = 20, T = 15, B = 35, w = 700 - L - R, h = 280 - T - B, max = Math.max(...d.values) * 1.15, n = d.values.length, sx = w / (n - 1);
 let html = '';
 for (let i = 0; i <= 5; i++) { const y = T + (h / 5) * i; html += `<line x1="${L}" y1="${y}" x2="${L + w}" y2="${y}" class="grid-line"/><text x="${L - 8}" y="${y + 4}" class="axis-label" text-anchor="end">${Math.round(max - (max / 5) * i)}</text>`; }
 const pts = d.values.map((v, i) => ({ x: L + i * sx, y: T + h - (v / max) * h }));
 html += `<path d="M${pts[0].x},${T + h} ${pts.map(p => `L${p.x},${p.y}`).join(' ')} L${pts[pts.length-1].x},${T + h} Z" fill="rgba(79,195,247,0.06)"/>`;
 html += `<path d="${pts.map((p,i)=>`${i?'L':'M'}${p.x},${p.y}`).join(' ')}" class="data-line" id="animatedLine"/>`;
 pts.forEach(p => { html += `<circle cx="${p.x}" cy="${p.y}" r="4.5" class="data-dot"/>`; });
 pts.forEach((p, i) => { if (n <= 14 || i % Math.ceil(n / 14) === 0) html += `<text x="${p.x}" y="${T + h + 20}" class="axis-label" text-anchor="middle">${d.labels[i]}</text>`; });
 svg.innerHTML = html;
 const line = document.getElementById('animatedLine');
 if (line) { const len = line.getTotalLength(); line.style.strokeDasharray = len; line.style.strokeDashoffset = len; line.style.transition = 'none'; requestAnimationFrame(() => { line.style.transition = 'stroke-dashoffset 1s cubic-bezier(0.4,0,0.2,1)'; line.style.strokeDashoffset = '0'; }); }
 ['statPromedio','statPromedioSub','statTotal','statTotalSub','statMayor','statMayorVal','statMenor','statMenorVal'].forEach((id, i) => { const el = document.getElementById(id); if (el) el.textContent = [d.avg, d.avgSub, d.total, d.totalSub, d.mayor, d.mayorVal, d.menor, d.menorVal][i]; });
}
document.querySelectorAll('.history-tab').forEach(t => t.addEventListener('click', () => { document.querySelectorAll('.history-tab').forEach(x => x.classList.remove('active')); t.classList.add('active'); lines(t.dataset.period); }));

// ====== ALERTAS ======
const ad = [
 { type:'danger', icon:'warning', title:'Nivel bajo', desc:'El nivel del agua esta por debajo del 20%', date:'Hoy 11:15', status:'activo' },
 { type:'warning', icon:'warning', title:'Nivel alto', desc:'El nivel del agua esta por encima del 90%', date:'Ayer 22:15', status:'activo' },
 { type:'info', icon:'info', title:'Sin conexión', desc:'El dispositivo no esta respondiendo', date:'11 de May', status:'resuelta' },
 { type:'success', icon:'check', title:'Nivel normal', desc:'El nivel del agua volvio a la normalidad', date:'11 de May 14:30', status:'resuelta' },
 { type:'success', icon:'check', title:'Nivel normal', desc:'El nivel del agua volvio a la normalidad', date:'10 de May 21:45', status:'resuelta' }
];
let af = 'activas';
function alertIcon(t) {
 if (t === 'warning') return `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>`;
 if (t === 'info') return `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>`;
 return `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>`;
}
function alertas() {
 const list = document.getElementById('alertasList');
 if (!list) return;
 list.innerHTML = '';
 ad.filter(a => af === 'todas' ? true : a.status === af.slice(0,-1)).forEach((a, i) => {
  const d = document.createElement('div'); d.className = 'alert-item'; d.style.animationDelay = `${i * 0.06}s`;
  const ic = a.icon === 'warning' ? (a.type === 'danger' ? 'danger-icon' : 'warning-icon') : a.icon === 'info' ? 'info-icon' : 'success-icon';
  d.innerHTML = `<div class="alert-icon ${ic}">${alertIcon(a.icon)}</div><div class="alert-content"><div class="alert-name">${a.title}</div><div class="alert-desc">${a.desc}</div></div><div class="alert-meta"><div class="alert-date">${a.date}</div><div class="alert-badge ${a.status}">${a.status === 'activo' ? 'Activo' : 'Resuelta'}</div></div>`;
  list.appendChild(d);
 });
}
document.querySelectorAll('.alertas-filter').forEach(b => b.addEventListener('click', () => { document.querySelectorAll('.alertas-filter').forEach(x => x.classList.remove('active')); b.classList.add('active'); af = b.dataset.filter; alertas(); }));

// ====== SLIDERS DE CONFIGURACION ======
const sL = document.getElementById('sliderLow'), sH = document.getElementById('sliderHigh');
function sliderFill(s) { if (!s) return; const p = ((s.value - s.min) / (s.max - s.min)) * 100; s.style.background = `linear-gradient(to right, #2c6cef 0%, #2c6cef ${p}%, #2a3042 ${p}%, #2a3042 100%)`; }
if (sL) { const sLv = document.getElementById('sliderLowVal'); sL.addEventListener('input', () => { sLv.textContent = `${sL.value} %`; sliderFill(sL); }); sliderFill(sL); }
if (sH) { const sHv = document.getElementById('sliderHighVal'); sH.addEventListener('input', () => { sHv.textContent = `${sH.value} %`; sliderFill(sH); }); sliderFill(sH); }

// ====== VISTA RESUMEN ======
function resumen() {
 const arc = document.getElementById('resumenGaugeArc');
 if (!arc) return;
 const pct = Math.round(lvl), a = (pct / 100) * 251, angle = -90 + (pct / 100) * 180;
 arc.setAttribute('stroke-dasharray', `${a} 251`);
 const n = document.getElementById('resumenNeedle');
 if (n) n.setAttribute('transform', `rotate(${angle} 100 110)`);
 const v = document.getElementById('resumenGaugeVal');
 if (v) v.textContent = pct;
 const e = document.getElementById('resumenEstado');
 if (e) {
  if (pct <= 20) { e.textContent = 'Crítico'; e.className = 'resumen-estado-value danger'; }
  else if (pct <= 40) { e.textContent = 'Bajo'; e.className = 'resumen-estado-value warning'; }
  else { e.textContent = 'Normal'; e.className = 'resumen-estado-value'; }
 }
 const t = document.getElementById('resumenTemp');
 if (t) t.textContent = `${Math.round(tmp)}°C`;
 const d = document.getElementById('resumenDisponible');
 if (d) d.textContent = `${Math.round(CAP * pct / 100).toLocaleString('es-AR')} L`;
 const c = document.getElementById('resumenConsumo');
 if (c) c.textContent = `${Math.round(CAP * (100 - pct) / 100).toLocaleString('es-AR')} L`;
 const p = document.getElementById('resumenPromedio');
 if (p) p.textContent = '1.250 L';
 rChart();
}
function rChart() {
 const svg = document.getElementById('resumenMiniChart');
 if (!svg) return;
 const data = [65,78,52,85,90,45,72], labels = ['L','M','X','J','V','S','D'];
 const W = 500, H = 170, L = 40, R = 20, T = 10, B = 30, gW = W - L - R, gH = H - T - B, max = 100;
 const pts = data.map((v, i) => ({ x: L + (i / (data.length - 1)) * gW, y: T + gH - (v / max) * gH }));
 const lt = document.body.classList.contains('light-theme'), gc = lt?'rgba(0,0,0,0.06)':'rgba(255,255,255,0.04)', tc = lt?'#6b7280':'#4a5068', cf = lt?'#fff':'#1a1f2e';
 let html = '';
 for (let i = 0; i <= 4; i++) { const y = T + (i / 4) * gH; html += `<line x1="${L}" y1="${y}" x2="${W - R}" y2="${y}" stroke="${gc}" stroke-width="1"/><text x="${L - 8}" y="${y + 4}" fill="${tc}" font-size="9" text-anchor="end" font-family="Inter,sans-serif">${Math.round(max - (i / 4) * max)}</text>`; }
 data.forEach((v, i) => { const x = L + (i / (data.length - 1)) * gW; html += `<text x="${x}" y="${H - 8}" fill="${tc}" font-size="9" text-anchor="middle" font-family="Inter,sans-serif">${labels[i]}</text>`; });
 const lp = pts.map((p, i) => `${i?'L':'M'}${p.x},${p.y}`).join(' ');
 html += `<path d="${lp} L${pts[pts.length-1].x},${T + gH} L${pts[0].x},${T + gH} Z" fill="url(#resumenAreaGrad)" opacity="0.3"/>`;
 html += `<path d="${lp}" fill="none" stroke="#42a5f5" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" stroke-dasharray="800" stroke-dashoffset="800"><animate attributeName="stroke-dashoffset" from="800" to="0" dur="1.2s" fill="freeze"/></path>`;
 pts.forEach(p => { html += `<circle cx="${p.x}" cy="${p.y}" r="3.5" fill="${cf}" stroke="#42a5f5" stroke-width="2"/>`; });
 html += `<defs><linearGradient id="resumenAreaGrad" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="#42a5f5" stop-opacity="0.4"/><stop offset="100%" stop-color="#42a5f5" stop-opacity="0"/></linearGradient></defs>`;
 svg.innerHTML = html;
}
const chartSelect = document.getElementById('resumenChartSelect');
if (chartSelect) chartSelect.addEventListener('change', rChart);

// ====== SIMULACION EN TIEMPO REAL ======
function simulate() {
 lvl = Math.max(0, Math.min(100, lvl + (Math.random() - 0.45) * 4));
 lvl = Math.round(lvl * 10) / 10;
 tmp = Math.max(10, Math.min(100, tmp + (Math.random() - 0.5) * 6));
 tmp = Math.round(tmp);
 if (page === 'mitanque.html') { tank(lvl); gauge(tmp); status(); clock(); }
 if (page === 'index.html') resumen();
}

// ====== CAMBIAR TEMA (oscuro/claro) ======
const themeToggle = document.getElementById('themeToggle');
if (themeToggle) {
 const iconSun = themeToggle.querySelector('.icon-sun'), iconMoon = themeToggle.querySelector('.icon-moon');
 let isLight = localStorage.getItem('eva-theme') === 'light';
 function theme() { document.body.classList.toggle('light-theme', isLight); iconSun.classList.toggle('hidden', isLight); iconMoon.classList.toggle('hidden', !isLight); }
 theme();
 themeToggle.addEventListener('click', () => { isLight = !isLight; localStorage.setItem('eva-theme', isLight ? 'light' : 'dark'); theme(); if (page === 'index.html') rChart(); });
}

// ====== DROPDOWN DE USUARIO ======
const userDropdown = document.getElementById('userDropdown');
const userMenu = document.getElementById('userMenu');
if (userDropdown && userMenu) {
 userDropdown.querySelector('.user-info').addEventListener('click', (e) => { e.stopPropagation(); userMenu.classList.toggle('hidden'); });
 document.addEventListener('click', () => { userMenu.classList.add('hidden'); });
 userMenu.addEventListener('click', (e) => { e.stopPropagation(); });
}

// ====== INICIALIZACION SEGUN PAGINA ======
if (page === 'index.html') { resumen(); setInterval(simulate, 3000); }
if (page === 'mitanque.html') { bars(); gauge(tmp); tank(lvl); clock(); setInterval(simulate, 3000); }
if (page === 'alertas.html') { alertas(); }
