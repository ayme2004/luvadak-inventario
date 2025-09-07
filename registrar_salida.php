<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'empleado') {
    header("Location: login.php");
    exit();
}

include("conexion.php");
$productos = $conexion->query("SELECT id_producto, nombre_producto, stock, talla FROM productos");
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Registrar Salida de Producto</title>
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

    /* 2 bloques */
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
    .left{ flex:1.1 } .right{ flex:0.9 }

    /* Inputs */
    .form-label{ font-size:.9rem; font-weight:600; color:#334155; margin-bottom:.35rem }
    .form-control, .form-select{
      border:1px solid var(--border); border-radius:12px; padding:.55rem .7rem; background:#fff;
      transition:border .2s, box-shadow .2s, background .2s;
    }
    .form-control:focus, .form-select:focus{
      border-color:#d5d9e3; box-shadow:0 0 0 3px var(--ring); background:#fff;
    }
    textarea{ resize:vertical }

    /* Badges */
    .pill{
      display:inline-flex; align-items:center; gap:.4rem;
      padding:.35rem .6rem; border-radius:999px; font-weight:700; font-size:.78rem;
      background:linear-gradient(135deg,#f0ecff,#e6f9ff); color:#0f172a; border:1px solid #edf0ff;
    }

    /* Estado/Resumen */
    .summary{ border:1px dashed #dbe0ef; border-radius:12px; padding:12px; background:#fafbff }
    .rowline{ display:flex; justify-content:space-between; gap:12px; padding:6px 0 }
    .val{ font-weight:800 }
    .muted{ color:var(--muted) }
    .warn{ background:#fff7ed; border:1px solid #ffedd5; color:#9a3412; border-radius:10px; padding:10px 12px }

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

    .actions{ display:grid; gap:10px; grid-template-columns:1fr; }
    @media (min-width:480px){ .actions{ grid-template-columns:1fr 1fr } }
  </style>
</head>
<body>

<div class="page">
  <!-- Hero -->
  <div class="hero">
    <div class="icon"><i class="bi bi-box-arrow-up-right"></i></div>
    <div class="flex-grow-1">
      <h3>Registrar salida de producto</h3>
      <div class="sub">Empleado: <strong><?= htmlspecialchars($_SESSION['usuario']); ?></strong></div>
    </div>
  </div>

  <form action="procesar_salida.php" method="POST" id="formSalida">
    <div class="blocks">

      <!-- IZQUIERDA: Form -->
      <section class="block left">
        <div class="block-header"><i class="bi bi-pencil-square"></i> Datos de la salida</div>
        <div class="block-body">

          <div class="mb-3">
            <label for="producto" class="form-label">Producto</label>
            <select name="id_producto" id="producto" class="form-select" required>
              <option value="">Selecciona un producto</option>
              <?php while ($prod = $productos->fetch_assoc()) { ?>
                <option
                  value="<?= $prod['id_producto'] ?>"
                  data-nombre="<?= htmlspecialchars($prod['nombre_producto']) ?>"
                  data-stock="<?= (int)$prod['stock'] ?>"
                  data-talla="<?= htmlspecialchars($prod['talla']) ?>">
                  <?= htmlspecialchars($prod['nombre_producto']) ?> (T: <?= htmlspecialchars($prod['talla']) ?> · Stock: <?= (int)$prod['stock'] ?>)
                </option>
              <?php } ?>
            </select>
          </div>

          <div class="row g-2">
            <div class="col-md-6">
              <label for="cantidad" class="form-label">Cantidad</label>
              <input type="number" name="cantidad" id="cantidad" class="form-control" placeholder="Cantidad a retirar" required min="1">
            </div>
            <div class="col-md-6">
              <label for="motivo" class="form-label">Motivo (opcional)</label>
              <select name="motivo" id="motivo" class="form-select">
                <option value="">Selecciona un motivo</option>
                <option value="venta">Venta</option>
                <option value="muestra">Muestra</option>
                <option value="error">Error</option>
                <option value="obsequio">Obsequio</option>
              </select>
            </div>
          </div>

          <div class="mt-3">
            <label for="observaciones" class="form-label">Observaciones</label>
            <textarea name="observaciones" id="observaciones" class="form-control" rows="3" placeholder="Observaciones (opcional)"></textarea>
          </div>
        </div>
      </section>

      <!-- DERECHA: Resumen/Estado -->
      <section class="block right">
        <div class="block-header"><i class="bi bi-clipboard-data"></i> Resumen & Estado</div>
        <div class="block-body">
          <div class="summary">
            <div class="muted mb-2"><i class="bi bi-eye"></i> Vista previa</div>
            <div class="rowline"><span>Producto</span> <span class="val" id="sProd">—</span></div>
            <div class="rowline"><span>Talla</span> <span class="val" id="sTalla">—</span></div>
            <div class="rowline"><span>Stock disponible</span> <span class="val" id="sStock">—</span></div>
            <div class="rowline"><span>Cantidad a retirar</span> <span class="val" id="sCant">0</span></div>
            <hr class="my-2">
            <div id="warn" class="warn d-none"><i class="bi bi-exclamation-triangle-fill"></i> La cantidad excede el stock disponible.</div>
          </div>

          <div class="mt-3">
            <span class="pill"><i class="bi bi-info-circle"></i> Validado en tiempo real</span>
          </div>
        </div>
      </section>

    </div>

    <div class="actions mt-3">
      <button type="submit" class="btn btn-primary">
        <i class="bi bi-check2-circle"></i> Registrar salida
      </button>
      <a href="dashboard_empleado.php" class="btn btn-secondary">
        <i class="bi bi-arrow-left-circle"></i> Volver al Panel
      </a>
    </div>
  </form>
</div>

<script>
  const sel   = document.getElementById('producto');
  const cant  = document.getElementById('cantidad');
  const form  = document.getElementById('formSalida');

  const sProd  = document.getElementById('sProd');
  const sTalla = document.getElementById('sTalla');
  const sStock = document.getElementById('sStock');
  const sCant  = document.getElementById('sCant');
  const warn   = document.getElementById('warn');

  function actualizarResumen(){
    const opt   = sel.options[sel.selectedIndex];
    const nombre= opt?.dataset?.nombre || '—';
    const stock = parseInt(opt?.dataset?.stock || '0', 10);
    const talla = opt?.dataset?.talla || '—';
    const qty   = parseInt(cant.value || '0', 10);

    sProd.textContent  = nombre;
    sTalla.textContent = talla;
    sStock.textContent = isFinite(stock) ? stock : '—';
    sCant.textContent  = isFinite(qty) ? qty : 0;

    const excede = isFinite(qty) && isFinite(stock) && qty > stock && stock >= 0;
    warn.classList.toggle('d-none', !excede);

    // Limita mínimo 1 y evita negativos
    if (qty < 1 && cant.value !== '') { cant.value = 1; sCant.textContent = 1; }
  }

  sel.addEventListener('change', actualizarResumen);
  cant.addEventListener('input', actualizarResumen);

  form.addEventListener('submit', function(e){
    const opt   = sel.options[sel.selectedIndex];
    const stock = parseInt(opt?.dataset?.stock || '0', 10);
    const qty   = parseInt(cant.value || '0', 10);

    if (!sel.value) {
      alert('Selecciona un producto.');
      e.preventDefault(); return false;
    }
    if (!qty || qty < 1) {
      alert('Ingresa una cantidad válida (mínimo 1).');
      e.preventDefault(); return false;
    }
    if (stock >= 0 && qty > stock) {
      alert('La cantidad excede el stock disponible.');
      e.preventDefault(); return false;
    }
    if (!confirm('¿Confirmar salida del producto?')) {
      e.preventDefault(); return false;
    }
  });

  // init
  actualizarResumen();
</script>
</body>
</html>
