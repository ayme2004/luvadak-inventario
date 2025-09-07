<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'empleado') {
    header("Location: login.php");
    exit();
}

$usuarioLogin = $_SESSION['usuario'];

require_once "conexion.php";

/* ====== Usuario: nombre, rol, foto ====== */
$stmt = $conexion->prepare("
  SELECT nombre_completo, rol, foto_url
  FROM usuarios
  WHERE usuario = ?
  LIMIT 1
");
$stmt->bind_param("s", $usuarioLogin);
$stmt->execute();
$u = $stmt->get_result()->fetch_assoc();
$stmt->close();

$nombreCompleto = $u['nombre_completo'] ?? $usuarioLogin;
$rolActual      = $u['rol'] ?? 'empleado';

/* Solo PRIMER nombre */
$primerNombre = trim($nombreCompleto);
if ($primerNombre !== '') {
  $partes = preg_split('/\s+/', $primerNombre);
  $primerNombre = $partes[0];
}

$fotosrc = $u['foto_url'] ?? '';
if ($fotosrc && str_starts_with($fotosrc, 'uploads/')) {
  $rutaFisica = __DIR__ . '/' . $fotosrc;
  if (!file_exists($rutaFisica)) $fotosrc = '';
}
if (!$fotosrc) {
  $fotosrc = 'https://api.dicebear.com/7.x/initials/svg?seed=' . urlencode($nombreCompleto ?: $usuarioLogin);
}

/* ====== Menú lateral ====== */
$opciones = [
    ["ver_productos.php", "📦", "Ver Productos"],
    ["punto_venta.php", "🛒", "Punto de Venta"],
    ["ver_boletas.php", "📄", "Historial de Ventas"],
    ["reporte_mis_ventas_pdf.php", "📊", "Reporte Personal"],
    ["historial_clientes_empleado.php", "📁", "Historial del Cliente"],
    ["enviar_comentario.php", "💬", "Enviar Comentario"],
    ["perfil.php", "👤", "Perfil"],
    ["logout.php", "🚪", "Cerrar Sesión", "logout"],
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Panel de Empleado - Luvadak</title>

  <script>
    (function(){
      const saved = localStorage.getItem('theme') || 'dark';
      const root = document.documentElement;
      saved === 'light' ? root.classList.add('light') : root.classList.remove('light');
    })();
  </script>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">

  <style>
  :root{
    --bg:#0f1221; --panel:#14172b; --card:#151934; --text:#e9ecf1; --muted:#8f9bb3; --border:rgba(255,255,255,0.08);
    --brand:#7c3aed; --brand-2:#00d4ff;
    --hover-bg:rgba(124,58,237,0.14); --press-bg:rgba(124,58,237,0.22);
    --press-border:rgba(124,58,237,0.45); --ring:rgba(124,58,237,0.45);
    --btn-text:#dbe2f5; --btn-border:var(--border); --btn-bg:transparent;
    --btn-bg-hover:var(--hover-bg); --btn-bg-active:var(--press-bg);
  }
  .light{
    --bg:#eef2f7; --panel:#ffffff; --card:#ffffff; --text:#0f172a; --muted:#6b7280; --border:rgba(0,0,0,0.08);
    --hover-bg:rgba(124,58,237,0.08); --press-bg:rgba(124,58,237,0.12);
    --press-border:rgba(124,58,237,0.30); --ring:rgba(124,58,237,0.35);
    --btn-text:#0f172a; --btn-border:var(--border); --btn-bg:transparent; --btn-bg-hover:#f3f4ff; --btn-bg-active:#e9e5ff;
  }

  *{box-sizing:border-box}
  html,body{height:100%}
  body{
    font-family:'Inter',system-ui,-apple-system,Segoe UI,Roboto,sans-serif;
    background:
      radial-gradient(1100px 550px at -10% -10%, rgba(124,58,237,0.16), transparent 40%),
      radial-gradient(900px 500px at 110% 10%, rgba(0,212,255,0.12), transparent 45%),
      var(--bg);
    color:var(--text);
  }
  .light, .light body{ background:var(--bg); }

  .app{ display:grid; grid-template-columns: 260px 1fr; gap:0; }
  @media (max-width: 992px){ .app{ grid-template-columns: 1fr; } }

  /* ===== Sidebar ===== */
  .sidebar{
    position:sticky; top:0; align-self:start; min-height:100vh; padding:22px 16px;
    background: rgba(20,23,43,0.75); backdrop-filter:saturate(140%) blur(14px); border-right:1px solid var(--border);
  }
  .light .sidebar{ background: rgba(255,255,255,0.92); border-right:1px solid #e5e7eb; }

  .brand{ display:flex; align-items:center; gap:10px; margin-bottom:16px; }
  .logo{ width:38px;height:38px; display:grid; place-items:center; color:#fff; font-weight:800;
    background: linear-gradient(135deg, var(--brand), var(--brand-2));
    border-radius:12px; box-shadow:0 10px 24px rgba(124,58,237,.35);
  }
  .brand h2{ font-size:1.05rem; margin:0; font-weight:800; letter-spacing:.2px; }

  .user-pill{ display:flex; align-items:center; gap:10px; color:var(--muted); margin-bottom:14px; white-space:nowrap; }
  .user-pill .name{ color:var(--text); font-weight:700; }
  .user-pill .role{ color:var(--muted); }
  .light .user-pill .name{ color:#111827; }

  .nav.flex-column .nav-link{
    color:#cbd5e1; border:1px solid transparent; border-radius:10px;
    padding:10px 12px; display:flex; align-items:center; gap:10px; transition:.22s;
  }
  .nav.flex-column .nav-link:hover{
    transform: translateX(4px);
    color:#fff; background:linear-gradient(135deg, rgba(124,58,237,.14), rgba(0,212,255,.10));
    border-color:var(--border);
  }
  .nav.flex-column .nav-link.active{
    color:#fff; background:linear-gradient(135deg, rgba(124,58,237,.20), rgba(0,212,255,.14));
    border-color:var(--border);
  }
  .nav .logout{ background:rgba(239,68,68,.10); border-color:rgba(239,68,68,.30); color:#ffb4b4 !important; }
  .nav .logout:hover{ background:rgba(239,68,68,.16); }
  .light .nav.flex-column .nav-link{ color:#374151; }
  .light .nav.flex-column .nav-link:hover{ background:#f3f4ff; border-color:#e5e7eb; color:#111827; }
  .light .nav.flex-column .nav-link.active{ background:#eef2ff; border-color:#e5e7eb; color:#111827; }
  .light .nav .logout{ background:#fff5f5; border-color:#fecaca; color:#b91c1c !important; }

  /* ===== Topbar ===== */
  .topbar{
    position: sticky; top:0; z-index:10; display:flex; align-items:center; justify-content:space-between;
    padding:12px 14px; margin: 16px 16px 18px; background: rgba(15,18,33,.60);
    border:1px solid var(--border); border-radius:14px; backdrop-filter: blur(10px);
  }
  .light .topbar{ background: rgba(255,255,255,.92); border-color:#e5e7eb; }

  .icon-btn{
    border:1px solid var(--btn-border); background:var(--btn-bg); color:var(--btn-text);
    padding:8px 10px; border-radius:10px; cursor:pointer;
    transition: background .2s, border-color .2s, color .2s, transform .06s;
  }
  .icon-btn:hover{ background:var(--btn-bg-hover); border-color:var(--press-border); color:var(--text); }
  .icon-btn:active{ transform: translateY(1px); background:var(--btn-bg-active); border-color:var(--press-border); }
  .icon-btn:focus-visible, .nav-link:focus-visible, a:focus-visible, button:focus-visible{
    outline:none; box-shadow: 0 0 0 3px var(--ring);
  }

  .main{ padding-right:16px; }

  /* ===== Hero ===== */
  .hero{
    margin: 0 16px 18px; border-radius: 16px; border: 1px solid var(--border);
    background: linear-gradient(135deg, rgba(124,58,237,0.22), rgba(0,212,255,0.22)),
                linear-gradient(180deg, rgba(255,255,255,.02), rgba(0,0,0,.10)), var(--card);
    box-shadow: 0 8px 24px rgba(0,0,0,.22); padding: clamp(22px, 4vw, 32px); text-align: center;
  }
  .hero__title{ font-weight: 800; font-size: clamp(1.15rem, 1.6vw, 1.35rem); margin: 0 0 6px; }
  .hero__sub{ color: var(--muted); margin: 0; }
  .hero__pill{
    display:inline-flex; align-items:center; gap:8px; margin-bottom:10px;
    padding:8px 12px; border-radius:999px; border:1px solid var(--border);
    background: rgba(0,0,0,.10); color:var(--text); font-weight:600; font-size:.92rem;
  }
  .light .hero{ background:#fff; border-color:#e5e7eb; box-shadow:0 8px 22px rgba(17,24,39,.06); }
  .light .hero__pill{ background:#f8fafc; border-color:#e5e7eb; color:#0f172a; }

  /* ===== Cards ===== */
  .cards{ margin: 0 16px 28px; display:grid; grid-template-columns: repeat(12, 1fr); gap: 16px; }
  .card-modern{
    grid-column: span 4; background: linear-gradient(180deg, rgba(255,255,255,.02), rgba(0,0,0,.10)), var(--card);
    border:1px solid var(--border); border-radius: 16px; box-shadow: 0 6px 20px rgba(0,0,0,.18);
    padding: 20px; text-align:center; transition: .18s; position: relative;
  }
  .card-modern:hover{ transform: translateY(-4px); box-shadow: 0 16px 40px rgba(0,0,0,.32); }
  .card-modern img{ width:60px; margin-bottom: 12px; }
  .card-modern h5{ margin:6px 0 8px; font-weight:800; }
  .card-modern p{ color: var(--muted); margin:0; }
  .light .card-modern{ background:#fff; border-color:#e5e7eb; box-shadow:0 6px 18px rgba(17,24,39,.06); }

  /* ===== Avatares ===== */
  .avatar-sm{ width:28px; height:28px; border-radius:999px; object-fit:cover; border:1px solid rgba(255,255,255,.15) }
  .avatar-pill{ width:22px; height:22px; border-radius:999px; object-fit:cover; border:1px solid rgba(255,255,255,.15) }
  .light .avatar-sm, .light .avatar-pill{ border-color: rgba(17,24,39,.12) }

  /* ======== RESPONSIVE EXTRA ======== */
  @media (max-width: 992px){
    .sidebar{
      position: fixed;
      top: 0; left: 0; bottom: 0;
      width: 86vw;
      max-width: 340px;
      transform: translateX(-100%);
      transition: transform .25s ease, box-shadow .25s ease;
      z-index: 1045;
    }
    .sidebar.open{
      transform: translateX(0);
      box-shadow: 0 0 0 9999px rgba(0,0,0,.25);
    }
    .card-modern{ grid-column: span 12; }
  }
  @media (min-width: 576px) and (max-width: 991.98px){
    .card-modern{ grid-column: span 6; }
  }
  </style>
</head>
<body>

<div class="app">
  <!-- ===== SIDEBAR ===== -->
  <aside class="sidebar" id="sidebar">
    <div class="brand">
      <div class="logo">Lv</div>
      <h2>Luvadak Admin</h2>
    </div>

    <!-- Saludo con PRIMER nombre + foto -->
    <div class="user-pill">
      <img src="<?php echo htmlspecialchars($fotosrc); ?>" alt="Foto" class="avatar-sm">
      <span class="greet">Hola,</span>
      <span class="name"><?php echo htmlspecialchars($primerNombre ?: $usuarioLogin); ?></span>
      <span class="role">· <?php echo htmlspecialchars(ucfirst($rolActual)); ?></span>
    </div>

    <nav class="nav flex-column mt-2">
      <?php
        $paginaActual = basename($_SERVER['PHP_SELF']);
        foreach ($opciones as $opcion) {
          $archivo   = $opcion[0];
          $texto     = $opcion[2];
          $claseExtra= isset($opcion[3]) ? $opcion[3] : '';
          $active    = ($paginaActual === $archivo) ? 'active' : '';
          echo "<a class='nav-link $active $claseExtra' href='$archivo'>{$opcion[1]} $texto</a>";
        }
      ?>
    </nav>
  </aside>

  <!-- ===== MAIN ===== -->
  <section class="main">
<div class="topbar">
  <div class="d-flex align-items-center gap-2">
    <button class="icon-btn d-lg-none" id="menuBtn" aria-label="Abrir menú">
      <i class="fa-solid fa-bars"></i>
    </button>
    <h3 class="m-0 fw-bold">Panel de Empleado</h3>
  </div>

  <!-- Derecha: foto (link a perfil) + tema -->
  <div class="d-flex align-items-center gap-2 ms-auto">
    <a href="perfil.php" title="Mi perfil">
      <img src="<?php echo htmlspecialchars($fotosrc); ?>" alt="Foto" class="avatar-sm">
    </a>
  </div>
</div>


    <!-- Hero -->
    <section class="hero">
      <div class="hero__pill">
        <img src="<?php echo htmlspecialchars($fotosrc); ?>" alt="Foto" class="avatar-pill">
        Hola, <strong><?php echo htmlspecialchars($primerNombre ?: $usuarioLogin); ?></strong>
      </div>
      <h2 class="hero__title">👋 Bienvenid@</h2>
      <p class="hero__sub">Este es tu panel en <strong>Luvadak</strong>. Usa el menú izquierdo para navegar.</p>
      <p class="hero__sub">💡 <em>“La calidad no es un acto, es un hábito.”</em></p>
    </section>

    <!-- Tarjetas -->
    <section class="cards">
      <article class="card-modern">
        <img src="https://cdn-icons-png.flaticon.com/512/3135/3135715.png" alt="Misión" loading="lazy">
        <h5>Misión</h5>
        <p>Brindar soluciones innovadoras que satisfagan las necesidades de nuestros clientes con excelencia y compromiso.</p>
      </article>

      <article class="card-modern">
        <img src="https://cdn-icons-png.flaticon.com/512/3135/3135768.png" alt="Visión" loading="lazy">
        <h5>Visión</h5>
        <p>Ser la empresa líder en nuestro sector, reconocida por la calidad de nuestro servicio y la satisfacción de nuestros clientes.</p>
        </article>

      <article class="card-modern">
        <img src="https://cdn-icons-png.flaticon.com/512/3135/3135792.png" alt="Valores" loading="lazy">
        <h5>Valores</h5>
        <p>Compromiso, integridad, innovación, trabajo en equipo y responsabilidad social.</p>
      </article>
    </section>
  </section>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/js/all.min.js"></script>
<script>
  // Sidebar móvil
  (function(){
    const sidebar = document.getElementById('sidebar');
    const menuBtn = document.getElementById('menuBtn');
    if(menuBtn){
      menuBtn.addEventListener('click', ()=> sidebar.classList.toggle('open'));
      if (window.innerWidth <= 992) sidebar.classList.remove('open');
      else sidebar.classList.add('open');
      window.addEventListener('resize', ()=> {
        if (window.innerWidth > 992) sidebar.classList.add('open');
        else sidebar.classList.remove('open');
      });
    }

    // NUEVO: cerrar el sidebar al hacer click en un enlace (solo móvil)
    document.querySelectorAll('#sidebar .nav-link').forEach(a=>{
      a.addEventListener('click', ()=>{
        if (window.innerWidth <= 992) sidebar.classList.remove('open');
      });
    });
  })();
</script>
</body>
</html>

