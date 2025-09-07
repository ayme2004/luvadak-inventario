<?php
include("conexion.php");

$buscar = "";
$ventas = [];

if (isset($_GET['buscar'])) {
    $buscar = trim($_GET['buscar']);
    // Búsqueda segura con prepared statement
    $like = "%{$buscar}%";
    $sql = "
      SELECT c.nombre_completo, v.id_venta, v.fecha, v.total
      FROM clientes c
      JOIN ventas v ON c.id_cliente = v.id_cliente
      WHERE c.nombre_completo LIKE ?
      ORDER BY v.fecha DESC
    ";
    $st = $conexion->prepare($sql);
    $st->bind_param("s", $like);
    $st->execute();
    $res = $st->get_result();
    while ($row = $res->fetch_assoc()) { $ventas[] = $row; }
    $st->close();
}

// KPIs rápidos
$total_ventas  = count($ventas);
$total_monto   = array_sum(array_map(fn($r)=> (float)$r['total'], $ventas));
$primera_fecha = $total_ventas ? min(array_map(fn($r)=> strtotime($r['fecha']), $ventas)) : null;
$ultima_fecha  = $total_ventas ? max(array_map(fn($r)=> strtotime($r['fecha']), $ventas)) : null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Historial de Compras por Cliente</title>
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">

  <!-- Bootstrap + Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  <style>
    :root{
      --bg:#f7f8fb;
      --panel:#ffffff;
      --text:#0f172a;
      --muted:#6b7280;
      --border:#e7ebf3;

      --brand:#6d5dfc;
      --brand2:#22d3ee;
      --ring:rgba(109,93,252,.35);

      --ok:#16a34a;

      --shadow-sm:0 1px 6px rgba(15,23,42,.06);
      --shadow-md:0 8px 24px rgba(15,23,42,.08);
    }

    *{ box-sizing:border-box }
    html,body{ height:100% }
    body{
      font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, sans-serif;
      color:var(--text);
      background:var(--bg);
      -webkit-font-smoothing:antialiased;
      -moz-osx-font-smoothing:grayscale;
    }

    .page{ max-width:1100px; margin:24px auto 36px; padding:0 16px }

    /* ===== Hero minimal ===== */
    .hero{
      display:flex; gap:12px; align-items:center; flex-wrap:wrap;
      background:var(--panel);
      border:1px solid var(--border);
      border-radius:16px;
      padding:14px 16px;
      box-shadow:var(--shadow-sm);
    }
    .hero .ic{
      width:40px;height:40px;border-radius:12px;display:grid;place-items:center;color:#fff;
      background:linear-gradient(135deg,var(--brand),var(--brand2));
      font-size:1.1rem;
    }
    .hero h3{ margin:0; font-weight:800; font-size:1.12rem }
    .hero .sub{ color:var(--muted); font-size:.95rem }

    /* ===== Bloques generales ===== */
    .blocks{ display:flex; gap:16px; flex-direction:column }
    @media (min-width:992px){ .blocks{ flex-direction:row } }

    .block{
      background:var(--panel); border:1px solid var(--border); border-radius:16px;
      box-shadow:var(--shadow-sm); overflow:hidden; display:flex; flex-direction:column;
    }
    .block-header{ padding:12px 14px; font-weight:700; border-bottom:1px solid var(--border) }
    .block-body{ padding:14px 14px 16px }

    /* ===== Buscador móvil (bloque izquierdo) ===== */
    .block.left{ flex:1; max-width:100% }
    @media (min-width:992px){ .block.left{ display:none } } /* oculto en >= lg */

    /* ===== Buscador escritorio/tablet ===== */
    .search-desktop{ display:none }
    @media (min-width:992px){
      .search-desktop{ display:block; }
      .search-grid{
        display:grid; gap:12px;
        grid-template-columns: 1fr 380px;      /* formulario + KPIs a la derecha */
        align-items:end;
      }
      @media (min-width:1200px){
        .search-grid{ grid-template-columns: 1fr 420px; }
      }
    }

    /* Inputs */
    .form-control{
      border:1px solid var(--border); background:#fff; color:#0f172a;
      border-radius:12px; padding:.6rem .9rem;
      transition:border-color .15s, box-shadow .15s;
    }
    .form-control:focus{ border-color:#dcd7fe; box-shadow:0 0 0 4px var(--ring) }

    /* Botones */
    .btn{
      border-radius:12px; font-weight:700; letter-spacing:.2px;
      border:1px solid var(--border); min-height:44px;
      padding:.6rem 1rem; box-shadow:var(--shadow-sm);
      transition: transform .12s ease, background .2s, border-color .2s, box-shadow .2s;
    }
    .btn:focus-visible{ outline:3px solid var(--ring); outline-offset:2px }
    .btn-primary{
      color:#fff; border-color:transparent;
      background:linear-gradient(135deg,var(--brand),var(--brand2));
      box-shadow:0 8px 22px rgba(34,211,238,.18);
    }
    .btn-primary:hover{ transform:translateY(-1px); filter:brightness(1.03) }
    .btn-secondary{
      background:#fff; color:#0f172a; border-color:var(--border);
    }
    .btn-secondary:hover{ background:#f6f7fb; transform:translateY(-1px) }

    /* Grupo de botones: full en móvil, compacto en >= md */
    .btn-group-fluid{ display:flex; gap:10px; flex-wrap:wrap }
    .btn-group-fluid .btn{ flex:1 1 140px }
    @media (min-width:768px){
      .btn-group-fluid{ flex-wrap:nowrap }
      .btn-group-fluid .btn{ flex:0 0 auto }
    }

    /* KPIs */
    .kpi{
      border:1px solid var(--border); border-radius:12px; padding:12px; background:#fff;
    }
    .kpi .label{ color:var(--muted); font-size:.88rem; margin-bottom:2px }
    .kpi .value{ font-weight:800 }
    .money{ color:var(--ok); font-weight:800 }

    /* Grids para KPIs */
    .kpis{ display:grid; gap:10px; }
    .kpis--mobile{ grid-template-columns: 1fr; }
    .kpis--desktop{ grid-template-columns: 1fr 1fr 1fr; }

    /* Tabla */
    .table-wrap{ border:1px solid var(--border); border-radius:12px; overflow:hidden; background:#fff }
    table thead{ background:#f7f8fb }
    table thead th{ border:0; font-weight:700 }
    table tbody td{ border-color:#eef1f6 }

    /* Tarjetas en móvil */
    @media (max-width: 768px){
      .table-wrap table, .table-wrap thead, .table-wrap tbody, .table-wrap th, .table-wrap td, .table-wrap tr{ display:block; width:100% }
      .table-wrap thead{ display:none }
      .table-wrap tr{
        background:#fff; border:1px solid var(--border); border-radius:12px;
        box-shadow:var(--shadow-sm); padding:12px; margin-bottom:12px;
      }
      .table-wrap td{ border:none; padding:6px 0; text-align:left; white-space:normal }
      .table-wrap td::before{ content:attr(data-label); display:block; font-weight:700; color:var(--muted); margin-bottom:2px }
      tfoot{ display:block; margin-top:6px }
      tfoot tr{ display:block; border:1px solid var(--border); border-radius:12px; padding:10px 12px; box-shadow:var(--shadow-sm) }
      tfoot td{ display:flex; justify-content:space-between; border:0; padding:4px 0 }
    }

    @media (prefers-reduced-motion: reduce){
      *{ transition:none!important; animation:none!important; scroll-behavior:auto!important }
    }
  </style>
</head>
<body>
  <div class="page">
    <!-- Hero -->
    <div class="hero mb-3">
      <div class="ic"><i class="bi bi-journal-text"></i></div>
      <div class="flex-grow-1">
        <h3>Historial de Compras por Cliente</h3>
        <div class="sub">Busca por nombre y revisa sus últimas compras</div>
      </div>
      <a href="dashboard_empleado.php" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Volver al Panel
      </a>
    </div>

    <!-- ===== Buscador ESCRITORIO/TABLET (>= lg) ===== -->
    <section class="block search-desktop mb-3">
      <div class="block-header">Buscar cliente</div>
      <div class="block-body">
        <div class="search-grid">
          <!-- Col izquierda: formulario -->
          <form method="GET" class="vstack gap-2">
            <input type="text" name="buscar" class="form-control"
                   placeholder="Ej: Juan Pérez"
                   value="<?= htmlspecialchars($buscar) ?>" required>
            <div class="btn-group-fluid">
              <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i>&nbsp;Buscar</button>
              <a href="historial_clientes_empleado.php" class="btn btn-secondary">
                <i class="bi bi-arrow-clockwise"></i>&nbsp;Limpiar
              </a>
            </div>
          </form>

          <!-- Col derecha: KPIs (solo si hay búsqueda) -->
          <?php if ($buscar !== ''): ?>
            <div class="kpis kpis--desktop">
              <div class="kpi">
                <div class="label">Resultados</div>
                <div class="value"><?= number_format($total_ventas) ?></div>
              </div>
              <div class="kpi">
                <div class="label">Total comprado</div>
                <div class="value money">S/ <?= number_format($total_monto,2) ?></div>
              </div>
              <div class="kpi">
                <div class="label">Rango de fechas</div>
                <div class="value">
                  <?php if ($total_ventas): ?>
                    <?= date('d/m/Y', $primera_fecha) ?> — <?= date('d/m/Y', $ultima_fecha) ?>
                  <?php else: ?>
                    —
                  <?php endif; ?>
                </div>
              </div>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </section>

    <div class="blocks">
      <!-- ===== Buscador MÓVIL (xs–md) ===== -->
      <section class="block left">
        <div class="block-header">Buscar cliente</div>
        <div class="block-body">
          <form method="GET" class="vstack gap-2">
            <input type="text" name="buscar" class="form-control"
                   placeholder="Ej: Juan Pérez"
                   value="<?= htmlspecialchars($buscar) ?>" required>
            <div class="btn-group-fluid">
              <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i>&nbsp;Buscar</button>
              <a href="historial_clientes_empleado.php" class="btn btn-secondary">
                <i class="bi bi-arrow-clockwise"></i>&nbsp;Limpiar
              </a>
            </div>
          </form>

          <?php if ($buscar !== ''): ?>
            <div class="kpis kpis--mobile mt-2">
              <div class="kpi">
                <div class="label">Resultados</div>
                <div class="value"><?= number_format($total_ventas) ?></div>
              </div>
              <div class="kpi">
                <div class="label">Total comprado</div>
                <div class="value money">S/ <?= number_format($total_monto,2) ?></div>
              </div>
              <?php if ($total_ventas): ?>
                <div class="kpi">
                  <div class="label">Rango de fechas</div>
                  <div class="value"><?= date('d/m/Y', $primera_fecha) ?> — <?= date('d/m/Y', $ultima_fecha) ?></div>
                </div>
              <?php endif; ?>
            </div>
          <?php endif; ?>
        </div>
      </section>

      <!-- ===== Resultados ===== -->
      <section class="block right" style="flex:1 1 auto;">
        <div class="block-header">Compras encontradas</div>
        <div class="block-body">
          <?php if ($buscar === ''): ?>
            <div class="text-center text-muted py-3" style="border:1px dashed var(--border); border-radius:12px;">
              Ingresa un nombre de cliente para ver su historial.
            </div>
          <?php elseif ($total_ventas === 0): ?>
            <div class="text-center text-muted py-3" style="border:1px dashed var(--border); border-radius:12px;">
              <i class="bi bi-exclamation-triangle me-1"></i>
              No se encontraron compras para <strong><?= htmlspecialchars($buscar) ?></strong>.
            </div>
          <?php else: ?>
            <div class="table-wrap">
              <table class="table align-middle text-center m-0">
                <thead>
                  <tr>
                    <th>Cliente</th>
                    <th>ID Venta</th>
                    <th>Fecha</th>
                    <th>Total</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($ventas as $row): ?>
                    <tr>
                      <td data-label="Cliente"><?= htmlspecialchars($row['nombre_completo']) ?></td>
                      <td data-label="ID Venta">#<?= (int)$row['id_venta'] ?></td>
                      <td data-label="Fecha"><?= date("d/m/Y", strtotime($row['fecha'])) ?></td>
                      <td data-label="Total" class="money">S/ <?= number_format($row['total'], 2) ?></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
                <tfoot class="fw-bold">
                  <tr>
                    <td colspan="3" class="text-end">TOTAL</td>
                    <td class="money">S/ <?= number_format($total_monto, 2) ?></td>
                  </tr>
                </tfoot>
              </table>
            </div>
          <?php endif; ?>
        </div>
      </section>
    </div>
  </div>
</body>
</html>
