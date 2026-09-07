<?php

header("Content-Type: application/json; charset=utf-8");

error_reporting(E_ALL);
ini_set("display_errors", "0");

require_once __DIR__ . "/../config/db.php";

try {
    $input = json_decode(file_get_contents('php://input'), true);
    if(!$input) $input = $_POST;
    $id_sensor = (int)($input["id_sensor"] ?? $input["sensor_id"] ?? 1);
    $temperatura = $input["temperatura"] ?? null;
    $humedad = $input["humedad"] ?? null;
    $distancia = $input["distancia_cm"] ?? $input["distancia"] ?? null;
    $nivel = $input["nivel_cm"] ?? null;
    $porcentaje = $input["porcentaje"] ?? null;
    $litros = $input["litros"] ?? null;
    $fecha_hora = $input["fecha_hora"] ?? null;

    if ($distancia === null && $nivel === null && $porcentaje === null) {
        http_response_code(400);
        echo json_encode(["success" => false, "mensaje" => "Faltan datos distancia/nivel/porcentaje"]);
        exit;
    }
    if(!$id_sensor){ http_response_code(400); echo json_encode(["success"=>false,"mensaje"=>"id_sensor requerido"]); exit; }
    $chk=$pdo->prepare("SELECT id_sensor, id_dispositivo FROM sensores WHERE id_sensor=:id LIMIT 1");
    $chk->execute([':id'=>$id_sensor]);
    $sensor=$chk->fetch();
    if(!$sensor){ http_response_code(404); echo json_encode(["success"=>false,"mensaje"=>"Sensor no existe"]); exit; }

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
    require_once __DIR__ . '/../cliente/includes/procesador_mediciones.php';
    try{ $id_tanque=(int)$pdo->query("SELECT id_tanque FROM dispositivos WHERE id_dispositivo=".(int)$sensor['id_dispositivo'])->fetchColumn(); if($id_tanque) eva_procesar_tanque($pdo,$id_tanque); }catch(Throwable $e){}

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