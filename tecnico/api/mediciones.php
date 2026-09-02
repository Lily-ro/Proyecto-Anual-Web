<?php
declare(strict_types=1);
require_once __DIR__ . '/../../config/db_pdo.php';
require_once __DIR__ . '/../../config/helpers.php';

$idTecnico = requireTecnico();
$method = $_SERVER['REQUEST_METHOD'];
$pdo = getDB();

if ($method === 'GET') {
    $fechaInicio = $_GET['fecha_inicio'] ?? null;
    $fechaFin = $_GET['fecha_fin'] ?? null;
    $idSensor = $_GET['id_sensor'] ?? null;

    $sql = "
        SELECT m.*, s.modelo AS sensor_modelo, s.numero_serie, d.nombre AS dispositivo_nombre
        FROM mediciones m
        INNER JOIN sensores s ON m.id_sensor = s.id_sensor
        INNER JOIN dispositivos d ON s.id_dispositivo = d.id_dispositivo
        INNER JOIN tecnico_dispositivo td ON td.id_dispositivo = d.id_dispositivo
        WHERE td.id_tecnico = :tec
    ";
    $params = [':tec' => $idTecnico];

    if ($fechaInicio) {
        $sql .= " AND m.fecha_hora >= :fi";
        $params[':fi'] = $fechaInicio;
    }
    if ($fechaFin) {
        $sql .= " AND m.fecha_hora <= :ff";
        $params[':ff'] = $fechaFin;
    }
    if ($idSensor) {
        $sql .= " AND m.id_sensor = :ids";
        $params[':ids'] = $idSensor;
    }

    $sql .= " ORDER BY m.fecha_hora DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    jsonResponse('success', 'Mediciones obtenidas', $stmt->fetchAll());
}

jsonResponse('error', 'Método no permitido', null, 405);
