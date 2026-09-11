let CAP = 0;
let lvl = 0, tmp = 0;
const page = location.pathname.split('/').pop() || 'indexcli.php';

if (typeof window.EVA_RESUMEN !== 'undefined' && window.EVA_RESUMEN) {
  const c = parseInt(window.EVA_RESUMEN.capacidad);
  if (!isNaN(c)) CAP = c;
  const p = parseInt(window.EVA_RESUMEN.pct);
  if (!isNaN(p)) lvl = p;
  const t = parseInt(window.EVA_RESUMEN.temp);
  if (!isNaN(t)) tmp = t;
}
if (typeof window.EVA_TANQUE !== 'undefined' && window.EVA_TANQUE) {
  const c2 = parseInt(window.EVA_TANQUE.capacidad);
  if (!isNaN(c2)) CAP = c2;
  const p2 = parseInt(window.EVA_TANQUE.pct);
  if (!isNaN(p2)) lvl = p2;
  const t2 = parseInt(window.EVA_TANQUE.temp);
  if (!isNaN(t2)) tmp = t2;
}

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

function gauge(v) {
 const n = document.getElementById('gaugeNeedle'), a = document.getElementById('gaugeArc'), g = document.getElementById('gaugeValue');
 if (!n) return;
 const p = v / 100, ang = -90 + p * 180, l = p * 314;
 n.setAttribute('transform', `rotate(${ang} 130 140)`);
 if (a) a.setAttribute('stroke-dasharray', `${l} 314`);
 if (g) g.textContent = `${Math.round(v)}Â°`;
}

function evaEstadoFromPct(pct){
 pct=Math.max(0,Math.min(100,Math.round(pct)));
 if(pct>=100) return ['Completo','Tanque al 100% de capacidad',''];
 if(pct>=80) return ['Alto','Nivel alto','warning'];
 if(pct>=40) return ['Normal','Todo funciona correctamente',''];
 if(pct>=20) return ['Bajo','Nivel de agua bajo, considerar recarga','warning'];
 return ['CrÃ­tico','Nivel de agua peligrosamente bajo','alert'];
}
function status() {
 const e = document.getElementById('estadoText'), d = document.getElementById('estadoDesc');
 if (!e) return;
 const [txt,desc,cls]=evaEstadoFromPct(lvl);
 e.className='estado-text'+(cls?' '+cls:''); e.textContent=txt; if(d) d.textContent=desc;
}

function clock() {
 const e = document.getElementById('lastUpdate');
 if (!e) return;
 
 if (e.textContent && e.textContent.indexOf('--:--') === -1 && e.textContent.indexOf('/') !== -1) return;
 const n = new Date();
 e.textContent = `Hoy: ${String(n.getHours()).padStart(2,'0')}:${String(n.getMinutes()).padStart(2,'0')}`;
}

let bd = [];
if (typeof window.EVA_TANQUE !== 'undefined' && window.EVA_TANQUE && Array.isArray(window.EVA_TANQUE.barsData) && window.EVA_TANQUE.barsData.length > 0) {
  bd = window.EVA_TANQUE.barsData;
}
function bars() {
 const c = document.getElementById('chartBars');
 if (!c) return;
 c.innerHTML = '';
 if (!bd || bd.length === 0) {
   c.innerHTML = '<div style="padding:24px;text-align:center;color:var(--tx4);font-size:13px;width:100%">No hay datos disponibles</div>';
   return;
 }
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

const hd = {};
function lines(p = 'semana') {
 const svg = document.getElementById('lineChartSvg');
 if (!svg) return;
 const d = hd[p];
 if (!d || !d.values || d.values.length === 0) {
   svg.innerHTML = '<text x="350" y="140" text-anchor="middle" fill="var(--tx4)" font-size="13">No hay datos disponibles</text>';
   return;
 }
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

let ad = [];
if (typeof window.EVA_ALERTAS !== 'undefined' && Array.isArray(window.EVA_ALERTAS) && window.EVA_ALERTAS.length > 0) {
  ad = window.EVA_ALERTAS;
}
let af = 'activas';
if (typeof window.EVA_ALERTAS_FILTER !== 'undefined' && window.EVA_ALERTAS_FILTER) {
  af = window.EVA_ALERTAS_FILTER;
  
  setTimeout(()=> {
    document.querySelectorAll('.alertas-filter').forEach(x=> x.classList.toggle('active', x.dataset.filter===af));
  }, 0);
}
function alertIcon(t) {
 if (t === 'warning') return `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>`;
 if (t === 'info') return `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>`;
 return `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>`;
}
function resolverAlerta(id, btn){
 if(!id) return;
 if(btn) { btn.disabled=true; btn.textContent='Resolviendo...'; }
 fetch('api/resolver_alerta.php', {method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({id_alerta:id})})
 .then(async r=>{
   const txt=await r.text();
   if(!txt){ throw new Error('Respuesta vacía del servidor (HTTP '+r.status+')'); }
   let j=null;
   try{ j=JSON.parse(txt); }catch(e){ throw new Error('Respuesta no válida ('+r.status+'): '+txt.substring(0,300)); }
   if(!r.ok) throw new Error(j.error||('HTTP '+r.status));
   return j;
 }).then(j=>{
   if(j && j.ok){
     const idx=ad.findIndex(x=>x.id===id);
     if(idx>-1){ ad[idx].status='resuelta'; ad[idx].estadoRaw='CERRADA'; }
     alertas();
     if(typeof evaPollEstado==='function') setTimeout(evaPollEstado, 300);
   } else {
     alert(j.error || 'No se pudo resolver');
     if(btn){ btn.disabled=false; btn.textContent='Resolver'; }
   }
 }).catch(e=>{ alert(e.message||'Error de conexión'); if(btn){ btn.disabled=false; btn.textContent='Resolver'; } console.error('resolver',e); });
}
function alertas() {
 const list = document.getElementById('alertasList');
 if (!list) return;
 list.innerHTML = '';
 const filtered = ad.filter(a => af === 'todas' ? true : a.status === af.slice(0,-1) || a.status === af);
 
 const toShow = ad.filter(a => {
   if (af==='todas') return true;
   if (af==='activas') return a.status==='activo' || a.status==='en-revision';
   if (af==='resueltas') return a.status==='resuelta';
   return true;
 });
 if (toShow.length===0) {
   list.innerHTML = '<div style="padding:24px;text-align:center;color:var(--tx4);font-size:13px">No hay alertas para mostrar.</div>';
   return;
 }
 toShow.forEach((a, i) => {
  const d = document.createElement('div'); d.className = 'alert-item'; d.style.animationDelay = `${i * 0.06}s`;
  if(a.status==='resuelta'){ d.style.opacity='0.75'; d.style.borderColor='rgba(76,175,80,0.2)'; }
  const ic = a.icon === 'warning' ? (a.type === 'danger' ? 'danger-icon' : 'warning-icon') : a.icon === 'info' ? 'info-icon' : 'success-icon';
  const btnHtml = (a.status==='activo' || a.status==='en-revision') ? `<button onclick="resolverAlerta(${a.id}, this)" style="margin-left:8px;padding:6px 14px;border:none;border-radius:6px;background:#4caf50;color:#fff;font-size:12px;font-weight:600;cursor:pointer;white-space:nowrap">Resolver</button>` : `<span style="margin-left:8px;padding:6px 14px;border-radius:6px;background:rgba(76,175,80,0.12);color:#4caf50;font-size:11px;font-weight:700">✓ Resuelta</span>`;
  d.innerHTML = `<div class="alert-icon ${ic}">${alertIcon(a.icon)}</div><div class="alert-content"><div class="alert-name">${a.title}</div><div class="alert-desc">${a.desc}</div></div><div class="alert-meta"><div class="alert-date">${a.date}</div><div class="alert-badge ${a.status}">${a.status === 'activo' ? 'Activo' : (a.status==='en-revision'?'En revisión':'Resuelta')}</div>${btnHtml}</div>`;
  list.appendChild(d);
 });
} 
document.querySelectorAll('.alertas-filter').forEach(b => b.addEventListener('click', () => {
  document.querySelectorAll('.alertas-filter').forEach(x => x.classList.remove('active')); b.classList.add('active'); af = b.dataset.filter;

  if (typeof window.EVA_ALERTAS !== 'undefined' && window.EVA_ALERTAS.length>0) {
    alertas();
  } else {
    fetch(`api/alertas.php?filter=${encodeURIComponent(af)}`).then(r=>r.json()).then(data=>{
      if(Array.isArray(data)){ ad=data; alertas(); }
      else alertas();
    }).catch(()=> alertas());
  }
}));

const sL = document.getElementById('sliderLow'), sH = document.getElementById('sliderHigh');
function sliderFill(s) { if (!s) return; const p = ((s.value - s.min) / (s.max - s.min)) * 100; s.style.background = `linear-gradient(to right, #2c6cef 0%, #2c6cef ${p}%, #2a3042 ${p}%, #2a3042 100%)`; }
if (sL) { const sLv = document.getElementById('sliderLowVal'); sL.addEventListener('input', () => { sLv.textContent = `${sL.value} %`; sliderFill(sL); }); sliderFill(sL); }
if (sH) { const sHv = document.getElementById('sliderHighVal'); sH.addEventListener('input', () => { sHv.textContent = `${sH.value} %`; sliderFill(sH); }); sliderFill(sH); }

let cfgTimer=null;
function cfgSave(){
  if(!sL||!sH) return;
  const low=sL.value, high=sH.value;
  const csrf=document.querySelector('input[name="csrf"]')?.value || '';
  fetch('api/configuracion.php', {
    method:'POST',
    headers:{'Content-Type':'application/json'},
    body: JSON.stringify({low, high, csrf})
  }).then(r=>r.json()).then(()=>{}).catch(()=>{});
}
if(sL) sL.addEventListener('change', ()=>{ clearTimeout(cfgTimer); cfgTimer=setTimeout(cfgSave, 600); });
if(sH) sH.addEventListener('change', ()=>{ clearTimeout(cfgTimer); cfgTimer=setTimeout(cfgSave, 600); });

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
   if (typeof window.EVA_RESUMEN !== 'undefined' && window.EVA_RESUMEN && (window.EVA_RESUMEN.idTanque === null || window.EVA_RESUMEN.capacidad === 0)) {
     e.textContent = 'Sin datos'; e.className = 'resumen-estado-value';
   } else {
     const [txt, , cls]=evaEstadoFromPct(pct);
     e.textContent = txt;
     e.className = 'resumen-estado-value' + (cls==='alert'?' danger':cls==='warning'?' warning':'');
   }
  }
  const t = document.getElementById('resumenTemp');
  if (t) t.textContent = `${Math.round(tmp)}Â°C`;
  const d = document.getElementById('resumenDisponible');
  if (d) {
    const dispVal = (typeof window.EVA_RESUMEN!=='undefined' && window.EVA_RESUMEN.disponible) ? window.EVA_RESUMEN.disponible : Math.round(CAP * pct / 100);
    d.textContent = `${Number(dispVal).toLocaleString('es-AR')} L`;
  }
  const c = document.getElementById('resumenConsumo');
  if (c) {
    const cons = (typeof window.EVA_RESUMEN!=='undefined' && typeof window.EVA_RESUMEN.consumoHoy==='number') ? window.EVA_RESUMEN.consumoHoy : 0;
    c.textContent = cons ? `${Number(cons).toLocaleString('es-AR')} L` : 'Sin datos';
  }
  const p = document.getElementById('resumenPromedio');
  if (p && typeof window.EVA_RESUMEN !== 'undefined' && window.EVA_RESUMEN.promedio) p.textContent = `${Number(window.EVA_RESUMEN.promedio).toLocaleString('es-AR')} L`;
  else if (p) p.textContent = 'No hay datos disponibles';
 rChart();
}
function rChart() {
 const svg = document.getElementById('resumenMiniChart');
 if (!svg) return;
 let data = [], labels = [];
 if (typeof window.EVA_RESUMEN !== 'undefined' && window.EVA_RESUMEN && Array.isArray(window.EVA_RESUMEN.chartData) && window.EVA_RESUMEN.chartData.length>0) {
   data = window.EVA_RESUMEN.chartData;
   if (data.length===7) labels=['L','M','X','J','V','S','D'];
   else if (data.length===30) labels=Array.from({length:30},(_,i)=>`${i+1}`);
   else if (data.length===12) labels=['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
 }
 if (window.EVA_RESUMEN_DYNAMIC && Array.isArray(window.EVA_RESUMEN_DYNAMIC)) {
   data = window.EVA_RESUMEN_DYNAMIC;
 }
 if (!data || data.length === 0) {
   svg.innerHTML = '<text x="250" y="85" text-anchor="middle" fill="var(--tx4)" font-size="13" font-family="Inter,sans-serif">No hay datos disponibles</text>';
   return;
 }
 const W = 500, H = 170, L = 40, R = 20, T = 10, B = 30, gW = W - L - R, gH = H - T - B, max = 100;
 const pts = data.map((v, i) => ({ x: L + (i / (data.length - 1)) * gW, y: T + gH - (v / max) * gH }));
 const lt = document.body.classList.contains('light-theme'), gc = lt?'rgba(0,0,0,0.06)':'rgba(255,255,255,0.04)', tc = lt?'#6b7280':'#4a5068', cf = lt?'#fff':'#1a1f2e';
 let html = '';
 for (let i = 0; i <= 4; i++) { const y = T + (i / 4) * gH; html += `<line x1="${L}" y1="${y}" x2="${W - R}" y2="${y}" stroke="${gc}" stroke-width="1"/><text x="${L - 8}" y="${y + 4}" fill="${tc}" font-size="9" text-anchor="end" font-family="Inter,sans-serif">${Math.round(max - (i / 4) * max)}</text>`; }
 data.forEach((v, i) => { const x = L + (i / (data.length - 1)) * gW; html += `<text x="${x}" y="${H - 8}" fill="${tc}" font-size="9" text-anchor="middle" font-family="Inter,sans-serif">${labels[i]||i}</text>`; });
 const lp = pts.map((p, i) => `${i?'L':'M'}${p.x},${p.y}`).join(' ');
 html += `<path d="${lp} L${pts[pts.length-1].x},${T + gH} L${pts[0].x},${T + gH} Z" fill="url(#resumenAreaGrad)" opacity="0.3"/>`;
 html += `<path d="${lp}" fill="none" stroke="#42a5f5" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" stroke-dasharray="800" stroke-dashoffset="800"><animate attributeName="stroke-dashoffset" from="800" to="0" dur="1.2s" fill="freeze"/></path>`;
 pts.forEach(p => { html += `<circle cx="${p.x}" cy="${p.y}" r="3.5" fill="${cf}" stroke="#42a5f5" stroke-width="2"/>`; });
 html += `<defs><linearGradient id="resumenAreaGrad" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="#42a5f5" stop-opacity="0.4"/><stop offset="100%" stop-color="#42a5f5" stop-opacity="0"/></linearGradient></defs>`;
 svg.innerHTML = html;
}
const chartSelect = document.getElementById('resumenChartSelect');
if (chartSelect) chartSelect.addEventListener('change', () => {
  const period = chartSelect.value;
  
  fetch(`api/resumen.php?period=${encodeURIComponent(period)}`).then(r=>r.json()).then(j=>{
    if(j && Array.isArray(j.serie) && j.serie.length>0){
      const max=Math.max(...j.serie);
      const norm = max>0 ? j.serie.map(v=> Math.round((v/max)*90+10)) : j.serie;
      window.EVA_RESUMEN_DYNAMIC = norm;
      rChart();
    } else rChart();
  }).catch(()=> rChart());
});

let useSimulate = true;
if (typeof window.EVA_RESUMEN !== 'undefined' || typeof window.EVA_TANQUE !== 'undefined') {
  useSimulate = false;
}
function simulate() {
 if(useSimulate){
   lvl = Math.max(0, Math.min(100, lvl + (Math.random() - 0.45) * 4));
   lvl = Math.round(lvl * 10) / 10;
   tmp = Math.max(10, Math.min(100, tmp + (Math.random() - 0.5) * 6));
   tmp = Math.round(tmp);
   if (page === 'mitanque.php') { tank(lvl); gauge(tmp); status(); clock(); }
   if (page === 'indexcli.php') resumen();
 }
}
let pollInFlight=false;
let resumenFetchAbort=null;
function pollReal(){
  if(pollInFlight || document.hidden) return;
  pollInFlight=true;
  if(page==='mitanque.php'){
    fetch('api/tanque.php',{cache:'no-store'}).then(r=>r.json()).then(j=>{
      if(j && typeof j.pct==='number'){ lvl=j.pct; tmp=j.temp; CAP=j.capacidad||CAP; tank(lvl); gauge(tmp); status(); if(j.lastUpdate){ const el=document.getElementById('lastUpdate'); if(el) el.textContent=j.lastUpdate; } }
    }).catch(()=>{}).finally(()=>{ pollInFlight=false; });
  } else if(page==='indexcli.php'){
    if(resumenFetchAbort) resumenFetchAbort.abort();
    resumenFetchAbort=new AbortController();
    fetch('api/resumen.php',{cache:'no-store',signal:resumenFetchAbort.signal}).then(r=>r.json()).then(j=>{
      if(j && typeof j.pct==='number'){
        lvl=j.pct; tmp=j.temp; CAP=j.capacidad||CAP;
        if(typeof j.disponible==='number') window.EVA_RESUMEN.disponible=j.disponible;
        if(typeof j.consumoHoy==='number') window.EVA_RESUMEN.consumoHoy=j.consumoHoy;
        if(typeof j.promedio==='number') window.EVA_RESUMEN.promedio=j.promedio;
        if(j.serie && Array.isArray(j.serie)){
          const max=Math.max(...j.serie);
          const norm=max>0?j.serie.map(v=>Math.round((v/max)*90+10)):j.serie;
          window.EVA_RESUMEN.chartData=norm;
        }
        resumen();
      }
    }).catch(()=>{}).finally(()=>{ pollInFlight=false; });
  } else if(page==='historial.php'){
    const desde=document.getElementById('histDateFrom')?.value || '';
    const hasta=document.getElementById('histDateTo')?.value || '';
    const tanque=document.getElementById('histTankSelect')?.value || 'todos';
    const params=new URLSearchParams({desde,hasta,tanque});
    fetch('api/historial.php?'+params.toString(),{cache:'no-store'}).then(r=>r.json()).then(j=>{
      if(j && Array.isArray(j.rows) && j.rows.length>0){
        historialTabla(j.rows);
        historialStats(j.rows);
      }
    }).catch(()=>{}).finally(()=>{ pollInFlight=false; });
  } else if(page==='alertas.php'){
    fetch('api/alertas.php?filter='+encodeURIComponent(af),{cache:'no-store'}).then(r=>r.json()).then(j=>{
      if(Array.isArray(j) && j.length>0){ ad=j; alertas(); }
    }).catch(()=>{}).finally(()=>{ pollInFlight=false; });
  } else { pollInFlight=false; }
}

const themeToggle = document.getElementById('themeToggle');
if (themeToggle) {
 const iconSun = themeToggle.querySelector('.icon-sun'), iconMoon = themeToggle.querySelector('.icon-moon');
 let isLight = localStorage.getItem('eva-theme') === 'light';
 function theme() { document.body.classList.toggle('light-theme', isLight); iconSun.classList.toggle('hidden', isLight); iconMoon.classList.toggle('hidden', !isLight); }
 theme();
 themeToggle.addEventListener('click', () => { isLight = !isLight; localStorage.setItem('eva-theme', isLight ? 'light' : 'dark'); theme(); if (page === 'indexcli.php') rChart(); });
}

const userDropdown = document.getElementById('userDropdown');
const userMenu = document.getElementById('userMenu');
if (userDropdown && userMenu) {
 userDropdown.querySelector('.user-info').addEventListener('click', (e) => { e.stopPropagation(); userMenu.classList.toggle('hidden'); });
 document.addEventListener('click', () => { userMenu.classList.add('hidden'); });
 userMenu.addEventListener('click', (e) => { e.stopPropagation(); });
}

function aplicarEstadoDispositivo(txt){
 var el=document.querySelector('.device-status');
 var txtEl=document.querySelector('.status-text');
 var icon=document.querySelector('.wifi-icon');
 if(!el||!txtEl) return;
 var raw=(txt||txtEl.textContent||'').trim();
 var esCon = raw==='Conectado' || raw==='🟢 CONECTADO' || raw.toLowerCase()==='conectado';
 if(raw.toLowerCase().indexOf('desconectado')!==-1) esCon=false;
 else if(raw.toLowerCase().indexOf('conectado')!==-1) esCon=true;
 if(esCon){
   el.style.background='rgba(76,175,80,0.08)'; el.style.borderColor='rgba(76,175,80,0.15)';
   txtEl.textContent='Conectado'; txtEl.style.color='#4caf50'; txtEl.style.fontWeight='700';
   if(icon) icon.setAttribute('stroke','#4caf50');
 } else {
   el.style.background='rgba(244,67,54,0.08)'; el.style.borderColor='rgba(244,67,54,0.15)';
   txtEl.textContent='Desconectado'; txtEl.style.color='#f44336'; txtEl.style.fontWeight='700';
   if(icon) icon.setAttribute('stroke','#f44336');
 }
}
(function(){ var s=document.querySelector('.status-text'); if(s) aplicarEstadoDispositivo(s.textContent.trim()); })();
document.addEventListener('visibilitychange', ()=>{ if(!document.hidden && typeof evaPollEstado === 'undefined') pollReal(); });
const menuBtn=document.querySelector('.menu-btn');
const sidebar=document.querySelector('.sidebar');
if(menuBtn && sidebar){
  let overlay=document.querySelector('.sidebar-overlay');
  if(!overlay){ overlay=document.createElement('div'); overlay.className='sidebar-overlay'; document.body.appendChild(overlay); overlay.addEventListener('click', ()=>{ sidebar.classList.remove('open'); document.body.classList.remove('sidebar-open'); }); }
  menuBtn.addEventListener('click', (e)=>{ e.stopPropagation(); sidebar.classList.toggle('open'); document.body.classList.toggle('sidebar-open'); });
  document.addEventListener('click', (e)=>{ if(window.innerWidth<=768 && sidebar.classList.contains('open') && !sidebar.contains(e.target) && !menuBtn.contains(e.target) && !overlay.contains(e.target)){ sidebar.classList.remove('open'); document.body.classList.remove('sidebar-open'); } });
}
if (page === 'indexcli.php') { resumen(); if(useSimulate) setInterval(simulate, 3000); }
if (page === 'mitanque.php') { bars(); gauge(tmp); tank(lvl); clock(); if(useSimulate) setInterval(simulate, 3000); }
if (page === 'alertas.php') { alertas(); }
if (page === 'historial.php') { historialInit(); }
if (page === 'mantenimiento.php') { mantenimientoInit(); }

const histData = [];

function historialTabla(data) {
 const tbody = document.getElementById('histTableBody');
 if (!tbody) return;
 tbody.innerHTML = '';
 data.forEach(r => {
  const tr = document.createElement('tr');
  const st = r.estado === 'Normal' ? 'color:var(--gn)' : r.estado === 'Bajo' ? 'color:var(--or)' : 'color:var(--rd2)';
  tr.innerHTML = `<td style="padding:10px 14px;font-size:13px;color:var(--tx);border-bottom:1px solid var(--bd);white-space:nowrap">${r.fecha}</td><td style="padding:10px 14px;font-size:13px;color:var(--tx);border-bottom:1px solid var(--bd);white-space:nowrap">${r.hora}</td><td style="padding:10px 14px;font-size:13px;color:var(--tx);border-bottom:1px solid var(--bd);white-space:nowrap">${r.nivel}</td><td style="padding:10px 14px;font-size:13px;color:var(--tx);border-bottom:1px solid var(--bd);white-space:nowrap">${r.pct}%</td><td style="padding:10px 14px;font-size:13px;color:var(--tx);border-bottom:1px solid var(--bd);white-space:nowrap">${r.tmp}Â°C</td><td style="padding:10px 14px;font-size:13px;color:var(--tx);border-bottom:1px solid var(--bd);white-space:nowrap">${r.hum}%</td><td style="padding:10px 14px;font-size:13px;border-bottom:1px solid var(--bd);white-space:nowrap"><span style="padding:3px 10px;border-radius:6px;font-size:11px;font-weight:600;${st};background:${r.estado==='Normal'?'rgba(76,175,80,0.12)':r.estado==='Bajo'?'rgba(255,152,0,0.12)':'rgba(244,67,54,0.12)'}">${r.estado}</span></td>`;
  tbody.appendChild(tr);
 });
 const count = document.getElementById('histTableCount');
 if (count) count.textContent = `${data.length} registros`;
}

let histChartData = {
 semana: {values:[],labels:[]},
 mes: {values:[],labels:[]},
 trimestre: {values:[],labels:[]}
};
function histBuildLabels(period, n){
 if(period==='semana'){ const d=['Dom','Lun','Mar','Mie','Jue','Vie','Sab']; const out=[]; for(let i=6;i>=0;i--){ const dt=new Date(); dt.setDate(dt.getDate()-i); out.push(d[dt.getDay()]); } return out.slice(-n); }
 if(period==='mes'){ return Array.from({length:n},(_,i)=> String(i+1)); }
 if(period==='trimestre'){ return Array.from({length:n},(_,i)=> `S${i+1}`); }
 return Array.from({length:n},(_,i)=> String(i+1));
}
if (typeof window.EVA_HISTORIAL !== 'undefined' && window.EVA_HISTORIAL && window.EVA_HISTORIAL.chartData) {
  const ch = window.EVA_HISTORIAL.chartData;
  histChartData.semana.values = ch.semana && ch.semana.length ? ch.semana : histChartData.semana.values;
  histChartData.mes.values = ch.mes && ch.mes.length ? ch.mes : histChartData.mes.values;
  histChartData.trimestre.values = ch.trimestre && ch.trimestre.length ? ch.trimestre : histChartData.trimestre.values;
  histChartData.semana.labels = histBuildLabels('semana', histChartData.semana.values.length || 7);
  histChartData.mes.labels = histBuildLabels('mes', histChartData.mes.values.length || 30);
  histChartData.trimestre.labels = histBuildLabels('trimestre', histChartData.trimestre.values.length || 12);
  if(histChartData.semana.values.length===0 || histChartData.semana.values.every(v=>v===0)){
    const rowsTmp = window.EVA_HISTORIAL.rows || [];
    if(rowsTmp.length){
      const pcts = rowsTmp.map(r=> parseInt(r.pct)).filter(v=> !isNaN(v));
      if(pcts.length){ histChartData.semana.values = pcts.slice(-7); while(histChartData.semana.values.length<7) histChartData.semana.values.unshift(pcts[0]||0); }
    }
    if(histChartData.semana.values.every(v=>v===0)) histChartData.semana.values=[48,55,42,67,58,73,61];
    if(histChartData.mes.values.every(v=>v===0)) histChartData.mes.values=[45,50,48,52,60,55,49,62,58,53,48,55,42,67,58,73,61,50,54,48,60,55,49,62,58,53,45,50,48,52];
    if(histChartData.trimestre.values.every(v=>v===0)) histChartData.trimestre.values=[52,48,61,55,67,60,58,53,49,62,55,48];
  }
}
if(histChartData.semana.values.length===0){ histChartData.semana.values=[48,55,42,67,58,73,61]; histChartData.semana.labels=histBuildLabels('semana',7); }
if(histChartData.mes.values.length===0){ histChartData.mes.values=[45,50,48,52,60,55,49,62,58,53,48,55,42,67,58,73,61,50,54,48,60,55,49,62,58,53,45,50,48,52]; histChartData.mes.labels=histBuildLabels('mes',30); }
if(histChartData.trimestre.values.length===0){ histChartData.trimestre.values=[52,48,61,55,67,60,58,53,49,62,55,48]; histChartData.trimestre.labels=histBuildLabels('trimestre',12); }

function historialChart(p) {
 const svg = document.getElementById('histChartSvg');
 if (!svg) return;
 const d = histChartData[p];
 if (!d || !d.values || d.values.length === 0 || d.values.every(v=> v===0)) {
   if(d && d.values && d.values.every(v=> v===0) && typeof window.EVA_HISTORIAL !== 'undefined' && window.EVA_HISTORIAL.rows && window.EVA_HISTORIAL.rows.length){
     const pcts = window.EVA_HISTORIAL.rows.map(r=> parseInt(r.pct)).filter(v=> !isNaN(v));
     if(pcts.length){ d.values = pcts.slice(-d.values.length); while(d.values.length < (p==='semana'?7:p==='mes'?30:12)) d.values.unshift(0); }
   }
   if(!d || !d.values || d.values.length===0 || d.values.every(v=> v===0)){
     svg.innerHTML = '<text x="350" y="150" text-anchor="middle" fill="var(--tx4)" font-size="13" font-family="Inter,sans-serif">No hay datos disponibles</text>';
     return;
   }
 }
 const L = 40, R = 20, T = 15, B = 35, w = 700 - L - R, h = 300 - T - B;
 const max = 100, n = d.values.length, sx = w / Math.max(n - 1, 1);
 let html = '';
 for (let i = 0; i <= 5; i++) {
  const y = T + (h / 5) * i;
  html += `<line x1="${L}" y1="${y}" x2="${L + w}" y2="${y}" class="grid-line"/>`;
  html += `<text x="${L - 8}" y="${y + 4}" class="axis-label" text-anchor="end">${Math.round(max - (max / 5) * i)}</text>`;
 }
 const pts = d.values.map((v, i) => ({x: L + i * sx, y: T + h - (v / max) * h}));
 html += `<path d="M${pts[0].x},${T + h} ${pts.map(p => `L${p.x},${p.y}`).join(' ')} L${pts[pts.length - 1].x},${T + h} Z" fill="rgba(79,195,247,0.06)"/>`;
 html += `<path d="${pts.map((p, i) => `${i ? 'L' : 'M'}${p.x},${p.y}`).join(' ')}" class="data-line" id="histAnimatedLine"/>`;
 pts.forEach(p => { html += `<circle cx="${p.x}" cy="${p.y}" r="4.5" class="data-dot"/>`; });
 if (n <= 30) {
  pts.forEach((p, i) => {
   if (i % Math.ceil(n / 12) === 0) {
    html += `<text x="${p.x}" y="${T + h + 20}" class="axis-label" text-anchor="middle">${d.labels[i]||i}</text>`;
   }
  });
 }
 svg.innerHTML = html;
 const line = document.getElementById('histAnimatedLine');
 if (line) {
  const len = line.getTotalLength();
  line.style.strokeDasharray = len;
  line.style.strokeDashoffset = len;
  line.style.transition = 'none';
  requestAnimationFrame(() => { line.style.transition = 'stroke-dashoffset 1s cubic-bezier(0.4,0,0.2,1)'; line.style.strokeDashoffset = '0'; });
 }
}

function historialStats(data) {
 const pcts = data.map(r => r.pct).filter(v=> typeof v==='number' || (!isNaN(parseInt(v)) && v!=='-'));
 const numericPcts = pcts.map(v=> parseInt(v)).filter(v=> !isNaN(v));
 if(numericPcts.length===0) return;
 const avg = Math.round(numericPcts.reduce((a, b) => a + b, 0) / numericPcts.length);
 const max = Math.max(...numericPcts);
 const min = Math.min(...numericPcts);
 const maxIdx = numericPcts.indexOf(max);
 const minIdx = numericPcts.indexOf(min);
 const sp = document.getElementById('statPromedio');
 const ss = document.getElementById('statPromedioSub');
 const st = document.getElementById('statTotal');
 const sts = document.getElementById('statTotalSub');
 const sm = document.getElementById('statMayor');
 const smv = document.getElementById('statMayorVal');
 const sn = document.getElementById('statMenor');
 const snv = document.getElementById('statMenorVal');
 if (sp) sp.textContent = `${avg}%`;
 if (ss) ss.textContent = `${Math.round(avg * 2)} cm promedio`;
 if (st) st.textContent = data.length;
 if (sts) sts.textContent = 'mediciones';
 if (sm) sm.textContent = `${max}%`;
 if (smv) smv.textContent = data[maxIdx] ? `${data[maxIdx].fecha} ${data[maxIdx].hora}` : '-';
 if (sn) sn.textContent = `${min}%`;
 if (snv) snv.textContent = data[minIdx] ? `${data[minIdx].fecha} ${data[minIdx].hora}` : '-';
}

function historialInit() {
 
 const hasRealTable = typeof window.EVA_HISTORIAL !== 'undefined' && window.EVA_HISTORIAL && Array.isArray(window.EVA_HISTORIAL.rows);
 if (hasRealTable) {
   const period = window.EVA_HISTORIAL.period || 'semana';
   historialChart(period);
   document.querySelectorAll('.history-tab').forEach(x=> x.classList.toggle('active', x.dataset.period===period));
 } else {
   historialTabla(histData);
   historialChart('semana');
   historialStats(histData);
 }
 document.querySelectorAll('.history-tab').forEach(t => t.addEventListener('click', () => {
  document.querySelectorAll('.history-tab').forEach(x => x.classList.remove('active'));
  t.classList.add('active');
  
  const inp=document.getElementById('histPeriodInput'); if(inp) inp.value=t.dataset.period;
  historialChart(t.dataset.period);
 }));
 const btnFilter = document.getElementById('histBtnFilter');
 
 if (btnFilter && !hasRealTable) btnFilter.addEventListener('click', (e) => { e.preventDefault(); historialTabla(histData); historialStats(histData); });
}

const mtSolicitudes = [];

function mtEstadoClass(estado) {
 if (estado === 'Pendiente') return 'activo';
 if (estado === 'En Revision') return 'en-revision';
 return 'resuelta';
}

function mtTabla(data) {
 const tbody = document.getElementById('mtTablaBody');
 if (!tbody) return;
 
 if (typeof window.EVA_MT_HAS_REAL !== 'undefined' && window.EVA_MT_HAS_REAL) return;
 const counter = {total: data.length};
 tbody.innerHTML = data.map((s, i) => {
  const num = String(counter.total - i).padStart(4, '0');
  return `
  <tr style="animation:slideUp .3s ${i * .05}s backwards">
   <td>#${num}</td>
   <td>${s.fecha}</td>
   <td>${s.problema}</td>
   <td><span class="alert-badge ${mtEstadoClass(s.estado)}">${s.estado}</span></td>
   <td style="font-size:12px;color:var(--tx5)">${s.actualizacion}</td>
   <td>
    <button class="mt-info-btn" title="Ver detalles">
     <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
    </button>
   </td>
  </tr>`;
 }).join('');
}

function mantenimientoInit() {
 
 const tbody = document.getElementById('mtTablaBody');
 const hasRealRows = tbody && tbody.querySelector('tr');
 if (hasRealRows) {
   window.EVA_MT_HAS_REAL = true;
 } else if (mtSolicitudes.length > 0) {
   mtTabla(mtSolicitudes);
 }

 const desc = document.getElementById('mtDescripcion');
 const charCount = document.getElementById('mtCharCount');
 if (desc && charCount) {
  desc.addEventListener('input', () => { charCount.textContent = desc.value.length; });
  
  charCount.textContent = desc.value.length;
 }

 const upload = document.getElementById('mtUpload');
 const fileInput = document.getElementById('mtFileInput');
 const fileName = document.getElementById('mtFileName');
 if (upload && fileInput) {
  upload.addEventListener('click', (e) => { if(e.target!==fileInput) fileInput.click(); });
  fileInput.addEventListener('change', ()=>{ if(fileName) fileName.textContent = fileInput.files[0] ? fileInput.files[0].name : ''; });
  upload.addEventListener('dragover', (e) => { e.preventDefault(); upload.style.borderColor = 'var(--ac)'; upload.style.background = 'rgba(44,108,239,0.06)'; });
  upload.addEventListener('dragleave', () => { upload.style.borderColor = ''; upload.style.background = ''; });
  upload.addEventListener('drop', (e) => { e.preventDefault(); upload.style.borderColor = ''; upload.style.background = ''; if(e.dataTransfer.files[0]){ fileInput.files=e.dataTransfer.files; if(fileName) fileName.textContent=e.dataTransfer.files[0].name; } });
 }

  const form = document.getElementById('mtForm');
  const btnEnviar = document.getElementById('mtEnviar');
  if (form && btnEnviar) {
    form.addEventListener('submit', (e) => {
      const tanque = document.getElementById('mtTanque');
      const descripcion = document.getElementById('mtDescripcion');
      const f = document.getElementById('mtFileInput');
      if (!tanque.value) { e.preventDefault(); alert('Debes seleccionar un tanque.'); return; }
      const d = descripcion.value.trim();
      if (!d) { e.preventDefault(); alert('La descripciÃ³n es obligatoria.'); return; }
      if (d.length > 500) { e.preventDefault(); alert('La descripciÃ³n no puede superar los 500 caracteres.'); return; }
      if (f && f.files && f.files[0]) {
        const file = f.files[0];
        const allowed = ['image/jpeg','image/png','image/jpg','image/webp'];
        if (!allowed.includes(file.type)) { e.preventDefault(); alert('Formato de imagen no permitido.'); return; }
        if (file.size > 5*1024*1024) { e.preventDefault(); alert('El archivo supera el tamaÃ±o mÃ¡ximo de 5 MB.'); return; }
      }
      btnEnviar.disabled = true;
      btnEnviar.style.opacity = '0.7';
      const orig = btnEnviar.innerHTML;
      btnEnviar.innerHTML = 'Enviando...';
      setTimeout(() => { if (btnEnviar.disabled) { btnEnviar.disabled = false; btnEnviar.style.opacity = ''; btnEnviar.innerHTML = orig; } }, 8000);
    });
    if (!hasRealRows) {
      btnEnviar.addEventListener('click', (e) => {
        if (form.checkValidity && !form.checkValidity()) return;
      });
    }
  }
}
