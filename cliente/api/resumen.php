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
    $capacidad = (int)round(eva_tanque_capacidad_efectiva($tanque));
    $med = eva_latest_medicion($pdo, $idTanque);
    $pct = 0; $temp=0;
    if($med){
        $pct=eva_calcular_pct($tanque, $med);
        if(isset($med['temperatura']) && is_numeric($med['temperatura'])) $temp=(int)round((float)$med['temperatura']);
    }
    $pct=max(0,min(100,$pct));
    try{ eva_sincronizar_consumos($pdo,$idTanque); }catch(Throwable $e){}
    try{ eva_sincronizar_alertas($pdo,$idTanque); }catch(Throwable $e){}
    $period = $_GET['period'] ?? 'semana';
    if(!in_array($period,['semana','mes','anio'],true)) $period='semana';
    $serie = eva_consumo_serie($pdo, $idTanque, $period);
    $disponible = (int)round(eva_calcular_litros($tanque, $med ?? [], $pct));
    [$estadoTexto,$estadoDesc,$estadoClass]=eva_estado_texto($pct);
    $lastUpdate = $med['fecha_hora'] ?? null;
    if($lastUpdate) $lastUpdate=date('d/m/Y H:i',strtotime($lastUpdate));
    echo json_encode([
        'pct'=>$pct,
        'temp'=>$temp,
        'capacidad'=>$capacidad,
        'disponible'=>$disponible,
        'consumoHoy'=>(int)round(eva_consumo_hoy($pdo,$idTanque)),
        'promedio'=>(int)round(eva_consumo_promedio($pdo,$idTanque)),
        'serie'=>$serie,
        'period'=>$period,
        'estado'=>$estadoTexto,
        'estadoClass'=>$estadoClass,
        'lastUpdate'=>$lastUpdate,
        'advertencia'=>eva_tanque_advertencia($tanque)
    ], JSON_UNESCAPED_UNICODE);
} catch(Throwable $e){ http_response_code(500); echo json_encode(['error'=>$e->getMessage()]); }
