<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $errores = [];

    if (strlen($_POST["contraseña"]) >= 6) {


        //header("Location:Bienvenida.php?nombre=" . $_POST["usuario"]);
    } else {
       array_push($errores, "<p style='color: red;'> La contraseña tiene que ser minimo de 6 caracteres <p>");
    }

    if (strlen($_POST["usuario"])!=0) {
        # ir a la tienda
    } else {
        array_push($errores, "<p style='color: red;'> El nombre no debe de estar vacio<p>");

    }

}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="../assets/css/login.css">
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
        <h1>Inicio Sesión</h1>
       

        <form action="<?php echo ($_SERVER["PHP_SELF"]) ?>" method="POST">

            <label for="Usuario">Usuario</label>
            <input name="usuario" type="text" required>

            <label for="Mail">Mail</label>
            <input name="mail" type="email" required>

            <label for="Contraseña">Contraseña</label>
            <input name="contraseña" type="password" required>

            <input type="submit">

            <div style="text-align: right; padding: 10px;">
                <a href="registro.php">¿No tienes cuenta?</a>
            </div>

        </form>
    </div>



</body>

</html>