<?php

session_start();

include("../config/Database.php");

if ($_SERVER["REQUEST_METHOD"] == "GET") {


    if (!isset($_SESSION['id_Usuario'])) {
        header('Location: login.php');
    } else {
        // Realizar la consulta
        $sql = "SELECT Articulo.ID_Articulo, Articulo.Imagen, Articulo.Descripcion AS ArticuloDescripcion, 
                       Categoria.Descripcion AS CategoriaDescripcion, Articulo.Precio
                FROM Articulo
                LEFT JOIN ArticuloCategoria ON Articulo.ID_Articulo = ArticuloCategoria.ID_Articulo
                LEFT JOIN Categoria ON ArticuloCategoria.ID_Categoria = Categoria.ID_Categoria;"; // Cambia "tu_tabla" según tu necesidad
        $result = $conn->query($sql);

        // Inicializar el array para almacenar los resultados
        $arrayResultados = [];

        if ($result->num_rows > 0) {
            // Volcar los resultados en el array
            while ($row = $result->fetch_assoc()) {
                $arrayResultados[] = $row; // Agrega cada fila al array
            }
        } else {
            echo "0 resultados";
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
    <link rel="stylesheet" href="../assets/css/tienda.css">
    <title>Tienda de Ropa</title>
    <script>
        function abrirDetalle(idArticulo) {

            const url = 'detalles_producto.php?id=' + idArticulo;
            window.open(url, '_blank');

        }
    </script>
</head>

<body>
    <?php
    include("../includes/header.php");
    include("../includes/navbar.php");
    ?>
    <main>
        <div id="productos">
            <?php foreach ($arrayResultados as $item): ?>
                <div class="producto" onclick="abrirDetalle(<?php echo $item['ID_Articulo']; ?>)">
                    <img src=<?php echo htmlspecialchars($item['Imagen']); ?>>
                    <h2><?php echo htmlspecialchars($item['ArticuloDescripcion']); ?></h2> <!-- Cambia 'nombre' según tu columna -->
                    <p>Categoria: <?php if ($item['CategoriaDescripcion']==null) {
                        echo "Sin categoria";
                    }  else{echo htmlspecialchars($item['ArticuloDescripcion']);}?></p>
                    <p><?php echo htmlspecialchars($item['Precio']); ?>€</p> <!-- Cambia 'descripcion' según tu columna -->

                </div>
            <?php endforeach; ?>
        </div>

    </main>
</body>

</html>