<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'admin') {
    header("Location: login.php");
    exit();
}

include("conexion.php");

$id_usuario = $_POST['id_usuario'];
$monto = $_POST['monto'];
$fecha = $_POST['fecha_pago'];

$stmt = $conexion->prepare("INSERT INTO pagos_empleados (id_usuario, monto, fecha_pago) VALUES (?, ?, ?)");
$stmt->bind_param("ids", $id_usuario, $monto, $fecha);

if ($stmt->execute()) {
    echo "<script>alert('✅ Pago registrado correctamente'); window.location.href='reportes_admin.php';</script>";
} else {
    echo "<script>alert('❌ Error al registrar pago'); window.location.href='registrar_pago.php';</script>";
}

$stmt->close();
?>
