
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

// Consultar productos disponibles
$stmt = $conexion->query(
    "SELECT id, nombre, precio, stock
     FROM productos
     WHERE stock > 0
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

    <title>Nuevo Pedido | TechSolutions</title>

    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="../css/productos.css">
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
            <h1>Registrar Nuevo Pedido</h1>
            <p>Selecciona los productos que comprará el cliente</p>
        </div>
    </div>

    <div class="section">

        
<?php if (isset($_SESSION["error_pedido"])): ?>

    <div class="error-message">
        <?= escapar($_SESSION["error_pedido"]) ?>
    </div>

    <?php unset($_SESSION["error_pedido"]); ?>

<?php endif; ?>


        <form action="guardar.php" method="POST"
              id="pedidoForm" class="product-form">

            <input type="hidden"
                   name="csrf_token"
                   value="<?= escapar($_SESSION["csrf_token"]) ?>">

            <div class="form-field">
                <label>Nombre del cliente *</label>

                <input type="text"
                       name="cliente"
                       maxlength="150"
                       placeholder="Ejemplo: Juan Pérez"
                       required>
            </div>

            <h2>Productos del pedido</h2>

            <div id="productos-container">

                <div class="pedido-producto">

                    <div class="form-field">
                        <label>Producto</label>

                        <select name="producto_id[]"
                                class="producto-select"
                                required>

                            <option value="">
                                Seleccione un producto
                            </option>

                            <?php foreach ($productos as $producto): ?>

                                <option
                                    value="<?= (int)$producto["id"] ?>"
                                    data-precio="<?= escapar($producto["precio"]) ?>"
                                    data-stock="<?= (int)$producto["stock"] ?>"
                                >
                                    <?= escapar($producto["nombre"]) ?>
                                    - S/ <?= number_format(
                                        $producto["precio"], 2
                                    ) ?>
                                    (Stock: <?= (int)$producto["stock"] ?>)
                                </option>

                            <?php endforeach; ?>

                        </select>
                    </div>

                    <div class="form-field">
                        <label>Cantidad</label>

                        <input type="number"
                               name="cantidad[]"
                               class="cantidad-input"
                               min="1"
                               value="1"
                               required>
                    </div>

                    <div class="form-field">
                        <label>Subtotal</label>

                        <input type="text"
                               class="subtotal-input"
                               value="S/ 0.00"
                               readonly>
                    </div>

                    <button type="button"
                            class="btn-remove-product">
                        Quitar
                    </button>

                </div>

            </div>

            <button type="button"
                    id="agregarProducto"
                    class="btn-order-search">
                + Agregar otro producto
            </button>

            <div class="pedido-total">
                <h2>Total del pedido</h2>
                <h1 id="totalPedido">S/ 0.00</h1>
            </div>

            <div class="form-actions">

                <a href="index.php" class="btn-order-clear">
                    Cancelar
                </a>

                <button type="submit" class="btn-new-order"
                    <?= empty($productos) ? "disabled" : "" ?>>
                    Guardar pedido
                </button>

            </div>

            <?php if (empty($productos)): ?>
                <p class="error-message">
                    No hay productos con stock disponible.
                </p>
            <?php endif; ?>

        </form>

    </div>

</main>

<script>

const container = document.getElementById("productos-container");
const agregar = document.getElementById("agregarProducto");
const totalElemento = document.getElementById("totalPedido");
const formulario = document.getElementById("pedidoForm");

function calcularTotal() {

    let total = 0;

    document.querySelectorAll(".pedido-producto")
        .forEach(fila => {

            const select = fila.querySelector(".producto-select");
            const cantidadInput = fila.querySelector(".cantidad-input");
            const subtotalInput = fila.querySelector(".subtotal-input");

            const opcion = select.options[select.selectedIndex];

            const precio = Number(opcion?.dataset.precio || 0);
            const cantidad = Number(cantidadInput.value || 0);

            const subtotal = precio * cantidad;

            subtotalInput.value = "S/ " + subtotal.toFixed(2);
            total += subtotal;

        });

    totalElemento.textContent = "S/ " + total.toFixed(2);
}

const MAX_PRODUCTOS = 20;

agregar.addEventListener("click", () => {

    const filas = container.querySelectorAll(".pedido-producto");

    if (filas.length >= MAX_PRODUCTOS) {
        alert("Solo puedes agregar hasta 20 productos por pedido.");
        return;
    }

    const primeraFila = container.querySelector(".pedido-producto");

    const nuevaFila = primeraFila.cloneNode(true);

    nuevaFila.querySelector(".producto-select").value = "";
    nuevaFila.querySelector(".cantidad-input").value = 1;
    nuevaFila.querySelector(".subtotal-input").value = "S/ 0.00";

    container.appendChild(nuevaFila);

    calcularTotal();
});

container.addEventListener("input", calcularTotal);
container.addEventListener("change", calcularTotal);

container.addEventListener("click", (event) => {

    if (event.target.classList.contains("btn-remove-product")) {

        const filas = container.querySelectorAll(".pedido-producto");

        if (filas.length > 1) {
            event.target.closest(".pedido-producto").remove();
            calcularTotal();
        }
    }
});

formulario.addEventListener("submit", (event) => {

    const cantidadesPorProducto = new Map();
    let valido = true;

    document.querySelectorAll(".pedido-producto")
        .forEach(fila => {

            const select = fila.querySelector(".producto-select");
            const cantidad = Number(
                fila.querySelector(".cantidad-input").value
            );

            const opcion = select.options[select.selectedIndex];
            const stock = Number(opcion?.dataset.stock || 0);
            const id = select.value;

            if (!id || !Number.isInteger(cantidad) || cantidad <= 0) {
                valido = false;
                return;
            }

            const acumulado =
                (cantidadesPorProducto.get(id) || 0) + cantidad;

            cantidadesPorProducto.set(id, acumulado);

            if (acumulado > stock) {
                valido = false;
            }
        });

    if (!valido) {
        event.preventDefault();
        alert("Revisa las cantidades. No puedes superar el stock disponible.");
    }
});

calcularTotal();

</script>

</body>
</html>
