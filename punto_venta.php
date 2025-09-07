<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}
include("conexion.php");

$productos = $conexion->query("SELECT id_producto, nombre_producto, precio, stock, talla, color FROM productos");
$clientes  = $conexion->query("SELECT id_cliente, nombre_completo FROM clientes");
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Punto de Venta</title>
<meta name="viewport" content="width=device-width, initial-scale=1" />

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

<style>
  :root{
    --bg:#f8fafc; --panel:#ffffff; --text:#0f172a; --muted:#667085; --border:#e6e9f2;
    --brand:#7c3aed; --brand2:#00d4ff; --ring:rgba(124,58,237,.22);
    --radius:14px; --radius-sm:10px; --radius-lg:18px;
    --shadow:0 2px 12px rgba(16,24,40,.08);
    --success:#16a34a; --danger:#dc2626; --warn:#f59e0b;
  }
  body{
    background:
      radial-gradient(900px 520px at 110% -10%, rgba(124,58,237,.08), transparent 45%),
      var(--bg);
    color:var(--text);
    font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, sans-serif;
    font-size:14px;
  }

  .page{ max-width:1250px; margin:28px auto 32px; padding:0 16px }

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
    overflow:hidden; display:flex; flex-direction:column; min-height:100%;
  }
  .block-header{
    padding:12px 16px; border-bottom:1px solid var(--border);
    font-weight:800; display:flex; align-items:center; gap:.5rem;
  }
  .block-header .tag{ padding:.2rem .6rem; border-radius:999px; font-size:.78rem; font-weight:700;
    background:#f6f7fb; border:1px solid var(--border); color:#0f172a }
  .block-body{ padding:14px 16px }
  .block.left{ flex:1; max-width:420px }
  .block.right{ flex:1.6 }

  /* Inputs */
  .form-label{ font-size:.88rem; font-weight:600; color:#334155; margin-bottom:.35rem }
  .form-control, .form-select{
    border:1px solid var(--border); border-radius:12px; padding:.5rem .65rem; font-size:.92rem; background:#fff;
    transition:border .2s, box-shadow .2s, background .2s;
  }
  .form-control:focus, .form-select:focus{
    border-color:#d5d9e3; box-shadow:0 0 0 3px var(--ring);
  }
  .form-check-label{ font-size:.9rem }

  /* Botones modernos */
  .btn{
    border-radius:999px; font-weight:700; padding:.55rem 1rem; border:1px solid var(--border);
    display:inline-flex; align-items:center; gap:.35rem;
  }
  .btn-primary{
    background:linear-gradient(135deg,var(--brand),var(--brand2));
    border-color:transparent; color:#fff; box-shadow:0 6px 16px rgba(124,58,237,.22);
  }
  .btn-primary:hover{ filter:brightness(1.04); transform:translateY(-2px) }
  .btn-success{
    background:linear-gradient(135deg,#16a34a,#22c55e);
    border-color:transparent; color:#fff; box-shadow:0 6px 16px rgba(22,163,74,.22);
  }
  .btn-success:hover{ filter:brightness(1.04); transform:translateY(-2px) }
  .btn-danger{
    background:linear-gradient(135deg,#ef4444,#f97316);
    border-color:transparent; color:#fff; box-shadow:0 6px 16px rgba(239,68,68,.22);
  }
  .btn-secondary{
    background:#fff; color:#0f172a;
  }
  .btn-secondary:hover{ background:#f9f9ff; transform:translateY(-2px) }

  /* Carrito tabla */
  .table-modern{ border:1px solid var(--border); border-radius:12px; overflow:hidden }
  .table-modern thead{ background:#f6f7fb }
  .table-modern th{ border:0; font-weight:800; }
  .table-modern td{ border-color:#eef1f6; }
  .table-modern tbody tr:hover{ background:#fafbff }
  .btn-sm{ padding:.25rem .6rem; font-size:.8rem; border-radius:999px }

  /* Totales */
  .total-card{
    border:1px solid var(--border); background:var(--panel);
    border-radius:var(--radius-lg); box-shadow:var(--shadow);
  }
  #total{ font-size:1.6rem; font-weight:900; color:var(--success) }

  /* New client block badge */
  .badge-soft{
    padding:.22rem .55rem; border-radius:999px; border:1px solid var(--border);
    background:#f6f7fb; font-weight:700; font-size:.78rem;
  }
</style>
</head>
<body>

<div class="page">
  <!-- Hero -->
  <div class="hero">
    <div class="icon"><i class="bi bi-receipt-cutoff"></i></div>
    <div>
      <h3>Punto de Venta · Boleta</h3>
      <div class="sub">Agrega productos al carrito y genera la boleta</div>
    </div>
    <div class="ms-auto d-flex gap-2">
      <a href="dashboard_empleado.php" class="btn btn-secondary"><i class="bi bi-arrow-left-circle"></i> Volver</a>
    </div>
  </div>

  <form action="procesar_venta.php" method="POST">
    <div class="blocks">
      <!-- Bloque IZQ: Cliente -->
      <section class="block left">
        <div class="block-header"><i class="bi bi-person-lines-fill"></i> Cliente <span class="tag">Paso 1</span></div>
        <div class="block-body">
          <label class="form-label">Seleccionar cliente</label>
          <select name="id_cliente" class="form-select mb-3" id="cliente_select" required>
            <option value="">Seleccione un cliente</option>
            <?php while ($c = $clientes->fetch_assoc()) { ?>
              <option value="<?= $c['id_cliente']; ?>"><?= htmlspecialchars($c['nombre_completo']); ?></option>
            <?php } ?>
          </select>

          <div class="form-check mb-2">
            <input class="form-check-input" type="checkbox" id="nuevoClienteCheck" onclick="toggleNuevoCliente()">
            <label class="form-check-label" for="nuevoClienteCheck">Registrar nuevo cliente</label>
          </div>

          <div id="nuevoClienteForm" style="display:none">
            <hr class="my-3">
            <div class="d-flex align-items-center gap-2 mb-2">
              <span class="badge-soft"><i class="bi bi-person-plus"></i> Nuevo cliente</span>
            </div>
            <div class="row g-2">
              <div class="col-md-12">
                <label class="form-label">Nombre completo</label>
                <input type="text" name="nuevo_nombre" class="form-control">
              </div>
              <div class="col-md-6">
                <label class="form-label">DNI</label>
                <input type="text" name="nuevo_dni" class="form-control">
              </div>
              <div class="col-md-6">
                <label class="form-label">Teléfono</label>
                <input type="text" name="nuevo_telefono" class="form-control">
              </div>
              <div class="col-md-6">
                <label class="form-label">Correo</label>
                <input type="email" name="nuevo_correo" class="form-control">
              </div>
              <div class="col-12">
                <label class="form-label">Dirección</label>
                <textarea name="nuevo_direccion" class="form-control" rows="2"></textarea>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Bloque DER: Producto + Carrito -->
      <section class="block right">
        <div class="block-header"><i class="bi bi-bag-plus-fill"></i> Producto <span class="tag">Paso 2</span></div>
        <div class="block-body">
          <div class="row g-2 align-items-end">
            <div class="col-md-6">
              <label class="form-label">Producto</label>
              <select id="producto" class="form-select">
                <option value="">Seleccione un producto</option>
                <?php while ($p = $productos->fetch_assoc()) { ?>
                  <option
                    value="<?= $p['id_producto']; ?>"
                    data-nombre="<?= htmlspecialchars($p['nombre_producto']); ?>"
                    data-precio="<?= $p['precio']; ?>"
                    data-stock="<?= $p['stock']; ?>"
                    data-talla="<?= htmlspecialchars($p['talla']); ?>"
                    data-color="<?= htmlspecialchars($p['color']); ?>"
                  >
                    <?= htmlspecialchars($p['nombre_producto']); ?> · T:<?= htmlspecialchars($p['talla']); ?> · C:<?= htmlspecialchars($p['color']); ?> · S/<?= number_format($p['precio'],2); ?> (Stock: <?= (int)$p['stock']; ?>)
                  </option>
                <?php } ?>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label">Cantidad</label>
              <input type="number" id="cantidad" class="form-control" min="1" placeholder="1">
            </div>
            <div class="col-md-3 d-grid">
              <button type="button" onclick="agregarProducto()" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Agregar
              </button>
            </div>
          </div>

          <hr class="my-3">

          <div class="table-responsive table-modern">
            <table class="table align-middle text-center" id="tabla-carrito">
              <thead>
                <tr>
                  <th>Producto</th>
                  <th>Precio Unit.</th>
                  <th>Cantidad</th>
                  <th>Subtotal</th>
                  <th></th>
                </tr>
              </thead>
              <tbody></tbody>
            </table>
          </div>
        </div>
      </section>
    </div>

    <!-- Totales -->
    <div class="mt-3">
      <div class="total-card p-3 d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-2">
          <span class="badge-soft"><i class="bi bi-calculator"></i> Total</span>
        </div>
        <div>
          <h4 class="m-0">Total: <span id="total">S/ 0.00</span></h4>
          <input type="hidden" name="total" id="total_input">
        </div>
      </div>
    </div>

    <!-- Acciones -->
    <div class="d-grid gap-2 mt-3">
      <button type="submit" class="btn btn-success">
        <i class="bi bi-receipt-cutoff"></i> Generar Boleta
      </button>
      <a href="dashboard_empleado.php" class="btn btn-secondary">
        <i class="bi bi-arrow-left-circle"></i> Volver al Panel
      </a>
    </div>
  </form>
</div>

<script>
function agregarProducto() {
  const select = document.getElementById("producto");
  const id = select.value;
  if (!id) { alert("Selecciona un producto."); return; }

  const opt = select.options[select.selectedIndex];
  const nombre = opt.dataset.nombre;
  const precio = parseFloat(opt.dataset.precio);
  const stock = parseInt(opt.dataset.stock);
  const talla = opt.dataset.talla;
  const color = opt.dataset.color;
  const cantidadInput = document.getElementById("cantidad");
  const cantidad = parseInt(cantidadInput.value);

  if (isNaN(cantidad) || cantidad <= 0) {
    alert("Ingresa una cantidad válida.");
    return;
  }
  if (cantidad > stock) {
    alert("No hay suficiente stock disponible.");
    return;
  }

  const tbody = document.querySelector("#tabla-carrito tbody");
  const rows = tbody.querySelectorAll("tr");
  for (let r of rows) {
    const inputId = r.querySelector('input[name="id_producto[]"]');
    if (inputId && inputId.value === id) {
      const inputCant = r.querySelector('input[name="cantidad[]"]');
      const nuevaCantidad = parseInt(inputCant.value) + cantidad;
      if (nuevaCantidad > stock) {
        alert("No puedes superar el stock disponible.");
        return;
      }
      inputCant.value = nuevaCantidad;
      r.querySelector('.cantidad-visible').textContent = nuevaCantidad;
      r.querySelector('.subtotal').textContent = "S/ " + (nuevaCantidad * precio).toFixed(2);
      actualizarTotal();
      cantidadInput.value = "";
      return;
    }
  }

  const tr = document.createElement("tr");
  tr.innerHTML = `
    <td class="text-start">
      <input type="hidden" name="id_producto[]" value="${id}">
      <input type="hidden" name="talla[]" value="${talla}">
      <input type="hidden" name="color[]" value="${color}">
      <div class="fw-semibold">${nombre}</div>
      <small class="text-muted">Talla: <strong>${talla}</strong> · Color: <strong>${color}</strong></small>
    </td>
    <td>
      <input type="hidden" name="precio_unitario[]" value="${precio}">
      S/ ${precio.toFixed(2)}
    </td>
    <td>
      <input type="hidden" name="cantidad[]" value="${cantidad}">
      <span class="cantidad-visible">${cantidad}</span>
    </td>
    <td class="subtotal">S/ ${(cantidad * precio).toFixed(2)}</td>
    <td><button type="button" onclick="eliminarFila(this)" class="btn btn-sm btn-danger"><i class="bi bi-x-lg"></i></button></td>
  `;
  tbody.appendChild(tr);
  actualizarTotal();
  cantidadInput.value = "";
}

function eliminarFila(btn) {
  btn.closest("tr").remove();
  actualizarTotal();
}

function actualizarTotal() {
  const subtotales = document.querySelectorAll(".subtotal");
  let total = 0;
  subtotales.forEach(td => total += parseFloat(td.textContent.replace("S/ ","")) || 0);
  document.getElementById("total").textContent = "S/ " + total.toFixed(2);
  document.getElementById("total_input").value = total.toFixed(2);
}

function toggleNuevoCliente() {
  const check = document.getElementById("nuevoClienteCheck");
  const form = document.getElementById("nuevoClienteForm");
  const clienteSelect = document.getElementById("cliente_select");
  if (check.checked) {
    form.style.display = "block";
    clienteSelect.disabled = true;
    clienteSelect.required = false;
  } else {
    form.style.display = "none";
    clienteSelect.disabled = false;
    clienteSelect.required = true;
  }
}
</script>
</body>
</html>
