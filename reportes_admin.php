<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'admin') {
    header("Location: login.php");
    exit();
}
include("conexion.php");

/* KPIs */
$ventas_dia   = $conexion->query("SELECT SUM(dv.cantidad) AS total FROM detalle_venta dv JOIN ventas v ON dv.id_venta = v.id_venta WHERE DATE(v.fecha)=CURDATE()")->fetch_assoc()['total'] ?? 0;
$ventas_mes   = $conexion->query("SELECT SUM(dv.cantidad) AS total FROM detalle_venta dv JOIN ventas v ON dv.id_venta = v.id_venta WHERE MONTH(v.fecha)=MONTH(CURDATE()) AND YEAR(v.fecha)=YEAR(CURDATE())")->fetch_assoc()['total'] ?? 0;
$ganancias    = $conexion->query("SELECT SUM(dv.precio_unitario*dv.cantidad) AS total FROM detalle_venta dv JOIN ventas v ON dv.id_venta = v.id_venta WHERE MONTH(v.fecha)=MONTH(CURDATE()) AND YEAR(v.fecha)=YEAR(CURDATE())")->fetch_assoc()['total'] ?? 0;
$gan_dia      = $conexion->query("SELECT SUM(dv.precio_unitario*dv.cantidad) AS total FROM detalle_venta dv JOIN ventas v ON dv.id_venta = v.id_venta WHERE DATE(v.fecha)=CURDATE()")->fetch_assoc()['total'] ?? 0;
$compras_mes  = $conexion->query("SELECT SUM(metros_comprados) AS total FROM compras_telas WHERE MONTH(fecha_compra)=MONTH(CURDATE()) AND YEAR(fecha_compra)=YEAR(CURDATE())")->fetch_assoc()['total'] ?? 0;
$pagos        = $conexion->query("SELECT SUM(monto) AS total FROM pagos_empleados WHERE MONTH(fecha_pago)=MONTH(CURDATE()) AND YEAR(fecha_pago)=YEAR(CURDATE())")->fetch_assoc()['total'] ?? 0;

/* Top vendedores */
$vendedores = $conexion->query("
  SELECT u.nombre_completo, SUM(dv.cantidad) AS total_vendidos
  FROM ventas v
  JOIN usuarios u ON v.id_usuario = u.id_usuario
  JOIN detalle_venta dv ON v.id_venta = dv.id_venta
  WHERE MONTH(v.fecha)=MONTH(CURDATE()) AND YEAR(v.fecha)=YEAR(CURDATE())
  GROUP BY u.id_usuario
  ORDER BY total_vendidos DESC
  LIMIT 3
");
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Panel de Reportes - Luvadak</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  <style>
    :root{
      --bg:#f8fafc;
      --panel:#ffffff;
      --text:#0f172a;
      --muted:#667085;
      --border:#e6e9f2;

      --brand:#7c3aed;    /* lila */
      --brand2:#00d4ff;   /* celeste */
      --ring:rgba(124,58,237,.22);

      --radius:14px;
      --radius-lg:18px;
      --shadow:0 10px 26px rgba(16,24,40,.08);
    }

    body{
      background:
        radial-gradient(950px 520px at -10% -10%, rgba(124,58,237,.10), transparent 45%),
        radial-gradient(900px 520px at 110% 0%, rgba(0,212,255,.10), transparent 45%),
        var(--bg);
      color:var(--text);
      font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, sans-serif;
    }

    .wrap{ max-width:1200px; margin:28px auto; padding:0 16px }

    /* Hero glass */
    .hero{
      display:flex; align-items:center; gap:12px;
      background:linear-gradient(180deg, rgba(255,255,255,.88), rgba(255,255,255,.96));
      border:1px solid var(--border); border-radius:var(--radius-lg);
      padding:14px 16px; box-shadow:var(--shadow); margin-bottom:18px;
    }
    .hero .icon{
      width:40px;height:40px;border-radius:12px;display:grid;place-items:center;color:#fff;
      background:linear-gradient(135deg, var(--brand), var(--brand2));
      box-shadow:0 12px 24px rgba(124,58,237,.25);
      font-size:1.2rem;
    }
    .hero .title{ font-weight:800; font-size:1.25rem }
    .hero .sub{ color:var(--muted); font-size:.95rem }

    /* KPI cards */
    .kpi{
      border:1px solid var(--border); border-radius:16px; background:var(--panel);
      box-shadow:var(--shadow); padding:16px;
      transition:transform .18s ease, box-shadow .18s ease;
    }
    .kpi:hover{ transform:translateY(-3px); box-shadow:0 16px 40px rgba(16,24,40,.10) }
    .kpi .label{ color:#475569; font-weight:700 }
    .kpi .value{ font-size:1.6rem; font-weight:800; margin-top:4px }
    .kpi .tag{
      display:inline-flex; align-items:center; gap:6px; padding:4px 8px; border-radius:999px;
      background:#f3f4ff; color:#3730a3; font-weight:700; font-size:.78rem; border:1px solid #e7e7fe;
    }

    /* Panel list */
    .panel{
      border:1px solid var(--border); border-radius:16px; background:var(--panel);
      box-shadow:var(--shadow); padding:18px;
    }
    .panel h5{ font-weight:800; margin-bottom:10px }

    .rank li{
      display:flex; align-items:center; justify-content:space-between;
      padding:10px 12px; border:1px solid var(--border); border-radius:12px; background:#fff;
    }
    .rank li + li{ margin-top:8px }
    .rank .left{ display:flex; align-items:center; gap:10px; font-weight:700 }
    .medal{
      width:28px;height:28px;border-radius:999px;display:grid;place-items:center;color:#fff; font-size:.9rem;
      background:linear-gradient(135deg, var(--brand), var(--brand2));
      box-shadow:0 8px 18px rgba(124,58,237,.25);
    }

    /* Buttons */
    .btn{
      border-radius:999px; font-weight:700; border:1px solid var(--border);
    }
    .btn-secondary{ background:#fff; color:#0f172a }
    .btn-secondary:hover{ background:#f6f7fb }
  </style>
</head>
<body>
  <div class="wrap">
    <!-- HERO -->
    <div class="hero">
      <div class="icon"><i class="bi bi-graph-up-arrow"></i></div>
      <div class="flex-grow-1">
        <div class="title">Panel de Reportes Administrativos</div>
        <div class="sub">Indicadores clave del mes y ranking de vendedores</div>
      </div>
      <a href="dashboard_admin.php" class="btn btn-secondary">
        <i class="bi bi-arrow-left-circle me-1"></i> Volver
      </a>
    </div>

    <!-- KPIs -->
    <div class="row g-3">
      <div class="col-md-4">
        <div class="kpi">
          <div class="label"><span class="tag">📆 Hoy</span> Ventas del Día</div>
          <div class="value"><?= number_format($ventas_dia ?? 0) ?> <small class="text-muted">unid.</small></div>
        </div>
      </div>

      <div class="col-md-4">
        <div class="kpi">
          <div class="label"><span class="tag">📅 Mes</span> Ventas del Mes</div>
          <div class="value"><?= number_format($ventas_mes ?? 0) ?> <small class="text-muted">unid.</small></div>
        </div>
      </div>

      <div class="col-md-4">
        <div class="kpi">
          <div class="label"><span class="tag">📦 Telas</span> Compras del Mes</div>
          <div class="value"><?= number_format($compras_mes ?? 0, 2) ?> <small class="text-muted">m</small></div>
        </div>
      </div>

      <div class="col-md-4">
        <div class="kpi">
          <div class="label"><span class="tag">💰 Mes</span> Ganancias del Mes</div>
          <div class="value">S/ <?= number_format($ganancias ?? 0, 2) ?></div>
        </div>
      </div>

      <div class="col-md-4">
        <div class="kpi">
          <div class="label"><span class="tag">💵 Hoy</span> Ganancias del Día</div>
          <div class="value">S/ <?= number_format($gan_dia ?? 0, 2) ?></div>
        </div>
      </div>

      <div class="col-md-4">
        <div class="kpi">
          <div class="label"><span class="tag">👥 RRHH</span> Pagos a Empleados</div>
          <div class="value">S/ <?= number_format($pagos ?? 0, 2) ?></div>
        </div>
      </div>
    </div>

    <!-- TOP VENDEDORES -->
    <div class="panel mt-4">
      <h5 class="mb-2">🏆 Top 3 Vendedores del Mes</h5>
      <?php if ($vendedores->num_rows > 0): ?>
        <ol class="list-unstyled rank m-0">
          <?php 
          $i = 1;
          while ($v = $vendedores->fetch_assoc()): ?>
            <li>
              <div class="left">
                <span class="medal"><?= $i ?></span>
                <span><?= htmlspecialchars($v['nombre_completo']) ?></span>
              </div>
              <div class="fw-bold"><?= number_format($v['total_vendidos']) ?> <span class="text-muted">unid.</span></div>
            </li>
          <?php $i++; endwhile; ?>
        </ol>
      <?php else: ?>
        <div class="alert alert-warning mt-2 mb-0 rounded-3">
          <i class="bi bi-exclamation-triangle-fill me-1"></i> No se han registrado ventas este mes.
        </div>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>
