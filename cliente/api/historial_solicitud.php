<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'USUARIO') {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'No autorizado']);
    exit;
}
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../includes/helpers.php';

$idSol = (int)($_GET['id'] ?? 0);
if ($idSol <= 0) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'ID inválido']);
    exit;
}

try {
    $pdo = eva_pdo();
    $uid = eva_current_user_id();

    $st = $pdo->prepare("SELECT s.*, t.nombre AS tanque_nombre FROM solicitudes_mantenimiento s LEFT JOIN tanques t ON t.id_tanque = s.id_tanque WHERE s.id_solicitud = :id AND s.id_usuario = :uid LIMIT 1");
    $st->execute([':id' => $idSol, ':uid' => $uid]);
    $sol = $st->fetch(PDO::FETCH_ASSOC);

    if (!$sol) {
        http_response_code(404);
        echo json_encode(['ok' => false, 'error' => 'Solicitud no encontrada']);
        exit;
    }

    $timeline = [];

    $timeline[] = [
        'estado' => 'Solicitud creada',
        'fecha' => $sol['fecha_solicitud'] ? date('d/m/Y H:i', strtotime($sol['fecha_solicitud'])) : null,
        'descripcion' => 'Se creó la solicitud de mantenimiento para el tanque ' . ($sol['tanque_nombre'] ?? ''),
        'icono' => 'create'
    ];

    if (!empty($sol['fecha_aceptada'])) {
        $timeline[] = [
            'estado' => 'Solicitud aceptada',
            'fecha' => date('d/m/Y H:i', strtotime($sol['fecha_aceptada'])),
            'descripcion' => 'Un técnico aceptó la solicitud y comenzará a revisarla.',
            'icono' => 'accept'
        ];
    }

    if (!empty($sol['fecha_inicio'])) {
        $timeline[] = [
            'estado' => 'En proceso',
            'fecha' => date('d/m/Y H:i', strtotime($sol['fecha_inicio'])),
            'descripcion' => 'El técnico comenzó a trabajar en la solución del problema.',
            'icono' => 'process'
        ];
    }

    if (!empty($sol['fecha_finalizada'])) {
        $timeline[] = [
            'estado' => 'Finalizada',
            'fecha' => date('d/m/Y H:i', strtotime($sol['fecha_finalizada'])),
            'descripcion' => 'La solicitud fue resuelta.',
            'icono' => 'done'
        ];
    }

    if (strtoupper(trim($sol['estado'])) === 'CANCELADA') {
        $timeline[] = [
            'estado' => 'Cancelada',
            'fecha' => $sol['updated_at'] ? date('d/m/Y H:i', strtotime($sol['updated_at'])) : null,
            'descripcion' => 'La solicitud fue cancelada.',
            'icono' => 'cancel'
        ];
    }

    if (!empty($sol['observaciones_admin'])) {
        $timeline[] = [
            'estado' => 'Observación del administrador',
            'fecha' => $sol['updated_at'] ? date('d/m/Y H:i', strtotime($sol['updated_at'])) : null,
            'descripcion' => $sol['observaciones_admin'],
            'icono' => 'note'
        ];
    }

    usort($timeline, function ($a, $b) {
        if (!$a['fecha'] || !$b['fecha']) return 0;
        $da = DateTime::createFromFormat('d/m/Y H:i', $a['fecha']);
        $db = DateTime::createFromFormat('d/m/Y H:i', $b['fecha']);
        if (!$da || !$db) return 0;
        return $da <=> $db;
    });

    echo json_encode([
        'ok' => true,
        'solicitud' => [
            'id' => $sol['id_solicitud'],
            'tanque' => $sol['tanque_nombre'] ?? '',
            'descripcion' => $sol['descripcion'] ?? '',
            'estado' => $sol['estado'] ?? '',
            'imagen' => $sol['imagen'] ?? null,
            'fecha_solicitud' => $sol['fecha_solicitud'] ? date('d/m/Y H:i', strtotime($sol['fecha_solicitud'])) : null
        ],
        'timeline' => $timeline
    ], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Error del servidor']);
}
