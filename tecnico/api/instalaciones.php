<?php
declare(strict_types=1);
require_once __DIR__ . '/../../config/db_pdo.php';
require_once __DIR__ . '/../../config/helpers.php';

$idTecnico = requireTecnico();
$method = $_SERVER['REQUEST_METHOD'];
$pdo = getDB();

if ($method === 'GET') {
    $stmt = $pdo->prepare("
        SELECT i.*, d.nombre AS dispositivo_nombre, t.nombre AS tanque_nombre,
               e.nombre AS edificio_nombre, e.direccion AS edificio_direccion
        FROM instalaciones i
        INNER JOIN dispositivos d ON i.id_dispositivo = d.id_dispositivo
        INNER JOIN tanques t ON d.id_tanque = t.id_tanque
        INNER JOIN edificios e ON t.id_edificio = e.id_edificio
        WHERE i.id_tecnico = :tec
        ORDER BY i.fecha_instalacion DESC
    ");
    $stmt->execute([':tec' => $idTecnico]);
    jsonResponse('success', 'Instalaciones obtenidas', $stmt->fetchAll());
}

if ($method === 'POST') {
    $input = getInput();
    $idDispositivo = $input['id_dispositivo'] ?? null;
    $observaciones = $input['observaciones'] ?? null;
    $latitud = $input['latitud'] ?? null;
    $longitud = $input['longitud'] ?? null;

    if (!$idDispositivo) {
        jsonResponse('error', 'Falta id_dispositivo', null, 400);
    }

    $stmt = $pdo->prepare("
        INSERT INTO instalaciones (id_dispositivo, id_tecnico, fecha_instalacion, observaciones, latitud, longitud)
        VALUES (:disp, :tec, NOW(), :obs, :lat, :lng)
    ");
    $stmt->execute([
        ':disp' => $idDispositivo,
        ':tec'  => $idTecnico,
        ':obs'  => $observaciones,
        ':lat'  => $latitud,
        ':lng'  => $longitud,
    ]);
    jsonResponse('success', 'Instalación registrada', ['id' => (int)$pdo->lastInsertId()], 201);
}

if ($method === 'PUT') {
    $input = getInput();
    $idInstalacion = $input['id_instalacion'] ?? null;
    $observaciones = $input['observaciones'] ?? null;

    if (!$idInstalacion) {
        jsonResponse('error', 'Falta id_instalacion', null, 400);
    }

    $stmt = $pdo->prepare("UPDATE instalaciones SET observaciones = :obs WHERE id_instalacion = :id AND id_tecnico = :tec");
    $stmt->execute([':obs' => $observaciones, ':id' => $idInstalacion, ':tec' => $idTecnico]);
    jsonResponse('success', 'Instalación actualizada');
}

jsonResponse('error', 'Método no permitido', null, 405);
