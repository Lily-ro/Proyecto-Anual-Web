<?php

header("Content-Type: application/json; charset=utf-8");

error_reporting(E_ALL);
ini_set("display_errors", "0");

require_once __DIR__ . "/../config/db.php";

try {

    $id_sensor = $_POST["id_sensor"] ?? 1;
    $temperatura = $_POST["temperatura"] ?? null;
    $humedad = $_POST["humedad"] ?? null;
    $distancia = $_POST["distancia_cm"] ?? null;
    $nivel = $_POST["nivel_cm"] ?? null;
    $porcentaje = $_POST["porcentaje"] ?? null;
    $litros = $_POST["litros"] ?? null;

    if (
        $temperatura === null ||
        $humedad === null ||
        $distancia === null ||
        $nivel === null ||
        $porcentaje === null ||
        $litros === null
    ) {
        http_response_code(400);

        echo json_encode([
            "success" => false,
            "mensaje" => "Faltan datos"
        ]);

        exit;
    }

    $sql = "INSERT INTO mediciones
            (
                id_sensor,
                distancia_cm,
                nivel_cm,
                porcentaje,
                litros,
                temperatura,
                humedad
            )
            VALUES
            (
                :id_sensor,
                :distancia_cm,
                :nivel_cm,
                :porcentaje,
                :litros,
                :temperatura,
                :humedad
            )";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ":id_sensor" => $id_sensor,
        ":distancia_cm" => $distancia,
        ":nivel_cm" => $nivel,
        ":porcentaje" => $porcentaje,
        ":litros" => $litros,
        ":temperatura" => $temperatura,
        ":humedad" => $humedad
    ]);

    echo json_encode([
        "success" => true,
        "mensaje" => "Datos guardados correctamente",
        "id_medicion" => $pdo->lastInsertId()
    ]);

} catch (PDOException $e) {

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "mensaje" => "Error de base de datos",
        "error" => $e->getMessage()
    ]);

} catch (Throwable $e) {

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "mensaje" => "Error del servidor",
        "error" => $e->getMessage()
    ]);
}