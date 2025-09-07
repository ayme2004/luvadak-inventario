<?php
require_once 'libs/dompdf/autoload.inc.php';
use Dompdf\Dompdf;

include("conexion.php");

$id_cliente = isset($_GET['id']) ? intval($_GET['id']) : 0;
$cliente = $conexion->query("SELECT * FROM clientes WHERE id_cliente = $id_cliente")->fetch_assoc();

$sql = "
SELECT v.id_venta, v.fecha, p.nombre_producto, d.cantidad, d.precio_unitario
FROM ventas v
JOIN detalle_venta d ON v.id_venta = d.id_venta
JOIN productos p ON d.id_producto = p.id_producto
WHERE v.id_cliente = $id_cliente
ORDER BY v.fecha DESC
";
$resultado = $conexion->query($sql);

$html = "<h2>🧾 Historial de Compras - {$cliente['nombre_completo']}</h2>";
$html .= "<table border='1' cellspacing='0' cellpadding='6' width='100%'>
<tr>
  <th>Fecha</th>
  <th>ID Venta</th>
  <th>Producto</th>
  <th>Cantidad</th>
  <th>Precio Unitario</th>
  <th>Total</th>
</tr>";

$total_general = 0;
while ($row = $resultado->fetch_assoc()) {
    $total = $row['cantidad'] * $row['precio_unitario'];
    $total_general += $total;

    $html .= "<tr>
      <td>" . date("d/m/Y", strtotime($row['fecha'])) . "</td>
      <td>{$row['id_venta']}</td>
      <td>{$row['nombre_producto']}</td>
      <td>{$row['cantidad']}</td>
      <td>S/ " . number_format($row['precio_unitario'], 2) . "</td>
      <td>S/ " . number_format($total, 2) . "</td>
    </tr>";
}
$html .= "<tr>
  <td colspan='5' align='right'><strong>Total General</strong></td>
  <td><strong>S/ " . number_format($total_general, 2) . "</strong></td>
</tr>";
$html .= "</table>";

$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("historial_cliente_{$cliente['nombre_completo']}.pdf", array("Attachment" => false));
exit;

