<?php
declare(strict_types=1);
session_start();
header('Content-Type: application/json; charset=utf-8');
if(!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'USUARIO'){ http_response_code(401); echo json_encode(['error'=>'No autorizado']); exit; }
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../includes/helpers.php';
$filter=$_GET['filter']??'todas';
try{
    $pdo=eva_pdo();
    $uid=eva_current_user_id();
    $tanque=eva_first_tanque($pdo,$uid);
    $idTanque=$tanque ? (int)($tanque['id_tanque']??0): null;
    $params=[];
    if($idTanque){
        $sql="SELECT a.* FROM alertas a WHERE a.id_tanque=:id ORDER BY a.fecha_hora DESC, a.id_alerta DESC LIMIT 100";
        $params[':id']=$idTanque;
    } else {
        $sql="SELECT a.* FROM alertas a ORDER BY a.fecha_hora DESC, a.id_alerta DESC LIMIT 100";
    }
    $st=$pdo->prepare($sql);
    $st->execute($params);
    $rows=$st->fetchAll();
    $out=[];
    foreach($rows as $r){
        $tipo=$r['tipo']??'NIVEL_BAJO';
        $estadoRaw=strtoupper(trim($r['estado']??'PENDIENTE'));
        $estadoUI=match($estadoRaw){'PENDIENTE'=>'activo','ATENDIDA'=>'en-revision','CERRADA'=>'resuelta','RESUELTA'=>'resuelta', default=>strtolower($estadoRaw)};
        $statusForFilter=($estadoUI==='activo'||$estadoUI==='en-revision')?'activo':'resuelta';
        if($filter==='activas' && $statusForFilter!=='activo') continue;
        if($filter==='resueltas' && $statusForFilter!=='resuelta') continue;
        if($filter==='todas'){}
        [$badge,$icon,$titulo]=eva_alert_tipo_map((string)$tipo);
        $desc=$r['descripcion']??$r['mensaje']??'';
        $fechaRaw=$r['fecha_hora']??$r['fecha']??'';
        $fechaFmt='';
        if($fechaRaw){ $ts=strtotime((string)$fechaRaw); if($ts){ if(date('Y-m-d',$ts)===date('Y-m-d')) $fechaFmt='Hoy '.date('H:i',$ts); else $fechaFmt=date('d \d\e M',$ts); } else $fechaFmt=htmlspecialchars((string)$fechaRaw); }
        $out[]=['type'=>$badge,'icon'=>$icon,'title'=>$titulo,'desc'=>htmlspecialchars($desc),'date'=>$fechaFmt,'status'=>$statusForFilter,'estadoRaw'=>$estadoRaw];
    }
    echo json_encode($out, JSON_UNESCAPED_UNICODE);
}catch(Throwable $e){ http_response_code(500); echo json_encode(['error'=>$e->getMessage()]); }
