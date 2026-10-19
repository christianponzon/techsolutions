<?php
session_start();

require_once __DIR__ . "/../config/database.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../index.php");
    exit;
}

$correo = trim($_POST["correo"] ?? "");
$password = $_POST["password"] ?? "";

if ($correo === "" || $password === "") {
    $_SESSION["error"] = "Completa todos los campos.";
    header("Location: ../index.php");
    exit;
}

if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    $_SESSION["error"] = "Ingresa un correo válido.";
    header("Location: ../index.php");
    exit;
}

$sql = "SELECT id, nombre, correo, password, rol
        FROM usuarios
        WHERE correo = :correo
        LIMIT 1";

$stmt = $conexion->prepare($sql);
$stmt->execute(["correo" => $correo]);

$usuario = $stmt->fetch();

if ($usuario && password_verify($password, $usuario["password"])) {

    session_regenerate_id(true);

    $_SESSION["usuario_id"] = $usuario["id"];
    $_SESSION["usuario_nombre"] = $usuario["nombre"];
    $_SESSION["usuario_rol"] = $usuario["rol"];

    header("Location: ../dashboard.php");
    exit;

} else {

    $_SESSION["error"] = "Correo o contraseña incorrectos.";

    header("Location: ../index.php");
    exit;
}
?>
