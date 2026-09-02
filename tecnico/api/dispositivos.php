<?php
declare(strict_types=1);
require_once __DIR__ . '/../../config/db_pdo.php';
require_once __DIR__ . '/../../config/helpers.php';

$idTecnico = requireTecnico();
$method = $_SERVER['REQUEST_METHOD'];
$pdo = getDB();

if ($method === 'GET') {
    $stmt = $pdo->prepare("
        SELECT d.*, t.nombre AS tanque_nombre, e.nombre AS edificio_nombre,
               f.version AS firmware_version
        FROM dispositivos d
        INNER JOIN tanques t ON d.id_tanque = t.id_tanque
        INNER JOIN edificios e ON t.id_edificio = e.id_edificio
        LEFT JOIN firmware f ON d.id_firmware = f.id_firmware
        INNER JOIN tecnico_dispositivo td ON td.id_dispositivo = d.id_dispositivo
        WHERE td.id_tecnico = :tec
        ORDER BY d.nombre
    ");
    $stmt->execute([':tec' => $idTecnico]);
    jsonResponse('success', 'Dispositivos obtenidos', $stmt->fetchAll());
}

if ($method === 'PUT') {
    $input = getInput();
    $idDispositivo = $input['id_dispositivo'] ?? null;
    $estado = $input['estado'] ?? null;

    if (!$idDispositivo || !$estado) {
        jsonResponse('error', 'Faltan datos', null, 400);
    }

    $estados = ['ONLINE', 'OFFLINE', 'MANTENIMIENTO'];
    $mapped = match(strtoupper($estado)) {
        'OPERATIVO' => 'ONLINE',
        'MANTENIMIENTO' => 'MANTENIMIENTO',
        default => strtoupper($estado),
    };

    if (!in_array($mapped, $estados, true)) {
        jsonResponse('error', 'Estado inválido', null, 400);
    }

    $stmt = $pdo->prepare("
        UPDATE dispositivos SET estado = :est
        WHERE id_dispositivo = :id
        AND id_dispositivo IN (SELECT id_dispositivo FROM tecnico_dispositivo WHERE id_tecnico = :tec)
    ");
    $stmt->execute([':est' => $mapped, ':id' => $idDispositivo, ':tec' => $idTecnico]);

    if ($stmt->rowCount() === 0) {
        jsonResponse('error', 'Dispositivo no encontrado o sin acceso', null, 404);
    }

    jsonResponse('success', 'Estado del dispositivo actualizado');
}

jsonResponse('error', 'Método no permitido', null, 405);
