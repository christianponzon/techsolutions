
<?php

session_start();

if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}


// Verificar que el usuario inició sesión
if (!isset($_SESSION["usuario_id"])) {
    header("Location: ../index.php");
    exit;
}

// Conexión a MySQL
require_once __DIR__ . "/../config/database.php";

// Función para proteger la salida HTML
function escapar($valor) {
    return htmlspecialchars(
        (string)$valor,
        ENT_QUOTES,
        "UTF-8"
    );
}

// Obtener búsqueda
$buscar = trim($_GET["buscar"] ?? "");

// Consultar productos
if ($buscar !== "") {

    $sql = "SELECT * FROM productos
            WHERE nombre LIKE :buscar
            ORDER BY id DESC";

    $stmt = $conexion->prepare($sql);

    $stmt->execute([
        "buscar" => "%" . $buscar . "%"
    ]);

} else {

    $sql = "SELECT * FROM productos
            ORDER BY id DESC";

    $stmt = $conexion->query($sql);
}

$productos = $stmt->fetchAll();

$nombreUsuario = escapar(
    $_SESSION["usuario_nombre"]
);

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Productos | TechSolutions</title>

    <link rel="stylesheet"
          href="../css/dashboard.css">

    <link rel="stylesheet"
          href="../css/productos.css">
</head>

<body>

<!-- MENÚ LATERAL -->

<aside class="sidebar">

    <h2>TechSolutions</h2>

    <p>Inventarios y Pedidos</p>

    <a href="../dashboard.php">
        Inicio
    </a>

    <a href="index.php" class="active">
        Productos
    </a>

    <a href="../inventario/index.php">
        Inventario
    </a>

    <a href="../pedidos/index.php">
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
            <h1>Gestión de Productos</h1>
            <p>Administra los productos de TechSolutions</p>
        </div>

        <div class="user">
            <?= $nombreUsuario ?>
        </div>

    </div>


    <!-- BUSCADOR Y BOTÓN -->

    <div class="toolbar">

        <form action="index.php"
              method="GET"
              class="search-form">

            <input
                type="text"
                name="buscar"
                placeholder="Buscar producto..."
                value="<?= escapar($buscar) ?>"
            >

            <button type="submit" class="btn-search">
                Buscar
            </button>

            <a href="index.php" class="btn-clear">
                Limpiar
            </a>

        </form>

        <a href="crear.php" class="btn-add">
            + Nuevo producto
        </a>

    </div>

    
<?php if (isset($_GET["eliminado"])): ?>

    <div class="alert-success">
        Producto eliminado correctamente.
    </div>

<?php endif; ?>

<?php if (isset($_GET["error"])): ?>

    <div class="alert-error">

        <?php
        switch ($_GET["error"]) {

            case "pedidos":
                echo "No se puede eliminar un producto con pedidos asociados.";
                break;
            
            case "movimientos":
                echo "No se puede eliminar este producto porque tiene movimientos de inventario registrados.";
                break;

            case "noexiste":
                echo "El producto no existe.";
                break;

            case "id":
                echo "El identificador del producto no es válido.";
                break;

            default:
                echo "No se pudo eliminar el producto.";
        }
        ?>

    </div>

<?php endif; ?>


    <!-- TABLA DE PRODUCTOS -->

    <div class="section">

        <h2>Listado de productos</h2>

        <table>

            <thead>
                <tr>
                    <th>ID</th>
                    <th>Producto</th>
                    <th>Descripción</th>
                    <th>Precio</th>
                    <th>Stock</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>

            <tbody>

            <?php if (empty($productos)): ?>

                <tr>
                    <td colspan="7" class="empty">
                        No se encontraron productos.
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
                        $estado = "Stock bajo";
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
                            <?= escapar($producto["descripcion"]) ?>
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
                            <span class="badge <?= $clase ?>">
                                <?= $estado ?>
                            </span>
                        </td>

                        <td>

                            <div class="actions">

                                <a
                                    href="editar.php?id=<?= (int)$producto['id'] ?>"
                                    class="btn-edit">
                                    Editar
                                </a>

                                
<form
    action="eliminar.php"
    method="POST"
    onsubmit="return confirm('¿Estás seguro de eliminar este producto?');"
>

    <input
        type="hidden"
        name="id"
        value="<?= (int)$producto['id'] ?>"
    >

    <input
        type="hidden"
        name="csrf_token"
        value="<?= escapar($_SESSION['csrf_token']) ?>"
    >

    <button type="submit" class="btn-delete">
        Eliminar
    </button>

</form>

                            </div>

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
