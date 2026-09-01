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
  $cuit      = trim($_POST['cuit'] ?? '');
  $telefono  = trim($_POST['telefono'] ?? '');
  $email     = trim($_POST['email'] ?? '');
  $direccion = trim($_POST['direccion'] ?? '');
  $ciudad    = trim($_POST['ciudad'] ?? '');
  $provincia = trim($_POST['provincia'] ?? '');
  $pais      = trim($_POST['pais'] ?? '');
  $activo    = (int)($_POST['activo'] ?? 1);

  if($nombre){
   if($cuit){
    $check = $conn->prepare("SELECT id_empresa FROM empresas WHERE cuit=? LIMIT 1");
    $check->bind_param("s", $cuit);
    $check->execute();
    if($check->get_result()->num_rows > 0){
     echo '<script>alert("Ya existe una empresa con ese CUIT");history.back();</script>';
     $check->close();
     exit;
    }
    $check->close();
   }
   $stmt = $conn->prepare("INSERT INTO empresas (nombre, cuit, telefono, email, direccion, ciudad, provincia, pais, activo) VALUES (?,?,?,?,?,?,?,?,?)");
   $stmt->bind_param("ssssssssi", $nombre, $cuit, $telefono, $email, $direccion, $ciudad, $provincia, $pais, $activo);
   if($stmt->execute()){
    echo '<script>alert("Empresa creada exitosamente");window.location="empresas.php";</script>';
   } else {
    echo '<script>alert("Error al crear empresa");history.back();</script>';
   }
   $stmt->close();
   exit;
  }
  echo '<script>alert("El nombre es obligatorio");history.back();</script>';
  exit;
 }

 if($accion === 'editar'){
  $id       = (int)($_POST['empresa_id'] ?? 0);
  $nombre   = trim($_POST['nombre'] ?? '');
  $cuit     = trim($_POST['cuit'] ?? '');
  $telefono = trim($_POST['telefono'] ?? '');
  $email    = trim($_POST['email'] ?? '');
  $direccion = trim($_POST['direccion'] ?? '');
  $ciudad   = trim($_POST['ciudad'] ?? '');
  $provincia = trim($_POST['provincia'] ?? '');
  $pais     = trim($_POST['pais'] ?? '');
  $activo   = (int)($_POST['activo'] ?? 1);

  if($id && $nombre){
   if($cuit){
    $check = $conn->prepare("SELECT id_empresa FROM empresas WHERE cuit=? AND id_empresa!=? LIMIT 1");
    $check->bind_param("si", $cuit, $id);
    $check->execute();
    if($check->get_result()->num_rows > 0){
     echo '<script>alert("Ya existe otra empresa con ese CUIT");history.back();</script>';
     $check->close();
     exit;
    }
    $check->close();
   }
   $stmt = $conn->prepare("UPDATE empresas SET nombre=?, cuit=?, telefono=?, email=?, direccion=?, ciudad=?, provincia=?, pais=?, activo=? WHERE id_empresa=?");
   $stmt->bind_param("ssssssssii", $nombre, $cuit, $telefono, $email, $direccion, $ciudad, $provincia, $pais, $activo, $id);
   if($stmt->execute()){
    echo '<script>alert("Empresa actualizada exitosamente");window.location="empresas.php";</script>';
   } else {
    echo '<script>alert("Error al actualizar empresa");history.back();</script>';
   }
   $stmt->close();
   exit;
  }
  echo '<script>alert("El nombre es obligatorio");history.back();</script>';
  exit;
 }

 if($accion === 'toggle_estado'){
  $id     = (int)($_POST['empresa_id'] ?? 0);
  $activo = (int)($_POST['activo'] ?? 1);
  if($id){
   $stmt = $conn->prepare("UPDATE empresas SET activo=? WHERE id_empresa=?");
   $stmt->bind_param("ii", $activo, $id);
   if($stmt->execute()){
    $txt = $activo ? 'activada' : 'desactivada';
    echo '<script>alert("Empresa '.$txt.' exitosamente");window.location="empresas.php";</script>';
   } else {
    echo '<script>alert("Error al cambiar estado");history.back();</script>';
   }
   $stmt->close();
   exit;
  }
 }

 if($accion === 'eliminar'){
  $id = (int)($_POST['empresa_id'] ?? 0);
  if($id){
   $checkEdif = $conn->prepare("SELECT COUNT(*) FROM edificios WHERE id_usuario IN (SELECT id_usuario FROM usuarios WHERE id_empresa=?)");
   $checkEdif->bind_param("i", $id);
   $checkEdif->execute();
   $cnt = $checkEdif->get_result()->fetch_row()[0];
   $checkEdif->close();
   if($cnt > 0){
    echo '<script>alert("No se puede eliminar: la empresa tiene edificios/asociados. Desactívela en su lugar.");history.back();</script>';
    exit;
   }
   $stmt = $conn->prepare("DELETE FROM empresas WHERE id_empresa=?");
   $stmt->bind_param("i", $id);
   if($stmt->execute()){
    echo '<script>alert("Empresa eliminada exitosamente");window.location="empresas.php";</script>';
   } else {
    echo '<script>alert("Error al eliminar empresa");history.back();</script>';
   }
   $stmt->close();
   exit;
  }
 }
}

$currentPage = 'empresas';
$pageSubtitle = 'Gestión de empresas';

$resEmpresas = $conn->query("SELECT * FROM empresas ORDER BY fecha_alta DESC");
$listaEmpresas = [];
if($resEmpresas){ while($e = $resEmpresas->fetch_assoc()) $listaEmpresas[] = $e; }

function empresaBadge($activo){
 return $activo ? '<span class="badge activo">Activo</span>' : '<span class="badge inactivo">Inactivo</span>';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>EVA - Gestión de Empresas</title>
<link rel="stylesheet" href="css/admin.css">
</head>
<body>
<?php include __DIR__ . '/includes/sidebar.php'; ?>

<div class="main">
 <?php include __DIR__ . '/includes/header.php'; ?>

 <div class="content">
  <div class="page-header">
   <h2 class="page-title">Gestión de Empresas</h2>
   <p class="page-desc">Administrar empresas registradas en el sistema.</p>
  </div>

  <div class="card">
   <div class="card-header">
    <div class="filters-row">
     <div class="filter-group">
      <input type="text" class="filter-input" id="empresaSearch" placeholder="Buscar empresa...">
     </div>
     <div class="filter-group">
      <select class="filter-input" id="filterEstado">
       <option value="">Todos los estados</option>
       <option value="1">Activo</option>
       <option value="0">Inactivo</option>
      </select>
     </div>
     <div class="filter-group filter-group-btn">
      <button class="btn btn-primary" onclick="abrirModalEmpresa()">+ Agregar Empresa</button>
     </div>
    </div>
   </div>
   <div class="table-responsive">
    <table class="table" id="tablaEmpresas">
     <thead>
      <tr>
       <th>Nombre</th>
       <th>CUIT</th>
       <th>Email</th>
       <th>Teléfono</th>
       <th>Ciudad</th>
       <th>Estado</th>
       <th>Acciones</th>
      </tr>
     </thead>
     <tbody>
      <?php if(count($listaEmpresas) > 0): ?>
       <?php foreach($listaEmpresas as $e): ?>
        <tr data-estado="<?php echo $e['activo']; ?>"
            data-nombre="<?php echo htmlspecialchars(strtolower($e['nombre'])); ?>"
            data-cuit="<?php echo htmlspecialchars(strtolower($e['cuit'])); ?>">
         <td><?php echo htmlspecialchars($e['nombre']); ?></td>
         <td><?php echo htmlspecialchars($e['cuit'] ?: '-'); ?></td>
         <td><?php echo htmlspecialchars($e['email'] ?: '-'); ?></td>
         <td><?php echo htmlspecialchars($e['telefono'] ?: '-'); ?></td>
         <td><?php echo htmlspecialchars($e['ciudad'] ?: '-'); ?></td>
         <td><?php echo empresaBadge($e['activo']); ?></td>
         <td class="actions-cell">
          <button class="btn-icon" title="Editar" onclick="editarEmpresa(<?php echo $e['id_empresa']; ?>)"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button>
          <?php if($e['activo']): ?>
           <button class="btn-icon" title="Desactivar" onclick="toggleEstado(<?php echo $e['id_empresa']; ?>,1)"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 11-2.12-9.36L23 10"/></svg></button>
          <?php else: ?>
           <button class="btn-icon" title="Activar" onclick="toggleEstado(<?php echo $e['id_empresa']; ?>,0)"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg></button>
          <?php endif; ?>
          <button class="btn-icon btn-icon-danger" title="Eliminar" onclick="eliminarEmpresa(<?php echo $e['id_empresa']; ?>)"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg></button>
         </td>
        </tr>
       <?php endforeach; ?>
      <?php else: ?>
       <tr><td colspan="7" style="text-align:center;color:var(--tx4);padding:40px">No hay empresas registradas</td></tr>
      <?php endif; ?>
     </tbody>
    </table>
   </div>
   <div class="table-footer">
    <div class="table-info">Mostrando <span id="empresaCount"><?php echo count($listaEmpresas); ?></span> empresas</div>
   </div>
  </div>
 </div>
</div>

<div class="modal-overlay" id="modalEmpresa">
 <div class="modal">
  <div class="modal-header">
   <h3 class="modal-title" id="modalEmpresaTitle">Nueva empresa</h3>
   <button class="modal-close" onclick="cerrarModalEmpresa()">&times;</button>
  </div>
  <div class="modal-body">
   <form id="formEmpresa" method="POST" action="empresas.php">
    <input type="hidden" name="accion" id="empresaAccion" value="crear">
    <input type="hidden" name="empresa_id" id="empresaId" value="">
    <div class="form-row">
     <div class="form-group">
      <label class="form-label">Nombre *</label>
      <input type="text" class="form-input" name="nombre" id="empNombre" placeholder="Ej: Consorcios Norte S.A." required>
     </div>
     <div class="form-group">
      <label class="form-label">CUIT</label>
      <input type="text" class="form-input" name="cuit" id="empCuit" placeholder="Ej: 30-71234567-8">
     </div>
    </div>
    <div class="form-row">
     <div class="form-group">
      <label class="form-label">Email</label>
      <input type="email" class="form-input" name="email" id="empEmail" placeholder="contacto@empresa.com">
     </div>
     <div class="form-group">
      <label class="form-label">Teléfono</label>
      <input type="text" class="form-input" name="telefono" id="empTelefono" placeholder="Ej: 011-4790-1122">
     </div>
    </div>
    <div class="form-group">
     <label class="form-label">Dirección</label>
     <input type="text" class="form-input" name="direccion" id="empDireccion" placeholder="Ej: Av. Maipú 1500">
    </div>
    <div class="form-row">
     <div class="form-group">
      <label class="form-label">Ciudad</label>
      <input type="text" class="form-input" name="ciudad" id="empCiudad" placeholder="Ej: Vicente López">
     </div>
     <div class="form-group">
      <label class="form-label">Provincia</label>
      <input type="text" class="form-input" name="provincia" id="empProvincia" placeholder="Ej: Buenos Aires">
     </div>
    </div>
    <div class="form-row">
     <div class="form-group">
      <label class="form-label">País</label>
      <input type="text" class="form-input" name="pais" id="empPais" placeholder="Ej: Argentina">
     </div>
     <div class="form-group">
      <label class="form-label">Estado</label>
      <select class="form-input" name="activo" id="empEstado" required>
       <option value="1">Activo</option>
       <option value="0">Inactivo</option>
      </select>
     </div>
    </div>
   </form>
  </div>
  <div class="modal-footer">
   <button class="btn btn-secondary" onclick="cerrarModalEmpresa()">Cancelar</button>
   <button class="btn btn-primary" onclick="guardarEmpresa()">Guardar</button>
  </div>
 </div>
</div>

<script src="js/admin.js"></script>
<script>
var empresasData = {
 <?php foreach($listaEmpresas as $e): ?>
 <?php echo $e['id_empresa']; ?>:{nombre:"<?php echo addslashes($e['nombre']); ?>",cuit:"<?php echo addslashes($e['cuit']); ?>",email:"<?php echo addslashes($e['email']); ?>",telefono:"<?php echo addslashes($e['telefono']); ?>",direccion:"<?php echo addslashes($e['direccion']); ?>",ciudad:"<?php echo addslashes($e['ciudad']); ?>",provincia:"<?php echo addslashes($e['provincia']); ?>",pais:"<?php echo addslashes($e['pais']); ?>",activo:<?php echo $e['activo']; ?>},
 <?php endforeach; ?>
};

function abrirModalEmpresa(){
 document.getElementById('modalEmpresaTitle').textContent='Nueva empresa';
 document.getElementById('empresaAccion').value='crear';
 document.getElementById('empresaId').value='';
 document.getElementById('formEmpresa').reset();
 document.getElementById('empEstado').value='1';
 document.getElementById('modalEmpresa').classList.add('active');
}
function editarEmpresa(id){
 var e=empresasData[id]; if(!e)return;
 document.getElementById('modalEmpresaTitle').textContent='Editar empresa';
 document.getElementById('empresaAccion').value='editar';
 document.getElementById('empresaId').value=id;
 document.getElementById('empNombre').value=e.nombre||'';
 document.getElementById('empCuit').value=e.cuit||'';
 document.getElementById('empEmail').value=e.email||'';
 document.getElementById('empTelefono').value=e.telefono||'';
 document.getElementById('empDireccion').value=e.direccion||'';
 document.getElementById('empCiudad').value=e.ciudad||'';
 document.getElementById('empProvincia').value=e.provincia||'';
 document.getElementById('empPais').value=e.pais||'';
 document.getElementById('empEstado').value=e.activo;
 document.getElementById('modalEmpresa').classList.add('active');
}
function cerrarModalEmpresa(){
 document.getElementById('modalEmpresa').classList.remove('active');
}
function guardarEmpresa(){
 var nombre=document.getElementById('empNombre').value.trim();
 if(!nombre){alert('El nombre es obligatorio');return;}
 document.getElementById('formEmpresa').submit();
}
function toggleEstado(id,actual){
 if(confirm(actual?'¿Desactivar esta empresa?':'¿Activar esta empresa?')){
  var f=document.createElement('form');f.method='POST';f.action='empresas.php';
  f.innerHTML='<input type="hidden" name="accion" value="toggle_estado"><input type="hidden" name="empresa_id" value="'+id+'"><input type="hidden" name="activo" value="'+(actual?0:1)+'">';
  document.body.appendChild(f);f.submit();
 }
}
function eliminarEmpresa(id){
 if(confirm('¿Eliminar esta empresa? Esta acción no se puede deshacer.')){
  var f=document.createElement('form');f.method='POST';f.action='empresas.php';
  f.innerHTML='<input type="hidden" name="accion" value="eliminar"><input type="hidden" name="empresa_id" value="'+id+'">';
  document.body.appendChild(f);f.submit();
 }
}
(function(){
 var s=document.getElementById('empresaSearch'),e=document.getElementById('filterEstado');
 if(!s)return;
 function filtrar(){
  var t=s.value.toLowerCase(),ev=e.value,c=0;
  document.querySelectorAll('#tablaEmpresas tbody tr').forEach(function(f){
   var vis=(!t||((f.dataset.nombre||'').includes(t)||(f.dataset.cuit||'').includes(t)))&&(!ev||f.dataset.estado===ev);
   f.style.display=vis?'':'none';if(vis)c++;
  });
  document.getElementById('empresaCount').textContent=c;
 }
 s.addEventListener('input',filtrar);e.addEventListener('change',filtrar);
})();
</script>
</body>
</html>
