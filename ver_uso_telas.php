<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'admin') {
  header("Location: login.php");
  exit();
}

include("conexion.php");

$sql = "SELECT tela, COUNT(*) AS total_productos, 
               SUM(metros_usados) AS total_metros,
               SUM(ganancia) AS total_ganancia
        FROM produccion
        GROUP BY tela
        ORDER BY total_metros DESC";

$uso = $conexion->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Uso de Telas</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
  <h3 class="mb-4">📊 Telas utilizadas en Producción</h3>
  <a href="dashboard_admin.php" class="btn btn-secondary mb-3">← Volver al Panel</a>

  <table class="table table-bordered table-hover">
    <thead class="table-dark">
      <tr>
        <th>Tela</th>
        <th>Productos fabricados</th>
        <th>Metros usados</th>
        <th>Ganancia total</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($fila = $uso->fetch_assoc()) { ?>
        <tr>
          <td><?php echo $fila['tela']; ?></td>
          <td><?php echo $fila['total_productos']; ?></td>
          <td><?php echo $fila['total_metros']; ?> m</td>
          <td>S/ <?php echo number_format($fila['total_ganancia'], 2); ?></td>
        </tr>
      <?php } ?>
    </tbody>
  </table>
</div>
</body>
</html>
