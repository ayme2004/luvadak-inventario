<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'admin') {
    header("Location: login.php");
    exit();
}
include("conexion.php");

$productos = $conexion->query("SELECT id_producto, nombre_producto, talla, color, stock FROM productos");
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Registrar Movimiento - Luvadak</title>
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
    .page{ max-width:1050px; margin:28px auto 34px; padding:0 16px }

    /* Hero */
    .hero{
      display:flex; align-items:center; gap:12px;
      background:linear-gradient(180deg, rgba(255,255,255,.92), rgba(255,255,255,.98));
      border:1px solid var(--border); border-radius:var(--radius-lg);
      padding:14px 16px; box-shadow:var(--shadow); margin-bottom:16px;
      flex-wrap:wrap; /* para responsivo */
    }
    .hero .icon{
      width:40px;height:40px;border-radius:12px;display:grid;place-items:center;color:#fff;
      background:linear-gradient(135deg,var(--brand),var(--brand2));
      box-shadow:0 10px 24px rgba(124,58,237,.22);
    }
    .hero h3{ margin:0; font-weight:800; font-size:1.2rem }
    .hero .sub{ color:var(--muted) }
    .hero-actions{
      margin-left:auto;
      display:flex; align-items:center; gap:.5rem;
      flex-wrap:wrap;
    }

    /* Layout 2 bloques */
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
    .block-header .tag{
      padding:.18rem .55rem; border-radius:999px; border:1px solid var(--border);
      background:#f6f7fb; font-size:.78rem; font-weight:700;
    }
    .block-body{ padding:14px 16px }

    .left{ flex:1.1 }
    .right{ flex:0.9 }

    /* Inputs */
    .form-label{ font-size:.9rem; font-weight:600; color:#334155; margin-bottom:.35rem }
    .form-control, .form-select{
      border:1px solid var(--border); border-radius:12px; padding:.55rem .7rem; background:#fff;
      transition:border .2s, box-shadow .2s, background .2s;
    }
    .form-control:focus, .form-select:focus{
      border-color:#d5d9e3; box-shadow:0 0 0 3px var(--ring);
    }
    option.sin-stock{ background:#ffe6e6; color:#dc2626 }

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

    /* Resumen */
    .summary{
      border:1px dashed #dbe0ef; border-radius:12px; padding:12px; background:#fafbff;
    }
    .summary .line{ display:flex; justify-content:space-between; gap:12px; padding:6px 0 }
    .summary .val{ font-weight:800 }
    .summary .badge-soft{
      padding:.18rem .55rem; border-radius:999px; border:1px solid var(--border);
      background:#f6f7fb; font-weight:700; font-size:.78rem;
    }
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
    <div class="icon"><i class="bi bi-arrow-left-right"></i></div>
    <div>
      <h3>Registrar Movimiento</h3>
      <div class="sub">Selecciona el producto, define el tipo y la cantidad. Verás el stock resultante.</div>
    </div>

    <!-- BOTONES ALINEADOS A LA DERECHA EN LA MISMA FILA -->
    <div class="hero-actions">
      <a href="dashboard_admin.php" class="btn btn-secondary">
        <i class="bi bi-arrow-left-circle"></i> Volver
      </a>
      <!-- Este botón envía el formulario #movForm -->
      <button type="submit" form="movForm" id="btnSubmit" class="btn btn-primary">
        <i class="bi bi-check2-circle"></i> Registrar Movimiento
      </button>
    </div>
  </div>

  <form action="procesar_movimiento.php" method="POST" id="movForm">
    <div class="blocks">
      <!-- Bloque IZQUIERDO: Formulario -->
      <section class="block left">
        <div class="block-header"><i class="bi bi-pencil-square"></i> Datos del movimiento <span class="tag">Requeridos</span></div>
        <div class="block-body">
          <div class="mb-3">
            <label class="form-label"><i class="bi bi-box-seam"></i> Producto (con stock actual)</label>
            <select name="id_producto" id="id_producto" class="form-select" required>
              <option value="">-- Selecciona un producto --</option>
              <?php while ($prod = $productos->fetch_assoc()):
                $sin_stock = $prod['stock'] <= 0;
                $texto = $prod['nombre_producto'] . " · T:" . $prod['talla'] . " · C:" . $prod['color'] .
                         ($sin_stock ? " · ⚠️ SIN STOCK" : " · Stock: " . $prod['stock']);
              ?>
                <option
                  value="<?= $prod['id_producto'] ?>"
                  class="<?= $sin_stock ? 'sin-stock' : '' ?>"
                  data-nombre="<?= htmlspecialchars($prod['nombre_producto']) ?>"
                  data-talla="<?= htmlspecialchars($prod['talla']) ?>"
                  data-color="<?= htmlspecialchars($prod['color']) ?>"
                  data-stock="<?= (int)$prod['stock'] ?>"
                ><?= htmlspecialchars($texto) ?></option>
              <?php endwhile; ?>
            </select>
          </div>

          <div class="row g-2">
            <div class="col-md-6">
              <label class="form-label"><i class="bi bi-shuffle"></i> Tipo de movimiento</label>
              <select name="tipo_movimiento" id="tipo_movimiento" class="form-select" required>
                <option value="">-- Selecciona tipo --</option>
                <option value="entrada">📥 Entrada (Agregar al inventario)</option>
                <option value="salida">📤 Salida (Quitar del inventario)</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label"><i class="bi bi-hash"></i> Cantidad</label>
              <input type="number" name="cantidad" id="cantidad" class="form-control" min="1" required placeholder="Ej: 10">
            </div>
          </div>

          <div class="mt-3">
            <label class="form-label"><i class="bi bi-chat-left-dots"></i> Observaciones (opcional)</label>
            <textarea name="observaciones" class="form-control" rows="3" placeholder="Ej: Reposición, error, venta directa..."></textarea>
          </div>
        </div>
      </section>

      <!-- Bloque DERECHO: Resumen -->
      <section class="block right">
        <div class="block-header"><i class="bi bi-clipboard-data"></i> Resumen</div>
        <div class="block-body">
          <div class="summary">
            <div class="line"><span class="muted">Producto</span> <span class="val" id="sNombre">—</span></div>
            <div class="line"><span class="muted">Talla · Color</span> <span class="val" id="sTC">—</span></div>
            <div class="line"><span class="muted">Stock actual</span> <span class="val" id="sStock">0</span></div>
            <div class="line"><span class="muted">Tipo</span> <span class="val"><span id="sTipo" class="badge-soft">—</span></span></div>
            <div class="line"><span class="muted">Cantidad</span> <span class="val" id="sCant">0</span></div>
            <hr class="my-2">
            <div class="line">
              <span class="muted">Stock resultante</span>
              <span class="val"><span id="sResult" class="pill ok">0</span></span>
            </div>
            <small class="text-muted">Este cálculo es referencial. El backend valida nuevamente.</small>
          </div>
        </div>
        <div class="p-3 pt-0">
          <div id="sWarn" class="small text-danger d-none"><i class="bi bi-exclamation-triangle"></i> La salida supera el stock disponible.</div>
        </div>
      </section>
    </div>

  </form>
</div>

<script>
  const selProd = document.getElementById('id_producto');
  const selTipo = document.getElementById('tipo_movimiento');
  const inpCant = document.getElementById('cantidad');
  const btnSubmit = document.getElementById('btnSubmit');

  const sNombre = document.getElementById('sNombre');
  const sTC     = document.getElementById('sTC');
  const sStock  = document.getElementById('sStock');
  const sTipo   = document.getElementById('sTipo');
  const sCant   = document.getElementById('sCant');
  const sResult = document.getElementById('sResult');
  const sWarn   = document.getElementById('sWarn');

  function parseIntSafe(v){ const n = parseInt(v,10); return isNaN(n) ? 0 : n; }

  function updateResumen(){
    const opt = selProd.options[selProd.selectedIndex];
    const stock = opt ? parseIntSafe(opt.dataset.stock) : 0;
    const nombre = opt ? opt.dataset.nombre : '—';
    const talla = opt ? opt.dataset.talla : '';
    const color = opt ? opt.dataset.color : '';
    const tipo = selTipo.value || '—';
    const cant = parseIntSafe(inpCant.value);

    sNombre.textContent = nombre || '—';
    sTC.textContent = (talla && color) ? (talla + ' · ' + color) : '—';
    sStock.textContent = stock;
    sTipo.textContent = tipo === 'entrada' ? 'Entrada' : (tipo === 'salida' ? 'Salida' : '—');
    sCant.textContent = cant || 0;

    let result = stock;
    if (tipo === 'entrada') result = stock + (cant || 0);
    if (tipo === 'salida')  result = stock - (cant || 0);

    sResult.textContent = result;

    // color del pill
    sResult.classList.remove('ok','warn','danger');
    sWarn.classList.add('d-none');
    btnSubmit.disabled = false; // por defecto habilitado

    if (tipo === 'salida') {
      if ((cant || 0) > stock) {
        sResult.classList.add('danger');
        sWarn.classList.remove('d-none');
        btnSubmit.disabled = true; // seguridad en UI
      } else if (result < 5) {
        sResult.classList.add('warn');
      } else {
        sResult.classList.add('ok');
      }
    } else if (tipo === 'entrada') {
      sResult.classList.add('ok');
    } else {
      sResult.classList.add('warn');
    }
  }

  [selProd, selTipo, inpCant].forEach(el => {
    el && el.addEventListener('change', updateResumen);
    el && el.addEventListener('input', updateResumen);
  });

  // Init
  updateResumen();
</script>
</body>
</html>
