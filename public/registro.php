<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

//Load Composer's autoloader
require '../config/PHPMail/Exception.php';
require '../config/PHPMail/PHPMailer.php';
require '../config/PHPMail/SMTP.php';
/*
include '../config/PHPmail/Testmail.php';*/

include '../config/database.php';





if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $errores = [];
    $erroresflag = false;



    if (!(strlen($_POST["contraseña"]) >= 6)) {
        $erroresflag = true;
        array_push($errores, "<p style='color: red;'> La contraseña tiene que ser minimo de 6 caracteres <p>");

    }

    if (strlen($_POST["nombre"]) == 0) {
        $erroresflag = true;
        array_push($errores, "<p style='color: red;'> El nombre no debe de estar vacio<p>");
    }

    if (strlen($_POST["apellido"]) == 0) {
        $erroresflag = true;
        array_push($errores, "<p style='color: red;'> El apellido no debe de estar vacio<p>");
    }

    if (strlen($_POST["dni"]) == 0) {
        $erroresflag = true;
        array_push($errores, "<p style='color: red;'> El dni no debe de estar vacio<p>");
    }


    function validarCorreo($correo)
    {
        // Eliminar espacios en blanco al inicio y al final
        $correo = trim($correo);

        // Verificar si el correo es válido usando filter_var
        if (filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            return true; // El correo es válido
        } else {
            return false; // El correo no es válido
        }
    }



    if (!validarCorreo($_POST["mail"])) {
        $erroresflag = true;
        array_push($errores, "<p style='color: red;'> El correo no tiene el formato correcto<p>");
    }

    function validarDNI($dni)
    {
        // Comprobar que el formato es correcto (8 dígitos seguidos de una letra)
        return preg_match('/^\d{8}[A-Z]$/', $dni) === 1;
    }

    if (!validarDNI($_POST["dni"])) {
        $erroresflag = true;
        array_push($errores, "<p style='color: red;'> El dni no tiene el formato correcto<p>");

    }


    if (!$erroresflag) {


        $usuarioExiste = false;

        $email = $conn->real_escape_string($_POST['mail']);

        // Consulta SQL para seleccionar todos los empleados
        $sql = "SELECT count(*) as usuarioExistente FROM usuario where usuario.Email='$email';";

        // Ejecutar la consulta
        $result = $conn->query($sql);

        // Comprobar si hay resultados
        if ($result) {
            $row = $result->fetch_assoc();
            if ($row['usuarioExistente'] != 0) {
                $usuarioExiste = true;
            }


        }


        if (!$usuarioExiste) {



            // Preparar la consulta SQL con sentencias preparadas
            $stmt = $conn->prepare("INSERT INTO usuario (Email, Contraseña, Nombre, Apellidos,Telefono,Calle,Dni) VALUES ( ?, ?, ?,?,?,?,?)");

            // Comprobar si la preparación fue exitosa
            if ($stmt === false) {
                die("Error en la preparación de la consulta: " . $conn->error);
            }

            // Vincular parámetros
            // "ssss" indica que se esperan 4 Strings
            // "i" es para enteros, "d" para decimales, "b" para BLOBs,
            $stmt->bind_param("sssssss", $email, $contraseña, $nombre, $apellidos, $telefono, $calle, $dni);

            // Ejecutar múltiples inserciones

            // Asignar valores a las variables
            $email = $_POST["mail"];
            $contraseña = $_POST["contraseña"];
            $nombre = $_POST["nombre"];
            $apellidos = $_POST["apellido"];
            $telefono = $_POST["telefono"];
            $calle = $_POST["calle"];
            $dni = $_POST["dni"];

            $mensaje = "Usuario" . $_POST["mail"] . " creado con éxito, se le ha enviado un correo.";

            try {
                // Ejecutar la consulta
                $stmt->execute();

                /*$mailerConfig = new TestMail('../config/PHPMail/mail.properties');
                $mailConfig = $mailerConfig->smtpConfig;*/

                //Create an instance; passing `true` enables exceptions
                $mail = new PHPMailer(true);

                try {


                    $mail->isSMTP();
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                    $mail->Host = "smtp.gmail.com";
                    $mail->SMTPAuth = true;
                    $mail->Port = 465;
                    $mail->Username = "intentoaprobar@gmail.com";
                    $mail->Password = "jhgl eire jouo oxxs";
                    $mail->setFrom("intentoaprobar@gmail.com");
                    $mail->addAddress($_POST["mail"]);
                    //Content
                    $mail->isHTML(true);                                  //Set email format to HTML
                    $mail->Subject = 'Creacion de usuario';
                    $mail->Body = '<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro Exitoso</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            background: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        h1 {
            color: #4CAF50;
        }
        p {
            color: #555;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>¡Registro Exitoso!</h1>
        <p>Gracias por registrarte en nuestra página. Ya puedes empezar a disfrutar de nuestros servicios.</p>
    </div>
</body>
</html>';
                    $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';


                    $mail->send();


                } catch (Exception $e) {
                    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
                }

                echo "<script type='text/javascript'>
                    alert('$mensaje');
                    window.location.href = 'login.php'; // Redirigir después de mostrar el alert
                    </script>";


            } catch (mysqli_sql_exception $e) {
                // Capturar la excepción
                $mensaje = "Error al insertar el usuario " . $_POST["mail"];

                echo "<script type='text/javascript'>alert('$mensaje');</script>";
                // Aquí puedes optar por redirigir a otra página o simplemente mostrar el mensaje
            }

            // Cerrar la sentencia y la conexión
            $stmt->close();





        } else {

            $mensaje = "Error el usuario ya esta registrado en el sistema";
            echo "<script type='text/javascript'>alert('$mensaje');</script>";
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
    <link rel="stylesheet" href="../assets/css/registro.css">
</head>

<body>
    <?php

    if (isset($errores)) {
        foreach ($errores as $value) {
            echo $value . "<br>";
        }

    }
    ?>
    <div>
        <a href="login.php" style="text-decoration: none;">
            <button style="background-color: #e54242;color: white;
    border: none;
    padding: 10px;
    border-radius: 4px;
    cursor: pointer;
    width: 100px;
    height: 80px;">
                <p>Volver al login</p>
            </button>
        </a>
    </div>
    <div class="contenedor">
        <h1>Registro</h1>

        <form action="<?php echo ($_SERVER["PHP_SELF"]) ?>" method="POST">

            <label for="Usuario">Usuario</label>
            <input name="nombre" type="text" required>

            <label for="Mail">Mail</label>
            <input name="mail" type="email" required>

            <label for="Contraseña">Contraseña</label>
            <input name="contraseña" type="password" required>

            <label for="Apellido">Apellidos</label>
            <input name="apellido" type="text" required>

            <label for="Telefono">Telefono</label>
            <input name="telefono" type="tel">

            <label for="Calle">Calle</label>
            <input name="calle" type="text">


            <label for="Dni">Dni</label>
            <input name="dni" type="text">

            <input type="submit" value="Crear cuenta">



        </form>
    </div>
</body>

</html>