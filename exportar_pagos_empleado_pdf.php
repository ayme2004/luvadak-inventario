<?php
require_once 'libs/dompdf/autoload.inc.php';
use Dompdf\Dompdf;

include("conexion.php");

$nombre = isset($_GET['nombre']) ? trim($_GET['nombre']) : "";

if (empty($nombre)) {
    die("Nombre no proporcionado.");
}

$stmt = $conexion->prepare("
    SELECT p.fecha_pago, u.nombre_completo, p.monto
    FROM pagos_empleados p
    JOIN usuarios u ON p.id_usuario = u.id_usuario
    WHERE u.nombre_completo LIKE ?
    ORDER BY p.fecha_pago DESC
");

$like = "%" . $nombre . "%";
$stmt->bind_param("s", $like);
$stmt->execute();
$resultado = $stmt->get_result();

$html = "<h2 style='text-align:center;'>📄 Historial de Pagos - {$nombre}</h2>";
$html .= "<table border='1' cellpadding='8' cellspacing='0' width='100%'>
            <thead>
              <tr>
                <th>Fecha</th>
                <th>Empleado</th>
                <th>Monto (S/)</th>
              </tr>
            </thead>
            <tbody>";

$total = 0;
while ($fila = $resultado->fetch_assoc()) {
    $html .= "<tr>
                <td>" . date("d/m/Y", strtotime($fila['fecha_pago'])) . "</td>
                <td>" . htmlspecialchars($fila['nombre_completo']) . "</td>
                <td>S/ " . number_format($fila['monto'], 2) . "</td>
              </tr>";
    $total += $fila['monto'];
}

$html .= "<tr>
            <td colspan='2'><strong>Total Pagado</strong></td>
            <td><strong>S/ " . number_format($total, 2) . "</strong></td>
          </tr>";

$html .= "</tbody></table>";

$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("pagos_{$nombre}.pdf", ["Attachment" => true]);
