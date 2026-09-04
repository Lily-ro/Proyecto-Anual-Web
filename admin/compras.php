<?php
session_start();
if(!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'ADMIN'){
    header("Location: ../index.php");
    exit;
}
require_once(__DIR__ . '/../config/db.php');
require_once(__DIR__ . '/../config/mail.php');
$pdo = eva_pdo();
$currentPage = 'compras';
$pageSubtitle = 'Gestión de compras del sistema';

if($_SERVER['REQUEST_METHOD']==='POST'){
    $accion = $_POST['accion']??'';
    $id = (int)($_POST['id_compra']??0);
    $estados = ['aprobar'=>'Aprobada','entrega'=>'En entrega','completada'=>'Completada','cancelar'=>'Cancelada','pendiente'=>'Pendiente'];
    if($id && isset($estados[$accion])){
        $nuevo = $estados[$accion];
        try{
            $st=$pdo->prepare("SELECT estado,codigo_compra,id_cliente,id_solicitante FROM compras WHERE id_compra=:id LIMIT 1");
            $st->execute([':id'=>$id]);
            $row=$st->fetch();
            if($row){
                $anterior=$row['estado'];
                $pdo->prepare("UPDATE compras SET estado=:nuevo, updated_at=NOW() WHERE id_compra=:id")->execute([':nuevo'=>$nuevo,':id'=>$id]);
                if($nuevo==='Aprobada') $pdo->prepare("UPDATE compras SET fecha_aprobacion=NOW() WHERE id_compra=:id")->execute([':id'=>$id]);
                if($nuevo==='En entrega') $pdo->prepare("UPDATE compras SET fecha_entrega=NOW() WHERE id_compra=:id")->execute([':id'=>$id]);
                if($nuevo==='Completada') $pdo->prepare("UPDATE compras SET fecha_completada=NOW() WHERE id_compra=:id")->execute([':id'=>$id]);
                if($nuevo==='Cancelada') $pdo->prepare("UPDATE compras SET fecha_cancelacion=NOW() WHERE id_compra=:id")->execute([':id'=>$id]);
                $pdo->prepare("INSERT INTO historial_compras (id_compra,id_usuario,estado_anterior,estado_nuevo,comentario,fecha_hora) VALUES (:c,:u,:ant,:nue,:com,NOW())")->execute([':c'=>$id,':u'=>$_SESSION['id_usuario']??null,':ant'=>$anterior,':nue'=>$nuevo,':com'=>$_POST['observaciones']??"Cambio a {$nuevo}"]);
                $pdo->prepare("INSERT INTO notificaciones_compras (id_cliente,id_usuario,id_compra,tipo,mensaje,enviada,leida) SELECT id_cliente,id_solicitante,:id,:tipo,:msg,0,0 FROM compras WHERE id_compra=:id2")->execute([':id'=>$id,':id2'=>$id,':tipo'=>$nuevo,':msg'=>"Compra {$row['codigo_compra']} pasó a {$nuevo}"]);
                $pdo->prepare("INSERT INTO log_actividad (id_usuario,accion,detalle,ip,fecha_hora) VALUES (:uid,'UPDATE',:det,:ip,NOW())")->execute([':uid'=>$_SESSION['id_usuario']??null,':det'=>"Compra {$id} {$anterior} -> {$nuevo}",':ip'=>$_SERVER['REMOTE_ADDR']??'']);
                if($nuevo==='Aprobada' && !empty($row['id_cliente'])){
                    try{
                        $cli=$pdo->prepare("SELECT id_cliente,id_usuario,nombre,apellido,dni,email,telefono FROM clientes WHERE id_cliente=:cid LIMIT 1");
                        $cli->execute([':cid'=>$row['id_cliente']]);
                        $c=$cli->fetch();
                        if($c && !empty($c['email'])){
                            $emailCli=trim($c['email']);
                            $chkU=$pdo->prepare("SELECT id_usuario FROM usuarios WHERE email=:e LIMIT 1");
                            $chkU->execute([':e'=>$emailCli]);
                            $uExist=$chkU->fetch();
                            $chkC=$pdo->prepare("SELECT id_credencial FROM credenciales_clientes WHERE id_cliente=:cid LIMIT 1");
                            $chkC->execute([':cid'=>$c['id_cliente']]);
                            $credExist=$chkC->fetch();
                            if(!$uExist && !$credExist){
                                $passPlano=eva_generar_password(10);
                                $hash=password_hash($passPlano,PASSWORD_DEFAULT);
                                $pdo->prepare("INSERT INTO usuarios (nombre,apellido,email,password_hash,telefono,activo,id_rol,dni) VALUES (:n,:a,:e,:h,:t,1,(SELECT id_rol FROM roles WHERE nombre='USUARIO' LIMIT 1),:dni)")->execute([':n'=>$c['nombre'],':a'=>$c['apellido'],':e'=>$emailCli,':h'=>$hash,':t'=>$c['telefono'],':dni'=>$c['dni']]);
                                $newUid=(int)$pdo->lastInsertId();
                                $pdo->prepare("UPDATE clientes SET id_usuario=:uid, credenciales_generadas=1, fecha_credenciales=NOW() WHERE id_cliente=:cid")->execute([':uid'=>$newUid,':cid'=>$c['id_cliente']]);
                                $pdo->prepare("INSERT INTO credenciales_clientes (id_cliente,id_usuario,usuario,password_hash,estado,fecha_generacion,fecha_activacion) VALUES (:cid,:uid,:us,:h,'ACTIVA',NOW(),NOW())")->execute([':cid'=>$c['id_cliente'],':uid'=>$newUid,':us'=>$emailCli,':h'=>$hash]);
                                try{ eva_enviar_credenciales($emailCli, $c['nombre'].' '.$c['apellido'], $emailCli, $passPlano); $pdo->prepare("UPDATE notificaciones_compras SET enviada=1, fecha_envio=NOW() WHERE id_compra=:id")->execute([':id'=>$id]); }catch(Throwable $em){ error_log("EVA compras mail a {$emailCli} id_compra {$id}: ".$em->getMessage()); }
                                $pdo->prepare("INSERT INTO notificaciones (id_usuario,mensaje,leida) VALUES (:uid,:msg,0)")->execute([':uid'=>$newUid,':msg'=>"Tus credenciales fueron generadas al aprobar la compra {$row['codigo_compra']}"]);
                            }
                        }
                    }catch(Throwable $e2){ error_log('EVA compras credenciales: '.$e2->getMessage()); }
                }
            }
        }catch(Throwable $e){ error_log($e->getMessage()); }
        header("Location: compras.php?msg=ok"); exit;
    }
}

function badgeEstadoCompra($e){
 $m=['Pendiente'=>'pendiente','Aprobada'=>'aprobada','En entrega'=>'en_camino','Completada'=>'completada','Cancelada'=>'cancelada'];
 $cls=$m[$e]??'pendiente';
 return '<span class="badge '.htmlspecialchars($cls).'">'.htmlspecialchars($e).'</span>';
}
$cntPend = (int)$pdo->query("SELECT COUNT(*) FROM compras WHERE estado='Pendiente'")->fetchColumn();
$cntAprob = (int)$pdo->query("SELECT COUNT(*) FROM compras WHERE estado='Aprobada'")->fetchColumn();
$cntEntrega = (int)$pdo->query("SELECT COUNT(*) FROM compras WHERE estado='En entrega'")->fetchColumn();
$cntComp = (int)$pdo->query("SELECT COUNT(*) FROM compras WHERE estado='Completada'")->fetchColumn();

// filtros
$fBusqueda=trim($_GET['busqueda']??'');
$fEstado=$_GET['estado']??'';
$where=[]; $params=[];
if($fBusqueda!==''){ $where[]="(c.codigo_compra LIKE :b OR p.nombre LIKE :b2 OR prov.nombre LIKE :b3)"; $params[':b']="%{$fBusqueda}%"; $params[':b2']="%{$fBusqueda}%"; $params[':b3']="%{$fBusqueda}%"; }
if($fEstado!=='' && in_array($fEstado,['Pendiente','Aprobada','En entrega','Completada','Cancelada'])){ $where[]="c.estado=:est"; $params[':est']=$fEstado; }
$whereSQL=$where?'WHERE '.implode(' AND ',$where):'';
$sql="SELECT c.id_compra,c.codigo_compra,c.fecha,c.cantidad,c.precio_unitario,c.total,c.estado, p.nombre AS producto, prov.nombre AS proveedor, CONCAT(u.nombre,' ',u.apellido) AS solicitante FROM compras c LEFT JOIN productos p ON c.id_producto=p.id_producto LEFT JOIN proveedores prov ON c.id_proveedor=prov.id_proveedor LEFT JOIN usuarios u ON c.id_solicitante=u.id_usuario {$whereSQL} ORDER BY c.created_at DESC LIMIT 50";
$st=$pdo->prepare($sql); $st->execute($params); $lista=$st->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>EVA - Gestión de Compras</title>
<link rel="stylesheet" href="css/admin.css">
</head>
<body>
<?php include __DIR__ . '/includes/sidebar.php'; ?>
<div class="main">
 <?php include __DIR__ . '/includes/header.php'; ?>
 <div class="content">
  <div class="page-header">
   <h2 class="page-title">Gestión de Compras</h2>
   <p class="page-desc">Visualizar, aprobar, cancelar y gestionar estados de compras.</p>
  </div>
  <div class="stats-row stats-row-4">
   <div class="stat-card anim-bounce0"><div class="stat-card-icon orange"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg></div><div class="stat-card-info"><div class="stat-card-title">Pendientes</div><div class="stat-card-value"><?php echo $cntPend; ?></div><div class="stat-card-sub">Requieren aprobación</div></div></div>
   <div class="stat-card anim-bounce1"><div class="stat-card-icon green"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg></div><div class="stat-card-info"><div class="stat-card-title">Aprobadas</div><div class="stat-card-value"><?php echo $cntAprob; ?></div><div class="stat-card-sub">Este mes</div></div></div>
   <div class="stat-card anim-bounce2"><div class="stat-card-icon blue"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg></div><div class="stat-card-info"><div class="stat-card-title">En entrega</div><div class="stat-card-value"><?php echo $cntEntrega; ?></div><div class="stat-card-sub">En camino</div></div></div>
   <div class="stat-card anim-bounce3"><div class="stat-card-icon cyan"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg></div><div class="stat-card-info"><div class="stat-card-title">Completadas</div><div class="stat-card-value"><?php echo $cntComp; ?></div><div class="stat-card-sub">Total</div></div></div>
  </div>
  <div class="card">
   <div class="card-header">
    <form method="GET" class="filters-row">
     <div class="filter-group"><input type="text" name="busqueda" class="filter-input" placeholder="Buscar por código o producto..." value="<?php echo htmlspecialchars($fBusqueda); ?>"></div>
     <div class="filter-group"><select name="estado" class="filter-input"><option value="">Todos los estados</option><option value="Pendiente"<?php echo $fEstado==='Pendiente'?' selected':''; ?>>Pendiente</option><option value="Aprobada"<?php echo $fEstado==='Aprobada'?' selected':''; ?>>Aprobada</option><option value="En entrega"<?php echo $fEstado==='En entrega'?' selected':''; ?>>En entrega</option><option value="Completada"<?php echo $fEstado==='Completada'?' selected':''; ?>>Completada</option><option value="Cancelada"<?php echo $fEstado==='Cancelada'?' selected':''; ?>>Cancelada</option></select></div>
     <div class="filter-group"><button type="submit" class="btn btn-primary">Filtrar</button></div>
    </form>
   </div>
   <div class="table-responsive">
    <table class="table">
     <thead><tr><th>ID</th><th>Fecha</th><th>Proveedor</th><th>Artículo</th><th>Cantidad</th><th>Total</th><th>Solicitante</th><th>Estado</th><th>Acciones</th></tr></thead>
     <tbody>
     <?php if(count($lista)>0): foreach($lista as $c): ?>
      <tr>
       <td><?php echo htmlspecialchars($c['codigo_compra']); ?></td>
       <td><?php echo htmlspecialchars($c['fecha']); ?></td>
       <td><?php echo htmlspecialchars($c['proveedor']??'-'); ?></td>
       <td><?php echo htmlspecialchars($c['producto']??'-'); ?></td>
       <td><?php echo (int)$c['cantidad']; ?></td>
       <td>$<?php echo number_format((float)$c['total'],2,',','.'); ?></td>
       <td><?php echo htmlspecialchars($c['solicitante']??'-'); ?></td>
       <td><?php echo badgeEstadoCompra($c['estado']); ?></td>
       <td class="actions-cell">
        <?php if($c['estado']==='Pendiente'): ?>
         <form method="POST" style="display:inline"><input type="hidden" name="id_compra" value="<?php echo (int)$c['id_compra']; ?>"><input type="hidden" name="accion" value="aprobar"><button class="btn btn-sm btn-success" type="submit">Aprobar</button></form>
         <form method="POST" style="display:inline"><input type="hidden" name="id_compra" value="<?php echo (int)$c['id_compra']; ?>"><input type="hidden" name="accion" value="cancelar"><button class="btn btn-sm btn-danger" type="submit">Cancelar</button></form>
        <?php elseif($c['estado']==='Aprobada'): ?>
         <form method="POST" style="display:inline"><input type="hidden" name="id_compra" value="<?php echo (int)$c['id_compra']; ?>"><input type="hidden" name="accion" value="entrega"><button class="btn btn-sm btn-info" type="submit">En entrega</button></form>
        <?php elseif($c['estado']==='En entrega'): ?>
         <form method="POST" style="display:inline"><input type="hidden" name="id_compra" value="<?php echo (int)$c['id_compra']; ?>"><input type="hidden" name="accion" value="completada"><button class="btn btn-sm btn-success" type="submit">Completar</button></form>
        <?php endif; ?>
       </td>
      </tr>
     <?php endforeach; else: ?>
      <tr><td colspan="9" style="text-align:center;color:var(--tx4)">No hay compras registradas</td></tr>
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
