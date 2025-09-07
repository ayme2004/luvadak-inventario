<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'admin') {
    header("Location: login.php");
    exit();
}

require_once 'libs/dompdf/autoload.inc.php'; 

use Dompdf\Dompdf;
use Dompdf\Options;

include("conexion.php");

if (!isset($_GET['fecha'])) {
    die("Fecha no especificada.");
}

$fecha = $_GET['fecha'];

$query = $conexion->prepare("
    SELECT v.id_venta, v.total, v.fecha, u.nombre_completo AS empleado
    FROM ventas v
    JOIN usuarios u ON v.id_usuario = u.id_usuario
    WHERE DATE(v.fecha) = ?
");
$query->bind_param("s", $fecha);
$query->execute();
$resultado = $query->get_result();

$html = "
<h2 style='text-align:center;'>🧾 Reporte de Ventas del Día: $fecha</h2>
<table width='100%' border='1' cellspacing='0' cellpadding='5'>
  <thead>
    <tr>
      <th>ID Venta</th>
      <th>Empleado</th>
      <th>Total (S/)</th>
      <th>Fecha y Hora</th>
    </tr>
  </thead>
  <tbody>
";

$total_dia = 0;

while ($fila = $resultado->fetch_assoc()) {
    $html .= "
    <tr>
      <td>{$fila['id_venta']}</td>
      <td>{$fila['empleado']}</td>
      <td>" . number_format($fila['total'], 2) . "</td>
      <td>{$fila['fecha']}</td>
    </tr>";
    $total_dia += $fila['total'];
}

$html .= "
  </tbody>
</table>
<br>
<h3 style='text-align:right;'>Total del día: <strong>S/ " . number_format($total_dia, 2) . "</strong></h3>
";

$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$dompdf->stream("reporte_ventas_$fecha.pdf", ["Attachment" => false]);
exit;
