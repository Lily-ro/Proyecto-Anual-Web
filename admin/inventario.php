<?php
session_start();
if(!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'ADMIN'){
    header("Location: ../index.php");
    exit;
}
require_once(__DIR__ . '/../config/db.php');
$currentPage = 'inventario';
$pageSubtitle = 'Gestión de inventario';

$filtroBusqueda  = trim($_GET['busqueda'] ?? '');
$filtroCategoria = $_GET['categoria'] ?? '';
$filtroEstado    = $_GET['estado'] ?? '';

$categoriasValidas = ['SENSOR','DISPOSITIVO','BATERIA','ANTENA','CABLE','REPUESTO','OTRO'];
$estadosValidos    = ['disponible','stock_bajo','agotado'];

function countInvSimple($conn, $where = ''){
    $sql = "SELECT COUNT(*) FROM inventario" . ($where ? " WHERE {$where}" : '');
    return (int)$conn->query($sql)->fetch_row()[0];
}
$cntTotal     = countInvSimple($conn);
$cntDisponibles = countInvSimple($conn, 'stock > stock_minimo');
$cntStockBajo   = countInvSimple($conn, 'stock > 0 AND stock <= stock_minimo');
$cntAgotados    = countInvSimple($conn, 'stock = 0');

$where  = [];
$types  = '';
$params = [];

if($filtroBusqueda !== ''){
    $where[] = "(i.nombre LIKE ? OR i.modelo LIKE ? OR i.ubicacion LIKE ?)";
    $like = "%{$filtroBusqueda}%";
    $params[] = $like; $types .= 's';
    $params[] = $like; $types .= 's';
    $params[] = $like; $types .= 's';
}
if($filtroCategoria !== '' && in_array($filtroCategoria, $categoriasValidas)){
    $where[] = "i.categoria = ?";
    $params[] = $filtroCategoria; $types .= 's';
}
if($filtroEstado !== '' && in_array($filtroEstado, $estadosValidos)){
    if($filtroEstado === 'disponible'){
        $where[] = "i.stock > i.stock_minimo";
    } elseif($filtroEstado === 'stock_bajo'){
        $where[] = "i.stock > 0 AND i.stock <= i.stock_minimo";
    } elseif($filtroEstado === 'agotado'){
        $where[] = "i.stock = 0";
    }
}

$whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$sql = "SELECT i.id_item, i.nombre, i.categoria, i.modelo, i.stock, i.stock_minimo,
               i.ubicacion, DATE_FORMAT(i.fecha_actualizacion,'%d/%m/%Y %H:%i') AS fecha_act
        FROM inventario i
        {$whereSQL}
        ORDER BY i.nombre ASC";

$stmt = $conn->prepare($sql);
if($params){
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$resInventario = $stmt->get_result();

function badgeCategoria($cat){
    $map = [
        'SENSOR'     => ['cls'=>'activo',  'txt'=>'Sensor'],
        'DISPOSITIVO'=> ['cls'=>'activo',  'txt'=>'Dispositivo'],
        'BATERIA'    => ['cls'=>'media',   'txt'=>'Batería'],
        'ANTENA'     => ['cls'=>'media',   'txt'=>'Antena'],
        'CABLE'      => ['cls'=>'baja',    'txt'=>'Cable'],
        'REPUESTO'   => ['cls'=>'activo',  'txt'=>'Repuesto'],
        'OTRO'       => ['cls'=>'inactivo','txt'=>'Otro'],
    ];
    $info = $map[$cat] ?? ['cls'=>'inactivo', 'txt'=>$cat];
    return '<span class="badge '.$info['cls'].'">'.htmlspecialchars($info['txt']).'</span>';
}

function badgeEstadoInv($stock, $stockMinimo){
    $stock = (int)$stock;
    $stockMinimo = (int)$stockMinimo;
    if($stock === 0){
        return '<span class="badge inactivo">Agotado</span>';
    } elseif($stock <= $stockMinimo){
        return '<span class="badge advertencia">Stock bajo</span>';
    }
    return '<span class="badge activo">Disponible</span>';
}

function badgeStock($stock, $stockMinimo){
    $stock = (int)$stock;
    $stockMinimo = (int)$stockMinimo;
    if($stock === 0) $cls = 'baja';
    elseif($stock <= $stockMinimo) $cls = 'media';
    else $cls = 'activo';
    return '<span class="badge '.$cls.'">'.$stock.'</span>';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>EVA - Inventario</title>
<link rel="stylesheet" href="css/admin.css">
</head>
<body>
<?php include __DIR__ . '/includes/sidebar.php'; ?>

<div class="main">
 <?php include __DIR__ . '/includes/header.php'; ?>

 <div class="content">
  <div class="page-header">
   <h2 class="page-title">Inventario</h2>
   <p class="page-desc">Control de inventario de equipos y repuestos.</p>
  </div>

  <div class="stats-row">
   <div class="stat-card">
    <div class="stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/></svg></div>
    <div class="stat-info"><div class="stat-val"><?php echo $cntTotal; ?></div><div class="stat-label">Total items</div></div>
   </div>
   <div class="stat-card">
    <div class="stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg></div>
    <div class="stat-info"><div class="stat-val"><?php echo $cntDisponibles; ?></div><div class="stat-label">Disponibles</div></div>
   </div>
   <div class="stat-card">
    <div class="stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg></div>
    <div class="stat-info"><div class="stat-val"><?php echo $cntStockBajo; ?></div><div class="stat-label">Stock bajo</div></div>
   </div>
   <div class="stat-card">
    <div class="stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg></div>
    <div class="stat-info"><div class="stat-val"><?php echo $cntAgotados; ?></div><div class="stat-label">Agotados</div></div>
   </div>
  </div>

  <div class="card">
   <div class="card-header">
    <form method="GET" class="filters-row">
     <div class="filter-group">
      <input type="text" name="busqueda" class="filter-input" placeholder="Buscar item..." value="<?php echo htmlspecialchars($filtroBusqueda); ?>">
     </div>
     <div class="filter-group">
      <select name="categoria" class="filter-input">
       <option value="">Todas las categorías</option>
       <option value="SENSOR"<?php echo $filtroCategoria==='SENSOR'?' selected':''; ?>>Sensor</option>
       <option value="DISPOSITIVO"<?php echo $filtroCategoria==='DISPOSITIVO'?' selected':''; ?>>Dispositivo</option>
       <option value="BATERIA"<?php echo $filtroCategoria==='BATERIA'?' selected':''; ?>>Batería</option>
       <option value="ANTENA"<?php echo $filtroCategoria==='ANTENA'?' selected':''; ?>>Antena</option>
       <option value="CABLE"<?php echo $filtroCategoria==='CABLE'?' selected':''; ?>>Cable</option>
       <option value="REPUESTO"<?php echo $filtroCategoria==='REPUESTO'?' selected':''; ?>>Repuesto</option>
       <option value="OTRO"<?php echo $filtroCategoria==='OTRO'?' selected':''; ?>>Otro</option>
      </select>
     </div>
     <div class="filter-group">
      <select name="estado" class="filter-input">
       <option value="">Todos los estados</option>
       <option value="disponible"<?php echo $filtroEstado==='disponible'?' selected':''; ?>>Disponible</option>
       <option value="stock_bajo"<?php echo $filtroEstado==='stock_bajo'?' selected':''; ?>>Stock bajo</option>
       <option value="agotado"<?php echo $filtroEstado==='agotado'?' selected':''; ?>>Agotado</option>
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
       <th>Modelo</th>
       <th>Categoría</th>
       <th>Stock</th>
       <th>Mínimo</th>
       <th>Ubicación</th>
       <th>Estado</th>
       <th>Acciones</th>
      </tr>
     </thead>
     <tbody>
      <?php if($resInventario && $resInventario->num_rows > 0): ?>
       <?php while($item = $resInventario->fetch_assoc()): ?>
        <tr>
         <td>INV-<?php echo str_pad($item['id_item'], 3, '0', STR_PAD_LEFT); ?></td>
         <td><?php echo htmlspecialchars($item['nombre'] ?? '-'); ?></td>
         <td><?php echo htmlspecialchars($item['modelo'] ?? '-'); ?></td>
         <td><?php echo badgeCategoria($item['categoria']); ?></td>
         <td><?php echo badgeStock($item['stock'], $item['stock_minimo']); ?></td>
         <td><?php echo (int)$item['stock_minimo']; ?></td>
         <td><?php echo htmlspecialchars($item['ubicacion'] ?? '-'); ?></td>
         <td><?php echo badgeEstadoInv($item['stock'], $item['stock_minimo']); ?></td>
         <td class="actions-cell">
          <button class="btn-icon" title="Ver"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button>
          <button class="btn-icon" title="Editar"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button>
          <button class="btn-icon btn-icon-danger" title="Eliminar"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg></button>
         </td>
        </tr>
       <?php endwhile; ?>
      <?php else: ?>
       <tr><td colspan="9" style="text-align:center;color:var(--tx4)">No hay items en el inventario</td></tr>
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
