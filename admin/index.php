<?php
session_start();
if(!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'ADMIN'){
    header("Location: ../index.php");
    exit;
}
require_once(__DIR__ . '/../config/db.php');
$currentPage = 'inicio';
$pageSubtitle = 'Resumen general del sistema';

$cntClientes   = $conn->query("SELECT COUNT(*) FROM usuarios WHERE id_rol=3 AND activo=1")->fetch_row()[0];
$cntTecnicos   = $conn->query("SELECT COUNT(*) FROM usuarios u JOIN roles r ON u.id_rol=r.id_rol WHERE r.nombre='TECNICO' AND u.activo=1")->fetch_row()[0];
$cntEdificios  = $conn->query("SELECT COUNT(*) FROM edificios")->fetch_row()[0];
$cntDispositivos = $conn->query("SELECT COUNT(*) FROM dispositivos")->fetch_row()[0];
$cntTanques    = $conn->query("SELECT COUNT(*) FROM tanques")->fetch_row()[0];
$cntSensores   = $conn->query("SELECT COUNT(*) FROM sensores")->fetch_row()[0];
$cntInstalaciones = $conn->query("SELECT COUNT(*) FROM instalaciones")->fetch_row()[0];

$totalDev = max((int)$cntDispositivos, 1);
$devOnline   = (int)$conn->query("SELECT COUNT(*) FROM dispositivos WHERE estado='ONLINE'")->fetch_row()[0];
$devAlerta   = (int)$conn->query("SELECT COUNT(*) FROM dispositivos WHERE estado='MANTENIMIENTO'")->fetch_row()[0];
$devInactivo = (int)$conn->query("SELECT COUNT(*) FROM dispositivos WHERE estado='OFFLINE'")->fetch_row()[0];
$pctOnline   = round(($devOnline / $totalDev) * 100);
$pctAlerta   = round(($devAlerta / $totalDev) * 100);
$pctInactivo = max(0, 100 - $pctOnline - $pctAlerta);

$resMant = $conn->query("SELECT m.descripcion, t.ubicacion, DATE_FORMAT(m.fecha_programada,'%d/%m/%Y') AS fecha, m.estado FROM mantenimientos m JOIN dispositivos d ON m.id_dispositivo=d.id_dispositivo JOIN tanques t ON d.id_tanque=t.id_tanque WHERE m.estado IN ('PENDIENTE','EN_PROCESO') ORDER BY m.fecha_programada ASC LIMIT 3");

$resUsuarios = $conn->query("SELECT CONCAT(u.nombre,' ',u.apellido) AS nombre, r.nombre AS rol, u.activo, DATE_FORMAT(u.ultimo_acceso,'%d/%m/%Y %H:%i') AS ultimo_acceso FROM usuarios u JOIN roles r ON u.id_rol=r.id_rol ORDER BY u.fecha_registro DESC LIMIT 4");

$resDispositivos = $conn->query("SELECT d.nombre, t.nombre AS tanque, d.estado, d.bateria, d.intensidad_senal, d.ultima_conexion FROM dispositivos d JOIN tanques t ON d.id_tanque=t.id_tanque ORDER BY d.ultima_conexion DESC LIMIT 5");

$resAlertas = $conn->query("SELECT DATE_FORMAT(a.fecha_hora,'%d/%m %H:%i') AS fecha, t.nombre AS tanque, a.tipo, a.estado FROM alertas a JOIN tanques t ON a.id_tanque=t.id_tanque ORDER BY a.fecha_hora DESC LIMIT 5");

$resMantHist = $conn->query("SELECT CONCAT(ut.nombre,' ',ut.apellido) AS tecnico, d.nombre AS dispositivo, m.estado, DATE_FORMAT(m.fecha_programada,'%d/%m/%Y') AS fecha FROM mantenimientos m JOIN dispositivos d ON m.id_dispositivo=d.id_dispositivo JOIN usuarios ut ON m.id_tecnico=ut.id_usuario ORDER BY m.fecha_programada DESC LIMIT 5");

function badgeEstado($estado){
    $map = ['ONLINE'=>'activo','OFFLINE'=>'inactivo','MANTENIMIENTO'=>'advertencia'];
    $cls = $map[$estado] ?? 'inactivo';
    return '<span class="badge '.$cls.'">'.htmlspecialchars($estado).'</span>';
}
function badgeBateria($b){
    $b = (float)$b;
    if($b >= 60) $cls='activo';
    elseif($b >= 30) $cls='media';
    else $cls='baja';
    return '<span class="badge '.$cls.'">'.round($b).'%</span>';
}
function badgeSenal($s){
    $s = (int)$s;
    if($s >= -60){ $txt='Fuerte'; $cls='activo'; }
    elseif($s >= -80){ $txt='Media'; $cls='media'; }
    else{ $txt='Debil'; $cls='baja'; }
    return '<span class="badge '.$cls.'">'.$txt.'</span>';
}
function badgeAlerta($tipo){
    $tipo = str_replace('_',' ',$tipo);
    return '<span class="badge media">'.htmlspecialchars($tipo).'</span>';
}
function badgeMantEstado($e){
    $map = ['PENDIENTE'=>'pendiente','EN_PROCESO'=>'advertencia','FINALIZADO'=>'activo','CANCELADO'=>'inactivo'];
    $cls = $map[$e] ?? '';
    $txt = str_replace('_',' ',$e);
    return '<span class="badge '.$cls.'">'.htmlspecialchars($txt).'</span>';
}
function badgeUsuarioActivo($a){
    return $a ? '<span class="badge activo">Activo</span>' : '<span class="badge inactivo">Inactivo</span>';
}
function tiempoDesde($fecha){
    if(!$fecha) return 'Sin registro';
    $diff = time() - strtotime($fecha);
    if($diff < 60) return 'Hace '.$diff.' seg';
    if($diff < 3600) return 'Hace '.floor($diff/60).' min';
    if($diff < 86400) return 'Hace '.floor($diff/3600).' hrs';
    return 'Hace '.floor($diff/86400).' dias';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>EVA - Panel de Administracion</title>
<link rel="stylesheet" href="css/admin.css">
</head>
<body>
<?php include __DIR__ . '/includes/sidebar.php'; ?>

<div class="main">
 <?php include __DIR__ . '/includes/header.php'; ?>

 <div class="content">
  <div class="stats-row stats-row-6">
   <div class="stat-card anim-bounce0">
    <div class="stat-card-icon blue"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg></div>
    <div class="stat-card-info">
     <div class="stat-card-title">Clientes</div>
     <div class="stat-card-value"><?php echo $cntClientes; ?></div>
     <div class="stat-card-sub">Activos</div>
    </div>
   </div>
   <div class="stat-card anim-bounce1">
    <div class="stat-card-icon cyan"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 21h18M3 7v14M9 7v14M15 7v14M21 7v14M6 11h.01M6 15h.01M12 11h.01M12 15h.01M18 11h.01M18 15h.01"/></svg></div>
    <div class="stat-card-info">
     <div class="stat-card-title">Edificios</div>
     <div class="stat-card-value"><?php echo $cntEdificios; ?></div>
     <div class="stat-card-sub">Activos</div>
    </div>
   </div>
   <div class="stat-card anim-bounce2">
    <div class="stat-card-icon cyan"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="4" width="16" height="16" rx="2"/><rect x="9" y="9" width="6" height="6"/><line x1="9" y1="1" x2="9" y2="4"/><line x1="15" y1="1" x2="15" y2="4"/><line x1="9" y1="20" x2="9" y2="23"/><line x1="15" y1="20" x2="15" y2="23"/><line x1="20" y1="9" x2="23" y2="9"/><line x1="20" y1="14" x2="23" y2="14"/><line x1="1" y1="9" x2="4" y2="9"/><line x1="1" y1="14" x2="4" y2="14"/></svg></div>
    <div class="stat-card-info">
     <div class="stat-card-title">Dispositivos EVA</div>
     <div class="stat-card-value"><?php echo $cntDispositivos; ?></div>
     <div class="stat-card-sub">Activos</div>
    </div>
   </div>
   <div class="stat-card anim-bounce3">
    <div class="stat-card-icon green"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v16"/></svg></div>
    <div class="stat-card-info">
     <div class="stat-card-title">Instalaciones</div>
     <div class="stat-card-value"><?php echo $cntInstalaciones; ?></div>
     <div class="stat-card-sub">Totales</div>
    </div>
   </div>
   <div class="stat-card anim-bounce4">
    <div class="stat-card-icon orange"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg></div>
    <div class="stat-card-info">
     <div class="stat-card-title">Tanques</div>
     <div class="stat-card-value"><?php echo $cntTanques; ?></div>
     <div class="stat-card-sub">Totales</div>
    </div>
   </div>
   <div class="stat-card anim-bounce5">
    <div class="stat-card-icon blue"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg></div>
    <div class="stat-card-info">
     <div class="stat-card-title">Tecnicos</div>
     <div class="stat-card-value"><?php echo $cntTecnicos; ?></div>
     <div class="stat-card-sub">Activos</div>
    </div>
   </div>
   <div class="stat-card anim-bounce6">
    <div class="stat-card-icon purple"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/></svg></div>
    <div class="stat-card-info">
     <div class="stat-card-title">Sensores</div>
     <div class="stat-card-value"><?php echo $cntSensores; ?></div>
     <div class="stat-card-sub">Instalados</div>
    </div>
   </div>
  </div>

  <div class="main-grid">
   <div class="card anim-bounce0">
    <div class="card-header"><div class="card-title">Estado de dispositivos</div></div>
    <div class="donut-wrapper">
     <div class="donut-container">
      <svg class="donut-svg" id="donutSvg" viewBox="0 0 140 140"
       data-online="<?php echo $devOnline; ?>"
       data-alerta="<?php echo $devAlerta; ?>"
       data-inactivo="<?php echo $devInactivo; ?>"
       data-total="<?php echo $cntDispositivos; ?>"></svg>
      <div class="donut-center">
       <div class="donut-val"><?php echo $cntDispositivos; ?></div>
       <div class="donut-label">Total</div>
      </div>
     </div>
     <div class="donut-legend">
      <div class="donut-legend-item"><div class="donut-legend-dot" style="background:#4caf50"></div><div class="donut-legend-text">Activos</div><div class="donut-legend-val"><?php echo $devOnline; ?></div><div class="donut-legend-pct">(<?php echo $pctOnline; ?>%)</div></div>
      <div class="donut-legend-item"><div class="donut-legend-dot" style="background:#ff9800"></div><div class="donut-legend-text">Advertencias</div><div class="donut-legend-val"><?php echo $devAlerta; ?></div><div class="donut-legend-pct">(<?php echo $pctAlerta; ?>%)</div></div>
      <div class="donut-legend-item"><div class="donut-legend-dot" style="background:#f44336"></div><div class="donut-legend-text">Inactivos</div><div class="donut-legend-val"><?php echo $devInactivo; ?></div><div class="donut-legend-pct">(<?php echo $pctInactivo; ?>%)</div></div>
     </div>
    </div>
   </div>
   <div class="card anim-bounce1">
    <div class="card-header"><div class="card-title">Mantenimientos pendientes</div><a class="card-link" href="mantenimientos.php">Ver todos</a></div>
    <table class="table">
     <thead><tr><th>Tarea</th><th>Ubicacion</th><th>Fecha</th><th>Estado</th></tr></thead>
     <tbody>
      <?php if($resMant && $resMant->num_rows > 0): ?>
       <?php while($m = $resMant->fetch_assoc()): ?>
        <tr>
         <td><?php echo htmlspecialchars($m['descripcion'] ?? '-'); ?></td>
         <td><?php echo htmlspecialchars($m['ubicacion'] ?? '-'); ?></td>
         <td><?php echo $m['fecha']; ?></td>
         <td><?php echo badgeMantEstado($m['estado']); ?></td>
        </tr>
       <?php endwhile; ?>
      <?php else: ?>
       <tr><td colspan="4" style="text-align:center;color:var(--tx4)">No hay mantenimientos pendientes</td></tr>
      <?php endif; ?>
     </tbody>
    </table>
   </div>
   <div class="card anim-bounce2">
    <div class="card-header"><div class="card-title">Actividad del sistema (7 dias)</div></div>
    <div class="line-chart">
     <svg id="lineChart" width="100%" height="160" viewBox="0 0 320 160"></svg>
    </div>
   </div>
  </div>

  <div class="bottom-grid">
   <div class="card anim-bounce0">
    <div class="card-header"><div class="card-title">Usuarios recientes</div><a class="card-link" href="usuarios.php">Ver todos</a></div>
    <table class="table">
     <thead><tr><th>Usuario</th><th>Rol</th><th>Estado</th><th>Ultimo acceso</th></tr></thead>
     <tbody>
      <?php if($resUsuarios && $resUsuarios->num_rows > 0): ?>
       <?php while($u = $resUsuarios->fetch_assoc()): ?>
        <tr>
         <td><?php echo htmlspecialchars($u['nombre']); ?></td>
         <td><?php echo htmlspecialchars($u['rol']); ?></td>
         <td><?php echo badgeUsuarioActivo($u['activo']); ?></td>
         <td><?php echo $u['ultimo_acceso'] ?? 'Nunca'; ?></td>
        </tr>
       <?php endwhile; ?>
      <?php else: ?>
       <tr><td colspan="4" style="text-align:center;color:var(--tx4)">No hay usuarios registrados</td></tr>
      <?php endif; ?>
     </tbody>
    </table>
   </div>
   <div class="card anim-bounce1">
    <div class="card-header"><div class="card-title">Acciones rapidas</div></div>
    <div class="quick-actions">
     <a class="quick-action" href="usuarios.php">
      <div class="quick-action-icon green"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><path d="M16 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg></div>
      <div class="quick-action-text">Agregar usuario</div>
     </a>
     <a class="quick-action" href="empresas.php">
      <div class="quick-action-icon blue"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><path d="M3 21h18M3 7v14M9 7v14M15 7v14M21 7v14M6 11h.01M6 15h.01M12 11h.01M12 15h.01M18 11h.01M18 15h.01"/></svg></div>
      <div class="quick-action-text">Gestionar empresas</div>
     </a>
     <a class="quick-action" href="dispositivos.php">
      <div class="quick-action-icon orange"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><rect x="4" y="4" width="16" height="16" rx="2"/><rect x="9" y="9" width="6" height="6"/><line x1="9" y1="1" x2="9" y2="4"/><line x1="15" y1="1" x2="15" y2="4"/><line x1="9" y1="20" x2="9" y2="23"/><line x1="15" y1="20" x2="15" y2="23"/><line x1="20" y1="9" x2="23" y2="9"/><line x1="20" y1="14" x2="23" y2="14"/><line x1="1" y1="9" x2="4" y2="9"/><line x1="1" y1="14" x2="4" y2="14"/></svg></div>
      <div class="quick-action-text">Gestionar dispositivos</div>
     </a>
     <a class="quick-action" href="tanques.php">
      <div class="quick-action-icon purple"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg></div>
      <div class="quick-action-text">Gestionar tanques</div>
     </a>
    </div>
   </div>
  </div>

  <div class="charts-grid">
   <div class="card anim-bounce0">
    <div class="card-header"><div class="card-title">Nivel promedio de agua por dia</div></div>
    <div class="chart-container"><svg id="nivelDiaChart" viewBox="0 0 400 180"></svg></div>
   </div>
   <div class="card anim-bounce1">
    <div class="card-header"><div class="card-title">Nivel promedio por edificio</div></div>
    <div class="chart-container"><svg id="nivelEdifChart" viewBox="0 0 400 180"></svg></div>
   </div>
   <div class="card anim-bounce2">
    <div class="card-header"><div class="card-title">Consumo semanal</div></div>
    <div class="chart-container"><svg id="consumoChart" viewBox="0 0 400 180"></svg></div>
   </div>
  </div>

  <div class="tables-grid">
   <div class="card anim-bounce0">
    <div class="card-header"><div class="card-title">Dispositivos</div><a class="card-link" href="dispositivos.php">Ver todos</a></div>
    <div class="table-responsive">
     <table class="table">
      <thead><tr><th>Dispositivo</th><th>Tanque</th><th>Estado</th><th>Bateria</th><th>Senal</th><th>Ultima act.</th></tr></thead>
      <tbody>
       <?php if($resDispositivos && $resDispositivos->num_rows > 0): ?>
        <?php while($d = $resDispositivos->fetch_assoc()): ?>
         <tr>
          <td><?php echo htmlspecialchars($d['nombre']); ?></td>
          <td><?php echo htmlspecialchars($d['tanque']); ?></td>
          <td><?php echo badgeEstado($d['estado']); ?></td>
          <td><?php echo badgeBateria($d['bateria']); ?></td>
          <td><?php echo badgeSenal($d['intensidad_senal']); ?></td>
          <td><?php echo tiempoDesde($d['ultima_conexion']); ?></td>
         </tr>
        <?php endwhile; ?>
       <?php else: ?>
        <tr><td colspan="6" style="text-align:center;color:var(--tx4)">No hay dispositivos registrados</td></tr>
       <?php endif; ?>
      </tbody>
     </table>
    </div>
   </div>

   <div class="card anim-bounce1">
    <div class="card-header"><div class="card-title">Ultimas alertas</div><a class="card-link" href="alertas.php">Ver todas</a></div>
    <div class="table-responsive">
     <table class="table">
      <thead><tr><th>Fecha</th><th>Tanque</th><th>Tipo</th><th>Estado</th></tr></thead>
      <tbody>
       <?php if($resAlertas && $resAlertas->num_rows > 0): ?>
        <?php while($a = $resAlertas->fetch_assoc()): ?>
         <tr>
          <td><?php echo $a['fecha']; ?></td>
          <td><?php echo htmlspecialchars($a['tanque']); ?></td>
          <td><?php echo badgeAlerta($a['tipo']); ?></td>
          <td><?php echo badgeMantEstado($a['estado']); ?></td>
         </tr>
        <?php endwhile; ?>
       <?php else: ?>
        <tr><td colspan="4" style="text-align:center;color:var(--tx4)">No hay alertas registradas</td></tr>
       <?php endif; ?>
      </tbody>
     </table>
    </div>
   </div>

   <div class="card anim-bounce2">
    <div class="card-header"><div class="card-title">Ultimos mantenimientos</div><a class="card-link" href="mantenimientos.php">Ver todos</a></div>
    <div class="table-responsive">
     <table class="table">
      <thead><tr><th>Tecnico</th><th>Dispositivo</th><th>Estado</th><th>Fecha</th></tr></thead>
      <tbody>
       <?php if($resMantHist && $resMantHist->num_rows > 0): ?>
        <?php while($mh = $resMantHist->fetch_assoc()): ?>
         <tr>
          <td><?php echo htmlspecialchars($mh['tecnico']); ?></td>
          <td><?php echo htmlspecialchars($mh['dispositivo']); ?></td>
          <td><?php echo badgeMantEstado($mh['estado']); ?></td>
          <td><?php echo $mh['fecha']; ?></td>
         </tr>
        <?php endwhile; ?>
       <?php else: ?>
        <tr><td colspan="4" style="text-align:center;color:var(--tx4)">No hay mantenimientos registrados</td></tr>
       <?php endif; ?>
      </tbody>
     </table>
    </div>
   </div>
  </div>
 </div>
</div>
<script src="js/admin.js"></script>
<script>
(function(){
 var svg = document.getElementById('donutSvg');
 if(!svg) return;
 var devOnline = parseInt(svg.dataset.online)||0;
 var devAlerta = parseInt(svg.dataset.alerta)||0;
 var devInactivo = parseInt(svg.dataset.inactivo)||0;
 var total = parseInt(svg.dataset.total)||1;
 var pctOnline = Math.round((devOnline/total)*100);
 var pctAlerta = Math.round((devAlerta/total)*100);
 var pctInactivo = 100 - pctOnline - pctAlerta;
 var r=50, c=2*Math.PI*r, offset=0;
 svg.innerHTML = '<circle cx="70" cy="70" r="'+r+'" class="donut-bg"/>';
 [{p:pctOnline,color:'#4caf50'},{p:pctAlerta,color:'#ff9800'},{p:pctInactivo,color:'#f44336'}].forEach(function(d,i){
  var len=(d.p/100)*c;
  var circle=document.createElementNS('http://www.w3.org/2000/svg','circle');
  circle.setAttribute('cx','70');circle.setAttribute('cy','70');circle.setAttribute('r',String(r));
  circle.setAttribute('fill','none');circle.setAttribute('stroke',d.color);circle.setAttribute('stroke-width','12');
  circle.setAttribute('stroke-linecap','round');circle.setAttribute('stroke-dasharray','0 '+c);
  circle.setAttribute('stroke-dashoffset',String(-offset));
  circle.style.transition='stroke-dasharray 1s '+i*0.2+'s cubic-bezier(.4,0,.2,1)';
  svg.appendChild(circle);
  setTimeout(function(){circle.setAttribute('stroke-dasharray',len+' '+(c-len));},100);
  offset+=len;
 });
})();
</script>
</body>
</html>
