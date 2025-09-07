<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

include("conexion.php");

$id_usuario = $_SESSION['id_usuario'];
$total = $_POST['total'];
$id_cliente = $_POST['id_cliente'];

if (empty($id_cliente) && !empty($_POST['nuevo_nombre'])) {
    $nuevo_nombre = $_POST['nuevo_nombre'];
    $nuevo_dni = $_POST['nuevo_dni'];
    $nuevo_telefono = $_POST['nuevo_telefono'];
    $nuevo_correo = $_POST['nuevo_correo'];
    $nuevo_direccion = $_POST['nuevo_direccion'];

    $stmt = $conexion->prepare("INSERT INTO clientes (nombre_completo, dni, telefono, correo, direccion) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $nuevo_nombre, $nuevo_dni, $nuevo_telefono, $nuevo_correo, $nuevo_direccion);
    $stmt->execute();
    $id_cliente = $stmt->insert_id;
}

if (empty($id_cliente) || $total <= 0) {
    echo "<script>alert('❌ Datos inválidos.'); window.location='punto_venta.php';</script>";
    exit();
}

$stmt = $conexion->prepare("INSERT INTO ventas (id_usuario, id_cliente, total) VALUES (?, ?, ?)");
$stmt->bind_param("iid", $id_usuario, $id_cliente, $total);
$stmt->execute();
$id_venta = $stmt->insert_id;

foreach ($_POST['id_producto'] as $i => $id_producto) {
    $cantidad = $_POST['cantidad'][$i];
    $precio = $_POST['precio_unitario'][$i];

    $detalle = $conexion->prepare("INSERT INTO detalle_venta (id_venta, id_producto, cantidad, precio_unitario) VALUES (?, ?, ?, ?)");
    $detalle->bind_param("iiid", $id_venta, $id_producto, $cantidad, $precio);
    $detalle->execute();

    $conexion->query("UPDATE productos SET stock = stock - $cantidad WHERE id_producto = $id_producto");
}

echo "<script>alert('✅ Venta realizada con éxito'); window.location='punto_venta.php';</script>";
?>
