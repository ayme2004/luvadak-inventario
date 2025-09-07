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
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
  <!-- Bootstrap + Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet"/>
  <style>
    :root{
      --bg:#f8fafc; --panel:#ffffff; --text:#0f172a; --muted:#667085; --border:#e6e9f2;
      --brand:#7c3aed; --brand2:#00d4ff; --ring:rgba(124,58,237,.22);
      --radius:14px; --radius-lg:18px; --shadow:0 12px 28px rgba(16,24,40,.08);
      --ok:#16a34a; --warn:#f59e0b; --bad:#dc2626;
      --safe-top:env(safe-area-inset-top,0px); --safe-bottom:env(safe-area-inset-bottom,0px);
    }
    /* Tipografía fluida */
    .fs-fluid-sm{ font-size:clamp(.95rem,.9rem + .3vw,1.05rem) }
    .fs-fluid-md{ font-size:clamp(1.05rem,1rem + .6vw,1.25rem) }
    .fs-fluid-lg{ font-size:clamp(1.18rem,1.05rem + 1vw,1.5rem) }

    body{
      background:
        radial-gradient(900px 520px at -10% -10%, rgba(124,58,237,.10), transparent 45%),
        radial-gradient(900px 520px at 110% 0%, rgba(0,212,255,.10), transparent 45%),
        var(--bg);
      color:var(--text);
      font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, sans-serif;
      -webkit-font-smoothing:antialiased; -moz-osx-font-smoothing:grayscale;
      padding-bottom:max(10px,var(--safe-bottom));
    }
    .wrap{ max-width:1200px; margin:calc(18px + var(--safe-top)) auto 28px; padding:0 16px }

    /* Hero / encabezado (sticky en móvil) */
    .hero{
      position:sticky; top:0; z-index:10;
      display:flex; gap:12px; align-items:center; flex-wrap:wrap;
      background:#fff; border:1px solid var(--border); border-radius:var(--radius-lg);
      padding:12px 14px; box-shadow:var(--shadow); margin-bottom:18px;
      backdrop-filter:saturate(120%) blur(6px);
    }
    .hero .icon{
      width:52px; height:52px; border-radius:14px; display:grid; place-items:center; color:#fff;
      background:linear-gradient(135deg, var(--brand), var(--brand2));
      box-shadow:0 12px 24px rgba(124,58,237,.25); font-size:1.35rem;
    }
    .hero .title{ font-weight:800; font-size:1.25rem }
    .hero .sub{ color:var(--muted); font-size:.95rem }

    /* Botones accesibles */
    .btn{
      border-radius:999px; font-weight:800; border:1px solid var(--border);
      min-height:44px; padding:.7rem 1rem; letter-spacing:.2px;
      box-shadow:0 6px 16px rgba(17,24,39,.06);
      transition:transform .12s ease, filter .12s ease, box-shadow .2s, background .2s, border-color .2s;
    }
    .btn:focus-visible{ outline:3px solid var(--ring); outline-offset:2px }
    .btn-primary{
      background:linear-gradient(135deg, var(--brand), var(--brand2));
      border-color:transparent; color:#fff; box-shadow:0 6px 16px rgba(124,58,237,.22);
    }
    .btn-primary:hover{ filter:brightness(1.04); transform:translateY(-2px) }
    .btn-secondary{ background:#fff; color:#0f172a; border-color:var(--border) }
    .btn-secondary:hover{ background:#f6f7fb; transform:translateY(-2px) }
    .btn-warning.btn-sm, .btn-danger.btn-sm{
      min-height:40px; padding:.5rem .85rem; border-radius:999px; font-weight:800;
    }
    .btn-warning.btn-sm{
      background:linear-gradient(135deg, #f59e0b, #fbbf24); border-color:transparent; color:#1f2937;
      box-shadow:0 6px 14px rgba(245,158,11,.22);
    }
    .btn-danger.btn-sm{
      background:linear-gradient(135deg, #ef4444, #f97316); border-color:transparent; color:#fff;
      box-shadow:0 6px 14px rgba(239,68,68,.22);
    }

    /* Bloque principal */
    .block{ border:1px solid var(--border); border-radius:var(--radius); background:var(--panel); box-shadow:var(--shadow); overflow:hidden }
    .block-header{ padding:14px 18px; border-bottom:1px solid var(--border); background:#fff }
    .block-body{ padding:16px 18px }

    /* Buscador */
    .search-wrap{ max-width:560px; position:relative; width:100% }
    .search-wrap .form-control{
      height:46px; padding-left:42px; border-radius:999px; border:1px solid var(--border);
      box-shadow:0 6px 16px rgba(17,24,39,.06);
    }
    .search-wrap .form-control:focus{ box-shadow:0 0 0 4px var(--ring); border-color:transparent }
    .search-wrap .icon{ position:absolute; left:14px; top:50%; transform:translateY(-50%); color:var(--brand) }

    /* Tabla */
    .table-card{ border:1px solid var(--border); border-radius:12px; overflow:auto; background:#fff }
    thead.sticky th{ position:sticky; top:0; z-index:1; background:#f6f7fb; border-bottom:1px solid var(--border) }
    .table-hover tbody tr:hover{ background:#fafbff }
    .mono{ font-variant-numeric:tabular-nums; font-feature-settings:"tnum" on, "lnum" on; }
    .pill{ display:inline-flex; align-items:center; gap:.4rem; padding:.25rem .6rem; border-radius:999px; font-weight:700; font-size:.8rem; border:1px solid #e6e9f2; background:#f8fafc; }
    .badge-stock{ font-weight:800 }
    .stock-ok{ color:var(--ok) } .stock-low{ color:var(--warn) } .stock-zero{ color:var(--bad) }

    /* Scrollbar suave en desktop/tablet */
    .table-responsive{ scrollbar-width:thin }
    .table-responsive::-webkit-scrollbar{ height:8px }
    .table-responsive::-webkit-scrollbar-thumb{ background:#d9def0; border-radius:999px }

    /* ===== Responsive: tarjetas en móvil ===== */
    @media (max-width: 768px){
      .block-header form{ gap:10px }
      .block-header .btn{ flex:1 1 auto }

      /* ✅ No incluir thead aquí, para poder ocultarlo */
      .table-card table, .table-card tbody, .table-card th, .table-card td, .table-card tr{
        display:block; width:100%;
      }

      /* ✅ Ocultar encabezado en móvil (más específico) */
      .table-card thead{ display:none !important; }
      .table-card thead.sticky th{ position:static !important; }

      .table-card tr{
        margin-bottom:12px; border:1px solid var(--border); border-radius:12px; padding:12px; box-shadow:var(--shadow); background:#fff;
      }
      .table-card td{ border:none; padding:6px 0; text-align:left; white-space:normal }
      .table-card td::before{ content:attr(data-label); display:block; font-weight:700; color:var(--muted); margin-bottom:2px }

      .actions{ display:grid !important; grid-template-columns:1fr 1fr; gap:.6rem; justify-content:stretch !important }
      .actions .btn{ width:100% }
    }

    /* Movimiento reducido */
    @media (prefers-reduced-motion: reduce){
      *{ transition:none!important; animation:none!important; scroll-behavior:auto!important }
    }
  </style>
</head>
<body>
  <div class="wrap">
    <!-- Hero -->
    <div class="hero">
      <div class="icon"><i class="bi bi-box-seam-fill"></i></div>
      <div class="flex-grow-1">
        <div class="title fs-fluid-lg">Lista de Productos Registrados</div>
        <div class="sub fs-fluid-sm">Búsqueda por nombre o categoría, con acciones rápidas</div>
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
          <div class="d-flex gap-2 flex-wrap">
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
