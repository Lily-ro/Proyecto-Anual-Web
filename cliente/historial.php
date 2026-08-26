<?php
session_start();
if(!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'USUARIO'){
    header("Location: ../index.php");
    exit;
}
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/includes/helpers.php';

$tanques = [];
$deviceStatus = 'Conectado';
$idTanqueSel = null;
$fechaDesde = $_GET['desde'] ?? date('Y-m-d', strtotime('-14 days'));
$fechaHasta = $_GET['hasta'] ?? date('Y-m-d');
$tanqueFilter = $_GET['tanque'] ?? 'todos';
$period = $_GET['period'] ?? 'semana';

// validar fechas
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaDesde)) $fechaDesde = date('Y-m-d', strtotime('-14 days'));
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaHasta)) $fechaHasta = date('Y-m-d');
if ($fechaDesde > $fechaHasta) { $tmp=$fechaDesde; $fechaDesde=$fechaHasta; $fechaHasta=$tmp; }

$historialRows = [];
$stats = ['promedio'=>0,'promedioSub'=>'','total'=>0,'mayor'=>'-','mayorVal'=>'-','menor'=>'-','menorVal'=>'-'];
$chartData = ['semana'=>[],'mes'=>[],'trimestre'=>[]];
try {
    $pdo = eva_pdo();
    $uid = eva_current_user_id();
    $tanques = eva_tanques_for_user($pdo, $uid);
    if (!empty($tanques)) {
        $first = $tanques[0];
        $idFirst = (int)($first['id_tanque'] ?? 0);
        $deviceStatus = eva_device_status($pdo, $idFirst);
        if ($tanqueFilter !== 'todos' && is_numeric($tanqueFilter)) $idTanqueSel = (int)$tanqueFilter;
        elseif ($tanqueFilter !== 'todos') {
            // buscar por string tanque1 etc legacy: mapear al primero
            $idTanqueSel = $idFirst;
        }
    }
    // detectar esquema mediciones
    $hasIdTanque = false;
    try { $c=$pdo->query("SHOW COLUMNS FROM mediciones LIKE 'id_tanque'"); $hasIdTanque=$c&&$c->rowCount()>0; } catch(Throwable $e){}
    $hasPorcentaje = false;
    try { $c=$pdo->query("SHOW COLUMNS FROM mediciones LIKE 'porcentaje'"); $hasPorcentaje=$c&&$c->rowCount()>0; } catch(Throwable $e){}
    $hasTemperatura = false;
    try { $c=$pdo->query("SHOW COLUMNS FROM mediciones LIKE 'temperatura'"); $hasTemperatura=$c&&$c->rowCount()>0; } catch(Throwable $e){}
    $hasHumedad = false;
    try { $c=$pdo->query("SHOW COLUMNS FROM mediciones LIKE 'humedad'"); $hasHumedad=$c&&$c->rowCount()>0; } catch(Throwable $e){}

    // Construir query base
    $params = [];
    $where = ["DATE(m.fecha) BETWEEN :desde AND :hasta"];
    $params[':desde']=$fechaDesde;
    $params[':hasta']=$fechaHasta;
    if ($hasIdTanque && $idTanqueSel) {
        $where[]="m.id_tanque = :id_tanque";
        $params[':id_tanque']=$idTanqueSel;
    } elseif ($hasIdTanque && $tanqueFilter==='todos' && !empty($tanques)) {
        // si todos, filtrar por tanques del usuario
        $ids = array_map(fn($t)=>(int)($t['id_tanque']??0), $tanques);
        $ids = array_filter($ids);
        if ($ids) {
            $placeholders = implode(',', $ids);
            $where[]="m.id_tanque IN ($placeholders)";
        }
    } elseif (!$hasIdTanque && $idTanqueSel) {
        // join via sensores/dispositivos
        // se hara JOIN luego
    }

    if ($hasIdTanque) {
        $sql = "SELECT m.* FROM mediciones m WHERE ".implode(' AND ',$where)." ORDER BY m.fecha DESC, m.hora DESC LIMIT 200";
        $st=$pdo->prepare($sql);
        $st->execute($params);
        $rows=$st->fetchAll();
    } else {
        // intentar join sensores->dispositivos->tanques
        $joinWhere = implode(' AND ', array_map(fn($w)=> str_replace('m.id_tanque','d.id_tanque',$w), $where));
        // Ajustar params: mantener mismos
        try {
            $sql = "SELECT m.* FROM mediciones m INNER JOIN sensores s ON s.id_sensor=m.id_sensor INNER JOIN dispositivos d ON d.id_dispositivo=s.id_dispositivo WHERE ".str_replace('m.fecha','m.fecha',$joinWhere)." ORDER BY m.fecha DESC, m.hora DESC LIMIT 200";
            $st=$pdo->prepare($sql);
            // traducir condicion id_tanque si existe en where original
            $params2=$params;
            if (isset($params2[':id_tanque'])) { $params2[':id_tanque']=$params2[':id_tanque']; }
            $st->execute($params2);
            $rows=$st->fetchAll();
        } catch(Throwable $e) {
            // fallback sin filtro de tanque
            $sql = "SELECT m.* FROM mediciones m WHERE DATE(m.fecha) BETWEEN :desde AND :hasta ORDER BY m.fecha DESC, m.hora DESC LIMIT 200";
            $st=$pdo->prepare($sql);
            $st->execute([':desde'=>$fechaDesde, ':hasta'=>$fechaHasta]);
            $rows=$st->fetchAll();
        }
    }

    foreach ($rows as $r) {
        $fechaRaw = $r['fecha'] ?? '';
        $horaRaw = $r['hora'] ?? '';
        $ts = strtotime(trim($fechaRaw.' '.$horaRaw));
        $fechaFmt = $fechaRaw ? date('d/m/Y', $ts ?: time()) : '-';
        $horaFmt = $horaRaw ? date('H:i', $ts ?: time()) : ($fechaRaw ? date('H:i', $ts ?: time()) : '-');
        $nivel = $r['nivel'] ?? $r['distancia'] ?? $r['nivel_cm'] ?? '-';
        if (is_numeric($nivel)) $nivel = (int)round((float)$nivel);
        $pct = $r['porcentaje'] ?? $r['nivel_porcentaje'] ?? $r['porcentaje_nivel'] ?? null;
        if ($pct!==null && is_numeric($pct)) $pct = (int)round((float)$pct); else $pct = '-';
        $tmp = $r['temperatura'] ?? $r['temp'] ?? '-';
        if (is_numeric($tmp)) $tmp = (int)round((float)$tmp);
        $hum = $r['humedad'] ?? '-';
        if (is_numeric($hum)) $hum = (int)round((float)$hum);
        // estado segun pct
        $estado = 'Normal';
        if (is_numeric($pct)) {
            if ((int)$pct <= 20) $estado='Bajo';
            elseif ((int)$pct >= 90) $estado='Alto';
            else $estado='Normal';
        }
        $historialRows[] = ['fecha'=>$fechaFmt,'hora'=>$horaFmt,'nivel'=>$nivel,'pct'=>$pct,'tmp'=>$tmp,'hum'=>$hum,'estado'=>$estado,'ts'=>$ts];
    }

    // stats
    $pcts = array_filter(array_map(fn($r)=> is_numeric($r['pct'])? (int)$r['pct']: null, $historialRows));
    if (count($pcts)>0) {
        $avg = (int)round(array_sum($pcts)/count($pcts));
        $max = max($pcts);
        $min = min($pcts);
        $maxIdx = array_search($max, $pcts, true);
        $minIdx = array_search($min, $pcts, true);
        // buscar filas correspondientes (indice en historialRows filtrado)
        $keys = array_keys(array_filter($historialRows, fn($r)=> is_numeric($r['pct'])));
        $maxRow = $historialRows[$keys[$maxIdx] ?? $keys[0]] ?? null;
        $minRow = $historialRows[$keys[$minIdx] ?? $keys[0]] ?? null;
        $stats['promedio']=$avg;
        $stats['promedioSub']= ($avg*2).' cm promedio';
        $stats['total']=count($historialRows);
        $stats['mayor']=$max.'%';
        $stats['mayorVal']= $maxRow ? ($maxRow['fecha'].' '.$maxRow['hora']) : '-';
        $stats['menor']=$min.'%';
        $stats['menorVal']= $minRow ? ($minRow['fecha'].' '.$minRow['hora']) : '-';
    } else {
        $stats['total']=count($historialRows);
        $stats['promedio']= $stats['promedio'] ?: 0;
    }

    // chartData: agrupar por period
    // semana: ultimos 7 dias valores promedio pct por dia
    // mes: 30 dias, trimestre: 12 semanas/meses
    $periods = ['semana'=>7,'mes'=>30,'trimestre'=>90];
    foreach ($periods as $p=>$days) {
        $vals=[];
        for($i=$days-1;$i>=0;$i--) {
            $d=date('Y-m-d', strtotime("-$i days"));
            // calcular promedio pct de ese dia
            $dayVals = array_filter($rows, fn($r)=> substr($r['fecha']??'',0,10)===$d);
            if ($dayVals) {
                $sum=0;$c=0;
                foreach($dayVals as $dv){ $pv=$dv['porcentaje']??$dv['nivel_porcentaje']??null; if(is_numeric($pv)){ $sum+=(float)$pv; $c++;}}
                $vals[] = $c ? (int)round($sum/$c) : 0;
            } else $vals[] = 0;
        }
        // si mes o semana y todos 0, dejar ejemplo fallback? pero lo dejamos como 0 para que JS muestre linea plana
        // para trimestre agrupar por semana (12 puntos)
        if ($p==='trimestre') {
            // reducir a 12 puntos promediando cada ~7-8 dias
            $chunked=array_chunk($vals, (int)ceil(count($vals)/12));
            $vals12=array_map(fn($ch)=> count($ch)? (int)round(array_sum($ch)/count($ch)):0, $chunked);
            while(count($vals12)<12) $vals12[]=0;
            $vals = array_slice($vals12,0,12);
        } elseif($p==='mes') {
            $vals = array_slice($vals, -30);
        } elseif($p==='semana') {
            $vals = array_slice($vals, -7);
            // si historial vacio pero hay rows antiguas, usar al menos ultimos 7 valores crudos
            if(array_sum($vals)===0 && count($rows)>=7){
                $vals = array_map(fn($r)=> (int)round((float)($r['porcentaje']??50)), array_slice(array_reverse($rows),0,7));
            }
        }
        $chartData[$p]=$vals;
    }

} catch(Throwable $e){ error_log('historial error: '.$e->getMessage()); }
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>EVA - Historial</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<!--BARRA LATERAL-->
<aside class="sidebar">
 <a href="indexcli.php" class="sidebar-logo anim-float">
  <svg class="logo-svg" width="37" height="53" viewBox="0 0 37 53" fill="none" xmlns="http://www.w3.org/2000/svg">
   <circle cx="26.9785" cy="43.3208" r="3" fill="#3C75C6"/>
   <path d="M22.2598 51.4631C22.5628 51.3284 22.7998 51.0789 22.9188 50.7695C23.0378 50.4601 23.029 50.1161 22.8944 49.8131C22.7598 49.5102 22.5103 49.2731 22.2009 49.1541C21.8914 49.0351 21.5474 49.0439 21.2445 49.1785C19.8615 49.7947 18.1704 49.91 16.5293 49.6893C10.6749 48.8403 5.25149 44.4313 3.33478 38.7933C1.31772 33.0838 3.17894 26.6965 6.53436 21.4902C7.44919 20.0474 8.37331 18.6266 9.31541 17.209C9.93643 16.2742 10.5628 15.3443 11.1927 14.4131C11.8239 13.4796 12.4563 12.5481 13.0865 11.608C15.1972 8.47603 17.2131 5.24513 19.068 1.93363L16.9447 2.02347C21.7025 9.02347 26.4603 16.0235 31.2181 23.0235L31.1919 22.9833C32.9909 26.0258 33.9913 29.8118 33.9443 33.4127C33.9171 35.1216 33.6357 36.8141 33.0732 38.4048C32.9628 38.7174 32.9812 39.061 33.1242 39.3601C33.2673 39.6592 33.5233 39.8892 33.8359 39.9995C34.1485 40.1099 34.4921 40.0915 34.7912 39.9485C35.0903 39.8054 35.3203 39.5494 35.4306 39.2368C36.0899 37.3732 36.4132 35.4047 36.444 33.4537C36.4729 29.3045 35.4424 25.2908 33.3119 21.6583L33.2857 21.6181C28.5279 14.6181 23.7701 7.61813 19.0123 0.618135C18.4281 -0.241428 17.3986 -0.197868 16.889 0.707974C15.062 3.96876 13.1013 7.11201 11.0098 10.216C10.3852 11.1479 9.75453 12.0769 9.12179 13.0126C8.49053 13.9458 7.85971 14.8823 7.23314 15.8255C6.28253 17.2559 5.34771 18.6931 4.4231 20.1514C0.849809 25.6877 -1.38458 32.9081 0.971117 39.6076C3.26484 46.2002 9.2809 51.1272 16.1989 52.1674C18.1639 52.426 20.2912 52.3304 22.2598 51.4631Z" fill="#3C75C6"/>
  </svg>
  <span>EVA</span>
 </a>
 <nav>
  <ul>
   <li class="anim-slide1"><a href="indexcli.php"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg><span>Resumen</span></a></li>
   <li class="anim-slide2"><a href="mitanque.php"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="2" width="16" height="20" rx="2"/><line x1="4" y1="18" x2="20" y2="18"/><rect x="7" y="12" width="10" height="6" rx="1" fill="currentColor" opacity="0.3"/></svg><span>Mi Tanque</span></a></li>
   <li class="anim-slide3"><a href="alertas.php"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg><span>Alertas</span></a></li>
   <li class="anim-slide4"><a href="configuracion.php"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 01-2.83 2.83l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z"/></svg><span>Configuración</span></a></li>
   <li class="active anim-slide5"><a href="historial.php"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg><span>Historial</span></a></li>
   <li class="anim-slide6"><a href="mantenimiento.php"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91a6 6 0 017.94-7.94l-3.76 3.76z"/></svg><span>Mantenimiento</span></a></li>
  </ul>
 </nav>
 <div class="device-status">
  <h4>Dispositivo</h4>
  <div class="status-row">
   <svg class="wifi-icon anim-pulse" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12.55a11 11 0 0114.08 0"/><path d="M1.42 9a16 16 0 0121.16 0"/><path d="M8.53 16.11a6 6 0 016.95 0"/><line x1="12" y1="20" x2="12.01" y2="20"/></svg>
   <span class="status-text"><?php echo h($deviceStatus); ?></span>
  </div>
 </div>
</aside>

<div class="main">
 <!-- HEADER-->
 <header class="header">
  <div class="header-left">
   <button class="menu-btn"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#7a829a" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg></button>
  </div>
  <div class="header-right">
   <button class="theme-btn" id="themeToggle" title="Cambiar tema">
    <svg class="icon-sun" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
    <svg class="icon-moon hidden" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
   </button>
    <div class="user-dropdown" id="userDropdown">
     <div class="user-info">
      <div class="user-details"><div class="user-name"><?php echo h($_SESSION['nombre'] ?? 'Usuario'); ?></div><div class="user-role">Cliente</div></div>
      <div class="user-avatar"><?php echo h(strtoupper(substr($_SESSION['nombre'] ?? 'U', 0, 2))); ?></div>
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#7a829a" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
     </div>
      <div class="user-menu hidden" id="userMenu">
       <a class="user-menu-item" href="perfil.php">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        <span>Mi perfil</span>
       </a>
       <a class="user-menu-item" href="configuracion.php">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z"/></svg>
        <span>Configuración</span>
       </a>
       <a class="user-menu-item" href="../config/logout.php">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
        <span>Cerrar sesión</span>
       </a>
      </div>
    </div>
   </div>
  </header>

 <!--VISTA HISTORIAL-->
 <div class="view active" id="viewHistorial">

  <div class="config-page-title">Historial de mediciones</div>
  <div class="config-page-subtitle">Consulta el registro historico del nivel, temperatura y humedad de tus tanques</div>

  <!-- FILTROS -->
  <div class="card anim-bounce0" style="margin-bottom:20px">
   <div class="card-title">Filtros de busqueda</div>
   <form method="GET" class="alertas-filters" style="margin-bottom:0;flex-wrap:wrap;gap:10px;align-items:flex-end">
    <div style="display:flex;flex-direction:column;gap:5px;flex:1;min-width:140px">
     <span style="font-size:12px;color:var(--tx4)">Tanque</span>
     <select name="tanque" id="histTankSelect" style="background:var(--inp);border:1px solid var(--bd3);border-radius:8px;padding:9px 12px;color:var(--tx);font-size:13px;font-family:inherit;outline:none;width:100%">
      <option value="todos" <?php echo $tanqueFilter==='todos'?'selected':''; ?>>Todos los tanques</option>
      <?php foreach ($tanques as $t): $tid=(int)($t['id_tanque']??0); $tname=h($t['nombre']??'Tanque '.$tid); $sel = ((string)$tanqueFilter===(string)$tid)?'selected':''; ?>
       <option value="<?php echo $tid; ?>" <?php echo $sel; ?>><?php echo $tname; ?> - <?php echo h($t['ubicacion']??''); ?> (<?php echo (int)($t['capacidad_litros']??0); ?>L)</option>
      <?php endforeach; ?>
     </select>
    </div>
    <div style="display:flex;flex-direction:column;gap:5px;flex:1;min-width:140px">
     <span style="font-size:12px;color:var(--tx4)">Desde</span>
     <input type="date" name="desde" id="histDateFrom" value="<?php echo h($fechaDesde); ?>" style="background:var(--inp);border:1px solid var(--bd3);border-radius:8px;padding:9px 12px;color:var(--tx);font-size:13px;font-family:inherit;outline:none;width:100%">
    </div>
    <div style="display:flex;flex-direction:column;gap:5px;flex:1;min-width:140px">
     <span style="font-size:12px;color:var(--tx4)">Hasta</span>
     <input type="date" name="hasta" id="histDateTo" value="<?php echo h($fechaHasta); ?>" style="background:var(--inp);border:1px solid var(--bd3);border-radius:8px;padding:9px 12px;color:var(--tx);font-size:13px;font-family:inherit;outline:none;width:100%">
    </div>
    <button type="submit" class="alertas-filter active" id="histBtnFilter" style="white-space:nowrap">
     <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:middle;margin-right:4px"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
     Filtrar
    </button>
    <input type="hidden" name="period" id="histPeriodInput" value="<?php echo h($period); ?>">
   </form>
  </div>

  <!-- TABLA -->
  <div class="card anim-bounce1" style="margin-bottom:20px">
   <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px">
    <div class="card-title" style="margin-bottom:0">Mediciones historicas</div>
    <span id="histTableCount" style="font-size:12px;color:var(--tx4)"><?php echo count($historialRows); ?> registros</span>
   </div>
   <div style="overflow-x:auto">
    <table style="width:100%;border-collapse:collapse">
     <thead>
      <tr>
       <th style="padding:10px 14px;text-align:left;font-size:12px;font-weight:600;color:var(--tx4);border-bottom:2px solid var(--bd2);white-space:nowrap">Fecha</th>
       <th style="padding:10px 14px;text-align:left;font-size:12px;font-weight:600;color:var(--tx4);border-bottom:2px solid var(--bd2);white-space:nowrap">Hora</th>
       <th style="padding:10px 14px;text-align:left;font-size:12px;font-weight:600;color:var(--tx4);border-bottom:2px solid var(--bd2);white-space:nowrap">Nivel (cm)</th>
       <th style="padding:10px 14px;text-align:left;font-size:12px;font-weight:600;color:var(--tx4);border-bottom:2px solid var(--bd2);white-space:nowrap">Porcentaje</th>
       <th style="padding:10px 14px;text-align:left;font-size:12px;font-weight:600;color:var(--tx4);border-bottom:2px solid var(--bd2);white-space:nowrap">Temperatura</th>
       <th style="padding:10px 14px;text-align:left;font-size:12px;font-weight:600;color:var(--tx4);border-bottom:2px solid var(--bd2);white-space:nowrap">Humedad</th>
       <th style="padding:10px 14px;text-align:left;font-size:12px;font-weight:600;color:var(--tx4);border-bottom:2px solid var(--bd2);white-space:nowrap">Estado</th>
      </tr>
     </thead>
     <tbody id="histTableBody">
     <?php if (empty($historialRows)): ?>
       <tr><td colspan="7" style="padding:20px;text-align:center;color:var(--tx4);font-size:13px">No hay mediciones en el rango seleccionado.</td></tr>
     <?php else: foreach ($historialRows as $r): ?>
       <tr>
        <td style="padding:10px 14px;font-size:13px;color:var(--tx);border-bottom:1px solid var(--bd);white-space:nowrap"><?php echo h($r['fecha']); ?></td>
        <td style="padding:10px 14px;font-size:13px;color:var(--tx);border-bottom:1px solid var(--bd);white-space:nowrap"><?php echo h($r['hora']); ?></td>
        <td style="padding:10px 14px;font-size:13px;color:var(--tx);border-bottom:1px solid var(--bd);white-space:nowrap"><?php echo h((string)$r['nivel']); ?></td>
        <td style="padding:10px 14px;font-size:13px;color:var(--tx);border-bottom:1px solid var(--bd);white-space:nowrap"><?php echo is_numeric($r['pct'])? h((string)$r['pct']).'%': h((string)$r['pct']); ?></td>
        <td style="padding:10px 14px;font-size:13px;color:var(--tx);border-bottom:1px solid var(--bd);white-space:nowrap"><?php echo is_numeric($r['tmp'])? h((string)$r['tmp']).'°C': h((string)$r['tmp']); ?></td>
        <td style="padding:10px 14px;font-size:13px;color:var(--tx);border-bottom:1px solid var(--bd);white-space:nowrap"><?php echo is_numeric($r['hum'])? h((string)$r['hum']).'%': h((string)$r['hum']); ?></td>
        <td style="padding:10px 14px;font-size:13px;border-bottom:1px solid var(--bd);white-space:nowrap"><span style="padding:3px 10px;border-radius:6px;font-size:11px;font-weight:600;<?php echo $r['estado']==='Normal'?'color:var(--gn);background:rgba(76,175,80,0.12)':($r['estado']==='Bajo'?'color:var(--or);background:rgba(255,152,0,0.12)':'color:var(--rd2);background:rgba(244,67,54,0.12)'); ?>"><?php echo h($r['estado']); ?></span></td>
       </tr>
     <?php endforeach; endif; ?>
     </tbody>
    </table>
   </div>
  </div>

  <!-- GRAFICO -->
  <div class="card anim-bounce2">
   <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:10px">
    <div class="card-title" style="margin-bottom:0">Evolucion del nivel del tanque</div>
    <div class="history-tabs">
     <button class="history-tab <?php echo $period==='semana'?'active':''; ?>" data-period="semana">Semana</button>
     <button class="history-tab <?php echo $period==='mes'?'active':''; ?>" data-period="mes">Mes</button>
     <button class="history-tab <?php echo $period==='trimestre'?'active':''; ?>" data-period="trimestre">Trimestre</button>
    </div>
   </div>
   <div class="line-chart-area">
    <svg class="line-chart-svg" id="histChartSvg" viewBox="0 0 700 300" preserveAspectRatio="xMidYMid meet"></svg>
   </div>
   <div class="history-stats">
    <div class="history-stat">
     <div class="history-stat-label">Promedio</div>
     <div class="history-stat-value blue" id="statPromedio"><?php echo (int)$stats['promedio']; ?>%</div>
     <div class="history-stat-sub" id="statPromedioSub"><?php echo h($stats['promedioSub'] ?: '-'); ?></div>
    </div>
    <div class="history-stat">
     <div class="history-stat-label">Total lecturas</div>
     <div class="history-stat-value" id="statTotal"><?php echo (int)$stats['total']; ?></div>
     <div class="history-stat-sub" id="statTotalSub">mediciones</div>
    </div>
    <div class="history-stat">
     <div class="history-stat-label">Maximo</div>
     <div class="history-stat-value" id="statMayor"><?php echo h($stats['mayor']); ?></div>
     <div class="history-stat-sub" id="statMayorVal"><?php echo h($stats['mayorVal']); ?></div>
    </div>
    <div class="history-stat">
     <div class="history-stat-label">Minimo</div>
     <div class="history-stat-value" id="statMenor"><?php echo h($stats['menor']); ?></div>
     <div class="history-stat-sub" id="statMenorVal"><?php echo h($stats['menorVal']); ?></div>
    </div>
   </div>
  </div>

 </div>
</div>
<script>
window.EVA_HISTORIAL = <?php echo json_encode([
    'rows'=>$historialRows,
    'stats'=>$stats,
    'chartData'=>$chartData,
    'period'=>$period,
    'tanqueFilter'=>$tanqueFilter,
    'fechaDesde'=>$fechaDesde,
    'fechaHasta'=>$fechaHasta
], JSON_UNESCAPED_UNICODE); ?>;
</script>
<script src="js/script.js"></script>
</body>
</html>
