<?php
declare(strict_types=1);
session_start();
header('Content-Type: application/json; charset=utf-8');
if(!isset($_SESSION['rol']) || !in_array($_SESSION['rol'], ['USUARIO','ADMIN','TECNICO'], true)){ http_response_code(401); echo json_encode(['error'=>'No autorizado']); exit; }
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../includes/helpers.php';
try{
 $pdo=eva_pdo();
 $uid=eva_current_user_id();
 $tanque=eva_first_tanque($pdo,$uid);
 $tid=$tanque ? (int)$tanque['id_tanque'] : null;
 if(isset($_GET['id_tanque']) && is_numeric($_GET['id_tanque'])) $tid=(int)$_GET['id_tanque'];
 $c1=eva_sincronizar_consumos($pdo,$tid);
 $c2=eva_sincronizar_alertas($pdo,$tid);
 echo json_encode(['ok'=>true,'consumos_sincronizados'=>$c1,'alertas_generadas'=>$c2,'tanque'=>$tid], JSON_UNESCAPED_UNICODE);
}catch(Throwable $e){ http_response_code(500); echo json_encode(['error'=>$e->getMessage()]); }
