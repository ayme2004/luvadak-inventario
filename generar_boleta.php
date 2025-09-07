<?php
require_once 'libs/dompdf/autoload.inc.php';
use Dompdf\Dompdf;
use Dompdf\Options;

include("conexion.php");

if (!isset($_GET['id'])) {
    die("Boleta no encontrada.");
}

$id_venta = intval($_GET['id']);


$venta = $conexion->query("
    SELECT v.*, 
           u.nombre_completo AS empleado, 
           c.nombre_completo AS cliente
    FROM ventas v 
    JOIN usuarios u ON v.id_usuario = u.id_usuario
    JOIN clientes c ON v.id_cliente = c.id_cliente
    WHERE v.id_venta = $id_venta
")->fetch_assoc();

if (!$venta) {
    die("Venta no encontrada.");
}

$detalles = $conexion->query("
    SELECT d.*, p.nombre_producto 
    FROM detalle_venta d
    JOIN productos p ON d.id_producto = p.id_producto
    WHERE d.id_venta = $id_venta
");

$html = '
<style>
  body { font-family: Arial; font-size: 12px; }
  h2 { text-align: center; }
  table { width: 100%; border-collapse: collapse; margin-top: 15px; }
  th, td { border: 1px solid #aaa; padding: 6px; text-align: center; }
  .resumen { margin-top: 20px; text-align: right; }
</style>

<h2>🧾 Boleta de Venta - Luvadak</h2>
<p><strong>Cliente:</strong> ' . $venta['cliente'] . '</p>
<p><strong>Empleado:</strong> ' . $venta['empleado'] . '</p>
<p><strong>Fecha:</strong> ' . $venta['fecha'] . '</p>

<table>
  <thead>
    <tr>
      <th>Producto</th>
      <th>Precio</th>
      <th>Cantidad</th>
      <th>Subtotal</th>
    </tr>
  </thead>
  <tbody>';

while ($fila = $detalles->fetch_assoc()) {
    $subtotal = $fila['precio_unitario'] * $fila['cantidad'];
    $html .= '
      <tr>
        <td>' . $fila['nombre_producto'] . '</td>
        <td>S/ ' . number_format($fila['precio_unitario'], 2) . '</td>
        <td>' . $fila['cantidad'] . '</td>
        <td>S/ ' . number_format($subtotal, 2) . '</td>
      </tr>';
}

$html .= '
  </tbody>
</table>

<div class="resumen">
  <h4>Total: S/ ' . number_format($venta['total'], 2) . '</h4>
</div>
';

$options = new Options();
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("boleta_" . $id_venta . ".pdf", ["Attachment" => false]);
exit;
?>
