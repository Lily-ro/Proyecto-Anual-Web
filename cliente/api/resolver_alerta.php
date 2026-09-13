<?php
declare(strict_types=1);
session_start();
header('Content-Type: application/json; charset=utf-8');
if(!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'USUARIO'){ http_response_code(401); echo json_encode(['error'=>'No autorizado']); exit; }
if($_SERVER['REQUEST_METHOD']!=='POST'){ http_response_code(405); echo json_encode(['error'=>'Método no permitido']); exit; }

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../includes/helpers.php';

$input = json_decode(file_get_contents('php://input'), true);
$idAlerta = (int)($input['id_alerta'] ?? 0);
if($idAlerta <= 0){ http_response_code(400); echo json_encode(['error'=>'ID de alerta inválido']); exit; }

try {
    $pdo = eva_pdo();
    $uid = eva_current_user_id();
    if(!$uid){ http_response_code(401); echo json_encode(['error'=>'Sesión expirada']); exit; }

    $tanque = eva_first_tanque($pdo, $uid);
    if(!$tanque){ http_response_code(403); echo json_encode(['error'=>'No tenés un tanque asociado']); exit; }
    $idTanque = (int)($tanque['id_tanque'] ?? 0);

    $st = $pdo->prepare("SELECT id_alerta, estado FROM alertas WHERE id_alerta=:id AND id_tanque=:tid LIMIT 1");
    $st->execute([':id'=>$idAlerta, ':tid'=>$idTanque]);
    $alerta = $st->fetch();
    if(!$alerta){ http_response_code(404); echo json_encode(['error'=>'Alerta no encontrada']); exit; }

    $estado = strtoupper(trim($alerta['estado'] ?? ''));
    if($estado === 'RESUELTA' || $estado === 'CERRADA'){
        echo json_encode(['ok'=>true, 'mensaje'=>'La alerta ya está resuelta']);
        exit;
    }

    $cols = $pdo->prepare("SHOW COLUMNS FROM alertas LIKE 'fecha_resolucion'");
    $cols->execute();
    $hasCol = $cols->fetch();

    if($hasCol){
        $pdo->prepare("UPDATE alertas SET estado='RESUELTA', fecha_resolucion=NOW() WHERE id_alerta=:id")->execute([':id'=>$idAlerta]);
    } else {
        $pdo->prepare("UPDATE alertas SET estado='RESUELTA' WHERE id_alerta=:id")->execute([':id'=>$idAlerta]);
    }

    echo json_encode(['ok'=>true, 'mensaje'=>'Alerta resuelta correctamente']);
} catch(Throwable $e) {
    http_response_code(500);
    echo json_encode(['error'=>'Error al resolver la alerta: '.$e->getMessage()]);
    error_log('resolver_alerta error: '.$e->getMessage());
}
