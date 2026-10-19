
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

// Obtener ID
$id = filter_input(
    INPUT_GET,
    "id",
    FILTER_VALIDATE_INT
);

if (!$id || $id <= 0) {
    header("Location: index.php");
    exit;
}

// Buscar producto
$stmt = $conexion->prepare(
    "SELECT * FROM productos WHERE id = :id"
);

$stmt->execute(["id" => $id]);

$producto = $stmt->fetch();

if (!$producto) {
    http_response_code(404);
    exit("Producto no encontrado.");
}

$error = "";

// Procesar formulario
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $token = $_POST["csrf_token"] ?? "";

    $nombre = trim($_POST["nombre"] ?? "");
    $descripcion = trim($_POST["descripcion"] ?? "");
    $precio = trim($_POST["precio"] ?? "");
    $stock_minimo = trim($_POST["stock_minimo"] ?? "");

    // Mantener valores si hay error
    $producto["nombre"] = $nombre;
    $producto["descripcion"] = $descripcion;
    $producto["precio"] = $precio;
    $producto["stock_minimo"] = $stock_minimo;

    if (!hash_equals($_SESSION["csrf_token"], $token)) {

        $error = "Solicitud no válida.";

    } elseif (
        $nombre === "" ||
        $precio === "" ||
        $stock_minimo === ""
    ) {

        $error = "Completa todos los campos obligatorios.";

    } elseif (strlen($nombre) > 150) {

        $error = "El nombre no puede superar 150 caracteres.";

    } elseif (
        !is_numeric($precio) ||
        !is_finite((float)$precio) ||
        (float)$precio < 0 ||
        (float)$precio > 99999999.99
    ) {

        $error = "Ingresa un precio válido.";

    } elseif (
        filter_var(
            $stock_minimo,
            FILTER_VALIDATE_INT,
            ["options" => ["min_range" => 0]]
        ) === false
    ) {

        $error = "El stock mínimo debe ser un entero mayor o igual a cero.";

    } else {

        try {

            $sql = "UPDATE productos
                    SET nombre = :nombre,
                        descripcion = :descripcion,
                        precio = :precio,
                        stock_minimo = :stock_minimo
                    WHERE id = :id";

            $stmt = $conexion->prepare($sql);

            $stmt->execute([
                "nombre" => $nombre,
                "descripcion" => $descripcion,
                "precio" => $precio,
                "stock_minimo" => $stock_minimo,
                "id" => $id
            ]);

            header("Location: index.php");
            exit;

        } catch (PDOException $e) {

            error_log($e->getMessage());

            $error = "No se pudo actualizar el producto.";
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

    <title>Editar Producto | TechSolutions</title>

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
            <h1>Editar Producto</h1>
            <p>Actualiza la información del producto</p>
        </div>
    </div>

    <div class="section">

        <?php if ($error !== ""): ?>

            <div class="error-message">
                <?= escapar($error) ?>
            </div>

        <?php endif; ?>

        <form
            method="POST"
            action="editar.php?id=<?= (int)$id ?>"
            class="product-form"
        >

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
                    value="<?= escapar($producto["nombre"]) ?>"
                    required
                >

            </div>

            <div class="form-field">

                <label>Descripción</label>

                <textarea
                    name="descripcion"
                    rows="4"
                ><?= escapar($producto["descripcion"]) ?></textarea>

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
                        value="<?= escapar($producto["precio"]) ?>"
                        required
                    >

                </div>

                <div class="form-field">

                    <label>Stock actual</label>

                    <input
                        type="number"
                        value="<?= escapar($producto["stock"]) ?>"
                        disabled
                    >

                </div>

                <div class="form-field">

                    <label>Stock mínimo *</label>

                    <input
                        type="number"
                        name="stock_minimo"
                        min="0"
                        step="1"
                        value="<?= escapar($producto["stock_minimo"]) ?>"
                        required
                    >

                </div>

            </div>

            <p class="stock-note">
                El stock actual se modificará desde
                el módulo de Inventario.
            </p>

            <div class="form-actions">

                <a href="index.php" class="btn-clear">
                    Cancelar
                </a>

                <button type="submit" class="btn-add">
                    Guardar cambios
                </button>

            </div>

        </form>

    </div>

</main>

</body>
</html>
