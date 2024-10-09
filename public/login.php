<?php

include("../config/Database.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $errores = [];
    $erroresflag = false;

    if (!(strlen($_POST["contraseña"]) >= 6)) {
        $erroresflag=true;
        array_push($errores, "<p style='color: red;'> La contraseña tiene que ser minimo de 6 caracteres <p>");

        
    }

    if (!$erroresflag) {
        // Comprobar la conexión
        if ($conn->connect_error) {
            die("Conexión fallida: " . $conn->connect_error);
        }
        $encontrado=false;
        // Consulta SQL para seleccionar todos los empleados
        $sql = "SELECT ID_usuario,email, contraseña, nombre, apellidos, Telefono,Calle,rol FROM usuario";

        // Ejecutar la consulta
        $result = $conn->query($sql);

        // Comprobar si hay resultados
        if ($result->num_rows > 0) {
            // Imprimir los datos de cada fila
            while ($row = $result->fetch_assoc()) { //Obtiene cada fila como un array asociativo
                if ($row["email"]==$_POST["mail"] && $row["contraseña"]==$_POST["contraseña"]) {
                    $encontrado=true;
                    session_start();
                    $_SESSION['id_Usuario'] = $row['ID_usuario'];
                    $_SESSION['rol'] = $row["rol"];
                    header("Location:index.php");
                }
            }
        } else {
            echo "No se encontraron resultados.";
        }

        if (!$encontrado) {
            array_push($errores, "<p style='color: red;'> No se ha encontrado el usuario indicado<p>");
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