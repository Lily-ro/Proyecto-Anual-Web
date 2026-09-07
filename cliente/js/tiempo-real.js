let EVA_TIEMPO_INTERVAL = 30000;
let evaPollCtrl = null;
let evaPollInFlight = false;

function evaActualizarDashboard(d) {
  if (!d || d.error) return;
  if (typeof d.pct === 'number') {
    if (typeof lvl !== 'undefined') lvl = d.pct;
    if (typeof CAP !== 'undefined' && d.capacidad) CAP = d.capacidad;
    if (typeof tmp !== 'undefined' && typeof d.temp === 'number') tmp = d.temp;
    if (typeof window.EVA_RESUMEN !== 'undefined') {
      window.EVA_RESUMEN.pct = d.pct;
      window.EVA_RESUMEN.temp = d.temp;
      window.EVA_RESUMEN.capacidad = d.capacidad;
      window.EVA_RESUMEN.disponible = d.litros;
      window.EVA_RESUMEN.consumoHoy = d.consumoHoy;
      window.EVA_RESUMEN.promedio = d.promedio;
      if (d.serie) {
        const max = Math.max(...d.serie);
        window.EVA_RESUMEN.chartData = max > 0 ? d.serie.map(v => Math.round((v / max) * 90 + 10)) : d.serie;
      }
    }
    if (typeof window.EVA_TANQUE !== 'undefined') {
      window.EVA_TANQUE.pct = d.pct;
      window.EVA_TANQUE.temp = d.temp;
      window.EVA_TANQUE.litros = d.litros;
      window.EVA_TANQUE.capacidad = d.capacidad;
      if (d.barsData) window.EVA_TANQUE.barsData = d.barsData;
    }
    const page = location.pathname.split('/').pop() || 'indexcli.php';
    if (page === 'indexcli.php' && typeof resumen === 'function') resumen();
    if (page === 'mitanque.php') {
      if (typeof tank === 'function') tank(d.pct);
      if (typeof gauge === 'function') gauge(d.temp);
      if (typeof status === 'function') status();
      if (d.lastUpdate) { const el = document.getElementById('lastUpdate'); if (el) el.textContent = d.lastUpdate; }
      if (d.barsData && typeof bars === 'function') { if (typeof bd !== 'undefined') bd = d.barsData; bars(); }
    }
  }
}

function evaPollEstado() {
  if (evaPollInFlight || document.hidden) return;
  evaPollInFlight = true;
  const period = document.getElementById('resumenChartSelect')?.value || 'semana';
  fetch('api/estado.php?period=' + encodeURIComponent(period), { cache: 'no-store' })
    .then(r => r.json())
    .then(d => { evaActualizarDashboard(d); })
    .catch(() => {})
    .finally(() => { evaPollInFlight = false; });
}

function evaIniciarTiempoReal() {
  evaPollEstado();
  setInterval(evaPollEstado, EVA_TIEMPO_INTERVAL);
  document.addEventListener('visibilitychange', () => { if (!document.hidden) evaPollEstado(); });
  const sel = document.getElementById('resumenChartSelect');
  if (sel) sel.addEventListener('change', () => setTimeout(evaPollEstado, 200));
}

if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', evaIniciarTiempoReal);
else evaIniciarTiempoReal();
