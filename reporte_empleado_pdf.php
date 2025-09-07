<?php
require_once 'libs/dompdf/autoload.inc.php';
use Dompdf\Dompdf;
use Dompdf\Options;

include("conexion.php");

if (!isset($_GET['id_empleado']) || !is_numeric($_GET['id_empleado'])) {
    die("ID de empleado inválido.");
}

$id_empleado = intval($_GET['id_empleado']);
$empleado = $conexion->query("SELECT nombre_completo FROM usuarios WHERE id_usuario = $id_empleado")->fetch_assoc();

$ventas = $conexion->query("
    SELECT 
        v.fecha AS fecha_venta,
        p.nombre_producto,
        p.talla,
        p.color,
        dv.cantidad,
        dv.precio_unitario,
        (dv.cantidad * dv.precio_unitario) AS total
    FROM ventas v
    JOIN detalle_venta dv ON v.id_venta = dv.id_venta
    JOIN productos p ON dv.id_producto = p.id_producto
    WHERE v.id_usuario = $id_empleado
    ORDER BY v.fecha DESC
");

ob_start();
$hoy = date("d/m/Y H:i");
$suma_total = 0;
$cant_items  = 0;
if ($ventas && $ventas->num_rows > 0) {
    foreach ($ventas as $r) {
        $suma_total += (float)$r['total'];
        $cant_items  += (int)$r['cantidad'];
    }
    // reiniciar puntero para volver a recorrer
    $ventas->data_seek(0);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Reporte de Ventas</title>
<style>
  /* ====== Config página ====== */
  @page{ margin: 90px 36px 70px 36px; }
  body{ font-family: DejaVu Sans, Arial, sans-serif; color:#0f172a; font-size:12px; }

  /* ====== Header / Footer fijos ====== */
  .header{
    position: fixed; top: -70px; left: 0; right: 0; height: 70px;
    padding: 10px 0 0 0;
    border-bottom: 1px solid #e6e9f2;
  }
  .brand{
    font-weight: 800; font-size: 16px; color:#0f172a;
  }
  .brand-chip{
    display:inline-block; padding:4px 8px; border-radius:999px; font-weight:700;
    background:#eef2ff; color:#4338ca; border:1px solid #e0e7ff;
    font-size:11px; margin-left:8px;
  }
  .subtitle{ color:#64748b; font-size:11px; margin-top:4px }

  .footer{
    position: fixed; bottom: -50px; left:0; right:0; height:50px;
    color:#64748b; font-size:11px; border-top:1px solid #e6e9f2; padding-top:6px;
  }
  .pagenum:before{ content: counter(page); }
  .totalpages:before{ content: counter(pages); }

  /* ====== Resumen ====== */
  .summary{
    margin-top: 8px;
    border:1px solid #e6e9f2; border-radius:10px; padding:8px 10px;
    background:#ffffff;
  }
  .pill{
    display:inline-block; border:1px solid #e6e9f2; border-radius:999px; padding:4px 10px;
    margin:4px 6px 0 0; font-weight:700; font-size:11px; background:#fafbff;
  }
  .pill strong{ color:#111827 }

  /* ====== Tabla ====== */
  .table-wrap{ margin-top:12px; }
  table{ width:100%; border-collapse:collapse; table-layout:fixed; }
  thead th{
    text-align:center; font-weight:800; color:#111827; font-size:12px;
    background:#f6f7fb; padding:8px; border:1px solid #e6e9f2;
  }
  tbody td{
    text-align:center; padding:7px; border:1px solid #eef1f6; font-size:11.5px;
  }
  tbody tr:nth-child(even){ background:#fbfcff; }
  tfoot td{
    border:1px solid #e6e9f2; padding:8px; font-weight:800; background:#f3f6ff;
  }
  .text-right{ text-align:right; }
  .text-left{ text-align:left; }
  .text-green{ color:#15803d; }
  .badge-soft{
    display:inline-block; padding:3px 8px; border-radius:999px; font-weight:700; font-size:10.5px;
    background:#f1f5ff; color:#1e3a8a; border:1px solid #e5edff;
  }

  /* Separador de secciones en caso de muchas filas */
  .spacer{ height:6px; }

</style>
</head>
<body>

<!-- ===== Header ===== -->
<div class="header">
  <table style="width:100%; border-collapse:collapse">
    <tr>
      <td style="width:65%">
        <div class="brand">Luvadak
          <span class="brand-chip">Reporte de Ventas por Empleado</span>
        </div>
        <div class="subtitle">Generado: <?= htmlspecialchars($hoy) ?></div>
      </td>
      <td style="width:35%; text-align:right">
        <div class="badge-soft">Empleado: <strong><?= htmlspecialchars($empleado['nombre_completo']) ?></strong></div>
      </td>
    </tr>
  </table>
</div>

<!-- ===== Footer ===== -->
<div class="footer">
  <table style="width:100%; border-collapse:collapse">
    <tr>
      <td style="width:60%; color:#64748b">Luvadak · Reporte interno</td>
      <td style="width:40%; text-align:right; color:#64748b">
        Página <span class="pagenum"></span> de <span class="totalpages"></span>
      </td>
    </tr>
  </table>
</div>

<!-- ===== Contenido ===== -->
<main>
  <!-- Resumen -->
  <div class="summary">
    <span class="pill">Ventas registradas: <strong><?= $ventas ? $ventas->num_rows : 0 ?></strong></span>
    <span class="pill">Unidades vendidas: <strong><?= (int)$cant_items ?></strong></span>
    <span class="pill">Total vendido: <strong>S/ <?= number_format($suma_total, 2) ?></strong></span>
  </div>

  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th style="width:15%">Fecha</th>
          <th style="width:32%">Producto</th>
          <th style="width:8%">Talla</th>
          <th style="width:10%">Color</th>
          <th style="width:10%">Cantidad</th>
          <th style="width:12%">P. Unitario</th>
          <th style="width:13%">Subtotal</th>
        </tr>
      </thead>
      <tbody>
      <?php if ($ventas && $ventas->num_rows > 0): ?>
        <?php while ($row = $ventas->fetch_assoc()): ?>
          <tr>
            <td><?= date("d/m/Y H:i", strtotime($row['fecha_venta'])) ?></td>
            <td class="text-left"><?= htmlspecialchars($row['nombre_producto']) ?></td>
            <td><?= htmlspecialchars($row['talla']) ?></td>
            <td><?= htmlspecialchars($row['color']) ?></td>
            <td><?= (int)$row['cantidad'] ?></td>
            <td>S/ <?= number_format($row['precio_unitario'], 2) ?></td>
            <td class="text-green"><strong>S/ <?= number_format($row['total'], 2) ?></strong></td>
          </tr>
        <?php endwhile; ?>
      <?php else: ?>
        <tr>
          <td colspan="7" style="text-align:center; padding:14px; color:#7c3a3a;">
            No hay ventas registradas para este empleado.
          </td>
        </tr>
      <?php endif; ?>
      </tbody>
      <tfoot>
        <tr>
          <td colspan="6" class="text-right">TOTAL VENDIDO</td>
          <td><strong>S/ <?= number_format($suma_total, 2) ?></strong></td>
        </tr>
      </tfoot>
    </table>
  </div>
</main>
</body>
</html>
<?php
$html = ob_get_clean();

$options = new Options();
$options->set('isRemoteEnabled', true);
$options->set('isHtml5ParserEnabled', true);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("reporte_empleado.pdf", ["Attachment" => false]);
