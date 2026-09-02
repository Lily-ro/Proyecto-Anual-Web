<?php
declare(strict_types=1);
require_once __DIR__ . '/../../config/db_pdo.php';
require_once __DIR__ . '/../../config/helpers.php';

$idTecnico = requireTecnico();
$method = $_SERVER['REQUEST_METHOD'];
$pdo = getDB();

if ($method === 'GET') {
    $stmt = $pdo->prepare("
        SELECT
            (SELECT COUNT(*) FROM sensores s
             INNER JOIN dispositivos d ON s.id_dispositivo = d.id_dispositivo
             INNER JOIN tecnico_dispositivo td ON td.id_dispositivo = d.id_dispositivo
             WHERE td.id_tecnico = :tec AND s.estado = 'ACTIVO') AS sensores_activos,
            (SELECT COUNT(*) FROM instalaciones WHERE id_tecnico = :tec2) AS total_instalaciones,
            (SELECT COUNT(*) FROM instalaciones WHERE id_tecnico = :tec3) AS instalaciones_pendientes,
            (SELECT COUNT(*) FROM mantenimientos WHERE id_tecnico = :tec4) AS total_mantenimientos,
            (SELECT COUNT(*) FROM mantenimientos WHERE id_tecnico = :tec5 AND estado = 'PENDIENTE') AS mant_pendientes,
            (SELECT COUNT(*) FROM dispositivos d
             INNER JOIN tecnico_dispositivo td ON td.id_dispositivo = d.id_dispositivo
             WHERE td.id_tecnico = :tec6) AS total_dispositivos
    ");
    $stmt->execute([
        ':tec' => $idTecnico, ':tec2' => $idTecnico, ':tec3' => $idTecnico,
        ':tec4' => $idTecnico, ':tec5' => $idTecnico, ':tec6' => $idTecnico,
    ]);
    jsonResponse('success', 'Stats obtenidos', $stmt->fetch());
}

jsonResponse('error', 'Método no permitido', null, 405);
