<?php
declare(strict_types=1);
session_start();
header('Content-Type: application/json; charset=utf-8');
if(!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'USUARIO'){ http_response_code(401); echo json_encode(['error'=>'No autorizado']); exit; }
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../includes/helpers.php';
try {
    $pdo = eva_pdo();
    $uid = eva_current_user_id();
    $tanque = eva_first_tanque($pdo, $uid);
    if(!$tanque){ echo json_encode(['error'=>'Sin tanques']); exit; }
    $idTanque = (int)($tanque['id_tanque'] ?? 0);
    $capacidad = (int)($tanque['capacidad_litros'] ?? 0);
    $med = eva_latest_medicion($pdo, $idTanque);
    $pct = 0; $temp=0;
    if($med){
        if(isset($med['porcentaje']) && is_numeric($med['porcentaje'])) $pct=(int)round((float)$med['porcentaje']);
        if(isset($med['temperatura']) && is_numeric($med['temperatura'])) $temp=(int)round((float)$med['temperatura']);
    }
    $period = $_GET['period'] ?? 'semana';
    if(!in_array($period,['semana','mes','anio'],true)) $period='semana';
    $serie = eva_consumo_serie($pdo, $idTanque, $period);
    $disponible = (int)round($capacidad*$pct/100);
    echo json_encode([
        'pct'=>$pct,
        'temp'=>$temp,
        'capacidad'=>$capacidad,
        'disponible'=>$disponible,
        'consumoHoy'=>(int)round(eva_consumo_hoy($pdo,$idTanque)),
        'promedio'=>(int)round(eva_consumo_promedio($pdo,$idTanque)),
        'serie'=>$serie,
        'period'=>$period
    ], JSON_UNESCAPED_UNICODE);
} catch(Throwable $e){ http_response_code(500); echo json_encode(['error'=>$e->getMessage()]); }
