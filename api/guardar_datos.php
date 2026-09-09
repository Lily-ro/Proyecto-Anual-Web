<?php

header("Content-Type: application/json; charset=utf-8");

error_reporting(E_ALL);
ini_set("display_errors", "0");

require_once __DIR__ . "/../config/db.php";

try {
    $input = json_decode(file_get_contents('php://input'), true);
    if(!$input) $input = $_POST;
    $id_sensor = (int)($input["id_sensor"] ?? $input["sensor_id"] ?? 0);
    $temperatura = $input["temperatura"] ?? null;
    $humedad = $input["humedad"] ?? null;
    $distancia = $input["distancia_cm"] ?? $input["distancia"] ?? null;
    $nivel = $input["nivel_cm"] ?? null;
    $porcentaje = $input["porcentaje"] ?? null;
    $litros = $input["litros"] ?? null;
    $fecha_hora = $input["fecha_hora"] ?? null;

    if ($distancia === null && $nivel === null && $porcentaje === null && $litros === null) {
        http_response_code(400);
        echo json_encode(["success" => false, "mensaje" => "Faltan datos distancia/nivel/porcentaje/litros"]);
        exit;
    }
    if(!$id_sensor){
        try{
            $tmp=$pdo->query("SELECT id_sensor FROM sensores WHERE estado='ACTIVO' ORDER BY id_sensor ASC LIMIT 1")->fetchColumn();
            if($tmp) $id_sensor=(int)$tmp;
            else $id_sensor=(int)$pdo->query("SELECT id_sensor FROM sensores ORDER BY id_sensor ASC LIMIT 1")->fetchColumn();
        }catch(Throwable $e){}
    }
    if(!$id_sensor){ http_response_code(400); echo json_encode(["success"=>false,"mensaje"=>"id_sensor requerido"]); exit; }
    $chk=$pdo->prepare("SELECT id_sensor, id_dispositivo FROM sensores WHERE id_sensor=:id LIMIT 1");
    $chk->execute([':id'=>$id_sensor]);
    $sensor=$chk->fetch();
    if(!$sensor){
        try{
            $alt=$pdo->query("SELECT id_sensor, id_dispositivo FROM sensores WHERE estado='ACTIVO' ORDER BY id_sensor ASC LIMIT 1")->fetch();
            if(!$alt) $alt=$pdo->query("SELECT id_sensor, id_dispositivo FROM sensores ORDER BY id_sensor ASC LIMIT 1")->fetch();
            if($alt) $sensor=$alt;
        }catch(Throwable $e){}
    }
    if(!$sensor){ http_response_code(404); echo json_encode(["success"=>false,"mensaje"=>"Sensor no existe"]); exit; }
    $id_sensor = (int)$sensor['id_sensor'];

    try{
        if(file_exists(__DIR__ . '/../cliente/includes/helpers.php')){
            require_once __DIR__ . '/../cliente/includes/helpers.php';
            $tanqueCfg = null;
            try{
                $stTanque = $pdo->prepare("SELECT t.capacidad_litros, t.volumen_util, t.altura_cm, t.diametro FROM tanques t INNER JOIN dispositivos d ON d.id_tanque=t.id_tanque WHERE d.id_dispositivo=:did LIMIT 1");
                $stTanque->execute([':did'=>$sensor['id_dispositivo']]);
                $tanqueCfg = $stTanque->fetch();
            }catch(Throwable $e){}
            if($tanqueCfg && function_exists('eva_tanque_capacidad_efectiva')){
                $capEff = (float)eva_tanque_capacidad_efectiva($tanqueCfg);
                $alt = (float)($tanqueCfg['altura_cm'] ?? 0);
                if($nivel === null && $distancia !== null && $alt > 0){
                    $nivel = $alt - (float)$distancia;
                    if($nivel < 0) $nivel = 0; if($nivel > $alt) $nivel = $alt;
                }
                if($capEff > 0 && $litros !== null && is_numeric($litros) && (float)$litros > 0){
                    $porcentaje = round(max(0, min(100, (float)$litros / $capEff * 100)),2);
                    $litros = round(max(0, min($capEff, (float)$litros)),2);
                    if($nivel === null && $alt > 0) $nivel = round($alt * (float)$porcentaje / 100,2);
                    if($distancia === null && $alt > 0) $distancia = round($alt - $nivel,2);
                }elseif($porcentaje !== null && is_numeric($porcentaje)){
                    $porcentaje = round(max(0, min(100, (float)$porcentaje)),2);
                    if($capEff > 0) $litros = round($capEff * (float)$porcentaje / 100,2);
                    if($nivel === null && $alt > 0) $nivel = round($alt * (float)$porcentaje / 100,2);
                    if($distancia === null && $alt > 0) $distancia = round($alt - $nivel,2);
                }elseif($nivel !== null && is_numeric($nivel) && $alt > 0){
                    $porcentaje = round(max(0, min(100, (float)$nivel / $alt * 100)),2);
                    if($capEff > 0) $litros = round($capEff * (float)$porcentaje / 100,2);
                }elseif($distancia !== null && is_numeric($distancia) && $alt > 0){
                    $nivel = $alt - (float)$distancia;
                    $porcentaje = round(max(0, min(100, $nivel / $alt * 100)),2);
                    if($capEff > 0) $litros = round($capEff * (float)$porcentaje / 100,2);
                    $nivel = round($nivel,2); $distancia = round((float)$distancia,2);
                }else{
                    if($porcentaje !== null) $porcentaje = round(max(0, min(100, (float)$porcentaje)),2);
                    if($capEff > 0 && $porcentaje !== null) $litros = round($capEff * (float)$porcentaje / 100,2);
                }
                if($nivel !== null) $nivel = round((float)$nivel,2);
                if($distancia !== null) $distancia = round((float)$distancia,2);
                if($porcentaje !== null) $porcentaje = round((float)$porcentaje,2);
                if($litros !== null) $litros = round((float)$litros,2);
            }
        }
    }catch(Throwable $e){}

    $intentar = function($conTemp) use ($pdo,$id_sensor,$distancia,$nivel,$porcentaje,$litros,$temperatura,$humedad,$fecha_hora){
        if($conTemp){
            $sql="INSERT INTO mediciones (id_sensor,distancia_cm,nivel_cm,porcentaje,litros,temperatura,humedad,fecha_hora) VALUES (:sid,:d,:n,:p,:l,:t,:h,COALESCE(:fh,NOW()))";
            $stmt=$pdo->prepare($sql);
            $stmt->execute([':sid'=>$id_sensor,':d'=>$distancia,':n'=>$nivel,':p'=>$porcentaje,':l'=>$litros,':t'=>$temperatura,':h'=>$humedad,':fh'=>$fecha_hora]);
        } else {
            $sql="INSERT INTO mediciones (id_sensor,distancia_cm,nivel_cm,porcentaje,litros,fecha_hora) VALUES (:sid,:d,:n,:p,:l,COALESCE(:fh,NOW()))";
            $stmt=$pdo->prepare($sql);
            $stmt->execute([':sid'=>$id_sensor,':d'=>$distancia,':n'=>$nivel,':p'=>$porcentaje,':l'=>$litros,':fh'=>$fecha_hora]);
        }
        return $pdo->lastInsertId();
    };
    try{ $id = $intentar(true); } catch(Throwable $e){
        if(stripos($e->getMessage(),'temperatura')!==false || stripos($e->getMessage(),'humedad')!==false) $id=$intentar(false);
        else throw $e;
    }
    $pdo->prepare("UPDATE dispositivos SET ultima_conexion=NOW(), ultima_actualizacion=NOW() WHERE id_dispositivo=:did")->execute([':did'=>$sensor['id_dispositivo']]);
    if(file_exists(__DIR__ . '/../cliente/includes/procesador_mediciones.php')){
        require_once __DIR__ . '/../cliente/includes/procesador_mediciones.php';
        try{ $id_tanque=(int)$pdo->query("SELECT id_tanque FROM dispositivos WHERE id_dispositivo=".(int)$sensor['id_dispositivo'])->fetchColumn(); if($id_tanque) eva_procesar_tanque($pdo,$id_tanque); }catch(Throwable $e){}
    }

    echo json_encode([
        "success" => true,
        "mensaje" => "Datos guardados correctamente",
        "id_medicion" => $id
    ]);

} catch (PDOException $e) {

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "mensaje" => "Error de base de datos",
        "error" => $e->getMessage()
    ]);

} catch (Throwable $e) {

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "mensaje" => "Error del servidor",
        "error" => $e->getMessage()
    ]);
}
