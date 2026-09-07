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
    $capacidad=(int)($tanque['capacidad_litros']??0);
    $med=eva_latest_medicion($pdo,$idTanque);
     $pct=0;$temp=0;
     $lastUpdate=null;
     if($med){
         if(isset($med['porcentaje'])&&is_numeric($med['porcentaje'])) $pct=(int)round((float)$med['porcentaje']);
         elseif(isset($med['nivel_cm']) && is_numeric($med['nivel_cm']) && isset($tanque['altura_cm']) && (float)$tanque['altura_cm']>0) $pct=max(0,min(100,(int)round((float)$med['nivel_cm']/(float)$tanque['altura_cm']*100)));
         elseif(isset($med['distancia_cm']) && isset($tanque['altura_cm']) && (float)$tanque['altura_cm']>0){ $nivel=(float)$tanque['altura_cm']-(float)$med['distancia_cm']; $pct=max(0,min(100,(int)round($nivel/(float)$tanque['altura_cm']*100))); }
         if(isset($med['temperatura'])&&is_numeric($med['temperatura'])) $temp=(int)round((float)$med['temperatura']);
         $lastUpdate=$med['fecha_hora']??null;
         if($lastUpdate) $lastUpdate=date('d/m/Y H:i',strtotime($lastUpdate));
     }
     try{ eva_sincronizar_consumos($pdo,$idTanque); }catch(Throwable $e){}
     try{ eva_sincronizar_alertas($pdo,$idTanque); }catch(Throwable $e){}
    echo json_encode([
        'pct'=>max(0,min(100,$pct)),
        'temp'=>$temp,
        'capacidad'=>$capacidad,
        'litros'=>(int)round($capacidad*$pct/100),
        'estado'=>eva_estado_texto($pct)[0],
        'lastUpdate'=>$lastUpdate
    ]);
}catch(Throwable $e){ http_response_code(500); echo json_encode(['error'=>$e->getMessage()]); }
