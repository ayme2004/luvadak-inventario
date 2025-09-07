<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'empleado') {
    header("Location: login.php");
    exit();
}

require_once 'libs/dompdf/autoload.inc.php';
use Dompdf\Dompdf;
use Dompdf\Options;

include("conexion.php");

$id_usuario = $_SESSION['id_usuario'];
$usuario = $_SESSION['usuario'];
$fecha_actual = date("d/m/Y H:i:s");

$ventas = $conexion->query("
    SELECT 
        v.fecha,
        p.nombre_producto,
        p.talla,
        p.color,
        dv.cantidad,
        dv.precio_unitario,
        (dv.cantidad * dv.precio_unitario) AS subtotal
    FROM ventas v
    JOIN detalle_venta dv ON v.id_venta = dv.id_venta
    JOIN productos p ON dv.id_producto = p.id_producto
    WHERE v.id_usuario = $id_usuario
    ORDER BY v.fecha DESC
");

$html = '
<style>
  body { font-family: Arial, sans-serif; font-size: 11px; }
  h2, .info { text-align: center; }
  .info { margin-bottom: 15px; font-size: 11px; color: #444; }
  table { width: 100%; border-collapse: collapse; margin-top: 10px; }
  th, td { border: 1px solid #999; padding: 6px; font-size: 11px; }
  th { background-color: #f0f0f0; text-align: center; }
  td { text-align: center; }
  .total-final { text-align: right; margin-top: 15px; font-size: 12px; font-weight: bold; }
</style>

<h2>🧾 Reporte de Ventas del Empleado</h2>
<div class="info">
  <strong>Empleado:</strong> ' . htmlspecialchars($usuario) . '<br>
  <strong>Fecha de generación:</strong> ' . $fecha_actual . '
</div>

<table>
  <thead>
    <tr>
      <th>🗓 Fecha</th>
      <th>👕 Producto</th>
      <th>📏 Talla</th>
      <th>🎨 Color</th>
      <th>📦 Cantidad</th>
      <th>💵 Precio Unitario</th>
      <th>💰 Subtotal</th>
    </tr>
  </thead>
  <tbody>';

$total_general = 0;
while ($row = $ventas->fetch_assoc()) {
    $html .= '
    <tr>
      <td>' . date("d/m/Y H:i", strtotime($row['fecha'])) . '</td>
      <td>' . htmlspecialchars($row['nombre_producto']) . '</td>
      <td>' . htmlspecialchars($row['talla']) . '</td>
      <td>' . htmlspecialchars($row['color']) . '</td>
      <td>' . $row['cantidad'] . '</td>
      <td>S/ ' . number_format($row['precio_unitario'], 2) . '</td>
      <td>S/ ' . number_format($row['subtotal'], 2) . '</td>
    </tr>';
    $total_general += $row['subtotal'];
}

$html .= '
  </tbody>
</table>

<p class="total-final">🔢 Total Vendido: <strong>S/ ' . number_format($total_general, 2) . '</strong></p>
';

$options = new Options();
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("reporte_ventas_" . strtolower($usuario) . ".pdf", ["Attachment" => false]);
exit;
