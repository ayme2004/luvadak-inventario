<?php
include("conexion.php");

$producto = $_POST['producto'];
$tela = $_POST['tela'];
$precio_tela = floatval($_POST['precio_tela']);
$metros_usados = floatval($_POST['metros_usados']);
$mano_obra = floatval($_POST['mano_obra']);
$otros_costos = floatval($_POST['otros_costos']);
$precio_venta = floatval($_POST['precio_venta']);

$costo_total = ($precio_tela * $metros_usados) + $mano_obra + $otros_costos;
$ganancia = $precio_venta - $costo_total;

$stmt = $conexion->prepare("INSERT INTO produccion (producto, tela, precio_tela, metros_usados, mano_obra, otros_costos, costo_total, precio_venta, ganancia) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssddddddd", $producto, $tela, $precio_tela, $metros_usados, $mano_obra, $otros_costos, $costo_total, $precio_venta, $ganancia);

if ($stmt->execute()) {
    echo "<script>alert('✅ Producción registrada exitosamente'); window.location='registrar_produccion.php';</script>";
} else {
    echo "❌ Error: " . $stmt->error;
}
$stmt->close();
?>
    