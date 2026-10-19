
<?php

session_start();

// Verificar sesión
if (!isset($_SESSION["usuario_id"])) {
    header("Location: ../index.php");
    exit;
}

require_once __DIR__ . "/../config/database.php";

// Solo aceptar solicitudes POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: crear.php");
    exit;
}

// Validar token de seguridad
$token = $_POST["csrf_token"] ?? "";

if (
    !is_string($token) ||
    empty($_SESSION["csrf_token"]) ||
    !hash_equals($_SESSION["csrf_token"], $token)
) {
    http_response_code(403);
    exit("Solicitud no autorizada.");
}

$cliente = trim($_POST["cliente"] ?? "");
$productos = $_POST["producto_id"] ?? [];
$cantidades = $_POST["cantidad"] ?? [];

try {

    // Validar nombre del cliente
    if ($cliente === "" || mb_strlen($cliente) > 150) {
        throw new Exception("Ingresa un nombre de cliente válido.");
    }

    // Validar productos
    if (
        !is_array($productos) ||
        !is_array($cantidades) ||
        count($productos) === 0 ||
        count($productos) !== count($cantidades)
    ) {
        throw new Exception("Debes seleccionar productos válidos.");
    }

    // Limitar la cantidad de filas enviadas
if (count($productos) > 20) {
    throw new Exception(
        "Solo puedes agregar hasta 20 productos por pedido."
    );
}

    // Agrupar cantidades de productos repetidos
    $items = [];

    foreach ($productos as $indice => $productoId) {

        $id = filter_var(
            $productoId,
            FILTER_VALIDATE_INT
        );

        $cantidad = filter_var(
            $cantidades[$indice],
            FILTER_VALIDATE_INT
        );

        if (
    !$id ||
    $id <= 0 ||
    !$cantidad ||
    $cantidad <= 0 ||
    $cantidad > 1000000
) {
    throw new Exception(
        "La cantidad debe estar entre 1 y 1,000,000."
    );
}

        if (!isset($items[$id])) {
            $items[$id] = 0;
        }

        $items[$id] += $cantidad;
    }

    // Ordenar IDs para mantener un orden de bloqueo estable
    ksort($items, SORT_NUMERIC);

    // Iniciar transacción
    $conexion->beginTransaction();

    $detalles = [];
    $totalCentimos = 0;

    // Consultar productos reales en MySQL
    $consultaProducto = $conexion->prepare(
        "SELECT id, nombre, precio, stock
         FROM productos
         WHERE id = :id
         FOR UPDATE"
    );

    foreach ($items as $id => $cantidad) {

        $consultaProducto->execute([
            "id" => $id
        ]);

        $producto = $consultaProducto->fetch(PDO::FETCH_ASSOC);
        $consultaProducto->closeCursor();

        if (!$producto) {
            throw new Exception("Uno de los productos no existe.");
        }

        if ($cantidad > (int)$producto["stock"]) {
            throw new Exception(
                "Stock insuficiente para: " . $producto["nombre"]
            );
        }

        // Trabajar en céntimos para evitar errores de redondeo
        $precioTexto = (string)$producto["precio"];

        if (!preg_match('/^\d+(\.\d{1,2})?$/', $precioTexto)) {
            throw new Exception("Precio de producto no válido.");
        }

        $partes = explode(".", $precioTexto);
        $precioCentimos =
            (int)$partes[0] * 100 +
            (int)str_pad($partes[1] ?? "0", 2, "0");

        $subtotalCentimos = $precioCentimos * $cantidad;

// Validar subtotal individual
if ($subtotalCentimos > 9999999999) {
    throw new Exception(
        "El subtotal de un producto supera el máximo permitido."
    );
}

$totalCentimos += $subtotalCentimos;

// Validar total del pedido
if ($totalCentimos > 9999999999) {
    throw new Exception(
        "El importe del pedido supera el máximo permitido."
    );
}

// Límite de DECIMAL(10,2): S/ 99,999,999.99
if ($totalCentimos > 9999999999) {
    throw new Exception(
        "El importe del pedido supera el máximo permitido."
    );
}

        $detalles[] = [
            "producto_id" => $id,
            "cantidad" => $cantidad,
            "precio" => number_format(
                $precioCentimos / 100, 2, ".", ""
            ),
            "subtotal" => number_format(
                $subtotalCentimos / 100, 2, ".", ""
            )
        ];
    }

    $total = number_format(
        $totalCentimos / 100, 2, ".", ""
    );

    // Registrar pedido
    $stmt = $conexion->prepare(
        "INSERT INTO pedidos (cliente, total, estado)
         VALUES (:cliente, :total, 'Registrado')"
    );

    $stmt->execute([
        "cliente" => $cliente,
        "total" => $total
    ]);

    $pedidoId = $conexion->lastInsertId();

    // Preparar consultas
    $insertarDetalle = $conexion->prepare(
        "INSERT INTO detalle_pedidos
         (pedido_id, producto_id, cantidad,
          precio_unitario, subtotal)
         VALUES
         (:pedido_id, :producto_id, :cantidad,
          :precio_unitario, :subtotal)"
    );

    $actualizarStock = $conexion->prepare(
        "UPDATE productos
         SET stock = stock - :cantidad
         WHERE id = :producto_id
         AND stock >= :cantidad"
    );

    $registrarMovimiento = $conexion->prepare(
        "INSERT INTO movimientos_inventario
         (producto_id, tipo, cantidad)
         VALUES (:producto_id, 'salida', :cantidad)"
    );

    // Guardar detalles y descontar stock
    foreach ($detalles as $detalle) {

        $insertarDetalle->execute([
            "pedido_id" => $pedidoId,
            "producto_id" => $detalle["producto_id"],
            "cantidad" => $detalle["cantidad"],
            "precio_unitario" => $detalle["precio"],
            "subtotal" => $detalle["subtotal"]
        ]);

        $actualizarStock->execute([
            "cantidad" => $detalle["cantidad"],
            "producto_id" => $detalle["producto_id"]
        ]);

        if ($actualizarStock->rowCount() !== 1) {
            throw new Exception("No se pudo actualizar el stock.");
        }

        $registrarMovimiento->execute([
            "producto_id" => $detalle["producto_id"],
            "cantidad" => $detalle["cantidad"]
        ]);
    }

    // Confirmar todos los cambios
    $conexion->commit();

    header(
        "Location: index.php?guardado=1"
    );
    exit;

} catch (Throwable $e) {

    if ($conexion->inTransaction()) {
        $conexion->rollBack();
    }

    error_log("Error al guardar pedido: " . $e->getMessage());

    if ($e instanceof PDOException) {
        $mensaje = "Error al registrar el pedido en la base de datos.";
    } else {
        $mensaje = $e->getMessage();
    }

    $_SESSION["error_pedido"] = $mensaje;

    header("Location: crear.php");
    exit;
}
?>
