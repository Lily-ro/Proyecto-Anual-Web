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
    elseif(!empty($tanques)) $idTanque=(int)($tanques[0]['id_tanque']??0);
    $params=[':desde'=>$desde, ':hasta'=>$hasta];
    $where="DATE(m.fecha_hora) BETWEEN :desde AND :hasta";
    if($idTanque){
        $where.=" AND (d.id_tanque=:tid OR d.id_tanque IS NULL)";
        $params[':tid']=$idTanque;
        $sql="SELECT m.* FROM mediciones m LEFT JOIN sensores s ON s.id_sensor=m.id_sensor LEFT JOIN dispositivos d ON d.id_dispositivo=s.id_dispositivo WHERE $where ORDER BY m.fecha_hora DESC LIMIT 200";
    } else {
        $ids=array_map(fn($t)=>(int)($t['id_tanque']??0),$tanques);
        $ids=array_filter($ids);
        if($ids){ $in=implode(',',$ids); $sql="SELECT m.* FROM mediciones m LEFT JOIN sensores s ON s.id_sensor=m.id_sensor LEFT JOIN dispositivos d ON d.id_dispositivo=s.id_dispositivo WHERE (d.id_tanque IN ($in) OR d.id_tanque IS NULL) AND $where ORDER BY m.fecha_hora DESC LIMIT 200"; }
        else { $sql="SELECT m.* FROM mediciones m LEFT JOIN sensores s ON s.id_sensor=m.id_sensor LEFT JOIN dispositivos d ON d.id_dispositivo=s.id_dispositivo WHERE $where ORDER BY m.fecha_hora DESC LIMIT 200"; }
    }
    $st=$pdo->prepare($sql); $st->execute($params); $rows=$st->fetchAll();
    if(!$rows){
        $st2=$pdo->prepare("SELECT * FROM mediciones WHERE DATE(fecha_hora) BETWEEN :desde AND :hasta ORDER BY fecha_hora DESC LIMIT 200");
        $st2->execute([':desde'=>$desde,':hasta'=>$hasta]); $rows=$st2->fetchAll();
    }
    $out=[];
    foreach($rows as $r){
        $fechaHora=$r['fecha_hora']??'';
        $ts=$fechaHora?strtotime($fechaHora):null;
        $out[]=[
            'fecha'=>$ts?date('d/m/Y',$ts):'-',
            'hora'=>$ts?date('H:i',$ts):'-',
            'nivel'=>isset($r['nivel_cm'])?(int)round((float)$r['nivel_cm']):'-',
            'pct'=>isset($r['porcentaje'])?(int)round((float)$r['porcentaje']):'-',
            'tmp'=>isset($r['temperatura'])?(int)round((float)$r['temperatura']):'-',
            'hum'=>isset($r['humedad'])?(int)round((float)$r['humedad']):'-',
            'estado'=> (isset($r['porcentaje']) && (int)$r['porcentaje']<=20?'Bajo':((int)$r['porcentaje']>=90?'Alto':'Normal'))
        ];
    }
    echo json_encode(['rows'=>$out], JSON_UNESCAPED_UNICODE);
}catch(Throwable $e){ http_response_code(500); echo json_encode(['error'=>$e->getMessage()]); }
