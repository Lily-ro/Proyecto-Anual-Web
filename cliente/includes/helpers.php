<?php
declare(strict_types=1);
require_once __DIR__ . '/../../config/db.php';

/**
 * Helpers reutilizables para cliente - PHP 8.0 - PDO + Prepared
 */

function h(?string $v): string
{
    return htmlspecialchars($v ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function eva_current_user_id(): ?int
{
    return isset($_SESSION['id_usuario']) ? (int)$_SESSION['id_usuario'] : null;
}

/**
 * Genera / valida token CSRF (almacenado en sesion)
 */
function eva_csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}
function eva_csrf_validate(?string $token): bool
{
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], (string)$token);
}

/**
 * Obtiene tanques del usuario autenticado.
 * Intenta resolver via edificios->empresas si existe relacion, sino fallback a tanques activos.
 */
function eva_tanques_for_user(PDO $pdo, ?int $uid): array
{
    // Intento 1: si existe columna id_empresa o id_edificio en usuarios (detectar via INFORMATION_SCHEMA)
    // Fallback simple: todos los tanques activos
    try {
        // Intentar mapear por empresa si el usuario tiene empresa asociada
        // Se prueba si existe columna id_empresa en usuarios
        $col = $pdo->query("SHOW COLUMNS FROM usuarios LIKE 'id_empresa'");
        if ($col && $col->rowCount() > 0 && $uid !== null) {
            $st = $pdo->prepare("SELECT t.* FROM tanques t
                INNER JOIN edificios e ON e.id_edificio = t.id_edificio
                INNER JOIN empresas emp ON emp.id_empresa = e.id_empresa
                INNER JOIN usuarios u ON u.id_empresa = emp.id_empresa
                WHERE u.id_usuario = :uid AND (t.activo = 1 OR t.activo = '1')
                ORDER BY t.id_tanque ASC");
            $st->execute([':uid' => $uid]);
            $rows = $st->fetchAll();
            if ($rows) return $rows;
        }
    } catch (Throwable $e) { /* silent fallback */ }

    try {
        $col2 = $pdo->query("SHOW COLUMNS FROM usuarios LIKE 'id_edificio'");
        if ($col2 && $col2->rowCount() > 0 && $uid !== null) {
            $st = $pdo->prepare("SELECT t.* FROM tanques t
                INNER JOIN usuarios u ON u.id_edificio = t.id_edificio
                WHERE u.id_usuario = :uid AND (t.activo = 1 OR t.activo='1')
                ORDER BY t.id_tanque ASC");
            $st->execute([':uid' => $uid]);
            $rows = $st->fetchAll();
            if ($rows) return $rows;
        }
    } catch (Throwable $e) { }

    // Fallback: todos los tanques activos
    try {
        $st = $pdo->query("SELECT t.*, e.nombre AS edificio_nombre FROM tanques t LEFT JOIN edificios e ON e.id_edificio = t.id_edificio WHERE t.activo = 1 ORDER BY t.id_tanque ASC LIMIT 20");
        return $st->fetchAll();
    } catch (Throwable $e) {
        return [];
    }
}

function eva_first_tanque(PDO $pdo, ?int $uid): ?array
{
    $list = eva_tanques_for_user($pdo, $uid);
    return $list[0] ?? null;
}

/**
 * Obtiene la ultima medicion para un tanque (via sensores -> dispositivos -> tanques o directo por id_tanque)
 * Intenta varias estrategias segun esquema.
 */
function eva_latest_medicion(PDO $pdo, int $id_tanque): ?array
{
    // Estrategia A: mediciones tiene id_tanque directo
    try {
        $col = $pdo->query("SHOW COLUMNS FROM mediciones LIKE 'id_tanque'");
        if ($col && $col->rowCount() > 0) {
            $st = $pdo->prepare("SELECT * FROM mediciones WHERE id_tanque = :id ORDER BY fecha DESC, hora DESC, id_medicion DESC LIMIT 1");
            $st->execute([':id' => $id_tanque]);
            $row = $st->fetch();
            if ($row) return $row;
        }
    } catch (Throwable $e) {}

    // Estrategia B: mediciones -> sensores -> dispositivos -> tanques
    try {
        $st = $pdo->prepare("SELECT m.* FROM mediciones m
            INNER JOIN sensores s ON s.id_sensor = m.id_sensor
            INNER JOIN dispositivos d ON d.id_dispositivo = s.id_dispositivo
            WHERE d.id_tanque = :id
            ORDER BY m.fecha DESC, m.hora DESC, m.id_medicion DESC LIMIT 1");
        $st->execute([':id' => $id_tanque]);
        $row = $st->fetch();
        if ($row) return $row;
    } catch (Throwable $e) {}

    // Estrategia C: mediciones tiene id_sensor pero sensores linked to tanque via edificio? probar id_dispositivo directo
    try {
        $st = $pdo->prepare("SELECT m.* FROM mediciones m
            INNER JOIN dispositivos d ON d.id_dispositivo = m.id_dispositivo
            WHERE d.id_tanque = :id ORDER BY m.fecha DESC, m.hora DESC LIMIT 1");
        $st->execute([':id' => $id_tanque]);
        $row = $st->fetch();
        if ($row) return $row;
    } catch (Throwable $e) {}

    return null;
}

/**
 * Totales de consumo
 */
function eva_consumo_hoy(PDO $pdo, int $id_tanque): float
{
    try {
        // intentar con campo fecha
        $st = $pdo->prepare("SELECT COALESCE(SUM(litros_consumidos),0) AS tot, COALESCE(SUM(cantidad),0) AS tot2, COALESCE(SUM(litros),0) AS tot3 FROM consumos WHERE id_tanque = :id AND DATE(fecha) = CURDATE()");
        $st->execute([':id' => $id_tanque]);
        $r = $st->fetch();
        if (!$r) return 0.0;
        foreach (['tot','tot2','tot3'] as $k) { if (isset($r[$k]) && (float)$r[$k] > 0) return (float)$r[$k]; }
        return 0.0;
    } catch (Throwable $e) { return 0.0; }
}

function eva_consumo_promedio(PDO $pdo, int $id_tanque): float
{
    try {
        $st = $pdo->prepare("SELECT COALESCE(AVG(litros_consumidos),0) AS avg1, COALESCE(AVG(cantidad),0) AS avg2, COALESCE(AVG(litros),0) AS avg3 FROM consumos WHERE id_tanque = :id");
        $st->execute([':id' => $id_tanque]);
        $r = $st->fetch();
        foreach (['avg1','avg2','avg3'] as $k) { if (isset($r[$k]) && (float)$r[$k] > 0) return (float)$r[$k]; }
        return 0.0;
    } catch (Throwable $e) { return 0.0; }
}

function eva_consumo_serie(PDO $pdo, int $id_tanque, string $period = 'semana'): array
{
    // retorna array de 7 o 30 valores para graficos
    $days = $period === 'mes' ? 30 : ($period === 'anio' ? 12 : 7);
    $result = [];
    try {
        if ($period === 'anio') {
            $st = $pdo->prepare("SELECT MONTH(fecha) AS m, COALESCE(SUM(litros_consumidos), SUM(cantidad), SUM(litros),0) AS tot FROM consumos WHERE id_tanque = :id AND YEAR(fecha)=YEAR(CURDATE()) GROUP BY MONTH(fecha) ORDER BY m ASC");
            $st->execute([':id' => $id_tanque]);
            $map = [];
            foreach ($st->fetchAll() as $r) { $map[(int)$r['m']] = (float)$r['tot']; }
            for ($i=1;$i<=12;$i++) $result[] = $map[$i] ?? 0;
            return $result;
        }
        $st = $pdo->prepare("SELECT DATE(fecha) AS d, COALESCE(SUM(litros_consumidos), SUM(cantidad), SUM(litros),0) AS tot FROM consumos WHERE id_tanque = :id AND fecha >= DATE_SUB(CURDATE(), INTERVAL :days DAY) GROUP BY DATE(fecha) ORDER BY d ASC");
        // PDO no permite bind limit interval como int directamente en algunos drivers, asi que interpolamos seguro
        $daysInt = (int)$days;
        $sql = "SELECT DATE(fecha) AS d, COALESCE(SUM(litros_consumidos), SUM(cantidad), SUM(litros),0) AS tot FROM consumos WHERE id_tanque = :id AND fecha >= DATE_SUB(CURDATE(), INTERVAL {$daysInt} DAY) GROUP BY DATE(fecha) ORDER BY d ASC";
        $st = $pdo->prepare($sql);
        $st->execute([':id' => $id_tanque]);
        $map = [];
        foreach ($st->fetchAll() as $r) { $map[$r['d']] = (float)$r['tot']; }
        // generar serie completa por fecha
        for ($i=$days-1; $i>=0; $i--) {
            $d = date('Y-m-d', strtotime("-{$i} days"));
            $result[] = $map[$d] ?? 0;
        }
        return $result;
    } catch (Throwable $e) { return array_fill(0, $days, 0); }
}

function eva_device_status(PDO $pdo, int $id_tanque): string
{
    try {
        $st = $pdo->prepare("SELECT estado, ultima_conexion FROM dispositivos WHERE id_tanque = :id ORDER BY ultima_conexion DESC LIMIT 1");
        $st->execute([':id' => $id_tanque]);
        $r = $st->fetch();
        if (!$r) return 'Desconectado';
        $estado = strtolower((string)($r['estado'] ?? ''));
        if (in_array($estado, ['activo','online','conectado','operativo'], true)) return 'Conectado';
        if (in_array($estado, ['inactivo','offline','desconectado'], true)) return 'Desconectado';
        // si ultima_conexion reciente (<10 min) considerar conectado
        if (!empty($r['ultima_conexion'])) {
            $ts = strtotime((string)$r['ultima_conexion']);
            if ($ts && (time() - $ts) < 600) return 'Conectado';
        }
        return h($r['estado'] ?? 'Desconectado');
    } catch (Throwable $e) { return 'Conectado'; }
}

function eva_estado_texto(int $pct): array
{
    if ($pct <= 10) return ['Crítico','Nivel de agua peligrosamente bajo','alert'];
    if ($pct >= 90) return ['Sobrecarga','Nivel de agua por encima del máximo','warning'];
    if ($pct <= 25) return ['Bajo','Nivel de agua bajo, considerar recarga','warning'];
    return ['Normal','Todo funciona correctamente',''];
}

function eva_alert_tipo_map(string $tipo): array
{
    $t = strtoupper($tipo);
    return match($t) {
        'NIVEL_BAJO' => ['warning','warning','Nivel bajo'],
        'NIVEL_ALTO' => ['danger','warning','Nivel alto'],
        'SIN_CONEXION' => ['info','info','Sin conexión'],
        'FALLA_SENSOR' => ['danger','info','Falla de sensor'],
        'CONSUMO_ANORMAL' => ['warning','warning','Consumo anormal'],
        default => ['info','info', h($tipo)],
    };
}

function eva_log_actividad(PDO $pdo, int $uid, string $accion, string $detalle = ''): void
{
    try {
        $pdo->prepare("INSERT INTO log_actividad (id_usuario, accion, detalle, fecha) VALUES (:uid, :acc, :det, NOW())")
            ->execute([':uid'=>$uid, ':acc'=>$accion, ':det'=>$detalle]);
    } catch (Throwable $e) {}
}
