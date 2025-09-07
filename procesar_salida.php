<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'empleado') {
    header("Location: login.php");
    exit();
}

include("conexion.php");

$id_producto = $_POST['id_producto'];
$cantidad = intval($_POST['cantidad']);
$observaciones = $_POST['observaciones'];
$tipo = 'salida';
$id_usuario = $_SESSION['id_usuario'];

$verificar = $conexion->prepare("SELECT stock FROM productos WHERE id_producto = ?");
$verificar->bind_param("i", $id_producto);
$verificar->execute();
$resultado = $verificar->get_result();
$producto = $resultado->fetch_assoc();

if (!$producto) {
    echo "<script>alert('❌ Producto no encontrado'); window.location.href='registrar_salida.php';</script>";
    exit();
}

if ($producto['stock'] < $cantidad) {
    echo "<script>alert('❌ No hay suficiente stock disponible. Stock actual: {$producto['stock']}'); window.location.href='registrar_salida.php';</script>";
    exit();
}

$stmt = $conexion->prepare("INSERT INTO movimientosinventario (id_producto, tipo_movimiento, cantidad, observaciones, id_usuario) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("isisi", $id_producto, $tipo, $cantidad, $observaciones, $id_usuario);
$stmt->execute();

$conexion->query("UPDATE productos SET stock = stock - $cantidad WHERE id_producto = $id_producto");

echo "<script>alert('✅ Salida registrada correctamente'); window.location.href='dashboard_empleado.php';</script>";
?>
