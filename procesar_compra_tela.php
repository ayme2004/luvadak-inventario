<?php
include("conexion.php");

$tela = $_POST['nombre_tela'];
$unidad = $_POST['unidad']; 
$metros = floatval($_POST['metros_comprados']);
$precio = floatval($_POST['precio_total']);
$proveedor = $_POST['proveedor'];
$obs = $_POST['observaciones'];

$stmt = $conexion->prepare("INSERT INTO compras_telas (nombre_tela, unidad, metros_comprados, precio_total, proveedor, observaciones) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssddss", $tela, $unidad, $metros, $precio, $proveedor, $obs);
$stmt->execute();

$precio_unidad = ($metros > 0) ? ($precio / $metros) : 0;

$consulta = $conexion->prepare("SELECT id_tela FROM telas WHERE nombre_tela = ?");
$consulta->bind_param("s", $tela);
$consulta->execute();
$res = $consulta->get_result();

if ($res->num_rows > 0) {
    $conexion->query("UPDATE telas SET metros_disponibles = metros_disponibles + $metros WHERE nombre_tela = '$tela'");
} else {
    $conexion->query("INSERT INTO telas (nombre_tela, metros_disponibles, precio_por_metro) VALUES ('$tela', $metros, $precio_unidad)");
}

echo "<script>alert('✅ Compra registrada correctamente'); window.location.href='registrar_compra_tela.php';</script>";
?>
