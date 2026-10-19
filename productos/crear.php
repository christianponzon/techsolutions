
<?php

session_start();

if (!isset($_SESSION["usuario_id"])) {
    header("Location: ../index.php");
    exit;
}

require_once __DIR__ . "/../config/database.php";

// Crear token de seguridad del formulario
if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

function escapar($valor) {
    return htmlspecialchars(
        (string)$valor,
        ENT_QUOTES,
        "UTF-8"
    );
}

$error = "";
$nombre = "";
$descripcion = "";
$precio = "";
$stock = "";
$stock_minimo = "5";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nombre = trim($_POST["nombre"] ?? "");
    $descripcion = trim($_POST["descripcion"] ?? "");
    $precio = trim($_POST["precio"] ?? "");
    $stock = trim($_POST["stock"] ?? "");
    $stock_minimo = trim($_POST["stock_minimo"] ?? "");

    $token = $_POST["csrf_token"] ?? "";

    if (!hash_equals($_SESSION["csrf_token"], $token)) {

        $error = "Solicitud no válida. Recarga la página.";

    } elseif (
        $nombre === "" ||
        $precio === "" ||
        $stock === "" ||
        $stock_minimo === ""
    ) {

        $error = "Completa todos los campos obligatorios.";

    } elseif (strlen($nombre) > 150) {

        $error = "El nombre es demasiado largo.";

    } elseif (
        !is_numeric($precio) ||
        !is_finite((float)$precio) ||
        (float)$precio < 0 ||
        (float)$precio > 99999999.99
    ) {

        $error = "Ingresa un precio válido.";

    } elseif (
        filter_var(
            $stock,
            FILTER_VALIDATE_INT,
            ["options" => ["min_range" => 0]]
        ) === false
    ) {

        $error = "El stock debe ser un número entero positivo o cero.";

    } elseif (
        filter_var(
            $stock_minimo,
            FILTER_VALIDATE_INT,
            ["options" => ["min_range" => 0]]
        ) === false
    ) {

        $error = "El stock mínimo debe ser válido.";

    } else {

        try {
            $conexion->beginTransaction();

            $sql = "INSERT INTO productos
                    (nombre, descripcion, precio, stock, stock_minimo)
                    VALUES
                    (:nombre, :descripcion, :precio, :stock, :stock_minimo)";

            $stmt = $conexion->prepare($sql);

            $stmt->execute([
                "nombre" => $nombre,
                "descripcion" => $descripcion,
                "precio" => $precio,
                "stock" => $stock,
                "stock_minimo" => $stock_minimo
            ]);

            // Registrar stock inicial como entrada
            $productoId = $conexion->lastInsertId();

            if ((int)$stock > 0) {

                $movimiento = $conexion->prepare(
                    "INSERT INTO movimientos_inventario
                    (producto_id, tipo, cantidad)
                    VALUES (:producto_id, 'entrada', :cantidad)"
                );

                $movimiento->execute([
                    "producto_id" => $productoId,
                    "cantidad" => $stock
                ]);
            }
            $conexion->commit();

            header("Location: index.php");
            exit;

        } catch (PDOException $e) {

            if ($conexion->inTransaction()) {
            $conexion->rollBack();
        }
            error_log($e->getMessage());

            $error = "No se pudo registrar el producto.";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Nuevo producto | TechSolutions</title>

    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="../css/productos.css">

</head>

<body>

<aside class="sidebar">

    <h2>TechSolutions</h2>
    <p>Inventarios y Pedidos</p>

    <a href="../dashboard.php">Inicio</a>

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

<main class="main">

    <div class="header">

        <div>
            <h1>Registrar Producto</h1>
            <p>Agrega un nuevo producto al inventario</p>
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

                <label>Nombre del producto *</label>

                <input
                    type="text"
                    name="nombre"
                    maxlength="150"
                    value="<?= escapar($nombre) ?>"
                    placeholder="Ejemplo: Laptop Lenovo"
                    required
                >

            </div>

            <div class="form-field">

                <label>Descripción</label>

                <textarea
                    name="descripcion"
                    rows="4"
                    placeholder="Descripción del producto"
                ><?= escapar($descripcion) ?></textarea>

            </div>

            <div class="form-grid">

                <div class="form-field">

                    <label>Precio (S/) *</label>

                    <input
                        type="number"
                        name="precio"
                        min="0"
                        max="99999999.99"
                        step="0.01"
                        value="<?= escapar($precio) ?>"
                        required
                    >

                </div>

                <div class="form-field">

                    <label>Stock inicial *</label>

                    <input
                        type="number"
                        name="stock"
                        min="0"
                        step="1"
                        value="<?= escapar($stock) ?>"
                        required
                    >

                </div>

                <div class="form-field">

                    <label>Stock mínimo *</label>

                    <input
                        type="number"
                        name="stock_minimo"
                        min="0"
                        step="1"
                        value="<?= escapar($stock_minimo) ?>"
                        required
                    >

                </div>

            </div>

            <div class="form-actions">

                <a href="index.php" class="btn-clear">
                    Cancelar
                </a>

                <button type="submit" class="btn-add">
                    Guardar producto
                </button>

            </div>

        </form>

    </div>

</main>

</body>
</html>
