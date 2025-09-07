<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'empleado') {
    header("Location: login.php");
    exit();
}

include("conexion.php");

$id_usuario = intval($_SESSION['id_usuario'] ?? 0);

$query = "
  SELECT v.id_venta, v.total, v.fecha
  FROM ventas v
  WHERE v.id_usuario = $id_usuario
  ORDER BY v.fecha DESC
";
$resultado = $conexion->query($query);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Mis Boletas - Luvadak</title>
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
  <!-- Bootstrap + Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

  <style>
    :root{
      --bg:#f8fafc; --panel:#ffffff; --text:#0f172a; --muted:#667085; --border:#e6e9f2;
      --brand:#7c3aed; --brand2:#00d4ff; --ring:rgba(124,58,237,.22);
      --radius:14px; --radius-lg:18px; --shadow:0 10px 26px rgba(16,24,40,.08);
      --safe-top:env(safe-area-inset-top,0px); --safe-bottom:env(safe-area-inset-bottom,0px);
    }

    /* Tipografía fluida para mejor lectura en móvil */
    .fs-fluid-sm{ font-size:clamp(.95rem,.9rem + .3vw,1.05rem) }
    .fs-fluid-md{ font-size:clamp(1.05rem,1rem + .6vw,1.25rem) }

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

    .wrap{ max-width:1100px; margin:calc(18px + var(--safe-top)) auto 28px; padding:0 16px }

    /* Hero / encabezado (ligeramente sticky en móvil) */
    .hero{
      position:sticky; top:0; z-index:5;
      display:flex; align-items:center; gap:12px; flex-wrap:wrap;
      background:linear-gradient(180deg, rgba(255,255,255,.88), rgba(255,255,255,.96));
      border:1px solid var(--border); border-radius:var(--radius-lg);
      padding:12px 14px; box-shadow:var(--shadow); margin-bottom:18px;
      backdrop-filter:saturate(120%) blur(6px);
    }
    .hero .icon{
      width:42px;height:42px;border-radius:12px;display:grid;place-items:center;color:#fff;
      background:linear-gradient(135deg, var(--brand), var(--brand2));
      box-shadow:0 12px 24px rgba(124,58,237,.25);
      font-size:1.2rem;
    }
    .hero .title{ font-weight:800; font-size:1.15rem }
    .hero .sub{ color:var(--muted) }

    /* Bloque principal */
    .block{
      border:1px solid var(--border); border-radius:var(--radius);
      background:var(--panel); box-shadow:var(--shadow); overflow:hidden;
    }
    .block-header{ padding:14px 18px; border-bottom:1px solid var(--border); font-weight:800 }
    .block-body{ padding:16px 18px }

    .empty{
      border:1px dashed #dbe0ef; border-radius:14px; padding:18px; text-align:center;
      color:#6b7280; background:#f9fbff;
    }

    /* Tabla + tarjeta móvil */
    .table-card{ border:1px solid var(--border); border-radius:12px; overflow:hidden; background:#fff }
    .table thead{ background:#f6f7fb }
    .table thead th{ border:0; font-weight:800; color:#111827 }
    .table tbody td{ border-color:#eef1f6 }

    /* Badges */
    .badge-soft{
      display:inline-flex; align-items:center; gap:6px; padding:6px 10px; border-radius:999px;
      background:#f3f4ff; color:#3730a3; border:1px solid #e7e7fe; font-weight:700; font-size:.8rem;
    }

    /* Botones más táctiles */
    .btn{
      border-radius:999px; font-weight:800; border:1px solid var(--border);
      min-height:44px; padding:.65rem 1rem; letter-spacing:.2px;
      box-shadow:0 6px 16px rgba(17,24,39,.06);
      transition:transform .12s ease, filter .12s ease, box-shadow .2s, background .2s, border-color .2s;
    }
    .btn:focus-visible{ outline:3px solid var(--ring); outline-offset:2px }
    .btn-secondary{ background:#fff; color:#0f172a; border-color:var(--border) }
    .btn-secondary:hover{ background:#f6f7fb; transform:translateY(-2px) }
    .btn-danger{
      background:linear-gradient(135deg, #ef4444, #f97316);
      border-color:transparent; color:#fff; box-shadow:0 8px 18px rgba(239,68,68,.25);
    }
    .btn-danger:hover{ filter:brightness(1.05); transform:translateY(-2px) }
    .btn-sm{ min-height:40px; padding:.5rem .85rem; font-weight:800; border-radius:999px }

    /* ===== Responsive: convertir tabla a tarjetas (≤768px) ===== */
    @media (max-width: 768px){
      .table-card table, .table-card thead, .table-card tbody, .table-card th, .table-card td, .table-card tr{ display:block; width:100% }
      .table-card thead{ display:none !important }
      .table-card tr{
        margin-bottom:12px; border:1px solid var(--border); border-radius:12px;
        padding:12px; box-shadow:var(--shadow); background:#fff;
      }
      .table-card td{ border:none; padding:6px 0; text-align:left; white-space:normal }
      .table-card td::before{ content:attr(data-label); display:block; font-weight:700; color:var(--muted); margin-bottom:2px }
      .hero .title{ font-size:1.2rem }
    }

    /* Movimiento reducido */
    @media (prefers-reduced-motion: reduce){
      *{ transition:none!important; animation:none!important }
    }
  </style>
</head>
<body>
  <div class="wrap">
    <!-- Hero -->
    <div class="hero">
      <div class="icon"><i class="bi bi-receipt-cutoff"></i></div>
      <div class="flex-grow-1">
        <div class="title fs-fluid-md">Historial de Boletas Emitidas</div>
        <div class="sub fs-fluid-sm">Consulta y descarga tus boletas en PDF</div>
      </div>
      <div class="d-none d-sm-block">
        <a href="dashboard_empleado.php" class="btn btn-secondary">
          <i class="bi bi-arrow-left-circle me-1"></i> Volver al Panel
        </a>
      </div>
    </div>

    <!-- Bloque listado -->
    <section class="block">
      <div class="block-header">Mis boletas</div>
      <div class="block-body">
        <?php if ($resultado && $resultado->num_rows > 0): ?>
          <div class="table-responsive table-card">
            <table class="table table-hover align-middle text-center m-0">
              <thead>
                <tr>
                  <th>ID Boleta</th>
                  <th>Total</th>
                  <th>Fecha</th>
                  <th>Acción</th>
                </tr>
              </thead>
              <tbody>
                <?php while ($boleta = $resultado->fetch_assoc()): ?>
                  <tr>
                    <td data-label="ID Boleta">
                      <span class="badge-soft">#<?= (int)$boleta['id_venta'] ?></span>
                    </td>
                    <td class="fw-bold" data-label="Total">S/ <?= number_format($boleta['total'], 2) ?></td>
                    <td data-label="Fecha"><?= htmlspecialchars(date("d/m/Y H:i", strtotime($boleta['fecha']))) ?></td>
                    <td data-label="Acción">
                      <a href="generar_boleta.php?id=<?= (int)$boleta['id_venta'] ?>" target="_blank" class="btn btn-danger btn-sm">
                        <i class="bi bi-file-earmark-pdf"></i> Ver PDF
                      </a>
                    </td>
                  </tr>
                <?php endwhile; ?>
              </tbody>
            </table>
          </div>

          <!-- Botón volver visible bajo la tabla solo en móvil -->
          <div class="d-grid d-sm-none mt-3">
            <a href="dashboard_empleado.php" class="btn btn-secondary">
              <i class="bi bi-arrow-left-circle me-1"></i> Volver al Panel
            </a>
          </div>
        <?php else: ?>
          <div class="empty">
            <div class="mb-1" style="font-size:1.05rem">🔕 No has emitido ninguna boleta aún.</div>
            <div class="text-muted">Cuando registres ventas, aparecerán aquí.</div>
          </div>
          <div class="d-grid d-sm-none mt-3">
            <a href="dashboard_empleado.php" class="btn btn-secondary">
              <i class="bi bi-arrow-left-circle me-1"></i> Volver al Panel
            </a>
          </div>
        <?php endif; ?>
      </div>
    </section>
  </div>
</body>
</html>
