<?php
declare(strict_types=1);
require_once __DIR__ . '/../../config/db_pdo.php';
require_once __DIR__ . '/../../config/helpers.php';

$idTecnico = requireTecnico();
$method = $_SERVER['REQUEST_METHOD'];
$pdo = getDB();

if ($method === 'GET') {
    $stmt = $pdo->prepare("
        SELECT s.*, d.nombre AS dispositivo_nombre, t.nombre AS tanque_nombre
        FROM sensores s
        INNER JOIN dispositivos d ON s.id_dispositivo = d.id_dispositivo
        INNER JOIN tanques t ON d.id_tanque = t.id_tanque
        INNER JOIN tecnico_dispositivo td ON td.id_dispositivo = d.id_dispositivo
        WHERE td.id_tecnico = :tec
        ORDER BY s.modelo
    ");
    $stmt->execute([':tec' => $idTecnico]);
    jsonResponse('success', 'Sensores obtenidos', $stmt->fetchAll());
}

if ($method === 'PUT') {
    $input = getInput();
    $idSensor = $input['id_sensor'] ?? null;
    $estado = $input['estado'] ?? null;

    if (!$idSensor || !$estado) {
        jsonResponse('error', 'Faltan datos', null, 400);
    }

    $estados = ['ACTIVO', 'INACTIVO', 'FALLA'];
    $mapped = match(strtoupper($estado)) {
        'OPERATIVO', 'REPARADO' => 'ACTIVO',
        'REPARACION', 'DEFECTUOSO' => 'FALLA',
        default => strtoupper($estado),
    };

    if (!in_array($mapped, $estados, true)) {
        jsonResponse('error', 'Estado inválido', null, 400);
    }

    $stmt = $pdo->prepare("
        UPDATE sensores SET estado = :est
        WHERE id_sensor = :id
        AND id_dispositivo IN (SELECT id_dispositivo FROM tecnico_dispositivo WHERE id_tecnico = :tec)
    ");
    $stmt->execute([':est' => $mapped, ':id' => $idSensor, ':tec' => $idTecnico]);
    jsonResponse('success', 'Estado del sensor actualizado');
}

if ($method === 'POST') {
    $input = getInput();
    $idSensor = $input['id_sensor'] ?? null;
    $fechaCal = $input['fecha_calibracion'] ?? null;
    $notas = $input['notas'] ?? null;

    if (!$idSensor || !$fechaCal) {
        jsonResponse('error', 'Faltan id_sensor y fecha_calibracion', null, 400);
    }

    $stmt = $pdo->prepare("
        UPDATE sensores SET fecha_calibracion = :fec, calibrado = 1
        WHERE id_sensor = :id
        AND id_dispositivo IN (SELECT id_dispositivo FROM tecnico_dispositivo WHERE id_tecnico = :tec)
    ");
    $stmt->execute([':fec' => $fechaCal, ':id' => $idSensor, ':tec' => $idTecnico]);
    jsonResponse('success', 'Calibración registrada');
}

jsonResponse('error', 'Método no permitido', null, 405);
