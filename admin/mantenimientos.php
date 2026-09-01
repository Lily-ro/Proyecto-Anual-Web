<?php
session_start();
if(!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'ADMIN'){
    header("Location: ../index.php");
    exit;
}
require_once(__DIR__ . '/../config/db.php');
$currentPage = 'mantenimientos';
$pageSubtitle = 'Gestión de mantenimientos';

$filtroBusqueda = trim($_GET['busqueda'] ?? '');
$filtroTipo     = $_GET['tipo'] ?? '';
$filtroEstado   = $_GET['estado'] ?? '';
$filtroFechaDesde = $_GET['fecha_desde'] ?? '';
$filtroFechaHasta = $_GET['fecha_hasta'] ?? '';

$tiposValidos   = ['PREVENTIVO','CORRECTIVO','PREDICTIVO'];
$estadosValidos = ['PENDIENTE','EN_PROCESO','FINALIZADO','CANCELADO'];

function countMantByEstados($conn, $estados){
    $placeholders = implode(',', array_fill(0, count($estados), '?'));
    $types = str_repeat('s', count($estados));
    $stmt = $conn->prepare("SELECT COUNT(*) FROM mantenimientos WHERE estado IN ({$placeholders})");
    $stmt->bind_param($types, ...$estados);
    $stmt->execute();
    return (int)$stmt->get_result()->fetch_row()[0];
}
$cntProgramados = countMantByEstados($conn, ['PENDIENTE','EN_PROCESO']);
$cntHistorial   = countMantByEstados($conn, ['FINALIZADO','CANCELADO']);

function buildMantQuery($conn, $filtroBusqueda, $filtroTipo, $estadosPermitidos, $filtroFechaDesde = '', $filtroFechaHasta = ''){
    $where  = [];
    $types  = '';
    $params = [];

    $placeholders = implode(',', array_fill(0, count($estadosPermitidos), '?'));
    $where[] = "m.estado IN ({$placeholders})";
    $types .= str_repeat('s', count($estadosPermitidos));
    $params = array_merge($params, $estadosPermitidos);

    if($filtroBusqueda !== ''){
        $where[] = "(m.descripcion LIKE ? OR d.nombre LIKE ? OR t.nombre LIKE ? OR CONCAT(u.nombre,' ',u.apellido) LIKE ?)";
        $like = "%{$filtroBusqueda}%";
        $params[] = $like; $types .= 's';
        $params[] = $like; $types .= 's';
        $params[] = $like; $types .= 's';
        $params[] = $like; $types .= 's';
    }
    if($filtroTipo !== '' && in_array($filtroTipo, ['PREVENTIVO','CORRECTIVO','PREDICTIVO'])){
        $where[] = "m.tipo = ?";
        $params[] = $filtroTipo; $types .= 's';
    }
    if($filtroFechaDesde !== ''){
        $where[] = "m.fecha_programada >= ?";
        $params[] = $filtroFechaDesde . ' 00:00:00'; $types .= 's';
    }
    if($filtroFechaHasta !== ''){
        $where[] = "m.fecha_programada <= ?";
        $params[] = $filtroFechaHasta . ' 23:59:59'; $types .= 's';
    }

    $whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';
    $sql = "SELECT m.id_mantenimiento, m.tipo, m.descripcion, m.fecha_programada, m.fecha_realizada,
                   m.costo, m.estado,
                   d.nombre AS dispositivo,
                   t.nombre AS tanque,
                   CONCAT(u.nombre,' ',u.apellido) AS tecnico
            FROM mantenimientos m
            JOIN dispositivos d ON m.id_dispositivo = d.id_dispositivo
            JOIN tanques t ON d.id_tanque = t.id_tanque
            JOIN usuarios u ON m.id_tecnico = u.id_usuario
            {$whereSQL}
            ORDER BY m.fecha_programada DESC";

    $stmt = $conn->prepare($sql);
    if($params){
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    return $stmt->get_result();
}

function badgeTipoMant($tipo){
    $map = [
        'PREVENTIVO'  => ['cls'=>'activo',     'txt'=>'Preventivo'],
        'CORRECTIVO'  => ['cls'=>'alta',       'txt'=>'Correctivo'],
        'PREDICTIVO'  => ['cls'=>'media',      'txt'=>'Predictivo'],
    ];
    $info = $map[$tipo] ?? ['cls'=>'media', 'txt'=>$tipo];
    return '<span class="badge '.$info['cls'].'">'.htmlspecialchars($info['txt']).'</span>';
}

function badgeEstadoMant($estado){
    $map = [
        'PENDIENTE'   => 'pendiente',
        'EN_PROCESO'  => 'advertencia',
        'FINALIZADO'  => 'activo',
        'CANCELADO'   => 'inactivo',
    ];
    $cls = $map[$estado] ?? '';
    $txt = htmlspecialchars(str_replace('_',' ',$estado));
    return '<span class="badge '.$cls.'">'.$txt.'</span>';
}

function badgeCosto($costo){
    $c = (float)$costo;
    if($c >= 5000) $cls = 'alta';
    elseif($c >= 1000) $cls = 'media';
    else $cls = 'activo';
    return '<span class="badge '.$cls.'">$'.number_format($c, 2, ',', '.').'</span>';
}

$resProgramados = buildMantQuery($conn, $filtroBusqueda, $filtroTipo, ['PENDIENTE','EN_PROCESO'], $filtroFechaDesde, $filtroFechaHasta);
$resHistorial   = buildMantQuery($conn, $filtroBusqueda, $filtroTipo, ['FINALIZADO','CANCELADO'], $filtroFechaDesde, $filtroFechaHasta);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>EVA - Mantenimientos</title>
<link rel="stylesheet" href="css/admin.css">
</head>
<body>
<?php include __DIR__ . '/includes/sidebar.php'; ?>

<div class="main">
 <?php include __DIR__ . '/includes/header.php'; ?>

 <div class="content">
  <div class="page-header">
   <h2 class="page-title">Mantenimientos</h2>
   <p class="page-desc">Programación y seguimiento de mantenimientos.</p>
  </div>

  <div class="tabs-container">
   <div class="tabs">
    <button class="tab active" data-tab="programados">Programados <span class="tab-count"><?php echo $cntProgramados; ?></span></button>
    <button class="tab" data-tab="historial">Historial <span class="tab-count"><?php echo $cntHistorial; ?></span></button>
   </div>

   <div class="tab-content active" id="tab-programados">
    <div class="card">
     <div class="card-header">
      <form method="GET" class="filters-row">
       <input type="hidden" name="tab" value="programados">
       <div class="filter-group">
        <input type="text" name="busqueda" class="filter-input" placeholder="Buscar mantenimiento..." value="<?php echo htmlspecialchars($filtroBusqueda); ?>">
       </div>
       <div class="filter-group">
        <select name="tipo" class="filter-input">
         <option value="">Todos los tipos</option>
         <option value="PREVENTIVO"<?php echo $filtroTipo==='PREVENTIVO'?' selected':''; ?>>Preventivo</option>
         <option value="CORRECTIVO"<?php echo $filtroTipo==='CORRECTIVO'?' selected':''; ?>>Correctivo</option>
         <option value="PREDICTIVO"<?php echo $filtroTipo==='PREDICTIVO'?' selected':''; ?>>Predictivo</option>
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
         <th>Tipo</th>
         <th>Descripción</th>
         <th>Dispositivo / Tanque</th>
         <th>Fecha programada</th>
         <th>Técnico</th>
         <th>Costo</th>
         <th>Estado</th>
         <th>Acciones</th>
        </tr>
       </thead>
       <tbody>
        <?php if($resProgramados && $resProgramados->num_rows > 0): ?>
         <?php while($m = $resProgramados->fetch_assoc()): ?>
          <tr>
           <td>MT-<?php echo str_pad($m['id_mantenimiento'], 3, '0', STR_PAD_LEFT); ?></td>
           <td><?php echo badgeTipoMant($m['tipo']); ?></td>
           <td><?php echo htmlspecialchars($m['descripcion'] ?? '-'); ?></td>
           <td><?php echo htmlspecialchars($m['dispositivo']); ?> / <?php echo htmlspecialchars($m['tanque']); ?></td>
           <td><?php echo $m['fecha_programada'] ? date('d/m/Y', strtotime($m['fecha_programada'])) : '-'; ?></td>
           <td><?php echo htmlspecialchars($m['tecnico']); ?></td>
           <td><?php echo badgeCosto($m['costo']); ?></td>
           <td><?php echo badgeEstadoMant($m['estado']); ?></td>
           <td class="actions-cell">
            <button class="btn-icon" title="Ver"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button>
            <button class="btn-icon" title="Editar"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button>
           </td>
          </tr>
         <?php endwhile; ?>
        <?php else: ?>
         <tr><td colspan="9" style="text-align:center;color:var(--tx4)">No hay mantenimientos programados</td></tr>
        <?php endif; ?>
       </tbody>
      </table>
     </div>
    </div>
   </div>

   <div class="tab-content" id="tab-historial">
    <div class="card">
     <div class="card-header">
      <form method="GET" class="filters-row">
       <input type="hidden" name="tab" value="historial">
       <div class="filter-group">
        <input type="text" name="busqueda" class="filter-input" placeholder="Buscar en historial..." value="<?php echo htmlspecialchars($filtroBusqueda); ?>">
       </div>
       <div class="filter-group">
        <input type="date" name="fecha_desde" class="filter-input" value="<?php echo htmlspecialchars($filtroFechaDesde); ?>">
       </div>
       <div class="filter-group">
        <input type="date" name="fecha_hasta" class="filter-input" value="<?php echo htmlspecialchars($filtroFechaHasta); ?>">
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
         <th>Tipo</th>
         <th>Descripción</th>
         <th>Dispositivo / Tanque</th>
         <th>Fecha completado</th>
         <th>Técnico</th>
         <th>Costo</th>
         <th>Estado</th>
        </tr>
       </thead>
       <tbody>
        <?php if($resHistorial && $resHistorial->num_rows > 0): ?>
         <?php while($m = $resHistorial->fetch_assoc()): ?>
          <tr>
           <td>MT-<?php echo str_pad($m['id_mantenimiento'], 3, '0', STR_PAD_LEFT); ?></td>
           <td><?php echo badgeTipoMant($m['tipo']); ?></td>
           <td><?php echo htmlspecialchars($m['descripcion'] ?? '-'); ?></td>
           <td><?php echo htmlspecialchars($m['dispositivo']); ?> / <?php echo htmlspecialchars($m['tanque']); ?></td>
           <td><?php echo $m['fecha_realizada'] ? date('d/m/Y', strtotime($m['fecha_realizada'])) : '-'; ?></td>
           <td><?php echo htmlspecialchars($m['tecnico']); ?></td>
           <td><?php echo badgeCosto($m['costo']); ?></td>
           <td><?php echo badgeEstadoMant($m['estado']); ?></td>
          </tr>
         <?php endwhile; ?>
        <?php else: ?>
         <tr><td colspan="8" style="text-align:center;color:var(--tx4)">No hay registros en el historial</td></tr>
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
