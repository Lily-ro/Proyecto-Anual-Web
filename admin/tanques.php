<?php
session_start();
if(!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'ADMIN'){
    header("Location: ../index.php");
    exit;
}
require_once(__DIR__ . '/../config/db.php');
$currentPage = 'tanques';
$pageSubtitle = 'Gestión completa de tanques';

$filtroBusqueda  = trim($_GET['busqueda'] ?? '');
$filtroUbicacion = trim($_GET['ubicacion'] ?? '');
$filtroEstado    = $_GET['estado'] ?? '';

$resEdificios = $conn->query("SELECT id_edificio, nombre FROM edificios ORDER BY nombre ASC");

$where  = [];
$types  = '';
$params = [];

if($filtroBusqueda !== ''){
    $where[] = "(t.nombre LIKE ? OR t.descripcion LIKE ? OR t.tipo LIKE ?)";
    $like = "%{$filtroBusqueda}%";
    $params[] = $like; $types .= 's';
    $params[] = $like; $types .= 's';
    $params[] = $like; $types .= 's';
}
if($filtroUbicacion !== ''){
    $where[] = "t.ubicacion LIKE ?";
    $params[] = "%{$filtroUbicacion}%"; $types .= 's';
}
if($filtroEstado === 'activo'){
    $where[] = "t.activo = 1";
} elseif($filtroEstado === 'inactivo'){
    $where[] = "t.activo = 0";
}

$whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$sql = "SELECT t.id_tanque, t.nombre, t.capacidad_litros, t.altura_cm,
               t.ubicacion, t.descripcion, t.fecha_instalacion, t.activo,
               t.tipo, t.material, t.diametro, t.volumen_util,
               e.nombre AS edificio,
               (SELECT COUNT(*) FROM dispositivos d WHERE d.id_tanque = t.id_tanque) AS cnt_dispositivos,
               (SELECT m.porcentaje FROM mediciones m
                JOIN sensores s ON m.id_sensor = s.id_sensor
                JOIN dispositivos d2 ON s.id_dispositivo = d2.id_dispositivo
                WHERE d2.id_tanque = t.id_tanque
                ORDER BY m.fecha_hora DESC LIMIT 1) AS nivel_actual
        FROM tanques t
        JOIN edificios e ON t.id_edificio = e.id_edificio
        {$whereSQL}
        ORDER BY t.nombre ASC";

$stmt = $conn->prepare($sql);
if($params){
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$resTanques = $stmt->get_result();

function badgeTanqueActivo($activo){
    return $activo
        ? '<span class="badge activo">Activo</span>'
        : '<span class="badge inactivo">Inactivo</span>';
}

function nivelBar($nivel){
    $pct = max(0, min(100, (int)($nivel ?? 0)));
    if($pct >= 60) $cls = '';
    elseif($pct >= 30) $cls = ' level-medio';
    else $cls = ' level-bajo';
    return '<div class="level-bar"><div class="level-fill'.$cls.'" style="width:'.$pct.'%"></div></div><span class="level-text">'.$pct.'%</span>';
}
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
  <div class="page-header">
   <h2 class="page-title">Gestión de Tanques</h2>
   <p class="page-desc">ABM completo de tanques del sistema.</p>
  </div>

  <div class="card">
   <div class="card-header">
    <form method="GET" class="filters-row">
     <div class="filter-group">
      <input type="text" name="busqueda" class="filter-input" placeholder="Buscar tanque..." value="<?php echo htmlspecialchars($filtroBusqueda); ?>">
     </div>
     <div class="filter-group">
      <input type="text" name="ubicacion" class="filter-input" placeholder="Filtrar por ubicación..." value="<?php echo htmlspecialchars($filtroUbicacion); ?>">
     </div>
     <div class="filter-group">
      <select name="estado" class="filter-input">
       <option value="">Todos los estados</option>
       <option value="activo"<?php echo $filtroEstado==='activo'?' selected':''; ?>>Activo</option>
       <option value="inactivo"<?php echo $filtroEstado==='inactivo'?' selected':''; ?>>Inactivo</option>
      </select>
     </div>
     <div class="filter-group">
      <button type="submit" class="btn btn-primary">Filtrar</button>
     </div>
    </form>
   </div>
   <div class="table-responsive">
    <table class="table">
     <thead>
      <tr>
       <th>ID</th>
       <th>Nombre</th>
       <th>Edificio</th>
       <th>Ubicación</th>
       <th>Capacidad (L)</th>
       <th>Nivel actual</th>
       <th>Estado</th>
       <th>Dispositivos</th>
       <th>Acciones</th>
      </tr>
     </thead>
     <tbody>
      <?php if($resTanques && $resTanques->num_rows > 0): ?>
       <?php while($t = $resTanques->fetch_assoc()): ?>
        <tr>
         <td>T-<?php echo str_pad($t['id_tanque'], 3, '0', STR_PAD_LEFT); ?></td>
         <td><?php echo htmlspecialchars($t['nombre']); ?></td>
         <td><?php echo htmlspecialchars($t['edificio']); ?></td>
         <td><?php echo htmlspecialchars($t['ubicacion'] ?? '-'); ?></td>
         <td><?php echo number_format($t['capacidad_litros'], 0, ',', '.'); ?></td>
         <td><?php echo nivelBar($t['nivel_actual']); ?></td>
         <td><?php echo badgeTanqueActivo($t['activo']); ?></td>
         <td><?php echo (int)$t['cnt_dispositivos']; ?></td>
         <td class="actions-cell">
          <button class="btn-icon" title="Ver detalles"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button>
          <button class="btn-icon" title="Editar" onclick="editarTanque(<?php echo (int)$t['id_tanque']; ?>)"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button>
          <button class="btn-icon btn-icon-danger" title="Eliminar" onclick="eliminarTanque(<?php echo (int)$t['id_tanque']; ?>)"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg></button>
         </td>
        </tr>
       <?php endwhile; ?>
      <?php else: ?>
       <tr><td colspan="9" style="text-align:center;color:var(--tx4)">No hay tanques registrados</td></tr>
      <?php endif; ?>
     </tbody>
    </table>
   </div>
  </div>
 </div>
</div>

<div class="modal-overlay" id="modalTanque">
 <div class="modal">
  <div class="modal-header">
   <h3 class="modal-title" id="modalTanqueTitle">Agregar Tanque</h3>
   <button class="modal-close" onclick="cerrarModalTanque()">&times;</button>
  </div>
  <div class="modal-body">
   <form id="formTanque" method="POST" action="tanques.php">
    <input type="hidden" name="accion" id="tanqueAccion" value="crear">
    <input type="hidden" name="tanque_id" id="tanqueId" value="">
    <div class="form-row">
     <div class="form-group">
      <label class="form-label">Nombre *</label>
      <input type="text" name="nombre" class="form-input" id="tanqueNombre" placeholder="Ej: Tanque Norte Principal" required>
     </div>
     <div class="form-group">
      <label class="form-label">Edificio *</label>
      <select name="id_edificio" class="form-input" id="tanqueEdificio" required>
       <option value="">Seleccionar...</option>
       <?php if($resEdificios): while($e = $resEdificios->fetch_assoc()): ?>
        <option value="<?php echo (int)$e['id_edificio']; ?>"><?php echo htmlspecialchars($e['nombre']); ?></option>
       <?php endwhile; endif; ?>
      </select>
     </div>
    </div>
    <div class="form-row">
     <div class="form-group">
      <label class="form-label">Capacidad (litros) *</label>
      <input type="number" name="capacidad_litros" class="form-input" id="tanqueCapacidad" placeholder="Ej: 5000" step="0.01" required>
     </div>
     <div class="form-group">
      <label class="form-label">Altura (cm) *</label>
      <input type="number" name="altura_cm" class="form-input" id="tanqueAltura" placeholder="Ej: 200" step="0.01" required>
     </div>
    </div>
    <div class="form-row">
     <div class="form-group">
      <label class="form-label">Ubicación</label>
      <input type="text" name="ubicacion" class="form-input" id="tanqueUbicacion" placeholder="Ej: Terraza / Azotea">
     </div>
     <div class="form-group">
      <label class="form-label">Tipo</label>
      <input type="text" name="tipo" class="form-input" id="tanqueTipo" placeholder="Ej: Elevado">
     </div>
    </div>
    <div class="form-row">
     <div class="form-group">
      <label class="form-label">Material</label>
      <input type="text" name="material" class="form-input" id="tanqueMaterial" placeholder="Ej: Hormigón Armado">
     </div>
     <div class="form-group">
      <label class="form-label">Diámetro (cm)</label>
      <input type="number" name="diametro" class="form-input" id="tanqueDiametro" step="0.01">
     </div>
    </div>
    <div class="form-row">
     <div class="form-group">
      <label class="form-label">Volumen útil (L)</label>
      <input type="number" name="volumen_util" class="form-input" id="tanqueVolumenUtil" step="0.01">
     </div>
     <div class="form-group">
      <label class="form-label">Fecha instalación</label>
      <input type="date" name="fecha_instalacion" class="form-input" id="tanqueFechaInst">
     </div>
    </div>
    <div class="form-group">
     <label class="form-label">Descripción</label>
     <textarea name="descripcion" class="form-input" id="tanqueDescripcion" rows="3" placeholder="Descripción del tanque..."></textarea>
    </div>
    <div class="form-group">
     <label class="form-label">
      <input type="checkbox" name="activo" id="tanqueActivo" value="1" checked> Activo
     </label>
    </div>
   </form>
  </div>
  <div class="modal-footer">
   <button class="btn btn-secondary" onclick="cerrarModalTanque()">Cancelar</button>
   <button class="btn btn-primary" onclick="document.getElementById('formTanque').submit()">Guardar</button>
  </div>
 </div>
</div>

<script src="js/admin.js"></script>
</body>
</html>
