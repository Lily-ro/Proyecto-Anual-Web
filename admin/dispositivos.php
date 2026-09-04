<?php
session_start();
if(!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'ADMIN'){
    header("Location: ../index.php");
    exit;
}
require_once(__DIR__ . '/../config/db.php');
$pdo = eva_pdo();
$currentPage = 'dispositivos';
$pageSubtitle = 'Gestión de dispositivos EVA y firmware';
$filtroBusqueda = trim($_GET['busqueda'] ?? '');
$filtroEstado   = $_GET['estado'] ?? '';
$filtroTanque   = $_GET['tanque'] ?? '';
$filtroFwBusqueda = trim($_GET['fw_busqueda'] ?? '');
$estadosValidos = ['ONLINE','OFFLINE','MANTENIMIENTO'];
if($_SERVER['REQUEST_METHOD']==='POST'){
    $accion=$_POST['accion']??'';
    if($accion==='crear'){
        $nombre=trim($_POST['nombre']??''); $mac=trim($_POST['mac_address']??''); $ip=trim($_POST['ip_local']??''); $estado=$_POST['estado']??'OFFLINE'; $id_tanque=(int)($_POST['id_tanque']??0);
        $bateria=(float)($_POST['bateria']??100); $senal=(int)($_POST['intensidad_senal']??0); $firmware=trim($_POST['firmware']??'');
        if($nombre && $id_tanque && in_array($estado,$estadosValidos,true)){
            $chk=$pdo->prepare("SELECT id_dispositivo FROM dispositivos WHERE mac_address=:mac LIMIT 1");
            $chk->execute([':mac'=>$mac]);
            if($mac && $chk->fetch()){ echo '<script>alert("MAC ya existe");history.back();</script>'; exit; }
            $pdo->prepare("INSERT INTO dispositivos (nombre,mac_address,ip_local,estado,id_tanque,bateria,intensidad_senal,firmware,fecha_instalacion,ultima_conexion) VALUES (:n,:mac,:ip,:e,:t,:b,:s,:f,NOW(),NOW())")->execute([':n'=>$nombre,':mac'=>$mac?:null,':ip'=>$ip?:null,':e'=>$estado,':t'=>$id_tanque,':b'=>$bateria,':s'=>$senal,':f'=>$firmware?:null]);
            $pdo->prepare("INSERT INTO log_actividad (id_usuario,accion,detalle,ip,fecha_hora) VALUES (:uid,'CREATE',:det,:ip,NOW())")->execute([':uid'=>$_SESSION['id_usuario']??null,':det'=>"Creó dispositivo {$nombre}",':ip'=>$_SERVER['REMOTE_ADDR']??'']);
            header("Location: dispositivos.php"); exit;
        }
        echo '<script>alert("Complete nombre y tanque");history.back();</script>'; exit;
    }
    if($accion==='editar'){
        $id=(int)($_POST['id_dispositivo']??0); $nombre=trim($_POST['nombre']??''); $estado=$_POST['estado']??'OFFLINE'; $id_tanque=(int)($_POST['id_tanque']??0); $bateria=(float)($_POST['bateria']??0); $senal=(int)($_POST['intensidad_senal']??0); $firmware=trim($_POST['firmware']??'');
        if($id && $nombre && $id_tanque && in_array($estado,$estadosValidos,true)){
            $prev=$pdo->prepare("SELECT estado FROM dispositivos WHERE id_dispositivo=:id LIMIT 1"); $prev->execute([':id'=>$id]); $old=$prev->fetchColumn();
            $pdo->prepare("UPDATE dispositivos SET nombre=:n, estado=:e, id_tanque=:t, bateria=:b, intensidad_senal=:s, firmware=:f, ultima_actualizacion=NOW() WHERE id_dispositivo=:id")->execute([':n'=>$nombre,':e'=>$estado,':t'=>$id_tanque,':b'=>$bateria,':s'=>$senal,':f'=>$firmware?:null,':id'=>$id]);
            if($old!==$estado){ $pdo->prepare("INSERT INTO historial_estado_dispositivo (id_dispositivo,estado,descripcion) VALUES (:id,:e,:d)")->execute([':id'=>$id,':e'=>$estado,':d'=>"Cambio {$old} -> {$estado}"]); }
            $pdo->prepare("INSERT INTO log_actividad (id_usuario,accion,detalle,ip,fecha_hora) VALUES (:uid,'UPDATE',:det,:ip,NOW())")->execute([':uid'=>$_SESSION['id_usuario']??null,':det'=>"Editó dispositivo {$id}",':ip'=>$_SERVER['REMOTE_ADDR']??'']);
            header("Location: dispositivos.php"); exit;
        }
        echo '<script>alert("Datos incompletos");history.back();</script>'; exit;
    }
    if($accion==='eliminar'){
        $id=(int)($_POST['id_dispositivo']??0);
        if($id){
            $c1=(int)$pdo->query("SELECT COUNT(*) FROM sensores WHERE id_dispositivo={$id}")->fetchColumn();
            $c2=(int)$pdo->query("SELECT COUNT(*) FROM instalaciones WHERE id_dispositivo={$id}")->fetchColumn();
            $c3=(int)$pdo->query("SELECT COUNT(*) FROM mantenimientos WHERE id_dispositivo={$id}")->fetchColumn();
            if($c1>0 || $c2>0 || $c3>0){
                $pdo->prepare("UPDATE dispositivos SET estado='OFFLINE', ultima_actualizacion=NOW() WHERE id_dispositivo=:id")->execute([':id'=>$id]);
                $pdo->prepare("INSERT INTO log_actividad (id_usuario,accion,detalle,ip,fecha_hora) VALUES (:uid,'UPDATE',:det,:ip,NOW())")->execute([':uid'=>$_SESSION['id_usuario']??null,':det'=>"Desactivó dispositivo {$id} por dependencias",':ip'=>$_SERVER['REMOTE_ADDR']??'']);
            } else {
                $pdo->prepare("DELETE FROM dispositivos WHERE id_dispositivo=:id")->execute([':id'=>$id]);
                $pdo->prepare("INSERT INTO log_actividad (id_usuario,accion,detalle,ip,fecha_hora) VALUES (:uid,'DELETE',:det,:ip,NOW())")->execute([':uid'=>$_SESSION['id_usuario']??null,':det'=>"Eliminó dispositivo {$id}",':ip'=>$_SERVER['REMOTE_ADDR']??'']);
            }
            header("Location: dispositivos.php"); exit;
        }
    }
}
function countDevByEstado(PDO $pdo,$estado){ $st=$pdo->prepare("SELECT COUNT(*) FROM dispositivos WHERE estado=:e"); $st->execute([':e'=>$estado]); return (int)$st->fetchColumn(); }
$cntOnline=countDevByEstado($pdo,'ONLINE'); $cntOffline=countDevByEstado($pdo,'OFFLINE'); $cntMantenimiento=countDevByEstado($pdo,'MANTENIMIENTO'); $cntTotalDev=$cntOnline+$cntOffline+$cntMantenimiento;
$tanquesList=$pdo->query("SELECT id_tanque, nombre FROM tanques ORDER BY nombre ASC")->fetchAll(PDO::FETCH_ASSOC);
$where=[]; $params=[];
if($filtroBusqueda !== ''){ $where[]="(d.nombre LIKE :b1 OR d.mac_address LIKE :b2 OR d.ip_local LIKE :b3 OR t.nombre LIKE :b4)"; $params[':b1']="%{$filtroBusqueda}%"; $params[':b2']="%{$filtroBusqueda}%"; $params[':b3']="%{$filtroBusqueda}%"; $params[':b4']="%{$filtroBusqueda}%"; }
if($filtroEstado !== '' && in_array($filtroEstado,$estadosValidos,true)){ $where[]="d.estado=:estado"; $params[':estado']=$filtroEstado; }
if($filtroTanque !== '' && ctype_digit($filtroTanque)){ $where[]="d.id_tanque=:tanque"; $params[':tanque']=(int)$filtroTanque; }
$whereSQL = $where ? 'WHERE '.implode(' AND ',$where) : '';
$sql="SELECT d.id_dispositivo, d.nombre, d.mac_address, d.ip_local, d.estado, d.bateria, d.intensidad_senal, d.firmware, d.fecha_instalacion, d.ultima_conexion, t.nombre AS tanque, d.id_tanque FROM dispositivos d JOIN tanques t ON d.id_tanque=t.id_tanque {$whereSQL} ORDER BY d.nombre ASC";
$st=$pdo->prepare($sql); $st->execute($params); $listaDisp=$st->fetchAll(PDO::FETCH_ASSOC);
$sqlFw="SELECT d.id_dispositivo, d.nombre, d.firmware, d.ultima_actualizacion, f.version, f.descripcion, f.fecha_publicacion FROM dispositivos d LEFT JOIN firmware f ON d.id_firmware=f.id_firmware";
$paramsFw=[];
if($filtroFwBusqueda !== ''){ $sqlFw.=" WHERE (d.nombre LIKE :b1 OR f.version LIKE :b2)"; $paramsFw[':b1']="%{$filtroFwBusqueda}%"; $paramsFw[':b2']="%{$filtroFwBusqueda}%"; }
$sqlFw.=" ORDER BY d.nombre ASC";
$stFw=$pdo->prepare($sqlFw); $stFw->execute($paramsFw); $listaFw=$stFw->fetchAll(PDO::FETCH_ASSOC);
$ultimaVersion=$pdo->query("SELECT version FROM firmware ORDER BY fecha_publicacion DESC LIMIT 1")->fetchColumn() ?: '';
function badgeEstadoDev($estado){ $map=['ONLINE'=>['cls'=>'activo','txt'=>'Online'],'OFFLINE'=>['cls'=>'inactivo','txt'=>'Offline'],'MANTENIMIENTO'=>['cls'=>'advertencia','txt'=>'Mantenimiento']]; $info=$map[$estado]??['cls'=>'inactivo','txt'=>$estado]; return '<span class="badge '.$info['cls'].'">'.htmlspecialchars($info['txt']).'</span>'; }
function badgeBateria($b){ $b=(float)$b; if($b>=60) $cls='activo'; elseif($b>=30) $cls='media'; else $cls='baja'; return '<span class="badge '.$cls.'">'.round($b).'%</span>'; }
function badgeSenal($s){ $s=(int)$s; if($s>=-60){ $txt='Fuerte'; $cls='activo'; } elseif($s>=-80){ $txt='Media'; $cls='media'; } else{ $txt='Débil'; $cls='baja'; } return '<span class="badge '.$cls.'">'.$txt.'</span>'; }
function badgeFirmwareStatus($version,$ultima){ if($version===$ultima) return '<span class="badge activo">Actualizado</span>'; return '<span class="badge advertencia">Desactualizado</span>'; }
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>EVA - Gestión de Dispositivos</title>
<link rel="stylesheet" href="css/admin.css">
</head>
<body>
<?php include __DIR__ . '/includes/sidebar.php'; ?>
<div class="main">
 <?php include __DIR__ . '/includes/header.php'; ?>
 <div class="content">
  <div class="page-header"><h2 class="page-title">Gestión de Dispositivos</h2><p class="page-desc">Administrar dispositivos EVA y control de firmware.</p></div>
  <div class="tabs-container">
   <div class="tabs"><button class="tab active" data-tab="lista">Lista de Dispositivos</button><button class="tab" data-tab="firmware">Firmware</button></div>
   <div class="tab-content active" id="tab-lista">
    <div class="card">
     <div class="card-header">
      <form method="GET" class="filters-row">
       <input type="hidden" name="tab" value="lista">
       <div class="filter-group"><input type="text" name="busqueda" class="filter-input" placeholder="Buscar dispositivo..." value="<?php echo htmlspecialchars($filtroBusqueda); ?>"></div>
       <div class="filter-group"><select name="estado" class="filter-input"><option value="">Todos los estados</option><option value="ONLINE"<?php echo $filtroEstado==='ONLINE'?' selected':''; ?>>Online</option><option value="OFFLINE"<?php echo $filtroEstado==='OFFLINE'?' selected':''; ?>>Offline</option><option value="MANTENIMIENTO"<?php echo $filtroEstado==='MANTENIMIENTO'?' selected':''; ?>>Mantenimiento</option></select></div>
       <div class="filter-group"><select name="tanque" class="filter-input"><option value="">Todos los tanques</option><?php foreach($tanquesList as $t): ?><option value="<?php echo (int)$t['id_tanque']; ?>"<?php echo $filtroTanque==(string)$t['id_tanque']?' selected':''; ?>><?php echo htmlspecialchars($t['nombre']); ?></option><?php endforeach; ?></select></div>
       <div class="filter-group"><button type="submit" class="btn btn-primary">Filtrar</button></div>
       <div class="filter-group"><button type="button" class="btn btn-primary" onclick="abrirModalDisp()">+ Nuevo dispositivo</button></div>
      </form>
     </div>
     <div class="table-responsive">
      <table class="table">
       <thead><tr><th>ID</th><th>Nombre</th><th>Tanque</th><th>Estado</th><th>Batería</th><th>Señal</th><th>Firmware</th><th>Última conexión</th><th>Acciones</th></tr></thead>
       <tbody>
       <?php if(count($listaDisp)>0): foreach($listaDisp as $d): ?>
        <tr data-id="<?php echo (int)$d['id_dispositivo']; ?>" data-nombre="<?php echo htmlspecialchars($d['nombre']); ?>" data-estado="<?php echo htmlspecialchars($d['estado']); ?>" data-tanque="<?php echo (int)$d['id_tanque']; ?>" data-bateria="<?php echo htmlspecialchars($d['bateria']); ?>" data-senal="<?php echo htmlspecialchars($d['intensidad_senal']); ?>" data-firmware="<?php echo htmlspecialchars($d['firmware']??''); ?>" data-mac="<?php echo htmlspecialchars($d['mac_address']??''); ?>" data-ip="<?php echo htmlspecialchars($d['ip_local']??''); ?>">
         <td>D-<?php echo str_pad($d['id_dispositivo'],3,'0',STR_PAD_LEFT); ?></td>
         <td><?php echo htmlspecialchars($d['nombre']); ?></td>
         <td><?php echo htmlspecialchars($d['tanque']); ?></td>
         <td><?php echo badgeEstadoDev($d['estado']); ?></td>
         <td><?php echo badgeBateria($d['bateria']); ?></td>
         <td><?php echo badgeSenal($d['intensidad_senal']); ?></td>
         <td><?php echo htmlspecialchars($d['firmware']??'-'); ?></td>
         <td><?php echo $d['ultima_conexion']?date('d/m/Y H:i',strtotime($d['ultima_conexion'])):'Nunca'; ?></td>
         <td class="actions-cell">
          <button class="btn-icon" title="Ver" onclick="verDisp(<?php echo (int)$d['id_dispositivo']; ?>)"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button>
          <button class="btn-icon" title="Editar" onclick="editarDisp(<?php echo (int)$d['id_dispositivo']; ?>)"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button>
          <button class="btn-icon btn-icon-danger" title="Eliminar" onclick="eliminarDisp(<?php echo (int)$d['id_dispositivo']; ?>)"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg></button>
         </td>
        </tr>
       <?php endforeach; else: ?>
        <tr><td colspan="9" style="text-align:center;color:var(--tx4)">No hay dispositivos registrados</td></tr>
       <?php endif; ?>
       </tbody>
      </table>
     </div>
    </div>
   </div>
   <div class="tab-content" id="tab-firmware">
    <div class="card">
     <div class="card-header"><div class="card-title">Control de Firmware</div><form method="GET" class="filters-row" style="margin-left:auto"><input type="hidden" name="tab" value="firmware"><div class="filter-group"><input type="text" name="fw_busqueda" class="filter-input" placeholder="Buscar..." value="<?php echo htmlspecialchars($filtroFwBusqueda); ?>"></div><div class="filter-group"><button type="submit" class="btn btn-primary">Filtrar</button></div></form></div>
     <div class="table-responsive">
      <table class="table"><thead><tr><th>Dispositivo</th><th>Versión actual</th><th>Descripción firmware</th><th>Fecha publicación</th><th>Estado</th></tr></thead><tbody><?php if(count($listaFw)>0): foreach($listaFw as $fw): ?><tr><td><?php echo htmlspecialchars($fw['nombre']); ?></td><td><span class="badge <?php echo $fw['version']===$ultimaVersion?'activo':'advertencia'; ?>"><?php echo htmlspecialchars($fw['version']??'-'); ?></span></td><td><?php echo htmlspecialchars($fw['descripcion']??'-'); ?></td><td><?php echo $fw['fecha_publicacion']?date('d/m/Y',strtotime($fw['fecha_publicacion'])):'-'; ?></td><td><?php echo badgeFirmwareStatus($fw['version']??'',$ultimaVersion); ?></td></tr><?php endforeach; else: ?><tr><td colspan="5" style="text-align:center;color:var(--tx4)">No hay dispositivos con firmware asignado</td></tr><?php endif; ?></tbody></table>
     </div>
    </div>
   </div>
  </div>
 </div>
</div>
<div class="modal-overlay" id="modalDisp"><div class="modal"><div class="modal-header"><h3 class="modal-title" id="modalDispTitle">Nuevo dispositivo</h3><button class="modal-close" onclick="cerrarModalDisp()">&times;</button></div><div class="modal-body"><form id="formDisp" method="POST" action="dispositivos.php"><input type="hidden" name="accion" id="dispAccion" value="crear"><input type="hidden" name="id_dispositivo" id="dispId" value=""><div class="form-group"><label class="form-label">Nombre *</label><input type="text" name="nombre" id="dispNombre" class="form-input" required></div><div class="form-row"><div class="form-group"><label class="form-label">MAC</label><input type="text" name="mac_address" id="dispMac" class="form-input" placeholder="24:6F:28:AB:12:34"></div><div class="form-group"><label class="form-label">IP local</label><input type="text" name="ip_local" id="dispIp" class="form-input" placeholder="192.168.1.105"></div></div><div class="form-row"><div class="form-group"><label class="form-label">Tanque *</label><select name="id_tanque" id="dispTanque" class="form-input" required><option value="">Seleccionar...</option><?php foreach($tanquesList as $t): ?><option value="<?php echo (int)$t['id_tanque']; ?>"><?php echo htmlspecialchars($t['nombre']); ?></option><?php endforeach; ?></select></div><div class="form-group"><label class="form-label">Estado</label><select name="estado" id="dispEstado" class="form-input"><option value="OFFLINE">Offline</option><option value="ONLINE">Online</option><option value="MANTENIMIENTO">Mantenimiento</option></select></div></div><div class="form-row"><div class="form-group"><label class="form-label">Batería %</label><input type="number" name="bateria" id="dispBateria" class="form-input" min="0" max="100" step="0.01"></div><div class="form-group"><label class="form-label">Señal</label><input type="number" name="intensidad_senal" id="dispSenal" class="form-input"></div></div><div class="form-group"><label class="form-label">Firmware</label><input type="text" name="firmware" id="dispFirmware" class="form-input"></div></form></div><div class="modal-footer"><button class="btn btn-secondary" onclick="cerrarModalDisp()">Cancelar</button><button class="btn btn-primary" onclick="document.getElementById('formDisp').submit()">Guardar</button></div></div></div>
<div class="modal-overlay" id="modalVerDisp"><div class="modal"><div class="modal-header"><h3 class="modal-title">Detalle dispositivo</h3><button class="modal-close" onclick="document.getElementById('modalVerDisp').classList.remove('active')">&times;</button></div><div class="modal-body" id="verDispBody"></div><div class="modal-footer"><button class="btn btn-secondary" onclick="document.getElementById('modalVerDisp').classList.remove('active')">Cerrar</button></div></div></div>
<script src="js/admin.js"></script>
<script>function abrirModalDisp(){document.getElementById('modalDispTitle').textContent='Nuevo dispositivo';document.getElementById('dispAccion').value='crear';document.getElementById('dispId').value='';document.getElementById('formDisp').reset();document.getElementById('modalDisp').classList.add('active');}function editarDisp(id){var r=document.querySelector('tr[data-id="'+id+'"]');if(!r)return;document.getElementById('modalDispTitle').textContent='Editar dispositivo';document.getElementById('dispAccion').value='editar';document.getElementById('dispId').value=id;document.getElementById('dispNombre').value=r.getAttribute('data-nombre')||'';document.getElementById('dispMac').value=r.getAttribute('data-mac')||'';document.getElementById('dispIp').value=r.getAttribute('data-ip')||'';document.getElementById('dispTanque').value=r.getAttribute('data-tanque')||'';document.getElementById('dispEstado').value=r.getAttribute('data-estado')||'OFFLINE';document.getElementById('dispBateria').value=r.getAttribute('data-bateria')||'';document.getElementById('dispSenal').value=r.getAttribute('data-senal')||'';document.getElementById('dispFirmware').value=r.getAttribute('data-firmware')||'';document.getElementById('modalDisp').classList.add('active');}function cerrarModalDisp(){document.getElementById('modalDisp').classList.remove('active');}function verDisp(id){var r=document.querySelector('tr[data-id="'+id+'"]');if(!r)return;var h='<p><b>Nombre:</b> '+r.getAttribute('data-nombre')+'</p><p><b>MAC:</b> '+(r.getAttribute('data-mac')||'-')+'</p><p><b>IP:</b> '+(r.getAttribute('data-ip')||'-')+'</p><p><b>Estado:</b> '+r.getAttribute('data-estado')+'</p><p><b>Batería:</b> '+(r.getAttribute('data-bateria')||'-')+'%</p><p><b>Señal:</b> '+(r.getAttribute('data-senal')||'-')+'</p><p><b>Firmware:</b> '+(r.getAttribute('data-firmware')||'-')+'</p>';document.getElementById('verDispBody').innerHTML=h;document.getElementById('modalVerDisp').classList.add('active');}function eliminarDisp(id){if(confirm('¿Eliminar/desactivar este dispositivo? Si tiene sensores o historial se desactivará.')){var f=document.createElement('form');f.method='POST';f.action='dispositivos.php';f.innerHTML='<input type="hidden" name="accion" value="eliminar"><input type="hidden" name="id_dispositivo" value="'+id+'">';document.body.appendChild(f);f.submit();}}</script>
</body>
</html>
