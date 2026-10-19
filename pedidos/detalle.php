
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

// Validar ID del pedido
$id = filter_input(
    INPUT_GET,
    "id",
    FILTER_VALIDATE_INT
);

if (!$id || $id <= 0) {
    header("Location: index.php");
    exit;
}

// Consultar información del pedido
$stmt = $conexion->prepare(
    "SELECT id, cliente, fecha, total, estado
     FROM pedidos
     WHERE id = :id"
);

$stmt->execute(["id" => $id]);
$pedido = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$pedido) {
    http_response_code(404);
    exit("El pedido solicitado no existe.");
}

// Consultar productos del pedido
$stmt = $conexion->prepare(
    "SELECT
        d.id,
        d.producto_id,
        p.nombre AS producto,
        d.cantidad,
        d.precio_unitario,
        d.subtotal
     FROM detalle_pedidos d
     INNER JOIN productos p
        ON d.producto_id = p.id
     WHERE d.pedido_id = :pedido_id
     ORDER BY d.id ASC"
);

$stmt->execute([
    "pedido_id" => $id
]);

$detalles = $stmt->fetchAll(PDO::FETCH_ASSOC);

$totalUnidades = 0;

foreach ($detalles as $detalle) {
    $totalUnidades += (int)$detalle["cantidad"];
}

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Detalle del Pedido | TechSolutions</title>

    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="../css/pedidos.css">

</head>

<body>

<aside class="sidebar">

    <h2>TechSolutions</h2>
    <p>Inventarios y Pedidos</p>

    <a href="../dashboard.php">Inicio</a>

    <a href="../productos/index.php">Productos</a>

    <a href="../inventario/index.php">Inventario</a>

    <a href="index.php" class="active">Pedidos</a>

    <a href="../logout.php">Cerrar sesión</a>

</aside>

<main class="main">

    <div class="header">

        <div>
            <h1>
                Detalle del Pedido #<?= (int)$pedido["id"] ?>
            </h1>

            <p>Información completa de la compra</p>
        </div>

        <div class="user">
            <?= escapar($_SESSION["usuario_nombre"] ?? "Administrador") ?>
        </div>

    </div>

    <!-- INFORMACIÓN GENERAL -->

    <div class="section">

        <h2>Información del pedido</h2>

        <div class="order-info-grid">

            <div class="order-info-item">
                <span>Cliente</span>
                <strong>
                    <?= escapar($pedido["cliente"]) ?>
                </strong>
            </div>

            <div class="order-info-item">
                <span>Fecha de registro</span>
                <strong>
                    <?= escapar(date(
                        "d/m/Y H:i",
                        strtotime($pedido["fecha"])
                    )) ?>
                </strong>
            </div>

            <div class="order-info-item">
                <span>Estado</span>
                <strong>
                    <?= escapar($pedido["estado"]) ?>
                </strong>
            </div>

            <div class="order-info-item">
                <span>Total de unidades</span>
                <strong>
                    <?= $totalUnidades ?>
                </strong>
            </div>

        </div>

    </div>

    <!-- PRODUCTOS COMPRADOS -->

    <div class="section">

        <h2>Productos del pedido</h2>

        <div class="order-table-wrapper">

            <table>

                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Producto</th>
                        <th>Cantidad</th>
                        <th>Precio unitario</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>

                <tbody>

                    <?php if (empty($detalles)): ?>

                        <tr>
                            <td colspan="5" class="empty">
                                No hay productos asociados.
                            </td>
                        </tr>

                    <?php else: ?>

                        <?php foreach ($detalles as $detalle): ?>

                            <tr>

                                <td>
                                    <?= (int)$detalle["producto_id"] ?>
                                </td>

                                <td>
                                    <?= escapar($detalle["producto"]) ?>
                                </td>

                                <td>
                                    <?= (int)$detalle["cantidad"] ?>
                                </td>

                                <td>
                                    S/ <?= number_format(
                                        (float)$detalle["precio_unitario"],
                                        2
                                    ) ?>
                                </td>

                                <td>
                                    S/ <?= number_format(
                                        (float)$detalle["subtotal"],
                                        2
                                    ) ?>
                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>

        <div class="order-detail-total">

            <span>Total del pedido</span>

            <strong>
                S/ <?= number_format(
                    (float)$pedido["total"],
                    2
                ) ?>
            </strong>

        </div>

        <div class="order-detail-actions">

            <a href="index.php" class="btn-order-clear">
                Volver a pedidos
            </a>

            <button
                type="button"
                class="btn-order-search"
                onclick="window.print()"
            >
                Imprimir pedido
            </button>

        </div>

    </div>

</main>

</body>
</html>
