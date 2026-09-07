<?php
declare(strict_types=1);
require_once __DIR__ . '/../../config/db.php';
function h(?string $v): string { return htmlspecialchars($v ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function eva_current_user_id(): ?int { return isset($_SESSION['id_usuario']) ? (int)$_SESSION['id_usuario'] : null; }
function eva_csrf_token(): string { if(empty($_SESSION['csrf_token'])) $_SESSION['csrf_token']=bin2hex(random_bytes(32)); return $_SESSION['csrf_token']; }
function eva_csrf_validate(?string $token): bool { return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], (string)$token); }
function eva_tanques_for_user(PDO $pdo, ?int $uid): array {
 try{
   $st=$pdo->prepare("SELECT t.*, e.nombre AS edificio_nombre FROM tanques t LEFT JOIN edificios e ON e.id_edificio=t.id_edificio WHERE t.activo=1 ORDER BY t.id_tanque ASC");
   $all=$st->execute() ? $st->fetchAll() : [];
   if($uid!==null){
     $chk=$pdo->prepare("SELECT id_cliente FROM clientes WHERE id_usuario=:uid LIMIT 1");
     $chk->execute([':uid'=>$uid]);
     if($chk->fetch()){ return $all; }
   }
   return $all;
 }catch(Throwable $e){ return []; }
}
function eva_first_tanque(PDO $pdo, ?int $uid): ?array { $list=eva_tanques_for_user($pdo,$uid); return $list[0] ?? null; }
function eva_latest_medicion(PDO $pdo, int $id_tanque): ?array {
 try{
   $st=$pdo->prepare("SELECT m.* FROM mediciones m INNER JOIN sensores s ON s.id_sensor=m.id_sensor INNER JOIN dispositivos d ON d.id_dispositivo=s.id_dispositivo WHERE d.id_tanque=:id ORDER BY m.fecha_hora DESC, m.id_medicion DESC LIMIT 1");
   $st->execute([':id'=>$id_tanque]);
   $row=$st->fetch();
   if($row) return $row;
 }catch(Throwable $e){}
 try{
   $st=$pdo->prepare("SELECT m.* FROM mediciones m INNER JOIN sensores s ON s.id_sensor=m.id_sensor WHERE s.id_dispositivo IN (SELECT id_dispositivo FROM dispositivos WHERE id_tanque=:id) ORDER BY m.fecha_hora DESC, m.id_medicion DESC LIMIT 1");
   $st->execute([':id'=>$id_tanque]);
   $row=$st->fetch();
   if($row) return $row;
 }catch(Throwable $e){}
 try{
   $st=$pdo->prepare("SELECT m.* FROM mediciones m ORDER BY m.fecha_hora DESC, m.id_medicion DESC LIMIT 1");
   $st->execute();
   $row=$st->fetch();
   if($row) return $row;
 }catch(Throwable $e){}
 return null;
}
function eva_mediciones_historial(PDO $pdo, int $id_tanque, int $limit=50): array {
 try{
   $st=$pdo->prepare("SELECT m.id_medicion, m.distancia_cm, m.nivel_cm, m.porcentaje, m.litros, m.fecha_hora, m.temperatura, m.humedad, s.modelo, s.numero_serie FROM mediciones m INNER JOIN sensores s ON s.id_sensor=m.id_sensor INNER JOIN dispositivos d ON d.id_dispositivo=s.id_dispositivo WHERE d.id_tanque=:id ORDER BY m.fecha_hora DESC, m.id_medicion DESC LIMIT :lim");
   $st->bindValue(':id',$id_tanque,PDO::PARAM_INT); $st->bindValue(':lim',$limit,PDO::PARAM_INT); $st->execute();
   return $st->fetchAll();
 }catch(Throwable $e){ return []; }
}
function eva_consumo_hoy(PDO $pdo, int $id_tanque): float {
 try{
   $st=$pdo->prepare("SELECT litros_consumidos FROM consumos WHERE id_tanque=:id AND fecha=CURDATE() LIMIT 1");
   $st->execute([':id'=>$id_tanque]); $r=$st->fetch(); if($r && isset($r['litros_consumidos'])) return (float)$r['litros_consumidos'];
   $st2=$pdo->prepare("SELECT litros_consumidos FROM consumos WHERE id_tanque=:id ORDER BY fecha DESC LIMIT 1");
   $st2->execute([':id'=>$id_tanque]); $r2=$st2->fetch(); if($r2 && isset($r2['litros_consumidos'])) return (float)$r2['litros_consumidos'];
 }catch(Throwable $e){}
 try{
   $st=$pdo->prepare("SELECT m.litros FROM mediciones m LEFT JOIN sensores s ON s.id_sensor=m.id_sensor LEFT JOIN dispositivos d ON d.id_dispositivo=s.id_dispositivo WHERE (d.id_tanque=:id OR d.id_tanque IS NULL) ORDER BY m.fecha_hora DESC LIMIT 1");
   $st->execute([':id'=>$id_tanque]); $r=$st->fetch(); if($r && isset($r['litros'])) return (float)$r['litros'];
 }catch(Throwable $e){}
 try{
   $st=$pdo->prepare("SELECT litros FROM mediciones ORDER BY fecha_hora DESC LIMIT 1");
   $st->execute(); $r=$st->fetch(); if($r && isset($r['litros'])) return (float)$r['litros'];
 }catch(Throwable $e){}
 return 0.0;
}
function eva_consumo_promedio(PDO $pdo, int $id_tanque): float {
 try{
   $st=$pdo->prepare("SELECT AVG(litros_consumidos) as avg_c FROM consumos WHERE id_tanque=:id");
   $st->execute([':id'=>$id_tanque]); $r=$st->fetch(); if($r && $r['avg_c']!==null && (float)$r['avg_c']>0) return (float)$r['avg_c'];
 }catch(Throwable $e){}
 try{
   $st=$pdo->prepare("SELECT AVG(m.litros) as avg_litros FROM mediciones m INNER JOIN sensores s ON s.id_sensor=m.id_sensor INNER JOIN dispositivos d ON d.id_dispositivo=s.id_dispositivo WHERE d.id_tanque=:id AND m.litros IS NOT NULL");
   $st->execute([':id'=>$id_tanque]); $r=$st->fetch(); if($r && $r['avg_litros']!==null) return (float)$r['avg_litros'];
 }catch(Throwable $e){}
 return 0.0;
}
function eva_consumo_serie(PDO $pdo, int $id_tanque, string $period='semana'): array {
 $days=$period==='mes'?30:($period==='anio'?12:7);
 try{
   if($period==='anio'){
     $st=$pdo->prepare("SELECT MONTH(fecha) as m, AVG(litros_consumidos) as tot FROM consumos WHERE id_tanque=:id AND YEAR(fecha)=YEAR(CURDATE()) GROUP BY MONTH(fecha) ORDER BY m ASC");
     $st->execute([':id'=>$id_tanque]); $map=[]; foreach($st->fetchAll() as $r){ $map[(int)$r['m']]=(float)$r['tot']; } $res=[]; for($i=1;$i<=12;$i++) $res[]=$map[$i]??0; if(array_sum($res)>0) return $res;
   } else {
     $daysInt=(int)$days;
     $st=$pdo->prepare("SELECT fecha, litros_consumidos as tot FROM consumos WHERE id_tanque=:id AND fecha >= DATE_SUB(CURDATE(), INTERVAL {$daysInt} DAY) ORDER BY fecha ASC");
     $st->execute([':id'=>$id_tanque]); $map=[]; foreach($st->fetchAll() as $r){ $map[$r['fecha']]=(float)$r['tot']; } $res=[]; for($i=$days-1;$i>=0;$i--){ $d=date('Y-m-d',strtotime("-{$i} days")); $res[]=$map[$d]??0; } if(array_sum($res)>0) return $res;
   }
 }catch(Throwable $e){}
 try{
   if($period==='anio'){
     $st=$pdo->prepare("SELECT MONTH(m.fecha_hora) as m, AVG(m.litros) as tot FROM mediciones m INNER JOIN sensores s ON s.id_sensor=m.id_sensor INNER JOIN dispositivos d ON d.id_dispositivo=s.id_dispositivo WHERE d.id_tanque=:id AND YEAR(m.fecha_hora)=YEAR(CURDATE()) GROUP BY MONTH(m.fecha_hora) ORDER BY m ASC");
     $st->execute([':id'=>$id_tanque]); $map=[]; foreach($st->fetchAll() as $r){ $map[(int)$r['m']]=(float)$r['tot']; } $res=[]; for($i=1;$i<=12;$i++) $res[]=$map[$i]??0; return $res;
   }
   $daysInt=(int)$days;
   $sql="SELECT DATE(m.fecha_hora) as d, AVG(m.litros) as tot FROM mediciones m INNER JOIN sensores s ON s.id_sensor=m.id_sensor INNER JOIN dispositivos d ON d.id_dispositivo=s.id_dispositivo WHERE d.id_tanque=:id AND m.fecha_hora >= DATE_SUB(CURDATE(), INTERVAL {$daysInt} DAY) GROUP BY DATE(m.fecha_hora) ORDER BY d ASC";
   $st=$pdo->prepare($sql); $st->execute([':id'=>$id_tanque]); $map=[]; foreach($st->fetchAll() as $r){ $map[$r['d']]=(float)$r['tot']; } $res=[]; for($i=$days-1;$i>=0;$i--){ $d=date('Y-m-d',strtotime("-{$i} days")); $res[]=$map[$d]??0; } return $res;
 }catch(Throwable $e){ return array_fill(0,$days,0); }
}
function eva_device_status(PDO $pdo, int $id_tanque): string {
 try{
   $st=$pdo->prepare("SELECT estado, ultima_conexion FROM dispositivos WHERE id_tanque=:id ORDER BY ultima_conexion DESC LIMIT 1");
   $st->execute([':id'=>$id_tanque]); $r=$st->fetch(); if(!$r) return 'Desconectado';
   $estado=strtolower((string)($r['estado']??'')); if(in_array($estado,['activo','online','conectado','operativo'],true)) return 'Conectado'; if(in_array($estado,['inactivo','offline','desconectado'],true)) return 'Desconectado';
   if(!empty($r['ultima_conexion'])){ $ts=strtotime((string)$r['ultima_conexion']); if($ts && (time()-$ts)<600) return 'Conectado'; }
   return h($r['estado']??'Desconectado');
 }catch(Throwable $e){ return 'Conectado'; }
}
function eva_estado_texto(int $pct): array {
 if($pct<=10) return ['Crítico','Nivel de agua peligrosamente bajo','alert'];
 if($pct>=90) return ['Sobrecarga','Nivel de agua por encima del máximo','warning'];
 if($pct<=25) return ['Bajo','Nivel de agua bajo, considerar recarga','warning'];
 return ['Normal','Todo funciona correctamente',''];
}
function eva_alert_tipo_map(string $tipo): array {
 $t=strtoupper($tipo); return match($t) {
   'NIVEL_BAJO'=>['warning','warning','Nivel bajo'],
   'NIVEL_ALTO'=>['danger','warning','Nivel alto'],
   'SIN_CONEXION'=>['info','info','Sin conexión'],
   'FALLA_SENSOR'=>['danger','info','Falla de sensor'],
   'CONSUMO_ANORMAL'=>['warning','warning','Consumo anormal'],
   default=>['info','info',h($tipo)],
 };
}
function eva_log_actividad(PDO $pdo, int $uid, string $accion, string $detalle=''): void {
 try{ $pdo->prepare("INSERT INTO log_actividad (id_usuario, accion, detalle, ip, fecha_hora) VALUES (:uid,:acc,:det,:ip,NOW())")->execute([':uid'=>$uid,':acc'=>$accion,':det'=>$detalle,':ip'=>$_SERVER['REMOTE_ADDR']??'']); }catch(Throwable $e){}
}
function eva_sincronizar_consumos(PDO $pdo, ?int $id_tanque=null): int {
 if (file_exists(__DIR__ . '/procesador_mediciones.php')) {
   require_once __DIR__ . '/procesador_mediciones.php';
   if (function_exists('eva_procesador_actualizar_consumos')) {
     if ($id_tanque) return eva_procesador_actualizar_consumos($pdo, $id_tanque);
     $tot = 0;
     try { foreach ($pdo->query("SELECT id_tanque FROM tanques WHERE activo=1")->fetchAll() as $t) $tot += eva_procesador_actualizar_consumos($pdo, (int)$t['id_tanque']); } catch (Throwable $e) {}
     return $tot;
   }
 }
 $inserted=0;
 try{
   $tanques=[];
   if($id_tanque){ $tanques[]=['id_tanque'=>$id_tanque]; }
   else { $tanques=$pdo->query("SELECT id_tanque FROM tanques WHERE activo=1")->fetchAll(); }
   foreach($tanques as $t){
     $tid=(int)$t['id_tanque'];
     $rows=$pdo->prepare("SELECT DATE(m.fecha_hora) as d, MIN(m.litros) as min_l, MAX(m.litros) as max_l, AVG(m.porcentaje) as avg_pct, AVG(m.litros) as avg_litros, COUNT(*) as cnt FROM mediciones m INNER JOIN sensores s ON s.id_sensor=m.id_sensor INNER JOIN dispositivos d ON d.id_dispositivo=s.id_dispositivo WHERE d.id_tanque=:tid GROUP BY DATE(m.fecha_hora) ORDER BY d ASC");
     $rows->execute([':tid'=>$tid]); $days=$rows->fetchAll();
     foreach($days as $day){
       $fecha=$day['d']; if(!$fecha) continue;
       $litros_consumidos=max(0, (float)$day['max_l'] - (float)$day['min_l']);
       if($litros_consumidos==0) $litros_consumidos=(float)$day['avg_litros'];
       $chk=$pdo->prepare("SELECT id_consumo FROM consumos WHERE id_tanque=:tid AND fecha=:f LIMIT 1");
       $chk->execute([':tid'=>$tid,':f'=>$fecha]);
       if($chk->fetch()){ $pdo->prepare("UPDATE consumos SET litros_consumidos=:lit WHERE id_tanque=:tid AND fecha=:f")->execute([':lit'=>$litros_consumidos,':tid'=>$tid,':f'=>$fecha]); }
       else { $pdo->prepare("INSERT INTO consumos (id_tanque, litros_consumidos, fecha) VALUES (:tid,:lit,:f)")->execute([':tid'=>$tid,':lit'=>$litros_consumidos,':f'=>$fecha]); $inserted++; }
     }
   }
 }catch(Throwable $e){ error_log('sincronizar_consumos: '.$e->getMessage()); }
 return $inserted;
}
function eva_sincronizar_alertas(PDO $pdo, ?int $id_tanque=null): int {
 if (file_exists(__DIR__ . '/procesador_mediciones.php')) {
   require_once __DIR__ . '/procesador_mediciones.php';
   if (function_exists('eva_procesador_detectar_alertas')) {
     if ($id_tanque) return eva_procesador_detectar_alertas($pdo, $id_tanque);
     $tot = 0;
     try { foreach ($pdo->query("SELECT id_tanque FROM tanques WHERE activo=1")->fetchAll() as $t) $tot += eva_procesador_detectar_alertas($pdo, (int)$t['id_tanque']); } catch (Throwable $e) {}
     return $tot;
   }
 }
 $created=0;
 try{
   $tanques=[];
   if($id_tanque){ $tanques[]=['id_tanque'=>$id_tanque]; }
   else { $tanques=$pdo->query("SELECT id_tanque FROM tanques WHERE activo=1")->fetchAll(); }
   foreach($tanques as $t){
     $tid=(int)$t['id_tanque'];
     $cfg=$pdo->prepare("SELECT nivel_minimo, nivel_maximo FROM configuracion_alertas WHERE id_tanque=:id LIMIT 1");
     $cfg->execute([':id'=>$tid]); $c=$cfg->fetch();
     if(!$c) continue;
     $min=(float)$c['nivel_minimo']; $max=(float)$c['nivel_maximo'];
     $meds=$pdo->prepare("SELECT m.* FROM mediciones m INNER JOIN sensores s ON s.id_sensor=m.id_sensor INNER JOIN dispositivos d ON d.id_dispositivo=s.id_dispositivo WHERE d.id_tanque=:tid AND DATE(m.fecha_hora)=CURDATE() ORDER BY m.fecha_hora DESC LIMIT 20");
     $meds->execute([':tid'=>$tid]); $rows=$meds->fetchAll();
     foreach($rows as $m){
       $pct=(float)($m['porcentaje']??0); $tipo=null; $desc=null;
       if($pct<= $min && $pct>0){ $tipo='NIVEL_BAJO'; $desc="Nivel bajo {$pct}% <= mínimo {$min}% (medición {$m['id_medicion']})"; }
       elseif($pct>=$max){ $tipo='NIVEL_ALTO'; $desc="Nivel alto {$pct}% >= máximo {$max}%"; }
       if($tipo){
         $chk=$pdo->prepare("SELECT id_alerta FROM alertas WHERE id_tanque=:tid AND tipo=:tipo AND descripcion=:desc AND DATE(fecha_hora)=CURDATE() LIMIT 1");
         $chk->execute([':tid'=>$tid,':tipo'=>$tipo,':desc'=>$desc]);
         if(!$chk->fetch()){
           $pdo->prepare("INSERT INTO alertas (id_tanque, tipo, descripcion, estado) VALUES (:tid,:tipo,:desc,'PENDIENTE')")->execute([':tid'=>$tid,':tipo'=>$tipo,':desc'=>$desc]); $created++;
         }
       }
     }
   }
 }catch(Throwable $e){ error_log('sincronizar_alertas: '.$e->getMessage()); }
 return $created;
}
