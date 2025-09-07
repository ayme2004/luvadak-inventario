<?php
include("conexion.php");

$producto = $_POST['producto'];
$descripcion = $_POST['descripcion']; 
$color = $_POST['color'];            
$id_tela = intval($_POST['id_tela']);
$cantidad = intval($_POST['cantidad_productos']);
$metros_usados = floatval($_POST['metros_usados']);
$mano_obra = floatval($_POST['mano_obra']);
$otros_costos = floatval($_POST['otros_costos']);
$precio_venta = floatval($_POST['precio_venta']);
$talla = $_POST['talla'];
$id_categoria = 1; 

$tela = $conexion->query("SELECT nombre_tela, precio_por_metro, metros_disponibles FROM telas WHERE id_tela = $id_tela")->fetch_assoc();

if (!$tela || $tela['metros_disponibles'] < $metros_usados) {
    echo "<script>alert('❌ No hay suficiente tela disponible'); window.location.href='registrar_produccion_tela.php';</script>";
    exit();
}

$precio_tela = $tela['precio_por_metro'];
$costo_total = ($precio_tela * $metros_usados) + $mano_obra + $otros_costos;
$ganancia_total = ($precio_venta * $cantidad) - $costo_total;

$stmt = $conexion->prepare("INSERT INTO produccion 
    (producto, tela, precio_tela, metros_usados, mano_obra, otros_costos, costo_total, precio_venta, ganancia, cantidad, talla) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssdddddddss", $producto, $tela['nombre_tela'], $precio_tela, $metros_usados, $mano_obra, $otros_costos, $costo_total, $precio_venta, $ganancia_total, $cantidad, $talla);
$stmt->execute();

$conexion->query("UPDATE telas SET metros_disponibles = metros_disponibles - $metros_usados WHERE id_tela = $id_tela");

$insertar = $conexion->prepare("INSERT INTO productos 
    (nombre_producto, descripcion, talla, color, precio, stock, id_categoria) 
    VALUES (?, ?, ?, ?, ?, ?, ?)");
$insertar->bind_param("ssssdii", $producto, $descripcion, $talla, $color, $precio_venta, $cantidad, $id_categoria);
$insertar->execute();

echo "<script>alert('✅ Producción registrada y producto agregado al inventario'); window.location.href='ver_productos.php';</script>";
?>
