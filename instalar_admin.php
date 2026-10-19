<?php

// Solo permitir ejecución desde la terminal
if (PHP_SAPI !== "cli") {
    http_response_code(403);
    exit("Acceso no permitido.");
}

require_once __DIR__ . "/config/database.php";

echo "=== TechSolutions: crear administrador ===\n";

echo "Nombre: ";
$nombre = trim(fgets(STDIN));

echo "Correo: ";
$correo = trim(fgets(STDIN));

echo "Contraseña: ";
$password = rtrim(fgets(STDIN), "\r\n");

if (
    $nombre === "" ||
    mb_strlen($nombre) > 150 ||
    !filter_var($correo, FILTER_VALIDATE_EMAIL) ||
    strlen($password) < 12
) {
    exit("Datos inválidos. La contraseña debe tener al menos 12 caracteres.\n");
}

$stmt = $conexion->prepare(
    "SELECT id FROM usuarios WHERE correo = :correo LIMIT 1"
);

$stmt->execute(["correo" => $correo]);

if ($stmt->fetch()) {
    exit("Ya existe un usuario con ese correo.\n");
}

$hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $conexion->prepare(
    "INSERT INTO usuarios (nombre, correo, password, rol)
     VALUES (:nombre, :correo, :password, 'administrador')"
);

$stmt->execute([
    "nombre" => $nombre,
    "correo" => $correo,
    "password" => $hash
]);

echo "Administrador creado correctamente.\n";