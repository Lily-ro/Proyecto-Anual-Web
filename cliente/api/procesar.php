<?php
declare(strict_types=1);
session_start();
header('Content-Type: application/json; charset=utf-8');
if (!isset($_SESSION['rol']) || !in_array($_SESSION['rol'], ['USUARIO', 'ADMIN', 'TECNICO'], true)) { http_response_code(401); echo json_encode(['error' => 'No autorizado']); exit; }
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/procesador_mediciones.php';
try {
    $pdo = eva_pdo();
    $uid = eva_current_user_id();
    $tanque = eva_first_tanque($pdo, $uid);
    $tid = $tanque ? (int)$tanque['id_tanque'] : null;
    if (isset($_GET['id_tanque']) && is_numeric($_GET['id_tanque'])) $tid = (int)$_GET['id_tanque'];
    $res = eva_procesar_todos($pdo, $tid);
    echo json_encode(['ok' => true, 'procesado' => $res, 'tanque' => $tid], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) { http_response_code(500); echo json_encode(['error' => $e->getMessage()]); }
