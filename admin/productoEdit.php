<?php
session_start();
include("../config/Database.php");

if ($_SERVER["REQUEST_METHOD"] == "GET") {

    if (!isset($_SESSION['id_Usuario'])) {
        header('Location: login.php');
    } else {

        if (isset($_GET['id_articulo'])) {
            $id_articulo = intval($_GET['id_articulo']); // Asegúrate de que es un número entero

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
        }

    }

} else {

    // // Obtener y sanitizar los datos del formulario
    // $id_articulo = intval($_POST['id_articulo']);
    // $imagen = trim($_POST['imagen']);
    // $descripcion = trim($_POST['descripcion']);
    // $precio = floatval($_POST['precio']);


    // // Preparar la consulta para actualizar el artículo
    // $sql = "UPDATE Articulo SET imagen = ?, descripcion = ?, precio = ? WHERE ID_Articulo = ?";
    // $stmt = $conn->prepare($sql);
    // $stmt->bind_param("ssdi", $imagen, $descripcion, $precio, $id_articulo);

    // // Ejecutar la consulta
    // if ($stmt->execute()) {
    //     echo "<script>alert('Artículo actualizado exitosamente.'); window.location.href='index.php';</script>";
    // } else {
    //     echo "Error al actualizar el artículo: " . $stmt->error;
    // }

    // // Cerrar la declaración y la conexión
    // // $stmt->close();
    // // $conn->close();

    // // Redirigir a una página de éxito o listado de artículos
    // header('Location: index.php');

    // Verificar que se ha subido una imagen
    if (isset($_FILES['articulo_imagen']) && $_FILES['articulo_imagen']['error'] === UPLOAD_ERR_OK) {
        $id_articulo = $_POST['id_articulo'];
        $descripcion = $_POST['descripcion'];
        $precio = $_POST['precio'];

        $nombreArchivo = $_FILES['articulo_imagen']['name'];
        $nombreArchivo = str_replace(' ', '_', $nombreArchivo);
        $rutaTemporal = $_FILES['articulo_imagen']['tmp_name'];
        $imagenRuta = "../assets/images/" . basename($nombreArchivo);

        // Mover el archivo a la carpeta de destino
        if (move_uploaded_file($rutaTemporal, $imagenRuta)) {
            // Aquí puedes actualizar el artículo en la base de datos
            $stmt = $conn->prepare("UPDATE Articulo SET Imagen = ?, Descripcion = ?, Precio = ? WHERE ID_Articulo = ?");
            $stmt->bind_param("ssdi", $imagenRuta, $descripcion, $precio, $id_articulo);

            if ($stmt->execute()) {
                echo "Artículo actualizado correctamente.";
                header('Location: index.php');
            } else {
                echo "Error al actualizar el artículo: " . $stmt->error;
            }
        } else {
            echo "Error al mover la imagen.";
        }
    } else {
        echo "Error en la carga de la imagen.";
    }

}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="../assets/css/editProducto.css">
</head>

<body>
    <?php if (isset($producto)): ?>
        <form action="<?php echo ($_SERVER["PHP_SELF"]) ?>" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id_articulo" value="<?php echo htmlspecialchars($producto['ID_Articulo']); ?>">

            <label for="imagen">Imagen:</label>


            <input type="file" name="articulo_imagen" value="<?php echo htmlspecialchars($producto['Imagen']); ?>"
                accept="image/*" required>
            <br><br>

            <label for="descripcion">Descripción:</label>
            <input type="text" id="descripcion" name="descripcion"
                value="<?php echo htmlspecialchars($producto['Descripcion']); ?>" required><br><br>

            <label for="precio">Precio:</label>
            <input type="number" id="precio" name="precio" value="<?php echo htmlspecialchars($producto['Precio']); ?>"
                required step="0.01"><br><br>

            <input type="submit" value="Actualizar Artículo">
        </form>
    <?php else: ?>
        <p class="error">No se encontraron datos del artículo.</p>
    <?php endif; ?>
</body>

</html>