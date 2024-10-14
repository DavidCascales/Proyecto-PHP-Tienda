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

        // Consulta para obtener las líneas de pedido
        $sql_lineas = "SELECT lp.ID_Linea_Pedido, lp.Cantidad, lp.Precio_Linea, a.Descripcion
         FROM Linea_Pedido lp
         JOIN Articulo a ON lp.ID_Articulo = a.ID_Articulo
         JOIN Pedido p ON lp.ID_Pedido = p.ID_Pedido
         WHERE p.ID_Usuario = ?";
        $stmt_lineas = $conn->prepare($sql_lineas);
        $stmt_lineas->bind_param("i", $id_usuario);
        $stmt_lineas->execute();
        $result_lineas = $stmt_lineas->get_result();

        $lineas_pedido = [];
        while ($row = $result_lineas->fetch_assoc()) {
            $lineas_pedido[] = $row;
        }

    }

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
    <h1>Carrito de <?php echo ($usuario["Nombre"]) ?></h1>
    <?php if (empty($lineas_pedido)): ?>
        <p>No tienes artículos en tu carrito.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Artículo</th>
                    <th>Cantidad</th>
                    <th>Precio</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($lineas_pedido as $linea): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($linea['Descripcion']); ?></td>
                        <td><?php echo htmlspecialchars($linea['Cantidad']); ?></td>
                        <td><?php echo htmlspecialchars($linea['Precio_Linea']); ?>€</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

</body>

</html>