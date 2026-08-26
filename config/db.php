<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = "localhost";
$user = "u156482620_EVAelvigilante";
$pass = "#VALzona122233";
$db   = "u156482620_EVAelvigilante";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("ERROR MYSQL: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

// PDO centralizado reutilizando las mismas credenciales ($host,$user,$pass,$db)
// Expone $pdo y funcion eva_pdo() para que CLIENTES, ADMIN y TECNICO reutilicen la misma conexion
$pdo = null;
try {
    $dsn = "mysql:host={$host};dbname={$db};charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    error_log('EVA PDO Error: ' . $e->getMessage());
    // No detener ejecución: $conn (mysqli) sigue disponible; eva_pdo() lanzará excepción si se requiere PDO
    $pdo = null;
}

// Funcion centralizada para obtener PDO - unica fuente para CLIENTES/ADMIN/TECNICO
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