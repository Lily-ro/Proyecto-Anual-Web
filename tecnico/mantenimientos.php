<?php
session_start();
if(!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'TECNICO'){
    header("Location: ../index.php");
    exit;
}
require_once(__DIR__ . '/../config/db.php');
$id_tecnico = (int)($_SESSION['id_usuario'] ?? 0);
$pdo = eva_pdo();
$msgOk = null; $msgErr = null;
if($_SERVER['REQUEST_METHOD']==='POST'){
    try{
        if(isset($_POST['accion_mant']) && isset($_POST['id_mantenimiento'])){
            $idM = (int)$_POST['id_mantenimiento'];
            $accion = trim($_POST['accion_mant']);
            $obs = trim($_POST['observaciones'] ?? '');
            $map = ['PENDIENTE'=>'PENDIENTE','EN_PROCESO'=>'EN_PROCESO','FINALIZADO'=>'FINALIZADO','CANCELADO'=>'CANCELADO'];
            if(!isset($map[$accion])) throw new Exception('Acción no válida');
            $nuevo = $map[$accion];
            $campos = "estado = ?, observaciones = COALESCE(CONCAT(COALESCE(observaciones,''), CASE WHEN observaciones IS NULL OR observaciones='' THEN '' ELSE '\n' END, ?), observaciones)";
            $params = [$nuevo, $obs ? ('['.date('d/m/Y H:i').' Tec#'.$id_tecnico.'] '.$obs) : ''];
            if($nuevo==='FINALIZADO'){ $campos .= ", fecha_realizada = NOW()"; }
            elseif($nuevo==='EN_PROCESO'){ $campos .= ", fecha_realizada = NULL"; }
            $sqlU = "UPDATE mantenimientos SET $campos, id_tecnico = COALESCE(id_tecnico, ?) WHERE id_mantenimiento = ?";
            $params[] = $id_tecnico; $params[] = $idM;
            $pdo->prepare($sqlU)->execute($params);
            $pdo->prepare("INSERT INTO log_actividad (id_usuario, accion, detalle) VALUES (?,?,?)")->execute([$id_tecnico,'UPDATE',"Mant $idM -> $nuevo"]);
            $msgOk = "Mantenimiento #$idM actualizado a $nuevo.";
        } elseif(isset($_POST['accion_sol']) && isset($_POST['id_solicitud'])){
            $idS = (int)$_POST['id_solicitud'];
            $accion = trim($_POST['accion_sol']);
            $mapS = ['PENDIENTE'=>'PENDIENTE','ACEPTADA'=>'ACEPTADA','EN_PROCESO'=>'EN_PROCESO','FINALIZADA'=>'FINALIZADA','CANCELADA'=>'CANCELADA'];
            if(!isset($mapS[$accion])) throw new Exception('Acción no válida');
            $nuevo = $mapS[$accion];
            $obs = trim($_POST['observaciones_sol'] ?? '');
            if($nuevo==='ACEPTADA'){ $pdo->prepare("UPDATE solicitudes_mantenimiento SET estado=?, id_tecnico=?, fecha_aceptada=NOW() WHERE id_solicitud=?")->execute([$nuevo,$id_tecnico,$idS]); }
            elseif($nuevo==='EN_PROCESO'){ $pdo->prepare("UPDATE solicitudes_mantenimiento SET estado=?, id_tecnico=COALESCE(id_tecnico,?), fecha_inicio=NOW() WHERE id_solicitud=?")->execute([$nuevo,$id_tecnico,$idS]); }
            elseif($nuevo==='FINALIZADA'){ $pdo->prepare("UPDATE solicitudes_mantenimiento SET estado=?, fecha_finalizada=NOW(), observaciones_admin=CONCAT(COALESCE(observaciones_admin,''), ?) WHERE id_solicitud=?")->execute([$nuevo, $obs ? "\n[".date('d/m/Y H:i')."] $obs" : '', $idS]); }
            else { $pdo->prepare("UPDATE solicitudes_mantenimiento SET estado=?, observaciones_admin=CONCAT(COALESCE(observaciones_admin,''), ?) WHERE id_solicitud=?")->execute([$nuevo, $obs ? "\n[".date('d/m/Y H:i')."] $obs" : '', $idS]); }
            $pdo->prepare("INSERT INTO log_actividad (id_usuario, accion, detalle) VALUES (?,?,?)")->execute([$id_tecnico,'UPDATE',"Solicitud $idS -> $nuevo"]);
            $msgOk = "Solicitud #$idS actualizada a $nuevo.";
            if($nuevo==='FINALIZADA' && !empty($_POST['crear_mantenimiento'])){
                $st = $pdo->prepare("SELECT id_tanque FROM solicitudes_mantenimiento WHERE id_solicitud=?");
                $st->execute([$idS]); $rowS=$st->fetch();
                if($rowS){
                    $dev = $pdo->prepare("SELECT id_dispositivo FROM dispositivos WHERE id_tanque=? LIMIT 1");
                    $dev->execute([$rowS['id_tanque']]); $drow=$dev->fetch();
                    if($drow){ $pdo->prepare("INSERT INTO mantenimientos (id_solicitud, id_dispositivo, id_tecnico, tipo, descripcion, fecha_programada, estado) VALUES (?,?,?,?,?,NOW(),'FINALIZADO')")->execute([$idS,$drow['id_dispositivo'],$id_tecnico,'CORRECTIVO', trim($_POST['descripcion_final'] ?? 'Resuelto desde solicitud #'.$idS)]); }
                }
            }
        }
    }catch(Throwable $e){ $msgErr = $e->getMessage(); }
    if($msgOk || $msgErr){ header("Location: mantenimientos.php?ok=".urlencode($msgOk??'')."&err=".urlencode($msgErr??'')); exit; }
}
if(isset($_GET['ok']) && $_GET['ok']!=='') $msgOk = $_GET['ok'];
if(isset($_GET['err']) && $_GET['err']!=='') $msgErr = $_GET['err'];
function mantBadgeEstadoTec($estado){
    $m = ['PENDIENTE'=>['cls'=>'programado','txt'=>'Pendiente'],'EN_PROCESO'=>['cls'=>'pendiente','txt'=>'En proceso'],'FINALIZADO'=>['cls'=>'completado','txt'=>'Finalizado'],'CANCELADO'=>['cls'=>'inactivo','txt'=>'Cancelado']];
    $d = $m[$estado] ?? ['cls'=>'inactivo','txt'=>htmlspecialchars($estado)];
    return '<span class="badge '.$d['cls'].'">'.$d['txt'].'</span>';
}
function mantTipoTxt($tipo){
    $map = ['PREVENTIVO'=>'Preventivo','CORRECTIVO'=>'Correctivo','PREDICTIVO'=>'Predictivo'];
    return $map[$tipo] ?? htmlspecialchars($tipo);
}
function fmtFecha($f){
    if(!$f || $f==='0000-00-00 00:00:00') return '—';
    $ts = strtotime($f);
    if(!$ts) return htmlspecialchars($f);
    return date('d/m/Y H:i', $ts);
}
function dirCliente($row){
    $partes=[];
    if(!empty($row['cli_calle']) && !empty($row['cli_numero'])) $partes[] = trim($row['cli_calle'].' '.$row['cli_numero'].(!empty($row['cli_piso'])?' Piso '.$row['cli_piso']:''));
    elseif(!empty($row['cli_calle'])) $partes[]=$row['cli_calle'];
    if(!empty($row['cli_localidad'])) $partes[]=$row['cli_localidad'];
    if(!empty($row['cli_provincia'])) $partes[]=$row['cli_provincia'];
    if(!empty($partes)) return implode(', ', $partes);
    if(!empty($row['edificio_direccion'])) return $row['edificio_direccion'];
    if(!empty($row['tanque_ubicacion'])) return $row['tanque_ubicacion'];
    return '—';
}
$sql = "SELECT m.id_mantenimiento, m.tipo, m.descripcion, m.observaciones, m.fecha_programada, m.fecha_realizada, m.estado, m.costo, m.id_solicitud, d.id_dispositivo, d.nombre AS dispositivo_nombre, d.mac_address, t.id_tanque, t.nombre AS tanque_nombre, t.ubicacion AS tanque_ubicacion, e.id_edificio, e.nombre AS edificio_nombre, e.direccion AS edificio_direccion, ut.id_usuario AS tec_id, ut.nombre AS tec_nombre, ut.apellido AS tec_apellido, ut.email AS tec_email, c.id_cliente AS cli_id, c.nombre AS cli_nombre, c.apellido AS cli_apellido, c.email AS cli_email, c.telefono AS cli_telefono, c.calle AS cli_calle, c.numero AS cli_numero, c.piso AS cli_piso, c.codigo_postal AS cli_cp, c.localidad AS cli_localidad, c.partido AS cli_partido, c.provincia AS cli_provincia, u_owner.id_usuario AS owner_id, u_owner.nombre AS owner_nombre, u_owner.apellido AS owner_apellido, u_owner.email AS owner_email, sm.id_solicitud AS sm_id, sm.descripcion AS sm_desc, u_sol.id_usuario AS sol_uid, u_sol.nombre AS sol_nombre, u_sol.apellido AS sol_apellido, u_sol.email AS sol_email, c2.id_cliente AS cli2_id, c2.nombre AS cli2_nombre, c2.apellido AS cli2_apellido, c2.email AS cli2_email, c2.calle AS cli2_calle, c2.numero AS cli2_numero, c2.localidad AS cli2_localidad, c2.provincia AS cli2_provincia FROM mantenimientos m INNER JOIN dispositivos d ON d.id_dispositivo = m.id_dispositivo LEFT JOIN tanques t ON t.id_tanque = d.id_tanque LEFT JOIN edificios e ON e.id_edificio = t.id_edificio LEFT JOIN usuarios u_owner ON u_owner.id_usuario = e.id_usuario LEFT JOIN clientes c ON c.id_usuario = u_owner.id_usuario LEFT JOIN usuarios ut ON ut.id_usuario = m.id_tecnico LEFT JOIN solicitudes_mantenimiento sm ON sm.id_solicitud = m.id_solicitud LEFT JOIN usuarios u_sol ON u_sol.id_usuario = sm.id_usuario LEFT JOIN clientes c2 ON c2.id_usuario = u_sol.id_usuario ORDER BY (m.id_tecnico = ?) DESC, m.fecha_programada DESC, m.id_mantenimiento DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id_tecnico]);
$mantenimientos = $stmt->fetchAll(PDO::FETCH_ASSOC);
$sqlSol = "SELECT sm.id_solicitud, sm.id_tanque, sm.id_usuario, sm.id_tecnico, sm.descripcion, sm.estado, sm.fecha_solicitud, sm.fecha_finalizada, t.nombre AS tanque_nombre, t.ubicacion AS tanque_ubicacion, e.nombre AS edificio_nombre, e.direccion AS edificio_direccion, u.nombre AS cli_nombre, u.apellido AS cli_apellido, u.email AS cli_email, c.calle AS cli_calle, c.numero AS cli_numero, c.localidad AS cli_localidad, c.provincia AS cli_provincia, c.telefono AS cli_telefono, ut.nombre AS tec_nombre, ut.apellido AS tec_apellido, ut.email AS tec_email FROM solicitudes_mantenimiento sm LEFT JOIN tanques t ON t.id_tanque=sm.id_tanque LEFT JOIN edificios e ON e.id_edificio=t.id_edificio LEFT JOIN usuarios u ON u.id_usuario=sm.id_usuario LEFT JOIN clientes c ON c.id_usuario=u.id_usuario LEFT JOIN usuarios ut ON ut.id_usuario=sm.id_tecnico ORDER BY sm.fecha_solicitud DESC";
$solicitudes = $pdo->query($sqlSol)->fetchAll(PDO::FETCH_ASSOC);
$total = count($mantenimientos);
$pend = 0; $enproc=0; $fin=0; $canc=0;
foreach($mantenimientos as $mm){
    if($mm['estado']==='PENDIENTE') $pend++;
    elseif($mm['estado']==='EN_PROCESO') $enproc++;
    elseif($mm['estado']==='FINALIZADO') $fin++;
    elseif($mm['estado']==='CANCELADO') $canc++;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Mantenimientos - EVA</title>
<link rel="stylesheet" href="css/tecnico.css">
<style>
.mant-cliente-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px 18px;margin-top:12px}
.mant-cliente-item{display:flex;flex-direction:column;gap:2px}
.mant-cliente-label{font-size:11px;color:var(--tx5);font-weight:600;text-transform:uppercase;letter-spacing:.4px}
.mant-cliente-val{font-size:13px;color:var(--tx2);font-weight:500;word-break:break-word}
.mant-divider{height:1px;background:var(--bd2);margin:12px 0}
.mant-prox{font-size:12px;color:var(--tx4)}
@media(max-width:900px){.mant-cliente-grid{grid-template-columns:1fr}}
</style>
</head>
<body>
<aside class="sidebar">
 <a href="indextec.php" class="sidebar-logo">
  <svg class="logo-svg" width="37" height="53" viewBox="0 0 37 53" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="26.9785" cy="43.3208" r="3" fill="#3C75C6"/><path d="M22.2598 51.4631C22.5628 51.3284 22.7998 51.0789 22.9188 50.7695C23.0378 50.4601 23.029 50.1161 22.8944 49.8131C22.7598 49.5102 22.5103 49.2731 22.2009 49.1541C21.8914 49.0351 21.5474 49.0439 21.2445 49.1785C19.8615 49.7947 18.1704 49.91 16.5293 49.6893C10.6749 48.8403 5.25149 44.4313 3.33478 38.7933C1.31772 33.0838 3.17894 26.6965 6.53436 21.4902C7.44919 20.0474 8.37331 18.6266 9.31541 17.209C9.93643 16.2742 10.5628 15.3443 11.1927 14.4131C11.8239 13.4796 12.4563 12.5481 13.0865 11.608C15.1972 8.47603 17.2131 5.24513 19.068 1.93363L16.9447 2.02347C21.7025 9.02347 26.4603 16.0235 31.2181 23.0235L31.1919 22.9833C32.9909 26.0258 33.9913 29.8118 33.9443 33.4127C33.9171 35.1216 33.6357 36.8141 33.0732 38.4048C32.9628 38.7174 32.9812 39.061 33.1242 39.3601C33.2673 39.6592 33.5233 39.8892 33.8359 39.9995C34.1485 40.1099 34.4921 40.0915 34.7912 39.9485C35.0903 39.8054 35.3203 39.5494 35.4306 39.2368C36.0899 37.3732 36.4132 35.4047 36.444 33.4537C36.4729 29.3045 35.4424 25.2908 33.3119 21.6583L33.2857 21.6181C28.5279 14.6181 23.7701 7.61813 19.0123 0.618135C18.4281 -0.241428 17.3986 -0.197868 16.889 0.707974C15.062 3.96876 13.1013 7.11201 11.0098 10.216C10.3852 11.1479 9.75453 12.0769 9.12179 13.0126C8.49053 13.9458 7.85971 14.8823 7.23314 15.8255C6.28253 17.2559 5.34771 18.6931 4.4231 20.1514C0.849809 25.6877 -1.38458 32.9081 0.971117 39.6076C3.26484 46.2002 9.2809 51.1272 16.1989 52.1674C18.1639 52.426 20.2912 52.3304 22.2598 51.4631Z" fill="#3C75C6"/></svg><span>EVA</span>
 </a>
 <div class="sidebar-role">Técnico</div>
 <nav>
  <ul>
   <li class="anim-slide1"><a href="indextec.php"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg><span>Inicio</span></a></li>
   <li class="anim-slide2"><a href="misdispositivos.php"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="4" width="16" height="16" rx="2"/><rect x="9" y="9" width="6" height="6"/><line x1="9" y1="1" x2="9" y2="4"/><line x1="15" y1="1" x2="15" y2="4"/><line x1="9" y1="20" x2="9" y2="23"/><line x1="15" y1="20" x2="15" y2="23"/><line x1="20" y1="9" x2="23" y2="9"/><line x1="20" y1="14" x2="23" y2="14"/><line x1="1" y1="9" x2="4" y2="9"/><line x1="1" y1="14" x2="4" y2="14"/></svg><span>Mis dispositivos</span></a></li>
   <li class="anim-slide3"><a href="alertas.php"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg><span>Alertas</span></a></li>
   <li class="anim-slide4"><a href="mediciones.php"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg><span>Mediciones</span></a></li>
   <li class="anim-slide5"><a href="mitanques.php"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 21h18M3 7v1a3 3 0 006 0V7m0 0H9m6 0h6M3 7l1.5-4h15L21 7M4 7v10a2 2 0 002 2h12a2 2 0 002-2V7"/></svg><span>Mis tanques</span></a></li>
   <li class="anim-slide6"><a href="sensores.php"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M12 1v4m0 14v4m-7.07-15.07l2.83 2.83m8.48 8.48l2.83 2.83M1 12h4m14 0h4m-15.07 7.07l2.83-2.83m8.48-8.48l2.83-2.83"/></svg><span>Sensores</span></a></li>
   <li class="open anim-slide7" data-toggle="mant"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91a6 6 0 017.94-7.94l-3.76 3.76z"/></svg><span>Mantenimiento</span><span class="arrow">&#9654;</span></li>
   <ul class="sub-menu open"><li><a href="mantenimientos.php" style="font-weight:600">Lista de mantenimientos</a></li></ul>
   <li class="anim-slide8" data-toggle="inst"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v16"/></svg><span>Instalación</span><span class="arrow">&#9654;</span></li>
   <ul class="sub-menu"><li><a href="instalaciones.php">Lista de instalaciones</a></li></ul>
   <li class="anim-slide9"><a href="historialtecnico.php"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg><span>Histórico Técnico</span></a></li>
   <li class="anim-slide10"><a href="notificaciones.php"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 01-3.46 0"/></svg><span>Notificaciones</span></a></li>
   <li class="anim-slide11"><a href="perfil.php"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg><span>Perfil</span></a></li>
  </ul>
 </nav>
 <div class="sidebar-footer"><a href="../config/logout.php"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg><span>Cerrar sesión</span></a></div>
</aside>
<div class="main">
 <header class="header">
  <div class="header-left"><div class="header-greeting">Mantenimientos de clientes</div><div class="header-subtitle">Mantenimientos asignados con información del cliente y equipo</div></div>
  <div class="header-right">
   <button class="bell-btn" title="Notificaciones"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 01-3.46 0"/></svg><span class="bell-badge">3</span></button>
   <button class="theme-btn" id="themeToggle" title="Cambiar tema"><svg class="icon-sun" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg><svg class="icon-moon hidden" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg></button>
   <div class="user-dropdown" id="userDropdown"><div class="user-info"><div class="user-details"><div class="user-name">Técnico</div><div class="user-role">Soporte</div></div><div class="user-avatar">T</div><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#7a829a" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg></div><div class="user-menu hidden" id="userMenu"><a class="user-menu-item" href="perfil.php"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg><span>Mi perfil</span></a></div></div>
  </div>
 </header>
 <div class="content">
  <?php if($msgOk): ?><div style="background:rgba(76,175,80,0.12);border:1px solid rgba(76,175,80,0.2);color:var(--gn);padding:12px 16px;border-radius:10px;margin-bottom:16px"><?php echo htmlspecialchars($msgOk); ?></div><?php endif; ?>
  <?php if($msgErr): ?><div style="background:rgba(244,67,54,0.12);border:1px solid rgba(244,67,54,0.2);color:var(--rd);padding:12px 16px;border-radius:10px;margin-bottom:16px"><?php echo htmlspecialchars($msgErr); ?></div><?php endif; ?>
  <div class="stats-row anim-bounce0">
   <div class="stat-card"><div class="stat-card-icon blue"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91a6 6 0 017.94-7.94l-3.76 3.76z"/></svg></div><div class="stat-card-info"><div class="stat-card-title">Total</div><div class="stat-card-value"><?php echo $total; ?></div></div></div>
   <div class="stat-card"><div class="stat-card-icon orange"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg></div><div class="stat-card-info"><div class="stat-card-title">En proceso</div><div class="stat-card-value"><?php echo $enproc; ?></div></div></div>
   <div class="stat-card"><div class="stat-card-icon green"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg></div><div class="stat-card-info"><div class="stat-card-title">Finalizados</div><div class="stat-card-value"><?php echo $fin; ?></div></div></div>
   <div class="stat-card"><div class="stat-card-icon cyan"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg></div><div class="stat-card-info"><div class="stat-card-title">Pendientes</div><div class="stat-card-value"><?php echo $pend; ?></div></div></div>
  </div>
  <div class="filtros-bar anim-bounce1">
   <input class="form-input" type="text" placeholder="Buscar por cliente, tanque o descripción..." id="busquedaMant" oninput="filtrarMantenimientos()">
   <select class="form-select" id="filtroEstadoMant" onchange="filtrarMantenimientos()"><option value="">Todos los estados</option><option value="pendiente">Pendiente</option><option value="en_proceso">En proceso</option><option value="finalizado">Finalizado</option><option value="cancelado">Cancelado</option></select>
   <select class="form-select" id="filtroTipoMant" onchange="filtrarMantenimientos()"><option value="">Todos los tipos</option><option value="preventivo">Preventivo</option><option value="correctivo">Correctivo</option><option value="predictivo">Predictivo</option></select>
  </div>
  <?php if(empty($mantenimientos) && empty($solicitudes)): ?>
   <div class="card"><p style="text-align:center;color:var(--tx4);padding:20px">No hay mantenimientos ni solicitudes registradas.</p></div>
  <?php else: ?>
  <?php if(!empty($mantenimientos)): ?>
  <div class="mant-lista" id="listaMantenimientos">
   <?php foreach($mantenimientos as $idx=>$row):
        $cliNombre = $row['cli_nombre'] ?? null; $cliApellido = $row['cli_apellido'] ?? null; $cliEmail = $row['cli_email'] ?? null;
        if(empty($cliNombre) && !empty($row['cli2_nombre'])){ $cliNombre=$row['cli2_nombre']; $cliApellido=$row['cli2_apellido']; $cliEmail=$row['cli2_email']; }
        if(empty($cliNombre) && !empty($row['owner_nombre'])){ $cliNombre=$row['owner_nombre']; $cliApellido=$row['owner_apellido']; $cliEmail=$row['owner_email']; }
        if(empty($cliNombre) && !empty($row['sol_nombre'])){ $cliNombre=$row['sol_nombre']; $cliApellido=$row['sol_apellido']; $cliEmail=$row['sol_email']; }
        if(empty($cliNombre)) $cliNombre='—'; if(empty($cliApellido)) $cliApellido=''; if(empty($cliEmail)) $cliEmail='—';
        $direccion = dirCliente($row); if($direccion==='—' && !empty($row['cli2_calle'])) $direccion=trim($row['cli2_calle'].' '.$row['cli2_numero'].', '.$row['cli2_localidad']);
        $tanqueTxt = htmlspecialchars($row['tanque_nombre'] ?? 'Sin tanque'); $dispTxt = htmlspecialchars($row['dispositivo_nombre'] ?? ('ID '.$row['id_dispositivo'])); $equipoTxt = $tanqueTxt.' — '. $dispTxt . ' (#'.$row['id_dispositivo'].')'; if(!empty($row['id_tanque'])) $equipoTxt .= ' · Tanque #'.$row['id_tanque'];
        $tipoTxt = mantTipoTxt($row['tipo']); $estadoNorm = str_replace('_','-',strtolower($row['estado'])); $tipoNorm = strtolower($row['tipo']); $tecTxt = trim(($row['tec_nombre']??'').' '.($row['tec_apellido']??'')); if($tecTxt==='') $tecTxt='—'; $fechaProg = fmtFecha($row['fecha_programada']); $fechaReal = fmtFecha($row['fecha_realizada']); $proxFecha = $row['fecha_realizada'] ? $fechaReal : '—'; $desc = $row['descripcion'] ?? ''; $obs = $row['observaciones'] ?? '';
   ?>
   <div class="mant-card anim-bounce<?php echo $idx%4; ?>" data-estado="<?php echo $estadoNorm; ?>" data-tipo="<?php echo $tipoNorm; ?>">
    <div class="mant-card-header" onclick="toggleMantCard(this)"><div class="mant-card-info"><div class="mant-card-titulo"><?php echo htmlspecialchars(trim($cliNombre.' '.$cliApellido)); ?> — <?php echo htmlspecialchars($tipoTxt); ?></div><div class="mant-card-meta"><span><?php echo $equipoTxt; ?></span><span><?php echo htmlspecialchars($tipoTxt); ?></span><span><?php echo $fechaProg; ?></span></div></div><?php echo mantBadgeEstadoTec($row['estado']); ?></div>
    <div class="mant-card-body">
     <div class="mant-cliente-grid"><div class="mant-cliente-item"><span class="mant-cliente-label">Cliente</span><span class="mant-cliente-val"><?php echo htmlspecialchars(trim($cliNombre.' '.$cliApellido)); ?></span></div><div class="mant-cliente-item"><span class="mant-cliente-label">Email / Usuario</span><span class="mant-cliente-val"><?php echo htmlspecialchars($cliEmail); ?></span></div><div class="mant-cliente-item"><span class="mant-cliente-label">Teléfono</span><span class="mant-cliente-val"><?php echo htmlspecialchars($row['cli_telefono'] ?? '—'); ?></span></div><div class="mant-cliente-item"><span class="mant-cliente-label">Dirección</span><span class="mant-cliente-val"><?php echo htmlspecialchars($direccion); ?></span></div><div class="mant-cliente-item"><span class="mant-cliente-label">Tanque / Equipo</span><span class="mant-cliente-val"><?php echo $equipoTxt; ?></span></div><div class="mant-cliente-item"><span class="mant-cliente-label">Edificio</span><span class="mant-cliente-val"><?php echo htmlspecialchars($row['edificio_nombre'] ?? '—'); ?> <?php echo $row['edificio_direccion'] ? '· '.htmlspecialchars($row['edificio_direccion']) : ''; ?></span></div><div class="mant-cliente-item"><span class="mant-cliente-label">Fecha programada</span><span class="mant-cliente-val"><?php echo $fechaProg; ?></span></div><div class="mant-cliente-item"><span class="mant-cliente-label">Próxima fecha / Realizada</span><span class="mant-cliente-val"><?php echo $proxFecha; ?></span></div><div class="mant-cliente-item"><span class="mant-cliente-label">Tipo</span><span class="mant-cliente-val"><?php echo htmlspecialchars($tipoTxt); ?></span></div><div class="mant-cliente-item"><span class="mant-cliente-label">Técnico asignado</span><span class="mant-cliente-val"><?php echo htmlspecialchars($tecTxt); ?> (<?php echo htmlspecialchars($row['tec_email'] ?? ''); ?>)</span></div><div class="mant-cliente-item"><span class="mant-cliente-label">Estado</span><span class="mant-cliente-val"><?php echo mantBadgeEstadoTec($row['estado']); ?></span></div><div class="mant-cliente-item"><span class="mant-cliente-label">Costo</span><span class="mant-cliente-val">$<?php echo number_format((float)$row['costo'],2,',','.'); ?></span></div></div>
     <div class="mant-divider"></div>
     <div class="form-group"><label class="form-label">Descripción</label><p style="font-size:13px;color:var(--tx);line-height:1.5"><?php echo nl2br(htmlspecialchars($desc ?: '—')); ?></p></div>
     <div class="form-group"><label class="form-label">Observaciones</label><p style="font-size:13px;color:var(--tx);line-height:1.5"><?php echo nl2br(htmlspecialchars($obs ?: '—')); ?></p></div>
     <?php if(!empty($row['sm_id'])): ?><div class="mant-prox">Solicitud vinculada #<?php echo (int)$row['sm_id']; ?> · <?php echo htmlspecialchars($row['sm_desc'] ?? ''); ?></div><?php endif; ?>
     <div class="mant-divider"></div>
     <form method="POST" style="display:flex;flex-direction:column;gap:10px">
      <input type="hidden" name="id_mantenimiento" value="<?php echo (int)$row['id_mantenimiento']; ?>">
      <label class="form-label">Resolver / Cambiar estado</label>
      <textarea name="observaciones" rows="2" class="form-input" placeholder="Añadir observación al resolver (opcional)"></textarea>
      <div style="display:flex;gap:8px;flex-wrap:wrap">
       <button type="submit" name="accion_mant" value="EN_PROCESO" class="btn btn-sm" style="background:var(--or);color:#fff" <?php echo $row['estado']==='EN_PROCESO'?'disabled style="opacity:.5"':''; ?>>Tomar / En proceso</button>
       <button type="submit" name="accion_mant" value="FINALIZADO" class="btn btn-sm" style="background:var(--gn);color:#fff" <?php echo $row['estado']==='FINALIZADO'?'disabled style="opacity:.5"':''; ?>>Resolver / Finalizado</button>
       <button type="submit" name="accion_mant" value="CANCELADO" class="btn btn-sm btn-outline" <?php echo $row['estado']==='CANCELADO'?'disabled':''; ?>>Cancelar</button>
       <button type="submit" name="accion_mant" value="PENDIENTE" class="btn btn-sm btn-outline">Reabrir</button>
      </div>
     </form>
    </div>
   </div>
   <?php endforeach; ?>
  </div>
  <?php endif; ?>
  <div style="margin:28px 0 14px;display:flex;align-items:center;gap:10px"><div style="width:4px;height:22px;background:var(--ac);border-radius:2px"></div><h3 style="font-size:16px;font-weight:700;color:var(--tx2)">Solicitudes levantadas por usuarios</h3><span style="background:var(--hvr);border:1px solid var(--bd);padding:3px 10px;border-radius:20px;font-size:12px;color:var(--tx4)"><?php echo count($solicitudes); ?> registros (tabla solicitudes_mantenimiento)</span></div>
  <?php if(empty($solicitudes)): ?>
   <div class="card"><p style="text-align:center;color:var(--tx4);padding:16px">No hay solicitudes de usuarios.</p></div>
  <?php else: ?>
  <div class="mant-lista" id="listaSolicitudes">
   <?php foreach($solicitudes as $idx=>$s):
        $sn = trim(($s['cli_nombre']??'').' '.($s['cli_apellido']??'')); if($sn==='') $sn='Usuario #'.($s['id_usuario']??'—');
        $sDir = trim(($s['cli_calle']??'').' '.($s['cli_numero']??'')); if($sDir==='') $sDir=$s['edificio_direccion'] ?? $s['tanque_ubicacion'] ?? '—'; if(!empty($s['cli_localidad'])) $sDir .= ', '.$s['cli_localidad'];
        $sTec = trim(($s['tec_nombre']??'').' '.($s['tec_apellido']??'')); if($sTec==='') $sTec='Sin asignar';
        $sEstado = $s['estado'] ?? 'PENDIENTE'; $sEstadoNorm = strtolower(str_replace('_','-', $sEstado)); $sBadge = ['PENDIENTE'=>'programado','ACEPTADA'=>'pendiente','EN_PROCESO'=>'pendiente','FINALIZADA'=>'completado','CANCELADA'=>'inactivo'][$sEstado] ?? 'programado';
   ?>
   <div class="mant-card" data-estado="<?php echo $sEstadoNorm; ?>" data-tipo="solicitud">
    <div class="mant-card-header" onclick="toggleMantCard(this)"><div class="mant-card-info"><div class="mant-card-titulo">Solicitud #<?php echo (int)$s['id_solicitud']; ?> — <?php echo htmlspecialchars($sn); ?></div><div class="mant-card-meta"><span><?php echo htmlspecialchars($s['tanque_nombre'] ?? 'Tanque #'.$s['id_tanque']); ?></span><span>Solicitud</span><span><?php echo fmtFecha($s['fecha_solicitud']); ?></span></div></div><span class="badge <?php echo $sBadge; ?>"><?php echo htmlspecialchars($sEstado); ?></span></div>
    <div class="mant-card-body">
     <div class="mant-cliente-grid"><div class="mant-cliente-item"><span class="mant-cliente-label">Cliente</span><span class="mant-cliente-val"><?php echo htmlspecialchars($sn); ?></span></div><div class="mant-cliente-item"><span class="mant-cliente-label">Email</span><span class="mant-cliente-val"><?php echo htmlspecialchars($s['cli_email'] ?? '—'); ?></span></div><div class="mant-cliente-item"><span class="mant-cliente-label">Teléfono</span><span class="mant-cliente-val"><?php echo htmlspecialchars($s['cli_telefono'] ?? '—'); ?></span></div><div class="mant-cliente-item"><span class="mant-cliente-label">Dirección</span><span class="mant-cliente-val"><?php echo htmlspecialchars($sDir); ?></span></div><div class="mant-cliente-item"><span class="mant-cliente-label">Tanque / Equipo</span><span class="mant-cliente-val"><?php echo htmlspecialchars(($s['tanque_nombre'] ?? 'Tanque #'.$s['id_tanque']).' · '.($s['edificio_nombre'] ?? '')); ?></span></div><div class="mant-cliente-item"><span class="mant-cliente-label">Técnico asignado</span><span class="mant-cliente-val"><?php echo htmlspecialchars($sTec); ?></span></div><div class="mant-cliente-item"><span class="mant-cliente-label">Fecha solicitud</span><span class="mant-cliente-val"><?php echo fmtFecha($s['fecha_solicitud']); ?></span></div><div class="mant-cliente-item"><span class="mant-cliente-label">Próxima fecha / Finalizada</span><span class="mant-cliente-val"><?php echo fmtFecha($s['fecha_finalizada']); ?></span></div><div class="mant-cliente-item"><span class="mant-cliente-label">Estado</span><span class="mant-cliente-val"><span class="badge <?php echo $sBadge; ?>"><?php echo htmlspecialchars($sEstado); ?></span></span></div><div class="mant-cliente-item"><span class="mant-cliente-label">ID Tanque</span><span class="mant-cliente-val">#<?php echo (int)$s['id_tanque']; ?></span></div></div>
     <div class="mant-divider"></div>
     <div class="form-group"><label class="form-label">Descripción / Observaciones</label><p style="font-size:13px;color:var(--tx);line-height:1.5"><?php echo nl2br(htmlspecialchars($s['descripcion'] ?? '—')); ?></p></div>
     <form method="POST" style="display:flex;flex-direction:column;gap:10px">
      <input type="hidden" name="id_solicitud" value="<?php echo (int)$s['id_solicitud']; ?>">
      <label class="form-label">Acción del técnico — Resolver solicitud</label>
      <textarea name="observaciones_sol" rows="2" class="form-input" placeholder="Observación técnica (opcional)"></textarea>
      <label style="display:flex;gap:6px;align-items:center;font-size:12px;color:var(--tx4)"><input type="checkbox" name="crear_mantenimiento" value="1"> Al finalizar, crear registro en mantenimientos como FINALIZADO</label>
      <input type="text" name="descripcion_final" class="form-input" placeholder="Descripción para mantenimiento (si marca la casilla)">
      <div style="display:flex;gap:8px;flex-wrap:wrap">
       <?php if($sEstado==='PENDIENTE'): ?><button type="submit" name="accion_sol" value="ACEPTADA" class="btn btn-sm" style="background:var(--ac);color:#fff">Aceptar solicitud</button><?php endif; ?>
       <button type="submit" name="accion_sol" value="EN_PROCESO" class="btn btn-sm" style="background:var(--or);color:#fff">En proceso</button>
       <button type="submit" name="accion_sol" value="FINALIZADA" class="btn btn-sm" style="background:var(--gn);color:#fff">Resolver / Finalizada</button>
       <button type="submit" name="accion_sol" value="CANCELADA" class="btn btn-sm btn-outline">Cancelar</button>
       <button type="submit" name="accion_sol" value="PENDIENTE" class="btn btn-sm btn-outline">Reabrir</button>
      </div>
     </form>
    </div>
   </div>
   <?php endforeach; ?>
  </div>
  <?php endif; ?>
  <?php endif; ?>
 </div>
 <div class="modal-overlay hidden" id="modalObservacionesMant"><div class="modal"><div class="modal-header"><h2>Observaciones</h2><button class="modal-close" onclick="cerrarModal('modalObservacionesMant')">&times;</button></div><div class="modal-body"><div class="form-group"><label class="form-label">Observaciones del mantenimiento</label><textarea class="form-input" id="textoObservacionesMant" rows="5" placeholder="Escribe las observaciones..."></textarea></div></div><div class="modal-footer"><button class="btn btn-outline" onclick="cerrarModal('modalObservacionesMant')">Cancelar</button><button class="btn btn-primary" onclick="guardarObservacionesMant()">Guardar</button></div></div></div>
</div>
<script src="js/tecnico.js"></script>
<script>
function filtrarMantenimientos(){
 const q=(document.getElementById('busquedaMant').value||'').toLowerCase();
 const est=(document.getElementById('filtroEstadoMant').value||'').toLowerCase();
 const tipo=(document.getElementById('filtroTipoMant').value||'').toLowerCase();
 document.querySelectorAll('.mant-card').forEach(c=>{
  const me=!est||c.dataset.estado===est;
  const mt=!tipo||c.dataset.tipo===tipo;
  const mq=!q||c.textContent.toLowerCase().includes(q);
  c.style.display=(me&&mt&&mq)?'':'none';
 });
}
</script>
</body>
</html>
