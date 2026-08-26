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

 // --- CREAR SENSOR ---
 if($accion === 'crear'){
  $modelo           = trim($_POST['modelo'] ?? '');
  $numero_serie     = trim($_POST['numero_serie'] ?? '');
  $fecha_instalacion = $_POST['fecha_instalacion'] ?? '';
  $estado           = $_POST['estado'] ?? 'ACTIVO';
  $id_dispositivo   = (int)($_POST['id_dispositivo'] ?? 0);
  $fabricante       = trim($_POST['fabricante'] ?? '');
  $precision_sensor = $_POST['precision_sensor'] ?? '';
  $rango_min        = $_POST['rango_min'] ?? '';
  $rango_max        = $_POST['rango_max'] ?? '';
  $fecha_calibracion = $_POST['fecha_calibracion'] ?? '';
  $calibrado        = isset($_POST['calibrado']) ? 1 : 0;

  if($modelo && $id_dispositivo){
   if($numero_serie){
    $check = $conn->prepare("SELECT id_sensor FROM sensores WHERE numero_serie=? LIMIT 1");
    $check->bind_param("s", $numero_serie);
    $check->execute();
    if($check->get_result()->num_rows > 0){
     echo '<script>alert("Ya existe un sensor con ese número de serie");history.back();</script>';
     $check->close();
     exit;
    }
    $check->close();
   }
   $stmt = $conn->prepare("INSERT INTO sensores (modelo, numero_serie, fecha_instalacion, estado, id_dispositivo, fabricante, precision_sensor, rango_min, rango_max, fecha_calibracion, calibrado) VALUES (?,?,?,?,?,?,?,?,?,?,?)");
   $stmt->bind_param("ssssisdddsi", $modelo, $numero_serie, $fecha_instalacion, $estado, $id_dispositivo, $fabricante, $precision_sensor, $rango_min, $rango_max, $fecha_calibracion, $calibrado);
   if($stmt->execute()){
    echo '<script>alert("Sensor creado exitosamente");window.location="sensores.php";</script>';
   } else {
    echo '<script>alert("Error al crear sensor");history.back();</script>';
   }
   $stmt->close();
   exit;
  }
  echo '<script>alert("Modelo y dispositivo son obligatorios");history.back();</script>';
  exit;
 }

 // --- EDITAR SENSOR ---
 if($accion === 'editar'){
  $id               = (int)($_POST['sensor_id'] ?? 0);
  $modelo           = trim($_POST['modelo'] ?? '');
  $numero_serie     = trim($_POST['numero_serie'] ?? '');
  $fecha_instalacion = $_POST['fecha_instalacion'] ?? '';
  $estado           = $_POST['estado'] ?? 'ACTIVO';
  $id_dispositivo   = (int)($_POST['id_dispositivo'] ?? 0);
  $fabricante       = trim($_POST['fabricante'] ?? '');
  $precision_sensor = $_POST['precision_sensor'] ?? '';
  $rango_min        = $_POST['rango_min'] ?? '';
  $rango_max        = $_POST['rango_max'] ?? '';
  $fecha_calibracion = $_POST['fecha_calibracion'] ?? '';
  $calibrado        = isset($_POST['calibrado']) ? 1 : 0;

  if($id && $modelo && $id_dispositivo){
   if($numero_serie){
    $check = $conn->prepare("SELECT id_sensor FROM sensores WHERE numero_serie=? AND id_sensor!=? LIMIT 1");
    $check->bind_param("si", $numero_serie, $id);
    $check->execute();
    if($check->get_result()->num_rows > 0){
     echo '<script>alert("Ya existe otro sensor con ese número de serie");history.back();</script>';
     $check->close();
     exit;
    }
    $check->close();
   }
   $stmt = $conn->prepare("UPDATE sensores SET modelo=?, numero_serie=?, fecha_instalacion=?, estado=?, id_dispositivo=?, fabricante=?, precision_sensor=?, rango_min=?, rango_max=?, fecha_calibracion=?, calibrado=? WHERE id_sensor=?");
   $stmt->bind_param("ssssisdddssii", $modelo, $numero_serie, $fecha_instalacion, $estado, $id_dispositivo, $fabricante, $precision_sensor, $rango_min, $rango_max, $fecha_calibracion, $calibrado, $id);
   if($stmt->execute()){
    echo '<script>alert("Sensor actualizado exitosamente");window.location="sensores.php";</script>';
   } else {
    echo '<script>alert("Error al actualizar sensor");history.back();</script>';
   }
   $stmt->close();
   exit;
  }
  echo '<script>alert("Modelo y dispositivo son obligatorios");history.back();</script>';
  exit;
 }

 // --- ELIMINAR SENSOR ---
 if($accion === 'eliminar'){
  $id = (int)($_POST['sensor_id'] ?? 0);
  if($id){
   $check = $conn->prepare("SELECT COUNT(*) FROM mediciones WHERE id_sensor=?");
   $check->bind_param("i", $id);
   $check->execute();
   $cnt = $check->get_result()->fetch_row()[0];
   $check->close();
   if($cnt > 0){
    echo '<script>alert("No se puede eliminar: el sensor tiene mediciones registradas.");history.back();</script>';
    exit;
   }
   $stmt = $conn->prepare("DELETE FROM sensores WHERE id_sensor=?");
   $stmt->bind_param("i", $id);
   if($stmt->execute()){
    echo '<script>alert("Sensor eliminado exitosamente");window.location="sensores.php";</script>';
   } else {
    echo '<script>alert("Error al eliminar sensor");history.back();</script>';
   }
   $stmt->close();
   exit;
  }
 }
}

// === CONSULTAS PARA LA VISTA ===
$currentPage = 'sensores';
$pageSubtitle = 'Gestión de sensores';

// Dispositivos disponibles para el select
$resDisp = $conn->query("SELECT d.id_dispositivo, d.nombre, t.nombre AS tanque_nombre FROM dispositivos d JOIN tanques t ON d.id_tanque=t.id_tanque ORDER BY d.nombre");
$listaDispositivos = [];
if($resDisp){ while($d = $resDisp->fetch_assoc()) $listaDispositivos[] = $d; }

// Sensores con datos del dispositivo y tanque
$resSensores = $conn->query("
 SELECT s.*,
        d.nombre AS dispositivo_nombre,
        t.nombre AS tanque_nombre,
        (SELECT m.porcentaje FROM mediciones m WHERE m.id_sensor=s.id_sensor ORDER BY m.fecha_hora DESC LIMIT 1) AS ultima_lectura
 FROM sensores s
 JOIN dispositivos d ON s.id_dispositivo = d.id_dispositivo
 JOIN tanques t ON d.id_tanque = t.id_tanque
 ORDER BY s.fecha_instalacion DESC
");
$listaSensores = [];
if($resSensores){ while($s = $resSensores->fetch_assoc()) $listaSensores[] = $s; }

function sensorBadge($estado){
 $map = ['ACTIVO'=>'activo','INACTIVO'=>'inactivo','FALLA'=>'alta'];
 $cls = $map[$estado] ?? 'inactivo';
 return '<span class="badge '.$cls.'">'.htmlspecialchars($estado).'</span>';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>EVA - Gestión de Sensores</title>
<link rel="stylesheet" href="css/admin.css">
</head>
<body>
<?php include __DIR__ . '/includes/sidebar.php'; ?>

<div class="main">
 <?php include __DIR__ . '/includes/header.php'; ?>

 <div class="content">
  <div class="page-header">
   <h2 class="page-title">Gestión de Sensores</h2>
   <p class="page-desc">Administrar sensores instalados en dispositivos.</p>
  </div>

  <div class="card">
   <div class="card-header">
    <div class="filters-row">
     <div class="filter-group">
      <input type="text" class="filter-input" id="sensorSearch" placeholder="Buscar sensor...">
     </div>
     <div class="filter-group">
      <select class="filter-input" id="filterEstado">
       <option value="">Todos los estados</option>
       <option value="ACTIVO">Activo</option>
       <option value="INACTIVO">Inactivo</option>
       <option value="FALLA">Falla</option>
      </select>
     </div>
     <div class="filter-group filter-group-btn">
      <button class="btn btn-primary" onclick="abrirModalSensor()">+ Agregar Sensor</button>
     </div>
    </div>
   </div>
   <div class="table-responsive">
    <table class="table" id="tablaSensores">
     <thead>
      <tr>
       <th>Modelo</th>
       <th>N° Serie</th>
       <th>Fabricante</th>
       <th>Dispositivo</th>
       <th>Tanque</th>
       <th>Estado</th>
       <th>Calibrado</th>
       <th>Acciones</th>
      </tr>
     </thead>
     <tbody>
      <?php if(count($listaSensores) > 0): ?>
       <?php foreach($listaSensores as $s): ?>
        <tr data-estado="<?php echo $s['estado']; ?>"
            data-modelo="<?php echo htmlspecialchars(strtolower($s['modelo'])); ?>"
            data-serie="<?php echo htmlspecialchars(strtolower($s['numero_serie'])); ?>">
         <td><?php echo htmlspecialchars($s['modelo']); ?></td>
         <td><?php echo htmlspecialchars($s['numero_serie'] ?: '-'); ?></td>
         <td><?php echo htmlspecialchars($s['fabricante'] ?: '-'); ?></td>
         <td><?php echo htmlspecialchars($s['dispositivo_nombre']); ?></td>
         <td><?php echo htmlspecialchars($s['tanque_nombre']); ?></td>
         <td><?php echo sensorBadge($s['estado']); ?></td>
         <td><?php echo $s['calibrado'] ? '<span class="badge activo">Si</span>' : '<span class="badge inactivo">No</span>'; ?></td>
         <td class="actions-cell">
          <button class="btn-icon" title="Editar" onclick="editarSensor(<?php echo $s['id_sensor']; ?>)"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button>
          <button class="btn-icon btn-icon-danger" title="Eliminar" onclick="eliminarSensor(<?php echo $s['id_sensor']; ?>)"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg></button>
         </td>
        </tr>
       <?php endforeach; ?>
      <?php else: ?>
       <tr><td colspan="8" style="text-align:center;color:var(--tx4);padding:40px">No hay sensores registrados</td></tr>
      <?php endif; ?>
     </tbody>
    </table>
   </div>
   <div class="table-footer">
    <div class="table-info">Mostrando <span id="sensorCount"><?php echo count($listaSensores); ?></span> sensores</div>
   </div>
  </div>
 </div>
</div>

<!-- Modal Agregar/Editar Sensor -->
<div class="modal-overlay" id="modalSensor">
 <div class="modal">
  <div class="modal-header">
   <h3 class="modal-title" id="modalSensorTitle">Nuevo sensor</h3>
   <button class="modal-close" onclick="cerrarModalSensor()">&times;</button>
  </div>
  <div class="modal-body">
   <form id="formSensor" method="POST" action="sensores.php">
    <input type="hidden" name="accion" id="sensorAccion" value="crear">
    <input type="hidden" name="sensor_id" id="sensorId" value="">
    <div class="form-row">
     <div class="form-group">
      <label class="form-label">Modelo *</label>
      <input type="text" class="form-input" name="modelo" id="senModelo" placeholder="Ej: JSR-SR04T" required>
     </div>
     <div class="form-group">
      <label class="form-label">N° Serie</label>
      <input type="text" class="form-input" name="numero_serie" id="senSerie" placeholder="Ej: SN-HC04T-202601">
     </div>
    </div>
    <div class="form-row">
     <div class="form-group">
      <label class="form-label">Fabricante</label>
      <input type="text" class="form-input" name="fabricante" id="senFabricante" placeholder="Ej: Ultrasonic Tech">
     </div>
     <div class="form-group">
      <label class="form-label">Dispositivo *</label>
      <select class="form-input" name="id_dispositivo" id="senDispositivo" required>
       <option value="">Seleccionar...</option>
       <?php foreach($listaDispositivos as $d): ?>
        <option value="<?php echo $d['id_dispositivo']; ?>"><?php echo htmlspecialchars($d['nombre'].' → '.$d['tanque_nombre']); ?></option>
       <?php endforeach; ?>
      </select>
     </div>
    </div>
    <div class="form-row">
     <div class="form-group">
      <label class="form-label">Fecha instalación</label>
      <input type="date" class="form-input" name="fecha_instalacion" id="senFechaInst">
     </div>
     <div class="form-group">
      <label class="form-label">Estado</label>
      <select class="form-input" name="estado" id="senEstado">
       <option value="ACTIVO">Activo</option>
       <option value="INACTIVO">Inactivo</option>
       <option value="FALLA">Falla</option>
      </select>
     </div>
    </div>
    <div class="form-row">
     <div class="form-group">
      <label class="form-label">Precisión (±)</label>
      <input type="number" step="0.01" class="form-input" name="precision_sensor" id="senPrecision" placeholder="Ej: 0.50">
     </div>
     <div class="form-group">
      <label class="form-label">Calibrado</label>
      <select class="form-input" name="calibrado" id="senCalibrado">
       <option value="1">Sí</option>
       <option value="0">No</option>
      </select>
     </div>
    </div>
    <div class="form-row">
     <div class="form-group">
      <label class="form-label">Rango mínimo (cm)</label>
      <input type="number" step="0.01" class="form-input" name="rango_min" id="senRangoMin" placeholder="Ej: 20">
     </div>
     <div class="form-group">
      <label class="form-label">Rango máximo (cm)</label>
      <input type="number" step="0.01" class="form-input" name="rango_max" id="senRangoMax" placeholder="Ej: 450">
     </div>
    </div>
    <div class="form-group">
     <label class="form-label">Fecha calibración</label>
     <input type="date" class="form-input" name="fecha_calibracion" id="senFechaCal">
    </div>
   </form>
  </div>
  <div class="modal-footer">
   <button class="btn btn-secondary" onclick="cerrarModalSensor()">Cancelar</button>
   <button class="btn btn-primary" onclick="guardarSensor()">Guardar</button>
  </div>
 </div>
</div>

<script src="js/admin.js"></script>
<script>
var sensoresData={
 <?php foreach($listaSensores as $s): ?>
 <?php echo $s['id_sensor']; ?>:{modelo:"<?php echo addslashes($s['modelo']); ?>",numero_serie:"<?php echo addslashes($s['numero_serie']); ?>",fabricante:"<?php echo addslashes($s['fabricante']); ?>",id_dispositivo:<?php echo $s['id_dispositivo']; ?>,fecha_instalacion:"<?php echo $s['fecha_instalacion']; ?>",estado:"<?php echo $s['estado']; ?>",precision_sensor:"<?php echo $s['precision_sensor']; ?>",rango_min:"<?php echo $s['rango_min']; ?>",rango_max:"<?php echo $s['rango_max']; ?>",fecha_calibracion:"<?php echo $s['fecha_calibracion']; ?>",calibrado:<?php echo $s['calibrado']; ?>},
 <?php endforeach; ?>
};

function abrirModalSensor(){
 document.getElementById('modalSensorTitle').textContent='Nuevo sensor';
 document.getElementById('sensorAccion').value='crear';
 document.getElementById('sensorId').value='';
 document.getElementById('formSensor').reset();
 document.getElementById('senEstado').value='ACTIVO';
 document.getElementById('senCalibrado').value='1';
 document.getElementById('modalSensor').classList.add('active');
}
function editarSensor(id){
 var s=sensoresData[id];if(!s)return;
 document.getElementById('modalSensorTitle').textContent='Editar sensor';
 document.getElementById('sensorAccion').value='editar';
 document.getElementById('sensorId').value=id;
 document.getElementById('senModelo').value=s.modelo||'';
 document.getElementById('senSerie').value=s.numero_serie||'';
 document.getElementById('senFabricante').value=s.fabricante||'';
 document.getElementById('senDispositivo').value=s.id_dispositivo||'';
 document.getElementById('senFechaInst').value=s.fecha_instalacion||'';
 document.getElementById('senEstado').value=s.estado;
 document.getElementById('senPrecision').value=s.precision_sensor||'';
 document.getElementById('senRangoMin').value=s.rango_min||'';
 document.getElementById('senRangoMax').value=s.rango_max||'';
 document.getElementById('senFechaCal').value=s.fecha_calibracion||'';
 document.getElementById('senCalibrado').value=s.calibrado;
 document.getElementById('modalSensor').classList.add('active');
}
function cerrarModalSensor(){
 document.getElementById('modalSensor').classList.remove('active');
}
function guardarSensor(){
 var m=document.getElementById('senModelo').value.trim();
 var d=document.getElementById('senDispositivo').value;
 if(!m){alert('El modelo es obligatorio');return;}
 if(!d){alert('Debe seleccionar un dispositivo');return;}
 document.getElementById('formSensor').submit();
}
function eliminarSensor(id){
 if(confirm('¿Eliminar este sensor? Solo se puede si no tiene mediciones registradas.')){
  var f=document.createElement('form');f.method='POST';f.action='sensores.php';
  f.innerHTML='<input type="hidden" name="accion" value="eliminar"><input type="hidden" name="sensor_id" value="'+id+'">';
  document.body.appendChild(f);f.submit();
 }
}
(function(){
 var s=document.getElementById('sensorSearch'),e=document.getElementById('filterEstado');
 if(!s)return;
 function filtrar(){
  var t=s.value.toLowerCase(),ev=e.value,c=0;
  document.querySelectorAll('#tablaSensores tbody tr').forEach(function(f){
   var vis=(!t||((f.dataset.modelo||'').includes(t)||(f.dataset.serie||'').includes(t)))&&(!ev||f.dataset.estado===ev);
   f.style.display=vis?'':'none';if(vis)c++;
  });
  document.getElementById('sensorCount').textContent=c;
 }
 s.addEventListener('input',filtrar);e.addEventListener('change',filtrar);
})();
</script>
</body>
</html>
