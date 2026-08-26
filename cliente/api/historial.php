<?php
declare(strict_types=1);
session_start();
header('Content-Type: application/json; charset=utf-8');
if(!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'USUARIO'){ http_response_code(401); echo json_encode(['error'=>'No autorizado']); exit; }
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../includes/helpers.php';
$desde=$_GET['desde']??date('Y-m-d',strtotime('-14 days'));
$hasta=$_GET['hasta']??date('Y-m-d');
$tanque=$_GET['tanque']??'todos';
try{
    $pdo=eva_pdo();
    $uid=eva_current_user_id();
    $tanques=eva_tanques_for_user($pdo,$uid);
    $idTanque=null;
    if($tanque!=='todos' && is_numeric($tanque)) $idTanque=(int)$tanque;
    $hasIdTanque=false;
    try{$c=$pdo->query("SHOW COLUMNS FROM mediciones LIKE 'id_tanque'"); $hasIdTanque=$c&&$c->rowCount()>0;}catch(Throwable $e){}
    $params=[':desde'=>$desde, ':hasta'=>$hasta];
    $where=["DATE(m.fecha) BETWEEN :desde AND :hasta"];
    if($hasIdTanque && $idTanque){ $where[]="m.id_tanque=:id_tanque"; $params[':id_tanque']=$idTanque; }
    if($hasIdTanque){
        $sql="SELECT m.* FROM mediciones m WHERE ".implode(' AND ',$where)." ORDER BY m.fecha DESC LIMIT 200";
        $st=$pdo->prepare($sql); $st->execute($params); $rows=$st->fetchAll();
    } else {
        $sql="SELECT m.* FROM mediciones m WHERE DATE(m.fecha) BETWEEN :desde AND :hasta ORDER BY m.fecha DESC LIMIT 200";
        $st=$pdo->prepare($sql); $st->execute([':desde'=>$desde, ':hasta'=>$hasta]); $rows=$st->fetchAll();
    }
    $out=[];
    foreach($rows as $r){
        $fechaRaw=$r['fecha']??'';
        $horaRaw=$r['hora']??'';
        $ts=strtotime(trim($fechaRaw.' '.$horaRaw));
        $out[]=[
            'fecha'=>$fechaRaw?date('d/m/Y',$ts):'-',
            'hora'=>$horaRaw?date('H:i',$ts):'-',
            'nivel'=>$r['nivel']??$r['distancia']??'-',
            'pct'=>$r['porcentaje']??$r['nivel_porcentaje']??'-',
            'tmp'=>$r['temperatura']??'-',
            'hum'=>$r['humedad']??'-',
            'estado'=> (isset($r['porcentaje']) && (int)$r['porcentaje']<=20?'Bajo':'Normal')
        ];
    }
    echo json_encode(['rows'=>$out], JSON_UNESCAPED_UNICODE);
}catch(Throwable $e){ http_response_code(500); echo json_encode(['error'=>$e->getMessage()]); }
