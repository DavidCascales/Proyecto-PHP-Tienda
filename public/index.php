<?php

session_start();

include("../config/Database.php");

if ($_SERVER["REQUEST_METHOD"] == "GET") {


    if (!isset($_SESSION['id_Usuario'])) {
        header('Location: login.php');
    } else {

        // Inicializar la consulta base
        $sql = "SELECT Articulo.ID_Articulo, Articulo.Imagen, Articulo.Descripcion AS ArticuloDescripcion, 
    Categoria.Descripcion AS CategoriaDescripcion, Articulo.Precio
               FROM Articulo
           LEFT JOIN ArticuloCategoria ON Articulo.ID_Articulo = ArticuloCategoria.ID_Articulo
           LEFT JOIN Categoria ON ArticuloCategoria.ID_Categoria = Categoria.ID_Categoria";

        // Comprobar si hay un término de búsqueda
        if (isset($_GET['buscar']) && !empty(trim($_GET['buscar']))) {
            $buscar = trim($_GET['buscar']);
            $sql .= " WHERE Articulo.Descripcion LIKE ? OR Categoria.Descripcion LIKE ?";
        }

        $stmt = $conn->prepare($sql);

        // Si hay un término de búsqueda, se usa en la consulta
        if (isset($buscar)) {
            $likeBuscar = "%" . $buscar . "%";
            $stmt->bind_param("ss", $likeBuscar, $likeBuscar);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        // Inicializar el array para almacenar los resultados
        $arrayResultados = [];

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $arrayResultados[] = $row;
            }
        } else {
            echo "0 resultados";
        }
        $stmt->close();
       
    }
    
}




 $conn->close();


?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/tienda.css">
    <title>Tienda de Ropa</title>
    <script>
        function abrirDetalle(idArticulo) {

            const url = 'detalles_producto.php?id=' + idArticulo;
            window.open(url, '_blank');

        }
        function LimpiarFiltro() {
            // Redirigir a la misma página sin parámetros de búsqueda
            window.location.href = "<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>";
        }
    </script>
</head>

<body>
    <?php
    include("../includes/header.php");
    include("../includes/navbar.php");
    ?>
    <main>
        <form method="GET" class="buscador-form" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
            <input type="text" class="buscador-input" name="buscar" placeholder="Buscar por descripción o categoría" required>
            <button type="submit"class="buscador-button">Buscar</button>
            <button type="button"class="buscador-button" onclick="LimpiarFiltro()">Limpiar</button>
        </form>
        <div id="productos">
            <?php foreach ($arrayResultados as $item): ?>
                <div class="producto" onclick="abrirDetalle(<?php echo $item['ID_Articulo']; ?>)">
                    <img src=<?php echo htmlspecialchars($item['Imagen']); ?>>
                    <h2><?php echo htmlspecialchars($item['ArticuloDescripcion']); ?></h2>
                    <!-- Cambia 'nombre' según tu columna -->
                    <p>Categoria: <?php if ($item['CategoriaDescripcion'] == null) {
                        echo "Sin categoria";
                    } else {
                        echo htmlspecialchars($item['CategoriaDescripcion']);
                    } ?></p>
                    <p><?php echo htmlspecialchars($item['Precio']); ?>€</p> <!-- Cambia 'descripcion' según tu columna -->

                </div>
            <?php endforeach; ?>
        </div>

    </main>
</body>

</html>