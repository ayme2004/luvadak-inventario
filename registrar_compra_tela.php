<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Registrar Compra de Tela - Luvadak</title>
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />

  <!-- Bootstrap + Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  <style>
    :root{
      --bg:#f8fafc; --panel:#ffffff; --text:#0f172a; --muted:#667085; --border:#e6e9f2;
      --brand:#7c3aed; --brand2:#00d4ff; --ring:rgba(124,58,237,.22);
      --radius:14px; --radius-lg:18px; --shadow:0 2px 12px rgba(16,24,40,.08);
      --ok:#16a34a; --warn:#f59e0b;
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
      font-size:14px;
      -webkit-font-smoothing:antialiased; -moz-osx-font-smoothing:grayscale;
      padding-bottom: calc(8px + var(--safe-bottom));
    }

    /* Tipografía fluida para mejorar lectura en móvil */
    .fs-fluid-sm{ font-size:clamp(.95rem, .9rem + .4vw, 1.05rem); }
    .fs-fluid-md{ font-size:clamp(1rem, .95rem + .8vw, 1.25rem); }
    .fs-fluid-lg{ font-size:clamp(1.1rem, 1rem + 1.2vw, 1.5rem); }

    .page{ max-width:1100px; margin:calc(18px + var(--safe-top)) auto 34px; padding:0 16px }

    /* Hero (sticky en móvil para acceso rápido) */
    .hero{
      position:sticky; top:0; z-index:10;
      display:flex; align-items:center; gap:12px;
      background:linear-gradient(180deg, rgba(255,255,255,.94), rgba(255,255,255,.98));
      border:1px solid var(--border); border-radius:var(--radius-lg);
      padding:12px 14px; box-shadow:var(--shadow); margin-bottom:16px;
      backdrop-filter:saturate(120%) blur(6px);
    }
    .hero .icon{
      width:44px; height:44px; border-radius:12px; display:grid; place-items:center; color:#fff;
      background:linear-gradient(135deg,var(--brand),var(--brand2));
      box-shadow:0 10px 24px rgba(124,58,237,.22);
      font-size:1.1rem; flex:0 0 44px;
    }
    .hero h3{ margin:0; font-weight:800 }
    .hero .sub{ color:var(--muted); font-weight:500 }

    /* Botón de volver adaptado a móvil */
    .btn{
      border-radius:999px; font-weight:800; border:1px solid var(--border);
      display:inline-flex; align-items:center; gap:.45rem; letter-spacing:.2px;
      transition:transform .15s ease, filter .15s ease, box-shadow .15s ease;
    }
    .btn:focus-visible{ outline:3px solid rgba(124,58,237,.35); outline-offset:2px; }
    .btn-secondary{ background:#fff; color:#0f172a }
    .btn-secondary:hover{ background:#f9f9ff; transform:translateY(-2px) }

    /* Tamaños táctiles */
    .btn-touch{ min-height:44px; padding:.7rem 1rem; font-size:1rem; }
    .btn-icon{ width:44px; height:44px; padding:0; justify-content:center; }

    /* Layout responsive de bloques */
    .blocks{ display:grid; gap:16px; }
    @media (min-width:992px){
      .blocks{ grid-template-columns: 1fr .9fr; align-items:start; }
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
    .block-header .tag{
      padding:.2rem .6rem; border-radius:999px; border:1px solid var(--border);
      background:#f6f7fb; font-size:.78rem; font-weight:700;
    }
    .block-body{ padding:14px 16px }

    .block.left{ /* 1º columna */ }
    .block.right{ /* 2º columna */ }

    /* Inputs cómodos en móvil */
    .form-label{ font-size:.95rem; font-weight:700; color:#334155; margin-bottom:.35rem }
    .form-control, .form-select{
      border:1px solid var(--border); border-radius:12px; padding:.65rem .8rem; background:#fff;
      min-height:48px; font-size:1rem;
      transition:border .2s, box-shadow .2s, background .2s;
    }
    .form-control:focus, .form-select:focus{
      border-color:#d5d9e3; box-shadow:0 0 0 3px var(--ring);
    }

    /* Input group */
    .input-group-text{ background:#f6f7fb; border:1px solid var(--border); border-right:0; min-height:48px; }

    /* Botón principal (submit) */
    .btn-success{
      background:linear-gradient(135deg,#16a34a,#22c55e);
      border-color:transparent; color:#fff; box-shadow:0 8px 18px rgba(22,163,74,.25);
    }
    .btn-success:hover{ filter:brightness(1.04); transform:translateY(-2px) }

    /* Resumen */
    .summary{
      border:1px dashed #dbe0ef; border-radius:12px; padding:12px; background:#fafbff;
    }
    .summary .line{ display:flex; justify-content:space-between; gap:12px; padding:6px 0 }
    .summary .val{ font-weight:800 }
    .summary .val.ok{ color:var(--ok) }
    .muted{ color:var(--muted) }

    /* Barra de acciones “pegajosa” en móvil (solo estético, no cambia lógica) */
    .actions-sticky{
      position:sticky; bottom:0; z-index:5;
      padding:10px 0 0; background:linear-gradient(180deg, transparent, rgba(248,250,252,.9));
      backdrop-filter: blur(4px);
    }

    /* Respeta movimiento reducido */
    @media (prefers-reduced-motion: reduce){
      *{ animation-duration:0.01ms !important; animation-iteration-count:1 !important; transition-duration:0.01ms !important; scroll-behavior:auto !important; }
    }
  </style>
</head>
<body>

<div class="page">
  <!-- Hero -->
  <div class="hero">
    <div class="icon" aria-hidden="true"><i class="bi bi-cart-plus-fill"></i></div>
    <div class="me-2">
      <h3 class="fs-fluid-lg">Registrar Compra de Tela</h3>
      <div class="sub fs-fluid-sm">Ingresa los datos de la compra y revisa el costo por unidad</div>
    </div>

    <!-- Desktop: botón con texto; Móvil: ícono táctil -->
    <div class="ms-auto d-none d-sm-block">
      <a href="dashboard_admin.php" class="btn btn-secondary btn-touch"><i class="bi bi-arrow-left-circle"></i> Volver</a>
    </div>
    <div class="ms-auto d-sm-none">
      <a href="dashboard_admin.php" class="btn btn-secondary btn-icon" aria-label="Volver al Panel"><i class="bi bi-arrow-left-circle"></i></a>
    </div>
  </div>

  <form action="procesar_compra_tela.php" method="POST">
    <div class="blocks">
      <!-- Bloque IZQUIERDO: Datos de compra -->
      <section class="block left">
        <div class="block-header fs-fluid-md"><i class="bi bi-receipt"></i> <span>Datos de compra</span> <span class="tag ms-1">Requeridos</span></div>
        <div class="block-body">
          <div class="mb-3">
            <label class="form-label"><i class="bi bi-tag"></i> Nombre de la tela</label>
            <input type="text" name="nombre_tela" class="form-control" placeholder="Ej: Algodón stretch" required>
          </div>

          <div class="row g-2">
            <div class="col-12 col-md-6">
              <label class="form-label"><i class="bi bi-rulers"></i> Unidad de medida</label>
              <select name="unidad" id="unidad" class="form-select" required>
                <option value="">-- Selecciona unidad --</option>
                <option value="metro">Metro</option>
                <option value="kilo">Kilo</option>
              </select>
            </div>
            <div class="col-12 col-md-6">
              <label id="lblCantidad" class="form-label"><i class="bi bi-arrow-down-up"></i> Cantidad comprada</label>
              <div class="input-group">
                <input type="number" step="0.01" name="metros_comprados" id="cantidad" class="form-control" placeholder="Ej: 25.5" required>
                <span class="input-group-text" id="unidadSuffix">m</span>
              </div>
            </div>
          </div>

          <div class="mt-3">
            <label class="form-label"><i class="bi bi-currency-dollar"></i> Precio total (S/)</label>
            <div class="input-group">
              <span class="input-group-text">S/</span>
              <input type="number" step="0.01" name="precio_total" id="precio" class="form-control" placeholder="Ej: 150.00" required>
            </div>
          </div>
        </div>
      </section>

      <!-- Bloque DERECHO: Opcionales + Resumen -->
      <section class="block right">
        <div class="block-header fs-fluid-md"><i class="bi bi-sliders2"></i> <span>Opcionales & Resumen</span></div>
        <div class="block-body">
          <div class="mb-3">
            <label class="form-label"><i class="bi bi-truck"></i> Proveedor (opcional)</label>
            <input type="text" name="proveedor" class="form-control" placeholder="Nombre del proveedor">
          </div>

          <div class="mb-3">
            <label class="form-label"><i class="bi bi-chat-left-dots"></i> Observaciones (opcional)</label>
            <textarea name="observaciones" class="form-control" rows="3" placeholder="Ej: Compra con descuento, pago al contado..."></textarea>
          </div>

          <div class="summary">
            <div class="muted mb-1"><i class="bi bi-clipboard-data"></i> Resumen</div>
            <div class="line"><span>Unidad</span> <span class="val" id="sumUnidad">—</span></div>
            <div class="line"><span>Cantidad</span> <span class="val" id="sumCantidad">0</span></div>
            <div class="line"><span>Precio total</span> <span class="val" id="sumPrecio">S/ 0.00</span></div>
            <hr class="my-2">
            <div class="line">
              <span>Costo por unidad</span>
              <span class="val ok" id="sumCPU">S/ 0.00</span>
            </div>
            <small class="text-muted">Se recalcula automáticamente al cambiar cantidad o precio.</small>
          </div>
        </div>
      </section>
    </div>

    <!-- Acciones (sticky en móvil) -->
    <div class="actions-sticky">
      <div class="d-grid gap-2 mt-3">
        <button type="submit" class="btn btn-success btn-touch">
          <i class="bi bi-check-circle-fill"></i> Registrar Compra
        </button>
      </div>
    </div>
  </form>
</div>

<script>
  const unidad = document.getElementById('unidad');
  const cantidad = document.getElementById('cantidad');
  const precio = document.getElementById('precio');
  const unidadSuffix = document.getElementById('unidadSuffix');

  const sumUnidad = document.getElementById('sumUnidad');
  const sumCantidad = document.getElementById('sumCantidad');
  const sumPrecio = document.getElementById('sumPrecio');
  const sumCPU = document.getElementById('sumCPU');

  function updateUnidadSuffix(){
    const u = unidad.value;
    unidadSuffix.textContent = (u === 'kilo') ? 'kg' : 'm';
    sumUnidad.textContent = u ? (u === 'kilo' ? 'Kilo' : 'Metro') : '—';
  }

  function fmt(v){ return (isFinite(v) ? v : 0).toFixed(2); }

  function recalc(){
    const q = parseFloat(cantidad.value);
    const p = parseFloat(precio.value);
    const cpu = (q > 0 && p >= 0) ? (p / q) : 0;
    sumCantidad.textContent = isFinite(q) ? q : 0;
    sumPrecio.textContent = 'S/ ' + fmt(p);
    sumCPU.textContent = 'S/ ' + fmt(cpu);
  }

  unidad.addEventListener('change', updateUnidadSuffix);
  ;['input','change'].forEach(evt=>{
    cantidad.addEventListener(evt, recalc);
    precio.addEventListener(evt, recalc);
  });

  // Init
  updateUnidadSuffix();
  recalc();
</script>
</body>
</html>
