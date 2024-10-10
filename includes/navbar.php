<?php

if ($_SESSION["rol"] == "administrador") {
    echo '<nav>
    <ul>
        <li><a href="../public/index.php">Tienda</a></li>
        <li><a href="../public/carrito.php">Carrito</a></li>
        <li><a href="../admin/index.php">Zona Administradores</a></li>
        
    
    </ul>
</nav>';
} else {
    echo '<nav>
        <ul>
            <li><a href="../public/index.php">Carrito</a></li>
            <li><a href="../public/carrito.php">Carrito</a></li>
        </ul>
    </nav>';
}

?>