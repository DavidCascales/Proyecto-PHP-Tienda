<?php

session_start();

include("../config/Database.php");

if ($_SERVER["REQUEST_METHOD"] == "GET") {

    
    if (!isset($_SESSION['id_Usuario'])) {
        header('Location: login.php');
    } else {
        // Realizar la consulta
        $sql = "SELECT * FROM Articulo"; // Cambia "tu_tabla" según tu necesidad
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
</head>

<body>
    <?php
    include("../includes/header.php");
    include("../includes/navbar.php");
    ?>
    <main >
        <div id="productos">
            <?php foreach ($arrayResultados as $item): ?>
                <div class="producto">
                    <img src=<?php echo htmlspecialchars($item['Imagen']); ?>>
                    <h2><?php echo htmlspecialchars($item['Descripcion']); ?></h2> <!-- Cambia 'nombre' según tu columna -->
                    <p><?php echo htmlspecialchars($item['Precio']); ?>€</p> <!-- Cambia 'descripcion' según tu columna -->
                    <!-- Agrega más campos según necesites -->
                </div>
            <?php endforeach; ?>
        </div>

    </main>
</body>

</html>