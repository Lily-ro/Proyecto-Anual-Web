<?php
// EVA - Conexion central unica. NO crear db2.php ni conexiones por rol.
// Mantiene credenciales originales del proyecto y añade fallback para hosting Hostinger (u767...) y local (root).
// Produccion: /home/u767580032/domains/dashboard.elvigilantedeagua.com/public_html/config/db.php linea 11
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

$host = "localhost";

// Candidatos en orden: produccion Hostinger (u767...), original proyecto (u156...), local XAMPP (root)
$candidatos = [
    ['user' => 'u767580032_elvigilante',     'pass' => '#VALzona122233', 'db' => 'u767580032_elvigilante'],
    ['user' => 'u156482620_EVAelvigilante', 'pass' => '#VALzona122233', 'db' => 'u156482620_EVAelvigilante'],
    ['user' => 'root',                      'pass' => '',               'db' => 'u156482620_EVAelvigilante'],
    ['user' => 'root',                      'pass' => '',               'db' => 'u767580032_elvigilante'],
    ['user' => 'root',                      'pass' => 'root',           'db' => 'u156482620_EVAelvigilante'],
];

$conn = null;
$pdo = null;
$ultimoError = '';
foreach ($candidatos as $c) {
    $u = $c['user']; $p = $c['pass']; $d = $c['db'];
    // Intentar mysqli
    try {
        mysqli_report(MYSQLI_REPORT_OFF);
        $tmp = @new mysqli($host, $u, $p, $d);
        if ($tmp && !$tmp->connect_error) {
            $tmp->set_charset("utf8mb4");
            $conn = $tmp;
            $user = $u; $pass = $p; $db = $d;
            break;
        }
        $ultimoError = $tmp ? $tmp->connect_error : 'mysqli init failed';
    } catch (Throwable $e) { $ultimoError = $e->getMessage(); }
}
if (!$conn) {
    error_log("EVA db.php: todos los candidatos fallaron. Ultimo error: {$ultimoError}");
    // Mensaje generico para usuario final, detalle en log
    http_response_code(500);
    die("Error de conexion a la base de datos. Contacte al administrador.");
}

// PDO centralizado reutilizando credenciales que funcionaron ($host,$user,$pass,$db)
try {
    $dsn = "mysql:host={$host};dbname={$db};charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    error_log('EVA PDO Error: ' . $e->getMessage());
    $pdo = null;
}

if (!function_exists('eva_pdo')) {
    function eva_pdo(): PDO
    {
        global $pdo;
        if ($pdo instanceof PDO) {
            return $pdo;
        }
        error_log('EVA eva_pdo(): PDO central no disponible - verificar config/db.php');
        throw new RuntimeException('Error de conexion a la base de datos');
    }
}