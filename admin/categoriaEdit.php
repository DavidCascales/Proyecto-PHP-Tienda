<?php
session_start();
include("../config/Database.php");

if ($_SERVER["REQUEST_METHOD"] == "GET") {

    if (!isset($_SESSION['id_Usuario'])) {
        header('Location: login.php');
    } else {

        if (isset($_GET['id_categoria'])) {
            $id_categoria = intval($_GET['id_categoria']); // Asegúrate de que es un número entero

            // Realizar la consulta para obtener los detalles del producto
            $sql = "SELECT * FROM Categoria WHERE ID_Categoria = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id_categoria);
            $stmt->execute();
            $result = $stmt->get_result();

            // Verificar si se encontró el producto
            if ($result->num_rows > 0) {
                $categoria = $result->fetch_assoc(); // Obtener los detalles del producto
            } else {
                echo "Producto no encontrado.";
                exit;
            }

            $stmt->close();
        }

    }

} else {

    // Obtener y sanitizar los datos del formulario
    $id_categoria = intval($_POST['id_categoria']);
    $descripcion = trim($_POST['descripcion']);


    // Preparar la consulta para actualizar el artículo
    $sql = "UPDATE Categoria SET descripcion = ? WHERE ID_Categoria = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $descripcion, $id_categoria);

    // Ejecutar la consulta
    if ($stmt->execute()) {
        echo "<script>alert('Categoria actualizada exitosamente.'); window.location.href='index.php';</script>";
    } else {
        echo "<script>alert('Error al actualizar la categoria: " . addslashes($stmt->error) . "');</script>";
    }

    // Cerrar la declaración y la conexión
    // $stmt->close();
    // $conn->close();

   

}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
<?php if (isset($categoria)): ?>
        <form action="<?php echo ($_SERVER["PHP_SELF"]) ?>" method="POST">

            <input type="hidden" name="id_categoria" value="<?php echo htmlspecialchars($categoria['ID_Categoria']); ?>">

            <label for="descripcion">Descripcion:</label>
            <input type="text" id="descripcion" name="descripcion" value="<?php echo htmlspecialchars($categoria['Descripcion']); ?>"
                required><br><br>

           

            <input type="submit" value="Actualizar Categoria">
        </form>
    <?php else: ?>
        <p class="error">No se encontraron datos de la Categoria.</p>
    <?php endif; ?>
</body>
</html>