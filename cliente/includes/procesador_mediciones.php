<?php
declare(strict_types=1);
require_once __DIR__ . '/../../config/db.php';

function eva_procesador_calcular_pct(array $med, array $tanque): int {
    if (isset($med['porcentaje']) && is_numeric($med['porcentaje'])) return max(0, min(100, (int)round((float)$med['porcentaje'])));
    $altura = (float)($tanque['altura_cm'] ?? 0);
    if ($altura > 0) {
        if (isset($med['nivel_cm']) && is_numeric($med['nivel_cm']) && (float)$med['nivel_cm'] > 0) return max(0, min(100, (int)round((float)$med['nivel_cm'] / $altura * 100)));
        if (isset($med['distancia_cm']) && is_numeric($med['distancia_cm'])) {
            $nivel = $altura - (float)$med['distancia_cm'];
            return max(0, min(100, (int)round($nivel / $altura * 100)));
        }
    }
    return 0;
}

function eva_procesador_actualizar_consumos(PDO $pdo, int $idTanque): int {
    $cnt = 0;
    $cap = 5000.0;
    try {
        $st = $pdo->prepare("SELECT capacidad_litros FROM tanques WHERE id_tanque=:id LIMIT 1");
        $st->execute([':id' => $idTanque]);
        $t = $st->fetch();
        if ($t && isset($t['capacidad_litros'])) $cap = (float)$t['capacidad_litros'];
    } catch (Throwable $e) {}
    try {
        $rows = $pdo->prepare("SELECT DATE(m.fecha_hora) as d, MIN(m.litros) as min_l, MAX(m.litros) as max_l, AVG(m.litros) as avg_l, COUNT(*) as c, MIN(m.porcentaje) as min_p, MAX(m.porcentaje) as max_p, AVG(m.porcentaje) as avg_p FROM mediciones m LEFT JOIN sensores s ON s.id_sensor=m.id_sensor LEFT JOIN dispositivos d ON d.id_dispositivo=s.id_dispositivo WHERE (d.id_tanque=:tid OR d.id_tanque IS NULL) GROUP BY DATE(m.fecha_hora) ORDER BY d ASC");
        $rows->execute([':tid' => $idTanque]);
        $all = $rows->fetchAll();
        if (!$all) {
            $rows2 = $pdo->prepare("SELECT DATE(fecha_hora) as d, MIN(litros) as min_l, MAX(litros) as max_l, AVG(litros) as avg_l, COUNT(*) as c, MIN(porcentaje) as min_p, MAX(porcentaje) as max_p, AVG(porcentaje) as avg_p FROM mediciones GROUP BY DATE(fecha_hora) ORDER BY d ASC");
            $rows2->execute();
            $all = $rows2->fetchAll();
        } else {
            $check = 0;
            foreach ($all as $r) if ($r['d']) $check++;
            if ($check == 0) {
                $rows2 = $pdo->prepare("SELECT DATE(fecha_hora) as d, MIN(litros) as min_l, MAX(litros) as max_l, AVG(litros) as avg_l, COUNT(*) as c, MIN(porcentaje) as min_p, MAX(porcentaje) as max_p, AVG(porcentaje) as avg_p FROM mediciones GROUP BY DATE(fecha_hora) ORDER BY d ASC");
                $rows2->execute();
                $all = $rows2->fetchAll();
            }
        }
        foreach ($all as $day) {
            $fecha = $day['d'];
            if (!$fecha) continue;
            $litros = 0.0;
            $minL = $day['min_l'] !== null ? (float)$day['min_l'] : null;
            $maxL = $day['max_l'] !== null ? (float)$day['max_l'] : null;
            if ($minL !== null && $maxL !== null && $maxL > 0 && $minL > 0) {
                $litros = max(0, $maxL - $minL);
            }
            if ($litros == 0 && $day['avg_l'] !== null) $litros = (float)$day['avg_l'];
            if ($litros == 0 && $day['avg_p'] !== null) $litros = (float)$day['avg_p'] / 100 * $cap;
            if ($litros == 0) continue;
            $chk = $pdo->prepare("SELECT id_consumo FROM consumos WHERE id_tanque=:tid AND fecha=:f LIMIT 1");
            $chk->execute([':tid' => $idTanque, ':f' => $fecha]);
            if ($chk->fetch()) {
                $pdo->prepare("UPDATE consumos SET litros_consumidos=:lit WHERE id_tanque=:tid AND fecha=:f")->execute([':lit' => $litros, ':tid' => $idTanque, ':f' => $fecha]);
            } else {
                $pdo->prepare("INSERT INTO consumos (id_tanque, litros_consumidos, fecha) VALUES (:tid,:lit,:f)")->execute([':tid' => $idTanque, ':lit' => $litros, ':f' => $fecha]);
                $cnt++;
            }
        }
    } catch (Throwable $e) { error_log('procesador_consumos: ' . $e->getMessage()); }
    return $cnt;
}

function eva_procesador_actualizar_dispositivo(PDO $pdo, int $idTanque): void {
    try {
        $st = $pdo->prepare("SELECT m.fecha_hora FROM mediciones m LEFT JOIN sensores s ON s.id_sensor=m.id_sensor LEFT JOIN dispositivos d ON d.id_dispositivo=s.id_dispositivo WHERE (d.id_tanque=:tid OR d.id_tanque IS NULL) ORDER BY m.fecha_hora DESC LIMIT 1");
        $st->execute([':tid' => $idTanque]);
        $r = $st->fetch();
        if (!$r || empty($r['fecha_hora'])) {
            $st2 = $pdo->prepare("SELECT fecha_hora FROM mediciones ORDER BY fecha_hora DESC LIMIT 1");
            $st2->execute();
            $r = $st2->fetch();
        }
        if (!$r || empty($r['fecha_hora'])) return;
        $ultima = $r['fecha_hora'];
        $ts = strtotime((string)$ultima);
        $estado = ($ts && (time() - $ts) < 600) ? 'ONLINE' : 'OFFLINE';
        $pdo->prepare("UPDATE dispositivos SET ultima_conexion=:u, estado=:e, ultima_actualizacion=NOW() WHERE id_tanque=:tid")->execute([':u' => $ultima, ':e' => $estado, ':tid' => $idTanque]);
        try {
            $pdo->prepare("INSERT INTO historial_estado_dispositivo (id_dispositivo, estado, descripcion, fecha) SELECT id_dispositivo, :e, :d, NOW() FROM dispositivos WHERE id_tanque=:tid")->execute([':e' => $estado, ':d' => 'Actualización automática desde mediciones', ':tid' => $idTanque]);
        } catch (Throwable $e) {}
    } catch (Throwable $e) { error_log('procesador_dispositivo: ' . $e->getMessage()); }
}

function eva_procesador_detectar_alertas(PDO $pdo, int $idTanque): int {
    $created = 0;
    try {
        $cfg = $pdo->prepare("SELECT nivel_minimo, nivel_maximo FROM configuracion_alertas WHERE id_tanque=:id LIMIT 1");
        $cfg->execute([':id' => $idTanque]);
        $c = $cfg->fetch();
        if (!$c) return 0;
        $min = (float)$c['nivel_minimo'];
        $max = (float)$c['nivel_maximo'];
        try {
            $meds = $pdo->prepare("SELECT m.* FROM mediciones m LEFT JOIN sensores s ON s.id_sensor=m.id_sensor LEFT JOIN dispositivos d ON d.id_dispositivo=s.id_dispositivo WHERE (d.id_tanque=:tid OR d.id_tanque IS NULL) ORDER BY m.fecha_hora DESC LIMIT 30");
            $meds->execute([':tid' => $idTanque]);
            $rows = $meds->fetchAll();
        } catch (Throwable $e) { $rows = []; }
        if (!$rows) {
            try { $st2 = $pdo->prepare("SELECT * FROM mediciones ORDER BY fecha_hora DESC LIMIT 30"); $st2->execute(); $rows = $st2->fetchAll(); } catch (Throwable $e) {}
        }
        if (!$rows) return 0;
        $ultima = $rows[0];
        $pctUlt = (float)($ultima['porcentaje'] ?? 0);
        $tempUlt = isset($ultima['temperatura']) ? (float)$ultima['temperatura'] : null;
        $tipos = [];
        if ($pctUlt > 0 && $pctUlt <= $min) $tipos[] = ['tipo' => 'NIVEL_BAJO', 'desc' => "Nivel bajo {$pctUlt}% <= mínimo {$min}% (medición {$ultima['id_medicion']})"];
        if ($pctUlt >= $max) $tipos[] = ['tipo' => 'NIVEL_ALTO', 'desc' => "Nivel alto {$pctUlt}% >= máximo {$max}% (medición {$ultima['id_medicion']})"];
        $sensorChk = $pdo->prepare("SELECT estado FROM sensores WHERE id_dispositivo IN (SELECT id_dispositivo FROM dispositivos WHERE id_tanque=:tid) LIMIT 1");
        $sensorChk->execute([':tid' => $idTanque]);
        $sRow = $sensorChk->fetch();
        if ($sRow && strtoupper((string)$sRow['estado']) === 'FALLA') $tipos[] = ['tipo' => 'FALLA_SENSOR', 'desc' => "Sensor en estado FALLA detectado"];
        elseif ($pctUlt == 0) {
            $zeros = 0;
            foreach (array_slice($rows, 0, 5) as $r) if ((float)($r['porcentaje'] ?? -1) == 0) $zeros++;
            if ($zeros >= 3) $tipos[] = ['tipo' => 'FALLA_SENSOR', 'desc' => "Posible falla de sensor: {$zeros} mediciones consecutivas en 0%"];
        }
        $devSt = $pdo->prepare("SELECT ultima_conexion FROM dispositivos WHERE id_tanque=:tid LIMIT 1");
        $devSt->execute([':tid' => $idTanque]);
        $dRow = $devSt->fetch();
        if ($dRow && !empty($dRow['ultima_conexion'])) {
            $tsDev = strtotime((string)$dRow['ultima_conexion']);
            if ($tsDev && (time() - $tsDev) > 3600) $tipos[] = ['tipo' => 'SIN_CONEXION', 'desc' => "Sin conexión por más de 60 minutos (última: {$dRow['ultima_conexion']})"];
        }
        try {
            $avgStmt = $pdo->prepare("SELECT AVG(litros_consumidos) as avg_c FROM consumos WHERE id_tanque=:tid");
            $avgStmt->execute([':tid' => $idTanque]);
            $avgRow = $avgStmt->fetch();
            $avgC = $avgRow && $avgRow['avg_c'] !== null ? (float)$avgRow['avg_c'] : 0;
            $hoyStmt = $pdo->prepare("SELECT litros_consumidos FROM consumos WHERE id_tanque=:tid AND fecha=CURDATE() LIMIT 1");
            $hoyStmt->execute([':tid' => $idTanque]);
            $hoyRow = $hoyStmt->fetch();
            if ($avgC > 0 && $hoyRow && (float)$hoyRow['litros_consumidos'] > $avgC * 1.5) $tipos[] = ['tipo' => 'CONSUMO_ANORMAL', 'desc' => "Consumo anormal: hoy " . round((float)$hoyRow['litros_consumidos'], 2) . " L vs promedio " . round($avgC, 2) . " L"];
        } catch (Throwable $e) {}
        foreach ($tipos as $a) {
            $tipo = $a['tipo'];
            $desc = $a['desc'];
            $chk = $pdo->prepare("SELECT id_alerta FROM alertas WHERE id_tanque=:tid AND tipo=:tipo AND descripcion=:desc AND DATE(fecha_hora)=CURDATE() LIMIT 1");
            $chk->execute([':tid' => $idTanque, ':tipo' => $tipo, ':desc' => $desc]);
            if ($chk->fetch()) continue;
            $pdo->prepare("INSERT INTO alertas (id_tanque, tipo, descripcion, estado) VALUES (:tid,:tipo,:desc,'PENDIENTE')")->execute([':tid' => $idTanque, ':tipo' => $tipo, ':desc' => $desc]);
            $created++;
            try {
                $tanqueNombre = $pdo->prepare("SELECT nombre FROM tanques WHERE id_tanque=:id LIMIT 1");
                $tanqueNombre->execute([':id' => $idTanque]);
                $tN = $tanqueNombre->fetch();
                $nombre = $tN ? $tN['nombre'] : "Tanque {$idTanque}";
                $edifStmt = $pdo->prepare("SELECT id_usuario FROM edificios e INNER JOIN tanques t ON t.id_edificio=e.id_edificio WHERE t.id_tanque=:tid LIMIT 1");
                $edifStmt->execute([':tid' => $idTanque]);
                $eRow = $edifStmt->fetch();
                if ($eRow && !empty($eRow['id_usuario'])) {
                    $uid = (int)$eRow['id_usuario'];
                    $msg = "Alerta {$tipo} en {$nombre}: {$desc}";
                    $pdo->prepare("INSERT INTO notificaciones (id_usuario, mensaje, leida, fecha_hora) VALUES (:uid,:msg,0,NOW())")->execute([':uid' => $uid, ':msg' => $msg]);
                }
            } catch (Throwable $e) {}
        }
    } catch (Throwable $e) { error_log('procesador_alertas: ' . $e->getMessage()); }
    return $created;
}

function eva_procesar_tanque(PDO $pdo, int $idTanque): array {
    $r = ['consumos' => 0, 'alertas' => 0, 'dispositivo' => false];
    $r['consumos'] = eva_procesador_actualizar_consumos($pdo, $idTanque);
    eva_procesador_actualizar_dispositivo($pdo, $idTanque);
    $r['dispositivo'] = true;
    $r['alertas'] = eva_procesador_detectar_alertas($pdo, $idTanque);
    return $r;
}

function eva_procesar_todos(PDO $pdo, ?int $idTanque = null): array {
    $total = ['consumos' => 0, 'alertas' => 0, 'tanques' => 0];
    $tanques = [];
    if ($idTanque) $tanques[] = ['id_tanque' => $idTanque];
    else {
        try { $tanques = $pdo->query("SELECT id_tanque FROM tanques WHERE activo=1")->fetchAll(); } catch (Throwable $e) { return $total; }
    }
    foreach ($tanques as $t) {
        $tid = (int)$t['id_tanque'];
        $res = eva_procesar_tanque($pdo, $tid);
        $total['consumos'] += $res['consumos'];
        $total['alertas'] += $res['alertas'];
        $total['tanques']++;
    }
    return $total;
}

function eva_dashboard_datos(PDO $pdo, int $idTanque): array {
    $out = ['pct' => 0, 'litros' => 0, 'temp' => 0, 'hum' => 0, 'estado' => 'Sin datos', 'estadoClass' => '', 'lastUpdate' => null, 'capacidad' => 0];
    try {
        $tSt = $pdo->prepare("SELECT capacidad_litros, altura_cm FROM tanques WHERE id_tanque=:id LIMIT 1");
        $tSt->execute([':id' => $idTanque]);
        $tanque = $tSt->fetch();
        $out['capacidad'] = $tanque ? (int)$tanque['capacidad_litros'] : 0;
        $med = null;
        try {
            $st = $pdo->prepare("SELECT m.* FROM mediciones m LEFT JOIN sensores s ON s.id_sensor=m.id_sensor LEFT JOIN dispositivos d ON d.id_dispositivo=s.id_dispositivo WHERE (d.id_tanque=:id OR d.id_tanque IS NULL) ORDER BY m.fecha_hora DESC LIMIT 1");
            $st->execute([':id' => $idTanque]);
            $med = $st->fetch();
            if (!$med) { $st2 = $pdo->prepare("SELECT * FROM mediciones ORDER BY fecha_hora DESC LIMIT 1"); $st2->execute(); $med = $st2->fetch(); }
        } catch (Throwable $e) {
            try { $st2 = $pdo->prepare("SELECT * FROM mediciones ORDER BY fecha_hora DESC LIMIT 1"); $st2->execute(); $med = $st2->fetch(); } catch (Throwable $ex) {}
        }
        if ($med) {
            $out['pct'] = eva_procesador_calcular_pct($med, $tanque ?: []);
            if (isset($med['temperatura']) && is_numeric($med['temperatura'])) $out['temp'] = (int)round((float)$med['temperatura']);
            if (isset($med['humedad']) && is_numeric($med['humedad'])) $out['hum'] = (int)round((float)$med['humedad']);
            if (isset($med['litros']) && is_numeric($med['litros']) && (float)$med['litros'] > 0) $out['litros'] = (int)round((float)$med['litros']);
            else $out['litros'] = (int)round($out['capacidad'] * $out['pct'] / 100);
            if (!empty($med['fecha_hora'])) $out['lastUpdate'] = date('d/m/Y H:i', strtotime($med['fecha_hora']));
            $out['fechaRaw'] = $med['fecha_hora'];
        }
        if ($out['pct'] <= 10) { $out['estado'] = 'Crítico'; $out['estadoClass'] = 'alert'; }
        elseif ($out['pct'] >= 90) { $out['estado'] = 'Sobrecarga'; $out['estadoClass'] = 'warning'; }
        elseif ($out['pct'] <= 25) { $out['estado'] = 'Bajo'; $out['estadoClass'] = 'warning'; }
        else { $out['estado'] = 'Normal'; $out['estadoClass'] = ''; }
        try {
            $st = $pdo->prepare("SELECT litros_consumidos FROM consumos WHERE id_tanque=:id AND fecha=CURDATE() LIMIT 1");
            $st->execute([':id' => $idTanque]);
            $c = $st->fetch();
            if ($c) $out['consumoHoy'] = (float)$c['litros_consumidos'];
            else {
                $st2 = $pdo->prepare("SELECT litros_consumidos FROM consumos WHERE id_tanque=:id ORDER BY fecha DESC LIMIT 1");
                $st2->execute([':id' => $idTanque]);
                $c2 = $st2->fetch();
                $out['consumoHoy'] = $c2 ? (float)$c2['litros_consumidos'] : 0;
            }
        } catch (Throwable $e) { $out['consumoHoy'] = 0; }
        try {
            $st = $pdo->prepare("SELECT AVG(litros_consumidos) as avg_c FROM consumos WHERE id_tanque=:id");
            $st->execute([':id' => $idTanque]);
            $c = $st->fetch();
            $out['promedio'] = $c && $c['avg_c'] !== null ? (float)$c['avg_c'] : 0;
        } catch (Throwable $e) { $out['promedio'] = 0; }
        try {
            $st = $pdo->prepare("SELECT estado, ultima_conexion FROM dispositivos WHERE id_tanque=:id LIMIT 1");
            $st->execute([':id' => $idTanque]);
            $d = $st->fetch();
            $out['deviceStatus'] = $d ? ($d['estado'] ?? 'Desconectado') : 'Desconectado';
        } catch (Throwable $e) { $out['deviceStatus'] = 'Desconectado'; }
    } catch (Throwable $e) {}
    return $out;
}
