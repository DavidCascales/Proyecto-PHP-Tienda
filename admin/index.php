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

        $stmt->close();
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
</head>

<body>
<?php include("../includes/headerAdmin.php"); ?>
    
    <div class="container">
       

        <div class="section">
            <h2>Lista de Artículos</h2>
            <ul>
                <?php
                $result = $conn->query("SELECT * FROM Articulo");
                while ($row = $result->fetch_assoc()) {
                    echo "<li>{$row['Descripcion']} - {$row['Precio']} 
                    <a href='?delete_articulo={$row['ID_Articulo']}'>Eliminar</a></li>";
                }
                ?>
            </ul>
       
            <h2>Añadir Artículo</h2>
            <form method="POST">
                <input type="text" name="articulo_descripcion" placeholder="Descripción del Artículo" required>
                <input type="text" name="articulo_imagen" placeholder="URL de la Imagen" required>
                <input type="number" step="0.01" name="articulo_precio" placeholder="Precio" required>
                <button type="submit" name="add_articulo">Añadir Artículo</button>
            </form>
        </div>
  
       
        <div class="section">
            <h2>Lista de Categorías</h2>
            <ul>
                <?php
                $result = $conn->query("SELECT * FROM Categoria");
                while ($row = $result->fetch_assoc()) {
                    echo "<li>{$row['Descripcion']} 
                    <a href='?delete_categoria={$row['ID_Categoria']}'>Eliminar</a></li>";
                }
                ?>
            </ul>
        
            <h2>Añadir Categoría</h2>
            <form method="POST">
                <input type="text" name="categoria_descripcion" placeholder="Descripción de la Categoría" required>
                <button type="submit" name="add_categoria">Añadir Categoría</button>
            </form>
        </div>
    </div>

    <?php $conn->close(); ?>
</body>

</html>