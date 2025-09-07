<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'admin') {
    header("Location: login.php");
    exit();
}
include("conexion.php");

/* ===== Validar id y obtener cliente (prepared) ===== */
$id_cliente = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stCli = $conexion->prepare("SELECT id_cliente, nombre_completo, dni, telefono, correo, direccion, fecha_registro FROM clientes WHERE id_cliente = ?");
$stCli->bind_param("i", $id_cliente);
$stCli->execute();
$cliente = $stCli->get_result()->fetch_assoc();
$stCli->close();

if (!$cliente) {
    header("Location: clientes.php?msg=cliente_no_encontrado");
    exit();
}

/* ===== Ventas del cliente ===== */
$st = $conexion->prepare("
  SELECT v.id_venta, v.fecha, p.nombre_producto, d.cantidad, d.precio_unitario
  FROM ventas v
  JOIN detalle_venta d ON v.id_venta = d.id_venta
  JOIN productos p ON d.id_producto = p.id_producto
  WHERE v.id_cliente = ?
  ORDER BY v.fecha DESC
");
$st->bind_param("i", $id_cliente);
$st->execute();
$resultado = $st->get_result();

/* ===== KPIs ===== */
$total_general = 0.0;
$total_items   = 0;
$ventas        = [];
while ($row = $resultado->fetch_assoc()) {
    $row['total'] = (float)$row['cantidad'] * (float)$row['precio_unitario'];
    $total_general += $row['total'];
    $total_items   += (int)$row['cantidad'];
    $ventas[] = $row;
}
$st->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Historial del Cliente - <?= htmlspecialchars($cliente['nombre_completo']) ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    :root{
      --bg:#f8fafc; --panel:#ffffff; --text:#0f172a; --muted:#667085; --border:#e6e9f2;
      --brand:#7c3aed; --brand2:#00d4ff; --ring:rgba(124,58,237,.22);
      --radius:12px; --radius-lg:16px; --shadow:0 2px 12px rgba(16,24,40,.08);
      --success:#16a34a;
    }
    body{
      background:
        radial-gradient(900px 520px at 110% -10%, rgba(124,58,237,.06), transparent 45%),
        var(--bg);
      color:var(--text);
      font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, sans-serif;
    }

    .page{ max-width:1250px; margin:34px auto 40px; padding:0 16px }

    /* Hero */
    .hero{
      display:flex; align-items:center; gap:12px;
      background:linear-gradient(180deg, rgba(255,255,255,.9), rgba(255,255,255,.98));
      border:1px solid var(--border); border-radius:var(--radius-lg);
      padding:14px 16px; box-shadow:var(--shadow); margin-bottom:16px;
    }
    .hero .icon{
      width:38px;height:38px;border-radius:12px;display:grid;place-items:center;color:#fff;
      background:linear-gradient(135deg,var(--brand),var(--brand2));
      box-shadow:0 10px 24px rgba(124,58,237,.22);
    }
    .hero h3{ margin:0; font-weight:800; font-size:1.15rem }
    .hero .sub{ color:var(--muted) }

    /* Layout 2 bloques */
    .blocks{ display:flex; flex-direction:column; gap:18px }
    @media (min-width: 992px){ .blocks{ flex-direction:row } }

    .block{
      border:1px solid var(--border); background:var(--panel);
      border-radius:var(--radius-lg); box-shadow:var(--shadow);
      overflow:hidden; display:flex; flex-direction:column;
    }
    .block-header{ padding:14px 16px; border-bottom:1px solid var(--border); font-weight:700 }
    .block-body{ padding:16px 16px }
    .block-footer{ padding:12px 16px; border-top:1px solid var(--border); background:#fafbff }

    .block.left{ flex:1; max-width:390px }
    .block.right{ flex:1.6 }

    /* Ficha cliente + KPIs */
    .kpi{ border:1px solid var(--border); border-radius:12px; padding:12px; background:#fff }
    .kpi .label{ color:var(--muted); font-size:.9rem }
    .kpi .value{ font-weight:800; font-size:1.1rem }
    .money{ color:var(--success); font-weight:800 }

    .badge-soft{
      background:#f4f2ff; color:#3f2ab5; border:1px solid #e8e5ff;
      border-radius:999px; padding:.22rem .6rem; font-weight:700; font-size:.78rem;
    }

    /* Tabla */
    .table-modern{ border:1px solid var(--border); border-radius:12px; overflow:hidden }
    .table-modern thead{ background:#f6f7fb }
    .table-modern th{ border:0; font-weight:700 }
    .table-modern td{ border-color:#eef1f6 }
    .table-modern tbody tr:hover{ background:#fafbff }

    /* Buttons */
    .btn{ border-radius:999px; font-weight:700; padding:.6rem 1rem; border:1px solid var(--border) }
    .btn-primary{
      background:linear-gradient(135deg,var(--brand),var(--brand2));
      border-color:transparent; color:#fff; box-shadow:0 6px 16px rgba(124,58,237,.22);
    }
    .btn-primary:hover{ filter:brightness(1.04); transform:translateY(-2px) }
    .btn-secondary{ background:#fff; color:var(--text) }
    .btn-secondary:hover{ background:#f9f9ff; transform:translateY(-2px) }

    .pill{ display:inline-flex; align-items:center; gap:.35rem }
  </style>
</head>
<body>
  <div class="page">
    <!-- Hero -->
    <div class="hero">
      <div class="icon"><i class="bi bi-person-vcard"></i></div>
      <div>
        <h3>Historial de Compras</h3>
        <div class="sub">Cliente: <strong><?= htmlspecialchars($cliente['nombre_completo']) ?></strong></div>
      </div>
      <div class="ms-auto d-flex gap-2">
        <a href="clientes.php" class="btn btn-secondary"><i class="bi bi-arrow-left-circle me-1"></i>Volver</a>
        <?php if (!empty($ventas)): ?>
          <a href="exportar_pdf_cliente.php?id=<?= (int)$cliente['id_cliente'] ?>" class="btn btn-primary">
            <i class="bi bi-filetype-pdf me-1"></i>PDF
          </a>
        <?php endif; ?>
      </div>
    </div>

    <div class="blocks">
      <!-- Bloque izquierdo: ficha + KPIs -->
      <section class="block left">
        <div class="block-header">Ficha del cliente</div>
        <div class="block-body">
          <div class="vstack gap-2 mb-3">
            <div><span class="badge-soft pill"><i class="bi bi-person"></i><?= htmlspecialchars($cliente['nombre_completo']) ?></span></div>
            <?php if ($cliente['dni']): ?><div class="text-muted">DNI: <strong><?= htmlspecialchars($cliente['dni']) ?></strong></div><?php endif; ?>
            <?php if ($cliente['telefono']): ?><div class="text-muted">Tel: <strong><?= htmlspecialchars($cliente['telefono']) ?></strong></div><?php endif; ?>
            <?php if ($cliente['correo']): ?><div class="text-muted">Correo: <strong><?= htmlspecialchars($cliente['correo']) ?></strong></div><?php endif; ?>
            <?php if ($cliente['direccion']): ?><div class="text-muted">Dirección: <strong><?= htmlspecialchars($cliente['direccion']) ?></strong></div><?php endif; ?>
            <div class="text-muted">Cliente desde: <strong><?= date('d/m/Y', strtotime($cliente['fecha_registro'])) ?></strong></div>
          </div>

          <hr class="my-3">

          <div class="vstack gap-2">
            <div class="kpi">
              <div class="label">Total gastado</div>
              <div class="value money">S/ <?= number_format($total_general,2) ?></div>
            </div>
            <div class="kpi">
              <div class="label">Unidades compradas</div>
              <div class="value"><?= (int)$total_items ?></div>
            </div>
            <div class="kpi">
              <div class="label">Compras registradas</div>
              <div class="value"><?= count($ventas) ?></div>
            </div>
          </div>
        </div>
      </section>

      <!-- Bloque derecho: tabla -->
      <section class="block right">
        <div class="block-header">Detalle de compras</div>
        <div class="block-body">
          <?php if (empty($ventas)): ?>
            <div class="alert alert-warning rounded-3"><i class="bi bi-info-circle me-1"></i>Este cliente no tiene compras registradas.</div>
          <?php else: ?>
            <div class="table-responsive table-modern">
              <table class="table align-middle text-center">
                <thead>
                  <tr>
                    <th>📅 Fecha</th>
                    <th># Venta</th>
                    <th>Producto</th>
                    <th>Cant.</th>
                    <th>Precio Unit.</th>
                    <th>Total</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($ventas as $v): ?>
                    <tr>
                      <td><?= date("d/m/Y", strtotime($v['fecha'])) ?></td>
                      <td><?= (int)$v['id_venta'] ?></td>
                      <td><?= htmlspecialchars($v['nombre_producto']) ?></td>
                      <td><?= (int)$v['cantidad'] ?></td>
                      <td>S/ <?= number_format($v['precio_unitario'],2) ?></td>
                      <td class="money">S/ <?= number_format($v['total'],2) ?></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
                <tfoot class="table-secondary fw-bold">
                  <tr>
                    <td colspan="5" class="text-end">TOTAL GENERAL</td>
                    <td class="money">S/ <?= number_format($total_general,2) ?></td>
                  </tr>
                </tfoot>
              </table>
            </div>
          <?php endif; ?>
        </div>
      </section>
    </div>
  </div>
</body>
</html>
