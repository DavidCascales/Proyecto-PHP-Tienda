<?php
session_start();
include("../config/Database.php");
if ($_SERVER["REQUEST_METHOD"] == "GET") {

    if (!isset($_SESSION['id_Usuario'])) {
        header('Location: login.php');
        exit;
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

    $totalPedido = isset($_GET['total']) ? $_GET['total'] : 0;
    $id_pedido = isset($_GET['id_pedido']) ? $_GET['id_pedido'] : null;


} else {
    if (isset($_POST["pagar"])) {
        $id_pedido = $_POST['id_pedido'];
        $total_pedido = $_POST['total_pedido'];

        // Preparar la consulta para actualizar el total del pedido
        $sql = "UPDATE Pedido SET Estado = 'Pedido' WHERE ID_Pedido = ? AND ID_Usuario=? AND Estado='Carrito'";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $id_pedido, $_SESSION['id_Usuario']);

        if ($stmt->execute()) {
            echo "Total pagado.".$total_pedido;
            
            echo "<script>alert('Total pagado: " . htmlspecialchars($total_pedido) . "'); window.location.href='carrito.php';</script>";
        } else {
            echo "Error al pagar : " . $stmt->error;
        }

    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/detallesPedido.css">

    <title>Document</title>

</head>

<body>
    <div class="container">
        <h1>Confirmar Pago</h1>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
            <h2>Información del Usuario</h2>
            <p><strong>Nombre:</strong> <?php echo htmlspecialchars($usuario['Nombre']); ?></p>
            <p><strong>Correo Electrónico:</strong> <?php echo htmlspecialchars($usuario['Email']); ?></p>
            <p><strong>Teléfono:</strong> <?php echo htmlspecialchars($usuario['Telefono']); ?></p>

            <h2>Monto a Pagar</h2>
            <p><strong>Total:</strong> <?php echo $totalPedido ?>€</p>
            <input type="hidden" name="id_pedido" value="<?php echo $id_pedido; ?>">
            <input type="hidden" name="total_pedido" value="<?php echo $totalPedido; ?>">
            <button type="submit" name="pagar">Confirmar Pago</button>


        </form>
    </div>
</body>

</html>