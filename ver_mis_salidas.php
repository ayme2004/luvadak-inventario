<?php
session_start();
if (!isset($_SESSION['usuario']) || ($_SESSION['rol'] !== 'empleado' && $_SESSION['rol'] !== 'admin')) {
  header("Location: login.php");
  exit();
}

include("conexion.php");

$rol = $_SESSION['rol'];
$id_usuario = $_SESSION['id_usuario'];

if ($rol === 'admin') {
  $sql = "SELECT m.fecha_movimiento, p.nombre_producto, m.cantidad, m.observaciones, u.nombre_completo AS usuario
          FROM movimientosinventario m
          INNER JOIN productos p ON m.id_producto = p.id_producto
          LEFT JOIN usuarios u ON m.id_usuario = u.id_usuario
          WHERE m.tipo_movimiento = 'salida'
          ORDER BY m.fecha_movimiento DESC";
  $resultado = $conexion->query($sql);
} else {
  $sql = "SELECT m.fecha_movimiento, p.nombre_producto, m.cantidad, m.observaciones
          FROM movimientosinventario m
          INNER JOIN productos p ON m.id_producto = p.id_producto
          WHERE m.tipo_movimiento = 'salida' AND m.id_usuario = ?
          ORDER BY m.fecha_movimiento DESC";
  $stmt = $conexion->prepare($sql);
  $stmt->bind_param("i", $id_usuario);
  $stmt->execute();
  $resultado = $stmt->get_result();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title><?php echo ($rol === 'admin') ? 'Todas las Salidas' : 'Mis Salidas'; ?> · Inventario</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <!-- Bootstrap + Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  <style>
    :root{
      --bg:#f8fafc; --panel:#ffffff; --text:#0f172a; --muted:#667085; --border:#e6e9f2;
      --brand:#7c3aed; --brand2:#00d4ff; --ring:rgba(124,58,237,.22);
      --radius:14px; --radius-lg:18px; --shadow:0 10px 26px rgba(16,24,40,.08);
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

    /* Block */
    .block{
      border:1px solid var(--border);
      border-radius:var(--radius);
      background:var(--panel);
      box-shadow:var(--shadow);
      overflow:hidden;
      margin-bottom:16px;
    }
    .block-header{
      background:#fff; border-bottom:1px solid var(--border);
      padding:14px 18px; font-weight:800; display:flex; align-items:center; gap:10px;
    }
    .block-body{ padding:16px 18px }

    /* Table card */
    .table-card{ border:1px solid var(--border); border-radius:12px; overflow:hidden; background:#fff }
    thead.sticky th{
      position:sticky; top:0; z-index:1; background:#f6f7fb; border-bottom:1px solid var(--border);
    }
    .table-hover tbody tr:hover{ background:#fafbff }
    .pill{
      display:inline-block; padding:.25rem .6rem; border-radius:999px; font-weight:700;
      background:#eef2ff; color:#3730a3; border:1px solid #e0e7ff; font-size:.8rem;
    }

    /* Buttons */
    .btn{ border-radius:999px; font-weight:700; border:1px solid var(--border) }
    .btn-primary{
      background:linear-gradient(135deg, var(--brand), var(--brand2));
      border-color:transparent; color:#fff;
      box-shadow:0 10px 22px rgba(124,58,237,.28);
    }
    .btn-secondary{ background:#fff; color:#0f172a; border-color:var(--border) }
    .btn-secondary:hover{ background:#f6f7fb }
  </style>
</head>
<body>
  <div class="wrap">

    <!-- Hero -->
    <div class="hero">
      <div class="icon"><i class="bi bi-box-arrow-up"></i></div>
      <div class="flex-grow-1">
        <div class="title">Salidas de Inventario</div>
        <div class="sub">
          <?php echo ($rol === 'admin')
            ? 'Vista general de todas las salidas registradas'
            : 'Listado de salidas registradas por ti'; ?>
        </div>
      </div>
      <div class="d-none d-sm-flex">
        <a href="<?php echo ($rol === 'admin') ? 'dashboard_admin.php' : 'dashboard_empleado.php'; ?>" class="btn btn-secondary">
          <i class="bi bi-arrow-left"></i>&nbsp;Volver al Panel
        </a>
      </div>
    </div>

    <!-- Tabla -->
    <section class="block">
      <div class="block-header"><i class="bi bi-list-ul"></i> Resultados</div>
      <div class="block-body">
        <div class="table-responsive table-card">
          <table class="table table-hover align-middle text-center m-0">
            <thead class="sticky">
              <tr>
                <th>📅 Fecha</th>
                <th>🛍️ Producto</th>
                <th>🔢 Cantidad</th>
                <?php if ($rol === 'admin'): ?><th>👤 Usuario</th><?php endif; ?>
                <th>📝 Observaciones</th>
              </tr>
            </thead>
            <tbody>
            <?php if ($resultado && $resultado->num_rows > 0): ?>
              <?php while ($fila = $resultado->fetch_assoc()): ?>
                <tr>
                  <td><?= htmlspecialchars(date('d/m/Y H:i', strtotime($fila['fecha_movimiento']))) ?></td>
                  <td><?= htmlspecialchars($fila['nombre_producto']) ?></td>
                  <td><span class="pill"><?= (int)$fila['cantidad'] ?></span></td>
                  <?php if ($rol === 'admin'): ?>
                    <td><?= htmlspecialchars($fila['usuario'] ?? '—') ?></td>
                  <?php endif; ?>
                  <td><?= htmlspecialchars($fila['observaciones'] ?? '—') ?></td>
                </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr>
                <td colspan="<?= ($rol === 'admin') ? 5 : 4; ?>" class="text-muted py-4">
                  <i class="bi bi-inbox me-1"></i> No se encontraron salidas registradas.
                </td>
              </tr>
            <?php endif; ?>
            </tbody>
          </table>
        </div>

        <div class="d-flex d-sm-none justify-content-center mt-3">
          <a href="<?php echo ($rol === 'admin') ? 'dashboard_admin.php' : 'dashboard_empleado.php'; ?>" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i>&nbsp;Volver al Panel
          </a>
        </div>
      </div>
    </section>
  </div>
</body>
</html>
