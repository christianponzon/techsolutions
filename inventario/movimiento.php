<?php

session_start();

if (!isset($_SESSION["usuario_id"])) {
    header("Location: ../index.php");
    exit;
}

require_once __DIR__ . "/../config/database.php";

function escapar($valor) {
    return htmlspecialchars(
        (string)$valor,
        ENT_QUOTES,
        "UTF-8"
    );
}

// Token de seguridad
if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

$error = "";

// Procesar movimiento
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $producto_id = filter_var(
        $_POST["producto_id"] ?? null,
        FILTER_VALIDATE_INT
    );

    $tipo = $_POST["tipo"] ?? "";

    $cantidad = filter_var(
        $_POST["cantidad"] ?? null,
        FILTER_VALIDATE_INT
    );

    $token = $_POST["csrf_token"] ?? "";

    if (
        !is_string($token) ||
        !hash_equals($_SESSION["csrf_token"], $token)
    ) {

        $error = "Solicitud no válida.";

    } elseif (
        !$producto_id ||
        $producto_id <= 0 ||
        !in_array($tipo, ["entrada", "salida"], true) ||
        !$cantidad ||
        $cantidad <= 0
    ) {

        $error = "Ingresa datos válidos.";

    } else {

        try {

            // Iniciar transacción
            $conexion->beginTransaction();

            // Bloquear producto para evitar conflictos
            $stmt = $conexion->prepare(
                "SELECT id, stock
                 FROM productos
                 WHERE id = :id
                 FOR UPDATE"
            );

            $stmt->execute([
                "id" => $producto_id
            ]);

            $producto = $stmt->fetch();

            if (!$producto) {
                throw new Exception("El producto no existe.");
            }

            $stock_actual = (int)$producto["stock"];

            // Calcular nuevo stock
            if ($tipo === "entrada") {

                $nuevo_stock = $stock_actual + $cantidad;

            } else {

                if ($cantidad > $stock_actual) {
                    throw new Exception(
                        "No hay suficiente stock disponible."
                    );
                }

                $nuevo_stock = $stock_actual - $cantidad;
            }

            // Actualizar stock
            $stmt = $conexion->prepare(
                "UPDATE productos
                 SET stock = :stock
                 WHERE id = :id"
            );

            $stmt->execute([
                "stock" => $nuevo_stock,
                "id" => $producto_id
            ]);

            // Guardar movimiento
            $stmt = $conexion->prepare(
                "INSERT INTO movimientos_inventario
                 (producto_id, tipo, cantidad)
                 VALUES (:producto_id, :tipo, :cantidad)"
            );

            $stmt->execute([
                "producto_id" => $producto_id,
                "tipo" => $tipo,
                "cantidad" => $cantidad
            ]);

            // Confirmar cambios
            $conexion->commit();

            header("Location: index.php?movimiento=ok");
            exit;

        } catch (Exception $e) {

            if ($conexion->inTransaction()) {
                $conexion->rollBack();
            }

            if ($e instanceof PDOException) {
                error_log($e->getMessage());
                $error = "No se pudo registrar el movimiento.";
            } else {
                $error = $e->getMessage();
            }
        }
    }
}

// Consultar productos disponibles
$stmt = $conexion->query(
    "SELECT id, nombre, stock
     FROM productos
     ORDER BY nombre ASC"
);

$productos = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Registrar Movimiento | TechSolutions</title>

    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="../css/inventario.css">
    <link rel="stylesheet" href="../css/productos.css">

</head>

<body>

<aside class="sidebar">

    <h2>TechSolutions</h2>
    <p>Inventarios y Pedidos</p>

    <a href="../dashboard.php">Inicio</a>
    <a href="../productos/index.php">Productos</a>

    <a href="index.php" class="active">
        Inventario
    </a>

    <a href="../pedidos/index.php">Pedidos</a>
    <a href="../logout.php">Cerrar sesión</a>

</aside>

<main class="main">

    <div class="header">
        <div>
            <h1>Registrar Movimiento</h1>
            <p>Registra entradas y salidas de productos</p>
        </div>
    </div>

    <div class="section">

        <?php if ($error !== ""): ?>

            <div class="error-message">
                <?= escapar($error) ?>
            </div>

        <?php endif; ?>

        <form method="POST" class="product-form">

            <input
                type="hidden"
                name="csrf_token"
                value="<?= escapar($_SESSION["csrf_token"]) ?>"
            >

            <div class="form-field">

                <label>Seleccionar producto *</label>

                <select name="producto_id" required>

                    <option value="">
                        Seleccione un producto
                    </option>

                    <?php foreach ($productos as $producto): ?>

                        <option
                            value="<?= (int)$producto["id"] ?>"
                            <?= (string)($_POST["producto_id"] ?? "") ===
                                (string)$producto["id"] ? "selected" : "" ?>
                        >
                            <?= escapar($producto["nombre"]) ?>
                            (Stock: <?= (int)$producto["stock"] ?>)
                        </option>

                    <?php endforeach; ?>

                </select>

            </div>

            <div class="form-field">

                <label>Tipo de movimiento *</label>

                <select name="tipo" required>

                    <option value="entrada"
                        <?= ($_POST["tipo"] ?? "") === "entrada"
                            ? "selected" : "" ?>>
                        Entrada de productos
                    </option>

                    <option value="salida"
                        <?= ($_POST["tipo"] ?? "") === "salida"
                            ? "selected" : "" ?>>
                        Salida de productos
                    </option>

                </select>

            </div>

            <div class="form-field">

                <label>Cantidad *</label>

                <input
                    type="number"
                    name="cantidad"
                    min="1"
                    step="1"
                    value="<?= escapar($_POST["cantidad"] ?? "") ?>"
                    placeholder="Ingrese la cantidad"
                    required
                >

            </div>

            <div class="form-actions">

                <a href="index.php" class="btn-clear">
                    Cancelar
                </a>

                <button type="submit" class="btn-add">
                    Guardar movimiento
                </button>

            </div>

        </form>

    </div>

</main>

</body>
</html>
