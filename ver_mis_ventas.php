<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'empleado') {
  header("Location: login.php");
  exit();
}

include("conexion.php");

$id_usuario = (int)$_SESSION['id_usuario'];

/* Listado de ventas del empleado con productos en una sola fila
   (usamos '||' como separador para escapar seguro en la vista) */
$ventas = $conexion->query("
  SELECT
    v.id_venta,
    v.total,
    v.fecha,
    GROUP_CONCAT(CONCAT(p.nombre_producto, ' x', dv.cantidad) SEPARATOR '||') AS productos_raw
  FROM ventas v
  JOIN detalle_venta dv ON v.id_venta = dv.id_venta
  JOIN productos p ON dv.id_producto = p.id_producto
  WHERE v.id_usuario = $id_usuario
  GROUP BY v.id_venta
  ORDER BY v.fecha DESC
");
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Mis Ventas · Luvadak</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <!-- Bootstrap + Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  <style>
    :root{
      --bg:#f8fafc; --panel:#ffffff; --text:#0f172a; --muted:#667085; --border:#e6e9f2;
      --brand:#7c3aed; --brand2:#00d4ff; --ring:rgba(124,58,237,.22);
      --radius:14px; --radius-lg:18px; --shadow:0 10px 26px rgba(16,24,40,.08);
    }
    body{
      background:
        radial-gradient(900px 520px at -10% -10%, rgba(124,58,237,.10), transparent 45%),
        radial-gradient(900px 520px at 110% 0%, rgba(0,212,255,.10), transparent 45%),
        var(--bg);
      color:var(--text);
      font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, sans-serif;
    }
    .wrap{ max-width:1200px; margin:28px auto; padding:0 16px }

    /* Hero */
    .hero{
      display:flex; gap:12px; align-items:center;
      background:linear-gradient(180deg, rgba(255,255,255,.92), rgba(255,255,255,.98));
      border:1px solid var(--border); border-radius:var(--radius-lg);
      padding:16px; box-shadow:var(--shadow); margin-bottom:18px;
    }
    .hero .icon{
      width:46px;height:46px;border-radius:12px;display:grid;place-items:center;color:#fff;
      background:linear-gradient(135deg, var(--brand), var(--brand2));
      box-shadow:0 12px 24px rgba(124,58,237,.25);
      font-size:1.25rem;
    }
    .hero .title{ font-weight:800; font-size:1.25rem }
    .hero .sub{ color:var(--muted); font-size:.95rem }

    /* Block */
    .block{
      border:1px solid var(--border);
      border-radius:var(--radius);
      background:var(--panel);
      box-shadow:var(--shadow);
      overflow:hidden;
    }
    .block-header{
      background:#fff; border-bottom:1px solid var(--border);
      padding:14px 18px; font-weight:800; display:flex; align-items:center; gap:10px;
    }
    .block-body{ padding:16px 18px }

    /* Table card */
    .table-card{ border:1px solid var(--border); border-radius:12px; overflow:hidden; background:#fff }
    thead.sticky th{
      position:sticky; top:0; z-index:1; background:#f6f7fb; border-bottom:1px solid var(--border);
    }
    .table-hover tbody tr:hover{ background:#fafbff }
    .pill{
      display:inline-block; padding:.2rem .55rem; border-radius:999px; font-weight:700;
      background:#eef2ff; color:#3730a3; border:1px solid #e0e7ff; font-size:.8rem;
    }
    .total{
      font-size:1.05rem; font-weight:800; color:#065f46; background:#ecfdf5; border:1px solid #d1fae5; border-radius:999px; padding:.25rem .6rem;
    }

    /* Buttons */
    .btn{ border-radius:999px; font-weight:700; border:1px solid var(--border) }
    .btn-primary{
      background:linear-gradient(135deg, var(--brand), var(--brand2));
      border-color:transparent; color:#fff;
      box-shadow:0 10px 22px rgba(124,58,237,.28);
    }
    .btn-secondary{ background:#fff; color:#0f172a; border-color:var(--border) }
    .btn-secondary:hover{ background:#f6f7fb }

    .actions{ display:flex; gap:8px; flex-wrap:wrap }
  </style>
</head>
<body>
  <div class="wrap">

    <!-- Hero -->
    <div class="hero">
      <div class="icon"><i class="bi bi-graph-up-arrow"></i></div>
      <div class="flex-grow-1">
        <div class="title">Mis Ventas Realizadas</div>
        <div class="sub">Resumen de tus ventas con detalle de productos</div>
      </div>
      <div class="d-none d-sm-flex">
        <a href="dashboard_empleado.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i>&nbsp;Volver</a>
      </div>
    </div>

    <!-- Tabla -->
    <section class="block">
      <div class="block-header"><i class="bi bi-receipt"></i> Listado</div>
      <div class="block-body">
        <?php if ($ventas && $ventas->num_rows > 0): ?>
          <div class="table-responsive table-card">
            <table class="table table-hover align-middle text-center m-0">
              <thead class="sticky">
                <tr>
                  <th># Boleta</th>
                  <th>Fecha</th>
                  <th>Productos vendidos</th>
                  <th>Total</th>
                  <th>Acciones</th>
                </tr>
              </thead>
              <tbody>
                <?php while ($venta = $ventas->fetch_assoc()): ?>
                  <tr>
                    <td><span class="pill">#<?= (int)$venta['id_venta']; ?></span></td>
                    <td><?= htmlspecialchars(date("d/m/Y H:i", strtotime($venta['fecha']))); ?></td>
                    <td class="text-start">
                      <?php
                        $items = array_filter(explode('||', $venta['productos_raw'] ?? ''));
                        foreach ($items as $i => $txt) {
                          echo ($i ? '<br>' : '') . htmlspecialchars($txt);
                        }
                      ?>
                    </td>
                    <td><span class="total">S/ <?= number_format((float)$venta['total'], 2); ?></span></td>
                    <td class="actions">
                      <a class="btn btn-sm btn-primary" target="_blank" href="generar_boleta.php?id=<?= (int)$venta['id_venta']; ?>">
                        <i class="bi bi-filetype-pdf"></i>&nbsp;PDF
                      </a>
                    </td>
                  </tr>
                <?php endwhile; ?>
              </tbody>
            </table>
          </div>
        <?php else: ?>
          <div class="alert alert-info mb-0">
            <i class="bi bi-info-circle me-1"></i>No has realizado ninguna venta todavía.
          </div>
        <?php endif; ?>

        <div class="d-flex d-sm-none justify-content-center mt-3">
          <a href="dashboard_empleado.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i>&nbsp;Volver</a>
        </div>
      </div>
    </section>

  </div>
</body>
</html>
