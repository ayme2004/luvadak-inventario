<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'admin') {
  header("Location: login.php");
  exit();
}
include("conexion.php");

$sql = "SELECT m.*, p.nombre_producto 
        FROM movimientosinventario m 
        JOIN productos p ON m.id_producto = p.id_producto 
        ORDER BY m.fecha_movimiento DESC";
$movimientos = $conexion->query($sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Movimientos de Inventario · Luvadak</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <!-- Bootstrap + Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    :root{
      --bg:#f8fafc; --panel:#ffffff; --text:#0f172a; --muted:#667085; --border:#e6e9f2;
      --brand:#7c3aed; --brand2:#00d4ff; --ring:rgba(124,58,237,.22);
      --radius:14px; --radius-lg:18px; --shadow:0 10px 26px rgba(16,24,40,.08);
      --in:#16a34a; --out:#dc2626;
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

    /* Hero */
    .hero{
      display:flex; gap:12px; align-items:center; flex-wrap:wrap;
      background:linear-gradient(180deg, rgba(255,255,255,.92), rgba(255,255,255,.98));
      border:1px solid var(--border); border-radius:var(--radius-lg);
      padding:16px; box-shadow:var(--shadow); margin-bottom:18px;
    }
    .hero .icon{
      width:46px;height:46px;border-radius:12px;display:grid;place-items:center;color:#fff;
      background:linear-gradient(135deg, var(--brand), var(--brand2));
      box-shadow:0 12px 24px rgba(124,58,237,.25);
      font-size:1.25rem;
    }
    .hero .title{ font-weight:800; font-size:1.25rem }
    .hero .sub{ color:var(--muted); font-size:.95rem }
    .btn{ border-radius:999px; font-weight:700; border:1px solid var(--border) }
    .btn-primary{
      background:linear-gradient(135deg, var(--brand), var(--brand2));
      border-color:transparent; color:#fff;
      box-shadow:0 10px 22px rgba(124,58,237,.28);
    }
    .btn-secondary{ background:#fff; color:#0f172a; border-color:var(--border) }
    .btn-secondary:hover{ background:#f6f7fb }

    /* Block */
    .block{
      border:1px solid var(--border);
      border-radius:var(--radius);
      background:var(--panel);
      box-shadow:var(--shadow);
      overflow:hidden;
    }
    .block-header{
      background:#fff; border-bottom:1px solid var(--border);
      padding:14px 18px; font-weight:800; display:flex; align-items:center; gap:10px; justify-content:space-between;
      flex-wrap:wrap;
    }

    .block-body{ padding:16px 18px }

    /* Buscador */
    .search{ display:flex; gap:8px; position:relative }
    .search .form-control{
      border:1px solid var(--border); border-radius:999px; padding:.5rem 1rem;
      padding-left:2.6rem !important; width:260px; transition:all .25s ease;
    }
    .search .form-control:focus{ box-shadow:0 0 0 3px var(--ring); border-color:transparent }
    .search i{ font-size:1.1rem; pointer-events:none }

    /* Table card */
    .table-card{ border:1px solid var(--border); border-radius:12px; overflow:hidden; background:#fff }
    thead.sticky th{ position:sticky; top:0; z-index:1; background:#f6f7fb; border-bottom:1px solid var(--border) }
    .table-hover tbody tr:hover{ background:#fafbff }
    .pill{
      display:inline-flex; align-items:center; gap:.4rem;
      padding:.2rem .55rem; border-radius:999px; font-weight:700; font-size:.8rem; border:1px solid;
    }
    .pill-in{ background:#ecfdf5; color:#065f46; border-color:#a7f3d0 }
    .pill-out{ background:#fef2f2; color:#7f1d1d; border-color:#fecaca }
    .muted{ color:var(--muted) }

    /* -------- Responsive (stack table in mobile) -------- */
    @media (max-width: 768px){
      .search .form-control{ width:100% }
      .table-card table, .table-card thead, .table-card tbody, .table-card th, .table-card td, .table-card tr{
        display:block; width:100%;
      }
      .table-card thead{ display:none }
      .table-card tbody tr{
        border:1px solid var(--border); border-radius:12px; margin-bottom:12px; padding:12px; box-shadow:var(--shadow);
      }
      .table-card tbody td{
        border:0; text-align:left; padding:6px 0; white-space:normal;
      }
      .table-card tbody td::before{
        content:attr(data-label);
        display:block; font-weight:700; color:var(--muted); margin-bottom:2px;
      }
    }
  </style>
</head>
<body>
  <div class="wrap">

    <!-- Hero -->
    <div class="hero">
      <div class="icon"><i class="bi bi-clipboard-data"></i></div>
      <div class="flex-grow-1">
        <div class="title">Historial de Movimientos</div>
        <div class="sub">Entradas y salidas del inventario en orden reciente</div>
      </div>
      <div class="d-none d-sm-flex gap-2">
        <a href="dashboard_admin.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i>&nbsp;Volver</a>
      </div>
    </div>

    <!-- Tabla -->
    <section class="block">
      <div class="block-header">
        <span><i class="bi bi-box-seam me-1"></i> Movimientos de Inventario</span>
        <div class="search">
          <input id="filtro" class="form-control ps-5" placeholder="Buscar producto…">
          <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
        </div>
      </div>

      <div class="block-body">
        <div class="table-responsive table-card">
          <table class="table table-hover align-middle text-center m-0" id="tabla">
            <thead class="sticky">
              <tr>
                <th><i class="bi bi-calendar-event"></i> Fecha</th>
                <th><i class="bi bi-box"></i> Producto</th>
                <th><i class="bi bi-arrow-left-right"></i> Tipo</th>
                <th><i class="bi bi-hash"></i> Cantidad</th>
                <th><i class="bi bi-chat-left-text"></i> Observaciones</th>
              </tr>
            </thead>
            <tbody>
              <?php if ($movimientos && $movimientos->num_rows > 0): ?>
                <?php while ($mov = $movimientos->fetch_assoc()):
                  $isEntrada = strtolower($mov['tipo_movimiento']) === 'entrada';
                ?>
                  <tr>
                    <td class="muted" data-label="Fecha">
                      <?= htmlspecialchars(date("d/m/Y H:i", strtotime($mov['fecha_movimiento']))); ?>
                    </td>
                    <td class="text-start" data-label="Producto"><?= htmlspecialchars($mov['nombre_producto']); ?></td>
                    <td data-label="Tipo">
                      <?php if ($isEntrada): ?>
                        <span class="pill pill-in"><i class="bi bi-arrow-down-circle"></i> Entrada</span>
                      <?php else: ?>
                        <span class="pill pill-out"><i class="bi bi-arrow-up-circle"></i> Salida</span>
                      <?php endif; ?>
                    </td>
                    <td data-label="Cantidad"><?= (int)$mov['cantidad']; ?></td>
                    <td class="text-start" data-label="Observaciones"><?= htmlspecialchars($mov['observaciones']); ?></td>
                  </tr>
                <?php endwhile; ?>
              <?php else: ?>
                <tr>
                  <td colspan="5" class="text-center muted py-4">🔍 No hay movimientos registrados.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>

        <div class="d-flex d-sm-none justify-content-center mt-3">
          <a href="dashboard_admin.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i>&nbsp;Volver</a>
        </div>
      </div>
    </section>
  </div>

  <script>
    function filtrar(){
      const q = (document.getElementById('filtro').value || '').toLowerCase();
      const rows = document.querySelectorAll('#tabla tbody tr');
      rows.forEach(r=>{
        const prod = (r.cells[1]?.innerText || '').toLowerCase();
        r.style.display = prod.includes(q) ? '' : 'none';
      });
    }
    document.getElementById('filtro').addEventListener('input', filtrar);
  </script>
</body>
</html>
