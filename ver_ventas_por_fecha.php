<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'admin') {
  header("Location: login.php");
  exit();
}

include("conexion.php");

$ventas = [];
$total = 0;

if (isset($_GET['fecha']) && $_GET['fecha'] !== '') {
    $fecha = $_GET['fecha'];

    $consulta = $conexion->prepare("
      SELECT v.id_venta, v.total AS total_venta, v.fecha, 
             u.nombre_completo AS empleado, 
             p.nombre_producto, p.color, p.talla, 
             dv.cantidad, dv.precio_unitario
      FROM ventas v
      INNER JOIN usuarios u ON v.id_usuario = u.id_usuario
      INNER JOIN detalle_venta dv ON v.id_venta = dv.id_venta
      INNER JOIN productos p ON dv.id_producto = p.id_producto
      WHERE DATE(v.fecha) = ?
      ORDER BY v.id_venta ASC
    ");
    $consulta->bind_param("s", $fecha);
    $consulta->execute();
    $resultado = $consulta->get_result();

    while ($fila = $resultado->fetch_assoc()) {
        $ventas[] = $fila;
        $total += $fila['precio_unitario'] * $fila['cantidad'];
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Ventas Detalladas del Día - Luvadak</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
  <div class="card shadow p-4">
    <h2 class="text-center mb-4">📊 Ventas Detalladas del Día</h2>

    <?php if (isset($_GET['fecha'])): ?>
      <h5>📌 Fecha: <strong><?= htmlspecialchars($_GET['fecha']) ?></strong></h5>
      <h5>💵 Total Ganado: <strong>S/ <?= number_format($total, 2) ?></strong></h5>

      <?php if (count($ventas) > 0): ?>
        <table class="table table-bordered table-hover mt-3">
          <thead class="table-dark text-center">
            <tr>
              <th>ID Venta</th>
              <th>Empleado</th>
              <th>Producto</th>
              <th>Color</th>
              <th>Talla</th>
              <th>Cantidad</th>
              <th>Precio Unitario</th>
              <th>Subtotal</th>
              <th>Fecha y Hora</th>
            </tr>
          </thead>
          <tbody class="text-center">
            <?php foreach ($ventas as $venta): ?>
              <tr>
                <td><?= $venta['id_venta'] ?></td>
                <td><?= $venta['empleado'] ?></td>
                <td><?= $venta['nombre_producto'] ?></td>
                <td><?= $venta['color'] ?></td>
                <td><?= $venta['talla'] ?></td>
                <td><?= $venta['cantidad'] ?></td>
                <td>S/ <?= number_format($venta['precio_unitario'], 2) ?></td>
                <td>S/ <?= number_format($venta['precio_unitario'] * $venta['cantidad'], 2) ?></td>
                <td><?= $venta['fecha'] ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php else: ?>
        <div class="alert alert-warning mt-3">No hay ventas registradas para esa fecha.</div>
      <?php endif; ?>
    <?php else: ?>
      <div class="alert alert-danger">No se ha seleccionado una fecha válida.</div>
    <?php endif; ?>

    <a href="reporte_dia.php" class="btn btn-secondary mt-4">← Volver al Reporte Diario</a>
  </div>
</div>
</body>
</html>
