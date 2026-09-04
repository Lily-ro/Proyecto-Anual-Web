<?php
session_start();
if(!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'ADMIN'){
    header("Location: ../index.php");
    exit;
}
require_once(__DIR__ . '/../config/db.php');
$pdo = eva_pdo();
$currentPage = 'tecnicos';
$pageSubtitle = 'Gestión y seguimiento de técnicos';
if($_SERVER['REQUEST_METHOD']==='POST'){
    $accion=$_POST['accion']??'';
    if($accion==='editar'){
        $id=(int)($_POST['id_usuario']??0); $nombre=trim($_POST['nombre']??''); $apellido=trim($_POST['apellido']??''); $email=trim($_POST['email']??''); $telefono=trim($_POST['telefono']??''); $activo=(int)($_POST['activo']??1);
        if($id && $nombre && $apellido && $email){
            $chk=$pdo->prepare("SELECT id_usuario FROM usuarios WHERE email=:e AND id_usuario!=:id LIMIT 1"); $chk->execute([':e'=>$email,':id'=>$id]);
            if($chk->fetch()){ echo '<script>alert("Email ya existe");history.back();</script>'; exit; }
            $pdo->prepare("UPDATE usuarios SET nombre=:n, apellido=:a, email=:e, telefono=:t, activo=:ac WHERE id_usuario=:id")->execute([':n'=>$nombre,':a'=>$apellido,':e'=>$email,':t'=>$telefono,':ac'=>$activo,':id'=>$id]);
            $pdo->prepare("INSERT INTO log_actividad (id_usuario,accion,detalle,ip,fecha_hora) VALUES (:uid,'UPDATE',:det,:ip,NOW())")->execute([':uid'=>$_SESSION['id_usuario']??null,':det'=>"Editó técnico {$id}",':ip'=>$_SERVER['REMOTE_ADDR']??'']);
            header("Location: tecnicos.php"); exit;
        }
        echo '<script>alert("Datos incompletos");history.back();</script>'; exit;
    }
    if($accion==='toggle'){
        $id=(int)($_POST['id_usuario']??0); $activo=(int)($_POST['activo']??1);
        if($id){
            $cnt=(int)$pdo->query("SELECT COUNT(*) FROM mantenimientos WHERE id_tecnico={$id} AND estado IN ('PENDIENTE','EN_PROCESO')")->fetchColumn();
            if($cnt>0 && $activo==0){ echo '<script>alert("No se puede desactivar: tiene trabajos pendientes");history.back();</script>'; exit; }
            $pdo->prepare("UPDATE usuarios SET activo=:a WHERE id_usuario=:id")->execute([':a'=>$activo,':id'=>$id]);
            header("Location: tecnicos.php"); exit;
        }
    }
}
$fBusqueda = trim($_GET['busqueda'] ?? '');
$where=''; $params=[];
if($fBusqueda!==''){ $where="WHERE (u.nombre LIKE :b OR u.apellido LIKE :b2 OR u.email LIKE :b3)"; $params[':b']="%{$fBusqueda}%"; $params[':b2']="%{$fBusqueda}%"; $params[':b3']="%{$fBusqueda}%"; }
$st=$pdo->prepare("SELECT u.id_usuario, u.nombre, u.apellido, u.email, u.activo, u.telefono, (SELECT COUNT(*) FROM mantenimientos m WHERE m.id_tecnico=u.id_usuario) as cnt_mant, (SELECT COUNT(*) FROM instalaciones i WHERE i.id_tecnico=u.id_usuario) as cnt_inst FROM usuarios u JOIN roles r ON u.id_rol=r.id_rol WHERE r.nombre='TECNICO' {$where} ORDER BY u.apellido ASC");
$st->execute($params);
$lista=$st->fetchAll(PDO::FETCH_ASSOC);
$historial = [];
if(isset($_GET['tecnico_id']) && ctype_digit($_GET['tecnico_id'])){
 $tid=(int)$_GET['tecnico_id'];
 $h=$pdo->prepare("SELECT m.id_mantenimiento, m.tipo, m.descripcion, m.estado, m.fecha_programada, m.fecha_realizada, t.nombre as tanque FROM mantenimientos m LEFT JOIN dispositivos d ON m.id_dispositivo=d.id_dispositivo LEFT JOIN tanques t ON d.id_tanque=t.id_tanque WHERE m.id_tecnico=:id ORDER BY m.fecha_programada DESC LIMIT 50");
 $h->execute([':id'=>$tid]);
 $historial=$h->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>EVA - Gestión de Técnicos</title>
<link rel="stylesheet" href="css/admin.css">
</head>
<body>
<?php include __DIR__ . '/includes/sidebar.php'; ?>
<div class="main">
 <?php include __DIR__ . '/includes/header.php'; ?>
 <div class="content">
  <div class="page-header"><h2 class="page-title">Gestión de Técnicos</h2><p class="page-desc">Administrar técnicos y seguimiento de su rendimiento.</p></div>
  <div class="tabs-container">
   <div class="tabs"><button class="tab active" data-tab="lista">Lista de Técnicos</button><button class="tab" data-tab="historial">Historial</button></div>
   <div class="tab-content active" id="tab-lista">
    <div class="card">
     <div class="card-header"><form method="GET" class="filters-row"><div class="filter-group"><input type="text" name="busqueda" class="filter-input" placeholder="Buscar técnico..." value="<?php echo htmlspecialchars($fBusqueda); ?>"></div><div class="filter-group"><button type="submit" class="btn btn-primary">Filtrar</button></div></form></div>
     <div class="table-responsive">
      <table class="table">
       <thead><tr><th>ID</th><th>Nombre</th><th>Apellido</th><th>Email</th><th>Estado</th><th>Trabajos asignados</th><th>Acciones</th></tr></thead>
       <tbody>
       <?php if(count($lista)>0): foreach($lista as $t): $total=(int)$t['cnt_mant']+(int)$t['cnt_inst']; ?>
        <tr data-id="<?php echo (int)$t['id_usuario']; ?>" data-nombre="<?php echo htmlspecialchars($t['nombre']); ?>" data-apellido="<?php echo htmlspecialchars($t['apellido']); ?>" data-email="<?php echo htmlspecialchars($t['email']); ?>" data-telefono="<?php echo htmlspecialchars($t['telefono']??''); ?>" data-activo="<?php echo (int)$t['activo']; ?>">
         <td>T-<?php echo str_pad($t['id_usuario'],3,'0',STR_PAD_LEFT); ?></td>
         <td><?php echo htmlspecialchars($t['nombre']); ?></td>
         <td><?php echo htmlspecialchars($t['apellido']); ?></td>
         <td><?php echo htmlspecialchars($t['email']); ?></td>
         <td><?php echo $t['activo']?'<span class="badge activo">Activo</span>':'<span class="badge inactivo">Inactivo</span>'; ?></td>
         <td><?php echo (int)$total; ?></td>
         <td class="actions-cell"><a class="btn-icon" href="tecnicos.php?tecnico_id=<?php echo (int)$t['id_usuario']; ?>" title="Ver"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></a><button class="btn-icon" title="Editar" onclick="editarTecnico(<?php echo (int)$t['id_usuario']; ?>)"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button><button class="btn-icon" title="<?php echo $t['activo']?'Desactivar':'Activar'; ?>" onclick="toggleTec(<?php echo (int)$t['id_usuario']; ?>,<?php echo (int)$t['activo']; ?>)"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg></button></td>
        </tr>
       <?php endforeach; else: ?>
        <tr><td colspan="7" style="text-align:center;color:var(--tx4)">No hay técnicos registrados</td></tr>
       <?php endif; ?>
       </tbody>
      </table>
     </div>
    </div>
   </div>
   <div class="tab-content" id="tab-historial">
    <div class="card">
     <div class="card-header"><div class="card-title">Historial de trabajos</div></div>
     <div class="table-responsive">
      <table class="table">
       <thead><tr><th>ID Trabajo</th><th>Tipo</th><th>Descripción</th><th>Tanque</th><th>Fecha asignación</th><th>Fecha completado</th><th>Estado</th></tr></thead>
       <tbody>
       <?php if(count($historial)>0): foreach($historial as $h): ?>
        <tr><td>M-<?php echo str_pad($h['id_mantenimiento'],3,'0',STR_PAD_LEFT); ?></td><td><?php echo htmlspecialchars($h['tipo']); ?></td><td><?php echo htmlspecialchars($h['descripcion']??'-'); ?></td><td><?php echo htmlspecialchars($h['tanque']??'-'); ?></td><td><?php echo htmlspecialchars($h['fecha_programada']??'-'); ?></td><td><?php echo htmlspecialchars($h['fecha_realizada']??'-'); ?></td><td><?php echo htmlspecialchars($h['estado']); ?></td></tr>
       <?php endforeach; else: ?>
        <tr><td colspan="7" style="text-align:center;color:var(--tx4)">Seleccione un técnico para ver su historial o no hay trabajos asignados</td></tr>
       <?php endif; ?>
       </tbody>
      </table>
     </div>
    </div>
   </div>
  </div>
 </div>
</div>
<div class="modal-overlay" id="modalTec"><div class="modal"><div class="modal-header"><h3 class="modal-title">Editar técnico</h3><button class="modal-close" onclick="document.getElementById('modalTec').classList.remove('active')">&times;</button></div><div class="modal-body"><form id="formTec" method="POST" action="tecnicos.php"><input type="hidden" name="accion" value="editar"><input type="hidden" name="id_usuario" id="tecId"><div class="form-group"><label class="form-label">Nombre</label><input type="text" name="nombre" id="tecNombre" class="form-input" required></div><div class="form-group"><label class="form-label">Apellido</label><input type="text" name="apellido" id="tecApellido" class="form-input" required></div><div class="form-group"><label class="form-label">Email</label><input type="email" name="email" id="tecEmail" class="form-input" required></div><div class="form-group"><label class="form-label">Teléfono</label><input type="text" name="telefono" id="tecTelefono" class="form-input"></div><div class="form-group"><label class="form-label">Estado</label><select name="activo" id="tecActivo" class="form-input"><option value="1">Activo</option><option value="0">Inactivo</option></select></div></form></div><div class="modal-footer"><button class="btn btn-secondary" onclick="document.getElementById('modalTec').classList.remove('active')">Cancelar</button><button class="btn btn-primary" onclick="document.getElementById('formTec').submit()">Guardar</button></div></div></div>
<script src="js/admin.js"></script>
<script>function editarTecnico(id){var r=document.querySelector('tr[data-id="'+id+'"]');if(!r)return;document.getElementById('tecId').value=id;document.getElementById('tecNombre').value=r.getAttribute('data-nombre')||'';document.getElementById('tecApellido').value=r.getAttribute('data-apellido')||'';document.getElementById('tecEmail').value=r.getAttribute('data-email')||'';document.getElementById('tecTelefono').value=r.getAttribute('data-telefono')||'';document.getElementById('tecActivo').value=r.getAttribute('data-activo')||'1';document.getElementById('modalTec').classList.add('active');}function toggleTec(id,actual){var n=actual?0:1;if(confirm(actual?'¿Desactivar técnico?':'¿Activar técnico?')){var f=document.createElement('form');f.method='POST';f.action='tecnicos.php';f.innerHTML='<input type="hidden" name="accion" value="toggle"><input type="hidden" name="id_usuario" value="'+id+'"><input type="hidden" name="activo" value="'+n+'">';document.body.appendChild(f);f.submit();}}</script>
</body>
</html>
