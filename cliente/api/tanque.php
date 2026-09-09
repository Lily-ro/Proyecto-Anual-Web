<?php
declare(strict_types=1);
session_start();
header('Content-Type: application/json; charset=utf-8');
if(!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'USUARIO'){ http_response_code(401); echo json_encode(['error'=>'No autorizado']); exit; }
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../includes/helpers.php';
try{
    $pdo=eva_pdo();
    $uid=eva_current_user_id();
    $tanque=eva_first_tanque($pdo,$uid);
    if(!$tanque){ echo json_encode(['error'=>'Sin tanques']); exit;}
     $idTanque=(int)($tanque['id_tanque']??0);
     $capacidad=(int)round(eva_tanque_capacidad_efectiva($tanque));
     $med=eva_latest_medicion($pdo,$idTanque);
      $pct=0;$temp=0;
      $lastUpdate=null;
      if($med){
          $pct=eva_calcular_pct($tanque, $med);
          if(isset($med['temperatura'])&&is_numeric($med['temperatura'])) $temp=(int)round((float)$med['temperatura']);
          $lastUpdate=$med['fecha_hora']??null;
          if($lastUpdate) $lastUpdate=date('d/m/Y H:i',strtotime($lastUpdate));
      }
      $pct=max(0,min(100,$pct));
      $litros=(int)round(eva_calcular_litros($tanque, $med ?? [], $pct));
      try{ eva_sincronizar_consumos($pdo,$idTanque); }catch(Throwable $e){}
      try{ eva_sincronizar_alertas($pdo,$idTanque); }catch(Throwable $e){}
     echo json_encode([
         'pct'=>$pct,
         'temp'=>$temp,
         'capacidad'=>$capacidad,
         'litros'=>$litros,
         'estado'=>eva_estado_texto($pct)[0],
         'lastUpdate'=>$lastUpdate,
         'advertencia'=>eva_tanque_advertencia($tanque)
     ]);
}catch(Throwable $e){ http_response_code(500); echo json_encode(['error'=>$e->getMessage()]); }
