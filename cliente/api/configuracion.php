<?php
declare(strict_types=1);
session_start();
header('Content-Type: application/json; charset=utf-8');
if(!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'USUARIO'){ http_response_code(401); echo json_encode(['error'=>'No autorizado']); exit; }
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../includes/helpers.php';

if($_SERVER['REQUEST_METHOD']==='GET'){
    try{
        $pdo=eva_pdo();
        $uid=eva_current_user_id();
        $tanque=eva_first_tanque($pdo,$uid);
        $idTanque=$tanque?(int)($tanque['id_tanque']??0):0;
        $low=20;$high=90;
        $cols=[]; try{$st=$pdo->query("SHOW COLUMNS FROM configuracion_alertas"); foreach($st->fetchAll() as $c) $cols[]=$c['Field'];}catch(Throwable $e){}
        $hasTipoValor=in_array('tipo',$cols,true) && in_array('valor',$cols,true);
        $hasNivel=in_array('nivel_minimo',$cols,true) && in_array('nivel_maximo',$cols,true);
        if($hasNivel){
            $st=$pdo->prepare("SELECT nivel_minimo, nivel_maximo FROM configuracion_alertas WHERE id_tanque=:id LIMIT 1");
            $st->execute([':id'=>$idTanque]); $cfg=$st->fetch();
            if($cfg){ if(isset($cfg['nivel_minimo'])&&is_numeric($cfg['nivel_minimo'])) $low=(int)$cfg['nivel_minimo']; if(isset($cfg['nivel_maximo'])&&is_numeric($cfg['nivel_maximo'])) $high=(int)$cfg['nivel_maximo']; }
        } elseif($hasTipoValor){
            $st=$pdo->prepare("SELECT tipo, valor AS v FROM configuracion_alertas WHERE id_tanque=:id");
            $st->execute([':id'=>$idTanque]);
            foreach($st->fetchAll() as $r){ $t=strtoupper($r['tipo']??''); if($t==='NIVEL_BAJO') $low=(int)$r['v']; if($t==='NIVEL_ALTO') $high=(int)$r['v']; }
        }
        echo json_encode(['low'=>$low,'high'=>$high]);
    }catch(Throwable $e){ http_response_code(500); echo json_encode(['error'=>$e->getMessage()]); }
    exit;
}
if($_SERVER['REQUEST_METHOD']==='POST'){
    $input=json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $low=(int)($input['low']??$input['nivel_bajo']??20);
    $high=(int)($input['high']??$input['nivel_alto']??90);
    $token=$input['csrf']??$input['token']??'';
    if($token && !eva_csrf_validate($token)){ http_response_code(403); echo json_encode(['error'=>'CSRF invalido']); exit; }
    if($low<10||$low>50||$high<50||$high>100||$low>=$high){ http_response_code(400); echo json_encode(['error'=>'Valores invalidos']); exit; }
    try{
        $pdo=eva_pdo();
        $tanque=eva_first_tanque($pdo, eva_current_user_id());
        $idTanque=$tanque?(int)($tanque['id_tanque']??0):0;
        $cols=[]; $st=$pdo->query("SHOW COLUMNS FROM configuracion_alertas"); foreach($st->fetchAll() as $c) $cols[]=$c['Field'];
        $hasTipoValor=in_array('tipo',$cols,true) && in_array('valor',$cols,true);
        $hasNivel=in_array('nivel_minimo',$cols,true) && in_array('nivel_maximo',$cols,true);
        if($hasNivel){
            $chk=$pdo->prepare("SELECT id_config FROM configuracion_alertas WHERE id_tanque=:id LIMIT 1");
            $chk->execute([':id'=>$idTanque]); $ex=$chk->fetch();
            if($ex) $pdo->prepare("UPDATE configuracion_alertas SET nivel_minimo=:low, nivel_maximo=:high WHERE id_config=:cid")->execute([':low'=>$low,':high'=>$high,':cid'=>$ex['id_config']]);
            else $pdo->prepare("INSERT INTO configuracion_alertas (id_tanque, nivel_minimo, nivel_maximo, notificar_email, notificar_sistema) VALUES (:id,:low,:high,1,1)")->execute([':id'=>$idTanque,':low'=>$low,':high'=>$high]);
            eva_log_actividad($pdo,(int)eva_current_user_id(),'ACTUALIZAR_CONFIG_ALERTA',"bajo={$low} alto={$high}");
        } elseif($hasTipoValor){
            foreach(['NIVEL_BAJO'=>$low,'NIVEL_ALTO'=>$high] as $tipo=>$val){
                $chk=$pdo->prepare("SELECT id_configuracion FROM configuracion_alertas WHERE id_tanque=:id AND tipo=:tipo LIMIT 1");
                $chk->execute([':id'=>$idTanque,':tipo'=>$tipo]); $ex=$chk->fetch();
                if($ex) $pdo->prepare("UPDATE configuracion_alertas SET valor=:v WHERE id_configuracion=:cid")->execute([':v'=>$val,':cid'=>$ex['id_configuracion']]);
                else $pdo->prepare("INSERT INTO configuracion_alertas (id_tanque,tipo,valor) VALUES (:id,:tipo,:v)")->execute([':id'=>$idTanque,':tipo'=>$tipo,':v'=>$val]);
            }
        } else {
            echo json_encode(['error'=>'Estructura no soportada']); exit;
        }
        echo json_encode(['ok'=>true,'low'=>$low,'high'=>$high]);
    }catch(Throwable $e){ http_response_code(500); echo json_encode(['error'=>$e->getMessage()]); }
    exit;
}
http_response_code(405); echo json_encode(['error'=>'Metodo no permitido']);
