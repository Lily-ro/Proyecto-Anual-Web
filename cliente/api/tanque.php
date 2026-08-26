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
        if(isset($med['temperatura'])&&is_numeric($med['temperatura'])) $temp=(int)round((float)$med['temperatura']);
        $lastUpdate=$med['fecha']??null;
        if(isset($med['hora'])) $lastUpdate=trim(($lastUpdate??'').' '. $med['hora']);
    }
    echo json_encode([
        'pct'=>max(0,min(100,$pct)),
        'temp'=>$temp,
        'capacidad'=>$capacidad,
        'litros'=>(int)round($capacidad*$pct/100),
        'estado'=>eva_estado_texto($pct)[0],
        'lastUpdate'=>$lastUpdate
    ]);
}catch(Throwable $e){ http_response_code(500); echo json_encode(['error'=>$e->getMessage()]); }
