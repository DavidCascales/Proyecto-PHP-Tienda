<?php

if ($_SESSION["rol"] == "administrador") {
    echo '<nav>
    <ul>
        <li><a href="#productos">Productos</a></li>
        <li><a href="#carrito">Carrito</a></li>
        <li><a href="#zona-administradores">Zona Administradores</a></li>
        
    
    </ul>
</nav>';
} else {
    echo '<nav>
        <ul>
            <li><a href="#productos">Productos</a></li>
            <li><a href="#carrito">Carrito</a></li>
        </ul>
    </nav>';
}

?>