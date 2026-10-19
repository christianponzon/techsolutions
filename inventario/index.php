
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

// Consultar todos los productos
$stmt = $conexion->query(
    "SELECT id, nombre, precio, stock, stock_minimo
     FROM productos
     ORDER BY nombre ASC"
);

$productos = $stmt->fetchAll();

// Estadísticas del inventario
$totalProductos = count($productos);
$totalUnidades = 0;
$stockCritico = 0;
$agotados = 0;

foreach ($productos as $producto) {

    $totalUnidades += (int)$producto["stock"];

    if ((int)$producto["stock"] === 0) {
        $agotados++;
    } elseif (
        (int)$producto["stock"] <=
        (int)$producto["stock_minimo"]
    ) {
        $stockCritico++;
    }
}

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Inventario | TechSolutions</title>

    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="../css/inventario.css">
</head>

<body>

<!-- MENÚ LATERAL -->

<aside class="sidebar">

    <h2>TechSolutions</h2>
    <p>Inventarios y Pedidos</p>

    <a href="../dashboard.php">
        Inicio
    </a>

    <a href="../productos/index.php">
        Productos
    </a>

    <a href="index.php" class="active">
        Inventario
    </a>

    <a href="../pedidos/index.php">
        Pedidos
    </a>

    <a href="../logout.php">
        Cerrar sesión
    </a>

</aside>

<!-- CONTENIDO -->

<main class="main">

    <div class="header">

        <div>
            <h1>Control de Inventario</h1>
            <p>Gestiona las existencias de productos</p>
        </div>

        <div class="user">
            <?= escapar($_SESSION["usuario_nombre"]) ?>
        </div>

    </div>

    
<?php if (($_GET["movimiento"] ?? "") === "ok"): ?>

    <div class="inventory-ok" style="margin-bottom:20px;">
        Movimiento registrado correctamente.
        El stock se actualizó automáticamente.
    </div>

<?php endif; ?>

    <!-- ESTADÍSTICAS -->

    <div class="cards">

        <div class="card">
            <h3>Total de productos</h3>
            <div class="number">
                <?= $totalProductos ?>
            </div>
        </div>

        <div class="card">
            <h3>Unidades disponibles</h3>
            <div class="number">
                <?= $totalUnidades ?>
            </div>
        </div>

        <div class="card">
            <h3>Stock crítico</h3>
            <div class="number">
                <?= $stockCritico ?>
            </div>
        </div>

        <div class="card">
            <h3>Productos agotados</h3>
            <div class="number">
                <?= $agotados ?>
            </div>
        </div>

    </div>

    <!-- BOTONES -->

    <div class="inventory-toolbar">

        <h2>Existencias de productos</h2>

        <div class="inventory-buttons">

            <a href="movimiento.php" class="btn-movement">
                + Registrar movimiento
            </a>

            <a href="historial.php" class="btn-history">
                Ver historial
            </a>

        </div>

    </div>

    <!-- TABLA -->

    <div class="section">

        <table>

            <thead>
                <tr>
                    <th>ID</th>
                    <th>Producto</th>
                    <th>Precio</th>
                    <th>Stock actual</th>
                    <th>Stock mínimo</th>
                    <th>Estado</th>
                </tr>
            </thead>

            <tbody>

            <?php if (empty($productos)): ?>

                <tr>
                    <td colspan="6" class="empty">
                        No hay productos registrados.
                    </td>
                </tr>

            <?php else: ?>

                <?php foreach ($productos as $producto): ?>

                    <?php

                    if ($producto["stock"] == 0) {
                        $estado = "Agotado";
                        $clase = "danger";

                    } elseif (
                        $producto["stock"] <=
                        $producto["stock_minimo"]
                    ) {
                        $estado = "Stock crítico";
                        $clase = "warning";

                    } else {
                        $estado = "Disponible";
                        $clase = "success";
                    }

                    ?>

                    <tr>

                        <td>
                            <?= escapar($producto["id"]) ?>
                        </td>

                        <td>
                            <?= escapar($producto["nombre"]) ?>
                        </td>

                        <td>
                            S/ <?= number_format(
                                $producto["precio"], 2
                            ) ?>
                        </td>

                        <td>
                            <?= escapar($producto["stock"]) ?>
                        </td>

                        <td>
                            <?= escapar($producto["stock_minimo"]) ?>
                        </td>

                        <td>
                            <span class="badge <?= $clase ?>">
                                <?= $estado ?>
                            </span>
                        </td>

                    </tr>

                <?php endforeach; ?>

            <?php endif; ?>

            </tbody>

        </table>

    </div>

    <!-- ALERTAS -->

    <div class="section">

        <h2>Alertas de Inventario</h2>

        <?php if ($stockCritico > 0 || $agotados > 0): ?>

            <?php if ($stockCritico > 0): ?>
                <p class="inventory-warning">
                    Hay <?= $stockCritico ?>
                    producto(s) con stock crítico.
                </p>
            <?php endif; ?>

            <?php if ($agotados > 0): ?>
                <p class="inventory-danger">
                    Hay <?= $agotados ?>
                    producto(s) agotados.
                </p>
            <?php endif; ?>

        <?php else: ?>

            <p class="inventory-ok">
                Todos los productos tienen
                existencias suficientes.
            </p>

        <?php endif; ?>

    </div>

</main>

</body>
</html>
