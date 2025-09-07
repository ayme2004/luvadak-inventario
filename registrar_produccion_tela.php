<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'admin') {
  header("Location: login.php");
  exit();
}
include("conexion.php");

$telas = $conexion->query("SELECT * FROM telas WHERE metros_disponibles > 0");
$categorias = $conexion->query("SELECT * FROM categorias");
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Producción con Tela - Luvadak</title>
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />

  <!-- Bootstrap + Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  <style>
    :root{
      --bg:#f8fafc; --panel:#ffffff; --text:#0f172a; --muted:#667085; --border:#e6e9f2;
      --brand:#7c3aed; --brand2:#00d4ff; --ring:rgba(124,58,237,.22);
      --radius:14px; --radius-lg:18px; --shadow:0 2px 12px rgba(16,24,40,.08);
      --ok:#16a34a; --danger:#dc2626; --warn:#f59e0b;
      --safe-top: env(safe-area-inset-top); --safe-bottom: env(safe-area-inset-bottom);
    }

    /* Base */
    *,*::before,*::after{ box-sizing:border-box; }
    html, body { height:100%; }
    body{
      background:
        radial-gradient(900px 520px at 110% -10%, rgba(124,58,237,.08), transparent 45%),
        var(--bg);
      color:var(--text);
      font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, sans-serif;
      -webkit-font-smoothing:antialiased; -moz-osx-font-smoothing:grayscale;
      font-size:14px;
      padding-bottom:calc(10px + var(--safe-bottom));
    }

    /* Tipografía fluida y utilidades */
    .fs-fluid-sm{ font-size:clamp(.95rem, .9rem + .4vw, 1.05rem); }
    .fs-fluid-md{ font-size:clamp(1rem, .95rem + .8vw, 1.25rem); }
    .fs-fluid-lg{ font-size:clamp(1.1rem, 1rem + 1.2vw, 1.5rem); }

    .page{ max-width:1100px; margin:calc(18px + var(--safe-top)) auto 34px; padding:0 16px }

    /* Hero (sticky en móvil) */
    .hero{
      position:sticky; top:0; z-index:10;
      display:flex; align-items:center; gap:12px;
      background:linear-gradient(180deg, rgba(255,255,255,.94), rgba(255,255,255,.98));
      border:1px solid var(--border); border-radius:var(--radius-lg);
      padding:12px 14px; box-shadow:var(--shadow); margin-bottom:16px;
      backdrop-filter:saturate(120%) blur(6px);
    }
    .hero .icon{
      width:44px;height:44px;border-radius:12px;display:grid;place-items:center;color:#fff;
      background:linear-gradient(135deg,var(--brand),var(--brand2));
      box-shadow:0 10px 24px rgba(124,58,237,.22);
      font-size:1.1rem; flex:0 0 44px;
    }
    .hero h3{ margin:0; font-weight:800 }
    .hero .sub{ color:var(--muted); font-weight:500 }

    /* Botones accesibles */
    .btn{
      border-radius:999px; font-weight:800; border:1px solid var(--border);
      display:inline-flex; align-items:center; gap:.45rem; letter-spacing:.2px;
      transition:transform .15s ease, filter .15s ease, box-shadow .15s ease;
    }
    .btn:focus-visible{ outline:3px solid rgba(124,58,237,.35); outline-offset:2px; }
    .btn-secondary{ background:#fff; color:#0f172a }
    .btn-secondary:hover{ background:#f9f9ff; transform:translateY(-2px) }
    .btn-primary{
      background:linear-gradient(135deg,var(--brand),var(--brand2));
      border-color:transparent; color:#fff; box-shadow:0 8px 18px rgba(124,58,237,.25);
    }
    .btn-primary:hover{ filter:brightness(1.05); transform:translateY(-2px) }

    /* Tamaños táctiles */
    .btn-touch{ min-height:44px; padding:.7rem 1rem; font-size:1rem; }
    .btn-icon{ width:44px; height:44px; padding:0; justify-content:center; }

    /* Layout responsive de bloques (Grid) */
    .blocks{ display:grid; gap:16px; }
    @media (min-width:992px){
      .blocks{ grid-template-columns: 1.1fr .9fr; align-items:start; }
    }

    .block{
      border:1px solid var(--border); background:var(--panel);
      border-radius:var(--radius-lg); box-shadow:var(--shadow);
      overflow:hidden; display:flex; flex-direction:column; min-width:0;
    }
    .block-header{
      padding:12px 14px; border-bottom:1px solid var(--border);
      font-weight:800; display:flex; align-items:center; gap:.5rem;
    }
    .block-body{ padding:14px 16px }

    /* Inputs más cómodos */
    .form-label{ font-size:.95rem; font-weight:700; color:#334155; margin-bottom:.35rem }
    .form-control, .form-select{
      border:1px solid var(--border); border-radius:12px; padding:.65rem .8rem; background:#fff;
      min-height:48px; font-size:1rem;
      transition:border .2s, box-shadow .2s, background .2s;
    }
    .form-control:focus, .form-select:focus{
      border-color:#d5d9e3; box-shadow:0 0 0 3px var(--ring);
    }

    /* Resumen/Costeo */
    .summary{
      border:1px dashed #dbe0ef; border-radius:12px; padding:12px; background:#fafbff;
    }
    .summary .rowline{ display:flex; justify-content:space-between; gap:12px; padding:6px 0 }
    .summary .val{ font-weight:800 }
    .muted{ color:var(--muted) }

    .pill{
      display:inline-flex; align-items:center; gap:.4rem; padding:.25rem .6rem; border-radius:999px;
      font-weight:700; font-size:.78rem; border:1px solid var(--border);
    }
    .pill.ok{ background:#ecfdf5; color:#166534; border-color:#bbf7d0 }
    .pill.warn{ background:#fff7ed; color:#9a3412; border-color:#fed7aa }
    .pill.danger{ background:#fef2f2; color:#991b1b; border-color:#fecaca }

    /* Acciones sticky en móvil */
    .actions-sticky{
      position:sticky; bottom:0; z-index:5;
      padding:10px 0 0; background:linear-gradient(180deg, transparent, rgba(248,250,252,.95));
      backdrop-filter: blur(4px);
    }

    /* Motion reduce */
    @media (prefers-reduced-motion: reduce){
      *{ animation-duration:0.01ms !important; animation-iteration-count:1 !important; transition-duration:0.01ms !important; scroll-behavior:auto !important; }
    }
  </style>
</head>
<body>

<div class="page">
  <!-- Hero -->
  <div class="hero">
    <div class="icon" aria-hidden="true"><i class="bi bi-scissors"></i></div>
    <div class="me-2">
      <h3 class="fs-fluid-lg">Producción con Tela del Inventario</h3>
      <div class="sub fs-fluid-sm">Completa los datos y revisa el costeo en tiempo real</div>
    </div>

    <!-- Desktop: botón con texto; Móvil: ícono táctil -->
    <div class="ms-auto d-none d-sm-block">
      <a href="dashboard_admin.php" class="btn btn-secondary btn-touch">
        <i class="bi bi-arrow-left-circle"></i> Volver
      </a>
    </div>
    <div class="ms-auto d-sm-none">
      <a href="dashboard_admin.php" class="btn btn-secondary btn-icon" aria-label="Volver al Panel">
        <i class="bi bi-arrow-left-circle"></i>
      </a>
    </div>
  </div>

  <form action="procesar_produccion_tela.php" method="POST" id="prodForm">
    <div class="blocks">
      <!-- IZQUIERDA: Formulario -->
      <section class="block left">
        <div class="block-header fs-fluid-md"><i class="bi bi-pencil-square"></i> <span>Datos del producto</span></div>
        <div class="block-body">
          <div class="mb-3">
            <label class="form-label"><i class="bi bi-tag"></i> Nombre del producto</label>
            <input type="text" name="producto" class="form-control" placeholder="Ej: Polo básico mujer" required>
          </div>

          <div class="mb-3">
            <label class="form-label"><i class="bi bi-card-text"></i> Descripción</label>
            <input type="text" name="descripcion" class="form-control" placeholder="Ej: Algodón 100% con cuello redondo" required>
          </div>

          <div class="mb-3">
            <label class="form-label"><i class="bi bi-palette"></i> Color</label>
            <input type="text" name="color" class="form-control" placeholder="Ej: Rosa pastel" required>
          </div>

          <div class="mb-3">
            <label class="form-label"><i class="bi bi-box-fill"></i> Selecciona la tela</label>
            <select name="id_tela" id="id_tela" class="form-select" required>
              <option value="">-- Selecciona --</option>
              <?php while ($fila = $telas->fetch_assoc()) { ?>
                <option
                  value="<?= $fila['id_tela']; ?>"
                  data-nombre="<?= htmlspecialchars($fila['nombre_tela']); ?>"
                  data-precio="<?= (float)$fila['precio_por_metro']; ?>"
                  data-stock="<?= (float)$fila['metros_disponibles']; ?>"
                >
                  <?= htmlspecialchars($fila['nombre_tela']) . " (S/{$fila['precio_por_metro']} · {$fila['metros_disponibles']} m)" ?>
                </option>
              <?php } ?>
            </select>
          </div>

          <div class="row">
            <div class="col-12 col-md-6 mb-3">
              <label class="form-label"><i class="bi bi-rulers"></i> Talla</label>
              <select name="talla" class="form-select" required>
                <option value="">Selecciona una talla</option>
                <?php foreach (["XS","S","M","L","XL"] as $talla): ?>
                  <option value="<?= $talla ?>"><?= $talla ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="col-12 col-md-6 mb-3">
              <label class="form-label"><i class="bi bi-tags"></i> Categoría</label>
              <select name="id_categoria" class="form-select" required>
                <option value="">-- Selecciona una categoría --</option>
                <?php while ($cat = $categorias->fetch_assoc()): ?>
                  <option value="<?= $cat['id_categoria']; ?>"><?= htmlspecialchars($cat['nombre_categoria']); ?></option>
                <?php endwhile; ?>
              </select>
            </div>
          </div>

          <div class="row">
            <div class="col-12 col-md-6 mb-3">
              <label class="form-label"><i class="bi bi-123"></i> Cantidad de productos</label>
              <input type="number" step="1" min="1" name="cantidad_productos" id="cantidad_productos" class="form-control" required>
            </div>
            <div class="col-12 col-md-6 mb-3">
              <label class="form-label"><i class="bi bi-rulers"></i> Material empleado (m)</label>
              <input type="number" step="0.01" min="0" name="metros_usados" id="metros_usados" class="form-control" required>
            </div>
          </div>

          <div class="row">
            <div class="col-12 col-md-6 mb-3">
              <label class="form-label"><i class="bi bi-person-workspace"></i> Costo mano de obra (S/)</label>
              <input type="number" step="0.01" min="0" name="mano_obra" id="mano_obra" class="form-control" required>
            </div>
            <div class="col-12 col-md-6 mb-3">
              <label class="form-label"><i class="bi bi-tools"></i> Otros costos (S/)</label>
              <input type="number" step="0.01" min="0" name="otros_costos" id="otros_costos" class="form-control" required>
            </div>
          </div>

          <div class="mb-1">
            <label class="form-label"><i class="bi bi-cash-coin"></i> Precio de venta por unidad (S/)</label>
            <input type="number" step="0.01" min="0" name="precio_venta" id="precio_venta" class="form-control" required>
          </div>
        </div>
      </section>

      <!-- DERECHA: Resumen y validación -->
      <section class="block right">
        <div class="block-header fs-fluid-md"><i class="bi bi-clipboard-data"></i> <span>Resumen & Costeo</span></div>
        <div class="block-body">
          <div class="summary">
            <div class="muted mb-2"><i class="bi bi-eye"></i> Vista previa de costos</div>

            <div class="rowline"><span>Tela</span> <span class="val" id="sTela">—</span></div>
            <div class="rowline"><span>Precio tela (S/ m)</span> <span class="val" id="sPrecioTela">0.00</span></div>
            <div class="rowline"><span>Disponible (m)</span> <span class="val" id="sStockTela">0.00</span></div>
            <div class="rowline"><span>Material empleado (m)</span> <span class="val" id="sUsado">0.00</span></div>
            <div class="rowline"><span>Costo tela (S/)</span> <span class="val" id="sCostoTela">0.00</span></div>
            <div class="rowline"><span>Mano de obra (S/)</span> <span class="val" id="sMO">0.00</span></div>
            <div class="rowline"><span>Otros costos (S/)</span> <span class="val" id="sOtros">0.00</span></div>
            <hr class="my-2">

            <div class="rowline"><span>Cantidad productos</span> <span class="val" id="sCant">0</span></div>
            <div class="rowline"><span>Costo total (S/)</span> <span class="val" id="sCostoTotal">0.00</span></div>
            <div class="rowline"><span>Costo unitario (S/)</span> <span class="val" id="sCostoUnit">0.00</span></div>
            <div class="rowline"><span>Precio venta (S/)</span> <span class="val" id="sPrecioVenta">0.00</span></div>
            <div class="rowline"><span>Margen unit. (S/)</span><span class="val" id="sMargen">0.00</span></div>
            <div class="rowline"><span>Utilidad total (S/)</span><span class="val" id="sUtilidad">0.00</span></div>

            <div class="mt-2">
              <span id="pillEstado" class="pill ok">Listo</span>
              <div id="warnStock" class="small text-danger mt-1 d-none">
                <i class="bi bi-exclamation-triangle"></i> El material empleado supera el stock disponible.
              </div>
            </div>

            <small class="text-muted d-block mt-2">
              El cálculo es referencial; el backend validará y registrará los datos finales.
            </small>
          </div>
        </div>
      </section>
    </div>

    <!-- Acciones del formulario (sticky en móvil) -->
    <div class="actions-sticky">
      <div class="d-grid gap-2 mt-3">
        <button type="submit" id="btnSubmit" class="btn btn-primary btn-touch">
          ✅ Registrar Producción
        </button>
      </div>
    </div>
  </form>
</div>

<script>
  const selTela = document.getElementById('id_tela');
  const inpCant = document.getElementById('cantidad_productos');
  const inpUsado = document.getElementById('metros_usados');
  const inpMO = document.getElementById('mano_obra');
  const inpOtros = document.getElementById('otros_costos');
  const inpPV = document.getElementById('precio_venta');
  const btnSubmit = document.getElementById('btnSubmit');

  const sTela = document.getElementById('sTela');
  const sPrecioTela = document.getElementById('sPrecioTela');
  const sStockTela = document.getElementById('sStockTela');
  const sUsado = document.getElementById('sUsado');
  const sCostoTela = document.getElementById('sCostoTela');
  const sMO = document.getElementById('sMO');
  const sOtros = document.getElementById('sOtros');
  const sCant = document.getElementById('sCant');
  const sCostoTotal = document.getElementById('sCostoTotal');
  const sCostoUnit = document.getElementById('sCostoUnit');
  const sPrecioVenta = document.getElementById('sPrecioVenta');
  const sMargen = document.getElementById('sMargen');
  const sUtilidad = document.getElementById('sUtilidad');
  const pillEstado = document.getElementById('pillEstado');
  const warnStock = document.getElementById('warnStock');

  const num = v => { const n = parseFloat(v); return Number.isFinite(n) ? n : 0; };
  const fmt = v => (num(v)).toFixed(2);

  function update(){
    const opt = selTela.options[selTela.selectedIndex];
    const nombre = opt ? opt.dataset.nombre : '';
    const pmetro = opt ? num(opt.dataset.precio) : 0;
    const stock = opt ? num(opt.dataset.stock) : 0;

    const cant = num(inpCant.value);
    const usado = num(inpUsado.value);
    const mo = num(inpMO.value);
    const otros = num(inpOtros.value);
    const pv = num(inpPV.value);

    const costoTela = usado * pmetro;
    const costoTotal = costoTela + mo + otros;
    const costoUnit = cant > 0 ? (costoTotal / cant) : 0;
    const margen = pv - costoUnit;
    const utilidad = margen * cant;

    // Set UI
    sTela.textContent = nombre || '—';
    sPrecioTela.textContent = fmt(pmetro);
    sStockTela.textContent = fmt(stock);
    sUsado.textContent = fmt(usado);
    sCostoTela.textContent = fmt(costoTela);
    sMO.textContent = fmt(mo);
    sOtros.textContent = fmt(otros);
    sCant.textContent = cant.toString();
    sCostoTotal.textContent = fmt(costoTotal);
    sCostoUnit.textContent = fmt(costoUnit);
    sPrecioVenta.textContent = fmt(pv);
    sMargen.textContent = fmt(margen);
    sUtilidad.textContent = fmt(utilidad);

    // Estado / Validaciones simples de UI
    pillEstado.classList.remove('ok','warn','danger');
    warnStock.classList.add('d-none');
    let ok = true;

    if (usado > stock) {
      pillEstado.classList.add('danger');
      pillEstado.textContent = 'Stock insuficiente';
      warnStock.classList.remove('d-none');
      ok = false;
    } else if (cant <= 0 || usado <= 0) {
      pillEstado.classList.add('warn');
      pillEstado.textContent = 'Completa cantidades';
      ok = false;
    } else if (pv <= 0) {
      pillEstado.classList.add('warn');
      pillEstado.textContent = 'Falta precio de venta';
      ok = false;
    } else {
      pillEstado.classList.add('ok');
      pillEstado.textContent = 'Listo';
    }
    btnSubmit.disabled = !ok;
  }

  [selTela, inpCant, inpUsado, inpMO, inpOtros, inpPV].forEach(el=>{
    el.addEventListener('input', update);
    el.addEventListener('change', update);
  });
  update();
</script>
</body>
</html>
