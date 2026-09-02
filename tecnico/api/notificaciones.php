<?php
declare(strict_types=1);
require_once __DIR__ . '/../../config/db_pdo.php';
require_once __DIR__ . '/../../config/helpers.php';

$idTecnico = requireTecnico();
$method = $_SERVER['REQUEST_METHOD'];
$pdo = getDB();

if ($method === 'GET') {
    $stmt = $pdo->prepare("
        SELECT * FROM notificaciones
        WHERE id_usuario = :tec
        ORDER BY fecha_hora DESC
    ");
    $stmt->execute([':tec' => $idTecnico]);
    jsonResponse('success', 'Notificaciones obtenidas', $stmt->fetchAll());
}

if ($method === 'POST') {
    $input = getInput();
    $idNotif = $input['id_notificacion'] ?? null;

    if (!$idNotif) {
        jsonResponse('error', 'Falta id_notificacion', null, 400);
    }

    $stmt = $pdo->prepare("UPDATE notificaciones SET leida = 1 WHERE id_notificacion = :id AND id_usuario = :tec");
    $stmt->execute([':id' => $idNotif, ':tec' => $idTecnico]);
    jsonResponse('success', 'Notificación marcada como leída');
}

if ($method === 'PUT') {
    $stmt = $pdo->prepare("UPDATE notificaciones SET leida = 1 WHERE id_usuario = :tec AND leida = 0");
    $stmt->execute([':tec' => $idTecnico]);
    jsonResponse('success', 'Todas las notificaciones marcadas como leídas');
}

jsonResponse('error', 'Método no permitido', null, 405);
