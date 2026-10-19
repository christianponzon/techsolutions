
<?php

session_start();

// Verificar sesión
if (!isset($_SESSION["usuario_id"])) {
    header("Location: index.php");
    exit;
}

require_once __DIR__ . "/config/database.php";

// Función para proteger datos HTML
function escapar($valor) {
    return htmlspecialchars(
        (string)$valor,
        ENT_QUOTES,
        "UTF-8"
    );
}

// ==========================================
// ESTADÍSTICAS GENERALES
// ==========================================

// Total de productos
$totalProductos = (int)$conexion->query(
    "SELECT COUNT(*) FROM productos"
)->fetchColumn();

// Total de pedidos
$totalPedidos = (int)$conexion->query(
    "SELECT COUNT(*) FROM pedidos"
)->fetchColumn();

// Ventas acumuladas
$totalVentas = (float)$conexion->query(
    "SELECT COALESCE(SUM(total), 0)
     FROM pedidos
     WHERE estado = 'Registrado'"
)->fetchColumn();

// Unidades disponibles
$totalUnidades = (int)$conexion->query(
    "SELECT COALESCE(SUM(stock), 0)
     FROM productos"
)->fetchColumn();

// Productos con stock crítico
// No incluye productos agotados
$stockBajo = (int)$conexion->query(
    "SELECT COUNT(*)
     FROM productos
     WHERE stock > 0
     AND stock <= stock_minimo"
)->fetchColumn();

// Productos agotados
$agotados = (int)$conexion->query(
    "SELECT COUNT(*)
     FROM productos
     WHERE stock = 0"
)->fetchColumn();

// Pedidos de hoy
$pedidosHoy = (int)$conexion->query(
    "SELECT COUNT(*)
     FROM pedidos
     WHERE DATE(fecha) = CURDATE()"
)->fetchColumn();

// ==========================================
// PRODUCTOS RECIENTES
// ==========================================

$stmt = $conexion->query(
    "SELECT id, nombre, precio, stock, stock_minimo
     FROM productos
     ORDER BY id DESC
     LIMIT 5"
);

$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ==========================================
// ÚLTIMOS PEDIDOS
// ==========================================

$stmt = $conexion->query(
    "SELECT id, cliente, fecha, total, estado
     FROM pedidos
     ORDER BY fecha DESC, id DESC
     LIMIT 5"
);

$pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ==========================================
// PRODUCTOS CON ALERTAS
// ==========================================

$stmt = $conexion->query(
    "SELECT id, nombre, stock, stock_minimo
     FROM productos
     WHERE stock <= stock_minimo
     ORDER BY stock ASC, nombre ASC"
);

$alertas = $stmt->fetchAll(PDO::FETCH_ASSOC);

$nombre = escapar(
    $_SESSION["usuario_nombre"] ?? "Administrador"
);

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Dashboard | TechSolutions</title>

    <link rel="stylesheet" href="css/dashboard.css">

</head>

<body>

<!-- =====================================
     MENÚ LATERAL
===================================== -->

<aside class="sidebar">

    <h2>TechSolutions</h2>

    <p>Inventarios y Pedidos</p>

    <a href="dashboard.php" class="active">
        Inicio
    </a>

    <a href="productos/index.php">
        Productos
    </a>

    <a href="inventario/index.php">
        Inventario
    </a>

    <a href="pedidos/index.php">
        Pedidos
    </a>

    <a href="logout.php">
        Cerrar sesión
    </a>

</aside>

<!-- =====================================
     CONTENIDO PRINCIPAL
===================================== -->

<main class="main">

    <div class="header">

        <div>
            <h1>Panel de Administración</h1>
            <p>Resumen general del sistema TechSolutions</p>
        </div>

        <div class="user">
            Bienvenido, <?= $nombre ?>
        </div>

    </div>

    <!-- =====================================
         TARJETAS DE ESTADÍSTICAS
    ===================================== -->

    <div class="cards">

        <div class="card">

            <h3>Total de productos</h3>

            <div class="number">
                <?= $totalProductos ?>
            </div>

        </div>

        <div class="card">

            <h3>Total de pedidos</h3>

            <div class="number">
                <?= $totalPedidos ?>
            </div>

        </div>

        <div class="card">

            <h3>Ventas acumuladas</h3>

            <div class="number">
                S/ <?= number_format($totalVentas, 2) ?>
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
                <?= $stockBajo ?>
            </div>

        </div>

        <div class="card">

            <h3>Productos agotados</h3>

            <div class="number">
                <?= $agotados ?>
            </div>

        </div>

        <div class="card">

            <h3>Pedidos de hoy</h3>

            <div class="number">
                <?= $pedidosHoy ?>
            </div>

        </div>

    </div>

    <!-- =====================================
         PRODUCTOS RECIENTES
    ===================================== -->

    <div class="section">

        <h2>Productos recientes</h2>

        <table>

            <thead>

                <tr>
                    <th>ID</th>
                    <th>Producto</th>
                    <th>Precio</th>
                    <th>Stock</th>
                    <th>Estado</th>
                </tr>

            </thead>

            <tbody>

            <?php if (empty($productos)): ?>

                <tr>
                    <td colspan="5" class="empty">
                        Todavía no hay productos registrados.
                    </td>
                </tr>

            <?php else: ?>

                <?php foreach ($productos as $producto): ?>

                    <?php

                    if ((int)$producto["stock"] === 0) {

                        $estado = "Agotado";
                        $clase = "danger";

                    } elseif (
                        (int)$producto["stock"] <=
                        (int)$producto["stock_minimo"]
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
                            <?= (int)$producto["id"] ?>
                        </td>

                        <td>
                            <?= escapar($producto["nombre"]) ?>
                        </td>

                        <td>
                            S/ <?= number_format(
                                (float)$producto["precio"], 2
                            ) ?>
                        </td>

                        <td>
                            <?= (int)$producto["stock"] ?>
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

    <!-- =====================================
         ÚLTIMOS PEDIDOS
    ===================================== -->

    <div class="section">

        <h2>Últimos pedidos registrados</h2>

        <table>

            <thead>

                <tr>
                    <th>ID</th>
                    <th>Cliente</th>
                    <th>Fecha</th>
                    <th>Total</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>

            </thead>

            <tbody>

            <?php if (empty($pedidos)): ?>

                <tr>
                    <td colspan="6" class="empty">
                        Todavía no hay pedidos registrados.
                    </td>
                </tr>

            <?php else: ?>

                <?php foreach ($pedidos as $pedido): ?>

                    <tr>

                        <td>
                            #<?= (int)$pedido["id"] ?>
                        </td>

                        <td>
                            <?= escapar($pedido["cliente"]) ?>
                        </td>

                        <td>
                            <?= escapar(date(
                                "d/m/Y H:i",
                                strtotime($pedido["fecha"])
                            )) ?>
                        </td>

                        <td>
                            S/ <?= number_format(
                                (float)$pedido["total"], 2
                            ) ?>
                        </td>

                        <td>
                            <span class="badge success">
                                <?= escapar($pedido["estado"]) ?>
                            </span>
                        </td>

                        <td>

                            <a
                                href="pedidos/detalle.php?id=<?= (int)$pedido['id'] ?>"
                                class="dashboard-detail-btn"
                            >
                                Ver detalle
                            </a>

                        </td>

                    </tr>

                <?php endforeach; ?>

            <?php endif; ?>

            </tbody>

        </table>

    </div>

    <!-- =====================================
         ALERTAS DE INVENTARIO
    ===================================== -->

    <div class="section">

        <h2>Alertas de inventario</h2>

        <?php if (empty($alertas)): ?>

            <p class="dashboard-ok">
                Todos los productos tienen existencias
                suficientes actualmente.
            </p>

        <?php else: ?>

            <?php foreach ($alertas as $producto): ?>

                <?php if ((int)$producto["stock"] === 0): ?>

                    <div class="dashboard-danger">

                        Producto agotado:
                        <strong>
                            <?= escapar($producto["nombre"]) ?>
                        </strong>

                    </div>

                <?php else: ?>

                    <div class="dashboard-warning">

                        Stock crítico:
                        <strong>
                            <?= escapar($producto["nombre"]) ?>
                        </strong>

                        — Quedan
                        <?= (int)$producto["stock"] ?>
                        unidades.

                    </div>

                <?php endif; ?>

            <?php endforeach; ?>

        <?php endif; ?>

    </div>

</main>

</body>
</html>
