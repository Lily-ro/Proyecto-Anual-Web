<?php
session_start();
if(!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'ADMIN'){
    header("Location: ../index.php");
    exit;
}
require_once(__DIR__ . '/../config/db.php');
require_once(__DIR__ . '/../config/mail.php');
$pdo = eva_pdo();
$currentPage='clientes';
$pageSubtitle='Gestión de clientes y credenciales';

$msg=$_GET['msg']??'';
if($_SERVER['REQUEST_METHOD']==='POST'){
 $accion=$_POST['accion']??'';
 if($accion==='crear'){
   $nombre=trim($_POST['nombre']??'');
   $apellido=trim($_POST['apellido']??'');
   $dni=trim($_POST['dni']??'');
   $email=trim($_POST['email']??'');
   $telefono=trim($_POST['telefono']??'');
   $calle=trim($_POST['calle']??'');
   $numero=trim($_POST['numero']??'');
   $codigo_postal=trim($_POST['codigo_postal']??'');
   $localidad=trim($_POST['localidad']??'');
   $provincia=trim($_POST['provincia']??'');
   if($nombre && $apellido && $dni && $email && $calle && $numero && $codigo_postal && $localidad && $provincia){
     try{
       // verificar email no existe en usuarios ni clientes
       $chk=$pdo->prepare("SELECT id_usuario FROM usuarios WHERE email=:e LIMIT 1");
       $chk->execute([':e'=>$email]);
       if($chk->fetch()){ echo '<script>alert("Email ya existe en usuarios");history.back();</script>'; exit; }
       $chk2=$pdo->prepare("SELECT id_cliente FROM clientes WHERE email=:e LIMIT 1");
       $chk2->execute([':e'=>$email]);
       if($chk2->fetch()){ echo '<script>alert("Email ya existe en clientes");history.back();</script>'; exit; }
       // generar password segura
       $passPlano = eva_generar_password(10);
       $hash = password_hash($passPlano, PASSWORD_DEFAULT);
       $pdo->beginTransaction();
       // crear usuario con rol USUARIO
       $pdo->prepare("INSERT INTO usuarios (nombre,apellido,email,password_hash,telefono,activo,id_rol,dni) VALUES (:n,:a,:e,:h,:t,1,(SELECT id_rol FROM roles WHERE nombre='USUARIO' LIMIT 1),:dni)")->execute([':n'=>$nombre,':a'=>$apellido,':e'=>$email,':h'=>$hash,':t'=>$telefono,':dni'=>$dni]);
       $idUsuario = (int)$pdo->lastInsertId();
       // crear cliente
       $pdo->prepare("INSERT INTO clientes (id_usuario,nombre,apellido,dni,email,telefono,calle,numero,codigo_postal,localidad,provincia,pais,activo,credenciales_generadas,fecha_credenciales) VALUES (:uid,:n,:a,:dni,:e,:t,:calle,:num,:cp,:loc,:prov,'Argentina',1,1,NOW())")->execute([':uid'=>$idUsuario,':n'=>$nombre,':a'=>$apellido,':dni'=>$dni,':e'=>$email,':t'=>$telefono,':calle'=>$calle,':num'=>$numero,':cp'=>$codigo_postal,':loc'=>$localidad,':prov'=>$provincia]);
       $idCliente = (int)$pdo->lastInsertId();
       // credenciales_clientes
       $pdo->prepare("INSERT INTO credenciales_clientes (id_cliente,id_usuario,usuario,password_hash,estado,fecha_generacion,fecha_activacion) VALUES (:cid,:uid,:us,:h,'ACTIVA',NOW(),NOW())")->execute([':cid'=>$idCliente,':uid'=>$idUsuario,':us'=>$email,':h'=>$hash]);
        $pdo->prepare("INSERT INTO log_actividad (id_usuario,accion,detalle,ip,fecha_hora) VALUES (:uid,'CREATE',:det,:ip,NOW())")->execute([':uid'=>$_SESSION['id_usuario']??null,':det'=>"Creó cliente {$email} con credenciales",':ip'=>$_SERVER['REMOTE_ADDR']??'']);
        $pdo->commit();
        $extra='Correo enviado.';
        try{ eva_enviar_credenciales($email, $nombre.' '.$apellido, $email, $passPlano); }catch(Throwable $em){ $extra='Cliente creado pero correo no pudo enviarse: '.htmlspecialchars($em->getMessage()); error_log('crear cliente mail: '.$em->getMessage()); }
        echo '<script>alert("Cliente y credenciales creados. '.$extra.'");window.location="clientes.php";</script>'; exit;
     }catch(Throwable $e){
       if($pdo->inTransaction()) $pdo->rollBack();
       error_log('crear cliente: '.$e->getMessage());
       echo '<script>alert("Error al crear cliente: '.htmlspecialchars($e->getMessage()).'");history.back();</script>'; exit;
     }
   } else { echo '<script>alert("Complete todos los campos obligatorios");history.back();</script>'; exit; }
 }
 if($accion==='toggle'){
   $id=(int)($_POST['id_cliente']??0); $activo=(int)($_POST['activo']??1);
   if($id){ $pdo->prepare("UPDATE clientes SET activo=:a WHERE id_cliente=:id")->execute([':a'=>$activo,':id'=>$id]); header("Location: clientes.php"); exit; }
 }
}

$fBusqueda=trim($_GET['busqueda']??'');
$where=''; $params=[];
if($fBusqueda!==''){ $where="WHERE c.nombre LIKE :b OR c.apellido LIKE :b OR c.email LIKE :b OR c.dni LIKE :b"; $params[':b']="%{$fBusqueda}%"; }
$st=$pdo->prepare("SELECT c.*, CONCAT(c.nombre,' ',c.apellido) AS fullname, u.email AS usuario_email FROM clientes c LEFT JOIN usuarios u ON c.id_usuario=u.id_usuario {$where} ORDER BY c.fecha_alta DESC");
$st->execute($params);
$lista=$st->fetchAll(PDO::FETCH_ASSOC);
function badgeActivo($a){ return $a?'<span class="badge activo">Activo</span>':'<span class="badge inactivo">Inactivo</span>'; }
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>EVA - Clientes</title>
<link rel="stylesheet" href="css/admin.css">
</head>
<body>
<?php include __DIR__ . '/includes/sidebar.php'; ?>
<div class="main">
 <?php include __DIR__ . '/includes/header.php'; ?>
 <div class="content">
  <div class="page-header"><h2 class="page-title">Clientes</h2><p class="page-desc">Gestión de clientes, credenciales y accesos.</p></div>
  <?php if($msg==='ok'): ?><div style="background:#dcfce7;color:#166534;padding:10px;border-radius:8px;margin-bottom:12px">Operación realizada.</div><?php endif; ?>
  <div class="card">
   <div class="card-header">
    <form method="GET" class="filters-row">
     <div class="filter-group"><input type="text" name="busqueda" class="filter-input" placeholder="Buscar por nombre, email o DNI..." value="<?php echo htmlspecialchars($fBusqueda); ?>"></div>
     <div class="filter-group"><button type="submit" class="btn btn-primary">Filtrar</button></div>
     <div class="filter-group filter-group-btn"><button type="button" class="btn btn-primary" onclick="document.getElementById('modalCliente').classList.add('active')">+ Nuevo cliente</button></div>
    </form>
   </div>
   <div class="table-responsive">
    <table class="table">
     <thead><tr><th>Cliente</th><th>DNI</th><th>Email</th><th>Teléfono</th><th>Dirección</th><th>Estado</th><th>Credenciales</th><th>Acciones</th></tr></thead>
     <tbody>
     <?php if(count($lista)>0): foreach($lista as $c): ?>
      <tr>
       <td><?php echo htmlspecialchars($c['fullname']); ?></td>
       <td><?php echo htmlspecialchars($c['dni']); ?></td>
       <td><?php echo htmlspecialchars($c['email']); ?></td>
       <td><?php echo htmlspecialchars($c['telefono']??'-'); ?></td>
       <td><?php echo htmlspecialchars($c['calle'].' '.$c['numero'].', '.$c['localidad']); ?></td>
       <td><?php echo badgeActivo($c['activo']); ?></td>
       <td><?php echo $c['credenciales_generadas']?'<span class="badge activo">Generadas</span>':'<span class="badge pendiente">Pendiente</span>'; ?></td>
       <td class="actions-cell">
        <form method="POST" style="display:inline"><input type="hidden" name="accion" value="toggle"><input type="hidden" name="id_cliente" value="<?php echo (int)$c['id_cliente']; ?>"><input type="hidden" name="activo" value="<?php echo $c['activo']?0:1; ?>"><button class="btn-icon" title="<?php echo $c['activo']?'Desactivar':'Activar'; ?>"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/></svg></button></form>
       </td>
      </tr>
     <?php endforeach; else: ?>
      <tr><td colspan="8" style="text-align:center;color:var(--tx4)">No hay clientes registrados</td></tr>
     <?php endif; ?>
     </tbody>
    </table>
   </div>
  </div>
 </div>
</div>

<div class="modal-overlay" id="modalCliente">
 <div class="modal" style="max-width:620px">
  <div class="modal-header"><h3 class="modal-title">Nuevo cliente</h3><button class="modal-close" onclick="document.getElementById('modalCliente').classList.remove('active')">&times;</button></div>
  <div class="modal-body">
   <form id="formCliente" method="POST" action="clientes.php">
    <input type="hidden" name="accion" value="crear">
    <div class="form-row"><div class="form-group"><label class="form-label">Nombre *</label><input type="text" name="nombre" class="form-input" required></div><div class="form-group"><label class="form-label">Apellido *</label><input type="text" name="apellido" class="form-input" required></div></div>
    <div class="form-row"><div class="form-group"><label class="form-label">DNI *</label><input type="text" name="dni" class="form-input" required></div><div class="form-group"><label class="form-label">Email *</label><input type="email" name="email" class="form-input" required></div></div>
    <div class="form-group"><label class="form-label">Teléfono</label><input type="text" name="telefono" class="form-input" placeholder="Ej: 11 3622-6501"></div>
    <div class="form-row"><div class="form-group"><label class="form-label">Calle *</label><input type="text" name="calle" class="form-input" required></div><div class="form-group"><label class="form-label">Número *</label><input type="text" name="numero" class="form-input" required></div></div>
    <div class="form-row"><div class="form-group"><label class="form-label">Código postal *</label><input type="text" name="codigo_postal" class="form-input" required></div><div class="form-group"><label class="form-label">Localidad *</label><input type="text" name="localidad" class="form-input" required></div></div>
    <div class="form-group"><label class="form-label">Provincia *</label><input type="text" name="provincia" class="form-input" value="Buenos Aires" required></div>
    <p style="font-size:12px;color:#64748b">Se generará automáticamente usuario y contraseña segura. Las credenciales se enviarán a <strong>no-reply@elvigilantedeagua.com</strong> vía PHPMailer y se guardarán con <code>password_hash</code>.</p>
   </form>
  </div>
  <div class="modal-footer"><button class="btn btn-secondary" onclick="document.getElementById('modalCliente').classList.remove('active')">Cancelar</button><button class="btn btn-primary" onclick="document.getElementById('formCliente').submit()">Crear cliente</button></div>
 </div>
</div>
<script src="js/admin.js"></script>
</body>
</html>
