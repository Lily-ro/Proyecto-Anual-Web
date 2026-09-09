let EVA_TEC_INTERVAL = 3000;
let evaTecPollInFlight = false;
let evaTecTimer = null;
function evaTecActualizar(d){
  if(!d || d.error || !d.success) return;
  var pairs = [
    ['tecStatSensores', d.sensoresTotal],
    ['tecStatInstalaciones', d.instalacionesTotal],
    ['tecStatInstalacionesSub', 'Programadas: '+d.instPendientes],
    ['tecStatMantenimientos', d.mantTotal],
    ['tecStatMantSub', 'Pendientes: '+d.mantPendientes],
    ['tecStatDispositivos', d.dispositivosTotal]
  ];
  pairs.forEach(function(p){
    var el=document.getElementById(p[0]);
    if(el) el.textContent=p[1];
  });
  var svg=document.getElementById('donutSvg');
  if(svg && typeof d.totalDonut !== 'undefined'){
    var total=d.totalDonut||1;
    var centerVal=svg.parentElement ? svg.parentElement.querySelector('.donut-val') : null;
    if(centerVal) centerVal.textContent=d.totalDonut;
    var legendItems=document.querySelectorAll('.donut-legend-item');
    if(legendItems.length>=3){
      var vals=[d.donutActivo,d.donutFalla,d.donutInactivo];
      var pcts=[d.pctActivo,d.pctFalla,d.pctInactivo];
      legendItems.forEach(function(li,i){
        var vEl=li.querySelector('.donut-legend-val');
        var pEl=li.querySelector('.donut-legend-pct');
        if(vEl) vEl.textContent=vals[i];
        if(pEl) pEl.textContent='('+pcts[i]+'%)';
      });
    }
    (function(){
      var r=60,c=2*Math.PI*r,offset=0;
      svg.innerHTML='<circle cx="80" cy="80" r="'+r+'" class="donut-bg"/>';
      [{p:d.pctActivo,color:'#4caf50'},{p:d.pctFalla,color:'#ff9800'},{p:d.pctInactivo,color:'#f44336'}].forEach(function(dd){
        var len=(dd.p/100)*c;
        var circle=document.createElementNS('http://www.w3.org/2000/svg','circle');
        circle.setAttribute('cx','80');circle.setAttribute('cy','80');circle.setAttribute('r',String(r));
        circle.setAttribute('fill','none');circle.setAttribute('stroke',dd.color);circle.setAttribute('stroke-width','12');
        circle.setAttribute('stroke-linecap','round');circle.setAttribute('stroke-dasharray',len+' '+(c-len));
        circle.setAttribute('stroke-dashoffset',String(-offset));
        svg.appendChild(circle);
        offset+=len;
      });
    })();
  }
  var tbMant = document.querySelector('.card .table tbody');
  
  if(d.mantProximos && Array.isArray(d.mantProximos)){
    var tables=document.querySelectorAll('.table');
    
    
    var mantTable=null;
    document.querySelectorAll('.card').forEach(function(card){
      var title=card.querySelector('.card-title');
      if(title && title.textContent.indexOf('Mantenimientos pr')!==-1){
        mantTable=card.querySelector('tbody');
      }
    });
    if(mantTable){
      if(d.mantProximos.length===0){
        mantTable.innerHTML='<tr><td colspan="4" style="text-align:center;color:var(--tx4)">No hay mantenimientos programados</td></tr>';
      } else {
        var html='';
        d.mantProximos.forEach(function(mp){
          var badgeCls = mp.estado==='PENDIENTE' ? 'pendiente' : (mp.estado==='EN_PROCESO' ? 'activo' : 'inactivo');
          var badgeTxt = mp.estado ? mp.estado.replace('_',' ') : '';
          var fecha = mp.fecha_programada ? mp.fecha_programada.substring(0,10).split('-').reverse().join('/') : '-';
          var ubic = ((mp.edificio||'Sin edificio')+' - '+(mp.tanque||'Sin tanque'));
          html+='<tr><td>'+escapeHtml(mp.descripcion||'-')+'</td><td>'+escapeHtml(ubic)+'</td><td>'+fecha+'</td><td><span class="badge '+badgeCls+'">'+escapeHtml(badgeTxt)+'</span></td></tr>';
        });
        mantTable.innerHTML=html;
      }
    }
    var logTable=null;
    document.querySelectorAll('.card').forEach(function(card){
      var title=card.querySelector('.card-title');
      if(title && title.textContent.indexOf('Actividad reciente')!==-1){
        logTable=card.querySelector('tbody');
      }
    });
    if(logTable && Array.isArray(d.logActividad)){
      if(d.logActividad.length===0){
        logTable.innerHTML='<tr><td colspan="3" style="text-align:center;color:var(--tx4)">No hay actividad reciente</td></tr>';
      } else {
        var html2='';
        d.logActividad.forEach(function(l){
          var fecha=l.fecha_hora ? l.fecha_hora.substring(0,16).replace('T',' ').split('-').reverse().join('/').replace('/','/') : '-';
          
          if(l.fecha_hora){
            var dt=new Date(l.fecha_hora.replace(' ','T'));
            if(!isNaN(dt)) fecha = ('0'+dt.getDate()).slice(-2)+'/'+('0'+(dt.getMonth()+1)).slice(-2)+'/'+dt.getFullYear()+' '+('0'+dt.getHours()).slice(-2)+':'+('0'+dt.getMinutes()).slice(-2);
            else fecha=l.fecha_hora;
          }
          html2+='<tr><td>'+escapeHtml(l.accion||'-')+'</td><td>'+escapeHtml(l.detalle||'-')+'</td><td>'+escapeHtml(fecha)+'</td></tr>';
        });
        logTable.innerHTML=html2;
      }
    }
  }
  var ind=document.getElementById('evaLiveIndicatorTec');
  if(ind){ ind.textContent='Actualizado '+ (d.ts||''); ind.style.opacity='1'; }
}
function escapeHtml(s){ var d=document.createElement('div'); d.textContent=s; return d.innerHTML; }
function evaTecPoll(){
  if(evaTecPollInFlight || document.hidden) return;
  evaTecPollInFlight=true;
  fetch('api/estado.php',{cache:'no-store'}).then(function(r){ if(!r.ok) throw new Error('http '+r.status); return r.json(); }).then(function(d){ evaTecActualizar(d); }).catch(function(){}).finally(function(){ evaTecPollInFlight=false; });
}
function evaTecIniciar(){
  evaTecPoll();
  if(evaTecTimer) clearInterval(evaTecTimer);
  evaTecTimer=setInterval(evaTecPoll, EVA_TEC_INTERVAL);
  document.addEventListener('visibilitychange', function(){ if(!document.hidden) evaTecPoll(); });
}
if(document.readyState==='loading') document.addEventListener('DOMContentLoaded', evaTecIniciar);
else evaTecIniciar();
