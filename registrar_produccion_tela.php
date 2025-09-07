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
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <!-- Bootstrap + Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  <style>
    :root{
      --bg:#f8fafc; --panel:#ffffff; --text:#0f172a; --muted:#667085; --border:#e6e9f2;
      --brand:#7c3aed; --brand2:#00d4ff; --ring:rgba(124,58,237,.22);
      --radius:14px; --radius-lg:18px; --shadow:0 2px 12px rgba(16,24,40,.08);
      --ok:#16a34a; --danger:#dc2626; --warn:#f59e0b;
    }
    body{
      background:
        radial-gradient(900px 520px at 110% -10%, rgba(124,58,237,.08), transparent 45%),
        var(--bg);
      color:var(--text);
      font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, sans-serif;
      font-size:14px;
    }

    .page{ max-width:1100px; margin:28px auto 34px; padding:0 16px }

    /* Hero */
    .hero{
      display:flex; align-items:center; gap:12px;
      background:linear-gradient(180deg, rgba(255,255,255,.92), rgba(255,255,255,.98));
      border:1px solid var(--border); border-radius:var(--radius-lg);
      padding:14px 16px; box-shadow:var(--shadow); margin-bottom:16px;
    }
    .hero .icon{
      width:40px;height:40px;border-radius:12px;display:grid;place-items:center;color:#fff;
      background:linear-gradient(135deg,var(--brand),var(--brand2));
      box-shadow:0 10px 24px rgba(124,58,237,.22);
    }
    .hero h3{ margin:0; font-weight:800; font-size:1.2rem }
    .hero .sub{ color:var(--muted) }

    /* Bloques 2 columnas */
    .blocks{ display:flex; flex-direction:column; gap:18px }
    @media (min-width:992px){ .blocks{ flex-direction:row } }

    .block{
      border:1px solid var(--border); background:var(--panel);
      border-radius:var(--radius-lg); box-shadow:var(--shadow);
      overflow:hidden; display:flex; flex-direction:column;
    }
    .block-header{
      padding:12px 16px; border-bottom:1px solid var(--border);
      font-weight:800; display:flex; align-items:center; gap:.5rem;
    }
    .block-body{ padding:14px 16px }
    .left{ flex:1.15 }
    .right{ flex:0.85 }

    /* Inputs */
    .form-label{ font-size:.9rem; font-weight:600; color:#334155; margin-bottom:.35rem }
    .form-control, .form-select{
      border:1px solid var(--border); border-radius:12px; padding:.55rem .7rem; background:#fff;
      transition:border .2s, box-shadow .2s, background .2s;
    }
    .form-control:focus, .form-select:focus{
      border-color:#d5d9e3; box-shadow:0 0 0 3px var(--ring);
    }

    /* Botones */
    .btn{
      border-radius:999px; font-weight:700; padding:.6rem 1rem; border:1px solid var(--border);
      display:inline-flex; align-items:center; gap:.35rem;
    }
    .btn-primary{
      background:linear-gradient(135deg,var(--brand),var(--brand2));
      border-color:transparent; color:#fff; box-shadow:0 6px 16px rgba(124,58,237,.22);
    }
    .btn-primary:hover{ filter:brightness(1.04); transform:translateY(-2px) }
    .btn-secondary{ background:#fff; color:#0f172a }
    .btn-secondary:hover{ background:#f9f9ff; transform:translateY(-2px) }

    /* Resumen/Costeo */
    .summary{
      border:1px dashed #dbe0ef; border-radius:12px; padding:12px; background:#fafbff;
    }
    .summary .rowline{ display:flex; justify-content:space-between; gap:12px; padding:6px 0 }
    .summary .val{ font-weight:800 }
    .muted{ color:var(--muted) }

    .pill{
      display:inline-flex; align-items:center; gap:.4rem; padding:.2rem .55rem; border-radius:999px;
      font-weight:700; font-size:.78rem; border:1px solid var(--border);
    }
    .pill.ok{ background:#ecfdf5; color:#166534; border-color:#bbf7d0 }
    .pill.warn{ background:#fff7ed; color:#9a3412; border-color:#fed7aa }
    .pill.danger{ background:#fef2f2; color:#991b1b; border-color:#fecaca }
  </style>
</head>
<body>

<div class="page">
  <!-- Hero -->
  <div class="hero">
    <div class="icon"><i class="bi bi-scissors"></i></div>
    <div>
      <h3>Producción con Tela del Inventario</h3>
      <div class="sub">Completa los datos y revisa el costeo en tiempo real</div>
    </div>
    <div class="ms-auto">
      <a href="dashboard_admin.php" class="btn btn-secondary">
        <i class="bi bi-arrow-left-circle"></i> Volver
      </a>
    </div>
  </div>

  <form action="procesar_produccion_tela.php" method="POST" id="prodForm">
    <div class="blocks">
      <!-- IZQUIERDA: Formulario -->
      <section class="block left">
        <div class="block-header"><i class="bi bi-pencil-square"></i> Datos del producto</div>
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
            <div class="col-md-6 mb-3">
              <label class="form-label"><i class="bi bi-rulers"></i> Talla</label>
              <select name="talla" class="form-select" required>
                <option value="">Selecciona una talla</option>
                <?php foreach (["XS","S","M","L","XL"] as $talla): ?>
                  <option value="<?= $talla ?>"><?= $talla ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="col-md-6 mb-3">
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
            <div class="col-md-6 mb-3">
              <label class="form-label"><i class="bi bi-123"></i> Cantidad de productos</label>
              <input type="number" step="1" min="1" name="cantidad_productos" id="cantidad_productos" class="form-control" required>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label"><i class="bi bi-rulers"></i> Material empleado (m)</label>
              <input type="number" step="0.01" min="0" name="metros_usados" id="metros_usados" class="form-control" required>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label"><i class="bi bi-person-workspace"></i> Costo mano de obra (S/)</label>
              <input type="number" step="0.01" min="0" name="mano_obra" id="mano_obra" class="form-control" required>
            </div>
            <div class="col-md-6 mb-3">
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
        <div class="block-header"><i class="bi bi-clipboard-data"></i> Resumen & Costeo</div>
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
            <div class="rowline">
              <span>Margen unit. (S/)</span>
              <span class="val" id="sMargen">0.00</span>
            </div>
            <div class="rowline">
              <span>Utilidad total (S/)</span>
              <span class="val" id="sUtilidad">0.00</span>
            </div>

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

    <!-- Acciones del formulario -->
    <div class="d-grid gap-2 mt-3">
      <button type="submit" id="btnSubmit" class="btn btn-primary">
        ✅ Registrar Producción
      </button>
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
