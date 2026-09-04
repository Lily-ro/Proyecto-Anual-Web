<?php
session_start();
if(!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'ADMIN'){
    header("Location: ../index.php");
    exit;
}
require_once(__DIR__ . '/../config/db.php');
$pdo = eva_pdo();
$currentPage = 'tanques';
$pageSubtitle = 'Gestión completa de tanques';
if($_SERVER['REQUEST_METHOD']==='POST'){
    $accion=$_POST['accion']??'';
    if($accion==='crear'){
        $nombre=trim($_POST['nombre']??''); $id_edificio=(int)($_POST['id_edificio']??0); $cap=(float)($_POST['capacidad_litros']??0); $alt=(float)($_POST['altura_cm']??0);
        $ubic=trim($_POST['ubicacion']??''); $tipo=trim($_POST['tipo']??''); $material=trim($_POST['material']??''); $diam=$_POST['diametro']??null; $vol=$_POST['volumen_util']??null; $fecha=$_POST['fecha_instalacion']??null; $desc=trim($_POST['descripcion']??''); $activo=isset($_POST['activo'])?1:1;
        if($nombre && $id_edificio && $cap>0 && $alt>0){
            $pdo->prepare("INSERT INTO tanques (nombre,capacidad_litros,altura_cm,ubicacion,descripcion,fecha_instalacion,activo,id_edificio,tipo,material,diametro,volumen_util) VALUES (:n,:cap,:alt,:ubi,:desc,:f,:a,:ed,:t,:m,:d,:v)")->execute([':n'=>$nombre,':cap'=>$cap,':alt'=>$alt,':ubi'=>$ubic?:null,':desc'=>$desc?:null,':f'=>$fecha?:null,':a'=>$activo,':ed'=>$id_edificio,':t'=>$tipo?:null,':m'=>$material?:null,':d'=>$diam?:null,':v'=>$vol?:null]);
            $pdo->prepare("INSERT INTO log_actividad (id_usuario,accion,detalle,ip,fecha_hora) VALUES (:uid,'CREATE',:det,:ip,NOW())")->execute([':uid'=>$_SESSION['id_usuario']??null,':det'=>"Creó tanque {$nombre}",':ip'=>$_SERVER['REMOTE_ADDR']??'']);
            header("Location: tanques.php"); exit;
        }
        echo '<script>alert("Complete campos obligatorios");history.back();</script>'; exit;
    }
    if($accion==='editar'){
        $id=(int)($_POST['tanque_id']??0); $nombre=trim($_POST['nombre']??''); $id_edificio=(int)($_POST['id_edificio']??0); $cap=(float)($_POST['capacidad_litros']??0); $alt=(float)($_POST['altura_cm']??0);
        $ubic=trim($_POST['ubicacion']??''); $tipo=trim($_POST['tipo']??''); $material=trim($_POST['material']??''); $diam=$_POST['diametro']??null; $vol=$_POST['volumen_util']??null; $fecha=$_POST['fecha_instalacion']??null; $desc=trim($_POST['descripcion']??''); $activo=isset($_POST['activo'])?1:0;
        if($id && $nombre && $id_edificio && $cap>0 && $alt>0){
            $pdo->prepare("UPDATE tanques SET nombre=:n, capacidad_litros=:cap, altura_cm=:alt, ubicacion=:ubi, descripcion=:desc, fecha_instalacion=:f, activo=:a, id_edificio=:ed, tipo=:t, material=:m, diametro=:d, volumen_util=:v WHERE id_tanque=:id")->execute([':n'=>$nombre,':cap'=>$cap,':alt'=>$alt,':ubi'=>$ubic?:null,':desc'=>$desc?:null,':f'=>$fecha?:null,':a'=>$activo,':ed'=>$id_edificio,':t'=>$tipo?:null,':m'=>$material?:null,':d'=>$diam?:null,':v'=>$vol?:null,':id'=>$id]);
            $pdo->prepare("INSERT INTO log_actividad (id_usuario,accion,detalle,ip,fecha_hora) VALUES (:uid,'UPDATE',:det,:ip,NOW())")->execute([':uid'=>$_SESSION['id_usuario']??null,':det'=>"Editó tanque {$id}",':ip'=>$_SERVER['REMOTE_ADDR']??'']);
            header("Location: tanques.php"); exit;
        }
        echo '<script>alert("Datos incompletos");history.back();</script>'; exit;
    }
    if($accion==='eliminar'){
        $id=(int)($_POST['tanque_id']??0);
        if($id){
            $cnt=(int)$pdo->query("SELECT COUNT(*) FROM dispositivos WHERE id_tanque={$id}")->fetchColumn();
            if($cnt>0){
                $pdo->prepare("UPDATE tanques SET activo=0 WHERE id_tanque=:id")->execute([':id'=>$id]);
            } else {
                try{ $pdo->prepare("DELETE FROM tanques WHERE id_tanque=:id")->execute([':id'=>$id]); }catch(Throwable $e){ $pdo->prepare("UPDATE tanques SET activo=0 WHERE id_tanque=:id")->execute([':id'=>$id]); }
            }
            $pdo->prepare("INSERT INTO log_actividad (id_usuario,accion,detalle,ip,fecha_hora) VALUES (:uid,'DELETE',:det,:ip,NOW())")->execute([':uid'=>$_SESSION['id_usuario']??null,':det'=>"Eliminó/desactivó tanque {$id}",':ip'=>$_SERVER['REMOTE_ADDR']??'']);
            header("Location: tanques.php"); exit;
        }
    }
}
$filtroBusqueda  = trim($_GET['busqueda'] ?? '');
$filtroUbicacion = trim($_GET['ubicacion'] ?? '');
$filtroEstado    = $_GET['estado'] ?? '';
$edificiosList=$pdo->query("SELECT id_edificio, nombre FROM edificios ORDER BY nombre ASC")->fetchAll(PDO::FETCH_ASSOC);
$where=[]; $params=[];
if($filtroBusqueda !== ''){ $where[]="(t.nombre LIKE :b1 OR t.descripcion LIKE :b2 OR t.tipo LIKE :b3)"; $params[':b1']="%{$filtroBusqueda}%"; $params[':b2']="%{$filtroBusqueda}%"; $params[':b3']="%{$filtroBusqueda}%"; }
if($filtroUbicacion !== ''){ $where[]="t.ubicacion LIKE :ubi"; $params[':ubi']="%{$filtroUbicacion}%"; }
if($filtroEstado === 'activo'){ $where[]="t.activo=1"; } elseif($filtroEstado === 'inactivo'){ $where[]="t.activo=0"; }
$whereSQL = $where ? 'WHERE '.implode(' AND ',$where) : '';
$sql="SELECT t.id_tanque, t.nombre, t.capacidad_litros, t.altura_cm, t.ubicacion, t.descripcion, t.fecha_instalacion, t.activo, t.tipo, t.material, t.diametro, t.volumen_util, t.id_edificio, e.nombre AS edificio, (SELECT COUNT(*) FROM dispositivos d WHERE d.id_tanque=t.id_tanque) AS cnt_dispositivos, (SELECT m.porcentaje FROM mediciones m JOIN sensores s ON m.id_sensor=s.id_sensor JOIN dispositivos d2 ON s.id_dispositivo=d2.id_dispositivo WHERE d2.id_tanque=t.id_tanque ORDER BY m.fecha_hora DESC LIMIT 1) AS nivel_actual FROM tanques t JOIN edificios e ON t.id_edificio=e.id_edificio {$whereSQL} ORDER BY t.nombre ASC";
$st=$pdo->prepare($sql); $st->execute($params); $listaTanques=$st->fetchAll(PDO::FETCH_ASSOC);
function badgeTanqueActivo($activo){ return $activo?'<span class="badge activo">Activo</span>':'<span class="badge inactivo">Inactivo</span>'; }
function nivelBar($nivel){ $pct=max(0,min(100,(int)($nivel??0))); if($pct>=60) $cls=''; elseif($pct>=30) $cls=' level-medio'; else $cls=' level-bajo'; return '<div class="level-bar"><div class="level-fill'.$cls.'" style="width:'.$pct.'%"></div></div><span class="level-text">'.$pct.'%</span>'; }
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>EVA - Gestión de Tanques</title>
<link rel="stylesheet" href="css/admin.css">
</head>
<body>
<?php include __DIR__ . '/includes/sidebar.php'; ?>
<div class="main">
 <?php include __DIR__ . '/includes/header.php'; ?>
 <div class="content">
  <div class="page-header"><h2 class="page-title">Gestión de Tanques</h2><p class="page-desc">ABM completo de tanques del sistema.</p></div>
  <div class="card">
   <div class="card-header">
    <form method="GET" class="filters-row">
     <div class="filter-group"><input type="text" name="busqueda" class="filter-input" placeholder="Buscar tanque..." value="<?php echo htmlspecialchars($filtroBusqueda); ?>"></div>
     <div class="filter-group"><input type="text" name="ubicacion" class="filter-input" placeholder="Filtrar por ubicación..." value="<?php echo htmlspecialchars($filtroUbicacion); ?>"></div>
     <div class="filter-group"><select name="estado" class="filter-input"><option value="">Todos los estados</option><option value="activo"<?php echo $filtroEstado==='activo'?' selected':''; ?>>Activo</option><option value="inactivo"<?php echo $filtroEstado==='inactivo'?' selected':''; ?>>Inactivo</option></select></div>
     <div class="filter-group"><button type="submit" class="btn btn-primary">Filtrar</button></div>
     <div class="filter-group"><button type="button" class="btn btn-primary" onclick="abrirModalTanque()">+ Nuevo tanque</button></div>
    </form>
   </div>
   <div class="table-responsive">
    <table class="table">
     <thead><tr><th>ID</th><th>Nombre</th><th>Edificio</th><th>Ubicación</th><th>Capacidad (L)</th><th>Nivel actual</th><th>Estado</th><th>Dispositivos</th><th>Acciones</th></tr></thead>
     <tbody>
     <?php if(count($listaTanques)>0): foreach($listaTanques as $t): ?>
      <tr data-id="<?php echo (int)$t['id_tanque']; ?>" data-nombre="<?php echo htmlspecialchars($t['nombre']); ?>" data-edificio="<?php echo (int)$t['id_edificio']; ?>" data-capacidad="<?php echo htmlspecialchars($t['capacidad_litros']); ?>" data-altura="<?php echo htmlspecialchars($t['altura_cm']); ?>" data-ubicacion="<?php echo htmlspecialchars($t['ubicacion']??''); ?>" data-tipo="<?php echo htmlspecialchars($t['tipo']??''); ?>" data-material="<?php echo htmlspecialchars($t['material']??''); ?>" data-diametro="<?php echo htmlspecialchars($t['diametro']??''); ?>" data-volumen="<?php echo htmlspecialchars($t['volumen_util']??''); ?>" data-fecha="<?php echo htmlspecialchars($t['fecha_instalacion']??''); ?>" data-descripcion="<?php echo htmlspecialchars($t['descripcion']??''); ?>" data-activo="<?php echo (int)$t['activo']; ?>">
       <td>T-<?php echo str_pad($t['id_tanque'],3,'0',STR_PAD_LEFT); ?></td>
       <td><?php echo htmlspecialchars($t['nombre']); ?></td>
       <td><?php echo htmlspecialchars($t['edificio']); ?></td>
       <td><?php echo htmlspecialchars($t['ubicacion']??'-'); ?></td>
       <td><?php echo number_format($t['capacidad_litros'],0,',','.'); ?></td>
       <td><?php echo nivelBar($t['nivel_actual']); ?></td>
       <td><?php echo badgeTanqueActivo($t['activo']); ?></td>
       <td><?php echo (int)$t['cnt_dispositivos']; ?></td>
       <td class="actions-cell">
        <button class="btn-icon" title="Ver detalles" onclick="verTanque(<?php echo (int)$t['id_tanque']; ?>)"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button>
        <button class="btn-icon" title="Editar" onclick="editarTanque(<?php echo (int)$t['id_tanque']; ?>)"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button>
        <button class="btn-icon btn-icon-danger" title="Eliminar" onclick="eliminarTanque(<?php echo (int)$t['id_tanque']; ?>)"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg></button>
       </td>
      </tr>
     <?php endforeach; else: ?>
      <tr><td colspan="9" style="text-align:center;color:var(--tx4)">No hay tanques registrados</td></tr>
     <?php endif; ?>
     </tbody>
    </table>
   </div>
  </div>
 </div>
</div>
<div class="modal-overlay" id="modalTanque"><div class="modal"><div class="modal-header"><h3 class="modal-title" id="modalTanqueTitle">Agregar Tanque</h3><button class="modal-close" onclick="cerrarModalTanque()">&times;</button></div><div class="modal-body"><form id="formTanque" method="POST" action="tanques.php"><input type="hidden" name="accion" id="tanqueAccion" value="crear"><input type="hidden" name="tanque_id" id="tanqueId" value=""><div class="form-row"><div class="form-group"><label class="form-label">Nombre *</label><input type="text" name="nombre" class="form-input" id="tanqueNombre" required></div><div class="form-group"><label class="form-label">Edificio *</label><select name="id_edificio" class="form-input" id="tanqueEdificio" required><option value="">Seleccionar...</option><?php foreach($edificiosList as $e): ?><option value="<?php echo (int)$e['id_edificio']; ?>"><?php echo htmlspecialchars($e['nombre']); ?></option><?php endforeach; ?></select></div></div><div class="form-row"><div class="form-group"><label class="form-label">Capacidad (litros) *</label><input type="number" name="capacidad_litros" class="form-input" id="tanqueCapacidad" step="0.01" required></div><div class="form-group"><label class="form-label">Altura (cm) *</label><input type="number" name="altura_cm" class="form-input" id="tanqueAltura" step="0.01" required></div></div><div class="form-row"><div class="form-group"><label class="form-label">Ubicación</label><input type="text" name="ubicacion" class="form-input" id="tanqueUbicacion"></div><div class="form-group"><label class="form-label">Tipo</label><input type="text" name="tipo" class="form-input" id="tanqueTipo"></div></div><div class="form-row"><div class="form-group"><label class="form-label">Material</label><input type="text" name="material" class="form-input" id="tanqueMaterial"></div><div class="form-group"><label class="form-label">Diámetro (cm)</label><input type="number" name="diametro" class="form-input" id="tanqueDiametro" step="0.01"></div></div><div class="form-row"><div class="form-group"><label class="form-label">Volumen útil (L)</label><input type="number" name="volumen_util" class="form-input" id="tanqueVolumenUtil" step="0.01"></div><div class="form-group"><label class="form-label">Fecha instalación</label><input type="date" name="fecha_instalacion" class="form-input" id="tanqueFechaInst"></div></div><div class="form-group"><label class="form-label">Descripción</label><textarea name="descripcion" class="form-input" id="tanqueDescripcion" rows="3"></textarea></div><div class="form-group"><label class="form-label"><input type="checkbox" name="activo" id="tanqueActivo" value="1" checked> Activo</label></div></form></div><div class="modal-footer"><button class="btn btn-secondary" onclick="cerrarModalTanque()">Cancelar</button><button class="btn btn-primary" onclick="document.getElementById('formTanque').submit()">Guardar</button></div></div></div>
<div class="modal-overlay" id="modalVerTanque"><div class="modal"><div class="modal-header"><h3 class="modal-title">Detalle tanque</h3><button class="modal-close" onclick="document.getElementById('modalVerTanque').classList.remove('active')">&times;</button></div><div class="modal-body" id="verTanqueBody"></div><div class="modal-footer"><button class="btn btn-secondary" onclick="document.getElementById('modalVerTanque').classList.remove('active')">Cerrar</button></div></div></div>
<script src="js/admin.js"></script>
<script>function abrirModalTanque(){document.getElementById('modalTanqueTitle').textContent='Agregar Tanque';document.getElementById('tanqueAccion').value='crear';document.getElementById('tanqueId').value='';document.getElementById('formTanque').reset();document.getElementById('modalTanque').classList.add('active');}function editarTanque(id){var r=document.querySelector('tr[data-id="'+id+'"]');if(!r)return;document.getElementById('modalTanqueTitle').textContent='Editar Tanque';document.getElementById('tanqueAccion').value='editar';document.getElementById('tanqueId').value=id;document.getElementById('tanqueNombre').value=r.getAttribute('data-nombre')||'';document.getElementById('tanqueEdificio').value=r.getAttribute('data-edificio')||'';document.getElementById('tanqueCapacidad').value=r.getAttribute('data-capacidad')||'';document.getElementById('tanqueAltura').value=r.getAttribute('data-altura')||'';document.getElementById('tanqueUbicacion').value=r.getAttribute('data-ubicacion')||'';document.getElementById('tanqueTipo').value=r.getAttribute('data-tipo')||'';document.getElementById('tanqueMaterial').value=r.getAttribute('data-material')||'';document.getElementById('tanqueDiametro').value=r.getAttribute('data-diametro')||'';document.getElementById('tanqueVolumenUtil').value=r.getAttribute('data-volumen')||'';document.getElementById('tanqueFechaInst').value=r.getAttribute('data-fecha')||'';document.getElementById('tanqueDescripcion').value=r.getAttribute('data-descripcion')||'';document.getElementById('tanqueActivo').checked=r.getAttribute('data-activo')=='1';document.getElementById('modalTanque').classList.add('active');}function cerrarModalTanque(){document.getElementById('modalTanque').classList.remove('active');}function verTanque(id){var r=document.querySelector('tr[data-id="'+id+'"]');if(!r)return;var h='<p><b>Nombre:</b> '+r.getAttribute('data-nombre')+'</p><p><b>Capacidad:</b> '+r.getAttribute('data-capacidad')+' L</p><p><b>Altura:</b> '+r.getAttribute('data-altura')+' cm</p><p><b>Ubicación:</b> '+(r.getAttribute('data-ubicacion')||'-')+'</p><p><b>Tipo:</b> '+(r.getAttribute('data-tipo')||'-')+'</p><p><b>Material:</b> '+(r.getAttribute('data-material')||'-')+'</p><p><b>Descripción:</b> '+(r.getAttribute('data-descripcion')||'-')+'</p>';document.getElementById('verTanqueBody').innerHTML=h;document.getElementById('modalVerTanque').classList.add('active');}function eliminarTanque(id){if(confirm('¿Eliminar/desactivar este tanque? Si tiene dispositivos se desactivará.')){var f=document.createElement('form');f.method='POST';f.action='tanques.php';f.innerHTML='<input type="hidden" name="accion" value="eliminar"><input type="hidden" name="tanque_id" value="'+id+'">';document.body.appendChild(f);f.submit();}}</script>
</body>
</html>
