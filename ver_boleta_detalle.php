<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}
include("conexion.php");

$id_venta = intval($_GET['id'] ?? 0);

/* Venta + vendedor */
$venta = $conexion->query("
  SELECT v.*, u.nombre_completo 
  FROM ventas v 
  JOIN usuarios u ON v.id_usuario = u.id_usuario 
  WHERE v.id_venta = {$id_venta}
")->fetch_assoc();

/* Detalles */
$detalles = $conexion->query("
  SELECT d.*, p.nombre_producto 
  FROM detalle_venta d 
  JOIN productos p ON d.id_producto = p.id_producto 
  WHERE d.id_venta = {$id_venta}
");

if (!$venta) {
  echo "Boleta no encontrada.";
  exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Boleta #<?= (int)$venta['id_venta'] ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <!-- Bootstrap + Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  <style>
    :root{
      --bg:#f8fafc; --panel:#ffffff; --text:#0f172a; --muted:#667085; --border:#e6e9f2;
      --brand:#7c3aed; --brand2:#00d4ff; --ring:rgba(124,58,237,.22);
      --radius:14px; --radius-lg:18px; --shadow:0 10px 26px rgba(16,24,40,.08);
    }
    body{
      background:
        radial-gradient(900px 520px at -10% -10%, rgba(124,58,237,.10), transparent 45%),
        radial-gradient(900px 520px at 110% 0%, rgba(0,212,255,.10), transparent 45%),
        var(--bg);
      color:var(--text);
      font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, sans-serif;
    }
    .wrap{ max-width:1100px; margin:28px auto; padding:0 16px }

    /* Hero */
    .hero{
      display:flex; align-items:center; gap:12px;
      background:linear-gradient(180deg, rgba(255,255,255,.88), rgba(255,255,255,.96));
      border:1px solid var(--border); border-radius:var(--radius-lg);
      padding:14px 16px; box-shadow:var(--shadow); margin-bottom:18px;
    }
    .hero .icon{
      width:42px;height:42px;border-radius:12px;display:grid;place-items:center;color:#fff;
      background:linear-gradient(135deg, var(--brand), var(--brand2));
      box-shadow:0 12px 24px rgba(124,58,237,.25);
      font-size:1.2rem;
    }
    .hero .title{ font-weight:800; font-size:1.2rem }
    .hero .sub{ color:var(--muted); font-size:.95rem }

    /* Blocks */
    .blocks{ display:flex; flex-direction:column; gap:18px }
    @media (min-width:992px){ .blocks{ flex-direction:row } }

    .block{
      border:1px solid var(--border); border-radius:var(--radius);
      background:var(--panel); box-shadow:var(--shadow);
      overflow:hidden; display:flex; flex-direction:column; flex:1;
    }
    .block-header{ padding:14px 18px; border-bottom:1px solid var(--border); font-weight:800 }
    .block-body{ padding:16px 18px }
    .block-footer{ padding:12px 18px; border-top:1px solid var(--border); background:#fafbff }

    .pill{
      display:inline-flex; align-items:center; gap:8px; padding:6px 10px; border-radius:999px;
      background:#f3f4ff; color:#3730a3; border:1px solid #e7e7fe; font-weight:700; font-size:.8rem;
    }

    /* Info grid */
    .info-item{ background:#fff; border:1px solid var(--border); border-radius:12px; padding:12px }
    .info-item .label{ color:#64748b; font-weight:700; font-size:.85rem; margin-bottom:2px }
    .info-item .value{ font-weight:800 }

    /* Table */
    .table{
      border:1px solid var(--border); border-radius:12px; overflow:hidden; background:#fff;
    }
    .table thead{ background:#f6f7fb }
    .table thead th{ border:0; font-weight:800; color:#111827 }
    .table tbody td{ border-color:#eef1f6 }
    .table tfoot{ background:#fafbff }

    /* Buttons */
    .btn{ border-radius:999px; font-weight:700; border:1px solid var(--border) }
    .btn-secondary{ background:#fff; color:#0f172a }
    .btn-secondary:hover{ background:#f6f7fb }
    .btn-primary{
      background:linear-gradient(135deg, var(--brand), var(--brand2));
      border-color:transparent; color:#fff; box-shadow:0 10px 22px rgba(124,58,237,.25);
    }
    .btn-primary:hover{ filter:brightness(1.03) }
  </style>
</head>
<body>
  <div class="wrap">
    <!-- HERO -->
    <div class="hero">
      <div class="icon"><i class="bi bi-receipt"></i></div>
      <div class="flex-grow-1">
        <div class="title">Boleta #<?= (int)$venta['id_venta'] ?></div>
        <div class="sub">Resumen de compra y detalle de productos</div>
      </div>
      <div class="d-flex gap-2">
        <a href="ver_boletas.php" class="btn btn-secondary"><i class="bi bi-arrow-left-circle me-1"></i> Volver</a>
        <button onclick="window.print()" class="btn btn-primary"><i class="bi bi-printer me-1"></i> Imprimir</button>
      </div>
    </div>

    <!-- 2 BLOQUES -->
    <div class="blocks">
      <!-- Bloque 1: Datos de la boleta -->
      <section class="block">
        <div class="block-header">Datos de la boleta</div>
        <div class="block-body">
          <div class="row g-3">
            <div class="col-md-6">
              <div class="info-item">
                <div class="label">Vendedor</div>
                <div class="value"><?= htmlspecialchars($venta['nombre_completo']) ?></div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="info-item">
                <div class="label">Fecha</div>
                <div class="value">
                  <?= htmlspecialchars(date("d/m/Y H:i", strtotime($venta['fecha'] ?? $venta['fecha_venta'] ?? 'now'))) ?>
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="info-item">
                <div class="label">ID Boleta</div>
                <div class="value">#<?= (int)$venta['id_venta'] ?></div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="info-item">
                <div class="label">Total</div>
                <div class="value">S/ <?= number_format($venta['total'] ?? 0, 2) ?></div>
              </div>
            </div>
          </div>
          <div class="mt-3">
            <span class="pill"><i class="bi bi-check2-circle"></i> Pago registrado</span>
          </div>
        </div>
      </section>

      <!-- Bloque 2: Detalle de productos -->
      <section class="block">
        <div class="block-header">Detalle de productos</div>
        <div class="block-body">
          <div class="table-responsive">
            <table class="table table-hover text-center align-middle">
              <thead>
                <tr>
                  <th>Producto</th>
                  <th>Cantidad</th>
                  <th>Precio Unitario</th>
                  <th>Subtotal</th>
                </tr>
              </thead>
              <tbody>
                <?php 
                $suma = 0;
                while ($fila = $detalles->fetch_assoc()):
                  $sub = (float)$fila['cantidad'] * (float)$fila['precio_unitario'];
                  $suma += $sub;
                ?>
                <tr>
                  <td><?= htmlspecialchars($fila['nombre_producto']) ?></td>
                  <td><?= (int)$fila['cantidad'] ?></td>
                  <td>S/ <?= number_format($fila['precio_unitario'], 2) ?></td>
                  <td>S/ <?= number_format($sub, 2) ?></td>
                </tr>
                <?php endwhile; ?>
              </tbody>
              <tfoot>
                <tr>
                  <td colspan="3" class="text-end fw-bold">TOTAL</td>
                  <td class="fw-bold">S/ <?= number_format($suma, 2) ?></td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>
        <div class="block-footer text-muted small">
          Los importes incluyen IGV si corresponde.
        </div>
      </section>
    </div>
  </div>
</body>
</html>
