<?php
header('Content-Type: application/json; charset=utf-8');
require_once(__DIR__ . '/../../config/db.php');
if(file_exists(__DIR__ . '/../../cliente/includes/helpers.php')) require_once(__DIR__ . '/../../cliente/includes/helpers.php');
$pdo = eva_pdo();

if($_SERVER['REQUEST_METHOD'] !== 'POST'){
 http_response_code(405);
 echo json_encode(['ok'=>false,'error'=>'Método no permitido']); exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if(!$input) $input = $_POST;
$id_sensor = (int)($input['id_sensor'] ?? $input['sensor_id'] ?? 0);
$distancia = isset($input['distancia_cm']) ? (float)$input['distancia_cm'] : (isset($input['distancia']) ? (float)$input['distancia'] : null);
$nivel = isset($input['nivel_cm']) ? (float)$input['nivel_cm'] : null;
$porcentaje = isset($input['porcentaje']) ? (float)$input['porcentaje'] : null;
$litros = isset($input['litros']) ? (float)$input['litros'] : null;
$temperatura = isset($input['temperatura']) ? (float)$input['temperatura'] : null;
$humedad = isset($input['humedad']) ? (float)$input['humedad'] : null;
$fecha_hora = $input['fecha_hora'] ?? null;

if(!$id_sensor){
 try{
   $tmp=$pdo->query("SELECT id_sensor FROM sensores WHERE estado='ACTIVO' ORDER BY id_sensor ASC LIMIT 1")->fetchColumn();
   if($tmp) $id_sensor=(int)$tmp;
   else $id_sensor=(int)$pdo->query("SELECT id_sensor FROM sensores ORDER BY id_sensor ASC LIMIT 1")->fetchColumn();
 }catch(Throwable $e){}
}
if($distancia===null && $nivel===null && $porcentaje===null && $litros===null){
 http_response_code(400);
 echo json_encode(['ok'=>false,'error'=>'Falta distancia/nivel/porcentaje/litros']); exit;
}
try{
  $chk=$pdo->prepare("SELECT id_sensor, id_dispositivo FROM sensores WHERE id_sensor=:id LIMIT 1");
  $chk->execute([':id'=>$id_sensor]);
  $sensor=$chk->fetch();
  if(!$sensor){
    try{
      $alt=$pdo->query("SELECT id_sensor, id_dispositivo FROM sensores WHERE estado='ACTIVO' ORDER BY id_sensor ASC LIMIT 1")->fetch();
      if(!$alt) $alt=$pdo->query("SELECT id_sensor, id_dispositivo FROM sensores ORDER BY id_sensor ASC LIMIT 1")->fetch();
      if($alt) $sensor=$alt;
    }catch(Throwable $e){}
  }
  if(!$sensor){ http_response_code(404); echo json_encode(['ok'=>false,'error'=>'Sensor no existe']); exit; }
  $id_sensor=(int)$sensor['id_sensor'];
  try {
      $tanqueCfg = null;
      try {
          $stTanque = $pdo->prepare("SELECT t.capacidad_litros, t.volumen_util, t.altura_cm, t.diametro FROM tanques t INNER JOIN dispositivos d ON d.id_tanque=t.id_tanque WHERE d.id_dispositivo=:did LIMIT 1");
          $stTanque->execute([':did'=>$sensor['id_dispositivo']]);
          $tanqueCfg = $stTanque->fetch();
      } catch(Throwable $e) {}
      if ($tanqueCfg && function_exists('eva_tanque_capacidad_efectiva')) {
          $capEff = (float)eva_tanque_capacidad_efectiva($tanqueCfg);
          $alt = (float)($tanqueCfg['altura_cm'] ?? 0);
          if($nivel === null && $distancia !== null && $alt > 0){
              $nivel = $alt - (float)$distancia; if($nivel < 0) $nivel=0; if($nivel>$alt) $nivel=$alt;
          }
          if($capEff>0 && $litros!==null && is_numeric($litros) && (float)$litros>0){
              $porcentaje = round(max(0,min(100,(float)$litros/$capEff*100)),2);
              $litros=round(max(0,min($capEff,(float)$litros)),2);
              if($nivel===null && $alt>0) $nivel=round($alt*(float)$porcentaje/100,2);
              if($distancia===null && $alt>0) $distancia=round($alt-$nivel,2);
          }elseif($porcentaje!==null && is_numeric($porcentaje)){
              $porcentaje=round(max(0,min(100,(float)$porcentaje)),2);
              if($capEff>0) $litros=round($capEff*(float)$porcentaje/100,2);
              if($nivel===null && $alt>0) $nivel=round($alt*(float)$porcentaje/100,2);
              if($distancia===null && $alt>0) $distancia=round($alt-$nivel,2);
          }elseif($nivel!==null && is_numeric($nivel) && $alt>0){
              $porcentaje=round(max(0,min(100,(float)$nivel/$alt*100)),2);
              if($capEff>0) $litros=round($capEff*(float)$porcentaje/100,2);
          }elseif($distancia!==null && is_numeric($distancia) && $alt>0){
              $nivel=$alt-(float)$distancia; $porcentaje=round(max(0,min(100,$nivel/$alt*100)),2);
              if($capEff>0) $litros=round($capEff*(float)$porcentaje/100,2); $nivel=round($nivel,2); $distancia=round((float)$distancia,2);
          }else{
              if($porcentaje!==null) $porcentaje=round(max(0,min(100,(float)$porcentaje)),2);
              if($capEff>0 && $porcentaje!==null) $litros=round($capEff*(float)$porcentaje/100,2);
          }
          if($nivel!==null) $nivel=round((float)$nivel,2);
          if($distancia!==null) $distancia=round((float)$distancia,2);
          if($porcentaje!==null) $porcentaje=round((float)$porcentaje,2);
          if($litros!==null) $litros=round((float)$litros,2);
      }
  } catch(Throwable $e) {}
  if($porcentaje!==null && ($porcentaje<0 || $porcentaje>100)){ http_response_code(400); echo json_encode(['ok'=>false,'error'=>'porcentaje fuera de rango']); exit; }
  try{
    $stmt=$pdo->prepare("INSERT INTO mediciones (id_sensor,distancia_cm,nivel_cm,porcentaje,litros,temperatura,humedad,fecha_hora) VALUES (:sid,:d,:n,:p,:l,:t,:h,COALESCE(:fh,NOW()))");
    $stmt->execute([':sid'=>$id_sensor,':d'=>$distancia,':n'=>$nivel,':p'=>$porcentaje,':l'=>$litros,':t'=>$temperatura,':h'=>$humedad,':fh'=>$fecha_hora]);
  } catch(Throwable $e){
    if(stripos($e->getMessage(),'temperatura')!==false || stripos($e->getMessage(),'humedad')!==false){
        $stmt=$pdo->prepare("INSERT INTO mediciones (id_sensor,distancia_cm,nivel_cm,porcentaje,litros,fecha_hora) VALUES (:sid,:d,:n,:p,:l,COALESCE(:fh,NOW()))");
        $stmt->execute([':sid'=>$id_sensor,':d'=>$distancia,':n'=>$nivel,':p'=>$porcentaje,':l'=>$litros,':fh'=>$fecha_hora]);
    } else throw $e;
  }
  $id = (int)$pdo->lastInsertId();
  $pdo->prepare("UPDATE dispositivos SET ultima_conexion=NOW(), ultima_actualizacion=NOW() WHERE id_dispositivo=:did")->execute([':did'=>$sensor['id_dispositivo']]);
  try{ if(file_exists(__DIR__ . '/../../cliente/includes/procesador_mediciones.php')){ require_once __DIR__ . '/../../cliente/includes/procesador_mediciones.php'; $id_tanque=(int)$pdo->query("SELECT id_tanque FROM dispositivos WHERE id_dispositivo=".(int)$sensor['id_dispositivo'])->fetchColumn(); if($id_tanque) eva_procesar_tanque($pdo,$id_tanque); }}catch(Throwable $e){}
 
 try{
   $id_tanque = (int)$pdo->query("SELECT id_tanque FROM dispositivos WHERE id_dispositivo=".(int)$sensor['id_dispositivo'])->fetchColumn();
   if($id_tanque && $porcentaje!==null){
     $cfg=$pdo->prepare("SELECT nivel_minimo,nivel_maximo FROM configuracion_alertas WHERE id_tanque=:t LIMIT 1");
     $cfg->execute([':t'=>$id_tanque]);
     $conf=$cfg->fetch();
     if($conf){
       $tipo=null; $desc=null;
       if($porcentaje <= (float)$conf['nivel_minimo']){ $tipo='NIVEL_BAJO'; $desc="Nivel bajo {$porcentaje}% <= minimo {$conf['nivel_minimo']}%"; }
       elseif($porcentaje >= (float)$conf['nivel_maximo']){ $tipo='NIVEL_ALTO'; $desc="Nivel alto {$porcentaje}% >= maximo {$conf['nivel_maximo']}%"; }
       if($tipo){
         $dup=$pdo->prepare("SELECT id_alerta FROM alertas WHERE id_tanque=:t AND tipo=:tipo AND estado='PENDIENTE' AND fecha_hora>=DATE_SUB(NOW(),INTERVAL 1 HOUR) LIMIT 1");
         $dup->execute([':t'=>$id_tanque,':tipo'=>$tipo]);
         if(!$dup->fetch()){
           $pdo->prepare("INSERT INTO alertas (id_tanque,tipo,descripcion,estado) VALUES (:t,:tipo,:desc,'PENDIENTE')")->execute([':t'=>$id_tanque,':tipo'=>$tipo,':desc'=>$desc]);
         }
       }
     }
   }
 }catch(Throwable $e){ error_log('alerta auto: '.$e->getMessage()); }
 echo json_encode(['ok'=>true,'id_medicion'=>$id]);
}catch(Throwable $e){
 http_response_code(500);
 error_log('ESP32 mediciones: '.$e->getMessage());
 echo json_encode(['ok'=>false,'error'=>'Error interno']);
}
