
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

// Consultar historial de movimientos
$sql = "SELECT
            m.id,
            p.nombre AS producto,
            m.tipo,
            m.cantidad,
            m.fecha
        FROM movimientos_inventario m
        INNER JOIN productos p
            ON m.producto_id = p.id
        ORDER BY m.fecha DESC, m.id DESC";

$stmt = $conexion->query($sql);
$movimientos = $stmt->fetchAll();

// Estadísticas
$totalMovimientos = count($movimientos);
$totalEntradas = 0;
$totalSalidas = 0;

foreach ($movimientos as $movimiento) {

    if ($movimiento["tipo"] === "entrada") {
        $totalEntradas += (int)$movimiento["cantidad"];
    } else {
        $totalSalidas += (int)$movimiento["cantidad"];
    }
}

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Historial de Inventario | TechSolutions</title>

    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="../css/inventario.css">

</head>

<body>

<aside class="sidebar">

    <h2>TechSolutions</h2>
    <p>Inventarios y Pedidos</p>

    <a href="../dashboard.php">Inicio</a>

    <a href="../productos/index.php">Productos</a>

    <a href="index.php" class="active">Inventario</a>

    <a href="../pedidos/index.php">Pedidos</a>

    <a href="../logout.php">Cerrar sesión</a>

</aside>

<main class="main">

    <div class="header">
        <div>
            <h1>Historial de Inventario</h1>
            <p>Registro de entradas y salidas de productos</p>
        </div>

        <div class="user">
            <?= escapar($_SESSION["usuario_nombre"]) ?>
        </div>
    </div>

    <!-- ESTADÍSTICAS -->

    <div class="cards">

        <div class="card">
            <h3>Total de movimientos</h3>
            <div class="number">
                <?= $totalMovimientos ?>
            </div>
        </div>

        <div class="card">
            <h3>Unidades ingresadas</h3>
            <div class="number">
                <?= $totalEntradas ?>
            </div>
        </div>

        <div class="card">
            <h3>Unidades retiradas</h3>
            <div class="number">
                <?= $totalSalidas ?>
            </div>
        </div>

    </div>

    <!-- TABLA DEL HISTORIAL -->

    <div class="section">

        <div class="inventory-toolbar">

            <h2>Movimientos registrados</h2>

            <a href="index.php" class="btn-history">
                Volver al inventario
            </a>

        </div>

        <table>

            <thead>
                <tr>
                    <th>ID</th>
                    <th>Producto</th>
                    <th>Tipo</th>
                    <th>Cantidad</th>
                    <th>Fecha</th>
                </tr>
            </thead>

            <tbody>

            <?php if (empty($movimientos)): ?>

                <tr>
                    <td colspan="5" class="empty">
                        No hay movimientos registrados.
                    </td>
                </tr>

            <?php else: ?>

                <?php foreach ($movimientos as $movimiento): ?>

                    <tr>

                        <td>
                            <?= escapar($movimiento["id"]) ?>
                        </td>

                        <td>
                            <?= escapar($movimiento["producto"]) ?>
                        </td>

                        <td>

                            <?php if ($movimiento["tipo"] === "entrada"): ?>

                                <span class="badge success">
                                    Entrada
                                </span>

                            <?php else: ?>

                                <span class="badge danger">
                                    Salida
                                </span>

                            <?php endif; ?>

                        </td>

                        <td>
                            <?= $movimiento["tipo"] === "entrada"
                                ? "+" : "-" ?>
                            <?= (int)$movimiento["cantidad"] ?>
                        </td>

                        <td>
                            <?= escapar(
                                date(
                                    "d/m/Y H:i",
                                    strtotime($movimiento["fecha"])
                                )
                            ) ?>
                        </td>

                    </tr>

                <?php endforeach; ?>

            <?php endif; ?>

            </tbody>

        </table>

    </div>

</main>

</body>
</html>
