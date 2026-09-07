<?php
declare(strict_types=1);
session_start();
header('Content-Type: application/json; charset=utf-8');
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'USUARIO') { http_response_code(401); echo json_encode(['error' => 'No autorizado']); exit; }
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/procesador_mediciones.php';
try {
    $pdo = eva_pdo();
    $uid = eva_current_user_id();
    $tanque = eva_first_tanque($pdo, $uid);
    if (!$tanque) { echo json_encode(['error' => 'Sin tanques']); exit; }
    $idTanque = (int)($tanque['id_tanque'] ?? 0);
    eva_procesar_tanque($pdo, $idTanque);
    $datos = eva_dashboard_datos($pdo, $idTanque);
    $period = $_GET['period'] ?? 'semana';
    if (!in_array($period, ['semana', 'mes', 'anio'], true)) $period = 'semana';
    try {
        if ($period === 'anio') {
            $st = $pdo->prepare("SELECT MONTH(fecha) as m, AVG(litros_consumidos) as tot FROM consumos WHERE id_tanque=:id AND YEAR(fecha)=YEAR(CURDATE()) GROUP BY MONTH(fecha) ORDER BY m ASC");
            $st->execute([':id' => $idTanque]);
            $map = [];
            foreach ($st->fetchAll() as $r) $map[(int)$r['m']] = (float)$r['tot'];
            $serie = [];
            for ($i = 1; $i <= 12; $i++) $serie[] = $map[$i] ?? 0;
        } else {
            $days = $period === 'mes' ? 30 : 7;
            $st = $pdo->prepare("SELECT fecha, litros_consumidos FROM consumos WHERE id_tanque=:id AND fecha >= DATE_SUB(CURDATE(), INTERVAL {$days} DAY) ORDER BY fecha ASC");
            $st->execute([':id' => $idTanque]);
            $map = [];
            foreach ($st->fetchAll() as $r) $map[$r['fecha']] = (float)$r['litros_consumidos'];
            $serie = [];
            for ($i = $days - 1; $i >= 0; $i--) { $d = date('Y-m-d', strtotime("-{$i} days")); $serie[] = $map[$d] ?? 0; }
        }
    } catch (Throwable $e) { $serie = []; }
    $datos['serie'] = $serie;
    $datos['period'] = $period;
    $datos['idTanque'] = $idTanque;
    $datos['tanqueNombre'] = $tanque['nombre'] ?? null;
    try {
        $st = $pdo->prepare("SELECT a.* FROM alertas a WHERE a.id_tanque=:tid ORDER BY a.fecha_hora DESC LIMIT 5");
        $st->execute([':tid' => $idTanque]);
        $datos['alertasRecientes'] = $st->fetchAll();
    } catch (Throwable $e) { $datos['alertasRecientes'] = []; }
    try {
        $st = $pdo->prepare("SELECT porcentaje, fecha_hora FROM mediciones m LEFT JOIN sensores s ON s.id_sensor=m.id_sensor LEFT JOIN dispositivos d ON d.id_dispositivo=s.id_dispositivo WHERE (d.id_tanque=:id OR d.id_tanque IS NULL) ORDER BY m.fecha_hora DESC LIMIT 7");
        $st->execute([':id' => $idTanque]);
        $rows = $st->fetchAll();
        if (!$rows) { $st2 = $pdo->prepare("SELECT porcentaje, fecha_hora FROM mediciones ORDER BY fecha_hora DESC LIMIT 7"); $st2->execute(); $rows = $st2->fetchAll(); }
        $rows = array_reverse($rows);
        $bars = [];
        foreach ($rows as $r) $bars[] = ['year' => date('d/m', strtotime($r['fecha_hora'])), 'bottom' => (int)round((float)($r['porcentaje'] ?? 0) * 0.6), 'top' => (int)round((float)($r['porcentaje'] ?? 0) * 0.4)];
        $datos['barsData'] = $bars;
    } catch (Throwable $e) { $datos['barsData'] = []; }
    echo json_encode($datos, JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) { http_response_code(500); echo json_encode(['error' => $e->getMessage()]); }
