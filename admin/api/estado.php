<?php
declare(strict_types=1);
session_start();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'ADMIN') { http_response_code(401); echo json_encode(['error' => 'No autorizado']); exit; }
require_once __DIR__ . '/../../config/db.php';
try {
    $pdo = eva_pdo();
    $cntClientes = (int)$pdo->query("SELECT COUNT(*) FROM usuarios WHERE id_rol=3 AND activo=1")->fetchColumn();
    $cntTecnicos = (int)$pdo->query("SELECT COUNT(*) FROM usuarios u JOIN roles r ON u.id_rol=r.id_rol WHERE r.nombre='TECNICO' AND u.activo=1")->fetchColumn();
    $cntEdificios = (int)$pdo->query("SELECT COUNT(*) FROM edificios")->fetchColumn();
    $cntDispositivos = (int)$pdo->query("SELECT COUNT(*) FROM dispositivos")->fetchColumn();
    $cntTanques = (int)$pdo->query("SELECT COUNT(*) FROM tanques")->fetchColumn();
    $cntSensores = (int)$pdo->query("SELECT COUNT(*) FROM sensores")->fetchColumn();
    $cntInstalaciones = (int)$pdo->query("SELECT COUNT(*) FROM instalaciones")->fetchColumn();
    $totalDev = max($cntDispositivos, 1);
    $devOnline = (int)$pdo->query("SELECT COUNT(*) FROM dispositivos WHERE estado='ONLINE'")->fetchColumn();
    $devAlerta = (int)$pdo->query("SELECT COUNT(*) FROM dispositivos WHERE estado='MANTENIMIENTO'")->fetchColumn();
    $devInactivo = (int)$pdo->query("SELECT COUNT(*) FROM dispositivos WHERE estado='OFFLINE'")->fetchColumn();
    $pctOnline = round(($devOnline / $totalDev) * 100);
    $pctAlerta = round(($devAlerta / $totalDev) * 100);
    $pctInactivo = max(0, 100 - $pctOnline - $pctAlerta);
    $st = $pdo->query("SELECT m.descripcion, t.ubicacion, DATE_FORMAT(m.fecha_programada,'%d/%m/%Y') AS fecha, m.estado FROM mantenimientos m JOIN dispositivos d ON m.id_dispositivo=d.id_dispositivo JOIN tanques t ON d.id_tanque=t.id_tanque WHERE m.estado IN ('PENDIENTE','EN_PROCESO') ORDER BY m.fecha_programada ASC LIMIT 3");
    $mantPend = $st ? $st->fetchAll() : [];
    $st2 = $pdo->query("SELECT d.nombre, t.nombre AS tanque, d.estado, d.bateria, d.intensidad_senal, d.ultima_conexion FROM dispositivos d JOIN tanques t ON d.id_tanque=t.id_tanque ORDER BY d.ultima_conexion DESC LIMIT 5");
    $dispositivos = $st2 ? $st2->fetchAll() : [];
    $st3 = $pdo->query("SELECT DATE_FORMAT(a.fecha_hora,'%d/%m %H:%i') AS fecha, t.nombre AS tanque, a.tipo, a.estado FROM alertas a JOIN tanques t ON a.id_tanque=t.id_tanque ORDER BY a.fecha_hora DESC LIMIT 5");
    $alertas = $st3 ? $st3->fetchAll() : [];
    $st4 = $pdo->query("SELECT CONCAT(ut.nombre,' ',ut.apellido) AS tecnico, d.nombre AS dispositivo, m.estado, DATE_FORMAT(m.fecha_programada,'%d/%m/%Y') AS fecha FROM mantenimientos m JOIN dispositivos d ON m.id_dispositivo=d.id_dispositivo JOIN usuarios ut ON m.id_tecnico=ut.id_usuario ORDER BY m.fecha_programada DESC LIMIT 5");
    $mantHist = $st4 ? $st4->fetchAll() : [];
    echo json_encode([
        'success' => true,
        'cntClientes' => $cntClientes,
        'cntTecnicos' => $cntTecnicos,
        'cntEdificios' => $cntEdificios,
        'cntDispositivos' => $cntDispositivos,
        'cntTanques' => $cntTanques,
        'cntSensores' => $cntSensores,
        'cntInstalaciones' => $cntInstalaciones,
        'devOnline' => $devOnline,
        'devAlerta' => $devAlerta,
        'devInactivo' => $devInactivo,
        'pctOnline' => $pctOnline,
        'pctAlerta' => $pctAlerta,
        'pctInactivo' => $pctInactivo,
        'mantPend' => $mantPend,
        'dispositivos' => $dispositivos,
        'alertas' => $alertas,
        'mantHist' => $mantHist,
        'ts' => date('Y-m-d H:i:s')
    ], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
