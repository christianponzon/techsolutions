<?php

// ==========================================
// TECHSOLUTIONS
// CONEXIÓN A BASE DE DATOS MYSQL
// ==========================================

$host = "localhost";
$dbname = "techsolutions_db";
$username = "root";
$password = "";

try {

    $conexion = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password
    );

    $conexion->setAttribute(
        PDO::ATTR_ERRMODE,
        PDO::ERRMODE_EXCEPTION
    );

    $conexion->setAttribute(
        PDO::ATTR_DEFAULT_FETCH_MODE,
        PDO::FETCH_ASSOC
    );

} catch (PDOException $e) {

    error_log("Error de conexión: " . $e->getMessage());

    http_response_code(500);
    exit("No se pudo conectar con la base de datos.");

}

?>
