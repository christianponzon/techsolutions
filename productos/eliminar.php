
<?php

session_start();

// Verificar sesión
if (!isset($_SESSION["usuario_id"])) {
    header("Location: ../index.php");
    exit;
}

require_once __DIR__ . "/../config/database.php";

// Solo permitir solicitudes POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit;
}

// Verificar token de seguridad
$token = $_POST["csrf_token"] ?? "";

if (
    empty($_SESSION["csrf_token"]) ||
    !is_string($token) ||
    !hash_equals($_SESSION["csrf_token"], $token)
) {
    http_response_code(403);
    exit("Solicitud no autorizada.");
}

// Obtener ID del producto
$id = filter_var(
    $_POST["id"] ?? null,
    FILTER_VALIDATE_INT
);

if (!$id || $id <= 0) {
    header("Location: index.php?error=id");
    exit;
}

try {

    $conexion->beginTransaction();

    // Bloquear producto durante la operación
    $stmt = $conexion->prepare(
        "SELECT id FROM productos
         WHERE id = :id FOR UPDATE"
    );

    $stmt->execute(["id" => $id]);

    if (!$stmt->fetch()) {
        $conexion->rollBack();
        header("Location: index.php?error=noexiste");
        exit;
    }

    // Comprobar pedidos asociados
    $stmt = $conexion->prepare(
        "SELECT COUNT(*) FROM detalle_pedidos
         WHERE producto_id = :id"
    );

    $stmt->execute(["id" => $id]);

    if ($stmt->fetchColumn() > 0) {
        $conexion->rollBack();
        header("Location: index.php?error=pedidos");
        exit;
    }

    // Comprobar movimientos de inventario asociados
$stmt = $conexion->prepare(
    "SELECT COUNT(*) FROM movimientos_inventario
     WHERE producto_id = :id"
);

$stmt->execute(["id" => $id]);

if ($stmt->fetchColumn() > 0) {
    $conexion->rollBack();

    header("Location: index.php?error=movimientos");
    exit;
}

    // Eliminar producto
    $stmt = $conexion->prepare(
        "DELETE FROM productos WHERE id = :id"
    );

    $stmt->execute(["id" => $id]);

    $conexion->commit();

    header("Location: index.php?eliminado=1");
    exit;

} catch (PDOException $e) {

    if ($conexion->inTransaction()) {
        $conexion->rollBack();
    }

    error_log("Error al eliminar: " . $e->getMessage());

    header("Location: index.php?error=general");
    exit;
}
?>
