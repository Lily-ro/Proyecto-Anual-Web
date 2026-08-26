<?php
session_start();
if(!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'ADMIN'){
    header("Location: ../index.php");
    exit;
}
require_once(__DIR__ . '/../config/db.php');

// === PROCESAMIENTO DE ACCIONES POST ===
if($_SERVER['REQUEST_METHOD'] === 'POST'){
 $accion = $_POST['accion'] ?? '';

 // --- CREAR EDIFICIO ---
 if($accion === 'crear'){
  $nombre      = trim($_POST['nombre'] ?? '');
  $direccion   = trim($_POST['direccion'] ?? '');
  $ciudad      = trim($_POST['ciudad'] ?? '');
  $provincia   = trim($_POST['provincia'] ?? '');
  $pais        = trim($_POST['pais'] ?? '');
  $id_usuario  = (int)($_POST['id_usuario'] ?? 0);
  $codigo      = trim($_POST['codigo'] ?? '');
  $telefono    = trim($_POST['telefono'] ?? '');
  $responsable = trim($_POST['responsable'] ?? '');

  if($nombre && $direccion && $id_usuario){
   $stmt = $conn->prepare("INSERT INTO edificios (nombre, direccion, ciudad, provincia, pais, id_usuario, codigo, telefono, responsable) VALUES (?,?,?,?,?,?,?,?,?)");
   $stmt->bind_param("sssssisss", $nombre, $direccion, $ciudad, $provincia, $pais, $id_usuario, $codigo, $telefono, $responsable);
   if($stmt->execute()){
    echo '<script>alert("Edificio creado exitosamente");window.location="edificios.php";</script>';
   } else {
    echo '<script>alert("Error al crear edificio");history.back();</script>';
   }
   $stmt->close();
   exit;
  }
  echo '<script>alert("Nombre, dirección y usuario responsable son obligatorios");history.back();</script>';
  exit;
 }

 // --- EDITAR EDIFICIO ---
 if($accion === 'editar'){
  $id         = (int)($_POST['edificio_id'] ?? 0);
  $nombre     = trim($_POST['nombre'] ?? '');
  $direccion  = trim($_POST['direccion'] ?? '');
  $ciudad     = trim($_POST['ciudad'] ?? '');
  $provincia  = trim($_POST['provincia'] ?? '');
  $pais       = trim($_POST['pais'] ?? '');
  $id_usuario = (int)($_POST['id_usuario'] ?? 0);
  $codigo     = trim($_POST['codigo'] ?? '');
  $telefono   = trim($_POST['telefono'] ?? '');
  $responsable = trim($_POST['responsable'] ?? '');

  if($id && $nombre && $direccion && $id_usuario){
   $stmt = $conn->prepare("UPDATE edificios SET nombre=?, direccion=?, ciudad=?, provincia=?, pais=?, id_usuario=?, codigo=?, telefono=?, responsable=? WHERE id_edificio=?");
   $stmt->bind_param("sssssisssi", $nombre, $direccion, $ciudad, $provincia, $pais, $id_usuario, $codigo, $telefono, $responsable, $id);
   if($stmt->execute()){
    echo '<script>alert("Edificio actualizado exitosamente");window.location="edificios.php";</script>';
   } else {
    echo '<script>alert("Error al actualizar edificio");history.back();</script>';
   }
   $stmt->close();
   exit;
  }
  echo '<script>alert("Nombre, dirección y usuario responsable son obligatorios");history.back();</script>';
  exit;
 }

 // --- ELIMINAR EDIFICIO ---
 if($accion === 'eliminar'){
  $id = (int)($_POST['edificio_id'] ?? 0);
  if($id){
   $check = $conn->prepare("SELECT COUNT(*) FROM tanques WHERE id_edificio=?");
   $check->bind_param("i", $id);
   $check->execute();
   $cnt = $check->get_result()->fetch_row()[0];
   $check->close();
   if($cnt > 0){
    echo '<script>alert("No se puede eliminar: el edificio tiene tanques asociados. Elimínalos primero.");history.back();</script>';
    exit;
   }
   $stmt = $conn->prepare("DELETE FROM edificios WHERE id_edificio=?");
   $stmt->bind_param("i", $id);
   if($stmt->execute()){
    echo '<script>alert("Edificio eliminado exitosamente");window.location="edificios.php";</script>';
   } else {
    echo '<script>alert("Error al eliminar edificio");history.back();</script>';
   }
   $stmt->close();
   exit;
  }
 }
}

// === CONSULTAS PARA LA VISTA ===
$currentPage = 'edificios';
$pageSubtitle = 'Gestión de edificios';

// Usuarios disponibles para el select (todos los activos)
$resUsuarios = $conn->query("SELECT id_usuario, CONCAT(nombre,' ',apellido) AS nombre_completo FROM usuarios WHERE activo=1 ORDER BY nombre");
$listaUsuarios = [];
if($resUsuarios){ while($u = $resUsuarios->fetch_assoc()) $listaUsuarios[] = $u; }

// Edificios con conteo de tanques y nombre del usuario responsable
$resEdificios = $conn->query("
 SELECT e.*,
        CONCAT(u.nombre,' ',u.apellido) AS usuario_nombre,
        (SELECT COUNT(*) FROM tanques t WHERE t.id_edificio = e.id_edificio) AS total_tanques
 FROM edificios e
 LEFT JOIN usuarios u ON e.id_usuario = u.id_usuario
 ORDER BY e.fecha_alta DESC
");
$listaEdificios = [];
if($resEdificios){ while($ed = $resEdificios->fetch_assoc()) $listaEdificios[] = $ed; }
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>EVA - Gestión de Edificios</title>
<link rel="stylesheet" href="css/admin.css">
</head>
<body>
<?php include __DIR__ . '/includes/sidebar.php'; ?>

<div class="main">
 <?php include __DIR__ . '/includes/header.php'; ?>

 <div class="content">
  <div class="page-header">
   <h2 class="page-title">Gestión de Edificios</h2>
   <p class="page-desc">Administrar edificios y ubicaciones.</p>
  </div>

  <div class="card">
   <div class="card-header">
    <div class="filters-row">
     <div class="filter-group">
      <input type="text" class="filter-input" id="edificioSearch" placeholder="Buscar edificio...">
     </div>
     <div class="filter-group filter-group-btn">
      <button class="btn btn-primary" onclick="abrirModalEdificio()">+ Agregar Edificio</button>
     </div>
    </div>
   </div>
   <div class="table-responsive">
    <table class="table" id="tablaEdificios">
     <thead>
      <tr>
       <th>Nombre</th>
       <th>Código</th>
       <th>Dirección</th>
       <th>Ciudad</th>
       <th>Responsable</th>
       <th>Tanques</th>
       <th>Acciones</th>
      </tr>
     </thead>
     <tbody>
      <?php if(count($listaEdificios) > 0): ?>
       <?php foreach($listaEdificios as $ed): ?>
        <tr data-nombre="<?php echo htmlspecialchars(strtolower($ed['nombre'])); ?>"
            data-ciudad="<?php echo htmlspecialchars(strtolower($ed['ciudad'])); ?>">
         <td><?php echo htmlspecialchars($ed['nombre']); ?></td>
         <td><?php echo htmlspecialchars($ed['codigo'] ?: '-'); ?></td>
         <td><?php echo htmlspecialchars($ed['direccion']); ?></td>
         <td><?php echo htmlspecialchars($ed['ciudad'] ?: '-'); ?></td>
         <td><?php echo htmlspecialchars($ed['usuario_nombre'] ?? '-'); ?></td>
         <td><span class="badge <?php echo $ed['total_tanques'] > 0 ? 'activo' : 'media'; ?>"><?php echo $ed['total_tanques']; ?></span></td>
         <td class="actions-cell">
          <button class="btn-icon" title="Editar" onclick="editarEdificio(<?php echo $ed['id_edificio']; ?>)"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button>
          <button class="btn-icon btn-icon-danger" title="Eliminar" onclick="eliminarEdificio(<?php echo $ed['id_edificio']; ?>)"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg></button>
         </td>
        </tr>
       <?php endforeach; ?>
      <?php else: ?>
       <tr><td colspan="7" style="text-align:center;color:var(--tx4);padding:40px">No hay edificios registrados</td></tr>
      <?php endif; ?>
     </tbody>
    </table>
   </div>
   <div class="table-footer">
    <div class="table-info">Mostrando <span id="edificioCount"><?php echo count($listaEdificios); ?></span> edificios</div>
   </div>
  </div>
 </div>
</div>

<!-- Modal Agregar/Editar Edificio -->
<div class="modal-overlay" id="modalEdificio">
 <div class="modal">
  <div class="modal-header">
   <h3 class="modal-title" id="modalEdificioTitle">Nuevo edificio</h3>
   <button class="modal-close" onclick="cerrarModalEdificio()">&times;</button>
  </div>
  <div class="modal-body">
   <form id="formEdificio" method="POST" action="edificios.php">
    <input type="hidden" name="accion" id="edificioAccion" value="crear">
    <input type="hidden" name="edificio_id" id="edificioId" value="">
    <div class="form-row">
     <div class="form-group">
      <label class="form-label">Nombre *</label>
      <input type="text" class="form-input" name="nombre" id="edNombre" placeholder="Ej: Edificio Olivos I" required>
     </div>
     <div class="form-group">
      <label class="form-label">Código</label>
      <input type="text" class="form-input" name="codigo" id="edCodigo" placeholder="Ej: EDIF-OLV-01">
     </div>
    </div>
    <div class="form-group">
     <label class="form-label">Dirección *</label>
     <input type="text" class="form-input" name="direccion" id="edDireccion" placeholder="Ej: Av. del Libertador 2200" required>
    </div>
    <div class="form-row">
     <div class="form-group">
      <label class="form-label">Ciudad</label>
      <input type="text" class="form-input" name="ciudad" id="edCiudad" placeholder="Ej: Vicente López">
     </div>
     <div class="form-group">
      <label class="form-label">Provincia</label>
      <input type="text" class="form-input" name="provincia" id="edProvincia" placeholder="Ej: Buenos Aires">
     </div>
    </div>
    <div class="form-row">
     <div class="form-group">
      <label class="form-label">País</label>
      <input type="text" class="form-input" name="pais" id="edPais" placeholder="Ej: Argentina">
     </div>
     <div class="form-group">
      <label class="form-label">Teléfono</label>
      <input type="text" class="form-input" name="telefono" id="edTelefono" placeholder="Ej: 011-4799-8800">
     </div>
    </div>
    <div class="form-row">
     <div class="form-group">
      <label class="form-label">Responsable</label>
      <input type="text" class="form-input" name="responsable" id="edResponsable" placeholder="Ej: Roberto Gómez">
     </div>
     <div class="form-group">
      <label class="form-label">Usuario asignado *</label>
      <select class="form-input" name="id_usuario" id="edUsuario" required>
       <option value="">Seleccionar...</option>
       <?php foreach($listaUsuarios as $u): ?>
        <option value="<?php echo $u['id_usuario']; ?>"><?php echo htmlspecialchars($u['nombre_completo']); ?></option>
       <?php endforeach; ?>
      </select>
     </div>
    </div>
   </form>
  </div>
  <div class="modal-footer">
   <button class="btn btn-secondary" onclick="cerrarModalEdificio()">Cancelar</button>
   <button class="btn btn-primary" onclick="guardarEdificio()">Guardar</button>
  </div>
 </div>
</div>

<script src="js/admin.js"></script>
<script>
var edificiosData={
 <?php foreach($listaEdificios as $ed): ?>
 <?php echo $ed['id_edificio']; ?>:{nombre:"<?php echo addslashes($ed['nombre']); ?>",direccion:"<?php echo addslashes($ed['direccion']); ?>",ciudad:"<?php echo addslashes($ed['ciudad']); ?>",provincia:"<?php echo addslashes($ed['provincia']); ?>",pais:"<?php echo addslashes($ed['pais']); ?>",id_usuario:<?php echo $ed['id_usuario']; ?>,codigo:"<?php echo addslashes($ed['codigo']); ?>",telefono:"<?php echo addslashes($ed['telefono']); ?>",responsable:"<?php echo addslashes($ed['responsable']); ?>"},
 <?php endforeach; ?>
};

function abrirModalEdificio(){
 document.getElementById('modalEdificioTitle').textContent='Nuevo edificio';
 document.getElementById('edificioAccion').value='crear';
 document.getElementById('edificioId').value='';
 document.getElementById('formEdificio').reset();
 document.getElementById('modalEdificio').classList.add('active');
}
function editarEdificio(id){
 var e=edificiosData[id];if(!e)return;
 document.getElementById('modalEdificioTitle').textContent='Editar edificio';
 document.getElementById('edificioAccion').value='editar';
 document.getElementById('edificioId').value=id;
 document.getElementById('edNombre').value=e.nombre||'';
 document.getElementById('edCodigo').value=e.codigo||'';
 document.getElementById('edDireccion').value=e.direccion||'';
 document.getElementById('edCiudad').value=e.ciudad||'';
 document.getElementById('edProvincia').value=e.provincia||'';
 document.getElementById('edPais').value=e.pais||'';
 document.getElementById('edTelefono').value=e.telefono||'';
 document.getElementById('edResponsable').value=e.responsable||'';
 document.getElementById('edUsuario').value=e.id_usuario||'';
 document.getElementById('modalEdificio').classList.add('active');
}
function cerrarModalEdificio(){
 document.getElementById('modalEdificio').classList.remove('active');
}
function guardarEdificio(){
 var n=document.getElementById('edNombre').value.trim();
 var d=document.getElementById('edDireccion').value.trim();
 var u=document.getElementById('edUsuario').value;
 if(!n){alert('El nombre es obligatorio');return;}
 if(!d){alert('La dirección es obligatoria');return;}
 if(!u){alert('Debe asignar un usuario responsable');return;}
 document.getElementById('formEdificio').submit();
}
function eliminarEdificio(id){
 if(confirm('¿Eliminar este edificio? Solo se puede si no tiene tanques asociados.')){
  var f=document.createElement('form');f.method='POST';f.action='edificios.php';
  f.innerHTML='<input type="hidden" name="accion" value="eliminar"><input type="hidden" name="edificio_id" value="'+id+'">';
  document.body.appendChild(f);f.submit();
 }
}
(function(){
 var s=document.getElementById('edificioSearch');
 if(!s)return;
 function filtrar(){
  var t=s.value.toLowerCase(),c=0;
  document.querySelectorAll('#tablaEdificios tbody tr').forEach(function(f){
   var vis=!t||((f.dataset.nombre||'').includes(t)||(f.dataset.ciudad||'').includes(t));
   f.style.display=vis?'':'none';if(vis)c++;
  });
  document.getElementById('edificioCount').textContent=c;
 }
 s.addEventListener('input',filtrar);
})();
</script>
</body>
</html>
