<?php
declare(strict_types=1);
require_once __DIR__ . '/../../config/db_pdo.php';
require_once __DIR__ . '/../../config/helpers.php';

$idTecnico = requireTecnico();
$method = $_SERVER['REQUEST_METHOD'];
$pdo = getDB();

if ($method === 'GET') {
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id_usuario = :tec");
    $stmt->execute([':tec' => $idTecnico]);
    $user = $stmt->fetch();

    if (!$user) {
        jsonResponse('error', 'Usuario no encontrado', null, 404);
    }

    unset($user['password_hash']);

    $stmt2 = $pdo->prepare("SELECT COUNT(*) AS total FROM mantenimientos WHERE id_tecnico = :tec");
    $stmt2->execute([':tec' => $idTecnico]);
    $user['mantenimientos_realizados'] = (int)$stmt2->fetch()['total'];

    $stmt3 = $pdo->prepare("SELECT COUNT(*) AS total FROM instalaciones WHERE id_tecnico = :tec");
    $stmt3->execute([':tec' => $idTecnico]);
    $user['instalaciones_completadas'] = (int)$stmt3->fetch()['total'];

    $stmt4 = $pdo->prepare("
        SELECT COUNT(DISTINCT s.id_sensor) AS total
        FROM sensores s
        INNER JOIN dispositivos d ON s.id_dispositivo = d.id_dispositivo
        INNER JOIN tecnico_dispositivo td ON td.id_dispositivo = d.id_dispositivo
        WHERE td.id_tecnico = :tec
    ");
    $stmt4->execute([':tec' => $idTecnico]);
    $user['sensores_configurados'] = (int)$stmt4->fetch()['total'];

    jsonResponse('success', 'Perfil obtenido', $user);
}

jsonResponse('error', 'Método no permitido', null, 405);
