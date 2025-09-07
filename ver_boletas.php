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
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <!-- Bootstrap + Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

  <style>
    :root{
      --bg:#f8fafc;
      --panel:#ffffff;
      --text:#0f172a;
      --muted:#667085;
      --border:#e6e9f2;

      --brand:#7c3aed;     /* lila */
      --brand2:#00d4ff;    /* celeste */
      --ring:rgba(124,58,237,.22);

      --radius:14px;
      --radius-lg:18px;
      --shadow:0 10px 26px rgba(16,24,40,.08);
    }

    body{
      background:
        radial-gradient(900px 520px at -10% -10%, rgba(124,58,237,.10), transparent 45%),
        radial-gradient(900px 520px at 110% 0%, rgba(0,212,255,.10), transparent 45%),
        var(--bg);
      color:var(--text);
      font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, sans-serif;
    }

    .wrap{ max-width:1100px; margin:30px auto; padding:0 16px }

    /* Hero / encabezado */
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

    /* Bloque principal */
    .block{
      border:1px solid var(--border); border-radius:var(--radius);
      background:var(--panel); box-shadow:var(--shadow);
      overflow:hidden;
    }
    .block-header{
      padding:14px 18px; border-bottom:1px solid var(--border); font-weight:800
    }
    .block-body{ padding:16px 18px }
    .empty{
      border:1px dashed #dbe0ef; border-radius:14px; padding:18px; text-align:center;
      color:#6b7280; background:#f9fbff;
    }

    /* Tabla */
    .table{
      border:1px solid var(--border);
      border-radius:12px;
      overflow:hidden;
      background:#fff;
      margin-bottom:0;
    }
    .table thead{ background:#f6f7fb }
    .table thead th{ border:0; font-weight:800; color:#111827 }
    .table tbody td{ border-color:#eef1f6 }
    .badge-soft{
      display:inline-flex; align-items:center; gap:6px; padding:6px 10px; border-radius:999px;
      background:#f3f4ff; color:#3730a3; border:1px solid #e7e7fe; font-weight:700; font-size:.8rem;
    }

    /* Botones */
    .btn{ border-radius:999px; font-weight:700; border:1px solid var(--border) }
    .btn-secondary{
      background:#fff; color:#0f172a; border-color:var(--border)
    }
    .btn-secondary:hover{ background:#f6f7fb }
    .btn-danger{
      background:linear-gradient(135deg, #ef4444, #f97316);
      border-color:transparent; color:#fff; box-shadow:0 8px 18px rgba(239,68,68,.25);
    }
    .btn-danger:hover{ filter:brightness(1.05) }
  </style>
</head>
<body>
  <div class="wrap">
    <!-- Hero -->
    <div class="hero">
      <div class="icon"><i class="bi bi-receipt-cutoff"></i></div>
      <div class="flex-grow-1">
        <div class="title">Historial de Boletas Emitidas</div>
        <div class="sub">Consulta y descarga tus boletas en PDF</div>
      </div>
      <div>
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
          <div class="table-responsive">
            <table class="table table-hover align-middle text-center">
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
                    <td><span class="badge-soft">#<?= (int)$boleta['id_venta'] ?></span></td>
                    <td class="fw-bold">S/ <?= number_format($boleta['total'], 2) ?></td>
                    <td><?= htmlspecialchars(date("d/m/Y H:i", strtotime($boleta['fecha']))) ?></td>
                    <td>
                      <a href="generar_boleta.php?id=<?= (int)$boleta['id_venta'] ?>" target="_blank" class="btn btn-danger btn-sm">
                        <i class="bi bi-file-earmark-pdf"></i> Ver PDF
                      </a>
                    </td>
                  </tr>
                <?php endwhile; ?>
              </tbody>
            </table>
          </div>
        <?php else: ?>
          <div class="empty">
            <div class="mb-1" style="font-size:1.05rem">🔕 No has emitido ninguna boleta aún.</div>
            <div class="text-muted">Cuando registres ventas, aparecerán aquí.</div>
          </div>
        <?php endif; ?>
      </div>
    </section>
  </div>
</body>
</html>
