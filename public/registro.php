<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

//Load Composer's autoloader
require '../config/PHPMail/Exception.php';
require '../config/PHPMail/PHPMailer.php';
require '../config/PHPMail/SMTP.php';

//Create an instance; passing `true` enables exceptions
$mail = new PHPMailer(true);




if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $errores = [];
    $erroresflag = false;

    



    if (!(strlen($_POST["contraseña"]) >= 6)) {
        $erroresflag = true;
        array_push($errores, "<p style='color: red;'> La contraseña tiene que ser minimo de 6 caracteres <p>");


    }

    if (strlen($_POST["usuario"]) == 0) {
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

    if (strlen($_POST["rol"]) == 0) {
        $erroresflag = true;
        array_push($errores, "<p style='color: red;'> Se debe elegir un rol<p>");
    }

    function validarCorreo($correo) {
        // Eliminar espacios en blanco al inicio y al final
        $correo = trim($correo);
        
        // Verificar si el correo es válido usando filter_var
        if (filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            return true; // El correo es válido
        } else {
            return false; // El correo no es válido
        }
    }



    if(!validarCorreo($_POST["mail"])){
        $erroresflag = true;
        array_push($errores, "<p style='color: red;'> El correo no tiene el formato correcto<p>");
    }

    function validarDNI($dni) {
        // Comprobar que el formato es correcto (8 dígitos seguidos de una letra)
        return preg_match('/^\d{8}[A-Z]$/', $dni) === 1;
    }

    if (!validarDNI($_POST["dni"])) {
        $erroresflag = true;
        array_push($errores, "<p style='color: red;'> El dni no tiene el formato correcto<p>");

    } 


    if (!$erroresflag) {
        $mensaje = "usuario" . $_POST["mail"]."creado con éxito";

        echo "<script type='text/javascript'>alert('$mensaje');</script>";

        try {
            //Server settings
            $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
            $mail->isSMTP();                                            //Send using SMTP
            $mail->Host = 'smtp-relay.gmail.com';                     //Set the SMTP server to send through
            $mail->SMTPAuth = true;                                   //Enable SMTP authentication
            $mail->Username = 'david.cascales@iesdoctorbalmis.com';                     //SMTP username
            $mail->Password = '123a-123b';                               //SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
            $mail->Port = 465;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
    
            //Recipients
            $mail->setFrom('david.cascales@iesdoctorbalmis.com', 'David');    //Add a recipient
    
    
            $mail->addAddress($_POST["mail"]);               //Name is optional
         
        
    
            //Content
            $mail->isHTML(true);                                  //Set email format to HTML
            $mail->Subject = 'Creación de usuario';
            $mail->Body = '<html><h1>usuario creado con exito</h1></html>';
            $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';
            
            
            $mail->send();
            header("Location:login.php");
          
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
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

    <div class="contenedor">
        <h1>Registro</h1>

        <form action="<?php echo ($_SERVER["PHP_SELF"]) ?>" method="POST">

            <label for="Usuario">Usuario</label>
            <input name="usuario" type="text" required>

            <label for="Mail">Mail</label>
            <input name="mail" type="email" required>

            <label for="Contraseña">Contraseña</label>
            <input name="contraseña" type="password" required>

            <label for="Apellido">Apellidos</label>
            <input name="apellido" type="text" required>

            <label for="Telefono">Telefono</label>
            <input name="telefono" type="tel" >

            <label for="Calle">Calle</label>
            <input name="calle" type="text" >

            <label for="Rol">Rol</label>
            <select name="rol" required>
                <option value="usuario">Cliente</option>
                <option value="administrador">Administrador</option>
            </select>

            <label for="Dni">Dni</label>
            <input name="dni" type="text">

            <input type="submit" value="Crear cuenta">



        </form>
    </div>
</body>

</html>