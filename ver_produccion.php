<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'admin') {
  header("Location: login.php");
  exit();
}
include("conexion.php");

$sql = "SELECT fecha_registro, producto, tela, precio_tela, metros_usados, mano_obra, otros_costos, costo_total, precio_venta, ganancia, cantidad 
        FROM produccion 
        ORDER BY fecha_registro DESC";
$res = $conexion->query($sql);

/* Cargamos a memoria para poder renderizar cards (móvil) y tabla (desktop) */
$rows = [];
if ($res) {
  while ($r = $res->fetch_assoc()) {
    $r['precio_tela']   = (float)$r['precio_tela'];
    $r['metros_usados'] = (float)$r['metros_usados'];
    $r['mano_obra']     = (float)$r['mano_obra'];
    $r['otros_costos']  = (float)$r['otros_costos'];
    $r['costo_total']   = (float)$r['costo_total'];
    $r['precio_venta']  = (float)$r['precio_venta'];
    $r['ganancia']      = (float)$r['ganancia'];
    $r['cantidad']      = (int)$r['cantidad'];
    $rows[] = $r;
  }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Historial de Producción · Luvadak</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <!-- Bootstrap + Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet"/>
  <style>
    :root{
      --bg:#f8fafc; --panel:#ffffff; --text:#0f172a; --muted:#667085; --border:#e6e9f2;
      --brand:#7c3aed; --brand2:#00d4ff; --ring:rgba(124,58,237,.22);
      --radius:14px; --radius-lg:18px; --shadow:0 12px 28px rgba(16,24,40,.08);
      --pos:#16a34a; --neg:#dc2626; --warn:#f59e0b;
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
      display:flex; gap:12px; align-items:center; flex-wrap:wrap;
      background:linear-gradient(180deg, rgba(255,255,255,.92), rgba(255,255,255,.98));
      border:1px solid var(--border); border-radius:var(--radius-lg);
      padding:16px; box-shadow:var(--shadow); margin-bottom:18px;
    }
    .hero .icon{
      width:50px;height:50px;border-radius:14px;display:grid;place-items:center;color:#fff;
      background:linear-gradient(135deg, var(--brand), var(--brand2));
      box-shadow:0 12px 24px rgba(124,58,237,.25);
      font-size:1.35rem;
    }
    .hero .title{ font-weight:800; font-size:1.25rem }
    .hero .sub{ color:var(--muted); font-size:.95rem }
    .btn{ border-radius:999px; font-weight:700; border:1px solid var(--border) }
    .btn-secondary{ background:#fff; color:#0f172a; border-color:var(--border) }
    .btn-secondary:hover{ background:#f6f7fb }

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
      padding:14px 18px; font-weight:800; display:flex; align-items:center; gap:10px; justify-content:space-between;
    }
    .block-body{ padding:0 }
    .note{ color:var(--muted); font-size:.95rem }

    /* Table card */
    .table-wrap{ padding:16px; }
    .table-card{ border:1px solid var(--border); border-radius:12px; overflow:auto; background:#fff }
    thead.sticky th{
      position:sticky; top:0; z-index:1; background:#f6f7fb; border-bottom:1px solid var(--border);
    }
    .table-hover tbody tr:hover{ background:#fafbff }
    .pill{
      display:inline-flex; align-items:center; gap:.4rem; padding:.25rem .6rem; border-radius:999px; font-weight:700; font-size:.8rem;
      border:1px solid #e6e9f2; background:#f8fafc;
    }
    .mono{ font-variant-numeric: tabular-nums; font-feature-settings: "tnum" on, "lnum" on; }

    .gain{ color:var(--pos); font-weight:800 }
    .loss{ color:var(--neg); font-weight:800 }

    /* Footer bar */
    .footer-bar{
      display:flex; justify-content:space-between; align-items:center; gap:10px; padding:14px 18px; border-top:1px solid var(--border);
      background:#fff;
    }
    .chip{
      display:inline-flex; align-items:center; gap:.5rem;
      padding:.45rem .8rem; border-radius:999px; background:#f1faf5; border:1px solid #cdebd6; color:#0a5c2b; font-weight:700;
    }

    .w-break{ word-break: break-word; }
  </style>
</head>
<body>
  <div class="wrap">
    <!-- Hero -->
    <div class="hero">
      <div class="icon"><i class="bi bi-clipboard2-check"></i></div>
      <div class="flex-grow-1">
        <div class="title">Historial de Producción Registrada</div>
        <div class="sub">Costos, metros, precios de venta y ganancia por lote</div>
      </div>
      <div class="d-none d-sm-flex">
        <a href="dashboard_admin.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i>&nbsp;Volver al Panel</a>
      </div>
    </div>

    <!-- Tabla / Cards -->
    <section class="block">
      <div class="block-header">
        <span><i class="bi bi-boxes me-1"></i> Registros</span>
        <span class="note">Ordenado del más reciente al más antiguo</span>
      </div>

      <div class="block-body">
        <div class="table-wrap">

          <!-- ====== VISTA MÓVIL: CARDS (xs–sm) ====== -->
          <div class="d-md-none">
            <?php if (count($rows) > 0): ?>
              <div class="vstack gap-3">
                <?php foreach ($rows as $r): 
                  $gan     = $r['ganancia'];
                  $ganUnit = $gan / max($r['cantidad'], 1);
                  $claseGan = $gan >= 0 ? 'text-success fw-bold' : 'text-danger fw-bold';
                ?>
                  <div class="card border-0 shadow-sm">
                    <div class="card-body">
                      <div class="d-flex justify-content-between align-items-start">
                        <div>
                          <div class="small text-muted"><i class="bi bi-calendar3 me-1"></i><?= htmlspecialchars($r['fecha_registro']) ?></div>
                          <h6 class="mb-1"><?= htmlspecialchars($r['producto']) ?></h6>
                          <span class="badge bg-light text-dark border"><i class="bi bi-rulers me-1"></i><?= htmlspecialchars($r['tela']) ?></span>
                        </div>
                        <div class="text-end">
                          <div class="small text-muted">Ganancia lote</div>
                          <div class="<?= $claseGan ?>">S/ <?= number_format($gan,2) ?></div>
                          <div class="small text-muted">(S/ <?= number_format($ganUnit,2) ?> c/u)</div>
                        </div>
                      </div>
                      <hr>
                      <div class="row g-2 text-center">
                        <div class="col-6">
                          <div class="small text-muted">Precio Tela</div>
                          <div class="fw-semibold">S/ <?= number_format($r['precio_tela'],2) ?></div>
                        </div>
                        <div class="col-6">
                          <div class="small text-muted">Metros Usados</div>
                          <div class="fw-semibold"><?= number_format($r['metros_usados'],2) ?> m</div>
                        </div>
                        <div class="col-6">
                          <div class="small text-muted">Mano de Obra</div>
                          <div class="fw-semibold">S/ <?= number_format($r['mano_obra'],2) ?></div>
                        </div>
                        <div class="col-6">
                          <div class="small text-muted">Otros Costos</div>
                          <div class="fw-semibold">S/ <?= number_format($r['otros_costos'],2) ?></div>
                        </div>
                        <div class="col-6">
                          <div class="small text-muted">Costo Total</div>
                          <div class="fw-bold">S/ <?= number_format($r['costo_total'],2) ?></div>
                        </div>
                        <div class="col-6">
                          <div class="small text-muted">Precio Venta</div>
                          <div class="fw-semibold">S/ <?= number_format($r['precio_venta'],2) ?></div>
                          <div class="small text-muted">(x <?= $r['cantidad'] ?> uds)</div>
                        </div>
                      </div>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php else: ?>
              <div class="text-center text-muted py-4">
                <i class="bi bi-info-circle me-1"></i> No hay registros de producción aún.
              </div>
            <?php endif; ?>
          </div>

          <!-- ====== VISTA TABLET/DESKTOP: TABLA (≥ md) ====== -->
          <div class="d-none d-md-block">
            <div class="table-responsive table-card">
              <table class="table table-hover align-middle text-center m-0">
                <thead class="sticky">
                  <tr>
                    <th>🗓 Fecha</th>
                    <th>👕 Producto</th>
                    <th>🧵 Tela</th>
                    <th title="Precio por metro">Precio Tela</th>
                    <th>Metros Usados</th>
                    <th title="Costo de mano de obra total">Mano de Obra</th>
                    <th title="Otros gastos (hilo, etiquetas, etc.)">Otros Costos</th>
                    <th title="Costo total del lote">Costo Total</th>
                    <th title="Precio de venta por unidad">Precio Venta</th>
                    <th title="Ganancia total del lote">Ganancia</th>
                  </tr>
                </thead>
                <tbody>
                <?php if (count($rows) > 0): ?>
                  <?php foreach ($rows as $fila): 
                    $gan = $fila['ganancia'];
                    $claseGan = $gan >= 0 ? 'gain' : 'loss';
                    $ganUnit = $gan / max($fila['cantidad'], 1);
                  ?>
                    <tr>
                      <td class="mono"><?= htmlspecialchars($fila['fecha_registro']); ?></td>
                      <td class="w-break"><span class="pill"><i class="bi bi-tag"></i><?= htmlspecialchars($fila['producto']); ?></span></td>
                      <td class="w-break"><span class="pill"><i class="bi bi-rulers"></i><?= htmlspecialchars($fila['tela']); ?></span></td>
                      <td class="mono">S/ <?= number_format($fila['precio_tela'], 2); ?></td>
                      <td class="mono"><?= number_format($fila['metros_usados'], 2); ?> m</td>
                      <td class="mono">S/ <?= number_format($fila['mano_obra'], 2); ?></td>
                      <td class="mono">S/ <?= number_format($fila['otros_costos'], 2); ?></td>
                      <td class="mono fw-bold">S/ <?= number_format($fila['costo_total'], 2); ?></td>
                      <td class="mono">
                        <div>S/ <?= number_format($fila['precio_venta'], 2); ?></div>
                        <small class="text-muted">(x <?= (int)$fila['cantidad']; ?> uds)</small>
                      </td>
                      <td class="mono">
                        <div class="<?= $claseGan ?>">S/ <?= number_format($gan, 2); ?></div>
                        <small class="text-muted">(S/ <?= number_format($ganUnit, 2); ?> c/u)</small>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php else: ?>
                  <tr>
                    <td colspan="10" class="text-center text-muted py-4">
                      <i class="bi bi-info-circle me-1"></i> No hay registros de producción aún.
                    </td>
                  </tr>
                <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>

        </div>

        <div class="footer-bar">
          <div class="d-flex gap-2">
            <span class="chip"><i class="bi bi-check2-circle"></i> Datos listos</span>
          </div>
          <a href="dashboard_admin.php" class="btn btn-secondary d-sm-none"><i class="bi bi-arrow-left"></i>&nbsp;Volver</a>
        </div>
      </div>
    </section>
  </div>
</body>
</html>
