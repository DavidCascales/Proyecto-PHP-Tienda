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
            <img src="<?php echo htmlspecialchars($producto['Imagen']); ?>" alt="<?php echo htmlspecialchars($producto['Descripcion']); ?>">
            <h2><?php echo htmlspecialchars($producto['Descripcion']); ?></h2>
            <p class="precio">Precio: <?php echo htmlspecialchars($producto['Precio']); ?>€</p>
            <p><strong>Descripción:</strong> <?php echo htmlspecialchars($producto['Descripcion']); ?></p>
            <form action="agregar_al_carrito.php" method="POST">
                <input type="hidden" name="id_articulo" value="<?php echo $producto['ID_Articulo']; ?>">
                <input type="number" name="cantidad" value="1" min="1">
                <button type="submit">Agregar al carrito</button>
            </form>
        </div>
    </main>
</body>

</html>