<?php

$conexion = new mysqli("localhost", "root", "", "tienda_juan");

if($conexion->connect_errno) {
    die("Conexion Fallida" . $conexion->connect_errno);
} else {
    echo "Conectado";
}

?>