<?php
require_once 'libs/dompdf/autoload.inc.php';
use Dompdf\Dompdf;
use Dompdf\Options;

include("conexion.php");

if (!isset($_GET['id_empleado'])) {
    die("Empleado no especificado.");
}

$id_empleado = $_GET['id_empleado'];

$empleado = $conexion->query("SELECT nombre_completo FROM usuarios WHERE id_usuario = $id_empleado")->fetch_assoc();
$nombre_empleado = $empleado['nombre_completo'];
$hoy = date("Y-m-d");

$ventas = $conexion->query("
    SELECT id_venta, total, fecha_venta
    FROM ventas
    WHERE id_usuario = $id_empleado AND DATE(fecha_venta) = '$hoy'
");

$movimientos = $conexion->query("
    SELECT m.tipo_movimiento, m.cantidad, p.nombre_producto, m.fecha_movimiento
    FROM movimientosinventario m
    INNER JOIN productos p ON m.id_producto = p.id_producto
    WHERE m.id_usuario = $id_empleado AND DATE(m.fecha_movimiento) = '$hoy'
");

$html = '
  <style>
    body { font-family: Arial, sans-serif; }
    h2, h3 { text-align: center; color: #2d2d2d; }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 15px;
    }
    th, td {
      border: 1px solid #aaa;
      padding: 6px;
      font-size: 12px;
      text-align: center;
    }
    th {
      background-color: #f2f2f2;
    }
    .seccion {
      margin-top: 25px;
    }
  </style>

  <h2>Reporte Diario - ' . $nombre_empleado . '</h2>
  <h3>Fecha: ' . $hoy . '</h3>

  <div class="seccion">
    <h4>🛒 Ventas del día</h4>';

if ($ventas->num_rows > 0) {
    $html .= '
    <table>
      <thead>
        <tr>
          <th>ID Venta</th>
          <th>Total (S/)</th>
          <th>Fecha</th>
        </tr>
      </thead>
      <tbody>';
    while ($venta = $ventas->fetch_assoc()) {
        $html .= '
        <tr>
          <td>' . $venta['id_venta'] . '</td>
          <td>' . number_format($venta['total'], 2) . '</td>
          <td>' . $venta['fecha_venta'] . '</td>
        </tr>';
    }
    $html .= '</tbody></table>';
} else {
    $html .= '<p>No se registraron ventas hoy.</p>';
}

$html .= '</div><div class="seccion">
  <h4>📦 Movimientos del día</h4>';

if ($movimientos->num_rows > 0) {
    $html .= '
    <table>
      <thead>
        <tr>
          <th>Producto</th>
          <th>Tipo</th>
          <th>Cantidad</th>
          <th>Fecha</th>
        </tr>
      </thead>
      <tbody>';
    while ($mov = $movimientos->fetch_assoc()) {
        $html .= '
        <tr>
          <td>' . $mov['nombre_producto'] . '</td>
          <td>' . ucfirst($mov['tipo_movimiento']) . '</td>
          <td>' . $mov['cantidad'] . '</td>
          <td>' . $mov['fecha_movimiento'] . '</td>
        </tr>';
    }
    $html .= '</tbody></table>';
} else {
    $html .= '<p>No se registraron movimientos hoy.</p>';
}

$html .= '</div>';

$options = new Options();
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$dompdf->stream("reporte_" . strtolower(str_replace(" ", "_", $nombre_empleado)) . ".pdf", ["Attachment" => false]);
exit;
?>
