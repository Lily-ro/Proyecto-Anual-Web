<?php
session_start();
if(!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'ADMIN'){
    header("Location: ../index.php");
    exit;
}
require_once(__DIR__ . '/../config/db.php');
$pdo = eva_pdo();
$currentPage = 'inventario';
$pageSubtitle = 'Gestión de inventario';
if($_SERVER['REQUEST_METHOD']==='POST'){
    $accion=$_POST['accion']??'';
    if($accion==='movimiento'){
        $id=(int)($_POST['id_item']??0); $tipo=$_POST['tipo']??''; $cant=(int)($_POST['cantidad']??0); $motivo=trim($_POST['motivo']??'');
        if($id && $cant>0 && in_array($tipo,['ENTRADA','SALIDA'],true)){
            try{
                $st=$pdo->prepare("SELECT stock FROM inventario WHERE id_item=:id LIMIT 1");
                $st->execute([':id'=>$id]);
                $row=$st->fetch();
                if($row){
                    $stock=(int)$row['stock'];
                    if($tipo==='SALIDA' && $stock < $cant){ echo '<script>alert("Stock insuficiente");history.back();</script>'; exit; }
                    $nuevo = $tipo==='ENTRADA' ? $stock+$cant : $stock-$cant;
                    $pdo->prepare("UPDATE inventario SET stock=:s, fecha_actualizacion=NOW() WHERE id_item=:id")->execute([':s'=>$nuevo,':id'=>$id]);
                    $pdo->prepare("INSERT INTO movimientos_inventario (id_item,tipo,cantidad,motivo) VALUES (:id,:t,:c,:m)")->execute([':id'=>$id,':t'=>$tipo,':c'=>$cant,':m'=>$motivo?:'Movimiento manual']);
                    $pdo->prepare("INSERT INTO log_actividad (id_usuario,accion,detalle,ip,fecha_hora) VALUES (:uid,'UPDATE',:det,:ip,NOW())")->execute([':uid'=>$_SESSION['id_usuario']??null,':det'=>"Inventario {$id} {$tipo} {$cant}",':ip'=>$_SERVER['REMOTE_ADDR']??'']);
                }
            }catch(Throwable $e){ error_log($e->getMessage()); }
            header("Location: inventario.php"); exit;
        }
    }
}
$filtroBusqueda  = trim($_GET['busqueda'] ?? '');
$filtroCategoria = $_GET['categoria'] ?? '';
$filtroEstado    = $_GET['estado'] ?? '';
$categoriasValidas = ['SENSOR','DISPOSITIVO','BATERIA','ANTENA','CABLE','REPUESTO','OTRO'];
$estadosValidos    = ['disponible','stock_bajo','agotado'];
$cntTotal     = (int)$pdo->query("SELECT COUNT(*) FROM inventario")->fetchColumn();
$cntDisponibles = (int)$pdo->query("SELECT COUNT(*) FROM inventario WHERE stock > stock_minimo")->fetchColumn();
$cntStockBajo   = (int)$pdo->query("SELECT COUNT(*) FROM inventario WHERE stock > 0 AND stock <= stock_minimo")->fetchColumn();
$cntAgotados    = (int)$pdo->query("SELECT COUNT(*) FROM inventario WHERE stock = 0")->fetchColumn();

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
$sql = "SELECT i.id_item, i.nombre, i.categoria, i.modelo, i.stock, i.stock_minimo, i.ubicacion, DATE_FORMAT(i.fecha_actualizacion,'%d/%m/%Y %H:%i') AS fecha_act FROM inventario i {$whereSQL} ORDER BY i.nombre ASC";
$where=[]; $params=[];
if($filtroBusqueda !== ''){
    $where[]="(i.nombre LIKE :b1 OR i.modelo LIKE :b2 OR i.ubicacion LIKE :b3)";
    $params[':b1']="%{$filtroBusqueda}%"; $params[':b2']="%{$filtroBusqueda}%"; $params[':b3']="%{$filtroBusqueda}%";
}
if($filtroCategoria !== '' && in_array($filtroCategoria,$categoriasValidas,true)){ $where[]="i.categoria=:cat"; $params[':cat']=$filtroCategoria; }
if($filtroEstado !== '' && in_array($filtroEstado,$estadosValidos,true)){
    if($filtroEstado==='disponible') $where[]="i.stock > i.stock_minimo";
    elseif($filtroEstado==='stock_bajo') $where[]="i.stock > 0 AND i.stock <= i.stock_minimo";
    elseif($filtroEstado==='agotado') $where[]="i.stock = 0";
}
$whereSQL = $where ? 'WHERE '.implode(' AND ',$where) : '';
$sql = "SELECT i.id_item, i.nombre, i.categoria, i.modelo, i.stock, i.stock_minimo, i.ubicacion, DATE_FORMAT(i.fecha_actualizacion,'%d/%m/%Y %H:%i') AS fecha_act FROM inventario i {$whereSQL} ORDER BY i.nombre ASC";
$st=$pdo->prepare($sql);
$st->execute($params);
$listaInv=$st->fetchAll(PDO::FETCH_ASSOC);

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
       <?php if(count($listaInv)>0): ?>
        <?php foreach($listaInv as $item): ?>
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
           <button class="btn-icon" title="Entrada" onclick="movInv(<?php echo (int)$item['id_item']; ?>,'ENTRADA')"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg></button>
           <button class="btn-icon" title="Salida" onclick="movInv(<?php echo (int)$item['id_item']; ?>,'SALIDA')"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><line x1="5" y1="12" x2="19" y2="12"/></svg></button>
          </td>
        </tr>
        <?php endforeach; ?>
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
<script>
function movInv(id,tipo){
 var c=prompt('Cantidad para '+tipo+':');
 if(!c) return;
 c=parseInt(c,10);
 if(!(c>0)){ alert('Cantidad inválida'); return; }
 var m=prompt('Motivo:')||'';
 var f=document.createElement('form');
 f.method='POST'; f.action='inventario.php';
 f.innerHTML='<input type="hidden" name="accion" value="movimiento"><input type="hidden" name="id_item" value="'+id+'"><input type="hidden" name="tipo" value="'+tipo+'"><input type="hidden" name="cantidad" value="'+c+'"><input type="hidden" name="motivo" value="'+m.replace(/"/g,'&quot;')+'">';
 document.body.appendChild(f); f.submit();
}
</script>
</body>
</html>
