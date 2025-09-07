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

/* === Normalizamos en array SOLO para dibujar tabla y cards (sin cambiar tu lógica de negocio) === */
$rows = [];
if ($ventas_dia) {
  while ($r = $ventas_dia->fetch_assoc()) { $rows[] = $r; }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Reporte Diario / Ventas - Luvadak</title>
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />

  <!-- Bootstrap + Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  <style>
    :root{
      --bg:#f8fafc; --panel:#ffffff; --text:#0f172a; --muted:#667085; --border:#e6e9f2;
      --brand:#7c3aed; --brand2:#00d4ff; --ring:rgba(124,58,237,.22);
      --radius:14px; --radius-lg:18px; --shadow:0 2px 14px rgba(16,24,40,.08);
      --danger:#e11d48;
      --safe-top:env(safe-area-inset-top,0px); --safe-bottom:env(safe-area-inset-bottom,0px);
    }

    /* Tipografía fluida */
    .fs-fluid-sm{ font-size:clamp(.95rem,.9rem + .3vw,1.05rem) }
    .fs-fluid-md{ font-size:clamp(1.05rem,1rem + .6vw,1.25rem) }
    .fs-fluid-lg{ font-size:clamp(1.18rem,1.05rem + 1vw,1.5rem) }

    body{
      background:
        radial-gradient(900px 520px at 110% -10%, rgba(124,58,237,.08), transparent 45%),
        var(--bg);
      color:var(--text);
      font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, sans-serif;
      font-size:14px;
      -webkit-font-smoothing:antialiased; -moz-osx-font-smoothing:grayscale;
      padding-bottom:max(10px,var(--safe-bottom));
    }
    .page{ max-width:1100px; margin:calc(18px + var(--safe-top)) auto 36px; padding:0 16px }

    /* Hero (sticky en móvil) */
    .hero{
      position:sticky; top:0; z-index:10;
      display:flex; align-items:center; gap:12px; flex-wrap:wrap;
      background:linear-gradient(180deg, rgba(255,255,255,.92), rgba(255,255,255,.98));
      border:1px solid var(--border); border-radius:var(--radius-lg);
      padding:12px 14px; box-shadow:var(--shadow); margin-bottom:16px;
      backdrop-filter:saturate(120%) blur(6px);
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
      font-weight:700;
    }

    /* Inputs */
    .form-label{ font-weight:700; color:#334155; font-size:.9rem; margin-bottom:.35rem }
    .form-control, .form-select{
      border:1px solid var(--border); border-radius:12px; padding:.65rem .8rem; background:#fff; min-height:44px;
      transition:border .2s, box-shadow .2s, background .2s;
    }
    .form-control:focus, .form-select:focus{
      border-color:#d5d9e3; box-shadow:0 0 0 4px var(--ring); background:#fff;
    }

    /* Botones accesibles */
    .btn{
      border-radius:999px; font-weight:800; padding:.65rem 1rem; border:1px solid var(--border);
      display:inline-flex; align-items:center; gap:.35rem; min-height:44px;
      box-shadow:0 6px 16px rgba(17,24,39,.06);
      transition:transform .12s ease, filter .12s ease, box-shadow .2s, background .2s, border-color .2s;
    }
    .btn:focus-visible{ outline:3px solid var(--ring); outline-offset:2px }
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

    /* Tabla */
    .table-wrap{
      border:1px solid var(--border); border-radius:12px; overflow:hidden; background:#fff;
    }
    .table thead{
      background:#f6f7fb; position:sticky; top:0; z-index:1;
    }
    .table thead th{ font-weight:800; color:#111827; border:0; white-space:nowrap }
    .table tbody td{ border-color:#eef1f6; vertical-align:middle }
    .table-hover tbody tr:hover{ background:#fafbff }
    .table-responsive{ scrollbar-width:thin }
    .table-responsive::-webkit-scrollbar{ height:8px }
    .table-responsive::-webkit-scrollbar-thumb{ background:#d9def0; border-radius:999px }

    /* Alert suave */
    .alert-soft{
      border:1px solid #fde68a; background:#fffbeb; color:#92400e; border-radius:12px;
    }

    /* Footer card */
    .card-footer{
      background:#fafbff; border-top:1px solid var(--border);
      padding:12px 16px;
    }

    /* ===== Mobile cards (xs–sm) ===== */
    @media (max-width: 575.98px){
      .mobile-cards .card{ border:1px solid var(--border); border-radius:12px }
      .mobile-cards .kv{ display:flex; justify-content:space-between; gap:10px; font-size:.96rem }
      .mobile-cards .k{ color:#64748b }
      .mobile-cards .v-strong{ font-weight:800 }
      .actions-sticky{
        position:sticky; bottom:0; z-index:5; padding:10px 0 2px;
        background:linear-gradient(180deg, rgba(250,251,255,.6), #fafbff);
        backdrop-filter: blur(4px);
      }
    }

    /* Movimiento reducido */
    @media (prefers-reduced-motion: reduce){
      *{ transition:none!important; animation:none!important; scroll-behavior:auto!important }
    }
  </style>
</head>
<body>
  <div class="page">

    <!-- Hero -->
    <div class="hero">
      <div class="icon"><i class="bi bi-graph-up-arrow"></i></div>
      <div class="flex-grow-1">
        <h2 class="fs-fluid-lg">Reporte diario de ventas</h2>
        <div class="sub fs-fluid-sm">Consulta por rango, fecha exacta y exporta a PDF</div>
      </div>
    </div>

    <!-- Contenido -->
    <section class="card-modern">
      <div class="card-header">
        <div class="d-flex align-items-center gap-2 flex-wrap">
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
          <div class="col-12 col-md-3">
            <label class="form-label">Desde</label>
            <input type="date" name="fecha_desde" value="<?= $_GET['fecha_desde'] ?? '' ?>" class="form-control">
          </div>
          <div class="col-12 col-md-3">
            <label class="form-label">Hasta</label>
            <input type="date" name="fecha_hasta" value="<?= $_GET['fecha_hasta'] ?? '' ?>" class="form-control">
          </div>
          <div class="col-12 col-md-3">
            <label class="form-label">Fecha exacta</label>
            <input type="date" name="buscar_fecha" value="<?= $_GET['buscar_fecha'] ?? '' ?>" class="form-control">
          </div>
          <div class="col-12 col-md-3 d-flex align-items-end">
            <div class="d-flex gap-2 w-100">
              <button type="submit" class="btn btn-primary w-50"><i class="bi bi-search"></i> Buscar</button>
              <a href="reporte_dia.php" class="btn btn-secondary w-50"><i class="bi bi-arrow-repeat"></i> Limpiar</a>
            </div>
          </div>
        </form>

        <!-- Resultados -->
        <?php if (count($rows) > 0): ?>

          <!-- ===== Vista móvil (cards) ===== -->
          <div class="d-md-none mobile-cards vstack gap-3">
            <?php foreach ($rows as $row): ?>
              <div class="card shadow-sm">
                <div class="card-body">
                  <div class="d-flex justify-content-between align-items-start">
                    <div class="fw-bold"><i class="bi bi-calendar-event me-1"></i><?= date("d/m/Y", strtotime($row['fecha_dia'])) ?></div>
                    <span class="badge text-bg-light"><?= (int)$row['cantidad_ventas'] ?> ventas</span>
                  </div>
                  <div class="kv mt-2">
                    <span class="k">Total del día</span>
                    <span class="v-strong text-success">S/ <?= number_format($row['total_dia'], 2) ?></span>
                  </div>
                  <div class="d-grid gap-2 mt-3">
                    <a href="ver_ventas_por_fecha.php?fecha=<?= $row['fecha_dia'] ?>" class="btn btn-primary">
                      <i class="bi bi-bar-chart-line"></i> Ver detalles
                    </a>
                    <a href="exportar_pdf_dia.php?fecha=<?= $row['fecha_dia'] ?>" class="btn btn-danger" target="_blank">
                      <i class="bi bi-file-earmark-pdf"></i> Exportar PDF
                    </a>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>

          <!-- ===== Vista tablet/desktop (tabla) ===== -->
          <div class="d-none d-md-block table-wrap">
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
                  <?php foreach ($rows as $row): ?>
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
                  <?php endforeach; ?>
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
