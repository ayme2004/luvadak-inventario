<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'admin') {
    header("Location: login.php");
    exit();
}
include("conexion.php");

$empleados = $conexion->query("SELECT id_usuario, nombre_completo FROM usuarios WHERE rol = 'empleado'");
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Registrar Pago - Luvadak</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <!-- Bootstrap + Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet" />

  <style>
    :root{
      --bg:#f8fafc; --panel:#ffffff; --text:#0f172a; --muted:#667085; --border:#e6e9f2;
      --brand:#7c3aed; --brand2:#00d4ff; --ring:rgba(124,58,237,.22);
      --radius:14px; --radius-lg:18px; --shadow:0 2px 12px rgba(16,24,40,.08);
      --ok:#16a34a; --danger:#dc2626;
    }
    body{
      background:
        radial-gradient(900px 520px at 110% -10%, rgba(124,58,237,.08), transparent 45%),
        var(--bg);
      color:var(--text);
      font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, sans-serif;
      font-size:14px;
    }
    .page{ max-width:900px; margin:28px auto 34px; padding:0 16px }

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
    .block-body{ padding:14px 16px }
    .left{ flex:1.05 }
    .right{ flex:0.95 }

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
    .summary .val.err{ color:var(--danger) }
    .muted{ color:var(--muted) }
  </style>
</head>
<body>

<div class="page">
  <!-- Hero -->
  <div class="hero">
    <div class="icon"><i class="bi bi-cash-coin"></i></div>
    <div>
      <h3>Registrar Pago a Empleado</h3>
      <div class="sub">Selecciona el empleado, ingresa el monto y la fecha</div>
    </div>
    <div class="ms-auto">
      <a href="dashboard_admin.php" class="btn btn-secondary">
        <i class="bi bi-arrow-left-circle"></i> Volver
      </a>
    </div>
  </div>

  <form action="procesar_pago.php" method="POST" id="pagoForm">
    <div class="blocks">
      <!-- Bloque IZQUIERDO: Formulario -->
      <section class="block left">
        <div class="block-header"><i class="bi bi-pencil-square"></i> Datos del pago</div>
        <div class="block-body">
          <div class="mb-3">
            <label class="form-label">👤 Empleado</label>
            <select name="id_usuario" id="id_usuario" class="form-select" required>
              <option value="">Selecciona un empleado</option>
              <?php while ($emp = $empleados->fetch_assoc()): ?>
                <option value="<?= $emp['id_usuario']; ?>">
                  <?= htmlspecialchars($emp['nombre_completo']); ?>
                </option>
              <?php endwhile; ?>
            </select>
          </div>

          <div class="row g-2">
            <div class="col-md-6">
              <label class="form-label">💰 Monto (S/)</label>
              <div class="input-group">
                <span class="input-group-text">S/</span>
                <input type="number" step="0.01" name="monto" id="monto" class="form-control" placeholder="Ej: 850.00" required>
              </div>
            </div>
            <div class="col-md-6">
              <label class="form-label">📅 Fecha de pago</label>
              <input type="date" name="fecha_pago" id="fecha_pago" class="form-control" required>
            </div>
          </div>
        </div>
      </section>

      <!-- Bloque DERECHO: Resumen -->
      <section class="block right">
        <div class="block-header"><i class="bi bi-clipboard-data"></i> Resumen</div>
        <div class="block-body">
          <div class="summary">
            <div class="muted mb-1"><i class="bi bi-receipt"></i> Vista previa</div>
            <div class="line"><span>Empleado</span> <span class="val" id="sEmpleado">—</span></div>
            <div class="line"><span>Monto</span> <span class="val" id="sMonto">S/ 0.00</span></div>
            <div class="line"><span>Fecha</span> <span class="val" id="sFecha">—</span></div>
            <hr class="my-2">
            <div class="line"><span>Estado</span> <span class="val ok" id="sEstado">Listo</span></div>
            <small class="text-muted">El backend validará nuevamente los datos enviados.</small>
          </div>
        </div>
      </section>
    </div>

    <!-- Acciones -->
    <div class="d-grid gap-2 mt-3">
      <button type="submit" id="btnSubmit" class="btn btn-success">
        <i class="bi bi-check2-circle"></i> Registrar Pago
      </button>
    </div>
  </form>
</div>

<script>
  // Referencias
  const selEmp = document.getElementById('id_usuario');
  const inpMonto = document.getElementById('monto');
  const inpFecha = document.getElementById('fecha_pago');
  const btnSubmit = document.getElementById('btnSubmit');

  const sEmpleado = document.getElementById('sEmpleado');
  const sMonto    = document.getElementById('sMonto');
  const sFecha    = document.getElementById('sFecha');
  const sEstado   = document.getElementById('sEstado');

  function fmtMoney(v){ const n = parseFloat(v); return 'S/ ' + (isFinite(n)? n.toFixed(2) : '0.00'); }

  function updateResumen(){
    const empText = selEmp.options[selEmp.selectedIndex]?.text || '—';
    const montoVal = inpMonto.value;
    const fechaVal = inpFecha.value;

    sEmpleado.textContent = empText.trim() || '—';
    sMonto.textContent = fmtMoney(montoVal);
    sFecha.textContent = fechaVal ? new Date(fechaVal + 'T00:00:00').toLocaleDateString() : '—';

    // Estado simple según validación UI
    const ok = selEmp.value && parseFloat(montoVal) > 0 && fechaVal;
    sEstado.textContent = ok ? 'Listo' : 'Incompleto';
    sEstado.classList.toggle('ok', ok);
    sEstado.classList.toggle('err', !ok);
    btnSubmit.disabled = !ok;
  }

  [selEmp, inpMonto, inpFecha].forEach(el=>{
    el.addEventListener('input', updateResumen);
    el.addEventListener('change', updateResumen);
  });

  // Init
  updateResumen();
</script>
</body>
</html>
