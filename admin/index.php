<?php
session_start();
include("../config/Database.php");


if ($_SERVER["REQUEST_METHOD"] == "GET") {


    if (!isset($_SESSION['id_Usuario'])) {
        header('Location: login.php');
    } else {

        $id_usuario = intval($_SESSION['id_Usuario']);


        $sql = "SELECT * FROM Usuario WHERE ID_Usuario = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $result = $stmt->get_result();


        if ($result->num_rows > 0) {
            $usuario = $result->fetch_assoc();
        } else {
            echo "Usuario no encontrado.";
            exit;
        }




    }

} else {

    // Método para eliminar las asociaciones en articulocategoria
    function eliminarAsociacionesCategoria($conn, $id_categoria)
    {
        $stmt = $conn->prepare("DELETE FROM articulocategoria WHERE ID_Categoria = ?");
        $stmt->bind_param("i", $id_categoria);
        return $stmt->execute();
    }

    // Método para eliminar las asociaciones en articulocategoria
    function eliminarAsociacionesArticulo($conn, $id_articulo)
    {
        $stmt = $conn->prepare("DELETE FROM articulocategoria WHERE ID_Articulo = ?");
        $stmt->bind_param("i", $id_articulo);
        return $stmt->execute();
    }

    // Método para eliminar una categoría
    function eliminarCategoria($conn, $id_categoria)
    {
        $stmt = $conn->prepare("DELETE FROM Categoria WHERE ID_Categoria = ?");
        $stmt->bind_param("i", $id_categoria);
        return $stmt->execute();
    }

    // Método para eliminar un artículo
    function eliminarArticulo($conn, $id_articulo)
    {
        $stmt = $conn->prepare("DELETE FROM Articulo WHERE ID_Articulo = ?");
        $stmt->bind_param("i", $id_articulo);
        return $stmt->execute();
    }


    // Manejar la eliminación de categorías
    if (isset($_POST['delete_categoria'])) {
        $id_categoria = $_POST['id_categoria'];
        eliminarAsociacionesCategoria($conn, $id_categoria);
        if (eliminarCategoria($conn, $id_categoria)) {
            echo "Categoría eliminada correctamente.";
        } else {
            echo "Error al eliminar la categoría.";
        }
    }

    // Manejar la eliminación de artículos
    if (isset($_POST['delete_articulo'])) {
        $id_articulo = $_POST['id_articulo'];
        eliminarAsociacionesArticulo($conn, $id_articulo);
        if (eliminarArticulo($conn, $id_articulo)) {
            echo "Artículo eliminado correctamente.";
        } else {
            echo "Error al eliminar el artículo.";
        }
    }

    // Método para añadir una categoría
    function añadirCategoria($conn, $categoria_descripcion)
    {

        $stmt = $conn->prepare("INSERT INTO Categoria (Descripcion) VALUES (?)");
        $stmt->bind_param("s", $categoria_descripcion);
        return $stmt->execute();
    }
    // Método para añadir un artículo
    function añadirArticulo($conn, $articulo_descripcion, $articulo_imagen, $articulo_precio, $id_categoria)
    {
        $imagenRuta = "../assets/images/" . $articulo_imagen;
        $stmt = $conn->prepare("INSERT INTO Articulo (Imagen, Descripcion, Precio) VALUES (?,?,?)");
        $stmt->bind_param("ssd", $imagenRuta, $articulo_descripcion, $articulo_precio);

        if ($stmt->execute()) {
            // Obtener el ID del artículo recién creado
            $id_articulo = $conn->insert_id; // Aquí usamos insert_id para obtener el ID directamente

            // Inserta la asociación en articulocategoria
            $stmt = $conn->prepare("INSERT INTO articulocategoria (ID_Articulo, ID_Categoria) VALUES (?, ?)");
            $stmt->bind_param("ii", $id_articulo, $id_categoria);
            return $stmt->execute();
        }
        return false;
    }

    // Manejar añadir artículos
    if (isset($_POST['add_articulo'])) {
        $articulo_descripcion = $_POST['articulo_descripcion'];

        $id_categoria = $_POST['id_categoria'];
        $articulo_precio = $_POST['articulo_precio'];

        // Procesar la imagen subida
        if (isset($_FILES['articulo_imagen']) && $_FILES['articulo_imagen']['error'] === UPLOAD_ERR_OK) {
            $nombreArchivo = $_FILES['articulo_imagen']['name'];
            $rutaTemporal = $_FILES['articulo_imagen']['tmp_name'];
            $imagenRuta = "../assets/images/" . basename($nombreArchivo);

            // Mover el archivo subido a la carpeta correspondiente
            if (move_uploaded_file($rutaTemporal, $imagenRuta)) {
                if (añadirArticulo($conn, $articulo_descripcion, $nombreArchivo, $articulo_precio, $id_categoria)) {
                    echo "Artículo añadido correctamente.";
                } else {
                    echo "Error al añadir el artículo.";
                }
            } else {
                echo "Error al mover la imagen.";
            }
        } else {
            echo "Error al subir la imagen.";
        }
    }
    // Manejar añadir categorías
    if (isset($_POST['add_categoria'])) {
        $categoria_descripcion = $_POST['categoria_descripcion'];
        if (añadirCategoria($conn, $categoria_descripcion)) {
            echo "Categoría añadida correctamente.";
        } else {
            echo "Error al añadir la categoría.";
        }
    }

    // Manejar cambiar rol
    if (isset($_POST['cambiarRol'])) {

        

        $id_usuario = $_POST['id_usuario'];

        
        $sql = "SELECT * FROM Usuario WHERE ID_Usuario = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $result = $stmt->get_result();


        if ($result->num_rows > 0) {
            $usuario = $result->fetch_assoc();
        } else {
            echo "Usuario no encontrado.";
            exit;
        }
        
        $rol = $_POST['rol'];
        

        // Determina el valor de rol para la consulta
        $rolquery = ($rol == "usuario") ? "usuario" : "administrador";

        if ($rol == "usuario" || $rol == "administrador") {
            // Preparar la consulta para actualizar el artículo
            $sql = "UPDATE usuario SET rol = ? WHERE ID_usuario = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $rolquery , $id_usuario);

            // Ejecutar la consulta
            if ($stmt->execute()) {
                echo "<script>alert('Usuario " . $usuario["Nombre"] . " ha cambiado al rol " . $rolquery . ".');</script>";
            } else {
                echo "<script>alert('Error al actualizar la categoria: " . addslashes($stmt->error) . "');</script>";
            }
        }

    }




    // Manejar editar artículos
    if (isset($_POST['editar_articulo'])) {
        $id_articulo = $_POST['id_articulo'];
        header("Location:productoEdit.php?id_articulo=" . $id_articulo);
    }

    // Manejar editar categoria
    if (isset($_POST['editar_categoria'])) {
        $id_categoria = $_POST['id_categoria'];
        header("Location:categoriaEdit.php?id_categoria=" . $id_categoria);
    }




}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="../assets/css/indexAdmin.css">
    <style>
        .back-button {
            position: absolute;
            top: 10px;
            left: 10px;
            padding: 10px 15px;
            background-color: #007BFF;
            /* Cambia el color según tu diseño */
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .back-button:hover {
            background-color: #0056b3;
            /* Cambia el color al pasar el ratón */
        }
    </style>

</head>

<body>

    <button class="back-button" onclick="window.location.href='../public/index.php'">Volver</button>

    <?php include("../includes/headerAdmin.php");
    include("../includes/navbar.php"); ?>

    <div class="container">


        <div class="section">
            <h2>Lista de Artículos</h2>
            <ul>
                <?php $result = $conn->query("SELECT * FROM Articulo");
                while ($row = $result->fetch_assoc()): ?>
                    <li>
                        <?php echo $row['Descripcion'] . " - " . $row['Precio'] . "€"; ?>
                        <form action="<?php echo ($_SERVER["PHP_SELF"]) ?>" method="POST" style="display:inline;">
                            <input type="hidden" name="id_articulo" value="<?php echo $row['ID_Articulo']; ?>">
                            <button type="submit" name="editar_articulo"
                                onclick="return confirm('¿Estás seguro de que deseas editar este artículo?');">Editar</button>
                            <button type="submit" name="delete_articulo"
                                onclick="return confirm('¿Estás seguro de que deseas eliminar este artículo?');">Eliminar</button>
                        </form>
                    </li>
                <?php endwhile; ?>
            </ul>

            <h2>Añadir Artículo</h2>
            <form method="POST" enctype="multipart/form-data">
                <input type="text" name="articulo_descripcion" placeholder="Descripción del Artículo" required>
                <input type="file" name="articulo_imagen" accept="image/*" required>
                <input type="number" step="0.01" name="articulo_precio" placeholder="Precio" required>
                <label for="categoria">Seleccionar Categoría:</label>
                <select name="id_categoria" id="categoria" required>
                    <option value="">Ninguna</option>
                    <?php
                    // Obtener categorías de la base de datos
                    $categorias = $conn->query("SELECT * FROM Categoria");
                    while ($row = $categorias->fetch_assoc()) {
                        echo "<option value='{$row['ID_Categoria']}'>{$row['Descripcion']}</option>";
                    }
                    ?>
                </select>
                <button type="submit" name="add_articulo">Añadir Artículo</button>
            </form>
        </div>


        <div class="section">
            <h2>Lista de Categorías</h2>
            <ul>

                <?php $result = $conn->query("SELECT * FROM Categoria");
                while ($row = $result->fetch_assoc()): ?>
                    <li>
                        <?php echo $row['Descripcion']; ?>

                        <form action="<?php echo ($_SERVER["PHP_SELF"]) ?>" method="POST" style="display:inline;">
                            <input type="hidden" name="id_categoria" value="<?php echo $row['ID_Categoria']; ?>">
                            <button type="submit" name="editar_categoria"
                                onclick="return confirm('¿Estás seguro de que deseas editar esta categoria?');">Editar</button>
                            <button type="submit" name="delete_categoria"
                                onclick="return confirm('¿Estás seguro de que deseas eliminar esta categoría?');">Eliminar</button>
                        </form>
                    </li>
                <?php endwhile; ?>
            </ul>

            <h2>Añadir Categoría</h2>
            <form method="POST">
                <input type="text" name="categoria_descripcion" placeholder="Descripción de la Categoría" required>
                <button type="submit" name="add_categoria">Añadir Categoría</button>
            </form>
        </div>

        <div class="section">
            <h2>Lista de Usuarios</h2>
            <ul>

                <?php $result = $conn->query("SELECT * FROM Usuario");
                while ($row = $result->fetch_assoc()): ?>
                    <li>
                        <?php echo $row['Nombre'] . " - " . $row['Email']; ?>


                        <form action="<?php echo ($_SERVER["PHP_SELF"]) ?>" method="POST" style="display:inline;">

                            <input type="hidden" name="id_usuario" value="<?php echo $row['ID_Usuario']; ?>">
                            <select name="rol" onchange="mostrarBoton(this)">
                                <option value="usuario" <?php echo ($row['Rol'] == 'usuario') ? 'selected' : ''; ?>>Usuario
                                </option>
                                <option value="administrador" <?php echo ($row['Rol'] == 'administrador') ? 'selected' : ''; ?>>Administrador</option>
                            </select>
                            <!-- Botón de envío oculto por defecto -->
                            <button type="submit" name="cambiarRol" style="display: none;"
                                class="submit-btn">Enviar</button>
                        </form>
                    </li>
                <?php endwhile; ?>
                <script>
                    function mostrarBoton(selectElement) {
                        // Muestra el botón de envío correspondiente
                        const submitButton = selectElement.form.querySelector('.submit-btn');
                        submitButton.style.display = 'inline'; // Cambia a inline o block según lo necesites
                    }
                </script>
            </ul>
        </div>
    </div>

    <?php $conn->close(); ?>
</body>

</html>