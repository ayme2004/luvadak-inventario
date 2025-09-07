<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'admin') {
    header("Location: login.php");
    exit();
}

require_once "conexion.php";

/* ================== KPIs + chart ================== */
$comentarios_no_leidos = $conexion->query("SELECT COUNT(*) FROM comentarios WHERE visto = 0")->fetch_row()[0];
$ventas_hoy           = $conexion->query("SELECT COUNT(*) FROM ventas WHERE DATE(fecha) = CURDATE()")->fetch_row()[0];
$total_empleados      = $conexion->query("SELECT COUNT(*) FROM usuarios WHERE rol='empleado'")->fetch_row()[0];
$total_clientes       = $conexion->query("SELECT COUNT(*) FROM clientes")->fetch_row()[0];

$ventasPorDia = [];
$labels = [];
for ($i = 6; $i >= 0; $i--) {
    $fecha = date('Y-m-d', strtotime("-$i days"));
    $dia = date('D', strtotime("-$i days"));
    $labels[] = ['Mon'=>'Lun','Tue'=>'Mar','Wed'=>'Mié','Thu'=>'Jue','Fri'=>'Vie','Sat'=>'Sáb','Sun'=>'Dom'][$dia] ?? $dia;
    $ventasPorDia[] = (float)$conexion->query("SELECT IFNULL(SUM(total),0) FROM ventas WHERE DATE(fecha)='$fecha'")->fetch_row()[0];
}

/* ================== Usuario (nombre + foto) ================== */
$usuarioLogin = $_SESSION['usuario'];
$stmt = $conexion->prepare("SELECT nombre_completo, rol, foto_url FROM usuarios WHERE usuario=? LIMIT 1");
$stmt->bind_param("s", $usuarioLogin);
$stmt->execute();
$u = $stmt->get_result()->fetch_assoc();
$stmt->close();

$nombreCompleto = $u['nombre_completo'] ?? $usuarioLogin;
$primerNombre   = trim($nombreCompleto);
if ($primerNombre !== '') {
  $partes = preg_split('/\s+/', $primerNombre);
  $primerNombre = $partes[0];
}
$rolActual = $u['rol'] ?? 'admin';

$fotoSrc = $u['foto_url'] ?? '';
if ($fotoSrc && str_starts_with($fotoSrc, 'uploads/')) {
  $rutaFisica = __DIR__ . '/' . $fotoSrc;
  if (!file_exists($rutaFisica)) $fotoSrc = '';
}
if (!$fotoSrc) {
  $fotoSrc = 'https://api.dicebear.com/7.x/initials/svg?seed=' . urlencode($nombreCompleto ?: $usuarioLogin);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Panel de Administrador - Luvadak</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1"></script>

<style>
:root{
  --bg:#fafbff; --panel:#ffffff; --card:#ffffff; --border:#edf0f7;
  --text:#0f172a; --muted:#6b7280;
  --brand:#a78bfa; --brand-2:#60a5fa; --mint:#a7f3d0; --peach:#ffd6a5; --pink:#fbcfe8;
  --success:#22c55e1a; --danger:#ef44441a; --warning:#f59e0b1a;
  --press-bg:#f4f5ff; --press-border:#e6e9ff; --hover-bg:#f7f7ff; --ring:rgba(167,139,250,.40);
  --btn-bg:#ffffff; --btn-text:#111827; --btn-border:#e7eaf4; --btn-bg-hover:#f6f7fe; --btn-bg-active:#eff1ff; --btn-border-active:#dcd7fe;
  --shadow-sm:0 2px 8px rgba(17,24,39,.06);
  --shadow-md:0 8px 24px rgba(17,24,39,.08);
  --shadow-lg:0 14px 40px rgba(17,24,39,.10);
  --radius:16px; --radius-lg:20px; --pad:18px;

  /* Mejoras móviles */
  --safe-top: env(safe-area-inset-top, 0px);
  --safe-bottom: env(safe-area-inset-bottom, 0px);
}
*{ box-sizing:border-box }
html,body{ height:100% }
body{
  font-family:'Inter', system-ui, -apple-system, Segoe UI, Roboto, sans-serif;
  color: var(--text);
  background:
    radial-gradient(900px 520px at -10% -10%, rgba(167,139,250,.18), transparent 45%),
    radial-gradient(800px 500px at 110% 0%, rgba(96,165,250,.16), transparent 45%),
    radial-gradient(900px 520px at 50% 110%, rgba(251,207,232,.16), transparent 55%),
    var(--bg);
  padding-top: max(0px, var(--safe-top));
  padding-bottom: max(0px, var(--safe-bottom));
}

/* ===== Avatares ===== */
.avatar-sm,.avatar-md{ border-radius:999px; object-fit:cover; border:1px solid #e6e9f3 }
.avatar-sm{ width:28px;height:28px }
.avatar-md{ width:34px;height:34px }

/* ===== Sidebar (Offcanvas + fija en ≥ lg) ===== */
.sidebar{
  width:260px;
  background:rgba(255,255,255,.88);
  -webkit-backdrop-filter:saturate(140%) blur(12px);
  backdrop-filter:saturate(140%) blur(12px);
  border-right:1px solid var(--border);
  padding:24px 18px;

  /* Scroll independiente */
  height:100dvh;
  max-height:100dvh;
  overflow-y:auto;
  overflow-x:hidden;
  -webkit-overflow-scrolling:touch;
}
@media (min-width: 992px){
  .sidebar{ position:fixed; inset:0 auto 0 0; transform:none!important; visibility:visible!important }
  .content{ margin-left:280px }
}
/* Móviles: offcanvas cómodo y sin scroll del body al abrir */
@media (max-width: 991.98px){
  .offcanvas-lg.offcanvas-start{ width: 86vw; max-width: 360px; }
}

.brand{ display:flex; align-items:center; gap:10px; margin-bottom:18px }
.brand .logo{
  width:36px; height:36px; display:grid; place-items:center; color:#fff;
  background:linear-gradient(135deg, var(--brand), var(--brand-2));
  border-radius:12px; font-weight:700; box-shadow:0 10px 24px rgba(124,58,237,.25);
}
.brand h2{ font-size:clamp(1rem, 1.2vw + .8rem, 1.12rem); margin:0; font-weight:800; letter-spacing:.2px }

.user-pill{
  display:flex; align-items:center; gap:10px; color:var(--muted);
  font-size:.95rem; margin-bottom:14px; white-space:nowrap;
}
.user-pill .user-name{ color:var(--text); font-weight:700 }

/* Navegación: mejor tactilidad en móvil */
.nav.flex-column .nav-link{
  color:#4b5563; margin-bottom:8px; font-weight:600; border-radius:12px; padding:12px 14px;
  border:1px solid transparent; display:flex; align-items:center; gap:10px; transition:all .22s ease;
}
.nav.flex-column .nav-link i{ width:20px; text-align:center }
.nav.flex-column .nav-link:hover{
  color:#111827; transform:translateX(4px);
  background:linear-gradient(135deg, #f6f6ff, #f3f9ff);
  border-color:#eef1fb; box-shadow:var(--shadow-sm);
}
.nav.flex-column .nav-link.active{
  color:#111827; background:linear-gradient(135deg, #f2efff, #eef6ff); border-color:#e8ebff;
}
.nav .badge{ margin-left:auto; color:#111827; background:linear-gradient(135deg,#ffe3e3,#ffeccc); border:1px solid #fff1d6; box-shadow:0 6px 18px rgba(249,115,22,.20) }
.nav .logout{ color:#b42318!important; background:#fff3f2; border-color:#ffe0dd }
.nav .logout:hover{ background:#ffecea }

/* ===== Topbar ===== */
.topbar{
  position:sticky; top:0; z-index:1031; display:flex; align-items:center; justify-content:space-between;
  margin-bottom:18px; padding:12px 14px; background:var(--panel);
  border:1px solid var(--border); border-radius:14px; box-shadow:var(--shadow-sm);
  gap:10px;
}
.topbar h3{ margin:0; font-size:clamp(1rem, .8vw + .9rem, 1.06rem); font-weight:800 }
.topbar .actions{ display:flex; align-items:center; gap:10px }

/* ===== Botones ===== */
.icon-btn, button, .btn{
  border:1px solid var(--btn-border); background:var(--btn-bg); color:var(--btn-text);
  padding:10px 12px; border-radius:12px; line-height:1; cursor:pointer;
  transition: background .2s, border-color .2s, transform .06s, box-shadow .2s;
  box-shadow:var(--shadow-sm);
}
.icon-btn:hover, button:hover, .btn:hover{ background:var(--btn-bg-hover); border-color:var(--btn-border-active) }
.icon-btn:active, button:active, .btn:active{ transform:translateY(1px); background:var(--btn-bg-active); border-color:var(--btn-border-active) }
.btn-primary{
  background:linear-gradient(135deg, var(--brand), var(--brand-2));
  color:#fff; border:1px solid transparent; box-shadow:0 10px 24px rgba(96,165,250,.30);
}
.btn-primary:hover{ filter:brightness(1.02) }
.btn-primary:active{ transform:translateY(1px) }

/* ===== Contenido ===== */
.content{ padding:20px 16px 28px }
@media (min-width: 576px){ .content{ padding:24px 18px 32px } }
@media (min-width: 992px){ .content{ padding:26px 22px 36px } }

/* ===== Stats ===== */
.stats-box{
  background:var(--card); border:1px solid var(--border); border-radius:var(--radius-lg);
  padding:16px; text-align:center; box-shadow:var(--shadow-md); transition:transform .2s, box-shadow .2s; height:100%;
}
@media (min-width: 576px){ .stats-box{ padding:18px } }
.stats-box:hover{ transform:translateY(-4px); box-shadow:var(--shadow-lg) }
.stats-box i{
  font-size:clamp(1.3rem, .6vw + 1rem, 1.6rem); margin-bottom:10px;
  background:linear-gradient(135deg, var(--brand), var(--brand-2));
  -webkit-background-clip:text; background-clip:text; color:transparent;
}
.stats-box h6{ color:#334155; font-weight:700; margin-bottom:6px }
.stats-box p{ font-size:clamp(1.1rem, .6vw + 1rem, 1.28rem); font-weight:800; margin:0 }

/* ===== Cards ===== */
.card-modern{
  background:var(--card); border:1px solid var(--border); border-radius:var(--radius-lg);
  box-shadow:var(--shadow-md);
}
.card-modern .card-header{
  background:transparent; border-bottom:1px solid var(--border); padding:var(--pad);
  display:flex; align-items:center; justify-content:space-between; gap:8px; flex-wrap:wrap;
}
.card-modern .card-header h5{ margin:0; font-size:clamp(1rem, .6vw + 1rem, 1.1rem) }

/* ===== Gráfico ===== */
.chart-wrap{ height: clamp(220px, 38vh, 340px); }
@media (max-width: 575.98px){ .chart-wrap{ height: clamp(220px, 44vh, 360px); } }
canvas{ border-radius:12px; background:#fff; box-shadow:var(--shadow-sm) }

/* ===== Inputs ===== */
input, select, textarea{
  background:#fff; border:1px solid var(--border); color:var(--text);
  border-radius:12px; padding:10px 12px; outline:none; transition:border-color .2s, box-shadow .2s;
}
input:focus, select:focus, textarea:focus{ border-color:#dcd7fe; box-shadow:0 0 0 4px var(--ring) }

/* ===== Accesibilidad / movimiento reducido ===== */
@media (prefers-reduced-motion: reduce){
  *{ transition:none!important; animation:none!important }
}
</style>
</head>
<body>

<!-- SIDEBAR -->
<aside
  class="offcanvas-lg offcanvas-start sidebar"
  tabindex="-1"
  id="sidebar"
  data-bs-scroll="true"
  data-bs-backdrop="false"
  aria-labelledby="sidebarLabel"
>
  <div class="brand">
    <div class="logo">Lv</div>
    <h2 id="sidebarLabel" class="mb-0">Luvadak Admin</h2>
  </div>

  <div class="user-pill">
    <img src="<?php echo htmlspecialchars($fotoSrc); ?>" class="avatar-md" alt="Foto">
    <span class="greet">Hola,</span>
    <span class="user-name"><?php echo htmlspecialchars($primerNombre ?: $usuarioLogin); ?></span>
    <span class="role">· <?php echo htmlspecialchars(ucfirst($rolActual)); ?></span>
  </div>

  <nav class="nav flex-column mt-3">  
    <a class="nav-link" href="registrar_compra_tela.php"><i class="fas fa-receipt"></i> Compra Tela</a>
    <a class="nav-link" href="ver_compras_telas.php"><i class="fas fa-book"></i> Compras Telas</a>
    <a class="nav-link" href="ficha_telas.php"><i class="fas fa-clipboard-list"></i> Ficha Telas</a>

    <a class="nav-link" href="registrar_produccion_tela.php"><i class="fas fa-cut"></i> Producción Telas</a>
    <a class="nav-link" href="ver_produccion.php"><i class="fas fa-clipboard"></i> Historial Producción</a>

    <a class="nav-link" href="agregar_producto.php"><i class="fas fa-plus"></i> Agregar Producto</a>
    <a class="nav-link" href="ver_productos.php"><i class="fas fa-box"></i> Ver Productos</a>
    <a class="nav-link" href="registrar_movimiento.php"><i class="fas fa-sync"></i> Entrada/Salida</a>
    <a class="nav-link" href="ver_movimientos.php"><i class="fas fa-chart-line"></i> Movimientos Inventario</a>

    <a class="nav-link" href="clientes.php"><i class="fas fa-address-book"></i> Clientes</a>
    <a class="nav-link" href="reporte_dia.php"><i class="fas fa-calendar-day"></i> Ventas Día</a>
    <a class="nav-link" href="reportes_admin.php"><i class="fas fa-chart-pie"></i> Reportes Mes</a>

    <a class="nav-link" href="ver_empleados.php"><i class="fas fa-users"></i> Empleados</a>
    <a class="nav-link" href="registrar_pago.php"><i class="fas fa-money-bill"></i> Registrar Pago</a>
    <a class="nav-link" href="ver_pagos.php"><i class="fas fa-file-invoice-dollar"></i> Pagos</a>
    <a class="nav-link" href="buscar_empleado_reporte.php"><i class="fas fa-clipboard"></i> Reporte Empleado</a>

    <a class="nav-link" href="ver_comentarios.php">
      <i class="fas fa-comments"></i> Comentarios
      <?php if($comentarios_no_leidos>0): ?>
        <span class="badge rounded-pill"><?php echo $comentarios_no_leidos; ?></span>
      <?php endif; ?>
    </a>
  </nav>
</aside>

<!-- CONTENT -->
<main class="content">
  <div class="container-fluid">
    <div class="topbar">
      <div class="d-flex align-items-center gap-2">
        <!-- Toggler: abre el offcanvas en móviles -->
        <button class="icon-btn d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebar" aria-controls="sidebar" aria-label="Abrir menú">
          <i class="fa-solid fa-bars"></i>
        </button>
        <h3 class="text-truncate">Panel de Administración</h3>
      </div>

      <div class="actions">
        <a href="perfil.php" title="Mi perfil" class="d-inline-flex align-items-center">
          <img src="<?php echo htmlspecialchars($fotoSrc); ?>" class="avatar-sm" alt="Foto">
        </a>
      </div>
    </div>

    <!-- KPIs -->
    <div class="row g-3 mb-4">
      <div class="col-6 col-sm-6 col-md-3">
        <div class="stats-box h-100">
          <i class="fas fa-cash-register"></i>
          <h6>Ventas Hoy</h6>
          <p><strong><?php echo $ventas_hoy; ?></strong></p>
        </div>
      </div>
      <div class="col-6 col-sm-6 col-md-3">
        <div class="stats-box h-100">
          <i class="fas fa-comments"></i>
          <h6>Comentarios</h6>
          <p><strong><?php echo $comentarios_no_leidos; ?></strong></p>
        </div>
      </div>
      <div class="col-6 col-sm-6 col-md-3">
        <div class="stats-box h-100">
          <i class="fas fa-users"></i>
          <h6>Empleados</h6>
          <p><strong><?php echo $total_empleados; ?></strong></p>
        </div>
      </div>
      <div class="col-6 col-sm-6 col-md-3">
        <div class="stats-box h-100">
          <i class="fas fa-user-friends"></i>
          <h6>Clientes</h6>
          <p><strong><?php echo $total_clientes; ?></strong></p>
        </div>
      </div>
    </div>

    <!-- Gráfico -->
    <div class="card card-modern">
      <div class="card-header">
        <h5 class="m-0">Ventas últimos 7 días</h5>
        <span class="text-muted small">Actualizado: <?php echo date('d/m/Y'); ?></span>
      </div>
      <div class="card-body chart-wrap">
        <canvas id="ventasChart" role="img" aria-label="Gráfico de ventas de los últimos 7 días"></canvas>
      </div>
    </div>
  </div>
</main>

<!-- Bootstrap JS (bundle necesario para offcanvas) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
const ventasData = <?php echo json_encode($ventasPorDia, JSON_NUMERIC_CHECK); ?>;
const etiquetas  = <?php echo json_encode($labels); ?>;

// Chart.js
const ctx = document.getElementById('ventasChart').getContext('2d');
function cssVar(n){ return getComputedStyle(document.body).getPropertyValue(n).trim(); }
function chartColors(){ return { text:cssVar('--text')||'#0f172a', muted:cssVar('--muted')||'#6b7280', grid:cssVar('--border')||'#edf0f7' }; }
let c = chartColors();

// Degradado pastel
const gradient = ctx.createLinearGradient(0, 0, 0, 220);
gradient.addColorStop(0, 'rgba(167,139,250,0.95)');
gradient.addColorStop(1, 'rgba(96,165,250,0.55)');

const chart = new Chart(ctx, {
  type: 'bar',
  data: { 
    labels: etiquetas, 
    datasets: [{
      label:'Ventas (S/.)',
      data: ventasData,
      backgroundColor: gradient,
      borderRadius:12,
      maxBarThickness:48,
      categoryPercentage:0.6,
      barPercentage:0.9
    }] 
  },
  options: {
    responsive:true, maintainAspectRatio:false,
    plugins:{
      legend:{ labels:{ color: c.text }},
      tooltip:{ 
        backgroundColor:'rgba(21,25,52,0.92)', 
        borderColor:'rgba(255,255,255,0.10)', 
        borderWidth:1, padding:10,
        callbacks:{ label:(ctx)=>` S/. ${Number(ctx.parsed.y).toLocaleString('es-PE',{minimumFractionDigits:2})}` }
      }
    },
    scales:{
      x:{ grid:{ color:c.grid }, ticks:{ color:c.muted }},
      y:{ beginAtZero:true, grid:{ color:c.grid }, ticks:{ color:c.muted, callback:(v)=>`S/. ${Number(v).toLocaleString('es-PE')}` }}
    }
  }
});

// Recalibrar colores del gráfico si cambias tema CSS en el futuro
function updateChartTheme(){
  c = chartColors();
  chart.options.plugins.legend.labels.color = c.text;
  chart.options.scales.x.ticks.color = c.muted;
  chart.options.scales.y.ticks.color = c.muted;
  chart.options.scales.x.grid.color  = c.grid;
  chart.options.scales.y.grid.color  = c.grid;
  chart.update();
}

// Mejor UX móvil: cerrar el menú al navegar (solo offcanvas)
document.querySelectorAll('#sidebar a.nav-link').forEach(a=>{
  a.addEventListener('click', ()=>{
    const off = bootstrap.Offcanvas.getInstance(document.getElementById('sidebar'));
    if (off) off.hide();
  });
});
</script>
</body>
</html>
