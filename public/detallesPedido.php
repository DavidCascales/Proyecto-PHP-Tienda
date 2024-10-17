<?php
session_start();
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$usuario = "";
include("../config/Database.php");
require '../config/PHPMail/Exception.php';
require '../config/PHPMail/PHPMailer.php';
require '../config/PHPMail/SMTP.php';

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

        $id_usuario = intval($_SESSION['id_Usuario']);


        $email_usuario = $_POST['emailUsuario'];
        $id_pedido = $_POST['id_pedido'];
        $total_pedido = $_POST['total_pedido'];

        // Consulta para obtener las líneas de pedido
        $sql_lineas = "SELECT lp.ID_Linea_Pedido, lp.Cantidad, lp.Precio_Linea, a.Descripcion , a.Precio
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
        // Debug: Verificar si se encontraron líneas de pedido
        if (empty($lineas_pedido)) {
            echo "No se encontraron líneas de pedido.";
        }


        // Preparar la consulta para actualizar el total del pedido
        $sql = "UPDATE Pedido SET Estado = 'Pedido' WHERE ID_Pedido = ? AND ID_Usuario=? AND Estado='Carrito'";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $id_pedido, $_SESSION['id_Usuario']);

        if ($stmt->execute()) {
            echo "Total pagado." . $total_pedido;


            //Create an instance; passing `true` enables exceptions
            $mail = new PHPMailer(true);



            // Construir productos HTML
            $productosHtml = '';
            foreach ($lineas_pedido as $producto) {
                $productosHtml .= "<li>" . htmlspecialchars($producto["Descripcion"]) . " - " . htmlspecialchars($producto["Precio"]) . "€     X". htmlspecialchars($producto["Cantidad"]) . "</li>";
            }


            try {


                $mail->isSMTP();
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                $mail->Host = "smtp.gmail.com";
                $mail->SMTPAuth = true;
                $mail->Port = 465;
                $mail->Username = "intentoaprobar@gmail.com";
                $mail->Password = "jhgl eire jouo oxxs";
                $mail->setFrom("intentoaprobar@gmail.com");
                $mail->addAddress($email_usuario);
                //Content
                $mail->isHTML(true);                                  //Set email format to HTML
                $mail->Subject = 'Resumen Compra';
                $mail->Body = '<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Productos</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        .container {
            background: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            color: #4CAF50;
        }
        ul {
            list-style-type: none;
            padding: 0;
        }
        li {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }
    </style>
</head>
<body>
    <div class="container">
                        <h1>Lista de Productos</h1>
                        <ul>' . $productosHtml . '</ul>
<p><strong>Total:</strong> ' . htmlspecialchars($total_pedido) . '€</p>
    </div>
</body>
</html>';
                $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';


                $mail->send();


            } catch (Exception $e) {
                echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            }






            echo "<script>alert('Total pagado: " . htmlspecialchars($total_pedido) . "€.Se le ha enviado un correo con toda la información'); window.location.href='carrito.php';</script>";
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
            <input type="hidden" name="emailUsuario" value="<?php echo $usuario["Email"]; ?>">
            <button type="submit" name="pagar">Confirmar Pago</button>


        </form>
    </div>
</body>

</html>