<?php
session_start();
include("../config/Database.php");
function obtenerProducto($conn, $id_articulo)
{
    $sql = "SELECT * FROM Articulo WHERE ID_Articulo = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_articulo);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    if (!isset($_SESSION['id_Usuario'])) {
        header('Location: login.php');
        exit;
    }

    // Verificar si se recibió el ID del producto
    if (isset($_GET['id'])) {
        $id_articulo = intval($_GET['id']); // Asegúrate de que es un número entero
        $producto = obtenerProducto($conn, $id_articulo); // Obtener los detalles del producto

        if (!$producto) {
            echo "Producto no encontrado.";
            exit;
        }
    } else {
        echo "ID de producto no especificado.";
        exit;
    }
} else {
    function añadirAlcarrito($conn, $id_articulo, $precio, $cantidad, $id_pedido)
    {


        // Convert precio to a float (or double) if it's a string
        $precio_linea = floatval($precio);
        $cantidadInt = intval($cantidad);
        $precio_total = $cantidadInt * $precio_linea;


        // Comprobar si el artículo ya está en la línea de pedido
        $stmt = $conn->prepare("SELECT ID_Linea_Pedido, Cantidad FROM Linea_Pedido WHERE ID_Articulo = ? AND ID_Pedido = ?");
        $stmt->bind_param("ii", $id_articulo, $id_pedido);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // El artículo ya existe, así que actualizamos la cantidad y el total
            $row = $result->fetch_assoc();
            $id_linea_pedido = $row['ID_Linea_Pedido'];
            $cantidad_actual = $row['Cantidad'];

            // Calcular nueva cantidad y precio total
            $nueva_cantidad = $cantidad_actual + $cantidadInt;
            $nuevo_precio_total = $nueva_cantidad * $precio_linea;

            // Actualizar la línea de pedido
            $update_stmt = $conn->prepare("UPDATE Linea_Pedido SET Cantidad = ?, Precio_Linea = ? WHERE ID_Linea_Pedido = ?");
            $update_stmt->bind_param("idi", $nueva_cantidad, $nuevo_precio_total, $id_linea_pedido);
            return $update_stmt->execute();
        } else {
            // El artículo no existe, así que lo insertamos
            $stmt = $conn->prepare("INSERT INTO Linea_Pedido (Cantidad, Precio_Linea, ID_Pedido, ID_Articulo) VALUES (?,?,?,?)");
            $stmt->bind_param("iddi", $cantidadInt, $precio_total, $id_pedido, $id_articulo);
            return $stmt->execute();
        }
    }
    if (isset($_POST["añadirAlcarrito"])) {


        $id_articulo = $_POST['id_articulo'];
        $precio = $_POST['precio'];
        $cantidad = $_POST['cantidad'];

        $id_pedido = 0;
        // Preparar la consulta
        $sql = "SELECT ID_Pedido 
                FROM Pedido 
                WHERE ID_Usuario = ? AND Estado = 'Carrito'";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $_SESSION['id_Usuario']);
        $stmt->execute();
        $result = $stmt->get_result();

        // Comprobar si hay resultados
        if ($result->num_rows > 0) {
            // Obtener el único pedido encontrado
            $row = $result->fetch_assoc();
            $id_pedido = $row['ID_Pedido'];

        } else {

            $total = 0; // Suponemos que el total se calculará más tarde o se pasará en la solicitud

            // Preparar la consulta para insertar el pedido
            $sql = "INSERT INTO Pedido (ID_Usuario, Total, Estado) VALUES (?, ?, 'Carrito')";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("id", $_SESSION['id_Usuario'], $total);

            if ($stmt->execute()) {
                $id_pedido = $conn->insert_id;
                echo "Pedido creado exitosamente. ID del Pedido: " . $conn->insert_id;
            } else {
                echo "Error al crear el pedido: " . $stmt->error;
            }
            echo "No hay pedidos en estado 'Carrito' para este usuario.";

        }

        if (añadirAlcarrito($conn, $id_articulo, $precio, $cantidad, $id_pedido)) {

            echo "Artículo añadido al carrito.";

            $producto_total = floatval($precio) * intval($cantidad);


            $sql = "SELECT Total 
            FROM Pedido 
            WHERE ID_Pedido = ? AND ID_Usuario = ? AND Estado = 'Carrito'";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $id_pedido, $_SESSION['id_Usuario']);
            $stmt->execute();
            $result = $stmt->get_result();

            // Comprobar si hay resultados
            if ($result->num_rows > 0) {
                // Obtener el único pedido encontrado
                $row = $result->fetch_assoc();
                $total_actual = $row['Total'];
            } else {
                // Manejo de error si no se encuentra el pedido
                echo "No se encontró el pedido en estado 'Carrito'.";
                $total_actual = 0; // Asignar 0 si no se encuentra el pedido
            }

            $nuevo_total = $total_actual + $producto_total;

            // Preparar la consulta para actualizar el total del pedido
            $sql = "UPDATE Pedido SET Total = ? WHERE ID_Pedido = ? AND ID_Usuario=? AND Estado='Carrito'";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("dii", $nuevo_total, $id_pedido, $_SESSION['id_Usuario']);

            if ($stmt->execute()) {
                echo "Total del carrito actualizado exitosamente.";
                $producto = obtenerProducto($conn, $id_articulo);
                echo "<script type='text/javascript'>
                alert('El artículo " . htmlspecialchars($producto['Descripcion']) . " se ha añadido al carrito.');
                window.close();
              </script>";

            } else {
                echo "Error al actualizar el total: " . $stmt->error;
            }


        } else {
            echo "Error al añadir al carrito.";
        }

    }

}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/detalles_producto.css">
    <title>Detalles del Producto</title>

</head>

<body>
    <?php
    /*
    include("../includes/header.php");
    include("../includes/navbar.php");
    */
    ?>
    <main>
        <div class="producto-detalle">
            <?php if (isset($producto)): ?>
                <img src="<?php echo htmlspecialchars($producto['Imagen']); ?>"
                    alt="<?php echo htmlspecialchars($producto['Descripcion']); ?>">
                <h2><?php echo htmlspecialchars($producto['Descripcion']); ?></h2>
                <p class="precio">Precio: <?php echo htmlspecialchars($producto['Precio']); ?>€</p>
                <p><strong>Descripción:</strong> <?php echo htmlspecialchars($producto['Descripcion']); ?></p>
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                    <input type="hidden" name="id_articulo" value="<?php echo $producto['ID_Articulo']; ?>">
                    <input type="hidden" name="precio" value="<?php echo $producto['Precio']; ?>">
                    <input type="number" name="cantidad" value="1" min="1">
                    <button type="submit" name="añadirAlcarrito">Agregar al carrito</button>
                </form>
            <?php else: ?>
                <p>No se pudo cargar el producto.</p>
            <?php endif; ?>
        </div>
    </main>
</body>

</html>