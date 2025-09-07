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
$total_ventas = count($ventas);
$total_monto  = array_sum(array_map(fn($r)=> (float)$r['total'], $ventas));
$primera_fecha = $total_ventas ? min(array_map(fn($r)=> strtotime($r['fecha']), $ventas)) : null;
$ultima_fecha  = $total_ventas ? max(array_map(fn($r)=> strtotime($r['fecha']), $ventas)) : null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Historial de Compras por Cliente</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <!-- Bootstrap + Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  <style>
    :root{
      --bg:#f8fafc; --panel:#ffffff; --text:#0f172a; --muted:#667085; --border:#e6e9f2;
      --brand:#7c3aed; --brand2:#00d4ff; --ring:rgba(124,58,237,.22);
      --radius:12px; --radius-lg:16px; --shadow:0 2px 12px rgba(16,24,40,.08);
      --success:#16a34a;
    }
    body{
      background:
        radial-gradient(900px 520px at 110% -10%, rgba(124,58,237,.06), transparent 45%),
        var(--bg);
      color:var(--text);
      font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, sans-serif;
    }

    .page{ max-width:1200px; margin:34px auto 40px; padding:0 16px }

    /* Hero */
    .hero{
      display:flex; align-items:center; gap:12px;
      background:linear-gradient(180deg, rgba(255,255,255,.9), rgba(255,255,255,.98));
      border:1px solid var(--border); border-radius:var(--radius-lg);
      padding:14px 16px; box-shadow:var(--shadow); margin-bottom:16px;
    }
    .hero .icon{
      width:38px;height:38px;border-radius:12px;display:grid;place-items:center;color:#fff;
      background:linear-gradient(135deg,var(--brand),var(--brand2));
      box-shadow:0 10px 24px rgba(124,58,237,.22);
    }
    .hero h3{ margin:0; font-weight:800; font-size:1.15rem }
    .hero .sub{ color:var(--muted) }

    /* Layout 2 bloques */
    .blocks{ display:flex; flex-direction:column; gap:18px }
    @media (min-width:992px){ .blocks{ flex-direction:row } }

    .block{
      border:1px solid var(--border); background:var(--panel);
      border-radius:var(--radius-lg); box-shadow:var(--shadow);
      overflow:hidden; display:flex; flex-direction:column;
    }
    .block-header{ padding:14px 16px; border-bottom:1px solid var(--border); font-weight:700 }
    .block-body{ padding:16px 16px }
    .block-footer{ padding:12px 16px; border-top:1px solid var(--border); background:#fafbff }

    .block.left{ flex:1; max-width:380px }
    .block.right{ flex:1.6 }

    /* Inputs / botones */
    .form-control{
      border:1px solid var(--border); border-radius:999px; padding:.65rem 1rem;
      transition:border .2s, box-shadow .2s, background .2s;
    }
    .form-control:focus{ border-color:#d5d9e3; box-shadow:0 0 0 3px var(--ring) }
    .btn{ border-radius:999px; font-weight:700; padding:.6rem 1rem; border:1px solid var(--border) }
    .btn-primary{
      background:linear-gradient(135deg,var(--brand),var(--brand2));
      border-color:transparent; color:#fff; box-shadow:0 6px 16px rgba(124,58,237,.22);
    }
    .btn-primary:hover{ filter:brightness(1.04); transform:translateY(-2px) }
    .btn-secondary{ background:#fff; color:var(--text) }
    .btn-secondary:hover{ background:#f9f9ff; transform:translateY(-2px) }

    /* KPI */
    .kpi{ border:1px solid var(--border); border-radius:12px; padding:12px; background:#fff }
    .kpi .label{ color:var(--muted); font-size:.9rem }
    .kpi .value{ font-weight:800; font-size:1.1rem }
    .money{ color:var(--success); font-weight:800 }

    /* Tabla */
    .table-modern{ border:1px solid var(--border); border-radius:12px; overflow:hidden }
    .table-modern thead{ background:#f6f7fb }
    .table-modern th{ border:0; font-weight:700 }
    .table-modern td{ border-color:#eef1f6 }
    .table-modern tbody tr:hover{ background:#fafbff }

    .empty{
      border:1px dashed #dbe0ef; border-radius:14px; padding:18px; text-align:center;
      color:#6b7280; background:#f9fbff;
    }
  </style>
</head>
<body>
  <div class="page">
    <!-- Hero -->
    <div class="hero">
      <div class="icon"><i class="bi bi-receipt-cutoff"></i></div>
      <div>
        <h3>Historial de Compras por Cliente</h3>
        <div class="sub">Busca por nombre y revisa sus últimas compras</div>
      </div>
      <div class="ms-auto">
        <a href="dashboard_empleado.php" class="btn btn-secondary">
          <i class="bi bi-arrow-left"></i> Volver al Panel
        </a>
      </div>
    </div>

    <div class="blocks">
      <!-- Izquierda: búsqueda + KPIs -->
      <section class="block left">
        <div class="block-header">Buscar cliente</div>
        <div class="block-body">
          <form method="GET" class="vstack gap-3">
            <div class="input-group">
              <input type="text" name="buscar" class="form-control"
                     placeholder="🔍 Ej: Juan Pérez"
                     value="<?= htmlspecialchars($buscar) ?>" required>
              <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i></button>
              <a href="historial_clientes_empleado.php" class="btn btn-secondary"><i class="bi bi-arrow-clockwise"></i></a>
            </div>
          </form>

          <?php if ($buscar !== ''): ?>
            <hr class="my-4">
            <div class="vstack gap-2">
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
                  <div class="value">
                    <?= date('d/m/Y', $primera_fecha) ?> — <?= date('d/m/Y', $ultima_fecha) ?>
                  </div>
                </div>
              <?php endif; ?>
            </div>
          <?php endif; ?>
        </div>
      </section>

      <!-- Derecha: tabla -->
      <section class="block right">
        <div class="block-header">Compras encontradas</div>
        <div class="block-body">
          <?php if ($buscar === ''): ?>
            <div class="empty">
              Ingresa un nombre de cliente para ver su historial.
            </div>
          <?php elseif ($total_ventas === 0): ?>
            <div class="empty">
              <i class="bi bi-exclamation-triangle me-1"></i>
              No se encontraron compras para <strong><?= htmlspecialchars($buscar) ?></strong>.
            </div>
          <?php else: ?>
            <div class="table-responsive table-modern">
              <table class="table align-middle text-center">
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
                      <td><?= htmlspecialchars($row['nombre_completo']) ?></td>
                      <td>#<?= (int)$row['id_venta'] ?></td>
                      <td><?= date("d/m/Y", strtotime($row['fecha'])) ?></td>
                      <td class="money">S/ <?= number_format($row['total'], 2) ?></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
                <tfoot class="table-secondary fw-bold">
                  <tr>
                    <td class="text-end" colspan="3">TOTAL</td>
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
