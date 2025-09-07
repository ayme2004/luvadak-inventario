<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Registrar Producción</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <!-- Bootstrap + Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  <style>
    :root{
      --bg:#f8fafc; --panel:#ffffff; --text:#0f172a; --muted:#667085; --border:#e6e9f2;
      --brand:#7c3aed; --brand2:#00d4ff; --ring:rgba(124,58,237,.22);
      --radius:14px; --radius-lg:18px; --shadow:0 2px 12px rgba(16,24,40,.08);
    }
    body{
      background:
        radial-gradient(900px 520px at 110% -10%, rgba(124,58,237,.08), transparent 45%),
        var(--bg);
      color:var(--text);
      font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, sans-serif;
      font-size:14px;
    }
    .page{ max-width:1000px; margin:28px auto 34px; padding:0 16px }

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

    /* 2 bloques horizontales */
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
    .left{ flex:1.1 }
    .right{ flex:0.9 }

    /* Inputs */
    .form-label{ font-size:.9rem; font-weight:600; color:#334155; margin-bottom:.35rem }
    .form-control{
      border:1px solid var(--border); border-radius:12px; padding:.55rem .7rem; background:#fff;
      transition:border .2s, box-shadow .2s, background .2s;
    }
    .form-control:focus{
      border-color:#d5d9e3; box-shadow:0 0 0 3px var(--ring); background:#fff;
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

    /* Resumen */
    .summary{ border:1px dashed #dbe0ef; border-radius:12px; padding:12px; background:#fafbff }
    .summary .rowline{ display:flex; justify-content:space-between; gap:12px; padding:6px 0 }
    .summary .val{ font-weight:800 }
    .muted{ color:var(--muted) }
  </style>
</head>
<body>

<div class="page">
  <!-- Hero -->
  <div class="hero">
    <div class="icon"><i class="bi bi-tools"></i></div>
    <div>
      <h3>Registrar Producción</h3>
      <div class="sub">Completa los datos y revisa el costeo en tiempo real</div>
    </div>
  </div>

  <form action="procesar_produccion.php" method="POST" id="prodForm">
    <div class="blocks">

      <!-- IZQUIERDA: Formulario -->
      <section class="block left">
        <div class="block-header"><i class="bi bi-pencil-square"></i> Datos del producto</div>
        <div class="block-body">
          <div class="mb-3">
            <label class="form-label"><i class="bi bi-tag"></i> Nombre del producto</label>
            <input type="text" name="producto" class="form-control" placeholder="Ej: Falda midi" required>
          </div>

          <div class="mb-3">
            <label class="form-label"><i class="bi bi-palette"></i> Tipo de tela</label>
            <input type="text" name="tela" class="form-control" placeholder="Ej: Lino" required>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label"><i class="bi bi-currency-dollar"></i> Precio por metro (S/)</label>
              <input type="number" step="0.01" name="precio_tela" id="precio_tela" class="form-control" placeholder="Ej: 18.50" required>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label"><i class="bi bi-rulers"></i> Metros usados</label>
              <input type="number" step="0.01" name="metros_usados" id="metros_usados" class="form-control" placeholder="Ej: 2.40" required>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label"><i class="bi bi-person-workspace"></i> Mano de obra (S/)</label>
              <input type="number" step="0.01" name="mano_obra" id="mano_obra" class="form-control" placeholder="Ej: 12.00" required>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label"><i class="bi bi-tools"></i> Otros costos (S/)</label>
              <input type="number" step="0.01" name="otros_costos" id="otros_costos" class="form-control" placeholder="Botones, hilos…" required>
            </div>
          </div>

          <div class="mb-1">
            <label class="form-label"><i class="bi bi-cash-coin"></i> Precio de venta (S/)</label>
            <input type="number" step="0.01" name="precio_venta" id="precio_venta" class="form-control" placeholder="Ej: 49.90" required>
          </div>
        </div>
      </section>

      <!-- DERECHA: Resumen -->
      <section class="block right">
        <div class="block-header"><i class="bi bi-clipboard-data"></i> Resumen & Costeo</div>
        <div class="block-body">
          <div class="summary">
            <div class="muted mb-2"><i class="bi bi-eye"></i> Vista previa</div>

            <div class="rowline"><span>Costo tela</span> <span class="val" id="sCostoTela">S/ 0.00</span></div>
            <div class="rowline"><span>Mano de obra</span> <span class="val" id="sMO">S/ 0.00</span></div>
            <div class="rowline"><span>Otros costos</span> <span class="val" id="sOtros">S/ 0.00</span></div>
            <hr class="my-2">
            <div class="rowline"><span>Costo total</span> <span class="val" id="sCostoTotal">S/ 0.00</span></div>
            <div class="rowline"><span>Precio de venta</span> <span class="val" id="sPV">S/ 0.00</span></div>
            <div class="rowline"><span>Margen</span> <span class="val" id="sMargen">S/ 0.00</span></div>

            <small class="text-muted d-block mt-2">
              El cálculo es referencial; el backend confirmará los importes finales.
            </small>
          </div>
        </div>
      </section>

    </div>

    <div class="d-grid gap-2 mt-3">
      <button type="submit" class="btn btn-primary">
        ✅ Registrar
      </button>
      <a href="javascript:history.back()" class="btn btn-secondary">
        ⬅️ Volver
      </a>
    </div>
  </form>
</div>

<script>
  const pt = document.getElementById('precio_tela');
  const mu = document.getElementById('metros_usados');
  const mo = document.getElementById('mano_obra');
  const ot = document.getElementById('otros_costos');
  const pv = document.getElementById('precio_venta');

  const sCostoTela = document.getElementById('sCostoTela');
  const sMO = document.getElementById('sMO');
  const sOtros = document.getElementById('sOtros');
  const sCostoTotal = document.getElementById('sCostoTotal');
  const sPV = document.getElementById('sPV');
  const sMargen = document.getElementById('sMargen');

  const num = v => { const n = parseFloat(v); return Number.isFinite(n) ? n : 0; };
  const fmt = v => 'S/ ' + (num(v)).toFixed(2);

  function update(){
    const costoTela = num(pt.value) * num(mu.value);
    const manoObra = num(mo.value);
    const otros = num(ot.value);
    const total = costoTela + manoObra + otros;
    const precioVenta = num(pv.value);
    const margen = precioVenta - total;

    sCostoTela.textContent = fmt(costoTela);
    sMO.textContent = fmt(manoObra);
    sOtros.textContent = fmt(otros);
    sCostoTotal.textContent = fmt(total);
    sPV.textContent = fmt(precioVenta);
    sMargen.textContent = fmt(margen);
  }

  [pt, mu, mo, ot, pv].forEach(el=>{
    el.addEventListener('input', update);
    el.addEventListener('change', update);
  });
  update();
</script>
</body>
</html>
