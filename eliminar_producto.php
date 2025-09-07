<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'admin') {
    header("Location: login.php");
    exit();
}
require_once "conexion.php";

$RUTA_MOVIMIENTOS = "movimientos_inventario.php";
$EMBED = isset($_GET['embed']) && $_GET['embed'] == '1';

if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
    header("Location: ver_productos.php");
    exit();
}
$id = (int)$_GET['id'];

$sql = "SELECT p.id_producto, p.nombre_producto, p.descripcion, p.precio, p.stock, p.talla, p.color,
               p.id_categoria, c.nombre_categoria
        FROM productos p
        LEFT JOIN categorias c ON c.id_categoria = p.id_categoria
        WHERE p.id_producto = ?";
$stm = $conexion->prepare($sql);
$stm->bind_param("i", $id);
$stm->execute();
$res = $stm->get_result();
if ($res->num_rows === 0) {
    $stm->close();
    header("Location: ver_productos.php?msg=no_encontrado");
    exit();
}
$prod = $res->fetch_assoc();
$stm->close();

$mov = $conexion->prepare("SELECT COUNT(*) AS total FROM movimientosinventario WHERE id_producto = ?");
$mov->bind_param("i", $id);
$mov->execute();
$totalMovs = (int)$mov->get_result()->fetch_assoc()['total'];
$mov->close();

$dv = $conexion->prepare("SELECT COUNT(*) AS total FROM detalle_venta WHERE id_producto = ?");
$dv->bind_param("i", $id);
$dv->execute();
$totalVentas = (int)$dv->get_result()->fetch_assoc()['total'];
$dv->close();

if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
$csrf = $_SESSION['csrf'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf'] ?? '';
    if (!hash_equals($_SESSION['csrf'], $token)) {
        http_response_code(400);
        echo "Token inválido.";
        exit();
    }

    $mov2 = $conexion->prepare("SELECT COUNT(*) AS total FROM movimientosinventario WHERE id_producto = ?");
    $mov2->bind_param("i", $id);
    $mov2->execute();
    $totalMovs2 = (int)$mov2->get_result()->fetch_assoc()['total'];
    $mov2->close();

    $dv2 = $conexion->prepare("SELECT COUNT(*) AS total FROM detalle_venta WHERE id_producto = ?");
    $dv2->bind_param("i", $id);
    $dv2->execute();
    $totalVentas2 = (int)$dv2->get_result()->fetch_assoc()['total'];
    $dv2->close();

    if ($totalMovs2 > 0) {
        $error = "No se puede eliminar: el producto tiene movimientos de inventario registrados.";
    } elseif ($totalVentas2 > 0) {
        $error = "No se puede eliminar: el producto está presente en ventas.";
    } else {
        $del = $conexion->prepare("DELETE FROM productos WHERE id_producto = ?");
        $del->bind_param("i", $id);
        if ($del->execute()) {
            $del->close();
            unset($_SESSION['csrf']);

            if ($EMBED) {
                ?>
                <!doctype html>
                <html><head><meta charset="utf-8"></head><body>
                <script>
                  try {
                    parent.postMessage({type:'product:deleted', id: <?= (int)$id ?>}, window.origin || '*');
                  } catch(e) {
                    parent.postMessage({type:'product:deleted', id: <?= (int)$id ?>}, '*');
                  }
                </script>
                </body></html>
                <?php
                exit();
            } else {
                header("Location: ver_productos.php?msg=producto_eliminado");
                exit();
            }
        } else {
            if ($conexion->errno === 1451) {
                $error = "No se puede eliminar: existen registros relacionados (ventas o movimientos).";
            } else {
                $error = "Error al eliminar: " . $del->error;
            }
            $del->close();
        }
    }

    $_SESSION['csrf'] = bin2hex(random_bytes(32));
    $csrf = $_SESSION['csrf'];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Eliminar Producto - <?= htmlspecialchars($prod['nombre_producto']) ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    :root{
      --bg:#f8fafc; --panel:#ffffff; --text:#0f172a; --muted:#667085; --border:#e6e9f2;
      --brand:#7c3aed; --ring:rgba(124,58,237,.24);
      --danger:#dc2626; --danger-2:#b91c1c; --ok:#16a34a;
      --radius:14px; --radius-sm:12px; --shadow:0 2px 10px rgba(16,24,40,.06);
    }
    body{ background: radial-gradient(900px 520px at 110% -10%, rgba(124,58,237,.06), transparent 45%), var(--bg); color:var(--text); font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, sans-serif; }
    .page{ max-width:1200px; margin:24px auto; padding:0 16px }
    .title{ display:flex; align-items:center; gap:.6rem; font-weight:700; font-size:1.35rem; margin-bottom:16px }
    .title .dot{ width:10px; height:10px; border-radius:999px; background:var(--brand); box-shadow:0 0 0 6px rgba(124,58,237,.12) }

    .blocks{ display:flex; flex-direction:column; gap:18px }
    @media (min-width:992px){ .blocks{ flex-direction:row } }

    .block{ background:var(--panel); border:1px solid var(--border); border-radius:var(--radius); box-shadow:var(--shadow); overflow:hidden; display:flex; flex-direction:column }
    .block-header{ padding:14px 18px; border-bottom:1px solid var(--border); background:#fff; font-weight:600 }
    .block-body{ padding:18px }
    .block-footer{ padding:14px 18px; border-top:1px solid var(--border); background:#fafbff }
    .block.summary{ flex:1; max-width:420px }
    .block.confirm{ flex:1.4 }

    .product-title{ font-weight:800; font-size:1.05rem }
    .muted{ color:var(--muted) }
    .badge-soft{ display:inline-block; padding:.35rem .6rem; border-radius:999px; font-weight:700; font-size:.78rem; background:#f4f2ff; color:#3f2ab5; border:1px solid #e8e5ff }

    .kpi{ display:flex; align-items:center; gap:8px; border:1px solid var(--border); border-radius:10px; padding:10px 12px; background:#fff }
    .kpi .value{ font-weight:800 }
    .swatch{ width:26px; height:26px; border-radius:999px; border:1px solid #e5e7eb; display:inline-block }

    .alert-soft{ border-radius:12px; padding:14px 16px; border:1px solid #fde68a; background:#fffbeb; color:#92400e }
    .alert-danger-soft{ border-radius:12px; padding:14px 16px; border:1px solid rgba(220,38,38,.22); background:rgba(220,38,38,.08); color:#7f1d1d }

    .btn{ border-radius:var(--radius-sm); font-weight:600 }
    .btn-danger{ background:var(--danger); border-color:var(--danger) }
    .btn-danger:hover{ background:var(--danger-2); border-color:var(--danger-2) }
    .btn-secondary{ background:#fff; color:var(--text); border:1px solid var(--border) }
    .btn-ok{ background:var(--ok); border-color:var(--ok); color:#fff }

    /* Ocultar botones redundantes en EMBED y móvil */
    <?php if ($EMBED): ?> .hide-when-embed{ display:none !important; } <?php endif; ?>

    @media (max-width: 576px){
      .mobile-hide{ display:none !important; }
      .page{ margin:14px auto }
      .block.summary{ max-width:100% }
    }
  </style>
</head>
<body>
  <div class="page">
    <div class="title"><span class="dot"></span> Eliminar producto</div>

    <div class="blocks">
      <!-- Resumen -->
      <section class="block summary">
        <div class="block-header">Resumen</div>
        <div class="block-body">
          <div class="d-flex justify-content-between">
            <div>
              <div class="product-title"><?= htmlspecialchars($prod['nombre_producto']) ?></div>
              <?php if (!empty($prod['nombre_categoria'])): ?>
                <div class="mt-1"><span class="badge-soft"><?= htmlspecialchars($prod['nombre_categoria']) ?></span></div>
              <?php endif; ?>
            </div>
          </div>

          <hr class="my-3">

          <div class="row g-2">
            <div class="col-12">
              <div class="kpi"><span class="muted">Precio</span><span class="value ms-auto">S/ <?= number_format((float)$prod['precio'], 2) ?></span></div>
            </div>
            <div class="col-6">
              <div class="kpi"><span class="muted">Stock</span><span class="value ms-auto"><?= (int)$prod['stock'] ?></span></div>
            </div>
            <div class="col-6">
              <div class="kpi"><span class="muted">Talla</span><span class="value ms-auto"><?= htmlspecialchars($prod['talla']) ?></span></div>
            </div>
            <div class="col-12">
              <div class="kpi">
                <span class="muted">Color</span>
                <span class="ms-auto d-flex align-items-center gap-2">
                  <span class="swatch" style="background: <?= htmlspecialchars($prod['color']) ?>;"></span>
                  <code class="muted"><?= htmlspecialchars($prod['color']) ?></code>
                </span>
              </div>
            </div>
          </div>

          <?php if (!empty($prod['descripcion'])): ?>
            <div class="mt-3">
              <div class="muted">Descripción</div>
              <div><?= nl2br(htmlspecialchars($prod['descripcion'])) ?></div>
            </div>
          <?php endif; ?>
        </div>

        <!-- Footer con volver/editar: oculto en EMBED y en móvil -->
        <div class="block-footer d-flex justify-content-between hide-when-embed mobile-hide">
          <a href="ver_productos.php" class="btn btn-secondary">⬅️ Volver</a>
          <a href="editar_producto.php?id=<?= (int)$prod['id_producto'] ?>" class="btn btn-secondary">✏️ Editar</a>
        </div>
      </section>

      <!-- Confirmación -->
      <section class="block confirm">
        <div class="block-header">Confirmar eliminación</div>
        <div class="block-body">
          <?php if(isset($error)): ?>
            <div class="alert-danger-soft mb-3"><?= htmlspecialchars($error) ?></div>
          <?php endif; ?>

          <?php if ($totalMovs > 0 || $totalVentas > 0): ?>
            <div class="alert-danger-soft mb-3">
              ❌ No se puede eliminar este producto.<br>
              <?php if ($totalMovs > 0): ?>• Tiene <strong><?= $totalMovs ?></strong> movimiento(s) de inventario.<br><?php endif; ?>
              <?php if ($totalVentas > 0): ?>• Está referenciado en <strong><?= $totalVentas ?></strong> venta(s).<?php endif; ?>
            </div>
            <?php if (!$EMBED): ?>
              <a href="ver_productos.php" class="btn btn-secondary">Volver</a>
            <?php endif; ?>
          <?php else: ?>
            <div class="alert-soft mb-3">
              <strong>⚠ Acción irreversible.</strong> Esta operación eliminará el producto del sistema.
            </div>
            <ul class="mb-3">
              <li>Se borrará el registro del producto.</li>
              <li>No podrás recuperarlo luego.</li>
              <li>Verifica que no existan dependencias externas.</li>
            </ul>

            <form method="POST" class="mt-3">
              <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
              <div class="d-flex gap-2 justify-content-end">
                <?php if (!$EMBED): ?><a href="ver_productos.php" class="btn btn-secondary">Cancelar</a><?php endif; ?>
                <button type="submit" class="btn btn-danger">Eliminar definitivamente</button>
              </div>
            </form>
          <?php endif; ?>
        </div>
      </section>
    </div>
  </div>
</body>
</html>
