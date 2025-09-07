<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'admin') {
    header("Location: login.php");
    exit();
}

include("conexion.php");

$nombre = $_POST['nombre_completo'];
$correo = $_POST['correo'];
$usuario = $_POST['usuario'];
$contrasena = $_POST['contrasena'];
$rol = $_POST['rol'];
$contrasenaHash = password_hash($contrasena, PASSWORD_DEFAULT);

$sql = "INSERT INTO usuarios (nombre_completo, correo, usuario, contrasena, rol) VALUES (?, ?, ?, ?, ?)";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("sssss", $nombre, $correo, $usuario, $contrasenaHash, $rol);

if ($stmt->execute()) {
    header("Location: ver_empleados.php?msg=empleado_agregado");
    exit();
} else {
    echo "Error al guardar: " . $stmt->error;
}

$stmt->close();
$conexion->close();
?>
