<?php
session_start();
if(!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'ADMIN'){
    header("Location: ../index.php");
    exit;
}
require_once(__DIR__ . '/../config/db.php');
$pdo = eva_pdo();
$currentPage = 'alertas';
$pageSubtitle = 'Gestión de alertas';

$tiposValidos     = ['NIVEL_BAJO','NIVEL_ALTO','SIN_CONEXION','FALLA_SENSOR','CONSUMO_ANORMAL'];
$estadosValidos   = ['PENDIENTE','ATENDIDA','CERRADA'];

if($_SERVER['REQUEST_METHOD']==='POST'){
    $accion = $_POST['accion'] ?? '';
    $id = (int)($_POST['id_alerta'] ?? 0);
    if($id && in_array($accion,['atender','cerrar'],true)){
        $nuevo = $accion==='atender' ? 'ATENDIDA' : 'CERRADA';
        try{
            $st=$pdo->prepare("UPDATE alertas SET estado=:e WHERE id_alerta=:id");
            $st->execute([':e'=>$nuevo,':id'=>$id]);
            $pdo->prepare("INSERT INTO log_actividad (id_usuario,accion,detalle,ip,fecha_hora) VALUES (:uid,'UPDATE',:det,:ip,NOW())")->execute([':uid'=>$_SESSION['id_usuario']??null,':det'=>"Alerta {$id} -> {$nuevo}",':ip'=>$_SERVER['REMOTE_ADDR']??'']);
        }catch(Throwable $e){ error_log($e->getMessage()); }
        header("Location: alertas.php"); exit;
    }
}

$filtroBusqueda = trim($_GET['busqueda'] ?? '');
$filtroTipo     = $_GET['tipo'] ?? '';
$filtroEstado   = $_GET['estado'] ?? '';

function countAlertasPorTipo(PDO $pdo, $tipo){
    $st=$pdo->prepare("SELECT COUNT(*) FROM alertas WHERE tipo=:t");
    $st->execute([':t'=>$tipo]);
    return (int)$st->fetchColumn();
}

$cntCriticas = 0;
foreach(['NIVEL_BAJO','FALLA_SENSOR','SIN_CONEXION'] as $t){
    $cntCriticas += countAlertasPorTipo($pdo, $t);
}
$cntAdvertencias = countAlertasPorTipo($pdo, 'NIVEL_ALTO');
$cntInformativas = countAlertasPorTipo($pdo, 'CONSUMO_ANORMAL');

$where=[]; $params=[];
if($filtroBusqueda !== ''){
    $where[]="(a.descripcion LIKE :b1 OR t.nombre LIKE :b2)";
    $params[':b1']="%{$filtroBusqueda}%"; $params[':b2']="%{$filtroBusqueda}%";
}
if($filtroTipo !== '' && in_array($filtroTipo, $tiposValidos,true)){
    $where[]="a.tipo=:tipo"; $params[':tipo']=$filtroTipo;
}
if($filtroEstado !== '' && in_array($filtroEstado, $estadosValidos,true)){
    $where[]="a.estado=:estado"; $params[':estado']=$filtroEstado;
}
$whereSQL = $where ? 'WHERE '.implode(' AND ',$where) : '';
$sql="SELECT a.id_alerta, a.tipo, a.descripcion, a.estado, t.nombre AS tanque, DATE_FORMAT(a.fecha_hora,'%d/%m/%Y %H:%i') AS fecha FROM alertas a JOIN tanques t ON a.id_tanque=t.id_tanque {$whereSQL} ORDER BY a.fecha_hora DESC";
$st=$pdo->prepare($sql);
$st->execute($params);
$listaAlertas=$st->fetchAll(PDO::FETCH_ASSOC);

function badgeTipoAlerta($tipo){
    $map=['NIVEL_BAJO'=>['cls'=>'alta','txt'=>'Nivel Bajo'],'NIVEL_ALTO'=>['cls'=>'media','txt'=>'Nivel Alto'],'SIN_CONEXION'=>['cls'=>'alta','txt'=>'Sin Conexión'],'FALLA_SENSOR'=>['cls'=>'alta','txt'=>'Falla Sensor'],'CONSUMO_ANORMAL'=>['cls'=>'baja','txt'=>'Consumo Anormal']];
    $info=$map[$tipo]??['cls'=>'media','txt'=>$tipo];
    return '<span class="badge '.$info['cls'].'">'.htmlspecialchars($info['txt']).'</span>';
}
function badgeEstadoAlerta($estado){
    $map=['PENDIENTE'=>'pendiente','ATENDIDA'=>'en_camino','CERRADA'=>'completada'];
    $cls=$map[$estado]??''; $txt=htmlspecialchars(str_replace('_',' ',$estado));
    return '<span class="badge '.$cls.'">'.$txt.'</span>';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>EVA - Alertas</title>
<link rel="stylesheet" href="css/admin.css">
</head>
<body>
<?php include __DIR__ . '/includes/sidebar.php'; ?>
<div class="main">
 <?php include __DIR__ . '/includes/header.php'; ?>
 <div class="content">
  <div class="page-header">
   <h2 class="page-title">Alertas</h2>
   <p class="page-desc">Monitoreo y gestión de alertas del sistema.</p>
  </div>
  <div class="stats-row">
   <div class="stat-card stat-critical">
    <div class="stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg></div>
    <div class="stat-info"><div class="stat-val"><?php echo $cntCriticas; ?></div><div class="stat-label">Críticas</div></div>
   </div>
   <div class="stat-card stat-warning">
    <div class="stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg></div>
    <div class="stat-info"><div class="stat-val"><?php echo $cntAdvertencias; ?></div><div class="stat-label">Advertencias</div></div>
   </div>
   <div class="stat-card stat-info">
    <div class="stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg></div>
    <div class="stat-info"><div class="stat-val"><?php echo $cntInformativas; ?></div><div class="stat-label">Informativas</div></div>
   </div>
  </div>
  <div class="card">
   <div class="card-header">
    <form method="GET" class="filters-row">
     <div class="filter-group">
      <input type="text" name="busqueda" class="filter-input" placeholder="Buscar alerta..." value="<?php echo htmlspecialchars($filtroBusqueda); ?>">
     </div>
     <div class="filter-group">
      <select name="tipo" class="filter-input">
       <option value="">Todos los tipos</option>
       <option value="NIVEL_BAJO"<?php echo $filtroTipo==='NIVEL_BAJO'?' selected':''; ?>>Nivel Bajo</option>
       <option value="NIVEL_ALTO"<?php echo $filtroTipo==='NIVEL_ALTO'?' selected':''; ?>>Nivel Alto</option>
       <option value="SIN_CONEXION"<?php echo $filtroTipo==='SIN_CONEXION'?' selected':''; ?>>Sin Conexión</option>
       <option value="FALLA_SENSOR"<?php echo $filtroTipo==='FALLA_SENSOR'?' selected':''; ?>>Falla Sensor</option>
       <option value="CONSUMO_ANORMAL"<?php echo $filtroTipo==='CONSUMO_ANORMAL'?' selected':''; ?>>Consumo Anormal</option>
      </select>
     </div>
     <div class="filter-group">
      <select name="estado" class="filter-input">
       <option value="">Todos los estados</option>
       <option value="PENDIENTE"<?php echo $filtroEstado==='PENDIENTE'?' selected':''; ?>>Pendiente</option>
       <option value="ATENDIDA"<?php echo $filtroEstado==='ATENDIDA'?' selected':''; ?>>Atendida</option>
       <option value="CERRADA"<?php echo $filtroEstado==='CERRADA'?' selected':''; ?>>Cerrada</option>
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
       <th>Tanque</th>
       <th>Fecha</th>
       <th>Estado</th>
       <th>Acciones</th>
      </tr>
     </thead>
     <tbody>
      <?php if(count($listaAlertas)>0): ?>
       <?php foreach($listaAlertas as $a): ?>
        <tr>
         <td>AL-<?php echo str_pad($a['id_alerta'], 3, '0', STR_PAD_LEFT); ?></td>
         <td><?php echo badgeTipoAlerta($a['tipo']); ?></td>
         <td><?php echo htmlspecialchars($a['descripcion'] ?? '-'); ?></td>
         <td><?php echo htmlspecialchars($a['tanque']); ?></td>
         <td><?php echo $a['fecha']; ?></td>
         <td><?php echo badgeEstadoAlerta($a['estado']); ?></td>
         <td class="actions-cell">
          <?php if($a['estado']==='PENDIENTE'): ?>
           <form method="POST" style="display:inline"><input type="hidden" name="id_alerta" value="<?php echo (int)$a['id_alerta']; ?>"><input type="hidden" name="accion" value="atender"><button class="btn-icon" title="Marcar atendida"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><polyline points="20 6 9 17 4 12"/></svg></button></form>
          <?php elseif($a['estado']==='ATENDIDA'): ?>
           <form method="POST" style="display:inline"><input type="hidden" name="id_alerta" value="<?php echo (int)$a['id_alerta']; ?>"><input type="hidden" name="accion" value="cerrar"><button class="btn-icon" title="Cerrar alerta"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><polyline points="20 6 9 17 4 12"/></svg></button></form>
          <?php endif; ?>
         </td>
        </tr>
       <?php endforeach; ?>
      <?php else: ?>
       <tr><td colspan="7" style="text-align:center;color:var(--tx4)">No hay alertas registradas</td></tr>
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
