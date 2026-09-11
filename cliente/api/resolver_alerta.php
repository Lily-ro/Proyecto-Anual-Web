<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors','0');
error_reporting(E_ALL);
if(!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'USUARIO'){
    http_response_code(401);
    echo json_encode(['ok'=>false,'error'=>'No autorizado - inicia sesión como cliente']);
    exit;
}
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../includes/helpers.php';
if($_SERVER['REQUEST_METHOD'] !== 'POST'){
    http_response_code(405);
    echo json_encode(['ok'=>false,'error'=>'Método no permitido']);
    exit;
}
$raw = file_get_contents('php://input');
$input = null;
if($raw !== '' && $raw !== false){
    $input = json_decode($raw, true);
    if(json_last_error() !== JSON_ERROR_NONE){
        $input = null;
    }
}
if(!is_array($input) || empty($input)){
    $input = $_POST;
}
if(!is_array($input)){
    $input = [];
}
$id_alerta = (int)($input['id_alerta'] ?? $input['id'] ?? 0);
if($id_alerta <= 0){
    http_response_code(400);
    echo json_encode(['ok'=>false,'error'=>'ID inválido']);
    exit;
}
try{
    $pdo = eva_pdo();
    $uid = eva_current_user_id();
    if(!$uid){
        http_response_code(401);
        echo json_encode(['ok'=>false,'error'=>'Sesión expirada']);
        exit;
    }
    $tanque = eva_first_tanque($pdo, $uid);
    if(!$tanque){
        http_response_code(403);
        echo json_encode(['ok'=>false,'error'=>'Sin tanque asignado']);
        exit;
    }
    $idTanque = (int)($tanque['id_tanque'] ?? 0);
    $st = $pdo->prepare("SELECT id_alerta, id_tanque, estado FROM alertas WHERE id_alerta=:id LIMIT 1");
    $st->execute([':id'=>$id_alerta]);
    $row = $st->fetch(PDO::FETCH_ASSOC);
    if(!$row){
        http_response_code(404);
        echo json_encode(['ok'=>false,'error'=>'Alerta no encontrada']);
        exit;
    }
    if((int)$row['id_tanque'] !== $idTanque){
        http_response_code(403);
        echo json_encode(['ok'=>false,'error'=>'No autorizado para esta alerta']);
        exit;
    }
    $estadoActual = strtoupper(trim($row['estado'] ?? ''));
    if($estadoActual === 'CERRADA'){
        echo json_encode(['ok'=>true,'estado'=>'CERRADA','msg'=>'Ya resuelta']);
        exit;
    }
    $up = $pdo->prepare("UPDATE alertas SET estado='CERRADA' WHERE id_alerta=:id");
    $up->execute([':id'=>$id_alerta]);
    if($up->rowCount()===0){
        echo json_encode(['ok'=>true,'estado'=>'CERRADA','msg'=>'Sin cambios']);
        exit;
    }
    try{ eva_log_actividad($pdo,(int)$uid,'UPDATE',"Alerta $id_alerta -> CERRADA por cliente"); }catch(Throwable $e){}
    echo json_encode(['ok'=>true,'estado'=>'CERRADA']);
    exit;
}catch(Throwable $e){
    error_log('resolver_alerta: '.$e->getMessage());
    http_response_code(500);
    echo json_encode(['ok'=>false,'error'=>'Error interno: '.$e->getMessage()]);
    exit;
}
