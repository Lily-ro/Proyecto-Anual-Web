<?php
session_start();
if(!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'USUARIO'){
    header("Location: ../index.php");
    exit;
}
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/includes/helpers.php';
$msg = '';
$msgType = '';
$tanques = [];
$deviceStatus = 'Conectado';
$solicitudes = [];
try {
    $pdo = eva_pdo();
    $uid = eva_current_user_id();
    $tanques = eva_tanques_for_user($pdo, $uid);
    if (!empty($tanques)) {
        $first = $tanques[0];
        $idFirst = (int)($first['id_tanque'] ?? 0);
        $deviceStatus = eva_device_status($pdo, $idFirst);
    }
    $pdo->exec("CREATE TABLE IF NOT EXISTS solicitudes_mantenimiento (id_solicitud BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY, id_tanque INT(11) NOT NULL, id_usuario INT(11) NOT NULL, id_tecnico INT(11) DEFAULT NULL, descripcion TEXT NOT NULL, imagen VARCHAR(255) DEFAULT NULL, estado ENUM('PENDIENTE','ACEPTADA','EN_PROCESO','FINALIZADA','CANCELADA') NOT NULL DEFAULT 'PENDIENTE', fecha_solicitud DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, fecha_aceptada DATETIME DEFAULT NULL, fecha_inicio DATETIME DEFAULT NULL, fecha_finalizada DATETIME DEFAULT NULL, observaciones_admin TEXT DEFAULT NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, KEY id_tanque (id_tanque), KEY id_usuario (id_usuario), KEY id_tecnico (id_tecnico), KEY estado (estado)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    try {
        $cols = $pdo->query("SHOW COLUMNS FROM mantenimientos")->fetchAll(PDO::FETCH_COLUMN,1);
        if (!in_array('id_solicitud', $cols, true)) {
            $pdo->exec("ALTER TABLE mantenimientos ADD COLUMN id_solicitud BIGINT DEFAULT NULL, ADD KEY id_solicitud (id_solicitud), ADD CONSTRAINT fk_mant_solicitud FOREIGN KEY (id_solicitud) REFERENCES solicitudes_mantenimiento(id_solicitud) ON DELETE SET NULL ON UPDATE CASCADE");
        }
    } catch(Throwable $e) {}
    $dirMant = __DIR__ . '/uploads/mantenimientos';
    if (!is_dir($dirMant)) @mkdir($dirMant, 0755, true);
    $ht = $dirMant . '/.htaccess';
    if (!file_exists($ht)) @file_put_contents($ht, "<FilesMatch \"\\.php$\">\nRequire all denied\n</FilesMatch>\nphp_flag engine off\n");
    $idx = $dirMant . '/index.html';
    if (!file_exists($idx)) @file_put_contents($idx, '');
} catch (Throwable $e) { $tanques = []; }
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mt_submit'])) {
    $token = $_POST['csrf'] ?? '';
    if (!eva_csrf_validate($token)) {
        $msg='Token inválido.'; $msgType='error';
    } else {
        $uid = eva_current_user_id();
        if (!$uid) { $msg='Debes iniciar sesión para enviar una solicitud.'; $msgType='error'; }
        else {
            $idTanque = (int)($_POST['mt_tanque'] ?? 0);
            $descRaw = $_POST['mt_descripcion'] ?? '';
            $desc = trim((string)$descRaw);
            $desc = preg_replace('/\s+/', ' ', $desc);
            if ($idTanque <= 0) { $msg='Debes seleccionar un tanque.'; $msgType='error'; }
            elseif ($desc === '') { $msg='La descripción es obligatoria.'; $msgType='error'; }
            elseif (mb_strlen($desc) > 500) { $msg='La descripción no puede superar los 500 caracteres.'; $msgType='error'; }
            else {
                try {
                    $pdo = eva_pdo();
                    $st = $pdo->prepare("SELECT id_tanque, nombre FROM tanques WHERE id_tanque=:id AND activo=1 LIMIT 1");
                    $st->execute([':id'=>$idTanque]);
                    $tanqueRow = $st->fetch();
                    if (!$tanqueRow) { $msg='El tanque seleccionado no existe.'; $msgType='error'; }
                    else {
                        $nombreTanque = $tanqueRow['nombre'];
                        $chkDup = $pdo->prepare("SELECT id_solicitud FROM solicitudes_mantenimiento WHERE id_usuario=:uid AND id_tanque=:tid AND estado IN ('PENDIENTE','ACEPTADA','EN_PROCESO') LIMIT 1");
                        $chkDup->execute([':uid'=>$uid, ':tid'=>$idTanque]);
                        if ($chkDup->fetch()) { $msg='Ya existe una solicitud de mantenimiento pendiente para este tanque.'; $msgType='error'; }
                        else {
                            $imgPath = null;
                            if (isset($_FILES['mt_imagen']) && $_FILES['mt_imagen']['error'] !== UPLOAD_ERR_NO_FILE) {
                                if ($_FILES['mt_imagen']['error'] !== UPLOAD_ERR_OK) { $msg='Error al subir la imagen.'; $msgType='error'; }
                                else {
                                    if ($_FILES['mt_imagen']['size'] > 5*1024*1024) { $msg='El archivo supera el tamaño máximo de 5 MB.'; $msgType='error'; }
                                    else {
                                        $tmp = $_FILES['mt_imagen']['tmp_name'];
                                        if (!is_uploaded_file($tmp)) { $msg='Archivo no válido.'; $msgType='error'; }
                                        else {
                                            $finfo = finfo_open(FILEINFO_MIME_TYPE);
                                            $mime = $finfo ? finfo_file($finfo, $tmp) : '';
                                            if ($finfo) finfo_close($finfo);
                                            $allowed = ['image/jpeg'=>'jpg','image/png'=>'png','image/jpg'=>'jpg','image/webp'=>'webp'];
                                            if (!isset($allowed[$mime])) { $msg='Formato de imagen no permitido.'; $msgType='error'; }
                                            else {
                                                $ext = $allowed[$mime];
                                                $fname = 'sm_' . date('Ymd_His') . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
                                                $dest = $dirMant . '/' . $fname;
                                                if (!@move_uploaded_file($tmp, $dest)) { $msg='No se pudo guardar la imagen.'; $msgType='error'; }
                                                else {
                                                    @chmod($dest, 0644);
                                                    $imgPath = 'uploads/mantenimientos/' . $fname;
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                            if ($msgType !== 'error') {
                                try {
                                    $pdo->beginTransaction();
                                    $ins = $pdo->prepare("INSERT INTO solicitudes_mantenimiento (id_tanque, id_usuario, descripcion, imagen, estado, fecha_solicitud, created_at, updated_at) VALUES (:tid, :uid, :desc, :img, 'PENDIENTE', NOW(), NOW(), NOW())");
                                    $ins->execute([':tid'=>$idTanque, ':uid'=>$uid, ':desc'=>$desc, ':img'=>$imgPath]);
                                    $idSol = (int)$pdo->lastInsertId();
                                    try {
                                        $admins = $pdo->query("SELECT id_usuario FROM usuarios WHERE id_rol IN (1,2) AND activo=1")->fetchAll();
                                        $txt = "Se recibió una nueva solicitud de mantenimiento para el tanque " . $nombreTanque . ". Solicitud #" . $idSol . ".";
                                        foreach ($admins as $a) {
                                            $au = (int)($a['id_usuario'] ?? 0);
                                            if ($au) $pdo->prepare("INSERT INTO notificaciones (id_usuario, mensaje, leida, fecha_hora) VALUES (:uid, :msg, 0, NOW())")->execute([':uid'=>$au, ':msg'=>$txt]);
                                        }
                                    } catch(Throwable $e) {}
                                    try { eva_log_actividad($pdo, (int)$uid, 'CREATE', "Creó solicitud de mantenimiento #".$idSol." para el tanque ".$nombreTanque); } catch(Throwable $e) {}
                                    $pdo->commit();
                                    $msg='Solicitud #'.$idSol.' creada correctamente. Solicitud enviada correctamente.'; $msgType='success';
                                } catch(Throwable $e){
                                    if ($pdo->inTransaction()) $pdo->rollBack();
                                    if ($imgPath) { $full = __DIR__ . '/' . $imgPath; if (file_exists($full)) @unlink($full); }
                                    error_log('solicitud insert error: '.$e->getMessage());
                                    $msg='No se pudo guardar la solicitud. Intentá nuevamente.'; $msgType='error';
                                }
                            }
                        }
                    }
                } catch(Throwable $e){
                    error_log('solicitud error: '.$e->getMessage());
                    $msg='No se pudo guardar la solicitud. Intentá nuevamente.'; $msgType='error';
                }
            }
        }
    }
}
try {
    $pdo = eva_pdo();
    $uid = eva_current_user_id();
    $hasTable = false;
    try { $pdo->query("SELECT 1 FROM solicitudes_mantenimiento LIMIT 1"); $hasTable=true; } catch(Throwable $e) {}
    if ($hasTable && $uid) {
        $st=$pdo->prepare("SELECT s.*, t.nombre AS tanque_nombre FROM solicitudes_mantenimiento s LEFT JOIN tanques t ON t.id_tanque=s.id_tanque WHERE s.id_usuario=:uid ORDER BY s.fecha_solicitud DESC, s.id_solicitud DESC LIMIT 50");
        $st->execute([':uid'=>$uid]);
        $rows=$st->fetchAll();
        foreach($rows as $r){
            $id = (int)($r['id_solicitud'] ?? 0);
            $fechaRaw = $r['fecha_solicitud'] ?? $r['created_at'] ?? '';
            $fechaFmt = $fechaRaw ? date('d/m/Y', strtotime((string)$fechaRaw)) : '-';
            $actualRaw = $r['updated_at'] ?? $r['fecha_solicitud'] ?? $fechaRaw;
            $actualFmt = $actualRaw ? date('d/m/Y H:i', strtotime((string)$actualRaw)) : $fechaFmt;
            $problema = $r['descripcion'] ?? '-';
            $estadoRaw = strtoupper(trim((string)($r['estado'] ?? 'PENDIENTE')));
            $estadoBadge = match($estadoRaw){ 'PENDIENTE'=>'activo','ACEPTADA'=>'en-revision','EN_PROCESO'=>'en-revision','FINALIZADA'=>'resuelta','CANCELADA'=>'resuelta', default=>'activo' };
            $solicitudes[]=['id'=>$id,'fecha'=>$fechaFmt,'problema'=>h((string)$problema),'estado'=>h($estadoRaw),'estadoClass'=>$estadoBadge,'actualizacion'=>$actualFmt];
        }
    }
    if (empty($solicitudes)) {
        $cols=[];
        try { $st=$pdo->query("SHOW COLUMNS FROM mantenimientos"); foreach($st->fetchAll() as $c) $cols[]=$c['Field']; } catch(Throwable $e){}
        $hasUsuario = in_array('id_usuario',$cols,true) || in_array('id_cliente',$cols,true);
        $colUsuario = in_array('id_usuario',$cols,true)?'id_usuario':(in_array('id_cliente',$cols,true)?'id_cliente':null);
        $sql="SELECT m.* FROM mantenimientos m";
        $params=[];
        if ($hasUsuario && $uid && $colUsuario) { $sql.=" WHERE m.{$colUsuario} = :uid"; $params[':uid']=$uid; }
        elseif (!empty($tanques)) { $ids=array_map(fn($t)=>(int)($t['id_tanque']??0), $tanques); $ids=array_filter($ids); if($ids) { $in=implode(',',$ids); $sql.=" WHERE m.id_tanque IN ($in)"; } }
        $orderCol = in_array('fecha_solicitud',$cols,true)?'fecha_solicitud':(in_array('fecha',$cols,true)?'fecha':(in_array('created_at',$cols,true)?'created_at':'id_mantenimiento'));
        $sql.=" ORDER BY {$orderCol} DESC LIMIT 50";
        $st=$pdo->prepare($sql); $st->execute($params); $rows=$st->fetchAll();
        foreach($rows as $r){
            $id = $r['id_mantenimiento'] ?? $r['id'] ?? 0;
            $fechaRaw = $r['fecha_solicitud'] ?? $r['fecha'] ?? $r['created_at'] ?? '';
            $fechaFmt = $fechaRaw ? date('d/m/Y', strtotime((string)$fechaRaw)) : '-';
            $actualRaw = $r['fecha_actualizacion'] ?? $r['updated_at'] ?? $r['fecha'] ?? $fechaRaw;
            $actualFmt = $actualRaw ? date('d/m/Y H:i', strtotime((string)$actualRaw)) : $fechaFmt;
            $problema = $r['descripcion'] ?? $r['detalle'] ?? $r['problema'] ?? '-';
            $estadoRaw = $r['estado'] ?? 'Pendiente';
            $estadoNorm = strtolower(trim((string)$estadoRaw));
            $estadoBadge = match($estadoNorm){ 'pendiente'=>'activo','en revision','en_revision'=>'en-revision','resuelto','cerrada','finalizado'=>'resuelta', default=>'activo' };
            if (empty($solicitudes)) $solicitudes[]=['id'=>$id,'fecha'=>$fechaFmt,'problema'=>h((string)$problema),'estado'=>h((string)$estadoRaw),'estadoClass'=>$estadoBadge,'actualizacion'=>$actualFmt];
        }
    }
} catch(Throwable $e){ error_log('solicitudes list error: '.$e->getMessage()); }
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>EVA - Mantenimiento</title>
<link rel="stylesheet" href="css/style.css">
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
   <li class="anim-slide1"><a href="indexcli.php"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg><span>Resumen</span></a></li>
   <li class="anim-slide2"><a href="mitanque.php"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="2" width="16" height="20" rx="2"/><line x1="4" y1="18" x2="20" y2="18"/><rect x="7" y="12" width="10" height="6" rx="1" fill="currentColor" opacity="0.3"/></svg><span>Mi Tanque</span></a></li>
   <li class="anim-slide3"><a href="alertas.php"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg><span>Alertas</span></a></li>
   <li class="anim-slide4"><a href="configuracion.php"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 01-2.83 2.83l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z"/></svg><span>Configuración</span></a></li>
   <li class="anim-slide5"><a href="historial.php"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg><span>Historial</span></a></li>
   <li class="active anim-slide6"><a href="mantenimiento.php"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91a6 6 0 017.94-7.94l-3.76 3.76z"/></svg><span>Mantenimiento</span></a></li>
  </ul>
 </nav>
 <div class="device-status">
  <h4>Dispositivo</h4>
  <div class="status-row">
   <svg class="wifi-icon anim-pulse" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12.55a11 11 0 0114.08 0"/><path d="M1.42 9a16 16 0 0121.16 0"/><path d="M8.53 16.11a6 6 0 016.95 0"/><line x1="12" y1="20" x2="12.01" y2="20"/></svg>
   <span class="status-text"><?php echo h($deviceStatus); ?></span>
  </div>
 </div>
</aside>
<div class="main">
 <header class="header">
  <div class="header-left">
   <button class="menu-btn"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#7a829a" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg></button>
  </div>
  <div class="header-right">
   <button class="theme-btn" id="themeToggle" title="Cambiar tema">
    <svg class="icon-sun" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
    <svg class="icon-moon hidden" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
   </button>
    <div class="user-dropdown" id="userDropdown">
     <div class="user-info">
      <div class="user-details"><div class="user-name"><?php echo h($_SESSION['nombre'] ?? 'Usuario'); ?></div><div class="user-role">Cliente</div></div>
      <div class="user-avatar"><?php echo h(strtoupper(substr($_SESSION['nombre'] ?? 'U', 0, 2))); ?></div>
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#7a829a" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
     </div>
      <div class="user-menu hidden" id="userMenu">
       <a class="user-menu-item" href="perfil.php">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        <span>Mi perfil</span>
       </a>
       <a class="user-menu-item" href="configuracion.php">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z"/></svg>
        <span>Configuración</span>
       </a>
       <a class="user-menu-item" href="../config/logout.php">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
        <span>Cerrar sesión</span>
       </a>
      </div>
    </div>
   </div>
  </header>
 <div class="view active" id="viewMantenimiento">
  <div class="mt-page-title anim-bounce0">Solicitar mantenimiento</div>
  <div class="mt-page-subtitle anim-bounce0">Deberas seleccionar un problema con tu dispositivo, completar el formulario y nuestro equipo tecnico revisara tu solicitud.</div>
  <?php if ($msg): ?>
   <div style="margin-bottom:16px;padding:12px 16px;border-radius:8px;font-size:13px;<?php echo $msgType==='success'?'background:rgba(76,175,80,0.12);color:var(--gn);border:1px solid rgba(76,175,80,0.2)':'background:rgba(244,67,54,0.12);color:var(--rd);border:1px solid rgba(244,67,54,0.2)'; ?>"><?php echo h($msg); ?></div>
  <?php endif; ?>
  <div class="anim-bounce0" style="margin-bottom:20px">
   <button class="mt-respuestas-btn" id="btnVerRespuestas">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 01-3.46 0"/></svg>
    Respuestas
   </button>
  </div>
  <div class="mt-grid">
   <div class="mt-card anim-bounce1">
    <div class="mt-card-title">Como funciona?</div>
    <div class="mt-steps">
     <div class="mt-step">
      <div class="mt-step-num">1</div>
      <div class="mt-step-text">Envias tu solicitud describiendo el problema.</div>
     </div>
     <div class="mt-step">
      <div class="mt-step-num">2</div>
      <div class="mt-step-text">Un tecnico revisa tu solicitud.</div>
     </div>
     <div class="mt-step">
      <div class="mt-step-num">3</div>
      <div class="mt-step-text">Te informaremos el estado y la solucion.</div>
     </div>
    </div>
   </div>
   <div class="mt-card anim-bounce2">
    <div class="mt-card-title">Nueva Solicitud de mantenimiento</div>
    <form id="mtForm" method="POST" enctype="multipart/form-data">
     <input type="hidden" name="csrf" value="<?php echo h(eva_csrf_token()); ?>">
     <input type="hidden" name="mt_submit" value="1">
     <div class="mt-field">
      <select id="mtTanque" name="mt_tanque" class="mt-select" required>
       <option value="">Seleccionar tanque...</option>
       <?php foreach ($tanques as $t): $tid=(int)($t['id_tanque']??0); $tname=h($t['nombre']??'Tanque '.$tid); $cap=(int)($t['capacidad_litros']??0); ?>
        <option value="<?php echo $tid; ?>"><?php echo $tname; ?> (<?php echo $cap; ?>L)</option>
       <?php endforeach; ?>
      </select>
     </div>
     <div class="mt-field">
      <label class="mt-label">Descripcion del problema</label>
      <textarea id="mtDescripcion" name="mt_descripcion" class="mt-textarea" maxlength="500" placeholder="Describe brevemente que esta sucediendo..." required></textarea>
      <div class="mt-charcount"><span id="mtCharCount">0</span>/500</div>
     </div>
     <div class="mt-upload" id="mtUpload">
      <svg class="mt-upload-icon" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
      <div class="mt-upload-title">Adjuntar imagen (opcional)</div>
      <div class="mt-upload-desc">Arrastra una imagen aqui o hace click</div>
      <div class="mt-upload-hint">(Formatos: JPG, PNG. Max. 5MB)</div>
      <input type="file" id="mtFileInput" name="mt_imagen" accept="image/jpeg,image/png,image/jpg,image/webp" style="display:none">
      <div id="mtFileName" style="margin-top:8px;font-size:12px;color:var(--tx4)"></div>
     </div>
     <button type="submit" class="mt-enviar-btn" id="mtEnviar">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
      Enviar solicitud
     </button>
    </form>
   </div>
  </div>
  <div class="mt-card anim-bounce3" style="margin-top:20px">
   <div style="overflow-x:auto">
    <table class="mt-table">
     <thead>
      <tr>
       <th>N Solicitud</th>
       <th>Fecha</th>
       <th>Problema</th>
       <th>Estado</th>
       <th>Ult. Actualizacion</th>
       <th></th>
      </tr>
     </thead>
     <tbody id="mtTablaBody">
     <?php if (empty($solicitudes)): ?>
       <tr><td colspan="6" style="text-align:center;padding:20px;color:var(--tx4);font-size:13px">No tienes solicitudes de mantenimiento.</td></tr>
     <?php else: foreach ($solicitudes as $idx=>$s): $num = str_pad((string)$s['id'],4,'0',STR_PAD_LEFT); ?>
      <tr style="animation:slideUp .3s <?php echo $idx*0.05; ?>s backwards">
       <td>#<?php echo h($num); ?></td>
       <td><?php echo h($s['fecha']); ?></td>
       <td><?php echo $s['problema']; ?></td>
       <td><span class="alert-badge <?php echo h($s['estadoClass']); ?>"><?php echo h($s['estado']); ?></span></td>
       <td style="font-size:12px;color:var(--tx5)"><?php echo h($s['actualizacion']); ?></td>
       <td>
        <button class="mt-info-btn" title="Ver detalles">
         <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
        </button>
       </td>
      </tr>
     <?php endforeach; endif; ?>
     </tbody>
    </table>
   </div>
  </div>
 </div>
</div>
<script>
window.EVA_MT_TANQUES = <?php echo json_encode(array_map(fn($t)=>['id'=>(int)($t['id_tanque']??0),'nombre'=>$t['nombre']??''], $tanques), JSON_UNESCAPED_UNICODE); ?>;
</script>
<script src="js/script.js?v=2"></script>
</body>
</html>
