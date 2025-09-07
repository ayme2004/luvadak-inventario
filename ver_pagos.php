<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'admin') {
  header("Location: login.php");
  exit();
}
include("conexion.php");

$busqueda = isset($_GET['buscar']) ? trim($_GET['buscar']) : "";
$pagos = [];
$total_pagado = 0;

if ($busqueda !== "") {
  $stmt = $conexion->prepare("
      SELECT p.fecha_pago, u.nombre_completo, p.monto
      FROM pagos_empleados p
      JOIN usuarios u ON p.id_usuario = u.id_usuario
      WHERE u.nombre_completo LIKE ?
      ORDER BY p.fecha_pago DESC
  ");
  $like = "%".$busqueda."%";
  $stmt->bind_param("s", $like);
  $stmt->execute();
  $resultado = $stmt->get_result();

  while ($fila = $resultado->fetch_assoc()) {
    $pagos[] = $fila;
    $total_pagado += (float)$fila['monto'];
  }
  $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Historial de Pagos por Empleado · Luvadak</title>
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
  <!-- Bootstrap + Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    :root{
      --bg:#f8fafc; --panel:#ffffff; --text:#0f172a; --muted:#667085; --border:#e6e9f2;
      --brand:#7c3aed; --brand2:#00d4ff; --ring:rgba(124,58,237,.22);
      --radius:14px; --radius-lg:18px; --shadow:0 12px 28px rgba(16,24,40,.08);
      --success:#16a34a; --danger:#dc2626;
      --safe-top:env(safe-area-inset-top,0px); --safe-bottom:env(safe-area-inset-bottom,0px);
    }

    /* Tipografía fluida */
    .fs-fluid-sm{ font-size:clamp(.95rem,.9rem + .3vw,1.05rem) }
    .fs-fluid-md{ font-size:clamp(1.05rem,1rem + .6vw,1.25rem) }
    .fs-fluid-lg{ font-size:clamp(1.18rem,1.05rem + 1vw,1.5rem) }

    body{
      background:
        radial-gradient(900px 520px at -10% -10%, rgba(124,58,237,.10), transparent 45%),
        radial-gradient(900px 520px at 110% 0%, rgba(0,212,255,.10), transparent 45%),
        var(--bg);
      color:var(--text);
      font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, sans-serif;
      -webkit-font-smoothing:antialiased; -moz-osx-font-smoothing:grayscale;
      padding-bottom:max(10px,var(--safe-bottom));
    }
    .wrap{ max-width:1100px; margin:calc(18px + var(--safe-top)) auto 28px; padding:0 16px }

    /* Hero (sticky en móvil) */
    .hero{
      position:sticky; top:0; z-index:10;
      display:flex; gap:12px; align-items:center; flex-wrap:wrap;
      background:linear-gradient(180deg, rgba(255,255,255,.92), rgba(255,255,255,.98));
      border:1px solid var(--border); border-radius:var(--radius-lg);
      padding:12px 14px; box-shadow:var(--shadow); margin-bottom:16px;
      backdrop-filter:saturate(120%) blur(6px);
    }
    .hero .icon{
      width:46px;height:46px;border-radius:12px;display:grid;place-items:center;color:#fff;
      background:linear-gradient(135deg, var(--brand), var(--brand2));
      box-shadow:0 12px 24px rgba(124,58,237,.25);
      font-size:1.25rem;
    }
    .hero .title{ font-weight:800 }
    .hero .sub{ color:var(--muted) }

    /* Botones accesibles */
    .btn{
      border-radius:999px; font-weight:800; border:1px solid var(--border);
      min-height:44px; padding:.7rem 1rem; letter-spacing:.2px;
      box-shadow:0 6px 16px rgba(17,24,39,.06);
      transition:transform .12s ease, filter .12s ease, box-shadow .2s, background .2s, border-color .2s;
    }
    .btn:focus-visible{ outline:3px solid var(--ring); outline-offset:2px }
    .btn-primary{
      background:linear-gradient(135deg, var(--brand), var(--brand2));
      border-color:transparent; color:#fff;
      box-shadow:0 10px 22px rgba(124,58,237,.28);
    }
    .btn-primary:hover{ filter:brightness(1.05); transform:translateY(-2px) }
    .btn-secondary{ background:#fff; color:#0f172a; border-color:var(--border) }
    .btn-secondary:hover{ background:#f6f7fb; transform:translateY(-2px) }
    .btn-danger{
      background:linear-gradient(135deg, #ef4444, #f97316);
      border-color:transparent; color:#fff;
      box-shadow:0 10px 22px rgba(239,68,68,.25);
    }
    .btn-danger:disabled{ opacity:.6; box-shadow:none; cursor:not-allowed }

    /* Blocks */
    .block{
      border:1px solid var(--border);
      border-radius:var(--radius-lg);
      background:var(--panel);
      box-shadow:var(--shadow);
      overflow:hidden;
    }
    .block-header{
      background:#fff; border-bottom:1px solid var(--border);
      padding:14px 18px; font-weight:800; display:flex; align-items:center; gap:10px; justify-content:space-between; flex-wrap:wrap;
    }
    .block-body{ padding:16px 18px }

    /* Search card (glass) */
    .search-card{
      background:linear-gradient(180deg, rgba(255,255,255,.86), rgba(255,255,255,.96));
      border:1px solid var(--border); border-radius:var(--radius-lg);
      padding:14px; box-shadow:var(--shadow); margin-bottom:14px;
    }
    .search-wrap{ display:flex; gap:10px; align-items:center; flex-wrap:wrap }
    .search-wrap .form-control{
      border:1px solid var(--border); border-radius:999px; padding:.7rem 1rem; min-height:44px;
    }
    .search-wrap .input-group-text{ background:#fff; border:0; padding-left:.25rem }
    .search-wrap .form-control:focus{ box-shadow:0 0 0 4px var(--ring); border-color:transparent }

    /* Table card */
    .table-card{ border:1px solid var(--border); border-radius:12px; overflow:hidden; background:#fff }
    thead.sticky th{
      position:sticky; top:0; z-index:1; background:#f6f7fb; border-bottom:1px solid var(--border);
      white-space:nowrap; font-weight:800;
    }
    .table-hover tbody tr:hover{ background:#fafbff }
    .table-responsive{ scrollbar-width:thin }
    .table-responsive::-webkit-scrollbar{ height:8px }
    .table-responsive::-webkit-scrollbar-thumb{ background:#d9def0; border-radius:999px }

    /* Totals bar */
    .totals{
      display:flex; justify-content:flex-end; gap:12px; flex-wrap:wrap; margin-top:12px;
    }
    .chip{
      display:inline-flex; align-items:center; gap:.5rem;
      padding:.5rem .9rem; border-radius:999px; background:#f1faf5; border:1px solid #cdebd6; color:#0a5c2b; font-weight:800;
    }
    .muted{ color:var(--muted) }

    /* ===== Mobile cards (xs–sm) ===== */
    @media (max-width: 575.98px){
      .mobile-cards .card{ border:1px solid var(--border); border-radius:12px }
      .mobile-cards .line{ display:flex; justify-content:space-between; gap:10px; font-size:.96rem }
      .mobile-cards .k{ color:#64748b }
      .mobile-cards .v-strong{ font-weight:800 }
    }

    /* Footer de acciones sticky en móvil */
    @media (max-width: 575.98px){
      .actions-sticky{
        position:sticky; bottom:0; z-index:5; padding:10px 0 2px;
        background:linear-gradient(180deg, rgba(250,251,255,.5), #fafbff);
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
  <div class="wrap">

    <!-- Hero -->
    <div class="hero">
      <div class="icon" aria-hidden="true"><i class="bi bi-journal-text"></i></div>
      <div class="flex-grow-1">
        <div class="title fs-fluid-lg">Historial de Pagos por Empleado</div>
        <div class="sub fs-fluid-sm">Consulta pagos realizados y exporta un resumen a PDF</div>
      </div>
      <div class="d-none d-sm-flex gap-2">
        <a href="dashboard_admin.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i>&nbsp;Volver</a>
      </div>
    </div>

    <!-- Buscador -->
    <section class="search-card">
      <form method="GET" class="search-wrap" id="formBuscar">
        <div class="input-group flex-grow-1">
          <span class="input-group-text"><i class="bi bi-search"></i></span>
          <input type="text" name="buscar" id="buscar" value="<?= htmlspecialchars($busqueda) ?>"
                 class="form-control" placeholder="Buscar por nombre del empleado" required>
        </div>
        <button class="btn btn-primary" type="submit"><i class="bi bi-funnel"></i>&nbsp;Filtrar</button>
        <a href="?buscar=" class="btn btn-secondary"><i class="bi bi-arrow-clockwise"></i>&nbsp;Limpiar</a>
      </form>
    </section>

    <!-- Resultados -->
    <section class="block">
      <div class="block-header">
        <span class="fs-fluid-md"><i class="bi bi-people-fill me-1"></i> Resultados</span>
        <?php if ($busqueda !== ""): ?>
          <span class="muted">Búsqueda: <strong><?= htmlspecialchars($busqueda) ?></strong></span>
        <?php endif; ?>
      </div>
      <div class="block-body">

        <?php if ($busqueda === ""): ?>
          <div class="text-center muted py-3">Ingresa un nombre para ver los pagos registrados.</div>
        <?php else: ?>

          <?php if (count($pagos) > 0): ?>

            <!-- ===== Vista móvil (cards) ===== -->
            <div class="d-md-none mobile-cards vstack gap-3">
              <?php foreach ($pagos as $p): ?>
                <div class="card shadow-sm">
                  <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-1">
                      <div class="fw-bold"><?= htmlspecialchars($p['nombre_completo']) ?></div>
                      <div class="small text-muted"><i class="bi bi-calendar-event me-1"></i><?= htmlspecialchars(date("d/m/Y", strtotime($p['fecha_pago']))) ?></div>
                    </div>
                    <div class="line mt-1">
                      <span class="k"><i class="bi bi-cash-coin me-1"></i>Monto</span>
                      <span class="v-strong">S/ <?= number_format((float)$p['monto'], 2) ?></span>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>

              <div class="alert alert-light border mt-1 mb-0 d-flex justify-content-between">
                <strong>Total pagado:</strong>
                <strong>S/ <?= number_format($total_pagado, 2) ?></strong>
              </div>

              <div class="actions-sticky mt-2 d-grid gap-2">
                <a href="exportar_pagos_empleado_pdf.php?nombre=<?= urlencode($busqueda) ?>" class="btn btn-danger">
                  <i class="bi bi-file-earmark-pdf"></i>&nbsp;Exportar PDF
                </a>
                <a href="dashboard_admin.php" class="btn btn-secondary d-sm-none"><i class="bi bi-arrow-left"></i>&nbsp;Volver</a>
              </div>
            </div>

            <!-- ===== Vista tablet/desktop (tabla) ===== -->
            <div class="d-none d-md-block">
              <div class="table-responsive table-card">
                <table class="table table-hover align-middle text-center m-0">
                  <thead class="sticky">
                    <tr>
                      <th><i class="bi bi-calendar-event"></i> Fecha de Pago</th>
                      <th><i class="bi bi-person-circle"></i> Empleado</th>
                      <th><i class="bi bi-cash-coin"></i> Monto</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($pagos as $p): ?>
                      <tr>
                        <td class="muted"><?= htmlspecialchars(date("d/m/Y", strtotime($p['fecha_pago']))) ?></td>
                        <td class="text-start"><?= htmlspecialchars($p['nombre_completo']) ?></td>
                        <td><strong>S/ <?= number_format((float)$p['monto'], 2) ?></strong></td>
                      </tr>
                    <?php endforeach; ?>
                    <tr class="table-light">
                      <td colspan="2" class="text-end fw-bold">Total Pagado</td>
                      <td class="fw-bold text-success">S/ <?= number_format($total_pagado, 2) ?></td>
                    </tr>
                  </tbody>
                </table>
              </div>

              <div class="totals">
                <span class="chip"><i class="bi bi-cash-stack"></i> Total: S/ <?= number_format($total_pagado, 2) ?></span>
                <a href="exportar_pagos_empleado_pdf.php?nombre=<?= urlencode($busqueda) ?>" class="btn btn-danger">
                  <i class="bi bi-file-earmark-pdf"></i>&nbsp;Exportar PDF
                </a>
              </div>
            </div>

          <?php else: ?>
            <div class="alert alert-warning rounded-3" role="alert">
              <i class="bi bi-exclamation-triangle-fill me-2"></i>
              No se encontraron pagos registrados para <strong><?= htmlspecialchars($busqueda) ?></strong>.
            </div>
            <div class="d-flex gap-2 flex-wrap">
              <a class="btn btn-danger disabled" aria-disabled="true">
                <i class="bi bi-file-earmark-pdf"></i>&nbsp;Exportar PDF
              </a>
              <a href="dashboard_admin.php" class="btn btn-secondary d-sm-none"><i class="bi bi-arrow-left"></i>&nbsp;Volver</a>
            </div>
          <?php endif; ?>

        <?php endif; ?>

        <div class="d-flex d-sm-none justify-content-center mt-3">
          <a href="dashboard_admin.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i>&nbsp;Volver</a>
        </div>
      </div>
    </section>
  </div>

  <script>
    // UX: bloquear Enter si input está vacío
    document.getElementById('buscar').addEventListener('keydown', (e)=>{
      if(e.key === 'Enter' && !e.shiftKey && e.target.value.trim() === '') {
        e.preventDefault();
      }
    });
  </script>
</body>
</html>
