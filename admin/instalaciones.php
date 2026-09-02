<?php
session_start();
if(!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'ADMIN'){
    header("Location: ../index.php");
    exit;
}
require_once(__DIR__ . '/../config/db.php');
$currentPage = 'instalaciones';
$pageSubtitle = 'Gestión de instalaciones';

$filtroBusqueda   = trim($_GET['busqueda'] ?? '');
$filtroFechaDesde = $_GET['fecha_desde'] ?? '';
$filtroFechaHasta = $_GET['fecha_hasta'] ?? '';

$where  = [];
$types  = '';
$params = [];

if($filtroBusqueda !== ''){
    $where[] = "(d.nombre LIKE ? OR t.nombre LIKE ? OR e.nombre LIKE ? OR CONCAT(u.nombre,' ',u.apellido) LIKE ? OR i.observaciones LIKE ?)";
    $like = "%{$filtroBusqueda}%";
    $params[] = $like; $types .= 's';
    $params[] = $like; $types .= 's';
    $params[] = $like; $types .= 's';
    $params[] = $like; $types .= 's';
    $params[] = $like; $types .= 's';
}
if($filtroFechaDesde !== ''){
    $where[] = "i.fecha_instalacion >= ?";
    $params[] = $filtroFechaDesde . ' 00:00:00'; $types .= 's';
}
if($filtroFechaHasta !== ''){
    $where[] = "i.fecha_instalacion <= ?";
    $params[] = $filtroFechaHasta . ' 23:59:59'; $types .= 's';
}

$whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$sql = "SELECT i.id_instalacion, i.fecha_instalacion, i.observaciones, i.latitud, i.longitud,
               d.nombre AS dispositivo,
               t.nombre AS tanque,
               e.nombre AS edificio,
               CONCAT(u.nombre,' ',u.apellido) AS tecnico
        FROM instalaciones i
        JOIN dispositivos d ON i.id_dispositivo = d.id_dispositivo
        JOIN tanques t ON d.id_tanque = t.id_tanque
        JOIN edificios e ON t.id_edificio = e.id_edificio
        JOIN usuarios u ON i.id_tecnico = u.id_usuario
        {$whereSQL}
        ORDER BY i.fecha_instalacion DESC";

$stmt = $conn->prepare($sql);
if($params){
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$resInstalaciones = $stmt->get_result();

function badgeCosto($costo){
    $c = (float)$costo;
    if($c >= 5000) $cls = 'alta';
    elseif($c >= 1000) $cls = 'media';
    else $cls = 'activo';
    return '<span class="badge '.$cls.'">$'.number_format($c, 2, ',', '.').'</span>';
}

function coordenadasLink($lat, $lng){
    if(!$lat || !$lng) return '<span style="color:var(--tx4)">Sin coordenadas</span>';
    $lat = htmlspecialchars($lat);
    $lng = htmlspecialchars($lng);
    return '<a href="https://www.google.com/maps?q='.$lat.','.$lng.'" target="_blank" title="Ver en mapa">'.$lat.', '.$lng.'</a>';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>EVA - Instalaciones</title>
<link rel="stylesheet" href="css/admin.css">
</head>
<body>
<?php include __DIR__ . '/includes/sidebar.php'; ?>

<div class="main">
 <?php include __DIR__ . '/includes/header.php'; ?>

 <div class="content">
  <div class="page-header">
   <h2 class="page-title">Instalaciones</h2>
   <p class="page-desc">Gestión de instalaciones de sistemas EVA.</p>
  </div>

  <div class="card">
   <div class="card-header">
    <form method="GET" class="filters-row">
     <div class="filter-group">
      <input type="text" name="busqueda" class="filter-input" placeholder="Buscar instalación..." value="<?php echo htmlspecialchars($filtroBusqueda); ?>">
     </div>
     <div class="filter-group">
      <input type="date" name="fecha_desde" class="filter-input" value="<?php echo htmlspecialchars($filtroFechaDesde); ?>" title="Fecha desde">
     </div>
     <div class="filter-group">
      <input type="date" name="fecha_hasta" class="filter-input" value="<?php echo htmlspecialchars($filtroFechaHasta); ?>" title="Fecha hasta">
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
       <th>Dispositivo</th>
       <th>Edificio</th>
       <th>Tanque</th>
       <th>Fecha</th>
       <th>Técnico</th>
       <th>Observaciones</th>
       <th>Acciones</th>
      </tr>
     </thead>
     <tbody>
      <?php if($resInstalaciones && $resInstalaciones->num_rows > 0): ?>
       <?php while($i = $resInstalaciones->fetch_assoc()): ?>
        <tr>
         <td>INS-<?php echo str_pad($i['id_instalacion'], 3, '0', STR_PAD_LEFT); ?></td>
         <td><?php echo htmlspecialchars($i['dispositivo']); ?></td>
         <td><?php echo htmlspecialchars($i['edificio']); ?></td>
         <td><?php echo htmlspecialchars($i['tanque']); ?></td>
         <td><?php echo $i['fecha_instalacion'] ? date('d/m/Y H:i', strtotime($i['fecha_instalacion'])) : '-'; ?></td>
         <td><?php echo htmlspecialchars($i['tecnico']); ?></td>
         <td><?php echo htmlspecialchars($i['observaciones'] ?? '-'); ?></td>
         <td class="actions-cell">
          <button class="btn-icon" title="Ver"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button>
          <button class="btn-icon" title="Editar"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button>
         </td>
        </tr>
       <?php endwhile; ?>
      <?php else: ?>
       <tr><td colspan="8" style="text-align:center;color:var(--tx4)">No hay instalaciones registradas</td></tr>
      <?php endif; ?>
     </tbody>
    </table>
   </div>
  </div>
 </div>
</div>
<script src="js/admin.js"></script>
</body>
</html>
