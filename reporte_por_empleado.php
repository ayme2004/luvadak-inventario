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
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  <style>
    :root{
      --bg:#f8fafc; --panel:#ffffff; --text:#0f172a; --muted:#667085; --border:#e6e9f2;
      --brand:#7c3aed; --brand2:#00d4ff; --ring:rgba(124,58,237,.22);
      --radius:12px; --radius-lg:16px; --shadow:0 6px 16px rgba(16,24,40,.08);
      --safe-top:env(safe-area-inset-top,0px); --safe-bottom:env(safe-area-inset-bottom,0px);
    }

    /* Tipografía fluida */
    .fs-fluid-sm{ font-size:clamp(.95rem,.9rem + .3vw,1.05rem) }
    .fs-fluid-md{ font-size:clamp(1.05rem,1rem + .6vw,1.25rem) }
    .fs-fluid-lg{ font-size:clamp(1.15rem,1.05rem + 1vw,1.5rem) }

    /* Base */
    *,*::before,*::after{ box-sizing:border-box }
    html,body{ height:100% }
    body{
      background:
        radial-gradient(900px 520px at 110% -10%, rgba(124,58,237,.06), transparent 45%),
        var(--bg);
      color:var(--text);
      font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, sans-serif;
      -webkit-font-smoothing:antialiased; -moz-osx-font-smoothing:grayscale;
      padding-bottom:max(10px,var(--safe-bottom));
    }

    .wrap{ max-width:1200px; margin:calc(20px + var(--safe-top)) auto 28px; padding:0 16px }

    /* Hero (sticky en móvil) */
    .hero{
      position:sticky; top:0; z-index:10;
      display:flex; align-items:center; gap:12px; flex-wrap:wrap;
      background:linear-gradient(180deg, rgba(255,255,255,.88), rgba(255,255,255,.96));
      border:1px solid var(--border); border-radius:var(--radius-lg);
      padding:12px 14px; box-shadow:var(--shadow); margin-bottom:14px;
      backdrop-filter:saturate(120%) blur(6px);
    }
    .hero .icon{
      width:40px;height:40px;border-radius:12px;display:grid;place-items:center;color:#fff;
      background:linear-gradient(135deg, var(--brand), var(--brand2));
      box-shadow:0 10px 24px rgba(124,58,237,.22);
      font-size:1.1rem;
    }
    .hero .title{ font-weight:800 }

    .chip{
      display:inline-flex; align-items:center; gap:6px; padding:6px 10px; border-radius:999px;
      border:1px solid #e5e7ff; background:#f1f5ff; color:#1e3a8a; font-weight:800; font-size:.9rem;
      white-space:nowrap;
    }

    /* Panel */
    .panel{
      background:var(--panel); border:1px solid var(--border); border-radius:var(--radius-lg);
      box-shadow:var(--shadow); padding:16px;
    }

    /* Summary pills */
    .summary{ display:flex; flex-wrap:wrap; gap:8px; margin:6px 0 14px }
    .pill{
      padding:6px 10px; border-radius:999px; font-weight:800; font-size:.9rem;
      background:linear-gradient(135deg,#f0ecff,#e6f9ff); color:#0f172a; border:1px solid #edf0ff;
    }

    /* Botones accesibles */
    .btn{
      border-radius:999px; font-weight:800; border:1px solid var(--border);
      min-height:44px; padding:.7rem 1rem; letter-spacing:.2px;
      box-shadow:0 6px 16px rgba(17,24,39,.06); transition:transform .12s ease, filter .12s ease, box-shadow .2s;
    }
    .btn:focus-visible{ outline:3px solid var(--ring); outline-offset:2px }
    .btn-primary{
      background:linear-gradient(135deg, var(--brand), var(--brand2));
      border-color:transparent; color:#fff; box-shadow:0 10px 22px rgba(124,58,237,.25);
    }
    .btn-primary:hover{ filter:brightness(1.04); transform:translateY(-2px) }
    .btn-secondary{ background:#fff; color:#0f172a; border-color:#e4e7ee }
    .btn-secondary:hover{ background:#f6f7fb; transform:translateY(-2px) }

    /* Tabla y responsive */
    .table-wrap{ border:1px solid var(--border); border-radius:12px; overflow:hidden; background:#fff }
    .table{ margin:0 }
    .table thead{ background:#f6f7fb; position:sticky; top:0; z-index:1 } /* cabecera fija en móvil */
    .table thead th{ font-weight:800; color:#111827; border:0; white-space:nowrap }
    .table tbody td{ border-color:#eef1f6; vertical-align:middle }
    .table-hover tbody tr:hover{ background:#fafbff }
    .table-responsive{ scrollbar-width:thin }
    .table-responsive::-webkit-scrollbar{ height:8px }
    .table-responsive::-webkit-scrollbar-thumb{ background:#d9def0; border-radius:999px }

    /* Tarjetas móviles (en xs–sm mostramos cards para lectura rápida) */
    @media (max-width: 575.98px){
      .mobile-cards .card{ border:1px solid var(--border); border-radius:12px }
      .mobile-cards .kv{ display:flex; justify-content:space-between; gap:10px; font-size:.95rem }
      .mobile-cards .kv .k{ color:#64748b }
      .mobile-cards .total{ color:#15803d; font-weight:800 }
    }

    .text-green{ color:#15803d; }
    .total-final{ font-size:1.05rem }
  </style>
</head>
<body>
  <div class="wrap">

    <!-- Hero -->
    <div class="hero">
      <div class="icon" aria-hidden="true"><i class="bi bi-clipboard-data"></i></div>
      <div class="flex-grow-1">
        <div class="title fs-fluid-lg">Reporte de Ventas por Empleado</div>
        <div class="text-muted fs-fluid-sm">Detalle de ventas registradas por el usuario seleccionado</div>
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

        <!-- ===== Vista móvil (cards) ===== -->
        <div class="d-md-none mobile-cards vstack gap-3">
          <?php foreach ($rows as $row): ?>
            <div class="card shadow-sm">
              <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-1">
                  <div>
                    <div class="small text-muted"><i class="bi bi-calendar-event me-1"></i><?= date("d/m/Y H:i", strtotime($row['fecha'])) ?></div>
                    <div class="fw-bold"><?= htmlspecialchars($row['nombre_producto']) ?></div>
                  </div>
                </div>
                <div class="kv"><span class="k"><i class="bi bi-rulers me-1"></i>Talla</span><span><?= htmlspecialchars($row['talla']) ?></span></div>
                <div class="kv"><span class="k"><i class="bi bi-palette-fill me-1"></i>Color</span><span><?= htmlspecialchars($row['color']) ?></span></div>
                <div class="kv"><span class="k"><i class="bi bi-box-seam me-1"></i>Cantidad</span><span><?= (int)$row['cantidad'] ?></span></div>
                <div class="kv"><span class="k"><i class="bi bi-cash-coin me-1"></i>Precio unit.</span><span>S/ <?= number_format($row['precio_unitario'], 2) ?></span></div>
                <hr class="my-2">
                <div class="d-flex justify-content-between">
                  <span class="k">Subtotal</span>
                  <span class="total">S/ <?= number_format($row['total'], 2) ?></span>
                </div>
              </div>
            </div>
          <?php endforeach; ?>

          <div class="alert alert-light border mt-1 mb-0 d-flex justify-content-between">
            <strong>Total vendidos:</strong>
            <strong>S/ <?= number_format($suma_total, 2) ?></strong>
          </div>
        </div>

        <!-- ===== Vista tablet/desktop (tabla) ===== -->
        <div class="d-none d-md-block table-wrap">
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
