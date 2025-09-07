<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'admin') {
  header("Location: login.php");
  exit();
}

include("conexion.php");

$buscar  = isset($_GET['buscar']) ? trim($_GET['buscar']) : '';
$ordenar = isset($_GET['ordenar']) ? $_GET['ordenar'] : '';

/* ========= Telas ========= */
$sql = "
SELECT DISTINCT nombre_tela FROM (
  SELECT nombre_tela FROM telas
  UNION
  SELECT tela AS nombre_tela FROM produccion
) AS todas_telas
";
$params = [];
$types  = '';

if ($buscar !== '') {
  $sql .= " WHERE nombre_tela LIKE ?";
  $params[] = "%$buscar%";
  $types   .= "s";
}
$sql .= " ORDER BY nombre_tela";

$st = $conexion->prepare($sql);
if ($types) { $st->bind_param($types, ...$params); }
$st->execute();
$telas = $st->get_result();

/* ========= Acumuladores ========= */
$total_comprados = 0;
$total_usados    = 0;
$total_stock     = 0;
$total_ganancia  = 0;
$total_productos = 0;

/* ========= Construir filas ========= */
$data_filas = [];
while ($fila = $telas->fetch_assoc()) {
  $nombre = $fila['nombre_tela'];

  $c = $conexion->query("SELECT SUM(metros_comprados) AS metros, SUM(precio_total) AS total FROM compras_telas WHERE nombre_tela = '".$conexion->real_escape_string($nombre)."'")->fetch_assoc() ?? [];
  $metros_comprados = (float)($c['metros'] ?? 0);
  $precio_total     = (float)($c['total'] ?? 0);
  $costo_promedio   = $metros_comprados > 0 ? ($precio_total / $metros_comprados) : 0;

  $u = $conexion->query("SELECT SUM(metros_usados) AS usados, SUM(cantidad) AS productos, SUM(ganancia) AS ganancia FROM produccion WHERE tela = '".$conexion->real_escape_string($nombre)."'")->fetch_assoc() ?? [];
  $metros_usados = (float)($u['usados'] ?? 0);
  $productos     = (int)($u['productos'] ?? 0);
  $ganancia      = (float)($u['ganancia'] ?? 0);

  $s = $conexion->query("SELECT metros_disponibles FROM telas WHERE nombre_tela = '".$conexion->real_escape_string($nombre)."'")->fetch_assoc() ?? [];
  $stock_actual = (float)($s['metros_disponibles'] ?? 0);

  $porcentaje_usado = $metros_comprados > 0 ? ($metros_usados / $metros_comprados) * 100 : 0;

  $data_filas[] = [
    'nombre'     => $nombre,
    'comprados'  => $metros_comprados,
    'usados'     => $metros_usados,
    'stock'      => $stock_actual,
    'costo'      => $costo_promedio,
    'productos'  => $productos,
    'ganancia'   => $ganancia,
    'porcentaje' => $porcentaje_usado
  ];

  $total_comprados += $metros_comprados;
  $total_usados    += $metros_usados;
  $total_stock     += $stock_actual;
  $total_ganancia  += $ganancia;
  $total_productos += $productos;
}
$st->close();

/* ========= Ordenamiento ========= */
$validSort = ['comprados','usados','ganancia'];
if (in_array($ordenar, $validSort, true)) {
  usort($data_filas, fn($a,$b) => $b[$ordenar] <=> $a[$ordenar]);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Ficha de Telas - Luvadak</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  <style>
    :root{
      --bg:#f8fafc; --panel:#ffffff; --text:#0f172a; --muted:#667085; --border:#e6e9f2;
      --brand:#7c3aed; --brand2:#00d4ff; --ring:rgba(124,58,237,.22);
      --radius:12px; --radius-lg:16px; --shadow:0 2px 12px rgba(16,24,40,.08);
      --success:#16a34a;
    }
    body{
      background: radial-gradient(900px 520px at 110% -10%, rgba(124,58,237,.06), transparent 45%), var(--bg);
      color:var(--text);
      font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, sans-serif;
    }
    .page{ max-width:1250px; margin:34px auto 40px; padding:0 16px }
    .hero{
      display:flex; align-items:center; gap:12px; flex-wrap:wrap;
      background:#fff; border:1px solid var(--border); border-radius:var(--radius-lg);
      padding:14px 16px; box-shadow:var(--shadow); margin-bottom:16px;
    }
    .hero .icon{ width:38px;height:38px;border-radius:12px;display:grid;place-items:center;color:#fff;
      background:linear-gradient(135deg,var(--brand),var(--brand2)); }
    .hero h3{ margin:0; font-weight:800; font-size:1.1rem }
    .hero .sub{ color:var(--muted) }

    .block{ border:1px solid var(--border); background:var(--panel); border-radius:var(--radius-lg); box-shadow:var(--shadow); margin-bottom:18px }
    .block-header{ padding:14px 16px; border-bottom:1px solid var(--border); font-weight:700 }
    .block-body{ padding:16px }
    .block-footer{ padding:12px 16px; border-top:1px solid var(--border); background:#fafbff }

    .table-modern{ border:1px solid var(--border); border-radius:12px; overflow:hidden }
    .table-modern thead{ background:#f6f7fb }
    .table-modern th{ border:0; font-weight:700; white-space:nowrap }
    .table-modern td{ border-color:#eef1f6; white-space:nowrap }
    .badge-soft{ background:#f4f2ff; color:#3f2ab5; border-radius:999px; padding:.25rem .55rem; font-weight:700; font-size:.78rem }
    .text-money{ color:var(--success); font-weight:700 }
    .low-stock{ background:rgba(239,68,68,.06) }

    /* ----------- Responsive tabla stackable ----------- */
    @media (max-width: 575.98px){
      .table-modern thead, .table-modern tfoot{ display:none }
      .table-modern tbody tr{
        display:block; background:#fff;
        border:1px solid var(--border); border-radius:12px;
        padding:10px; margin-bottom:10px;
      }
      .table-modern tbody td{
        display:flex; justify-content:space-between; align-items:center;
        border:0; padding:6px 4px; white-space:normal; word-break:break-word;
        font-size:.92rem;
      }
      .table-modern tbody td::before{
        content:attr(data-label);
        font-weight:700; color:var(--muted);
      }
    }
  </style>
</head>
<body>
  <div class="page">
    <div class="hero">
      <div class="icon"><i class="bi bi-file-earmark-text-fill"></i></div>
      <div class="flex-grow-1">
        <h3>Ficha Resumen de Telas</h3>
        <div class="sub">Métricas por tela, costos y rentabilidad</div>
      </div>
      <a href="dashboard_admin.php" class="btn btn-secondary ms-auto">
        <i class="bi bi-arrow-left-circle me-1"></i>Volver
      </a>
    </div>

    <section class="block">
      <div class="block-header">Detalle por tela</div>
      <div class="block-body">
        <div class="table-modern">
          <table class="table align-middle text-center">
            <thead>
              <tr>
                <th>🧵 Tela</th>
                <th>📥 Comprados</th>
                <th>✂️ Usados</th>
                <th>📦 Stock</th>
                <th>💰 Costo Prom.</th>
                <th>👕 Productos</th>
                <th>📈 Ganancia</th>
                <th>% Usado</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($data_filas)): ?>
                <tr><td colspan="8" class="text-muted py-4">No se encontraron telas.</td></tr>
              <?php else: ?>
                <?php foreach ($data_filas as $fila): ?>
                  <?php $low = ($fila['stock'] < 5) ? 'low-stock' : ''; ?>
                  <tr class="<?= $low ?>">
                    <td data-label="🧵 Tela"><?= htmlspecialchars($fila['nombre']) ?></td>
                    <td data-label="📥 Comprados"><?= number_format($fila['comprados'],2) ?> m</td>
                    <td data-label="✂️ Usados"><?= number_format($fila['usados'],2) ?> m</td>
                    <td data-label="📦 Stock"><?= number_format($fila['stock'],2) ?> m</td>
                    <td data-label="💰 Costo Prom.">S/ <?= number_format($fila['costo'],2) ?></td>
                    <td data-label="👕 Productos"><?= (int)$fila['productos'] ?></td>
                    <td data-label="📈 Ganancia" class="text-money">S/ <?= number_format($fila['ganancia'],2) ?></td>
                    <td data-label="% Usado"><span class="badge-soft"><?= number_format($fila['porcentaje'],1) ?>%</span></td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
            <tfoot class="table-secondary fw-bold">
              <tr>
                <td>Total</td>
                <td><?= number_format($total_comprados, 2); ?> m</td>
                <td><?= number_format($total_usados, 2); ?> m</td>
                <td><?= number_format($total_stock, 2); ?> m</td>
                <td>—</td>
                <td><?= (int)$total_productos; ?></td>
                <td class="text-money">S/ <?= number_format($total_ganancia, 2); ?></td>
                <td>—</td>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>
    </section>
  </div>
</body>
</html>
