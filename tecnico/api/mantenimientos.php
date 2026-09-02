<?php
declare(strict_types=1);
require_once __DIR__ . '/../../config/db_pdo.php';
require_once __DIR__ . '/../../config/helpers.php';

$idTecnico = requireTecnico();
$method = $_SERVER['REQUEST_METHOD'];
$pdo = getDB();

if ($method === 'GET') {
    $stmt = $pdo->prepare("
        SELECT m.*, d.nombre AS dispositivo_nombre, t.nombre AS tanque_nombre
        FROM mantenimientos m
        INNER JOIN dispositivos d ON m.id_dispositivo = d.id_dispositivo
        INNER JOIN tanques t ON d.id_tanque = t.id_tanque
        WHERE m.id_tecnico = :tec
        ORDER BY m.fecha_programada DESC
    ");
    $stmt->execute([':tec' => $idTecnico]);
    jsonResponse('success', 'Mantenimientos obtenidos', $stmt->fetchAll());
}

if ($method === 'POST') {
    $input = getInput();
    $idMant = $input['id_mantenimiento'] ?? null;
    $observaciones = $input['observaciones'] ?? null;
    $costo = $input['costo'] ?? null;

    if (!$idMant) {
        jsonResponse('error', 'Falta id_mantenimiento', null, 400);
    }

    $stmt = $pdo->prepare("
        UPDATE mantenimientos
        SET observaciones = COALESCE(:obs, observaciones),
            costo = COALESCE(:costo, costo),
            estado = 'FINALIZADO',
            fecha_realizada = NOW()
        WHERE id_mantenimiento = :id AND id_tecnico = :tec
    ");
    $stmt->execute([':obs' => $observaciones, ':costo' => $costo, ':id' => $idMant, ':tec' => $idTecnico]);
    jsonResponse('success', 'Mantenimiento finalizado');
}

if ($method === 'PUT') {
    $input = getInput();
    $idMant = $input['id_mantenimiento'] ?? null;
    $estado = $input['estado'] ?? null;

    if (!$idMant || !$estado) {
        jsonResponse('error', 'Faltan datos', null, 400);
    }

    $estados = ['PENDIENTE', 'EN_PROCESO', 'FINALIZADO', 'CANCELADO'];
    $mapped = strtoupper($estado);
    if (!in_array($mapped, $estados, true)) {
        jsonResponse('error', 'Estado inválido', null, 400);
    }

    $extra = '';
    $params = [':est' => $mapped, ':id' => $idMant, ':tec' => $idTecnico];
    if ($mapped === 'FINALIZADO') {
        $extra = ', fecha_realizada = NOW()';
    }

    $stmt = $pdo->prepare("
        UPDATE mantenimientos SET estado = :est {$extra}
        WHERE id_mantenimiento = :id AND id_tecnico = :tec
    ");
    $stmt->execute($params);
    jsonResponse('success', 'Estado del mantenimiento actualizado');
}

jsonResponse('error', 'Método no permitido', null, 405);
