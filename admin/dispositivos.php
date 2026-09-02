<?php
session_start();
if(!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'ADMIN'){
    header("Location: ../index.php");
    exit;
}
require_once(__DIR__ . '/../config/db.php');
$currentPage = 'dispositivos';
$pageSubtitle = 'Gestión de dispositivos EVA y firmware';

$filtroBusqueda = trim($_GET['busqueda'] ?? '');
$filtroEstado   = $_GET['estado'] ?? '';
$filtroTanque   = $_GET['tanque'] ?? '';
$filtroFwBusqueda = trim($_GET['fw_busqueda'] ?? '');

$estadosValidos = ['ONLINE','OFFLINE','MANTENIMIENTO'];

function countDevByEstado($conn, $estado){
    $stmt = $conn->prepare("SELECT COUNT(*) FROM dispositivos WHERE estado = ?");
    $stmt->bind_param("s", $estado);
    $stmt->execute();
    return (int)$stmt->get_result()->fetch_row()[0];
}
$cntOnline      = countDevByEstado($conn, 'ONLINE');
$cntOffline     = countDevByEstado($conn, 'OFFLINE');
$cntMantenimiento = countDevByEstado($conn, 'MANTENIMIENTO');
$cntTotalDev    = $cntOnline + $cntOffline + $cntMantenimiento;

$resTanques = $conn->query("SELECT id_tanque, nombre FROM tanques ORDER BY nombre ASC");

$where  = [];
$types  = '';
$params = [];

if($filtroBusqueda !== ''){
    $where[] = "(d.nombre LIKE ? OR d.mac_address LIKE ? OR d.ip_local LIKE ? OR t.nombre LIKE ?)";
    $like = "%{$filtroBusqueda}%";
    $params[] = $like; $types .= 's';
    $params[] = $like; $types .= 's';
    $params[] = $like; $types .= 's';
    $params[] = $like; $types .= 's';
}
if($filtroEstado !== '' && in_array($filtroEstado, $estadosValidos)){
    $where[] = "d.estado = ?";
    $params[] = $filtroEstado; $types .= 's';
}
if($filtroTanque !== '' && is_numeric($filtroTanque)){
    $where[] = "d.id_tanque = ?";
    $params[] = (int)$filtroTanque; $types .= 'i';
}

$whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$sql = "SELECT d.id_dispositivo, d.nombre, d.mac_address, d.ip_local,
               d.estado, d.bateria, d.intensidad_senal, d.firmware,
               d.fecha_instalacion, d.ultima_conexion,
               t.nombre AS tanque
        FROM dispositivos d
        JOIN tanques t ON d.id_tanque = t.id_tanque
        {$whereSQL}
        ORDER BY d.nombre ASC";

$stmt = $conn->prepare($sql);
if($params){
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$resDispositivos = $stmt->get_result();

$sqlFw = "SELECT d.id_dispositivo, d.nombre, d.firmware, d.ultima_actualizacion,
                 f.version, f.descripcion, f.fecha_publicacion
          FROM dispositivos d
          JOIN firmware f ON d.id_firmware = f.id_firmware";
$paramsFw = [];
$typesFw = '';

if($filtroFwBusqueda !== ''){
    $sqlFw .= " WHERE (d.nombre LIKE ? OR f.version LIKE ?)";
    $like = "%{$filtroFwBusqueda}%";
    $paramsFw[] = $like; $typesFw .= 's';
    $paramsFw[] = $like; $typesFw .= 's';
}
$sqlFw .= " ORDER BY d.nombre ASC";

$stmtFw = $conn->prepare($sqlFw);
if($paramsFw){
    $stmtFw->bind_param($typesFw, ...$paramsFw);
}
$stmtFw->execute();
$resFirmware = $stmtFw->get_result();

$resUltimaFw = $conn->query("SELECT version FROM firmware ORDER BY fecha_publicacion DESC LIMIT 1");
$ultimaVersion = $resUltimaFw ? ($resUltimaFw->fetch_row()[0] ?? '') : '';

function badgeEstadoDev($estado){
    $map = [
        'ONLINE'        => ['cls'=>'activo',      'txt'=>'Online'],
        'OFFLINE'       => ['cls'=>'inactivo',     'txt'=>'Offline'],
        'MANTENIMIENTO' => ['cls'=>'advertencia',  'txt'=>'Mantenimiento'],
    ];
    $info = $map[$estado] ?? ['cls'=>'inactivo', 'txt'=>$estado];
    return '<span class="badge '.$info['cls'].'">'.htmlspecialchars($info['txt']).'</span>';
}

function badgeBateria($b){
    $b = (float)$b;
    if($b >= 60) $cls = 'activo';
    elseif($b >= 30) $cls = 'media';
    else $cls = 'baja';
    return '<span class="badge '.$cls.'">'.round($b).'%</span>';
}

function badgeSenal($s){
    $s = (int)$s;
    if($s >= -60){ $txt = 'Fuerte'; $cls = 'activo'; }
    elseif($s >= -80){ $txt = 'Media'; $cls = 'media'; }
    else{ $txt = 'Débil'; $cls = 'baja'; }
    return '<span class="badge '.$cls.'">'.$txt.'</span>';
}

function badgeFirmwareStatus($version, $ultima){
    if($version === $ultima){
        return '<span class="badge activo">Actualizado</span>';
    }
    return '<span class="badge advertencia">Desactualizado</span>';
}
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
  <div class="page-header">
   <h2 class="page-title">Gestión de Dispositivos</h2>
   <p class="page-desc">Administrar dispositivos EVA y control de firmware.</p>
  </div>

  <div class="tabs-container">
   <div class="tabs">
    <button class="tab active" data-tab="lista">Lista de Dispositivos</button>
    <button class="tab" data-tab="firmware">Firmware</button>
   </div>

   <div class="tab-content active" id="tab-lista">
    <div class="card">
     <div class="card-header">
      <form method="GET" class="filters-row">
       <input type="hidden" name="tab" value="lista">
       <div class="filter-group">
        <input type="text" name="busqueda" class="filter-input" placeholder="Buscar dispositivo..." value="<?php echo htmlspecialchars($filtroBusqueda); ?>">
       </div>
       <div class="filter-group">
        <select name="estado" class="filter-input">
         <option value="">Todos los estados</option>
         <option value="ONLINE"<?php echo $filtroEstado==='ONLINE'?' selected':''; ?>>Online</option>
         <option value="OFFLINE"<?php echo $filtroEstado==='OFFLINE'?' selected':''; ?>>Offline</option>
         <option value="MANTENIMIENTO"<?php echo $filtroEstado==='MANTENIMIENTO'?' selected':''; ?>>Mantenimiento</option>
        </select>
       </div>
       <div class="filter-group">
        <select name="tanque" class="filter-input">
         <option value="">Todos los tanques</option>
         <?php if($resTanques): while($t = $resTanques->fetch_assoc()): ?>
          <option value="<?php echo (int)$t['id_tanque']; ?>"<?php echo $filtroTanque==(string)$t['id_tanque']?' selected':''; ?>><?php echo htmlspecialchars($t['nombre']); ?></option>
         <?php endwhile; endif; ?>
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
         <th>Tanque</th>
         <th>Estado</th>
         <th>Batería</th>
         <th>Señal</th>
         <th>Firmware</th>
         <th>Última conexión</th>
         <th>Acciones</th>
        </tr>
       </thead>
       <tbody>
        <?php if($resDispositivos && $resDispositivos->num_rows > 0): ?>
         <?php while($d = $resDispositivos->fetch_assoc()): ?>
          <tr>
           <td>D-<?php echo str_pad($d['id_dispositivo'], 3, '0', STR_PAD_LEFT); ?></td>
           <td><?php echo htmlspecialchars($d['nombre']); ?></td>
           <td><?php echo htmlspecialchars($d['tanque']); ?></td>
           <td><?php echo badgeEstadoDev($d['estado']); ?></td>
           <td><?php echo badgeBateria($d['bateria']); ?></td>
           <td><?php echo badgeSenal($d['intensidad_senal']); ?></td>
           <td><?php echo htmlspecialchars($d['firmware'] ?? '-'); ?></td>
           <td><?php echo $d['ultima_conexion'] ? date('d/m/Y H:i', strtotime($d['ultima_conexion'])) : 'Nunca'; ?></td>
           <td class="actions-cell">
            <button class="btn-icon" title="Ver"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button>
            <button class="btn-icon" title="Editar"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button>
            <button class="btn-icon btn-icon-danger" title="Eliminar"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg></button>
           </td>
          </tr>
         <?php endwhile; ?>
        <?php else: ?>
         <tr><td colspan="9" style="text-align:center;color:var(--tx4)">No hay dispositivos registrados</td></tr>
        <?php endif; ?>
       </tbody>
      </table>
     </div>
    </div>
   </div>

   <div class="tab-content" id="tab-firmware">
    <div class="card">
     <div class="card-header">
      <div class="card-title">Control de Firmware</div>
      <div class="firmware-note">
       <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
       <span>Solo visualización. La actualización de firmware no está habilitada para este rol.</span>
      </div>
      <form method="GET" class="filters-row" style="margin-left:auto">
       <input type="hidden" name="tab" value="firmware">
       <div class="filter-group">
        <input type="text" name="fw_busqueda" class="filter-input" placeholder="Buscar..." value="<?php echo htmlspecialchars($filtroFwBusqueda); ?>">
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
         <th>Dispositivo</th>
         <th>Versión actual</th>
         <th>Descripción firmware</th>
         <th>Fecha publicación</th>
         <th>Estado</th>
         <th>Acciones</th>
        </tr>
       </thead>
       <tbody>
        <?php if($resFirmware && $resFirmware->num_rows > 0): ?>
         <?php while($fw = $resFirmware->fetch_assoc()): ?>
          <tr>
           <td><?php echo htmlspecialchars($fw['nombre']); ?></td>
           <td><span class="badge <?php echo $fw['version'] === $ultimaVersion ? 'activo' : 'advertencia'; ?>"><?php echo htmlspecialchars($fw['version']); ?></span></td>
           <td><?php echo htmlspecialchars($fw['descripcion'] ?? '-'); ?></td>
           <td><?php echo $fw['fecha_publicacion'] ? date('d/m/Y', strtotime($fw['fecha_publicacion'])) : '-'; ?></td>
           <td><?php echo badgeFirmwareStatus($fw['version'], $ultimaVersion); ?></td>
           <td class="actions-cell">
            <button class="btn-icon" title="Ver historial de firmware"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg></button>
            <button class="btn-icon btn-disabled" title="Actualizar firmware (deshabilitado)" disabled><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg></button>
           </td>
          </tr>
         <?php endwhile; ?>
        <?php else: ?>
         <tr><td colspan="6" style="text-align:center;color:var(--tx4)">No hay dispositivos con firmware asignado</td></tr>
        <?php endif; ?>
       </tbody>
      </table>
     </div>
    </div>
   </div>
  </div>
 </div>
</div>
<script src="js/admin.js"></script>
</body>
</html>
