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

/* Cargamos a memoria para renderizar cards/tabla y calcular totales una vez */
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
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
  <!-- Bootstrap + Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    :root{
      --bg:#f8fafc; --panel:#ffffff; --text:#0f172a; --muted:#667085; --border:#e6e9f2;
      --brand:#7c3aed; --brand2:#00d4ff; --radius:14px; --radius-lg:18px; --shadow:0 10px 26px rgba(16,24,40,.08);
      --ring:rgba(124,58,237,.28);
      --safe-top:env(safe-area-inset-top,0px); --safe-bottom:env(safe-area-inset-bottom,0px);
    }

    /* Base */
    *,*::before,*::after{ box-sizing:border-box }
    html,body{ height:100% }
    body{
      background:
        radial-gradient(900px 520px at -10% -10%, rgba(124,58,237,.10), transparent 45%),
        radial-gradient(900px 520px at 110% 0%, rgba(0,212,255,.10), transparent 45%),
        var(--bg);
      color:var(--text);
      font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, sans-serif;
      -webkit-font-smoothing:antialiased; -moz-osx-font-smoothing:grayscale;
      padding-bottom:max(8px, var(--safe-bottom));
    }

    /* Tipografía fluida y utilidades */
    .fs-fluid-sm{ font-size:clamp(.95rem, .9rem + .4vw, 1.05rem) }
    .fs-fluid-md{ font-size:clamp(1.02rem, .95rem + .7vw, 1.2rem) }
    .fs-fluid-lg{ font-size:clamp(1.12rem, 1rem + 1vw, 1.45rem) }

    .wrap{ max-width:1200px; margin:calc(18px + var(--safe-top)) auto 28px; padding:0 16px }

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
      width:46px;height:46px;border-radius:12px;display:grid;place-items:center;color:#fff;
      background:linear-gradient(135deg, var(--brand), var(--brand2));
      box-shadow:0 12px 24px rgba(124,58,237,.25);
      font-size:1.2rem;
    }
    .hero .title{ font-weight:800 }

    /* Botones accesibles */
    .btn{
      border-radius:999px; font-weight:800; border:1px solid var(--border);
      display:inline-flex; align-items:center; gap:.45rem; letter-spacing:.2px;
      transition:transform .15s ease, filter .15s ease, box-shadow .15s ease, background .15s ease;
      box-shadow:0 4px 14px rgba(17,24,39,.06);
    }
    .btn:focus-visible{ outline:3px solid var(--ring); outline-offset:2px }
    .btn-primary{
      background:linear-gradient(135deg, var(--brand), var(--brand2));
      border-color:transparent; color:#fff; box-shadow:0 8px 18px rgba(124,58,237,.25);
    }
    .btn-primary:hover{ filter:brightness(1.05); transform:translateY(-2px) }
    .btn-secondary{ background:#fff; color:#0f172a }
    .btn-secondary:hover{ background:#f6f7fb; transform:translateY(-2px) }
    .btn-touch{ min-height:44px; padding:.7rem 1rem; font-size:1rem }
    .btn-icon{ width:44px; height:44px; padding:0; justify-content:center }

    /* Bloques */
    .block{ border:1px solid var(--border); border-radius:var(--radius-lg); background:var(--panel); box-shadow:var(--shadow); overflow:hidden; margin-bottom:16px }
    .block-header{ background:#fff; border-bottom:1px solid var(--border); padding:14px 18px; font-weight:800; display:flex; align-items:center; justify-content:space-between; gap:10px; flex-wrap:wrap }
    .block-body{ padding:16px 18px }
    .block-footer{ padding:12px 18px; background:#fafbff; border-top:1px solid var(--border); display:flex; gap:8px; justify-content:flex-end; flex-wrap:wrap }

    /* Inputs */
    .form-control, .form-select{
      border:1px solid var(--border); border-radius:12px; padding:.65rem .8rem; min-height:48px; font-size:1rem;
      transition:border .2s, box-shadow .2s, background .2s;
    }
    .form-control:focus, .form-select:focus{ border-color:#dcd7fe; box-shadow:0 0 0 4px var(--ring) }

    /* Tarjetas y “empty” */
    .table-card{ border:1px solid var(--border); border-radius:12px; overflow:hidden; background:#fff }
    .badge-soft{ padding:4px 10px; border-radius:999px; font-weight:800; font-size:.78rem; background:linear-gradient(135deg,#f0ecff,#e6f9ff); color:#0f172a; border:1px solid #edf0ff }
    .empty{ border:1px dashed #dbe0ef; border-radius:14px; padding:18px; text-align:center; color:#6b7280; background:#f9fbff }

    /* Tabla: mejoras responsive */
    .table thead th{ white-space:nowrap }
    .table td, .table th{ vertical-align:middle }
    .table-responsive{ scrollbar-width:thin }
    .table-responsive::-webkit-scrollbar{ height:8px }
    .table-responsive::-webkit-scrollbar-thumb{ background:#d9def0; border-radius:999px }

    /* Offcanvas filtros más cómodo en móvil */
    .offcanvas{ border-left:1px solid var(--border) }
    @media (max-width: 991.98px){
      .offcanvas.offcanvas-end{ width:86vw; max-width:360px }
    }

    /* Acciones sticky en móvil (footer de resultados) */
    @media (max-width: 575.98px){
      .block-footer{
        position:sticky; bottom:0; z-index:5;
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
      <div class="icon" aria-hidden="true"><i class="bi bi-book-fill"></i></div>
      <div class="flex-grow-1">
        <div class="title fs-fluid-lg">Historial de Compras de Telas</div>
        <div class="sub fs-fluid-sm text-muted">Consulta, filtra y analiza tus compras de insumos</div>
      </div>

      <!-- Botón Filtros (visible en < lg) -->
      <button class="btn btn-primary btn-touch d-lg-none" type="button"
              data-bs-toggle="offcanvas" data-bs-target="#offcanvasFiltros" aria-controls="offcanvasFiltros">
        <i class="bi bi-funnel-fill me-1"></i> Filtros
      </button>

      <a href="dashboard_admin.php" class="btn btn-secondary btn-touch ms-auto d-none d-lg-inline-flex">
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
              <button type="submit" class="btn btn-primary btn-touch"><i class="bi bi-funnel-fill me-1"></i> Filtrar</button>
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
            <button type="submit" class="btn btn-primary btn-touch"><i class="bi bi-funnel-fill me-1"></i> Aplicar</button>
            <button type="button" class="btn btn-secondary btn-touch" data-bs-dismiss="offcanvas">Cerrar</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Resultados -->
    <section class="block">
      <div class="block-header">
        <span class="fs-fluid-md">Resultados</span>
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
            <div class="mb-1 fs-fluid-sm"><i class="bi bi-exclamation-triangle-fill me-1"></i>No se encontraron compras con esos filtros.</div>
            <div class="text-muted">Prueba con otro nombre, mes o año.</div>
          </div>
        <?php endif; ?>
      </div>

      <div class="block-footer">
        <a href="dashboard_admin.php" class="btn btn-secondary btn-touch">
          <i class="bi bi-arrow-left-circle me-1"></i> Volver al Panel
        </a>
       
      </div>
    </section>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Cierra el offcanvas al enviar filtros en móvil/tablet (misma lógica funcional)
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
