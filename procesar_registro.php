<?php
include("conexion.php");

$nombre = $_POST['nombre_completo'];
$correo = $_POST['correo'];
$usuario = $_POST['usuario'];
$contrasena = password_hash($_POST['contrasena'], PASSWORD_BCRYPT);
$rol = $_POST['rol'];

$verificar = $conexion->prepare("SELECT * FROM usuarios WHERE usuario = ? OR correo = ?");
$verificar->bind_param("ss", $usuario, $correo);
$verificar->execute();
$resultado = $verificar->get_result();

if ($resultado->num_rows > 0) {
    echo "<script>alert('El usuario o correo ya está registrado'); window.location.href='registro.php';</script>";
    exit();
}

$stmt = $conexion->prepare("INSERT INTO usuarios (nombre_completo, correo, usuario, contrasena, rol) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("sssss", $nombre, $correo, $usuario, $contrasena, $rol);

if ($stmt->execute()) {
    echo "<script>alert('Registro exitoso'); window.location='login.php';</script>";
} else {
    echo "<script>alert('Ocurrió un error. Intenta nuevamente.'); window.location='registro.php';</script>";
}

$stmt->close();
$verificar->close();
?>
