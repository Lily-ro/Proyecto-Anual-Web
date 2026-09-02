<?php
session_start();
if(!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'ADMIN'){
    header("Location: ../index.php");
    exit;
}
require_once(__DIR__ . '/../config/db.php');

if($_SERVER['REQUEST_METHOD'] === 'POST'){
 $accion = $_POST['accion'] ?? '';

 if($accion === 'crear'){
  $nombre    = trim($_POST['nombre'] ?? '');
  $apellido  = trim($_POST['apellido'] ?? '');
  $email     = trim($_POST['email'] ?? '');
  $password  = $_POST['password'] ?? '';
  $rol       = $_POST['rol'] ?? '';
  $activo    = (int)($_POST['activo'] ?? 1);

  if($nombre && $apellido && $email && $password && $rol){
   if(strlen($password) < 6){
    echo '<script>alert("La contraseña debe tener al menos 6 caracteres");history.back();</script>';
    exit;
   }
   $check = $conn->prepare("SELECT id_usuario FROM usuarios WHERE email=? LIMIT 1");
   $check->bind_param("s", $email);
   $check->execute();
   if($check->get_result()->num_rows > 0){
    echo '<script>alert("Ya existe un usuario con ese email");history.back();</script>';
    $check->close();
    exit;
   }
   $check->close();
   $hash = password_hash($password, PASSWORD_DEFAULT);
   $stmt = $conn->prepare("INSERT INTO usuarios (nombre, apellido, email, password_hash, activo, id_rol) VALUES (?,?,?,?,?,(SELECT id_rol FROM roles WHERE nombre=? LIMIT 1))");
   $stmt->bind_param("ssssis", $nombre, $apellido, $email, $hash, $activo, $rol);
   if($stmt->execute()){
    echo '<script>alert("Usuario creado exitosamente");window.location="usuarios.php";</script>';
   } else {
    echo '<script>alert("Error al crear usuario");history.back();</script>';
   }
   $stmt->close();
   exit;
  }
  echo '<script>alert("Todos los campos son obligatorios");history.back();</script>';
  exit;
 }

 if($accion === 'editar'){
  $id       = (int)($_POST['usuario_id'] ?? 0);
  $nombre   = trim($_POST['nombre'] ?? '');
  $apellido = trim($_POST['apellido'] ?? '');
  $email    = trim($_POST['email'] ?? '');
  $rol      = $_POST['rol'] ?? '';
  $activo   = (int)($_POST['activo'] ?? 1);

  if($id && $nombre && $apellido && $email && $rol){
   $check = $conn->prepare("SELECT id_usuario FROM usuarios WHERE email=? AND id_usuario!=? LIMIT 1");
   $check->bind_param("si", $email, $id);
   $check->execute();
   if($check->get_result()->num_rows > 0){
    echo '<script>alert("Ya existe otro usuario con ese email");history.back();</script>';
    $check->close();
    exit;
   }
   $check->close();
   $stmt = $conn->prepare("UPDATE usuarios SET nombre=?, apellido=?, email=?, activo=?, id_rol=(SELECT id_rol FROM roles WHERE nombre=? LIMIT 1) WHERE id_usuario=?");
   $stmt->bind_param("sssisi", $nombre, $apellido, $email, $activo, $rol, $id);
   if($stmt->execute()){
    echo '<script>alert("Usuario actualizado exitosamente");window.location="usuarios.php";</script>';
   } else {
    echo '<script>alert("Error al actualizar usuario");history.back();</script>';
   }
   $stmt->close();
   exit;
  }
  echo '<script>alert("Todos los campos son obligatorios");history.back();</script>';
  exit;
 }

 if($accion === 'cambiar_password'){
  $id        = (int)($_POST['usuario_id'] ?? 0);
  $nueva     = $_POST['nueva_password'] ?? '';
  $confirmar = $_POST['confirmar_password'] ?? '';

  if($id && $nueva){
   if(strlen($nueva) < 6){
    echo '<script>alert("La contraseña debe tener al menos 6 caracteres");history.back();</script>';
    exit;
   }
   if($nueva !== $confirmar){
    echo '<script>alert("Las contraseñas no coinciden");history.back();</script>';
    exit;
   }
   $hash = password_hash($nueva, PASSWORD_DEFAULT);
   $stmt = $conn->prepare("UPDATE usuarios SET password_hash=? WHERE id_usuario=?");
   $stmt->bind_param("si", $hash, $id);
   if($stmt->execute()){
    echo '<script>alert("Contraseña actualizada exitosamente");window.location="usuarios.php";</script>';
   } else {
    echo '<script>alert("Error al actualizar contraseña");history.back();</script>';
   }
   $stmt->close();
   exit;
  }
  echo '<script>alert("Datos incompletos");history.back();</script>';
  exit;
 }

 if($accion === 'toggle_estado'){
  $id     = (int)($_POST['usuario_id'] ?? 0);
  $activo = (int)($_POST['activo'] ?? 1);
  if($id){
   $stmt = $conn->prepare("UPDATE usuarios SET activo=? WHERE id_usuario=?");
   $stmt->bind_param("ii", $activo, $id);
   if($stmt->execute()){
    $txt = $activo ? 'activado' : 'desactivado';
    echo '<script>alert("Usuario '.$txt.' exitosamente");window.location="usuarios.php";</script>';
   } else {
    echo '<script>alert("Error al cambiar estado");history.back();</script>';
   }
   $stmt->close();
   exit;
  }
 }

 if($accion === 'eliminar'){
  $id = (int)($_POST['usuario_id'] ?? 0);
  if($id){
   if($id == $_SESSION['id_usuario']){
    echo '<script>alert("No puedes eliminarte a ti mismo");history.back();</script>';
    exit;
   }
   $stmt = $conn->prepare("DELETE FROM usuarios WHERE id_usuario=?");
   $stmt->bind_param("i", $id);
   if($stmt->execute()){
    echo '<script>alert("Usuario eliminado exitosamente");window.location="usuarios.php";</script>';
   } else {
    echo '<script>alert("Error al eliminar usuario. Puede tener registros asociados.");history.back();</script>';
   }
   $stmt->close();
   exit;
  }
 }
}

$currentPage = 'usuarios';
$pageSubtitle = 'Administra los usuarios registrados en el sistema EVA.';

$resRoles = $conn->query("SELECT id_rol, nombre FROM roles WHERE nombre IN ('USUARIO','TECNICO') ORDER BY nombre");
$roles = [];
if($resRoles){ while($r = $resRoles->fetch_assoc()) $roles[] = $r; }

$resUsuarios = $conn->query("
 SELECT u.id_usuario, u.nombre, u.apellido, u.email, u.activo,
        u.ultimo_acceso, u.fecha_registro, r.nombre AS rol, r.id_rol
 FROM usuarios u
 JOIN roles r ON u.id_rol = r.id_rol
 ORDER BY u.fecha_registro DESC
");
$listaUsuarios = [];
if($resUsuarios){ while($u = $resUsuarios->fetch_assoc()) $listaUsuarios[] = $u; }

function avatarColor($id){
 $colors = [
  ['#2c6cef','#4fc3f7'],['#4caf50','#66bb6a'],['#ff9800','#ffb74d'],
  ['#f44336','#ef5350'],['#9c27b0','#ba68c8'],['#607d8b','#90a4ae'],
  ['#00bcd4','#4dd0e1'],['#e91e63','#f06292']
 ];
 $c = $colors[$id % count($colors)];
 return 'background:linear-gradient(135deg,'.$c[0].','.$c[1].')';
}

function iniciales($nombre, $apellido){
 $ini = strtoupper(substr($nombre,0,1));
 if($apellido) $ini .= strtoupper(substr($apellido,0,1));
 return $ini;
}

function rolBadge($rol){
 $map = ['ADMIN'=>'rol-admin','TECNICO'=>'rol-tecnico','USUARIO'=>'rol-cliente'];
 $cls = $map[$rol] ?? '';
 $txt = $rol === 'USUARIO' ? 'Cliente' : ($rol === 'TECNICO' ? 'Técnico' : 'Administrador');
 return '<span class="badge '.$cls.'">'.htmlspecialchars($txt).'</span>';
}

function estadoBadge($activo){
 return $activo ? '<span class="badge activo">Activo</span>' : '<span class="badge inactivo">Inactivo</span>';
}

function tiempoDesde($fecha){
 if(!$fecha) return 'Nunca';
 $diff = time() - strtotime($fecha);
 if($diff < 0) return 'Ahora';
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
<title>EVA - Gestionar Usuarios</title>
<link rel="stylesheet" href="css/admin.css">
</head>
<body>
<?php include __DIR__ . '/includes/sidebar.php'; ?>

<div class="main">
 <?php include __DIR__ . '/includes/header.php'; ?>

 <div class="content">
  <div class="page-header">
   <h2 class="page-title">Gestionar usuarios</h2>
   <p class="page-desc">Administra los usuarios registrados en el sistema EVA.</p>
  </div>

  <div class="card">
   <div class="card-header">
    <div class="filters-row">
     <div class="filter-group">
      <input type="text" class="filter-input" id="userSearch" placeholder="Buscar por nombre o correo...">
     </div>
     <div class="filter-group">
      <select class="filter-input" id="filterRol">
       <option value="">Todos los roles</option>
       <option value="USUARIO">Cliente</option>
       <option value="TECNICO">Técnico</option>
      </select>
     </div>
     <div class="filter-group">
      <select class="filter-input" id="filterEstado">
       <option value="">Todos los estados</option>
       <option value="1">Activo</option>
       <option value="0">Inactivo</option>
      </select>
     </div>
     <div class="filter-group filter-group-btn">
      <button class="btn btn-primary" onclick="abrirModalUsuario()">+ Nuevo usuario</button>
     </div>
    </div>
   </div>
   <div class="table-responsive">
    <table class="table" id="tablaUsuarios">
     <thead>
      <tr>
       <th>Foto</th>
       <th>Nombre</th>
       <th>Email</th>
       <th>Rol</th>
       <th>Estado</th>
       <th>Último acceso</th>
       <th>Acciones</th>
      </tr>
     </thead>
     <tbody>
      <?php if(count($listaUsuarios) > 0): ?>
       <?php foreach($listaUsuarios as $u): ?>
        <tr data-rol="<?php echo $u['rol']; ?>"
            data-estado="<?php echo $u['activo']; ?>"
            data-nombre="<?php echo htmlspecialchars(strtolower($u['nombre'].' '.$u['apellido'])); ?>"
            data-email="<?php echo htmlspecialchars(strtolower($u['email'])); ?>">
         <td>
          <div class="user-avatar-sm" style="<?php echo avatarColor($u['id_usuario']); ?>">
           <?php echo iniciales($u['nombre'], $u['apellido']); ?>
          </div>
         </td>
         <td><?php echo htmlspecialchars($u['nombre'].' '.$u['apellido']); ?></td>
         <td><?php echo htmlspecialchars($u['email']); ?></td>
         <td><?php echo rolBadge($u['rol']); ?></td>
         <td><?php echo estadoBadge($u['activo']); ?></td>
         <td><?php echo tiempoDesde($u['ultimo_acceso']); ?></td>
         <td class="actions-cell">
          <button class="btn-icon" title="Editar usuario" onclick="editarUsuario(<?php echo $u['id_usuario']; ?>)"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button>
          <button class="btn-icon" title="Cambiar contraseña" onclick="cambiarContrasena(<?php echo $u['id_usuario']; ?>)"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg></button>
          <?php if($u['activo']): ?>
           <button class="btn-icon" title="Desactivar usuario" onclick="toggleEstado(<?php echo $u['id_usuario']; ?>,1)"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 11-2.12-9.36L23 10"/></svg></button>
          <?php else: ?>
           <button class="btn-icon" title="Activar usuario" onclick="toggleEstado(<?php echo $u['id_usuario']; ?>,0)"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg></button>
          <?php endif; ?>
          <button class="btn-icon btn-icon-danger" title="Eliminar usuario" onclick="eliminarUsuario(<?php echo $u['id_usuario']; ?>)"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg></button>
         </td>
        </tr>
       <?php endforeach; ?>
      <?php else: ?>
       <tr><td colspan="7" class="empty-state"><p>No hay usuarios registrados</p></td></tr>
      <?php endif; ?>
     </tbody>
    </table>
   </div>
   <div class="table-footer">
    <div class="table-info">Mostrando <span id="userCount"><?php echo count($listaUsuarios); ?></span> usuarios</div>
    <div class="pagination">
     <button class="btn-page" disabled>&laquo;</button>
     <button class="btn-page btn-page-active">1</button>
     <button class="btn-page" disabled>»</button>
    </div>
   </div>
  </div>
 </div>
</div>

<div class="modal-overlay" id="modalUsuario">
 <div class="modal">
  <div class="modal-header">
   <h3 class="modal-title" id="modalUsuarioTitle">Nuevo usuario</h3>
   <button class="modal-close" onclick="cerrarModalUsuario()">&times;</button>
  </div>
  <div class="modal-body">
   <form id="formUsuario" method="POST" action="usuarios.php">
    <input type="hidden" name="accion" id="usuarioAccion" value="crear">
    <input type="hidden" name="usuario_id" id="usuarioId" value="">
    <div class="form-group">
     <label class="form-label">Nombre</label>
     <input type="text" class="form-input" name="nombre" id="usuarioNombre" placeholder="Ej: Juan" required>
    </div>
    <div class="form-group">
     <label class="form-label">Apellido</label>
     <input type="text" class="form-input" name="apellido" id="usuarioApellido" placeholder="Ej: Pérez" required>
    </div>
    <div class="form-group">
     <label class="form-label">Correo electrónico</label>
     <input type="email" class="form-input" name="email" id="usuarioEmail" placeholder="Ej: juan@eva.com" required>
    </div>
    <div class="form-group" id="grupoContrasena">
     <label class="form-label">Contraseña</label>
     <input type="password" class="form-input" name="password" id="usuarioContrasena" placeholder="Mínimo 6 caracteres">
    </div>
    <div class="form-row">
     <div class="form-group">
      <label class="form-label">Rol</label>
      <select class="form-input" name="rol" id="usuarioRol" required>
       <option value="">Seleccionar...</option>
       <option value="USUARIO">Cliente</option>
       <option value="TECNICO">Técnico</option>
       <option value="ADMIN">Administrador</option>
      </select>
     </div>
     <div class="form-group">
      <label class="form-label">Estado</label>
      <select class="form-input" name="activo" id="usuarioEstado" required>
       <option value="1">Activo</option>
       <option value="0">Inactivo</option>
      </select>
     </div>
    </div>
   </form>
  </div>
  <div class="modal-footer">
   <button class="btn btn-secondary" onclick="cerrarModalUsuario()">Cancelar</button>
   <button class="btn btn-primary" onclick="guardarUsuario()">Guardar</button>
  </div>
 </div>
</div>

<div class="modal-overlay" id="modalContrasena">
 <div class="modal">
  <div class="modal-header">
   <h3 class="modal-title">Cambiar contraseña</h3>
   <button class="modal-close" onclick="cerrarModalContrasena()">&times;</button>
  </div>
  <div class="modal-body">
   <form id="formContrasena" method="POST" action="usuarios.php">
    <input type="hidden" name="accion" value="cambiar_password">
    <input type="hidden" name="usuario_id" id="contrasenaUserId" value="">
    <div class="form-group">
     <label class="form-label">Nueva contraseña</label>
     <input type="password" class="form-input" name="nueva_password" id="nuevaContrasena" placeholder="Mínimo 6 caracteres" required>
    </div>
    <div class="form-group">
     <label class="form-label">Confirmar contraseña</label>
     <input type="password" class="form-input" name="confirmar_password" id="confirmarContrasena" placeholder="Repetir contraseña" required>
    </div>
   </form>
  </div>
  <div class="modal-footer">
   <button class="btn btn-secondary" onclick="cerrarModalContrasena()">Cancelar</button>
   <button class="btn btn-primary" onclick="guardarContrasena()">Actualizar contraseña</button>
  </div>
 </div>
</div>

<script src="js/admin.js"></script>
<script>
function abrirModalUsuario(){
 document.getElementById('modalUsuarioTitle').textContent = 'Nuevo usuario';
 document.getElementById('usuarioAccion').value = 'crear';
 document.getElementById('usuarioId').value = '';
 document.getElementById('formUsuario').reset();
 document.getElementById('grupoContrasena').style.display = '';
 document.getElementById('modalUsuario').classList.add('active');
}
function editarUsuario(id){
 document.getElementById('modalUsuarioTitle').textContent = 'Editar usuario';
 document.getElementById('usuarioAccion').value = 'editar';
 document.getElementById('usuarioId').value = id;
 document.getElementById('grupoContrasena').style.display = 'none';
 document.getElementById('modalUsuario').classList.add('active');
}
function cerrarModalUsuario(){
 document.getElementById('modalUsuario').classList.remove('active');
}
function guardarUsuario(){
 document.getElementById('formUsuario').submit();
}
function cambiarContrasena(id){
 document.getElementById('contrasenaUserId').value = id;
 document.getElementById('nuevaContrasena').value = '';
 document.getElementById('confirmarContrasena').value = '';
 document.getElementById('modalContrasena').classList.add('active');
}
function cerrarModalContrasena(){
 document.getElementById('modalContrasena').classList.remove('active');
}
function guardarContrasena(){
 var n = document.getElementById('nuevaContrasena').value;
 var c = document.getElementById('confirmarContrasena').value;
 if(n.length < 6){ alert('La contraseña debe tener al menos 6 caracteres'); return; }
 if(n !== c){ alert('Las contraseñas no coinciden'); return; }
 document.getElementById('formContrasena').submit();
}
function toggleEstado(id, actual){
 if(confirm(actual ? '¿Desactivar este usuario?' : '¿Activar este usuario?')){
  var f = document.createElement('form');
  f.method='POST'; f.action='usuarios.php';
  f.innerHTML='<input type="hidden" name="accion" value="toggle_estado"><input type="hidden" name="usuario_id" value="'+id+'"><input type="hidden" name="activo" value="'+(actual?0:1)+'">';
  document.body.appendChild(f); f.submit();
 }
}
function eliminarUsuario(id){
 if(confirm('¿Eliminar este usuario? Esta acción no se puede deshacer.')){
  var f = document.createElement('form');
  f.method='POST'; f.action='usuarios.php';
  f.innerHTML='<input type="hidden" name="accion" value="eliminar"><input type="hidden" name="usuario_id" value="'+id+'">';
  document.body.appendChild(f); f.submit();
 }
}
(function(){
 var s=document.getElementById('userSearch'),r=document.getElementById('filterRol'),e=document.getElementById('filterEstado');
 if(!s)return;
 function filtrar(){
  var t=s.value.toLowerCase(),rv=r.value,ev=e.value,c=0;
  document.querySelectorAll('#tablaUsuarios tbody tr').forEach(function(f){
   var vis=(!t||((f.dataset.nombre||'').includes(t)||(f.dataset.email||'').includes(t)))&&(!rv||f.dataset.rol===rv)&&(!ev||f.dataset.estado===ev);
   f.style.display=vis?'':'none'; if(vis)c++;
  });
  document.getElementById('userCount').textContent=c;
 }
 s.addEventListener('input',filtrar); r.addEventListener('change',filtrar); e.addEventListener('change',filtrar);
})();
</script>
</body>
</html>
