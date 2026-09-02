<?php
declare(strict_types=1);
require_once __DIR__ . '/../../config/db_pdo.php';
require_once __DIR__ . '/../../config/helpers.php';

$idTecnico = requireTecnico();
$method = $_SERVER['REQUEST_METHOD'];
$pdo = getDB();

if ($method === 'GET') {
    $stmt = $pdo->prepare("
        SELECT DISTINCT t.*, e.nombre AS edificio_nombre,
               (SELECT m.porcentaje FROM mediciones m
                INNER JOIN sensores s ON m.id_sensor = s.id_sensor
                INNER JOIN dispositivos d2 ON s.id_dispositivo = d2.id_dispositivo
                WHERE d2.id_tanque = t.id_tanque
                ORDER BY m.fecha_hora DESC LIMIT 1) AS nivel_actual,
               (SELECT m.litros FROM mediciones m
                INNER JOIN sensores s ON m.id_sensor = s.id_sensor
                INNER JOIN dispositivos d2 ON s.id_dispositivo = d2.id_dispositivo
                WHERE d2.id_tanque = t.id_tanque
                ORDER BY m.fecha_hora DESC LIMIT 1) AS litros_actual
        FROM tanques t
        INNER JOIN edificios e ON t.id_edificio = e.id_edificio
        INNER JOIN dispositivos d ON d.id_tanque = t.id_tanque
        INNER JOIN tecnico_dispositivo td ON td.id_dispositivo = d.id_dispositivo
        WHERE td.id_tecnico = :tec
        ORDER BY t.nombre
    ");
    $stmt->execute([':tec' => $idTecnico]);
    jsonResponse('success', 'Tanques obtenidos', $stmt->fetchAll());
}

jsonResponse('error', 'Método no permitido', null, 405);
