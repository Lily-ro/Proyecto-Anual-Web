<?php
header('Content-Type: application/json; charset=utf-8');
require_once(__DIR__ . '/../../config/db.php');
$pdo = eva_pdo();

// Validar método
if($_SERVER['REQUEST_METHOD'] !== 'POST'){
 http_response_code(405);
 echo json_encode(['ok'=>false,'error'=>'Método no permitido']); exit;
}

// Leer datos JSON o x-www-form
$input = json_decode(file_get_contents('php://input'), true);
if(!$input) $input = $_POST;
$id_sensor = (int)($input['id_sensor'] ?? $input['sensor_id'] ?? 0);
$distancia = isset($input['distancia_cm']) ? (float)$input['distancia_cm'] : (isset($input['distancia']) ? (float)$input['distancia'] : null);
$nivel = isset($input['nivel_cm']) ? (float)$input['nivel_cm'] : null;
$porcentaje = isset($input['porcentaje']) ? (float)$input['porcentaje'] : null;
$litros = isset($input['litros']) ? (float)$input['litros'] : null;
$fecha_hora = $input['fecha_hora'] ?? null;

if(!$id_sensor){
 http_response_code(400);
 echo json_encode(['ok'=>false,'error'=>'id_sensor requerido']); exit;
}
if($distancia===null && $nivel===null && $porcentaje===null){
 http_response_code(400);
 echo json_encode(['ok'=>false,'error'=>'Falta distancia/nivel/porcentaje']); exit;
}
try{
 $chk=$pdo->prepare("SELECT id_sensor, id_dispositivo FROM sensores WHERE id_sensor=:id LIMIT 1");
 $chk->execute([':id'=>$id_sensor]);
 $sensor=$chk->fetch();
 if(!$sensor){ http_response_code(404); echo json_encode(['ok'=>false,'error'=>'Sensor no existe']); exit; }
 // validar rangos si hay
 if($porcentaje!==null && ($porcentaje<0 || $porcentaje>100)){ http_response_code(400); echo json_encode(['ok'=>false,'error'=>'porcentaje fuera de rango']); exit; }
 $stmt=$pdo->prepare("INSERT INTO mediciones (id_sensor,distancia_cm,nivel_cm,porcentaje,litros,fecha_hora) VALUES (:sid,:d,:n,:p,:l,COALESCE(:fh,NOW()))");
 $stmt->execute([':sid'=>$id_sensor,':d'=>$distancia,':n'=>$nivel,':p'=>$porcentaje,':l'=>$litros,':fh'=>$fecha_hora]);
 $id = (int)$pdo->lastInsertId();
 // Actualizar dispositivo ultima_conexion
 $pdo->prepare("UPDATE dispositivos SET ultima_conexion=NOW(), ultima_actualizacion=NOW() WHERE id_dispositivo=:did")->execute([':did'=>$sensor['id_dispositivo']]);
 // Verificar alertas automáticas via configuracion_alertas
 try{
   $id_tanque = (int)$pdo->query("SELECT id_tanque FROM dispositivos WHERE id_dispositivo=".(int)$sensor['id_dispositivo'])->fetchColumn();
   if($id_tanque && $porcentaje!==null){
     $cfg=$pdo->prepare("SELECT nivel_minimo,nivel_maximo FROM configuracion_alertas WHERE id_tanque=:t LIMIT 1");
     $cfg->execute([':t'=>$id_tanque]);
     $conf=$cfg->fetch();
     if($conf){
       $tipo=null; $desc=null;
       if($porcentaje <= (float)$conf['nivel_minimo']){ $tipo='NIVEL_BAJO'; $desc="Nivel bajo {$porcentaje}% <= minimo {$conf['nivel_minimo']}%"; }
       elseif($porcentaje >= (float)$conf['nivel_maximo']){ $tipo='NIVEL_ALTO'; $desc="Nivel alto {$porcentaje}% >= maximo {$conf['nivel_maximo']}%"; }
       if($tipo){
         // evitar duplicar alerta pendiente misma tipo última hora
         $dup=$pdo->prepare("SELECT id_alerta FROM alertas WHERE id_tanque=:t AND tipo=:tipo AND estado='PENDIENTE' AND fecha_hora>=DATE_SUB(NOW(),INTERVAL 1 HOUR) LIMIT 1");
         $dup->execute([':t'=>$id_tanque,':tipo'=>$tipo]);
         if(!$dup->fetch()){
           $pdo->prepare("INSERT INTO alertas (id_tanque,tipo,descripcion,estado) VALUES (:t,:tipo,:desc,'PENDIENTE')")->execute([':t'=>$id_tanque,':tipo'=>$tipo,':desc'=>$desc]);
         }
       }
     }
   }
 }catch(Throwable $e){ error_log('alerta auto: '.$e->getMessage()); }
 echo json_encode(['ok'=>true,'id_medicion'=>$id]);
}catch(Throwable $e){
 http_response_code(500);
 error_log('ESP32 mediciones: '.$e->getMessage());
 echo json_encode(['ok'=>false,'error'=>'Error interno']);
}
