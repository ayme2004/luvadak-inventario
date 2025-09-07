<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'admin') {
  header("Location: login.php");
  exit();
}
include("conexion.php");

$busqueda = isset($_GET['buscar']) ? trim($_GET['buscar']) : "";
$pagos = [];
$total_pagado = 0;

if ($busqueda !== "") {
  $stmt = $conexion->prepare("
      SELECT p.fecha_pago, u.nombre_completo, p.monto
      FROM pagos_empleados p
      JOIN usuarios u ON p.id_usuario = u.id_usuario
      WHERE u.nombre_completo LIKE ?
      ORDER BY p.fecha_pago DESC
  ");
  $like = "%".$busqueda."%";
  $stmt->bind_param("s", $like);
  $stmt->execute();
  $resultado = $stmt->get_result();

  while ($fila = $resultado->fetch_assoc()) {
    $pagos[] = $fila;
    $total_pagado += (float)$fila['monto'];
  }
  $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Historial de Pagos por Empleado · Luvadak</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <!-- Bootstrap + Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    :root{
      --bg:#f8fafc; --panel:#ffffff; --text:#0f172a; --muted:#667085; --border:#e6e9f2;
      --brand:#7c3aed; --brand2:#00d4ff; --ring:rgba(124,58,237,.22);
      --radius:14px; --radius-lg:18px; --shadow:0 12px 28px rgba(16,24,40,.08);
      --success:#16a34a; --danger:#dc2626;
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
      display:flex; gap:12px; align-items:center;
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
    .btn-danger{
      background:linear-gradient(135deg, #ef4444, #f97316);
      border-color:transparent; color:#fff;
      box-shadow:0 10px 22px rgba(239,68,68,.25);
    }
    .btn-danger:disabled{ opacity:.6; box-shadow:none; cursor:not-allowed }

    /* Blocks */
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
    }
    .block-body{ padding:16px 18px }

    /* Search card (glass) */
    .search-card{
      background:linear-gradient(180deg, rgba(255,255,255,.86), rgba(255,255,255,.96));
      border:1px solid var(--border); border-radius:var(--radius);
      padding:14px; box-shadow:var(--shadow); margin-bottom:14px;
    }
    .search-wrap{ display:flex; gap:10px; align-items:center; }
    .search-wrap .form-control{
      border:1px solid var(--border); border-radius:999px; padding:.55rem 1rem;
    }
    .search-wrap .form-control:focus{ box-shadow:0 0 0 3px var(--ring); border-color:transparent }

    /* Table card */
    .table-card{ border:1px solid var(--border); border-radius:12px; overflow:hidden; background:#fff }
    thead.sticky th{
      position:sticky; top:0; z-index:1; background:#f6f7fb; border-bottom:1px solid var(--border);
    }
    .table-hover tbody tr:hover{ background:#fafbff }

    /* Totals bar */
    .totals{
      display:flex; justify-content:flex-end; gap:12px; flex-wrap:wrap; margin-top:12px;
    }
    .chip{
      display:inline-flex; align-items:center; gap:.5rem;
      padding:.45rem .8rem; border-radius:999px; background:#f1faf5; border:1px solid #cdebd6; color:#0a5c2b; font-weight:700;
    }
    .muted{ color:var(--muted) }
  </style>
</head>
<body>
  <div class="wrap">

    <!-- Hero -->
    <div class="hero">
      <div class="icon"><i class="bi bi-journal-text"></i></div>
      <div class="flex-grow-1">
        <div class="title">Historial de Pagos por Empleado</div>
        <div class="sub">Consulta pagos realizados y exporta un resumen a PDF</div>
      </div>
      <div class="d-none d-sm-flex gap-2">
        <a href="dashboard_admin.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i>&nbsp;Volver</a>
      </div>
    </div>

    <!-- Buscador -->
    <section class="search-card">
      <form method="GET" class="search-wrap" id="formBuscar">
        <div class="input-group">
          <span class="input-group-text bg-white border-0"><i class="bi bi-search"></i></span>
          <input type="text" name="buscar" id="buscar" value="<?= htmlspecialchars($busqueda) ?>"
                 class="form-control" placeholder="Buscar por nombre del empleado" required>
        </div>
        <button class="btn btn-primary" type="submit"><i class="bi bi-funnel"></i>&nbsp;Filtrar</button>
        <a href="?buscar=" class="btn btn-secondary"><i class="bi bi-arrow-clockwise"></i>&nbsp;Limpiar</a>
      </form>
    </section>

    <!-- Resultados -->
    <section class="block">
      <div class="block-header">
        <span><i class="bi bi-people-fill me-1"></i> Resultados</span>
        <?php if ($busqueda !== ""): ?>
          <span class="muted">Búsqueda: <strong><?= htmlspecialchars($busqueda) ?></strong></span>
        <?php endif; ?>
      </div>
      <div class="block-body">
        <?php if ($busqueda === ""): ?>
          <div class="text-center muted py-3">Ingresa un nombre para ver los pagos registrados.</div>
        <?php else: ?>
          <?php if (count($pagos) > 0): ?>
            <div class="table-responsive table-card">
              <table class="table table-hover align-middle text-center m-0">
                <thead class="sticky">
                  <tr>
                    <th><i class="bi bi-calendar-event"></i> Fecha de Pago</th>
                    <th><i class="bi bi-person-circle"></i> Empleado</th>
                    <th><i class="bi bi-cash-coin"></i> Monto</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($pagos as $p): ?>
                    <tr>
                      <td class="muted"><?= htmlspecialchars(date("d/m/Y", strtotime($p['fecha_pago']))) ?></td>
                      <td class="text-start"><?= htmlspecialchars($p['nombre_completo']) ?></td>
                      <td><strong>S/ <?= number_format((float)$p['monto'], 2) ?></strong></td>
                    </tr>
                  <?php endforeach; ?>
                  <tr class="table-light">
                    <td colspan="2" class="text-end fw-bold">Total Pagado</td>
                    <td class="fw-bold text-success">S/ <?= number_format($total_pagado, 2) ?></td>
                  </tr>
                </tbody>
              </table>
            </div>

            <div class="totals">
              <span class="chip"><i class="bi bi-cash-stack"></i> Total: S/ <?= number_format($total_pagado, 2) ?></span>
              <a href="exportar_pagos_empleado_pdf.php?nombre=<?= urlencode($busqueda) ?>"
                 class="btn btn-danger">
                <i class="bi bi-file-earmark-pdf"></i>&nbsp;Exportar PDF
              </a>
            </div>
          <?php else: ?>
            <div class="alert alert-warning rounded-3" role="alert">
              <i class="bi bi-exclamation-triangle-fill me-2"></i>
              No se encontraron pagos registrados para <strong><?= htmlspecialchars($busqueda) ?></strong>.
            </div>
            <a class="btn btn-danger disabled" aria-disabled="true">
              <i class="bi bi-file-earmark-pdf"></i>&nbsp;Exportar PDF
            </a>
          <?php endif; ?>
        <?php endif; ?>

        <div class="d-flex d-sm-none justify-content-center mt-3">
          <a href="dashboard_admin.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i>&nbsp;Volver</a>
        </div>
      </div>
    </section>
  </div>

  <script>
    // Pequeño UX: enviar con Enter desde el input si hay valor
    document.getElementById('buscar').addEventListener('keydown', (e)=>{
      if(e.key === 'Enter' && !e.shiftKey && e.target.value.trim() === '') {
        e.preventDefault();
      }
    });
  </script>
</body>
</html>
