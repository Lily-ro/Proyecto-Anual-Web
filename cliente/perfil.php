<?php
session_start();
if(!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'USUARIO'){
    header("Location: ../index.php");
    exit;
}
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/includes/helpers.php';

$msgOk=''; $msgErr='';
if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['cambiar_pass'])){
    $actual = $_POST['actual'] ?? '';
    $nueva = $_POST['nueva'] ?? '';
    $confirm = $_POST['confirm'] ?? '';
    if($actual==='' || $nueva==='' || $confirm===''){ $msgErr='Completá todos los campos.'; }
    elseif($nueva !== $confirm){ $msgErr='La nueva contraseña y su confirmación no coinciden.'; }
    elseif(mb_strlen($nueva) < 8){ $msgErr='La nueva contraseña debe tener al menos 8 caracteres.'; }
    elseif(!preg_match('/[A-Za-z]/',$nueva) || !preg_match('/[0-9]/',$nueva)){ $msgErr='La contraseña debe contener letras y números.'; }
    elseif($actual === $nueva){ $msgErr='La nueva contraseña no puede ser igual a la actual.'; }
    else {
        try{
            $pdo=eva_pdo();
            $uid=eva_current_user_id();
            $st=$pdo->prepare("SELECT password_hash FROM usuarios WHERE id_usuario=:id LIMIT 1");
            $st->execute([':id'=>$uid]);
            $row=$st->fetch();
            if(!$row || !password_verify($actual, $row['password_hash'])){ $msgErr='La contraseña actual es incorrecta.'; }
            else {
                $hash=password_hash($nueva, PASSWORD_DEFAULT);
                $pdo->prepare("UPDATE usuarios SET password_hash=:h WHERE id_usuario=:id")->execute([':h'=>$hash,':id'=>$uid]);
                try{ eva_log_actividad($pdo,(int)$uid,'UPDATE','Cambió su contraseña'); }catch(Throwable $e){}
                $msgOk='Contraseña actualizada correctamente.';
            }
        }catch(Throwable $e){ $msgErr='Error interno. Intentá nuevamente.'; error_log('cambiar_pass: '.$e->getMessage()); }
    }
}

$perfil = [
    'nombre' => $_SESSION['nombre'] ?? 'Usuario',
    'apellido' => $_SESSION['apellido'] ?? '',
    'email' => $_SESSION['email'] ?? '-',
    'rol' => $_SESSION['rol'] ?? 'USUARIO',
    'ultimo_acceso' => null,
    'telefono' => null,
    'direccion' => null,
    'dni' => null,
    'usuario' => null,
    'fecha_registro' => null,
    'cliente' => null,
];
try {
    $pdo = eva_pdo();
    $uid = eva_current_user_id();
    if ($uid) {
        $st = $pdo->prepare("SELECT u.*, r.nombre AS rol_nombre FROM usuarios u LEFT JOIN roles r ON r.id_rol = u.id_rol WHERE u.id_usuario = :id LIMIT 1");
        $st->execute([':id'=>$uid]);
        $row = $st->fetch();
        if ($row) {
            $perfil['nombre'] = $row['nombre'] ?? $perfil['nombre'];
            $perfil['apellido'] = $row['apellido'] ?? $perfil['apellido'];
            $perfil['email'] = $row['email'] ?? $perfil['email'];
            $perfil['usuario'] = $row['email'] ?? $perfil['email'];
            $perfil['rol'] = $row['rol_nombre'] ?? $perfil['rol'];
            $perfil['ultimo_acceso'] = $row['ultimo_acceso'] ?? null;
            $perfil['telefono'] = $row['telefono'] ?? null;
            $perfil['direccion'] = $row['direccion'] ?? null;
            $perfil['dni'] = $row['dni'] ?? null;
            $perfil['fecha_registro'] = $row['fecha_registro'] ?? null;
            $_SESSION['nombre'] = $perfil['nombre'];
            $_SESSION['apellido'] = $perfil['apellido'];
            $_SESSION['email'] = $perfil['email'];
        }
        $st2=$pdo->prepare("SELECT * FROM clientes WHERE id_usuario=:uid LIMIT 1");
        $st2->execute([':uid'=>$uid]);
        $cli=$st2->fetch();
        if($cli){
            $perfil['cliente']=$cli;
            $perfil['telefono'] = $cli['telefono'] ?? $perfil['telefono'];
            $perfil['dni'] = $cli['dni'] ?? $perfil['dni'];
            $parts=[];
            if(!empty($cli['calle'])) $parts[] = trim($cli['calle'].' '.$cli['numero'].(!empty($cli['piso'])?' Piso '.$cli['piso']:''));
            if(!empty($cli['localidad'])) $parts[]=$cli['localidad'];
            if(!empty($cli['provincia'])) $parts[]=$cli['provincia'];
            if($parts) $perfil['direccion']=implode(', ',$parts);
            if(!empty($cli['codigo_postal'])) $perfil['codigo_postal']=$cli['codigo_postal'];
        }
    }
} catch (Throwable $e) { error_log('perfil error: '.$e->getMessage()); }
$iniciales = strtoupper(substr($perfil['nombre'] ?? 'U',0,1) . substr($perfil['apellido'] ?? '',0,1));
if (trim($iniciales)==='') $iniciales = strtoupper(substr($perfil['nombre'] ?? 'U',0,2));
$deviceStatus='Conectado';
try{ $pdo=eva_pdo(); $uid=eva_current_user_id(); $t=eva_first_tanque($pdo,$uid); if($t) $deviceStatus=eva_device_status($pdo,(int)$t['id_tanque']); }catch(Throwable $e){}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>EVA - Mi Perfil</title>
<link rel="stylesheet" href="css/style.css">
<<<<<<< HEAD
<style>.pass-wrap{position:relative}.pass-wrap input{padding-right:42px}.pass-eye{position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--tx4);display:flex}.msg-ok{background:rgba(76,175,80,0.12);border:1px solid rgba(76,175,80,0.2);color:var(--gn);padding:12px 16px;border-radius:8px;margin-bottom:16px;font-size:13px}.msg-err{background:rgba(244,67,54,0.12);border:1px solid rgba(244,67,54,0.2);color:var(--rd);padding:12px 16px;border-radius:8px;margin-bottom:16px;font-size:13px}</style>
=======
<style>
.header-left{display:flex;flex-direction:column}
.header-greeting{font-size:22px;font-weight:700;color:var(--tx2)}
.header-subtitle{font-size:13px;color:var(--tx4);margin-top:2px}
.content{padding:24px 30px;flex:1}
.content-card{background:var(--bg2);border:1px solid var(--bd);border-radius:14px;padding:28px 32px}
.content-divider{height:1px;background:var(--bd);margin-bottom:20px}
.profile-avatar{width:72px;height:72px;border-radius:50%;background:linear-gradient(135deg,#3b82f6,#06b6d4);display:flex;align-items:center;justify-content:center;font-size:28px;font-weight:700;color:#fff;flex-shrink:0}
.profile-header{display:flex;align-items:center;gap:20px;padding:24px 28px}
.profile-info{display:flex;flex-direction:column;gap:4px}
.profile-name{font-size:20px;font-weight:700;color:var(--tx)}
.profile-role{font-size:13px;color:var(--tx4)}
.profile-section{padding:0 28px 20px}
.profile-section-title{font-size:14px;font-weight:700;color:var(--tx);margin-bottom:8px}
.profile-field{display:flex;justify-content:space-between;padding:12px 0;border-bottom:1px solid var(--bd)}
.profile-field-label{font-size:13px;color:var(--tx4)}
.profile-field-value{font-size:13px;font-weight:500;color:var(--tx2)}
.actions-row{display:flex;gap:12px;padding:20px 28px}
.btn-primary{padding:10px 24px;border:none;background:var(--ac);color:#fff;border-radius:8px;font-size:13px;font-weight:500;cursor:pointer;transition:all .2s}
.btn-primary:hover{opacity:.9;transform:translateY(-1px)}
.btn-outline{padding:10px 24px;border:1px solid var(--bd3);background:transparent;color:var(--tx2);border-radius:8px;font-size:13px;font-weight:500;cursor:pointer;transition:all .2s}
.btn-outline:hover{border-color:var(--ac);color:var(--ac)}
body.light-theme .profile-field{border-color:#e0e3e8}
body.light-theme .content-card{background:#fff;border-color:#e0e3e8}
body.light-theme .content-divider{background:#e0e3e8}
body.light-theme .profile-section-title{color:#1a1f2e}
body.light-theme .profile-field-label{color:#6b7280}
body.light-theme .profile-field-value{color:#1a1f2e}
</style>
>>>>>>> ad7f7b988ee33fa7a2a84311d3fa13c5b3715ab0
</head>
<body>
<aside class="sidebar">
 <a href="indexcli.php" class="sidebar-logo anim-float">
  <svg class="logo-svg" width="37" height="53" viewBox="0 0 37 53" fill="none" xmlns="http://www.w3.org/2000/svg">
   <circle cx="26.9785" cy="43.3208" r="3" fill="#3C75C6"/>
   <path d="M22.2598 51.4631C22.5628 51.3284 22.7998 51.0789 22.9188 50.7695C23.0378 50.4601 23.029 50.1161 22.8944 49.8131C22.7598 49.5102 22.5103 49.2731 22.2009 49.1541C21.8914 49.0351 21.5474 49.0439 21.2445 49.1785C19.8615 49.7947 18.1704 49.91 16.5293 49.6893C10.6749 48.8403 5.25149 44.4313 3.33478 38.7933C1.31772 33.0838 3.17894 26.6965 6.53436 21.4902C7.44919 20.0474 8.37331 18.6266 9.31541 17.209C9.93643 16.2742 10.5628 15.3443 11.1927 14.4131C11.8239 13.4796 12.4563 12.5481 13.0865 11.608C15.1972 8.47603 17.2131 5.24513 19.068 1.93363L16.9447 2.02347C21.7025 9.02347 26.4603 16.0235 31.2181 23.0235L31.1919 22.9833C32.9909 26.0258 33.9913 29.8118 33.9443 33.4127C33.9171 35.1216 33.6357 36.8141 33.0732 38.4048C32.9628 38.7174 32.9812 39.061 33.1242 39.3601C33.2673 39.6592 33.5233 39.8892 33.8359 39.9995C34.1485 40.1099 34.4921 40.0915 34.7912 39.9485C35.0903 39.8054 35.3203 39.5494 35.4306 39.2368C36.0899 37.3732 36.4132 35.4047 36.444 33.4537C36.4729 29.3045 35.4424 25.2908 33.3119 21.6583L33.2857 21.6181C28.5279 14.6181 23.7701 7.61813 19.0123 0.618135C18.4281 -0.241428 17.3986 -0.197868 16.889 0.707974C15.062 3.96876 13.1013 7.11201 11.0098 10.216C10.3852 11.1479 9.75453 12.0769 9.12179 13.0126C8.49053 13.9458 7.85971 14.8823 7.23314 15.8255C6.28253 17.2559 5.34771 18.6931 4.4231 20.1514C0.849809 25.6877 -1.38458 32.9081 0.971117 39.6076C3.26484 46.2002 9.2809 51.1272 16.1989 52.1674C18.1639 52.426 20.2912 52.3304 22.2598 51.4631Z" fill="#3C75C6"/>
  </svg>
  <span>EVA</span>
 </a>
 <nav>
  <ul>
<<<<<<< HEAD
   <li class="anim-slide1"><a href="indexcli.php"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg><span>Resumen</span></a></li>
   <li class="anim-slide2"><a href="mitanque.php"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="2" width="16" height="20" rx="2"/><line x1="4" y1="18" x2="20" y2="18"/><rect x="7" y="12" width="10" height="6" rx="1" fill="currentColor" opacity="0.3"/></svg><span>Mi Tanque</span></a></li>
   <li class="anim-slide3"><a href="alertas.php"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg><span>Alertas</span></a></li>
   <li class="anim-slide4"><a href="configuracion.php"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 01-2.83 2.83l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z"/></svg><span>Configuración</span></a></li>
   <li class="anim-slide5"><a href="historial.php"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg><span>Historial</span></a></li>
   <li class="anim-slide6"><a href="mantenimiento.php"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91a6 6 0 017.94-7.94l-3.76 3.76z"/></svg><span>Mantenimiento</span></a></li>
=======
    <li class="anim-slide1"><a href="indexcli.php"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg><span>Resumen</span></a></li>
    <li class="anim-slide2"><a href="mitanque.php"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="2" width="16" height="20" rx="2"/><line x1="4" y1="18" x2="20" y2="18"/><rect x="7" y="12" width="10" height="6" rx="1" fill="currentColor" opacity="0.3"/></svg><span>Mi Tanque</span></a></li>
    <li class="anim-slide3"><a href="alertas.php"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg><span>Alertas</span></a></li>
    <li class="anim-slide4"><a href="configuracion.php"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 01-2.83 2.83l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z"/></svg><span>Configuración</span></a></li>
    <li class="anim-slide5"><a href="historial.php"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg><span>Historial</span></a></li>
    <li class="anim-slide6"><a href="mantenimiento.php"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91a6 6 0 017.94-7.94l-3.76 3.76z"/></svg><span>Mantenimiento</span></a></li>
>>>>>>> ad7f7b988ee33fa7a2a84311d3fa13c5b3715ab0
  </ul>
 </nav>
 <div class="device-status" style="<?php echo $deviceStatus==='Conectado' ? 'background:rgba(76,175,80,0.08);border-color:rgba(76,175,80,0.15)' : 'background:rgba(244,67,54,0.08);border-color:rgba(244,67,54,0.15)'; ?>">
  <h4>Dispositivo</h4>
  <div class="status-row">
   <svg class="wifi-icon anim-pulse" viewBox="0 0 24 24" fill="none" stroke="<?php echo $deviceStatus==='Conectado' ? '#4caf50' : '#f44336'; ?>" stroke-width="2"><path d="M5 12.55a11 11 0 0114.08 0"/><path d="M1.42 9a16 16 0 0121.16 0"/><path d="M8.53 16.11a6 6 0 016.95 0"/><line x1="12" y1="20" x2="12.01" y2="20"/></svg>
    <span class="status-text" style="color:<?php echo $deviceStatus==='Conectado' ? '#4caf50' : '#f44336'; ?>;font-weight:700"><?php echo $deviceStatus==='Conectado' ? 'Conectado' : 'Desconectado'; ?></span>
  </div>
 </div>
</aside>

<div class="main">
 <header class="header">
   <div class="header-left">
    <div class="header-greeting">Mi Perfil</div>
    <div class="header-subtitle">Información personal y configuración de cuenta</div>
   </div>
  <div class="header-right">
   <button class="theme-btn" id="themeToggle" title="Cambiar tema">
    <svg class="icon-sun" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
    <svg class="icon-moon hidden" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
   </button>
   <div class="user-dropdown" id="userDropdown">
    <div class="user-info">
     <div class="user-details"><div class="user-name"><?php echo h($perfil['nombre'] ?? 'Usuario'); ?></div><div class="user-role">Cliente</div></div>
     <div class="user-avatar"><?php echo h($iniciales); ?></div>
     <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#7a829a" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
    </div>
    <div class="user-menu hidden" id="userMenu">
     <a class="user-menu-item" href="perfil.php">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
      <span>Mi perfil</span>
     </a>
<<<<<<< HEAD
     <a class="user-menu-item" href="configuracion.php">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z"/></svg>
      <span>Configuración</span>
     </a>
     <a class="user-menu-item" href="../config/logout.php">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
      <span>Cerrar sesión</span>
     </a>
=======
      <a class="user-menu-item" href="../config/logout.php">
       <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
       <span>Cerrar sesión</span>
      </a>
>>>>>>> ad7f7b988ee33fa7a2a84311d3fa13c5b3715ab0
    </div>
   </div>
  </div>
 </header>

<<<<<<< HEAD
 <div class="view active">
  <?php if($msgOk): ?><div class="msg-ok"><?php echo h($msgOk); ?></div><?php endif; ?>
  <?php if($msgErr): ?><div class="msg-err"><?php echo h($msgErr); ?></div><?php endif; ?>
  <div class="content-card anim-bounce0" style="margin-bottom:20px">
=======
 <div class="content">
  <div class="content-card anim-bounce0">
>>>>>>> ad7f7b988ee33fa7a2a84311d3fa13c5b3715ab0
   <div class="profile-header">
    <div class="profile-avatar"><?php echo h($iniciales); ?></div>
    <div class="profile-info">
     <div class="profile-name"><?php echo h(trim(($perfil['nombre']??'').' '.($perfil['apellido']??''))) ?: 'Usuario'; ?></div>
     <div class="profile-role"><?php echo h($perfil['rol'] ?? 'Cliente'); ?></div>
<<<<<<< HEAD
     <?php if (!empty($perfil['ultimo_acceso'])): ?><div style="font-size:12px;color:var(--tx4);margin-top:4px">Último acceso: <?php echo h(date('d/m/Y H:i', strtotime($perfil['ultimo_acceso']))); ?></div><?php endif; ?>
     <?php if (!empty($perfil['fecha_registro'])): ?><div style="font-size:12px;color:var(--tx4)">Miembro desde: <?php echo h(date('d/m/Y', strtotime($perfil['fecha_registro']))); ?></div><?php endif; ?>
=======
>>>>>>> ad7f7b988ee33fa7a2a84311d3fa13c5b3715ab0
    </div>
   </div>
   <div class="content-divider"></div>
   <div class="profile-section">
    <div class="profile-section-title">Datos personales</div>
    <div class="profile-field"><div class="profile-field-label">Nombre</div><div class="profile-field-value"><?php echo h($perfil['nombre'] ?? '-'); ?></div></div>
    <div class="profile-field"><div class="profile-field-label">Apellido</div><div class="profile-field-value"><?php echo h($perfil['apellido'] ?? '-'); ?></div></div>
    <?php if(!empty($perfil['dni'])): ?><div class="profile-field"><div class="profile-field-label">DNI</div><div class="profile-field-value"><?php echo h($perfil['dni']); ?></div></div><?php endif; ?>
    <div class="profile-field"><div class="profile-field-label">Correo electrónico</div><div class="profile-field-value"><?php echo h($perfil['email'] ?? '-'); ?></div></div>
    <div class="profile-field"><div class="profile-field-label">Usuario</div><div class="profile-field-value"><?php echo h($perfil['usuario'] ?? $perfil['email'] ?? '-'); ?></div></div>
    <div class="profile-field"><div class="profile-field-label">Rol</div><div class="profile-field-value"><?php echo h($perfil['rol'] ?? 'Cliente'); ?></div></div>
   </div>
   <div class="content-divider"></div>
   <div class="profile-section">
    <div class="profile-section-title">Información de contacto</div>
    <?php if (!empty($perfil['telefono'])): ?><div class="profile-field"><div class="profile-field-label">Teléfono</div><div class="profile-field-value"><?php echo h($perfil['telefono']); ?></div></div><?php endif; ?>
    <?php if (!empty($perfil['direccion'])): ?><div class="profile-field"><div class="profile-field-label">Dirección</div><div class="profile-field-value"><?php echo h($perfil['direccion']); ?></div></div><?php endif; ?>
    <?php if (!empty($perfil['codigo_postal'])): ?><div class="profile-field"><div class="profile-field-label">Código postal</div><div class="profile-field-value"><?php echo h($perfil['codigo_postal']); ?></div></div><?php endif; ?>
    <?php if(empty($perfil['telefono']) && empty($perfil['direccion'])): ?><div style="font-size:13px;color:var(--tx4);padding:8px 0">Sin información de contacto adicional.</div><?php endif; ?>
   </div>
<<<<<<< HEAD
   <?php if(!empty($perfil['cliente'])): ?>
   <div class="content-divider"></div>
   <div class="profile-section">
    <div class="profile-section-title">Información de la cuenta</div>
    <div class="profile-field"><div class="profile-field-label">ID Cliente</div><div class="profile-field-value">#<?php echo h((string)$perfil['cliente']['id_cliente']); ?></div></div>
    <div class="profile-field"><div class="profile-field-label">Estado</div><div class="profile-field-value"><?php echo ((int)$perfil['cliente']['activo']===1?'Activo':'Inactivo'); ?></div></div>
    <div class="profile-field"><div class="profile-field-label">Fecha alta</div><div class="profile-field-value"><?php echo h(date('d/m/Y', strtotime($perfil['cliente']['fecha_alta']))); ?></div></div>
   </div>
   <?php endif; ?>
   <div class="content-divider"></div>
   <div class="actions-row">
    <a href="#cambiarPass" onclick="document.getElementById('cambiarPass').scrollIntoView({behavior:'smooth'});return false;" class="btn btn-primary">Cambiar contraseña</a>
    <a href="../config/logout.php" class="btn btn-outline" style="text-decoration:none;text-align:center;display:inline-flex;align-items:center">Cerrar sesión</a>
=======
   <?php if (!empty($perfil['ultimo_acceso'])): ?>
   <div class="profile-section">
    <div class="profile-section-title">Actividad</div>
    <div class="profile-field"><div class="profile-field-label">Último acceso</div><div class="profile-field-value"><?php echo h(date('d/m/Y H:i', strtotime($perfil['ultimo_acceso']))); ?></div></div>
   </div>
   <?php endif; ?>
   <div class="actions-row">
    <button class="btn btn-primary">Editar Perfil</button>
    <button class="btn btn-outline">Cambiar Contraseña</button>
>>>>>>> ad7f7b988ee33fa7a2a84311d3fa13c5b3715ab0
   </div>
  </div>

  <div class="content-card anim-bounce1" id="cambiarPass">
   <div class="profile-section-title">Cambiar contraseña</div>
   <div style="font-size:13px;color:var(--tx4);margin-bottom:16px">La contraseña debe tener al menos 8 caracteres, incluir letras y números.</div>
   <form method="POST">
    <input type="hidden" name="cambiar_pass" value="1">
    <div style="margin-bottom:14px"><label style="font-size:13px;font-weight:500;color:var(--tx2);display:block;margin-bottom:6px">Contraseña actual</label><div class="pass-wrap"><input type="password" name="actual" id="passActual" class="form-input" style="width:100%;background:var(--inp);border:1px solid var(--bd3);border-radius:8px;padding:10px 42px 10px 14px;color:var(--tx);font-family:inherit" required><button type="button" class="pass-eye" onclick="togglePass('passActual',this)"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button></div></div>
    <div style="margin-bottom:14px"><label style="font-size:13px;font-weight:500;color:var(--tx2);display:block;margin-bottom:6px">Nueva contraseña</label><div class="pass-wrap"><input type="password" name="nueva" id="passNueva" class="form-input" style="width:100%;background:var(--inp);border:1px solid var(--bd3);border-radius:8px;padding:10px 42px 10px 14px;color:var(--tx);font-family:inherit" required><button type="button" class="pass-eye" onclick="togglePass('passNueva',this)"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button></div></div>
    <div style="margin-bottom:18px"><label style="font-size:13px;font-weight:500;color:var(--tx2);display:block;margin-bottom:6px">Confirmar nueva contraseña</label><div class="pass-wrap"><input type="password" name="confirm" id="passConfirm" class="form-input" style="width:100%;background:var(--inp);border:1px solid var(--bd3);border-radius:8px;padding:10px 42px 10px 14px;color:var(--tx);font-family:inherit" required><button type="button" class="pass-eye" onclick="togglePass('passConfirm',this)"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button></div></div>
    <button type="submit" class="btn btn-primary">Guardar nueva contraseña</button>
   </form>
  </div>
 </div>
</div>
<script>function togglePass(id,btn){var i=document.getElementById(id); if(!i) return; i.type=i.type==='password'?'text':'password';}</script>
<script src="js/script.js"></script>
</body>
</html>
