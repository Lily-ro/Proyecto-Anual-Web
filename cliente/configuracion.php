<?php
session_start();
if(!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'USUARIO'){
    header("Location: ../index.php");
    exit;
}
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/includes/helpers.php';

$low = 20;
$high = 90;
$msg = '';
$msgType = '';
$deviceStatus = 'Conectado';
$idTanqueSel = null;

try {
    $pdo = eva_pdo();
    $uid = eva_current_user_id();
    $tanque = eva_first_tanque($pdo, $uid);
    if ($tanque) {
        $idTanqueSel = (int)($tanque['id_tanque'] ?? 0);
        $deviceStatus = eva_device_status($pdo, $idTanqueSel);
        // obtener configuracion existente
        // detectar columnas disponibles en configuracion_alertas
        $cols = [];
        try {
            $st = $pdo->query("SHOW COLUMNS FROM configuracion_alertas");
            foreach ($st->fetchAll() as $c) $cols[] = $c['Field'];
        } catch (Throwable $e) {}
        // mapear low/high a columnas existentes
        $colLow = null; $colHigh = null;
        foreach (['nivel_bajo','umbral_bajo','valor_min','min','threshold_low','porcentaje_bajo'] as $cand) {
            if (in_array($cand, $cols, true)) { $colLow = $cand; break; }
        }
        foreach (['nivel_alto','umbral_alto','valor_max','max','threshold_high','porcentaje_alto'] as $cand) {
            if (in_array($cand, $cols, true)) { $colHigh = $cand; break; }
        }
        // si no hay columnas especificas, puede que cada tipo sea una fila con tipo=NIVEL_BAJO/NIVEL_ALTO y valor
        $hasTipoValor = in_array('tipo', $cols, true) && (in_array('valor', $cols, true) || in_array('umbral', $cols, true) || in_array('porcentaje', $cols, true));
        if ($hasTipoValor) {
            $valCol = in_array('valor', $cols, true) ? 'valor' : (in_array('umbral', $cols, true) ? 'umbral' : 'porcentaje');
            $st = $pdo->prepare("SELECT tipo, {$valCol} AS v FROM configuracion_alertas WHERE id_tanque = :id");
            $st->execute([':id'=>$idTanqueSel]);
            foreach ($st->fetchAll() as $r) {
                $t = strtoupper($r['tipo'] ?? '');
                if ($t === 'NIVEL_BAJO') $low = (int)$r['v'];
                if ($t === 'NIVEL_ALTO') $high = (int)$r['v'];
            }
        } elseif ($colLow || $colHigh) {
            $selectCols = [];
            if ($colLow) $selectCols[] = $colLow;
            if ($colHigh) $selectCols[] = $colHigh;
            $colsStr = implode(',', $selectCols);
            $st = $pdo->prepare("SELECT {$colsStr} FROM configuracion_alertas WHERE id_tanque = :id LIMIT 1");
            $st->execute([':id'=>$idTanqueSel]);
            $row = $st->fetch();
            if ($row) {
                if ($colLow && isset($row[$colLow])) $low = (int)$row[$colLow];
                if ($colHigh && isset($row[$colHigh])) $high = (int)$row[$colHigh];
            }
        } else {
            // intento genérico con * y buscar cualquier columna que parezca
            $st = $pdo->prepare("SELECT * FROM configuracion_alertas WHERE id_tanque = :id LIMIT 1");
            $st->execute([':id'=>$idTanqueSel]);
            $row = $st->fetch();
            if ($row) {
                // buscar claves que contengan bajo/alto
                foreach ($row as $k=>$v) {
                    if (stripos($k,'bajo')!==false && is_numeric($v)) $low = (int)$v;
                    if (stripos($k,'alto')!==false && is_numeric($v)) $high = (int)$v;
                }
            }
        }
    }
} catch (Throwable $e) { error_log('config load error: '.$e->getMessage()); }

// Manejar POST guardar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf'] ?? '';
    if (!eva_csrf_validate($token)) {
        $msg = 'Token de seguridad inválido.';
        $msgType = 'error';
    } else {
        $newLow = (int)($_POST['nivel_bajo'] ?? $low);
        $newHigh = (int)($_POST['nivel_alto'] ?? $high);
        // validacion
        if ($newLow < 10 || $newLow > 50) { $msg='Nivel bajo debe estar entre 10 y 50.'; $msgType='error'; }
        elseif ($newHigh < 50 || $newHigh > 100) { $msg='Nivel alto debe estar entre 50 y 100.'; $msgType='error'; }
        elseif ($newLow >= $newHigh) { $msg='Nivel bajo debe ser menor que nivel alto.'; $msgType='error'; }
        else {
            try {
                $pdo = eva_pdo();
                if ($idTanqueSel === null) {
                    $tanque = eva_first_tanque($pdo, eva_current_user_id());
                    $idTanqueSel = $tanque ? (int)($tanque['id_tanque'] ?? 0) : 0;
                }
                // detectar columnas nuevamente
                $cols = [];
                $st = $pdo->query("SHOW COLUMNS FROM configuracion_alertas");
                foreach ($st->fetchAll() as $c) $cols[] = $c['Field'];
                $hasTipoValor = in_array('tipo', $cols, true) && in_array('valor', $cols, true);
                $hasUmbral = in_array('umbral', $cols, true);
                $valCol = $hasTipoValor ? 'valor' : ($hasUmbral ? 'umbral' : (in_array('porcentaje',$cols,true)?'porcentaje':null));
                if ($hasTipoValor || ($hasUmbral && in_array('tipo',$cols,true))) {
                    // upsert por tipo
                    foreach (['NIVEL_BAJO'=>$newLow, 'NIVEL_ALTO'=>$newHigh] as $tipo=>$val) {
                        $chk = $pdo->prepare("SELECT id_configuracion FROM configuracion_alertas WHERE id_tanque=:id AND tipo=:tipo LIMIT 1");
                        $chk->execute([':id'=>$idTanqueSel, ':tipo'=>$tipo]);
                        $ex = $chk->fetch();
                        if ($ex) {
                            $pdo->prepare("UPDATE configuracion_alertas SET {$valCol}=:v WHERE id_configuracion=:cid")
                                ->execute([':v'=>$val, ':cid'=>$ex['id_configuracion']]);
                        } else {
                            $pdo->prepare("INSERT INTO configuracion_alertas (id_tanque, tipo, {$valCol}) VALUES (:id,:tipo,:v)")
                                ->execute([':id'=>$idTanqueSel, ':tipo'=>$tipo, ':v'=>$val]);
                        }
                    }
                    $low = $newLow; $high = $newHigh;
                    $msg='Configuración guardada correctamente.'; $msgType='success';
                    eva_log_actividad($pdo, (int)eva_current_user_id(), 'ACTUALIZAR_CONFIG_ALERTA', "bajo={$newLow} alto={$newHigh}");
                } else {
                    $colLow = null; $colHigh=null;
                    foreach (['nivel_bajo','umbral_bajo','valor_min'] as $cand) if (in_array($cand,$cols,true)) {$colLow=$cand;break;}
                    foreach (['nivel_alto','umbral_alto','valor_max'] as $cand) if (in_array($cand,$cols,true)) {$colHigh=$cand;break;}
                    if ($colLow && $colHigh) {
                        $chk = $pdo->prepare("SELECT id_configuracion FROM configuracion_alertas WHERE id_tanque=:id LIMIT 1");
                        $chk->execute([':id'=>$idTanqueSel]);
                        $ex=$chk->fetch();
                        if ($ex) {
                            $pdo->prepare("UPDATE configuracion_alertas SET {$colLow}=:low, {$colHigh}=:high WHERE id_configuracion=:cid")
                                ->execute([':low'=>$newLow, ':high'=>$newHigh, ':cid'=>$ex['id_configuracion']]);
                        } else {
                            $pdo->prepare("INSERT INTO configuracion_alertas (id_tanque, {$colLow}, {$colHigh}) VALUES (:id,:low,:high)")
                                ->execute([':id'=>$idTanqueSel, ':low'=>$newLow, ':high'=>$newHigh]);
                        }
                        $low=$newLow; $high=$newHigh;
                        $msg='Configuración guardada correctamente.'; $msgType='success';
                    } else {
                        $msg='No se pudo guardar: estructura de configuracion_alertas no reconocida.'; $msgType='error';
                    }
                }
            } catch (Throwable $e) {
                error_log('config save error: '.$e->getMessage());
                $msg='Error al guardar: '.h($e->getMessage()); $msgType='error';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>EVA - Configuración</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<!--BARRA LATERAL-->
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
   <li class="active anim-slide4"><a href="configuracion.php"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 01-2.83 2.83l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z"/></svg><span>Configuración</span></a></li>
   <li class="anim-slide5"><a href="historial.php"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg><span>Historial</span></a></li>
   <li class="anim-slide6"><a href="mantenimiento.php"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91a6 6 0 017.94-7.94l-3.76 3.76z"/></svg><span>Mantenimiento</span></a></li>
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
 <!-- HEADER-->
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

 <!-- VISTA CONFIGURACION -->
 <div class="view active" id="viewConfig">
  <div class="config-page-title">Configuración de alertas</div>
  <div class="config-page-subtitle">Personaliza los umbrales y metodos de notificacion</div>
  <?php if ($msg): ?>
   <div style="margin-bottom:16px;padding:12px 16px;border-radius:8px;font-size:13px;<?php echo $msgType==='success' ? 'background:rgba(76,175,80,0.12);color:var(--gn);border:1px solid rgba(76,175,80,0.2)' : 'background:rgba(244,67,54,0.12);color:var(--rd);border:1px solid rgba(244,67,54,0.2)'; ?>"><?php echo h($msg); ?></div>
  <?php endif; ?>
  <form method="POST" id="configForm">
   <input type="hidden" name="csrf" value="<?php echo h(eva_csrf_token()); ?>">
   <div class="config-card anim-bounce0">
    <div class="config-card-title">Umbrales de nivel</div>
    <div class="config-card-subtitle">Configura los niveles que activaran las alertas</div>
    <div class="config-divider"></div>
    <div class="config-threshold">
     <div class="config-threshold-header"><div class="config-threshold-icon orange"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg></div><div class="config-threshold-name">Nivel bajo</div></div>
     <div class="config-threshold-desc">Alerta cuando el nivel de agua sea menor o igual a:</div>
     <div class="slider-row"><span class="slider-range-label left">10%</span><div class="slider-container"><input type="range" class="slider-input" id="sliderLow" name="nivel_bajo" min="10" max="50" value="<?php echo (int)$low; ?>"></div><span class="slider-range-label">50%</span><span class="slider-value" id="sliderLowVal"><?php echo (int)$low; ?> %</span></div>
    </div>
    <div class="config-threshold">
     <div class="config-threshold-header"><div class="config-threshold-icon red"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg></div><div class="config-threshold-name">Nivel alto</div></div>
     <div class="config-threshold-desc">Alerta cuando el nivel de agua sea mayor o igual a:</div>
     <div class="slider-row"><span class="slider-range-label left">50%</span><div class="slider-container"><input type="range" class="slider-input" id="sliderHigh" name="nivel_alto" min="50" max="100" value="<?php echo (int)$high; ?>"></div><span class="slider-range-label">100%</span><span class="slider-value" id="sliderHighVal"><?php echo (int)$high; ?> %</span></div>
    </div>
    <div style="margin-top:20px;display:flex;gap:10px">
     <button type="submit" class="mt-enviar-btn" style="margin-top:0">Guardar cambios</button>
     <a href="alertas.php" class="mt-respuestas-btn" style="text-decoration:none;display:inline-flex;align-items:center;margin-bottom:0">Ver alertas</a>
    </div>
   </div>
  </form>
 </div>
</div>
<script src="js/script.js"></script>
</body>
</html>
