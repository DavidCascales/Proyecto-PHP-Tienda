<?php
session_start();
include("../config/Database.php");
if ($_SERVER["REQUEST_METHOD"] == "GET") {

    if (!isset($_SESSION['id_Usuario'])) {
        header('Location: login.php');
    } else {
        // Verificar si se recibió el ID del producto
        if (isset($_GET['id'])) {
            $id_articulo = intval($_GET['id']); // Asegúrate de que es un número entero

            // Realizar la consulta para obtener los detalles del producto
            $sql = "SELECT * FROM Articulo WHERE ID_Articulo = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id_articulo);
            $stmt->execute();
            $result = $stmt->get_result();

            // Verificar si se encontró el producto
            if ($result->num_rows > 0) {
                $producto = $result->fetch_assoc(); // Obtener los detalles del producto
            } else {
                echo "Producto no encontrado.";
                exit;
            }

            $stmt->close();
        } else {
            echo "ID de producto no especificado.";
            exit;
        }

        $conn->close();
    }
} else {
    function añadirAlcarrito($conn, $id_articulo, $precio, $cantidad, $id_pedido)
    {

        $stmt = $conn->prepare("INSERT INTO Linea_Pedido (Cantidad, Precio_Linea, ID_Pedido, ID_Articulo) VALUES (?,?,?,?)");
        $stmt->bind_param("idii", $cantidad, $cantidad * $precio, $id_pedido, $id_articulo);
        return $stmt->execute();

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
            $total = 0;
            // Preparar la consulta
            $sql = "SELECT Total 
                    FROM Pedido 
                    WHERE ID_Usuario = ? AND Estado = 'Carrito'AND ID_Pedido=$id_pedido";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $_SESSION['id_Usuario']);
            $stmt->execute();
            $result = $stmt->get_result();

            // Comprobar si hay resultados
            if ($result->num_rows > 0) {
                // Obtener el único pedido encontrado
                $row = $result->fetch_assoc();
                $total = $row['Total'];
            }

            $total += $precio * $cantidad;

            // Preparar la consulta para actualizar el total del pedido
            $sql = "UPDATE Pedido SET Total = ? WHERE ID_Pedido = ? AND ID_Usuario=? AND Estado='Carrito'";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("di", $total, $id_pedido);

            if ($stmt->execute()) {
                echo "Total del carrito actualizado exitosamente.";
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
            <img src="<?php echo htmlspecialchars($producto['Imagen']); ?>"
                alt="<?php echo htmlspecialchars($producto['Descripcion']); ?>">
            <h2><?php echo htmlspecialchars($producto['Descripcion']); ?></h2>
            <p class="precio">Precio: <?php echo htmlspecialchars($producto['Precio']); ?>€</p>
            <p><strong>Descripción:</strong> <?php echo htmlspecialchars($producto['Descripcion']); ?></p>
            <form action="<?php echo ($_SERVER["PHP_SELF"]) ?>" method="POST">
                <input type="hidden" name="id_articulo" value="<?php echo $producto['ID_Articulo']; ?>">
                <input type="hidden" name="precio" value="<?php echo $producto['Precio']; ?>">
                <input type="number" name="cantidad" value="1" min="1">
                <button type="submit" name="añadirAlcarrito">Agregar al carrito</button>
            </form>
        </div>
    </main>
</body>

</html>