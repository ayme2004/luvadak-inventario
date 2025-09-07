<?php
session_start();
include("conexion.php");

$usuario = $_POST['usuario'];
$contrasena = $_POST['contrasena'];

$sql = "SELECT * FROM usuarios WHERE usuario = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("s", $usuario);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $fila = $result->fetch_assoc();

    if (password_verify($contrasena, $fila['contrasena'])) {
        $_SESSION['usuario'] = $fila['usuario'];
        $_SESSION['rol'] = $fila['rol'];
        $_SESSION['id_usuario'] = $fila['id_usuario']; 

        if ($fila['rol'] === 'admin') {
            header("Location: dashboard_admin.php");
        } else {
            header("Location: dashboard_empleado.php");
        }
        exit(); 
    } else {
        echo "<script>alert('Contraseña incorrecta'); window.location='login.php';</script>";
    }
} else {
    echo "<script>alert('Usuario no encontrado'); window.location='login.php';</script>";
}
?>
