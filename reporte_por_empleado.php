<?php
include("conexion.php");

if (!isset($_GET['id_empleado']) || !is_numeric($_GET['id_empleado'])) {
    header("Location: buscar_empleado_reporte.php");
    exit();
}

$id_empleado = intval($_GET['id_empleado']);
$empleado = $conexion->query("SELECT nombre_completo FROM usuarios WHERE id_usuario = $id_empleado")->fetch_assoc();

$q = $conexion->query("
    SELECT 
        v.fecha, 
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

/* Normalizamos a array para poder calcular resumen y pintar tabla */
$rows = [];
$suma_total = 0;
$unidades = 0;
if ($q && $q->num_rows > 0) {
  while ($r = $q->fetch_assoc()) {
    $rows[] = $r;
    $suma_total += (float)$r['total'];
    $unidades   += (int)$r['cantidad'];
  }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Reporte de Ventas del Empleado</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  <style>
    /* ====== Variables pastel (coherentes con tus otros diseños) ====== */
    :root{
      --bg:#f8fafc;
      --panel:#ffffff;
      --text:#0f172a;
      --muted:#667085;
      --border:#e6e9f2;

      --brand:#7c3aed;     /* lila */
      --brand2:#00d4ff;    /* celeste */
      --ring:rgba(124,58,237,.22);

      --radius:12px;
      --radius-lg:16px;
      --shadow:0 6px 16px rgba(16,24,40,.08);
    }
    body{
      background:
        radial-gradient(900px 520px at 110% -10%, rgba(124,58,237,.06), transparent 45%),
        var(--bg);
      color:var(--text);
      font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, sans-serif;
    }

    .wrap{ max-width:1200px; margin:32px auto; padding:0 16px }

    /* ====== Hero ====== */
    .hero{
      display:flex; align-items:center; gap:12px;
      background:linear-gradient(180deg, rgba(255,255,255,.88), rgba(255,255,255,.96));
      border:1px solid var(--border); border-radius:var(--radius-lg);
      padding:14px 16px; box-shadow:var(--shadow); margin-bottom:14px;
    }
    .hero .icon{
      width:38px;height:38px;border-radius:12px;display:grid;place-items:center;color:#fff;
      background:linear-gradient(135deg, var(--brand), var(--brand2));
      box-shadow:0 10px 24px rgba(124,58,237,.22);
      font-size:1.05rem;
    }
    .hero .title{ font-weight:800; font-size:1.2rem }
    .chip{
      display:inline-flex; align-items:center; gap:6px; padding:6px 10px; border-radius:999px;
      border:1px solid #e5e7ff; background:#f1f5ff; color:#1e3a8a; font-weight:700; font-size:.85rem;
    }

    /* ====== Bloque panel ====== */
    .panel{
      background:var(--panel); border:1px solid var(--border); border-radius:var(--radius-lg);
      box-shadow:var(--shadow); padding:18px;
    }

    /* Summary pills */
    .summary{ display:flex; flex-wrap:wrap; gap:8px; margin:6px 0 14px }
    .pill{
      padding:6px 10px; border-radius:999px; font-weight:700; font-size:.85rem;
      background:linear-gradient(135deg,#f0ecff,#e6f9ff); color:#0f172a; border:1px solid #edf0ff;
    }

    /* Tabla moderna */
    .table{
      border:1px solid var(--border); border-radius:10px; overflow:hidden; background:#fff;
    }
    .table thead{ background:#f6f7fb }
    .table thead th{ font-weight:700; color:#111827; border:0 }
    .table tbody td{ border-color:#eef1f6 }
    .table-hover tbody tr:hover{ background:#fafbff }

    /* Botones */
    .btn{
      border-radius:999px; font-weight:700; border:1px solid var(--border);
    }
    .btn-primary{
      background:linear-gradient(135deg, var(--brand), var(--brand2));
      border-color:transparent; color:#fff;
      box-shadow:0 10px 22px rgba(124,58,237,.25);
    }
    .btn-secondary{ background:#fff; color:#0f172a; border-color:#e4e7ee }
    .btn-secondary:hover{ background:#f6f7fb }

    /* Utilidades */
    .text-green{ color:#15803d; }
    .total-final{ font-size:1.05rem }
  </style>
</head>
<body>
  <div class="wrap">

    <!-- Hero -->
    <div class="hero">
      <div class="icon"><i class="bi bi-clipboard-data"></i></div>
      <div class="flex-grow-1">
        <div class="title">Reporte de Ventas por Empleado</div>
        <div class="text-muted" style="font-size:.92rem">Detalle de ventas registradas por el usuario seleccionado</div>
      </div>
      <span class="chip"><i class="bi bi-person-fill"></i> <?= htmlspecialchars($empleado['nombre_completo']) ?></span>
    </div>

    <!-- Panel principal -->
    <section class="panel">
      <!-- Summary -->
      <div class="summary">
        <span class="pill">Movimientos: <strong><?= count($rows) ?></strong></span>
        <span class="pill">Unidades: <strong><?= (int)$unidades ?></strong></span>
        <span class="pill">Total vendido: <strong>S/ <?= number_format($suma_total, 2) ?></strong></span>
      </div>

      <?php if (count($rows) > 0): ?>
        <div class="table-responsive">
          <table class="table table-hover align-middle text-center">
            <thead>
              <tr>
                <th><i class="bi bi-calendar-event"></i> Fecha</th>
                <th class="text-start"><i class="bi bi-bag-check-fill"></i> Producto</th>
                <th><i class="bi bi-rulers"></i> Talla</th>
                <th><i class="bi bi-palette-fill"></i> Color</th>
                <th><i class="bi bi-box-seam"></i> Cantidad</th>
                <th><i class="bi bi-cash-coin"></i> Precio Unitario</th>
                <th><i class="bi bi-currency-exchange"></i> Subtotal</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($rows as $row): ?>
                <tr>
                  <td><?= date("d/m/Y H:i", strtotime($row['fecha'])) ?></td>
                  <td class="text-start"><?= htmlspecialchars($row['nombre_producto']) ?></td>
                  <td><?= htmlspecialchars($row['talla']) ?></td>
                  <td><?= htmlspecialchars($row['color']) ?></td>
                  <td><?= (int)$row['cantidad'] ?></td>
                  <td>S/ <?= number_format($row['precio_unitario'], 2) ?></td>
                  <td class="text-green"><strong>S/ <?= number_format($row['total'], 2) ?></strong></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
            <tfoot class="table-secondary fw-bold text-center">
              <tr>
                <td colspan="6" class="text-end total-final">TOTAL VENDIDO</td>
                <td class="total-final">S/ <?= number_format($suma_total, 2) ?></td>
              </tr>
            </tfoot>
          </table>
        </div>
      <?php else: ?>
        <div class="alert alert-warning rounded-3">
          <i class="bi bi-exclamation-triangle-fill me-1"></i>
          Este empleado aún no ha realizado ventas.
        </div>
      <?php endif; ?>

      <div class="text-center mt-3">
        <a href="buscar_empleado_reporte.php" class="btn btn-secondary px-4">
          <i class="bi bi-arrow-left-circle me-1"></i> Volver
        </a>
      </div>
    </section>
  </div>
</body>
</html>
