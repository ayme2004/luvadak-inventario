<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'admin') {
  header("Location: login.php");
  exit();
}

include("conexion.php");

$condiciones = [];
if (!empty($_GET['fecha_desde'])) {
  $desde = $_GET['fecha_desde'];
  $condiciones[] = "DATE(v.fecha) >= '$desde'";
}
if (!empty($_GET['fecha_hasta'])) {
  $hasta = $_GET['fecha_hasta'];
  $condiciones[] = "DATE(v.fecha) <= '$hasta'";
}
if (!empty($_GET['buscar_fecha'])) {
  $buscar = $_GET['buscar_fecha'];
  $condiciones[] = "DATE(v.fecha) = '$buscar'";
}

$where = count($condiciones) > 0 ? 'WHERE ' . implode(' AND ', $condiciones) : '';

$ventas_dia = $conexion->query("
  SELECT DATE(v.fecha) AS fecha_dia, 
         SUM(v.total) AS total_dia, 
         COUNT(*) AS cantidad_ventas
  FROM ventas v
  $where
  GROUP BY DATE(v.fecha)
  ORDER BY fecha_dia DESC
");
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Reporte Diario / Ventas - Luvadak</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <!-- Bootstrap + Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  <style>
    :root{
      --bg:#f8fafc; --panel:#ffffff; --text:#0f172a; --muted:#667085; --border:#e6e9f2;
      --brand:#7c3aed; --brand2:#00d4ff; --ring:rgba(124,58,237,.22);
      --radius:14px; --radius-lg:18px; --shadow:0 2px 14px rgba(16,24,40,.08);
      --danger:#e11d48;
    }
    body{
      background:
        radial-gradient(900px 520px at 110% -10%, rgba(124,58,237,.08), transparent 45%),
        var(--bg);
      color:var(--text);
      font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, sans-serif;
      font-size:14px;
    }
    .page{ max-width:1100px; margin:28px auto 36px; padding:0 16px }

    /* Hero */
    .hero{
      display:flex; align-items:center; gap:12px;
      background:linear-gradient(180deg, rgba(255,255,255,.92), rgba(255,255,255,.98));
      border:1px solid var(--border); border-radius:var(--radius-lg);
      padding:16px; box-shadow:var(--shadow); margin-bottom:16px;
    }
    .hero .icon{
      width:42px;height:42px;border-radius:12px;display:grid;place-items:center;color:#fff;
      background:linear-gradient(135deg,var(--brand),var(--brand2));
      box-shadow:0 10px 24px rgba(124,58,237,.22);
      font-size:1.1rem;
    }
    .hero h2{ margin:0; font-weight:800; font-size:1.25rem }
    .hero .sub{ color:var(--muted) }

    /* Card principal */
    .card-modern{
      border:1px solid var(--border); border-radius:var(--radius-lg); background:var(--panel);
      box-shadow:var(--shadow); overflow:hidden;
    }
    .card-modern .card-header{
      background:#fff; border-bottom:1px solid var(--border); padding:14px 16px; font-weight:800;
    }
    .card-modern .card-body{ padding:16px }

    /* Chips filtros */
    .chip{
      border:1px solid var(--border); border-radius:999px; padding:.5rem .8rem; background:#fff;
      display:flex; align-items:center; gap:.4rem; box-shadow:0 1px 3px rgba(16,24,40,.06);
      font-weight:600;
    }

    /* Inputs */
    .form-label{ font-weight:700; color:#334155; font-size:.9rem; margin-bottom:.35rem }
    .form-control, .form-select{
      border:1px solid var(--border); border-radius:12px; padding:.55rem .7rem; background:#fff;
      transition:border .2s, box-shadow .2s, background .2s;
    }
    .form-control:focus, .form-select:focus{
      border-color:#d5d9e3; box-shadow:0 0 0 3px var(--ring); background:#fff;
    }

    /* Botones */
    .btn{
      border-radius:999px; font-weight:800; padding:.6rem 1rem; border:1px solid var(--border);
      display:inline-flex; align-items:center; gap:.35rem;
    }
    .btn-primary{
      background:linear-gradient(135deg,var(--brand),var(--brand2));
      border-color:transparent; color:#fff; box-shadow:0 6px 16px rgba(124,58,237,.22);
    }
    .btn-primary:hover{ filter:brightness(1.04); transform:translateY(-2px) }
    .btn-secondary{ background:#fff; color:#0f172a }
    .btn-secondary:hover{ background:#f9f9ff; transform:translateY(-2px) }
    .btn-danger{
      background:linear-gradient(135deg,#f43f5e,#ef4444); border-color:transparent; color:#fff;
      box-shadow:0 6px 16px rgba(244,63,94,.22);
    }
    .btn-danger:hover{ filter:brightness(1.03) }

    /* Tabla */
    .table-wrap{
      border:1px solid var(--border); border-radius:12px; overflow:hidden; background:#fff;
    }
    .table thead{
      background:#f6f7fb; position:sticky; top:0; z-index:1;
    }
    .table thead th{ font-weight:800; color:#111827; border:0 }
    .table tbody td{ border-color:#eef1f6 }
    .table-hover tbody tr:hover{ background:#fafbff }

    /* Alert suave */
    .alert-soft{
      border:1px solid #fde68a; background:#fffbeb; color:#92400e; border-radius:12px;
    }

    /* Footer card */
    .card-footer{
      background:#fafbff; border-top:1px solid var(--border);
      padding:12px 16px;
    }
  </style>
</head>
<body>
  <div class="page">

    <!-- Hero -->
    <div class="hero">
      <div class="icon"><i class="bi bi-graph-up-arrow"></i></div>
      <div class="flex-grow-1">
        <h2>Reporte diario de ventas</h2>
        <div class="sub">Consulta por rango, fecha exacta y exporta a PDF</div>
      </div>
    </div>

    <!-- Contenido -->
    <section class="card-modern">
      <div class="card-header">
        <div class="d-flex align-items-center gap-2">
          <span class="chip"><i class="bi bi-calendar2-week"></i> Filtros</span>
          <?php if(!empty($_GET['fecha_desde'])): ?>
            <span class="chip">Desde: <strong><?= htmlspecialchars($_GET['fecha_desde']) ?></strong></span>
          <?php endif; ?>
          <?php if(!empty($_GET['fecha_hasta'])): ?>
            <span class="chip">Hasta: <strong><?= htmlspecialchars($_GET['fecha_hasta']) ?></strong></span>
          <?php endif; ?>
          <?php if(!empty($_GET['buscar_fecha'])): ?>
            <span class="chip">Exacta: <strong><?= htmlspecialchars($_GET['buscar_fecha']) ?></strong></span>
          <?php endif; ?>
        </div>
      </div>

      <div class="card-body">
        <!-- Filtros -->
        <form method="GET" class="row g-3 mb-3">
          <div class="col-md-3">
            <label class="form-label">Desde</label>
            <input type="date" name="fecha_desde" value="<?= $_GET['fecha_desde'] ?? '' ?>" class="form-control">
          </div>
          <div class="col-md-3">
            <label class="form-label">Hasta</label>
            <input type="date" name="fecha_hasta" value="<?= $_GET['fecha_hasta'] ?? '' ?>" class="form-control">
          </div>
          <div class="col-md-3">
            <label class="form-label">Fecha exacta</label>
            <input type="date" name="buscar_fecha" value="<?= $_GET['buscar_fecha'] ?? '' ?>" class="form-control">
          </div>
          <div class="col-md-3 d-flex align-items-end">
            <div class="d-flex gap-2 w-100">
              <button type="submit" class="btn btn-primary w-50"><i class="bi bi-search"></i> Buscar</button>
              <a href="reporte_dia.php" class="btn btn-secondary w-50"><i class="bi bi-arrow-repeat"></i> Limpiar</a>
            </div>
          </div>
        </form>

        <!-- Resultados -->
        <?php if ($ventas_dia && $ventas_dia->num_rows > 0): ?>
          <div class="table-wrap">
            <div class="table-responsive">
              <table class="table table-hover align-middle text-center m-0">
                <thead>
                  <tr>
                    <th>📆 Fecha</th>
                    <th>💵 Total del día</th>
                    <th>🧾 N° de ventas</th>
                    <th>🔎 Detalles</th>
                    <th>📄 PDF</th>
                  </tr>
                </thead>
                <tbody>
                  <?php while ($row = $ventas_dia->fetch_assoc()): ?>
                    <tr>
                      <td class="fw-semibold"><?= date("d/m/Y", strtotime($row['fecha_dia'])) ?></td>
                      <td class="text-success fw-bold">S/ <?= number_format($row['total_dia'], 2) ?></td>
                      <td><?= (int)$row['cantidad_ventas'] ?></td>
                      <td>
                        <a href="ver_ventas_por_fecha.php?fecha=<?= $row['fecha_dia'] ?>"
                           class="btn btn-sm btn-primary">
                          <i class="bi bi-bar-chart-line"></i> Ver
                        </a>
                      </td>
                      <td>
                        <a href="exportar_pdf_dia.php?fecha=<?= $row['fecha_dia'] ?>"
                           class="btn btn-sm btn-danger" target="_blank">
                          <i class="bi bi-file-earmark-pdf"></i> PDF
                        </a>
                      </td>
                    </tr>
                  <?php endwhile; ?>
                </tbody>
              </table>
            </div>
          </div>
        <?php else: ?>
          <div class="alert alert-soft mt-2">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            No se encontraron ventas para los filtros aplicados.
          </div>
        <?php endif; ?>
      </div>

      <div class="card-footer text-center">
        <a href="dashboard_admin.php" class="btn btn-secondary">
          <i class="bi bi-arrow-left-circle"></i> Volver al Panel
        </a>
      </div>
    </section>
  </div>
</body>
</html>
