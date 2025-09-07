
<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'admin') {
    header("Location: login.php");
    exit();
}

include("conexion.php");

if (!isset($_GET['id'])) {
    header("Location: ver_productos.php");
    exit();
}

$id_producto = intval($_GET['id']);

$sql = "SELECT * FROM productos WHERE id_producto = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id_producto);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    echo "Producto no encontrado.";
    exit();
}

$producto = $resultado->fetch_assoc();

/* Categorías para el select */
$categorias = $conexion->query("SELECT id_categoria, nombre_categoria FROM categorias");

/* Nombre de la categoría actual (para el resumen) */
$categoriaActual = '';
if (!empty($producto['id_categoria'])) {
  $cstmt = $conexion->prepare("SELECT nombre_categoria FROM categorias WHERE id_categoria = ?");
  $cstmt->bind_param("i", $producto['id_categoria']);
  $cstmt->execute();
  $cres = $cstmt->get_result();
  if ($row = $cres->fetch_assoc()) {
    $categoriaActual = $row['nombre_categoria'];
  }
  $cstmt->close();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Editar Producto - <?= htmlspecialchars($producto['nombre_producto']) ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    :root{
      --bg:#f8fafc; --panel:#ffffff; --text:#0f172a; --muted:#667085; --border:#e6e9f2;
      --brand:#7c3aed; --ring:rgba(124,58,237,.22); --radius:12px; --radius-sm:10px; --shadow:0 2px 10px rgba(16,24,40,.06);
    }
    body{
      background: radial-gradient(900px 520px at 110% -10%, rgba(124,58,237,.06), transparent 45%), var(--bg);
      color:var(--text); font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, sans-serif;
    }
    .page{ max-width:1200px; margin:36px auto; padding:0 16px }
    .page-title{ display:flex; align-items:center; gap:.6rem; font-weight:700; font-size:1.35rem; margin-bottom:18px }
    .page-title .dot{ width:10px; height:10px; border-radius:999px; background:var(--brand); box-shadow:0 0 0 6px rgba(124,58,237,.12) }
    .blocks{ display:flex; flex-direction:column; gap:18px } @media (min-width: 992px){ .blocks{ flex-direction:row } }
    .block{ background:var(--panel); border:1px solid var(--border); border-radius:var(--radius); box-shadow:var(--shadow); overflow:hidden; display:flex; flex-direction:column }
    .block-header{ background:#fff; border-bottom:1px solid var(--border); padding:14px 18px; font-weight:600 }
    .block-body{ padding:18px } .block-footer{ padding:12px 18px; border-top:1px solid var(--border); background:#fafbff }
    .block.summary{ flex:1; max-width:420px } .block.form{ flex:1.4 }
    .product-title{ font-weight:700; font-size:1.05rem } .muted{ color:var(--muted) }
    .badge-soft{ display:inline-block; padding:.35rem .6rem; border-radius:999px; font-weight:700; font-size:.78rem; background:#f4f2ff; color:#3f2ab5; border:1px solid #e8e5ff }
    .swatch{ width:26px; height:26px; border-radius:999px; border:1px solid #e5e7eb; display:inline-block; vertical-align:middle }
    .kpi{ display:flex; align-items:center; gap:8px; border:1px solid var(--border); border-radius:10px; padding:10px 12px; background:#fff }
    .kpi .value{ font-weight:800 }
    .form-label{ font-weight:600 }
    .form-control,.form-select{ border:1px solid var(--border); border-radius:var(--radius-sm); padding:10px 12px; transition:border .2s, box-shadow .2s }
    .form-control:focus,.form-select:focus{ border-color:#d4d8f0; box-shadow:0 0 0 3px var(--ring) }
    .help{ font-size:.92rem; color:var(--muted) }
    .btn{ border-radius:var(--radius-sm); font-weight:600; border:1px solid var(--border) }
    .btn-primary{ background:var(--brand); border-color:var(--brand) } .btn-primary:hover{ filter:brightness(1.04) }
    .btn-secondary{ background:#fff; color:var(--text); border:1px solid var(--border) } .btn-secondary:hover{ background:#f5f6fb }
  </style>
</head>
<body>
  <div class="page">
    <div class="page-title"><span class="dot"></span><span>Editar producto</span></div>

    <div class="blocks">
      <!-- Resumen -->
      <section class="block summary">
        <div class="block-header">Resumen</div>
        <div class="block-body">
          <div class="d-flex align-items-start justify-content-between">
            <div>
              <div class="product-title"><?= htmlspecialchars($producto['nombre_producto']) ?></div>
              <?php if ($categoriaActual): ?>
                <div class="mt-1"><span class="badge-soft"><?= htmlspecialchars($categoriaActual) ?></span></div>
              <?php endif; ?>
            </div>
          </div>

          <hr class="my-3">

          <div class="row g-2">
            <div class="col-12">
              <div class="kpi"><span class="muted">Precio</span><span class="value ms-auto">S/ <?= number_format((float)$producto['precio'], 2) ?></span></div>
            </div>
            <div class="col-6">
              <div class="kpi"><span class="muted">Stock</span><span class="value ms-auto"><?= (int)$producto['stock'] ?></span></div>
            </div>
            <div class="col-6">
              <div class="kpi"><span class="muted">Talla</span><span class="value ms-auto"><?= htmlspecialchars($producto['talla']) ?></span></div>
            </div>
            <div class="col-12">
              <div class="kpi">
                <span class="muted">Color</span>
                <span class="ms-auto d-flex align-items-center gap-2">
                  <span class="swatch" style="background: <?= htmlspecialchars($producto['color']) ?>;"></span>
                  <code class="muted"><?= htmlspecialchars($producto['color']) ?></code>
                </span>
              </div>
            </div>
          </div>

          <?php if (!empty($producto['descripcion'])): ?>
            <div class="mt-3">
              <div class="muted">Descripción</div>
              <div><?= nl2br(htmlspecialchars($producto['descripcion'])) ?></div>
            </div>
          <?php endif; ?>
        </div>
        <div class="block-footer d-flex justify-content-between">
          <a href="ver_productos.php" class="btn btn-secondary">⬅️ Volver</a>
        </div>
      </section>

      <!-- Formulario -->
      <section class="block form">
        <div class="block-header">Editar campos</div>
        <div class="block-body">
          <form action="actualizar_producto.php" method="POST" novalidate>
            <input type="hidden" name="id_producto" value="<?= (int)$producto['id_producto']; ?>">

            <div class="mb-3">
              <label class="form-label">Nombre del producto</label>
              <input type="text" name="nombre_producto" class="form-control"
                     value="<?= htmlspecialchars($producto['nombre_producto']); ?>" maxlength="120" required>
            </div>

            <div class="mb-3">
              <label class="form-label">Descripción</label>
              <textarea name="descripcion" class="form-control" rows="3" maxlength="255"
                        placeholder="Detalles, composición, cuidados…"><?= htmlspecialchars($producto['descripcion']); ?></textarea>
              <div class="help mt-1">Máx. 255 caracteres.</div>
            </div>

            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">Talla</label>
                <select name="talla" class="form-select" required>
                  <option value="" hidden>Selecciona una talla</option>
                  <?php
                  $tallas = ['XS','S','M','L','XL'];
                  foreach ($tallas as $talla) {
                      $selected = ($producto['talla'] === $talla) ? "selected" : "";
                      echo "<option value='".htmlspecialchars($talla)."' $selected>$talla</option>";
                  }
                  ?>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label">Color</label>
                <input type="text" name="color" class="form-control"
                       value="<?= htmlspecialchars($producto['color']) ?>" placeholder="#111827 o nombre de color" required>
              </div>
            </div>

            <div class="row g-3 mt-0">
              <div class="col-md-6">
                <label class="form-label">Precio (S/)</label>
                <input type="number" step="0.01" min="0" name="precio" class="form-control"
                       value="<?= htmlspecialchars($producto['precio']); ?>" required>
              </div>

              <!-- 🔒 Stock bloqueado: disabled + sin 'name' -->
              <div class="col-md-6">
                <label class="form-label">Stock (solo lectura)</label>
                <input type="number" class="form-control" value="<?= (int)$producto['stock']; ?>" disabled>
                <div class="help mt-1">
                  El stock se ajusta desde <a href="registrar_movimiento.php">Movimientos de Inventario</a> (entradas/salidas).
                </div>
              </div>
            </div>

            <div class="mt-3">
              <label class="form-label">Categoría</label>
              <select name="id_categoria" class="form-select" required>
                <option value="" hidden>Selecciona una categoría</option>
                <?php while ($cat = $categorias->fetch_assoc()):
                  $selected = ($producto['id_categoria'] == $cat['id_categoria']) ? "selected" : ""; ?>
                  <option value="<?= (int)$cat['id_categoria'] ?>" <?= $selected ?>>
                    <?= htmlspecialchars($cat['nombre_categoria']) ?>
                  </option>
                <?php endwhile; ?>
              </select>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
              <a href="ver_productos.php" class="btn btn-secondary">Cancelar</a>
              <button type="submit" class="btn btn-primary">Guardar cambios</button>
            </div>
          </form>
        </div>
      </section>
    </div>
  </div>
</body>
</html>
