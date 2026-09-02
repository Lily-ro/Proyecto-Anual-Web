<?php
declare(strict_types=1);
require_once __DIR__ . '/../../config/db_pdo.php';
require_once __DIR__ . '/../../config/helpers.php';

$idTecnico = requireTecnico();
$method = $_SERVER['REQUEST_METHOD'];
$pdo = getDB();

if ($method === 'GET') {
    $stmt = $pdo->prepare("
        SELECT a.*, t.nombre AS tanque_nombre
        FROM alertas a
        INNER JOIN tanques t ON a.id_tanque = t.id_tanque
        INNER JOIN dispositivos d ON d.id_tanque = t.id_tanque
        INNER JOIN tecnico_dispositivo td ON td.id_dispositivo = d.id_dispositivo
        WHERE td.id_tecnico = :tec
        ORDER BY a.fecha_hora DESC
    ");
    $stmt->execute([':tec' => $idTecnico]);
    jsonResponse('success', 'Alertas obtenidas', $stmt->fetchAll());
}

if ($method === 'POST') {
    $input = getInput();
    $idAlerta = $input['id_alerta'] ?? null;
    $comentario = $input['comentario'] ?? '';
    $solucion = $input['solucion'] ?? '';

    if (!$idAlerta) {
        jsonResponse('error', 'Falta id_alerta', null, 400);
    }

    $stmt = $pdo->prepare("UPDATE alertas SET estado = 'ATENDIDA' WHERE id_alerta = :id");
    $stmt->execute([':id' => $idAlerta]);
    jsonResponse('success', 'Alerta marcada como atendida');
}

if ($method === 'PUT') {
    $input = getInput();
    $idAlerta = $input['id_alerta'] ?? null;
    $estado = $input['estado'] ?? null;

    if (!$idAlerta || !$estado) {
        jsonResponse('error', 'Faltan datos', null, 400);
    }

    $estados = ['PENDIENTE', 'ATENDIDA', 'CERRADA'];
    if (!in_array(strtoupper($estado), $estados, true)) {
        jsonResponse('error', 'Estado inválido', null, 400);
    }

    $stmt = $pdo->prepare("UPDATE alertas SET estado = :est WHERE id_alerta = :id");
    $stmt->execute([':est' => strtoupper($estado), ':id' => $idAlerta]);
    jsonResponse('success', 'Alerta actualizada');
}

jsonResponse('error', 'Método no permitido', null, 405);
