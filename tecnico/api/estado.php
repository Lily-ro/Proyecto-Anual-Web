<?php
declare(strict_types=1);
session_start();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'TECNICO') { http_response_code(401); echo json_encode(['error' => 'No autorizado']); exit; }
require_once __DIR__ . '/../../config/db.php';
$id_tecnico = (int)($_SESSION['id_usuario'] ?? 0);
try {
    $pdo = eva_pdo();
    $sensoresTotal = (int)$pdo->query("SELECT COUNT(*) FROM sensores s INNER JOIN dispositivos d ON s.id_dispositivo = d.id_dispositivo INNER JOIN instalaciones i ON i.id_dispositivo = d.id_dispositivo WHERE i.id_tecnico = {$id_tecnico}")->fetchColumn();
    $instalacionesTotal = (int)$pdo->query("SELECT COUNT(*) FROM instalaciones WHERE id_tecnico = {$id_tecnico}")->fetchColumn();
    $instPendientes = $instalacionesTotal;
    $mantTotal = (int)$pdo->query("SELECT COUNT(*) FROM mantenimientos WHERE id_tecnico = {$id_tecnico}")->fetchColumn();
    $mantPendientes = (int)$pdo->query("SELECT COUNT(*) FROM mantenimientos WHERE id_tecnico = {$id_tecnico} AND estado IN ('PENDIENTE','EN_PROCESO')")->fetchColumn();
    $dispositivosTotal = (int)$pdo->query("SELECT COUNT(DISTINCT d.id_dispositivo) FROM dispositivos d INNER JOIN instalaciones i ON i.id_dispositivo = d.id_dispositivo WHERE i.id_tecnico = {$id_tecnico}")->fetchColumn();
    $donutData = $pdo->query("SELECT s.estado, COUNT(*) AS cnt FROM sensores s INNER JOIN dispositivos d ON s.id_dispositivo = d.id_dispositivo INNER JOIN instalaciones i ON i.id_dispositivo = d.id_dispositivo WHERE i.id_tecnico = {$id_tecnico} GROUP BY s.estado")->fetchAll(PDO::FETCH_KEY_PAIR);
    $donutActivo = (int)($donutData['ACTIVO'] ?? 0);
    $donutInactivo = (int)($donutData['INACTIVO'] ?? 0);
    $donutFalla = (int)($donutData['FALLA'] ?? 0);
    $totalDonut = $donutActivo + $donutInactivo + $donutFalla;
    $pctActivo = $totalDonut > 0 ? round(($donutActivo / $totalDonut) * 100) : 0;
    $pctInactivo = $totalDonut > 0 ? round(($donutInactivo / $totalDonut) * 100) : 0;
    $pctFalla = $totalDonut > 0 ? 100 - $pctActivo - $pctInactivo : 0;
    $st = $pdo->prepare("SELECT m.descripcion, m.fecha_programada, m.estado, m.tipo, t.nombre AS tanque, ed.nombre AS edificio FROM mantenimientos m INNER JOIN dispositivos d ON m.id_dispositivo = d.id_dispositivo LEFT JOIN tanques t ON d.id_tanque = t.id_tanque LEFT JOIN edificios ed ON t.id_edificio = ed.id_edificio WHERE m.id_tecnico = ? AND m.estado IN ('PENDIENTE','EN_PROCESO') ORDER BY m.fecha_programada ASC LIMIT 5");
    $st->execute([$id_tecnico]);
    $mantProximos = $st->fetchAll();
    $st2 = $pdo->prepare("SELECT l.accion, l.detalle, l.fecha_hora FROM log_actividad l WHERE l.id_usuario = ? ORDER BY l.fecha_hora DESC LIMIT 5");
    $st2->execute([$id_tecnico]);
    $logActividad = $st2->fetchAll();
    echo json_encode([
        'success' => true,
        'sensoresTotal' => $sensoresTotal,
        'instalacionesTotal' => $instalacionesTotal,
        'instPendientes' => $instPendientes,
        'mantTotal' => $mantTotal,
        'mantPendientes' => $mantPendientes,
        'dispositivosTotal' => $dispositivosTotal,
        'donutActivo' => $donutActivo,
        'donutInactivo' => $donutInactivo,
        'donutFalla' => $donutFalla,
        'totalDonut' => $totalDonut,
        'pctActivo' => $pctActivo,
        'pctInactivo' => $pctInactivo,
        'pctFalla' => $pctFalla,
        'mantProximos' => $mantProximos,
        'logActividad' => $logActividad,
        'ts' => date('Y-m-d H:i:s')
    ], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
