let EVA_ADMIN_INTERVAL = 3000;
let evaAdminPollInFlight = false;
let evaAdminTimer = null;
function evaAdminActualizar(d){
  if(!d || d.error || !d.success) return;
  var map = [
    ['adminStatClientes', d.cntClientes],
    ['adminStatEdificios', d.cntEdificios],
    ['adminStatDispositivos', d.cntDispositivos],
    ['adminStatInstalaciones', d.cntInstalaciones],
    ['adminStatTanques', d.cntTanques],
    ['adminStatTecnicos', d.cntTecnicos],
    ['adminStatSensores', d.cntSensores]
  ];
  map.forEach(function(pair){
    var el = document.getElementById(pair[0]);
    if(el && typeof pair[1] !== 'undefined') el.textContent = pair[1];
  });
  var donutSvg = document.getElementById('donutSvg');
  if(donutSvg && typeof d.devOnline !== 'undefined'){
    donutSvg.dataset.online = d.devOnline;
    donutSvg.dataset.alerta = d.devAlerta;
    donutSvg.dataset.inactivo = d.devInactivo;
    donutSvg.dataset.total = d.cntDispositivos;
    var total = Math.max(parseInt(d.cntDispositivos)||1,1);
    var online = parseInt(d.devOnline)||0;
    var alerta = parseInt(d.devAlerta)||0;
    var inactivo = parseInt(d.devInactivo)||0;
    var pctO = Math.round((online/total)*100);
    var pctA = Math.round((alerta/total)*100);
    var pctI = 100 - pctO - pctA;
    var centerVal = donutSvg.parentElement ? donutSvg.parentElement.querySelector('.donut-val') : null;
    if(centerVal) centerVal.textContent = d.cntDispositivos;
    var legendItems = document.querySelectorAll('.donut-legend-item');
    if(legendItems.length>=3){
      var vals = [online, alerta, inactivo];
      var pcts = [pctO, pctA, pctI];
      legendItems.forEach(function(li, i){
        var vEl = li.querySelector('.donut-legend-val');
        var pEl = li.querySelector('.donut-legend-pct');
        if(vEl) vEl.textContent = vals[i];
        if(pEl) pEl.textContent = '('+pcts[i]+'%)';
      });
    }
    (function(){
      var r=50,c=2*Math.PI*r,offset=0;
      donutSvg.innerHTML = '<circle cx="70" cy="70" r="'+r+'" class="donut-bg"/>';
      [{p:pctO,color:'#4caf50'},{p:pctA,color:'#ff9800'},{p:pctI,color:'#f44336'}].forEach(function(dd,idx){
        var len=(dd.p/100)*c;
        var circle=document.createElementNS('http://www.w3.org/2000/svg','circle');
        circle.setAttribute('cx','70');circle.setAttribute('cy','70');circle.setAttribute('r',String(r));
        circle.setAttribute('fill','none');circle.setAttribute('stroke',dd.color);circle.setAttribute('stroke-width','12');
        circle.setAttribute('stroke-linecap','round');circle.setAttribute('stroke-dasharray',len+' '+(c-len));
        circle.setAttribute('stroke-dashoffset',String(-offset));
        donutSvg.appendChild(circle);
        offset+=len;
      });
    })();
  }
  var ind = document.getElementById('evaLiveIndicator');
  if(ind){ ind.textContent = 'Actualizado ' + (d.ts||''); ind.style.opacity='1'; }
}
function evaAdminPoll(){
  if(evaAdminPollInFlight || document.hidden) return;
  evaAdminPollInFlight = true;
  fetch('api/estado.php',{cache:'no-store'}).then(function(r){ if(!r.ok) throw new Error('http '+r.status); return r.json(); }).then(function(d){ evaAdminActualizar(d); }).catch(function(){}).finally(function(){ evaAdminPollInFlight=false; });
}
function evaAdminIniciar(){
  evaAdminPoll();
  if(evaAdminTimer) clearInterval(evaAdminTimer);
  evaAdminTimer = setInterval(evaAdminPoll, EVA_ADMIN_INTERVAL);
  document.addEventListener('visibilitychange', function(){ if(!document.hidden) evaAdminPoll(); });
}
if(document.readyState==='loading') document.addEventListener('DOMContentLoaded', evaAdminIniciar);
else evaAdminIniciar();
