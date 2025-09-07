<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'admin') {
    header("Location: login.php");
    exit();
}

include("conexion.php");

$nombre = $_POST['nombre_producto'];
$descripcion = $_POST['descripcion'];
$talla = $_POST['talla'];
$color = $_POST['color'];
$precio = $_POST['precio'];
$stock = $_POST['stock'];
$id_categoria = $_POST['id_categoria'];

$stmt = $conexion->prepare("INSERT INTO productos (nombre_producto, descripcion, talla, color, precio, stock, id_categoria) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssssdii", $nombre, $descripcion, $talla, $color, $precio, $stock, $id_categoria);

if ($stmt->execute()) {
    echo "<script>alert('Producto registrado correctamente'); window.location.href='dashboard_admin.php';</script>";
} else {
    echo "Error: " . $conexion->error;
}

$stmt->close();
?>
