<?php
session_start();
if (!isset($_SESSION['usuario'])) {
  header("Location: login.php");
  exit();
}
include("conexion.php");

/* ===== Búsqueda segura ===== */
$busqueda = isset($_GET['buscar']) ? trim($_GET['buscar']) : '';

$sqlBase = "SELECT p.id_producto, p.nombre_producto, p.descripcion, p.talla, p.color,
                   p.precio, p.stock, c.nombre_categoria
            FROM productos p
            LEFT JOIN categorias c ON p.id_categoria = c.id_categoria";

if ($busqueda !== '') {
  $sql = $sqlBase . " WHERE p.nombre_producto LIKE ? OR c.nombre_categoria LIKE ? ORDER BY p.id_producto ASC";
  $stmt = $conexion->prepare($sql);
  $like = "%{$busqueda}%";
  $stmt->bind_param("ss", $like, $like);
  $stmt->execute();
  $resultado = $stmt->get_result();
} else {
  $sql = $sqlBase . " ORDER BY p.id_producto ASC";
  $resultado = $conexion->query($sql);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Productos · Luvadak</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <!-- Bootstrap + Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet"/>
  <style>
    :root{
      --bg:#f8fafc; --panel:#ffffff; --text:#0f172a; --muted:#667085; --border:#e6e9f2;
      --brand:#7c3aed; --brand2:#00d4ff; --ring:rgba(124,58,237,.22);
      --radius:14px; --radius-lg:18px; --shadow:0 12px 28px rgba(16,24,40,.08);
      --ok:#16a34a; --warn:#f59e0b; --bad:#dc2626;
    }
    body{
      background:
        radial-gradient(900px 520px at -10% -10%, rgba(124,58,237,.10), transparent 45%),
        radial-gradient(900px 520px at 110% 0%, rgba(0,212,255,.10), transparent 45%),
        var(--bg);
      color:var(--text);
      font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, sans-serif;
    }
    .wrap{ max-width:1200px; margin:28px auto; padding:0 16px }

    /* Hero / encabezado */
    .hero{
      display:flex; gap:12px; align-items:center; flex-wrap:wrap;
      background:#fff; border:1px solid var(--border); border-radius:var(--radius-lg);
      padding:16px; box-shadow:var(--shadow); margin-bottom:18px;
    }
    .hero .icon{
      width:52px; height:52px; border-radius:14px; display:grid; place-items:center; color:#fff;
      background:linear-gradient(135deg, var(--brand), var(--brand2));
      box-shadow:0 12px 24px rgba(124,58,237,.25); font-size:1.35rem;
    }
    .hero .title{ font-weight:800; font-size:1.25rem }
    .hero .sub{ color:var(--muted); font-size:.95rem }

    .btn{ border-radius:999px; font-weight:700; border:1px solid var(--border) }
    .btn-primary{
      background:linear-gradient(135deg, var(--brand), var(--brand2));
      border-color:transparent; color:#fff; box-shadow:0 6px 16px rgba(124,58,237,.28);
    }
    .btn-secondary{ background:#fff; color:#0f172a; border-color:var(--border) }
    .btn-warning.btn-sm, .btn-danger.btn-sm{ border-radius:999px; font-weight:700 }

    /* Bloque principal */
    .block{ border:1px solid var(--border); border-radius:var(--radius); background:var(--panel); box-shadow:var(--shadow); overflow:hidden }
    .block-header{ padding:14px 18px; border-bottom:1px solid var(--border) }
    .block-body{ padding:16px 18px }

    /* Buscador */
    .search-wrap{ max-width:560px; position:relative }
    .search-wrap .form-control{ height:46px; padding-left:42px; border-radius:999px; border:1px solid var(--border) }
    .search-wrap .form-control:focus{ box-shadow:0 0 0 3px var(--ring); border-color:transparent }
    .search-wrap .icon{ position:absolute; left:14px; top:50%; transform:translateY(-50%); color:var(--brand) }

    /* Tabla */
    .table-card{ border:1px solid var(--border); border-radius:12px; overflow:auto; background:#fff }
    thead.sticky th{ position:sticky; top:0; z-index:1; background:#f6f7fb; border-bottom:1px solid var(--border) }
    .table-hover tbody tr:hover{ background:#fafbff }
    .mono{ font-variant-numeric:tabular-nums; font-feature-settings:"tnum" on, "lnum" on; }
    .pill{ display:inline-flex; align-items:center; gap:.4rem; padding:.25rem .6rem; border-radius:999px; font-weight:700; font-size:.8rem; border:1px solid #e6e9f2; background:#f8fafc; }
    .badge-stock{ font-weight:800 }
    .stock-ok{ color:var(--ok) } .stock-low{ color:var(--warn) } .stock-zero{ color:var(--bad) }

    /* ===== Responsive: tarjetas en móvil ===== */
    @media (max-width: 768px){
      .table-card table, .table-card thead, .table-card tbody, .table-card th, .table-card td, .table-card tr{ display:block; width:100% }
      thead{ display:none }
      .table-card tr{ margin-bottom:12px; border:1px solid var(--border); border-radius:12px; padding:12px; box-shadow:var(--shadow) }
      .table-card td{ border:none; padding:6px 0; text-align:left; white-space:normal }
      .table-card td::before{ content:attr(data-label); display:block; font-weight:700; color:var(--muted); margin-bottom:2px }
      .actions{ justify-content:flex-start !important; gap:.5rem }
    }
  </style>
</head>
<body>
  <div class="wrap">
    <!-- Hero -->
    <div class="hero">
      <div class="icon"><i class="bi bi-box-seam-fill"></i></div>
      <div class="flex-grow-1">
        <div class="title">Lista de Productos Registrados</div>
        <div class="sub">Búsqueda por nombre o categoría, con acciones rápidas</div>
      </div>
      <div class="d-none d-sm-flex">
        <a href="<?= $_SESSION['rol'] === 'admin' ? 'dashboard_admin.php' : 'dashboard_empleado.php' ?>" class="btn btn-secondary">
          <i class="bi bi-arrow-left"></i>&nbsp;Volver al Panel <?= $_SESSION['rol'] === 'admin' ? 'Admin' : 'Empleado' ?>
        </a>
      </div>
    </div>

    <!-- Bloque principal -->
    <section class="block">
      <div class="block-header">
        <form method="GET" class="d-flex gap-2 align-items-center w-100 justify-content-between flex-wrap">
          <div class="search-wrap flex-grow-1 me-2">
            <i class="bi bi-search icon"></i>
            <input
              type="text"
              name="buscar"
              class="form-control"
              placeholder="Buscar por nombre o categoría…"
              value="<?= htmlspecialchars($busqueda) ?>"
              autocomplete="off"
            />
          </div>
          <div class="d-flex gap-2">
            <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i>&nbsp;Buscar</button>
            <a href="<?= htmlspecialchars(strtok($_SERVER['REQUEST_URI'], '?')) ?>" class="btn btn-secondary">
              <i class="bi bi-arrow-clockwise"></i>&nbsp;Limpiar
            </a>
            <!-- Volver SOLO en móviles, al lado de los otros botones -->
            <a href="<?= $_SESSION['rol'] === 'admin' ? 'dashboard_admin.php' : 'dashboard_empleado.php' ?>"
               class="btn btn-secondary d-inline-flex d-sm-none">
              <i class="bi bi-arrow-left"></i>&nbsp;Volver
            </a>
          </div>
        </form>
      </div>

      <div class="block-body">
        <div class="table-responsive table-card">
          <table class="table table-hover align-middle text-center m-0">
            <thead class="sticky">
              <tr>
                <th class="mono">ID</th>
                <th>Nombre</th>
                <th>Descripción</th>
                <th>Talla</th>
                <th>Color</th>
                <th>Precio</th>
                <th>Stock</th>
                <th>Categoría</th>
                <?php if ($_SESSION['rol'] === 'admin') echo '<th>Acciones</th>'; ?>
              </tr>
            </thead>
            <tbody>
              <?php if ($resultado && $resultado->num_rows > 0): ?>
                <?php while ($fila = $resultado->fetch_assoc()):
                  $stock = (int)$fila['stock'];
                  $stockClass = $stock > 10 ? 'stock-ok' : ($stock > 0 ? 'stock-low' : 'stock-zero');
                ?>
                  <tr>
                    <td class="mono" data-label="ID"><?= $fila['id_producto']; ?></td>
                    <td class="text-start" data-label="Nombre">
                      <span class="pill"><i class="bi bi-tag"></i><?= htmlspecialchars($fila['nombre_producto']); ?></span>
                    </td>
                    <td class="text-start" data-label="Descripción"><?= htmlspecialchars($fila['descripcion']); ?></td>
                    <td data-label="Talla"><?= htmlspecialchars($fila['talla']); ?></td>
                    <td data-label="Color"><?= htmlspecialchars($fila['color']); ?></td>
                    <td class="mono fw-bold" data-label="Precio">S/ <?= number_format($fila['precio'], 2); ?></td>
                    <td class="mono" data-label="Stock"><span class="badge-stock <?= $stockClass ?>"><?= $stock ?></span></td>
                    <td data-label="Categoría">
                      <?php if (!empty($fila['nombre_categoria'])): ?>
                        <span class="pill"><i class="bi bi-collection"></i><?= htmlspecialchars($fila['nombre_categoria']); ?></span>
                      <?php else: ?>
                        <span class="text-muted">—</span>
                      <?php endif; ?>
                    </td>
                    <?php if ($_SESSION['rol'] === 'admin'): ?>
                      <td data-label="Acciones">
                        <div class="actions d-flex justify-content-center flex-wrap">
                          <a href="editar_producto.php?id=<?= $fila['id_producto']; ?>" class="btn btn-warning btn-sm">
                            <i class="bi bi-pencil-square"></i> Editar
                          </a>
                          <a href="eliminar_producto.php?id=<?= $fila['id_producto']; ?>&embed=1"
                             class="btn btn-danger btn-sm delete-link"
                             data-id="<?= $fila['id_producto']; ?>">
                            <i class="bi bi-trash-fill"></i> Eliminar
                          </a>
                        </div>
                      </td>
                    <?php endif; ?>
                  </tr>
                <?php endwhile; ?>
              <?php else: ?>
                <tr>
                  <td colspan="<?= $_SESSION['rol'] === 'admin' ? '9' : '8' ?>" class="text-center text-muted py-4">
                    <i class="bi bi-search"></i> No se encontraron productos con ese criterio.
                  </td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </section>
  </div>

  <!-- Offcanvas lateral para Eliminar (solo escritorio) -->
  <div class="offcanvas offcanvas-end" tabindex="-1" id="offDelete" aria-labelledby="offDeleteLabel">
    <div class="offcanvas-header">
      <h5 class="offcanvas-title" id="offDeleteLabel">Eliminar producto</h5>
      <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
    </div>
    <div class="offcanvas-body p-0">
      <iframe id="offDeleteFrame" style="width:100%; height:80vh; border:0"></iframe>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Si es móvil, navegar directo. Si es desktop, abrir offcanvas con iframe.
    document.querySelectorAll('.delete-link').forEach(link => {
      link.addEventListener('click', (e) => {
        e.preventDefault();
        const id = link.getAttribute('data-id');
        const isMobile = window.matchMedia('(max-width: 991.98px)').matches;

        if (isMobile) {
          window.location.href = 'eliminar_producto.php?id=' + encodeURIComponent(id); // móvil: página completa
        } else {
          const url = 'eliminar_producto.php?id=' + encodeURIComponent(id) + '&embed=1';
          document.getElementById('offDeleteFrame').src = url;
          const off = bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('offDelete'));
          off.show();
        }
      });
    });

    // Limpiar iframe al cerrar el panel
    document.getElementById('offDelete').addEventListener('hidden.bs.offcanvas', () => {
      document.getElementById('offDeleteFrame').src = 'about:blank';
    });

    // Recibir evento desde eliminar_producto.php cuando se borra (modo EMBED)
    window.addEventListener('message', (ev) => {
      if (ev && ev.data && ev.data.type === 'product:deleted') {
        const off = bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('offDelete'));
        off.hide();
        window.location.reload();
      }
    });
  </script>
</body>
</html>
<?php if (isset($stmt)) $stmt->close(); $conexion->close(); ?>
