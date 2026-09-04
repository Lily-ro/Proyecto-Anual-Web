<?php
session_start();
if(!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'ADMIN'){
    header("Location: ../index.php");
    exit;
}
require_once(__DIR__ . '/../config/db.php');
$pdo = eva_pdo();
$currentPage = 'auditorias';
$pageSubtitle = 'Registro de actividad del sistema';
$fBusqueda = trim($_GET['busqueda'] ?? '');
$fDesde = $_GET['desde'] ?? '';
$fHasta = $_GET['hasta'] ?? '';
$fAccion = $_GET['accion'] ?? '';
$where=[]; $params=[];
if($fBusqueda!==''){ $where[]="(u.nombre LIKE :b OR u.apellido LIKE :b2 OR l.accion LIKE :b3 OR l.detalle LIKE :b4)"; $params[':b']="%{$fBusqueda}%"; $params[':b2']="%{$fBusqueda}%"; $params[':b3']="%{$fBusqueda}%"; $params[':b4']="%{$fBusqueda}%"; }
if($fDesde!=='' && preg_match('/^\d{4}-\d{2}-\d{2}$/',$fDesde)){ $where[]="DATE(l.fecha_hora) >= :desde"; $params[':desde']=$fDesde; }
if($fHasta!=='' && preg_match('/^\d{4}-\d{2}-\d{2}$/',$fHasta)){ $where[]="DATE(l.fecha_hora) <= :hasta"; $params[':hasta']=$fHasta; }
if($fAccion!=='' && in_array($fAccion,['CREATE','UPDATE','DELETE','LOGIN','LOGOUT','CONFIG'])){ $where[]="l.accion=:acc"; $params[':acc']=$fAccion; }
$whereSQL = $where ? 'WHERE '.implode(' AND ',$where) : '';
$st=$pdo->prepare("SELECT l.fecha_hora, l.accion, l.detalle, l.ip, CONCAT(COALESCE(u.nombre,''),' ',COALESCE(u.apellido,'')) as usuario, r.nombre as rol FROM log_actividad l LEFT JOIN usuarios u ON l.id_usuario=u.id_usuario LEFT JOIN roles r ON u.id_rol=r.id_rol {$whereSQL} ORDER BY l.fecha_hora DESC LIMIT 100");
$st->execute($params);
$lista=$st->fetchAll(PDO::FETCH_ASSOC);
function badgeRol($r){ $r=trim($r); if($r==='ADMIN') return '<span class="badge rol-admin">Admin</span>'; if($r==='TECNICO') return '<span class="badge rol-tecnico">Técnico</span>'; if($r==='USUARIO') return '<span class="badge rol-cliente">Usuario</span>'; return '<span class="badge">'.htmlspecialchars($r?:'-').'</span>'; }
function badgeAccion($a){ $m=['CREATE'=>'accion-create','UPDATE'=>'accion-update','DELETE'=>'accion-delete','LOGIN'=>'accion-login','LOGOUT'=>'accion-logout','CONFIG'=>'accion-config']; $cls=$m[strtoupper($a)]??'accion-update'; return '<span class="badge '.$cls.'">'.htmlspecialchars($a).'</span>'; }
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
  <div class="page-header"><h2 class="page-title">Auditorías</h2><p class="page-desc">Registro completo de actividad del sistema: quién ingresó, qué modificó, fecha, hora e IP.</p></div>
  <div class="card">
   <div class="card-header">
    <form method="GET" class="filters-row">
     <div class="filter-group"><label class="filter-label">Buscar</label><input type="text" name="busqueda" class="filter-input" placeholder="Buscar por usuario, acción..." value="<?php echo htmlspecialchars($fBusqueda); ?>"></div>
     <div class="filter-group"><label class="filter-label">Fecha desde</label><input type="date" name="desde" class="filter-input" value="<?php echo htmlspecialchars($fDesde); ?>"></div>
     <div class="filter-group"><label class="filter-label">Fecha hasta</label><input type="date" name="hasta" class="filter-input" value="<?php echo htmlspecialchars($fHasta); ?>"></div>
     <div class="filter-group"><label class="filter-label">Tipo de acción</label><select name="accion" class="filter-input"><option value="">Todas</option><option value="CREATE"<?php echo $fAccion==='CREATE'?' selected':''; ?>>Creación</option><option value="UPDATE"<?php echo $fAccion==='UPDATE'?' selected':''; ?>>Modificación</option><option value="DELETE"<?php echo $fAccion==='DELETE'?' selected':''; ?>>Eliminación</option><option value="LOGIN"<?php echo $fAccion==='LOGIN'?' selected':''; ?>>Inicio de sesión</option><option value="LOGOUT"<?php echo $fAccion==='LOGOUT'?' selected':''; ?>>Cierre de sesión</option><option value="CONFIG"<?php echo $fAccion==='CONFIG'?' selected':''; ?>>Configuración</option></select></div>
     <div class="filter-group filter-group-btn"><button class="btn btn-primary" type="submit">Filtrar</button><a class="btn btn-secondary" href="auditorias.php">Limpiar</a></div>
    </form>
   </div>
   <div class="table-responsive">
    <table class="table">
     <thead><tr><th>Fecha</th><th>Hora</th><th>Usuario</th><th>Rol</th><th>Acción</th><th>Detalle</th><th>IP</th></tr></thead>
     <tbody>
     <?php if(count($lista)>0): foreach($lista as $row): $ts=strtotime($row['fecha_hora']); ?>
      <tr>
       <td><?php echo date('d/m/Y',$ts); ?></td>
       <td><?php echo date('H:i:s',$ts); ?></td>
       <td><?php echo htmlspecialchars(trim($row['usuario'])?:'Sistema'); ?></td>
       <td><?php echo badgeRol($row['rol']); ?></td>
       <td><?php echo badgeAccion($row['accion']); ?></td>
       <td><?php echo htmlspecialchars($row['detalle']??'-'); ?></td>
       <td><?php echo htmlspecialchars($row['ip']??'-'); ?></td>
      </tr>
     <?php endforeach; else: ?>
      <tr><td colspan="7" style="text-align:center;color:var(--tx4)">No hay registros de auditoría</td></tr>
     <?php endif; ?>
     </tbody>
    </table>
   </div>
   <div class="table-footer"><div class="table-info">Mostrando <?php echo count($lista); ?> registros</div></div>
  </div>
 </div>
</div>
<script src="js/admin.js"></script>
</body>
</html>
