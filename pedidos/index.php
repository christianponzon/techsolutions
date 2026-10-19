
<?php

session_start();

// Verificar sesión
if (!isset($_SESSION["usuario_id"])) {
    header("Location: ../index.php");
    exit;
}

require_once __DIR__ . "/../config/database.php";

// Proteger datos mostrados en HTML
function escapar($valor) {
    return htmlspecialchars(
        (string)$valor,
        ENT_QUOTES,
        "UTF-8"
    );
}

// Obtener búsqueda
$buscar = trim($_GET["buscar"] ?? "");

// Consultar pedidos
$sql = "SELECT
            id,
            cliente,
            fecha,
            total,
            estado
        FROM pedidos";

if ($buscar !== "") {

    $sql .= " WHERE cliente LIKE :buscar
              ORDER BY fecha DESC, id DESC";

    $stmt = $conexion->prepare($sql);

    $stmt->execute([
        "buscar" => "%" . $buscar . "%"
    ]);

} else {

    $sql .= " ORDER BY fecha DESC, id DESC";

    $stmt = $conexion->query($sql);
}

$pedidos = $stmt->fetchAll();

// Estadísticas
$totalPedidos = (int)$conexion->query(
    "SELECT COUNT(*) FROM pedidos"
)->fetchColumn();

$totalVentas = (float)$conexion->query(
    "SELECT COALESCE(SUM(total), 0)
     FROM pedidos
     WHERE estado = 'Registrado'"
)->fetchColumn();

$pedidosHoy = (int)$conexion->query(
    "SELECT COUNT(*)
     FROM pedidos
     WHERE DATE(fecha) = CURDATE()"
)->fetchColumn();

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Pedidos | TechSolutions</title>

    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="../css/pedidos.css">

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

    <a href="../inventario/index.php">
        Inventario
    </a>

    <a href="index.php" class="active">
        Pedidos
    </a>

    <a href="../logout.php">
        Cerrar sesión
    </a>

</aside>

<!-- CONTENIDO PRINCIPAL -->

<main class="main">

    <div class="header">

        <div>
            <h1>Gestión de Pedidos</h1>
            <p>Administra los pedidos de TechSolutions</p>
        </div>

        <div class="user">
            <?= escapar($_SESSION["usuario_nombre"]) ?>
        </div>

    </div>

<?php if (($_GET["guardado"] ?? "") === "1"): ?>

    <div class="order-success">
        Pedido registrado correctamente.
        El inventario se actualizó automáticamente.
    </div>

<?php endif; ?>

    <!-- ESTADÍSTICAS -->

    <div class="cards">

        <div class="card">
            <h3>Total de pedidos</h3>

            <div class="number">
                <?= $totalPedidos ?>
            </div>
        </div>

        <div class="card">
            <h3>Ventas registradas</h3>

            <div class="number">
                S/ <?= number_format($totalVentas, 2) ?>
            </div>
        </div>

        <div class="card">
            <h3>Pedidos de hoy</h3>

            <div class="number">
                <?= $pedidosHoy ?>
            </div>
        </div>

    </div>

    <!-- BUSCADOR Y BOTONES -->

    <div class="orders-toolbar">

        <form
            action="index.php"
            method="GET"
            class="orders-search"
        >

            <input
                type="text"
                name="buscar"
                placeholder="Buscar cliente..."
                value="<?= escapar($buscar) ?>"
            >

            <button type="submit" class="btn-order-search">
                Buscar
            </button>

            <a href="index.php" class="btn-order-clear">
                Limpiar
            </a>

        </form>

        <a href="crear.php" class="btn-new-order">
            + Nuevo pedido
        </a>

    </div>

    <!-- TABLA DE PEDIDOS -->

    <div class="section">

        <h2>Listado de pedidos</h2>

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
                        No se encontraron pedidos.
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
                            <?= escapar(
                                date(
                                    "d/m/Y H:i",
                                    strtotime($pedido["fecha"])
                                )
                            ) ?>
                        </td>

                        <td>
                            S/ <?= number_format(
                                $pedido["total"], 2
                            ) ?>
                        </td>

                        <td>
                            <span class="badge success">
                                <?= escapar($pedido["estado"]) ?>
                            </span>
                        </td>

                        <td>

                            <a
                                href="detalle.php?id=<?= (int)$pedido['id'] ?>"
                                class="btn-view-order"
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

</main>

</body>
</html>
