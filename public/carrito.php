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
         WHERE p.ID_Usuario = ? and p.estado = 'Carrito'";
        $stmt_lineas = $conn->prepare($sql_lineas);
        $stmt_lineas->bind_param("i", $id_usuario);
        $stmt_lineas->execute();
        $result_lineas = $stmt_lineas->get_result();

        $lineas_pedido = [];
        while ($row = $result_lineas->fetch_assoc()) {
            $lineas_pedido[] = $row;
        }

    }

} else {
    if (isset($_POST['pagar'])) {


        $id_usuario = intval($_POST['id_usuario']);

        $sql_totalPedido = "SELECT ID_Pedido, Total FROM Pedido WHERE ID_Usuario = ? AND Estado = 'Carrito'";
        $stmt_total = $conn->prepare($sql_totalPedido);
        $stmt_total->bind_param("i", $id_usuario);
        $stmt_total->execute();
        $result_total = $stmt_total->get_result();
        
        if ($result_total->num_rows > 0) {
            $pedido = $result_total->fetch_assoc();
            $total = $pedido['Total'];
            $id_pedido = $pedido['ID_Pedido'];
            
            // Redirigir con el total y el ID del pedido
            header("Location: detallesPedido.php?total=".$total."&id_pedido=".$id_pedido);
            exit; // Asegúrate de salir después de redirigir
        } else {
            echo "No hay total de pedido en el carrito.";
        }

       

    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/carrito.css">

    <title>Document</title>
    <style>
         .boton {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
        }
        .button {
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
        }
        .button:hover {
            background-color: #0056b3;
        }
    </style>
</head>

<body>
<div class="boton">
        <a href="index.php" class="button">Ir a Inicio</a>
    </div>
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
        <div style="text-align: center; margin-top: 20px;">
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                <input type="hidden" name="id_usuario" value="<?php echo $id_usuario; ?>">
                <button type="submit" name="pagar"
                    style="padding: 10px 20px; background-color: #27ae60; color: white; border: none; border-radius: 5px; cursor: pointer;">
                    Pagar
                </button>
            </form>
        </div>
    <?php endif; ?>

</body>

</html>