<?php
$host = "localhost";
$usuario = "root";
$contrasena = "13112004";
$basedatos = "dbluvadak";

$conexion = new mysqli($host, $usuario, $contrasena, $basedatos);

if ($conexion->connect_error) {
    die("Conexión fallida: " . $conexion->connect_error);
}
?>

