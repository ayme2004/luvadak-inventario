<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Registrar Compra de Tela - Luvadak</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <!-- Bootstrap + Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  <style>
    :root{
      --bg:#f8fafc; --panel:#ffffff; --text:#0f172a; --muted:#667085; --border:#e6e9f2;
      --brand:#7c3aed; --brand2:#00d4ff; --ring:rgba(124,58,237,.22);
      --radius:14px; --radius-lg:18px; --shadow:0 2px 12px rgba(16,24,40,.08);
      --ok:#16a34a; --warn:#f59e0b;
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
      width:40px; height:40px; border-radius:12px; display:grid; place-items:center; color:#fff;
      background:linear-gradient(135deg,var(--brand),var(--brand2));
      box-shadow:0 10px 24px rgba(124,58,237,.22);
    }
    .hero h3{ margin:0; font-weight:800; font-size:1.2rem }
    .hero .sub{ color:var(--muted) }

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

    .block.left{ flex:1.1 }
    .block.right{ flex:0.9 }

    /* Inputs */
    .form-label{ font-size:.9rem; font-weight:600; color:#334155; margin-bottom:.35rem }
    .form-control, .form-select{
      border:1px solid var(--border); border-radius:12px; padding:.55rem .7rem; background:#fff;
      transition:border .2s, box-shadow .2s, background .2s;
    }
    .form-control:focus, .form-select:focus{
      border-color:#d5d9e3; box-shadow:0 0 0 3px var(--ring);
    }

    /* Input group */
    .input-group-text{ background:#f6f7fb; border:1px solid var(--border); border-right:0 }

    /* Botones modernos */
    .btn{
      border-radius:999px; font-weight:700; padding:.6rem 1rem; border:1px solid var(--border);
      display:inline-flex; align-items:center; gap:.35rem;
    }
    .btn-success{
      background:linear-gradient(135deg,#16a34a,#22c55e);
      border-color:transparent; color:#fff; box-shadow:0 6px 16px rgba(22,163,74,.22);
    }
    .btn-success:hover{ filter:brightness(1.04); transform:translateY(-2px) }
    .btn-secondary{ background:#fff; color:#0f172a }
    .btn-secondary:hover{ background:#f9f9ff; transform:translateY(-2px) }

    /* Resumen */
    .summary{
      border:1px dashed #dbe0ef; border-radius:12px; padding:12px; background:#fafbff;
    }
    .summary .line{ display:flex; justify-content:space-between; gap:12px; padding:6px 0 }
    .summary .val{ font-weight:800 }
    .summary .val.ok{ color:var(--ok) }
    .muted{ color:var(--muted) }
  </style>
</head>
<body>

<div class="page">
  <!-- Hero -->
  <div class="hero">
    <div class="icon"><i class="bi bi-cart-plus-fill"></i></div>
    <div>
      <h3>Registrar Compra de Tela</h3>
      <div class="sub">Ingresa los datos de la compra y revisa el costo por unidad</div>
    </div>
    <div class="ms-auto">
      <a href="dashboard_admin.php" class="btn btn-secondary"><i class="bi bi-arrow-left-circle"></i> Volver</a>
    </div>
  </div>

  <form action="procesar_compra_tela.php" method="POST">
    <div class="blocks">
      <!-- Bloque IZQUIERDO: Datos de compra -->
      <section class="block left">
        <div class="block-header"><i class="bi bi-receipt"></i> Datos de compra <span class="tag">Requeridos</span></div>
        <div class="block-body">
          <div class="mb-3">
            <label class="form-label"><i class="bi bi-tag"></i> Nombre de la tela</label>
            <input type="text" name="nombre_tela" class="form-control" placeholder="Ej: Algodón stretch" required>
          </div>

          <div class="row g-2">
            <div class="col-md-6">
              <label class="form-label"><i class="bi bi-rulers"></i> Unidad de medida</label>
              <select name="unidad" id="unidad" class="form-select" required>
                <option value="">-- Selecciona unidad --</option>
                <option value="metro">Metro</option>
                <option value="kilo">Kilo</option>
              </select>
            </div>
            <div class="col-md-6">
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
        <div class="block-header"><i class="bi bi-sliders2"></i> Opcionales & Resumen</div>
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

    <!-- Acciones -->
    <div class="d-grid gap-2 mt-3">
      <button type="submit" class="btn btn-success">
        <i class="bi bi-check-circle-fill"></i> Registrar Compra
      </button>
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
