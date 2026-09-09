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
        $pctRaw = isset($r['porcentaje']) && is_numeric($r['porcentaje']) ? (int)round((float)$r['porcentaje']) : 0;
        [$estTxt] = eva_estado_texto(max(0,min(100,$pctRaw)));
        $out[]=[
            'fecha'=>$ts?date('d/m/Y',$ts):'-',
            'hora'=>$ts?date('H:i',$ts):'-',
            'nivel'=>isset($r['nivel_cm'])?(int)round((float)$r['nivel_cm']):'-',
            'pct'=>isset($r['porcentaje'])?(int)round((float)$r['porcentaje']):'-',
            'tmp'=>isset($r['temperatura'])?(int)round((float)$r['temperatura']):'-',
            'hum'=>isset($r['humedad'])?(int)round((float)$r['humedad']):'-',
            'estado'=>$estTxt
        ];
    }
    $chartData=['semana'=>[],'mes'=>[],'trimestre'=>[]];
    try{
        $tidForChart=$idTanque;
        if(!$tidForChart && !empty($tanques)) $tidForChart=(int)($tanques[0]['id_tanque']??0);
        if($tidForChart){
            $qc=$pdo->prepare("SELECT m.porcentaje, m.fecha_hora FROM mediciones m INNER JOIN sensores s ON s.id_sensor=m.id_sensor INNER JOIN dispositivos d ON d.id_dispositivo=s.id_dispositivo WHERE d.id_tanque=:tid AND m.porcentaje IS NOT NULL ORDER BY m.fecha_hora DESC LIMIT 90");
            $qc->execute([':tid'=>$tidForChart]); $lastRows=$qc->fetchAll();
            if($lastRows){
                $byDay=[]; foreach(array_reverse($lastRows) as $lr){ $dk=substr($lr['fecha_hora'],0,10); $byDay[$dk][]=(float)$lr['porcentaje']; }
                $avgByDay=[]; foreach($byDay as $k=>$arr){ $avgByDay[$k]=(int)round(array_sum($arr)/count($arr)); }
                $s=[]; for($i=6;$i>=0;$i--){ $dk=date('Y-m-d', strtotime("-$i days")); $s[]=$avgByDay[$dk] ?? 0; }
                if(array_sum($s)===0){ $pcts=array_map(fn($x)=>(int)round((float)($x['porcentaje']??0)), array_slice(array_reverse($lastRows),0,7)); $s=array_slice(array_pad($pcts,7,0),-7); }
                $chartData['semana']=$s;
                $m=[]; for($i=29;$i>=0;$i--){ $dk=date('Y-m-d', strtotime("-$i days")); $m[]=$avgByDay[$dk] ?? 0; }
                $chartData['mes']=$m;
                $all=[]; for($i=89;$i>=0;$i--){ $dk=date('Y-m-d', strtotime("-$i days")); $all[]=$avgByDay[$dk] ?? 0; }
                $chunked=array_chunk($all,(int)ceil(count($all)/12));
                $t=array_map(fn($ch)=> count($ch)? (int)round(array_sum($ch)/count($ch)):0, $chunked);
                while(count($t)<12) $t[]=0;
                $chartData['trimestre']=array_slice($t,0,12);
            }
        }
        if(array_sum($chartData['semana'])===0){
            $qAll=$pdo->query("SELECT porcentaje FROM mediciones WHERE porcentaje IS NOT NULL ORDER BY fecha_hora DESC LIMIT 30");
            $allPcts=$qAll->fetchAll(PDO::FETCH_COLUMN);
            if($allPcts){ $allPcts=array_map(fn($v)=>(int)round((float)$v), array_reverse($allPcts)); $chartData['semana']=array_slice(array_pad($allPcts,7,0),-7); $chartData['mes']=array_slice(array_pad($allPcts,30,0),-30); }
        }
    }catch(Throwable $e){}
    echo json_encode(['rows'=>$out,'chartData'=>$chartData], JSON_UNESCAPED_UNICODE);
}catch(Throwable $e){ http_response_code(500); echo json_encode(['error'=>$e->getMessage()]); }
