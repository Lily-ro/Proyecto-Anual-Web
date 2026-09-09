let EVA_TIEMPO_INTERVAL = 3000;
let evaPollCtrl = null;
let evaPollInFlight = false;
let evaPollTimer = null;
let evaErrorCount = 0;
let evaLastSuccessData = null;

function evaActualizarDashboard(d) {
  if (!d || d.error) return;
  evaErrorCount = 0;
  evaLastSuccessData = d;
  var page = location.pathname.split('/').pop() || 'indexcli.php';
  if (typeof d.pct === 'number') {
    if (typeof lvl !== 'undefined') lvl = d.pct;
    if (typeof CAP !== 'undefined' && d.capacidad) CAP = d.capacidad;
    if (typeof tmp !== 'undefined' && typeof d.temp === 'number') tmp = d.temp;
    if (typeof window.EVA_RESUMEN !== 'undefined' && window.EVA_RESUMEN) {
      window.EVA_RESUMEN.pct = d.pct;
      window.EVA_RESUMEN.temp = d.temp;
      window.EVA_RESUMEN.capacidad = d.capacidad;
      window.EVA_RESUMEN.disponible = d.litros;
      window.EVA_RESUMEN.consumoHoy = d.consumoHoy;
      window.EVA_RESUMEN.promedio = d.promedio;
      if (d.serie) {
        var max = Math.max.apply(null, d.serie);
        window.EVA_RESUMEN.chartData = max > 0 ? d.serie.map(function(v){ return Math.round((v / max) * 90 + 10); }) : d.serie;
      }
    }
    if (typeof window.EVA_TANQUE !== 'undefined' && window.EVA_TANQUE) {
      window.EVA_TANQUE.pct = d.pct;
      window.EVA_TANQUE.temp = d.temp;
      window.EVA_TANQUE.litros = d.litros;
      window.EVA_TANQUE.capacidad = d.capacidad;
      if (d.barsData) window.EVA_TANQUE.barsData = d.barsData;
      if (d.lastUpdate) window.EVA_TANQUE.lastUpdate = d.lastUpdate;
    }
    if (page === 'indexcli.php' && typeof resumen === 'function') resumen();
    if (page === 'mitanque.php') {
      if (typeof tank === 'function') tank(d.pct);
      if (typeof gauge === 'function') gauge(d.temp);
      if (typeof status === 'function') status();
      if (d.lastUpdate) { var el = document.getElementById('lastUpdate'); if (el) el.textContent = d.lastUpdate; }
      if (d.barsData && typeof bars === 'function') { if (typeof bd !== 'undefined') bd = d.barsData; bars(); }
      var dStatus = document.querySelector('.status-text');
      if (dStatus && d.deviceStatus) dStatus.textContent = d.deviceStatus;
    }
  }
  if (typeof d.deviceStatus === 'string') {
    var sEl = document.querySelector('.status-text');
    if (sEl) sEl.textContent = d.deviceStatus;
  }
}

function evaActualizarAlertasHistorial(page) {
  if (page === 'historial.php') {
    var desde = document.getElementById('histDateFrom') ? document.getElementById('histDateFrom').value : '';
    var hasta = document.getElementById('histDateTo') ? document.getElementById('histDateTo').value : '';
    var tanque = document.getElementById('histTankSelect') ? document.getElementById('histTankSelect').value : 'todos';
    var params = new URLSearchParams({desde: desde, hasta: hasta, tanque: tanque});
    fetch('api/historial.php?' + params.toString(), {cache: 'no-store'}).then(function(r){ return r.json(); }).then(function(j){
      if (j && Array.isArray(j.rows) && typeof historialTabla === 'function') {
        historialTabla(j.rows);
        if (typeof historialStats === 'function') historialStats(j.rows);
        if (j.chartData && typeof histChartData !== 'undefined') {
          if (j.chartData.semana && j.chartData.semana.length) { histChartData.semana.values = j.chartData.semana; histChartData.semana.labels = (typeof histBuildLabels==='function'?histBuildLabels('semana', j.chartData.semana.length): j.chartData.semana.map(function(_,i){return 'D'+(i+1);})); }
          if (j.chartData.mes && j.chartData.mes.length) { histChartData.mes.values = j.chartData.mes; histChartData.mes.labels = (typeof histBuildLabels==='function'?histBuildLabels('mes', j.chartData.mes.length): j.chartData.mes.map(function(_,i){return String(i+1);})); }
          if (j.chartData.trimestre && j.chartData.trimestre.length) { histChartData.trimestre.values = j.chartData.trimestre; histChartData.trimestre.labels = (typeof histBuildLabels==='function'?histBuildLabels('trimestre', j.chartData.trimestre.length): j.chartData.trimestre.map(function(_,i){return 'S'+(i+1);})); }
          var active = document.querySelector('.history-tab.active'); var per = active ? active.dataset.period : 'semana';
          if (typeof historialChart === 'function') historialChart(per);
        }
      } else if (j && j.rows && typeof historialTabla === 'function') {
        historialTabla(j.rows);
        if (typeof historialStats === 'function') historialStats(j.rows);
      }
    }).catch(function(){});
  } else if (page === 'alertas.php') {
    var af = 'todas';
    var activeBtn = document.querySelector('.alertas-filter.active');
    if (activeBtn && activeBtn.dataset.filter) af = activeBtn.dataset.filter;
    if (typeof window.af !== 'undefined' && window.af) af = window.af;
    fetch('api/alertas.php?filter=' + encodeURIComponent(af), {cache: 'no-store'}).then(function(r){ return r.json(); }).then(function(j){
      if (Array.isArray(j) && typeof alertas === 'function') { if (typeof ad !== 'undefined') ad = j; alertas(); }
    }).catch(function(){});
  }
}

function evaPollEstado() {
  if (evaPollInFlight || document.hidden) return;
  evaPollInFlight = true;
  var page = location.pathname.split('/').pop() || 'indexcli.php';
  if (page === 'historial.php' || page === 'alertas.php') {
    evaActualizarAlertasHistorial(page);
    evaPollInFlight = false;
    return;
  }
  var period = document.getElementById('resumenChartSelect') ? document.getElementById('resumenChartSelect').value : 'semana';
  if (page !== 'indexcli.php' && page !== 'mitanque.php') period = 'semana';
  if (evaPollCtrl) try{ evaPollCtrl.abort(); }catch(e){}
  evaPollCtrl = new AbortController();
  fetch('api/estado.php?period=' + encodeURIComponent(period), { cache: 'no-store', signal: evaPollCtrl.signal })
    .then(function(r){ if(!r.ok) throw new Error('http '+r.status); return r.json(); })
    .then(function(d){ evaActualizarDashboard(d); })
    .catch(function(e){
      if (e && e.name === 'AbortError') return;
      evaErrorCount++;
    })
    .finally(function(){ evaPollInFlight = false; });
}

function evaIniciarTiempoReal() {
  evaPollEstado();
  if (evaPollTimer) clearInterval(evaPollTimer);
  evaPollTimer = setInterval(evaPollEstado, EVA_TIEMPO_INTERVAL);
  document.addEventListener('visibilitychange', function(){ if (!document.hidden) evaPollEstado(); });
  var sel = document.getElementById('resumenChartSelect');
  if (sel) sel.addEventListener('change', function(){ setTimeout(evaPollEstado, 200); });
  window._evaPollTimer = evaPollTimer;
}

if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', evaIniciarTiempoReal);
else evaIniciarTiempoReal();
