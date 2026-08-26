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
    $hasIdTanque=false;
    try{$c=$pdo->query("SHOW COLUMNS FROM alertas LIKE 'id_tanque'"); $hasIdTanque=$c&&$c->rowCount()>0;}catch(Throwable $e){}
    $sql="SELECT a.*, ca.tipo AS cfg_tipo FROM alertas a LEFT JOIN configuracion_alertas ca ON ca.id_configuracion=a.id_configuracion ";
    $params=[];
    if($hasIdTanque && $idTanque){ $sql.="WHERE a.id_tanque=:id "; $params[':id']=$idTanque; }
    $sql.="ORDER BY a.fecha DESC LIMIT 100";
    $st=$pdo->prepare($sql);
    $st->execute($params);
    $rows=$st->fetchAll();
    $out=[];
    foreach($rows as $r){
        $tipo=$r['tipo']??$r['cfg_tipo']??'NIVEL_BAJO';
        $estadoRaw=strtoupper(trim($r['estado']??'PENDIENTE'));
        $estadoUI=match($estadoRaw){'PENDIENTE'=>'activo','ATENDIDA'=>'en-revision','CERRADA'=>'resuelta', default=>strtolower($estadoRaw)};
        $statusForFilter=($estadoUI==='activo'||$estadoUI==='en-revision')?'activo':'resuelta';
        if($filter==='activas' && $statusForFilter!=='activo') continue;
        if($filter==='resueltas' && $statusForFilter!=='resuelta') continue;
        [$badge,$icon,$titulo]=eva_alert_tipo_map((string)$tipo);
        $desc=$r['mensaje']??$r['descripcion']??'';
        $fechaRaw=$r['fecha']??'';
        $fechaFmt=$fechaRaw? (date('Y-m-d',strtotime($fechaRaw))===date('Y-m-d')?'Hoy '.date('H:i',strtotime($fechaRaw)):date('d/m/Y H:i',strtotime($fechaRaw))):'';
        $out[]=['type'=>$badge,'icon'=>$icon,'title'=>$titulo,'desc'=>htmlspecialchars($desc),'date'=>$fechaFmt,'status'=>$statusForFilter,'estadoRaw'=>$estadoRaw];
    }
    echo json_encode($out, JSON_UNESCAPED_UNICODE);
}catch(Throwable $e){ http_response_code(500); echo json_encode(['error'=>$e->getMessage()]); }
