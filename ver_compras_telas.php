<?php
include("conexion.php");

$busqueda = isset($_GET['buscar']) ? trim($_GET['buscar']) : '';
$mes = isset($_GET['mes']) ? $_GET['mes'] : '';
$anio = isset($_GET['anio']) ? $_GET['anio'] : '';

$condiciones = [];
if ($busqueda !== '') { $condiciones[] = "nombre_tela LIKE '%" . $conexion->real_escape_string($busqueda) . "%'"; }
if ($mes !== '')      { $condiciones[] = "MONTH(fecha_compra) = " . intval($mes); }
if ($anio !== '')     { $condiciones[] = "YEAR(fecha_compra) = " . intval($anio); }

$whereSQL = count($condiciones) ? "WHERE " . implode(" AND ", $condiciones) : "";
$res = $conexion->query("SELECT * FROM compras_telas $whereSQL ORDER BY fecha_compra DESC");

/* Cargamos a memoria para poder renderizar 2 vistas (cards/table) y calcular totales una sola vez */
$rows = [];
$sumTotal = 0.0;
if ($res) {
  while ($r = $res->fetch_assoc()) {
    $r['precio_total'] = (float)$r['precio_total'];
    $r['metros_comprados'] = (float)$r['metros_comprados'];
    $rows[] = $r;
    $sumTotal += $r['precio_total'];
  }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Historial de Compras de Telas - Luvadak</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <!-- Bootstrap + Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    :root{
      --bg:#f8fafc; --panel:#ffffff; --text:#0f172a; --muted:#667085; --border:#e6e9f2;
      --brand:#7c3aed; --brand2:#00d4ff; --radius:14px; --radius-lg:18px; --shadow:0 10px 26px rgba(16,24,40,.08);
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
    .hero{
      display:flex; align-items:center; gap:12px; flex-wrap:wrap;
      background:linear-gradient(180deg, rgba(255,255,255,.90), rgba(255,255,255,.98));
      border:1px solid var(--border); border-radius:var(--radius-lg);
      padding:16px; box-shadow:var(--shadow); margin-bottom:18px;
    }
    .hero .icon{
      width:44px;height:44px;border-radius:12px;display:grid;place-items:center;color:#fff;
      background:linear-gradient(135deg, var(--brand), var(--brand2));
      box-shadow:0 12px 24px rgba(124,58,237,.25);
      font-size:1.25rem;
    }
    .hero .title{ font-weight:800; font-size:1.25rem }
    .hero .sub{ color:var(--muted) }

    .block{ border:1px solid var(--border); border-radius:var(--radius-lg); background:var(--panel); box-shadow:var(--shadow); overflow:hidden; margin-bottom:16px }
    .block-header{ background:#fff; border-bottom:1px solid var(--border); padding:14px 18px; font-weight:800 }
    .block-body{ padding:16px 18px }
    .block-footer{ padding:12px 18px; background:#fafbff; border-top:1px solid var(--border) }

    .table-card{ border:1px solid var(--border); border-radius:12px; overflow:hidden; background:#fff }
    .badge-soft{ padding:4px 10px; border-radius:999px; font-weight:700; font-size:.78rem; background:linear-gradient(135deg,#f0ecff,#e6f9ff); color:#0f172a; border:1px solid #edf0ff }
    .empty{ border:1px dashed #dbe0ef; border-radius:14px; padding:18px; text-align:center; color:#6b7280; background:#f9fbff }
  </style>
</head>
<body>
  <div class="wrap">
    <!-- Hero -->
    <div class="hero">
      <div class="icon"><i class="bi bi-book-fill"></i></div>
      <div class="flex-grow-1">
        <div class="title">Historial de Compras de Telas</div>
        <div class="sub">Consulta, filtra y analiza tus compras de insumos</div>
      </div>

      <!-- Botón Filtros (visible en < lg) -->
      <button class="btn btn-primary d-lg-none" type="button"
              data-bs-toggle="offcanvas" data-bs-target="#offcanvasFiltros" aria-controls="offcanvasFiltros">
        <i class="bi bi-funnel-fill me-1"></i> Filtros
      </button>

      <a href="dashboard_admin.php" class="btn btn-secondary ms-auto d-none d-lg-inline-flex">
        <i class="bi bi-arrow-left-circle me-1"></i> Volver al Panel
      </a>
    </div>

    <!-- Filtros en línea (≥ lg) -->
    <section class="block d-none d-lg-block">
      <div class="block-header">Filtros</div>
      <div class="block-body">
        <form method="GET">
          <div class="row g-2">
            <div class="col-lg-6">
              <input type="text" name="buscar" class="form-control" placeholder="🔍 Buscar por nombre de tela"
                     value="<?= htmlspecialchars($busqueda) ?>">
            </div>
            <div class="col-lg-2">
              <select name="mes" class="form-select">
                <option value="">📅 Mes</option>
                <?php
                  $meses = [1=>"Enero",2=>"Febrero",3=>"Marzo",4=>"Abril",5=>"Mayo",6=>"Junio",7=>"Julio",8=>"Agosto",9=>"Septiembre",10=>"Octubre",11=>"Noviembre",12=>"Diciembre"];
                  foreach ($meses as $num=>$nombre){
                    $sel = ($mes!=='' && intval($mes)===$num) ? 'selected' : '';
                    echo "<option value=\"$num\" $sel>$nombre</option>";
                  }
                ?>
              </select>
            </div>
            <div class="col-lg-2">
              <select name="anio" class="form-select">
                <option value="">📆 Año</option>
                <?php
                  $anioActual = date("Y");
                  for($a=$anioActual;$a>=2020;$a--){
                    $sel = ($anio!=='' && intval($anio)===$a) ? 'selected' : '';
                    echo "<option value=\"$a\" $sel>$a</option>";
                  }
                ?>
              </select>
            </div>
            <div class="col-lg-2 d-grid">
              <button type="submit" class="btn btn-primary"><i class="bi bi-funnel-fill me-1"></i> Filtrar</button>
            </div>
          </div>
        </form>
      </div>
    </section>

    <!-- Offcanvas Filtros (móvil/tablet) -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasFiltros" aria-labelledby="offcanvasFiltrosLabel">
      <div class="offcanvas-header">
        <h5 id="offcanvasFiltrosLabel" class="mb-0">Filtros</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
      </div>
      <div class="offcanvas-body">
        <form method="GET" id="formFiltrosMobile" class="vstack gap-3">
          <input type="text" name="buscar" class="form-control" placeholder="🔍 Buscar por nombre de tela"
                 value="<?= htmlspecialchars($busqueda) ?>">
          <div class="d-flex gap-2">
            <select name="mes" class="form-select">
              <option value="">📅 Mes</option>
              <?php
                foreach ([1=>"Enero",2=>"Febrero",3=>"Marzo",4=>"Abril",5=>"Mayo",6=>"Junio",7=>"Julio",8=>"Agosto",9=>"Septiembre",10=>"Octubre",11=>"Noviembre",12=>"Diciembre"] as $num=>$nombre){
                  $sel = ($mes!=='' && intval($mes)===$num) ? 'selected' : '';
                  echo "<option value=\"$num\" $sel>$nombre</option>";
                }
              ?>
            </select>
            <select name="anio" class="form-select">
              <option value="">📆 Año</option>
              <?php
                $anioActual = date("Y");
                for($a=$anioActual;$a>=2020;$a--){
                  $sel = ($anio!=='' && intval($anio)===$a) ? 'selected' : '';
                  echo "<option value=\"$a\" $sel>$a</option>";
                }
              ?>
            </select>
          </div>
          <div class="d-grid gap-2">
            <button type="submit" class="btn btn-primary"><i class="bi bi-funnel-fill me-1"></i> Aplicar</button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="offcanvas">Cerrar</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Resultados -->
    <section class="block">
      <div class="block-header d-flex justify-content-between align-items-center">
        <span>Resultados</span>
        <?php if (count($rows) > 0): ?>
          <span class="badge-soft"><?= number_format(count($rows)) ?> registros</span>
        <?php endif; ?>
      </div>

      <div class="block-body">
        <?php if (count($rows) > 0): ?>

          <!-- ====== MOBILE CARDS (xs–sm) ====== -->
          <div class="d-md-none">
            <div class="vstack gap-3">
              <?php $i=1; foreach ($rows as $fila): ?>
                <?php
                  $unidad = $fila['unidad'];
                  $cantidad = (float)$fila['metros_comprados'];
                  $texto_cantidad = ($unidad === 'kilo') ? number_format($cantidad,2)." kg" : number_format($cantidad,2)." m";
                ?>
                <div class="card shadow-sm border-0">
                  <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                      <div>
                        <div class="small text-muted"># <?= $i++ ?></div>
                        <h6 class="mb-1"><?= htmlspecialchars($fila['nombre_tela']) ?></h6>
                        <div class="small text-muted">
                          <span class="me-2"><i class="bi bi-rulers me-1"></i><?= $texto_cantidad ?></span>
                          <span class="fw-semibold"><i class="bi bi-cash-coin me-1"></i>S/ <?= number_format($fila['precio_total'],2) ?></span>
                        </div>
                      </div>
                    </div>
                    <hr class="my-2">
                    <div class="small">
                      <div class="mb-1"><span class="fw-semibold">Proveedor:</span> <?= htmlspecialchars($fila['proveedor']) ?: '—' ?></div>
                      <div class="mb-1"><span class="fw-semibold">Observaciones:</span> <?= htmlspecialchars($fila['observaciones']) ?: '—' ?></div>
                      <div><span class="fw-semibold">Fecha:</span> <?= date("d/m/Y", strtotime($fila['fecha_compra'])) ?></div>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>

            <div class="alert alert-light border mt-3 mb-0 d-flex justify-content-between">
              <strong>Total compras listadas:</strong>
              <strong>S/ <?= number_format($sumTotal,2) ?></strong>
            </div>
          </div>

          <!-- ====== TABLET/DESKTOP TABLE (≥ md) ====== -->
          <div class="d-none d-md-block table-responsive table-card">
            <table class="table table-hover align-middle m-0">
              <thead class="bg-light">
                <tr class="text-center">
                  <th class="text-nowrap" style="width:48px">#</th>
                  <th class="text-start text-nowrap">Nombre de Tela</th>
                  <th class="text-nowrap">Cantidad</th>
                  <th class="text-nowrap">Precio Total (S/)</th>
                  <th class="text-nowrap">Proveedor</th>
                  <th class="text-nowrap">Observaciones</th>
                  <th class="text-nowrap">Fecha de Compra</th>
                </tr>
              </thead>
              <tbody>
                <?php $i=1; foreach ($rows as $fila): ?>
                  <?php
                    $unidad = $fila['unidad'];
                    $cantidad = (float)$fila['metros_comprados'];
                    $texto_cantidad = ($unidad === 'kilo') ? number_format($cantidad,2)." kg" : number_format($cantidad,2)." m";
                  ?>
                  <tr>
                    <td class="text-center"><?= $i++ ?></td>
                    <td class="text-start"><?= htmlspecialchars($fila['nombre_tela']) ?></td>
                    <td class="text-center"><?= $texto_cantidad ?></td>
                    <td class="fw-semibold text-center">S/ <?= number_format($fila['precio_total'],2) ?></td>
                    <td class="text-center"><?= htmlspecialchars($fila['proveedor']) ?></td>
                    <td class="text-start" style="max-width:360px"><?= htmlspecialchars($fila['observaciones']) ?></td>
                    <td class="text-center"><?= date("d/m/Y", strtotime($fila['fecha_compra'])) ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
              <tfoot class="table-light">
                <tr class="text-center">
                  <td colspan="3" class="text-end fw-bold">Total compras listadas:</td>
                  <td class="fw-bold">S/ <?= number_format($sumTotal,2) ?></td>
                  <td colspan="3"></td>
                </tr>
              </tfoot>
            </table>
          </div>

        <?php else: ?>
          <div class="empty">
            <div class="mb-1" style="font-size:1.05rem"><i class="bi bi-exclamation-triangle-fill me-1"></i>No se encontraron compras con esos filtros.</div>
            <div class="text-muted">Prueba con otro nombre, mes o año.</div>
          </div>
        <?php endif; ?>
      </div>

      <div class="block-footer d-flex justify-content-end gap-2">
        <a href="dashboard_admin.php" class="btn btn-secondary">
          <i class="bi bi-arrow-left-circle me-1"></i> Volver al Panel
        </a>
        <button class="btn btn-primary d-lg-none" type="button"
                data-bs-toggle="offcanvas" data-bs-target="#offcanvasFiltros" aria-controls="offcanvasFiltros">
          <i class="bi bi-funnel-fill me-1"></i> Filtros
        </button>
      </div>
    </section>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Cierra el offcanvas al enviar filtros en móvil/tablet
    const formMobile = document.getElementById('formFiltrosMobile');
    if (formMobile) {
      formMobile.addEventListener('submit', () => {
        const el = document.getElementById('offcanvasFiltros');
        if (el) bootstrap.Offcanvas.getOrCreateInstance(el).hide();
      });
    }
  </script>
</body>
</html>
