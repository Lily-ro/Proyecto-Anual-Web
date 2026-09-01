<?php
session_start();
if(!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'ADMIN'){
    header("Location: ../index.php");
    exit;
}
require_once(__DIR__ . '/../config/db.php');
$currentPage = 'auditorias';
$pageSubtitle = 'Registro de actividad del sistema';

$filtroBusqueda   = trim($_GET['busqueda'] ?? '');
$filtroFechaDesde = $_GET['fecha_desde'] ?? '';
$filtroFechaHasta = $_GET['fecha_hasta'] ?? '');
$filtroAccion     = $_GET['accion'] ?? '';
$pagina           = max(1, (int)($_GET['pag'] ?? 1));
$porPagina        = 10;
$offset           = ($pagina - 1) * $porPagina;

$accionesFiltro = [
    'LOGIN'  => 'Inicio de sesión',
    'LOGOUT' => 'Cierre de sesión',
    'CREATE' => 'Creación',
    'UPDATE' => 'Modificación',
    'DELETE' => 'Eliminación',
];

$where  = [];
$types  = '';
$params = [];

if($filtroBusqueda !== ''){
    $where[] = "(u.nombre LIKE ? OR u.apellido LIKE ? OR l.accion LIKE ? OR l.detalle LIKE ? OR l.ip LIKE ?)";
    $like = "%{$filtroBusqueda}%";
    $params[] = $like; $types .= 's';
    $params[] = $like; $types .= 's';
    $params[] = $like; $types .= 's';
    $params[] = $like; $types .= 's';
    $params[] = $like; $types .= 's';
}
if($filtroFechaDesde !== ''){
    $where[] = "l.fecha_hora >= ?";
    $params[] = $filtroFechaDesde . ' 00:00:00'; $types .= 's';
}
if($filtroFechaHasta !== ''){
    $where[] = "l.fecha_hora <= ?";
    $params[] = $filtroFechaHasta . ' 23:59:59'; $types .= 's';
}
if($filtroAccion !== '' && array_key_exists(strtoupper($filtroAccion), $accionesFiltro)){
    $where[] = "UPPER(l.accion) LIKE ?";
    $params[] = '%' . strtoupper($filtroAccion) . '%'; $types .= 's';
}

$whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$pdo = eva_pdo();

$sqlCount = "SELECT COUNT(*)
             FROM log_actividad l
             LEFT JOIN usuarios u ON l.id_usuario = u.id_usuario
             {$whereSQL}";
$stmtCount = $pdo->prepare($sqlCount);
$stmtCount->execute($params);
$totalRegistros = (int)$stmtCount->fetchColumn();
$totalPaginas   = max(1, ceil($totalRegistros / $porPagina));

$sql = "SELECT l.id_log,
               DATE_FORMAT(l.fecha_hora,'%d/%m/%Y') AS fecha,
               DATE_FORMAT(l.fecha_hora,'%H:%i:%s') AS hora,
               CONCAT(COALESCE(u.nombre,''), ' ', COALESCE(u.apellido,'')) AS usuario,
               COALESCE(r.nombre,'Sin rol') AS rol,
               l.accion,
               l.detalle,
               l.ip
        FROM log_actividad l
        LEFT JOIN usuarios u ON l.id_usuario = u.id_usuario
        LEFT JOIN roles r ON u.id_rol = r.id_rol
        {$whereSQL}
        ORDER BY l.fecha_hora DESC
        LIMIT ? OFFSET ?";

$paramsPag = array_merge($params, [$porPagina, $offset]);
$stmt = $pdo->prepare($sql);
$stmt->execute($paramsPag);
$resLog = $stmt->fetchAll(PDO::FETCH_ASSOC);

function badgeRol($rol){
    $map = [
        'ADMIN'    => ['cls'=>'rol-admin',   'txt'=>'Admin'],
        'TECNICO'  => ['cls'=>'rol-tecnico', 'txt'=>'Técnico'],
        'USUARIO'  => ['cls'=>'activo',      'txt'=>'Usuario'],
    ];
    $info = $map[strtoupper($rol)] ?? ['cls'=>'inactivo', 'txt'=>htmlspecialchars($rol)];
    return '<span class="badge '.$info['cls'].'">'.htmlspecialchars($info['txt']).'</span>';
}

function badgeAccion($accion){
    $upper = strtoupper($accion);
    $map = [
        'LOGIN'  => ['cls'=>'accion-login',  'txt'=>'Inicio de sesión'],
        'LOGOUT' => ['cls'=>'accion-logout', 'txt'=>'Cierre de sesión'],
        'CREATE' => ['cls'=>'accion-create', 'txt'=>'Creación'],
        'UPDATE' => ['cls'=>'accion-update', 'txt'=>'Modificación'],
        'DELETE' => ['cls'=>'accion-delete', 'txt'=>'Eliminación'],
    ];
    $info = $map[$upper] ?? ['cls'=>'accion-update', 'txt'=>htmlspecialchars($accion)];
    return '<span class="badge '.$info['cls'].'">'.htmlspecialchars($info['txt']).'</span>';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>EVA - Auditorías</title>
<link rel="stylesheet" href="css/admin.css">
</head>
<body>
<?php include __DIR__ . '/includes/sidebar.php'; ?>

<div class="main">
 <?php include __DIR__ . '/includes/header.php'; ?>

 <div class="content">
  <div class="page-header">
   <h2 class="page-title">Auditorías</h2>
   <p class="page-desc">Registro completo de actividad del sistema: quién ingresó, qué modificó, fecha, hora e IP.</p>
  </div>

  <div class="card">
   <div class="card-header">
    <form method="GET" class="filters-row">
     <div class="filter-group">
      <label class="filter-label">Buscar</label>
      <input type="text" name="busqueda" class="filter-input" placeholder="Buscar por usuario, acción..." value="<?php echo htmlspecialchars($filtroBusqueda); ?>">
     </div>
     <div class="filter-group">
      <label class="filter-label">Fecha desde</label>
      <input type="date" name="fecha_desde" class="filter-input" value="<?php echo htmlspecialchars($filtroFechaDesde); ?>">
     </div>
     <div class="filter-group">
      <label class="filter-label">Fecha hasta</label>
      <input type="date" name="fecha_hasta" class="filter-input" value="<?php echo htmlspecialchars($filtroFechaHasta); ?>">
     </div>
     <div class="filter-group">
      <label class="filter-label">Tipo de acción</label>
      <select name="accion" class="filter-input">
       <option value="">Todas</option>
       <?php foreach($accionesFiltro as $val => $lbl): ?>
        <option value="<?php echo $val; ?>"<?php echo strtoupper($filtroAccion)===$val?' selected':''; ?>><?php echo $lbl; ?></option>
       <?php endforeach; ?>
      </select>
     </div>
     <div class="filter-group filter-group-btn">
      <button type="submit" class="btn btn-primary">Filtrar</button>
     </div>
    </form>
   </div>

   <div class="table-responsive">
    <table class="table">
     <thead>
      <tr>
       <th>Fecha</th>
       <th>Hora</th>
       <th>Usuario</th>
       <th>Rol</th>
       <th>Acción</th>
       <th>Detalle</th>
       <th>IP</th>
      </tr>
     </thead>
     <tbody>
      <?php if(count($resLog) > 0): ?>
       <?php foreach($resLog as $l): ?>
        <tr>
         <td><?php echo $l['fecha']; ?></td>
         <td><?php echo $l['hora']; ?></td>
         <td><?php echo htmlspecialchars(trim($l['usuario'])); ?></td>
         <td><?php echo badgeRol($l['rol']); ?></td>
         <td><?php echo badgeAccion($l['accion']); ?></td>
         <td><?php echo htmlspecialchars($l['detalle'] ?? '-'); ?></td>
         <td><?php echo htmlspecialchars($l['ip'] ?? '-'); ?></td>
        </tr>
       <?php endforeach; ?>
      <?php else: ?>
       <tr><td colspan="7" style="text-align:center;color:var(--tx4)">No hay registros de actividad</td></tr>
      <?php endif; ?>
     </tbody>
    </table>
   </div>

   <div class="table-footer">
    <div class="table-info">Mostrando <?php echo $totalRegistros > 0 ? ($offset+1).' - '.min($offset+$porPagina, $totalRegistros) : 0; ?> de <?php echo $totalRegistros; ?> registros</div>
    <div class="pagination">
     <?php if($pagina > 1): ?>
      <a class="btn btn-page" href="?<?php echo http_build_query(array_merge($_GET, ['pag'=>$pagina-1])); ?>">&laquo; Anterior</a>
     <?php else: ?>
      <button class="btn btn-page" disabled>&laquo; Anterior</button>
     <?php endif; ?>

     <?php
     $inicio = max(1, $pagina - 2);
     $fin    = min($totalPaginas, $pagina + 2);
     for($p = $inicio; $p <= $fin; $p++): ?>
      <a class="btn btn-page <?php echo $p===$pagina?'btn-page-active':''; ?>" href="?<?php echo http_build_query(array_merge($_GET, ['pag'=>$p])); ?>"><?php echo $p; ?></a>
     <?php endfor; ?>

     <?php if($pagina < $totalPaginas): ?>
      <a class="btn btn-page" href="?<?php echo http_build_query(array_merge($_GET, ['pag'=>$pagina+1])); ?>">Siguiente &raquo;</a>
     <?php else: ?>
      <button class="btn btn-page" disabled>Siguiente &raquo;</button>
     <?php endif; ?>
    </div>
   </div>
  </div>
 </div>
</div>
<script src="js/admin.js"></script>
</body>
</html>
