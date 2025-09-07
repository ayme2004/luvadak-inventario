<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'admin') {
    header("Location: login.php");
    exit();
}
include("conexion.php");

/* ===== Eliminar (GET) ===== */
if (isset($_GET['eliminar'])) {
    $idEliminar = intval($_GET['eliminar']);
    $stmt = $conexion->prepare("DELETE FROM comentarios WHERE id_comentario = ?");
    $stmt->bind_param("i", $idEliminar);
    $stmt->execute();
    $stmt->close();
    header("Location: ver_comentarios.php");
    exit();
}

/* ===== Enviar (POST) ===== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mensaje'])) {
    $mensaje = trim($_POST['mensaje']);
    $id_destino = $_POST['id_usuario'] ?? '';

    if ($mensaje !== '') {
        if ($id_destino === "todos") {
            $usuarios = $conexion->query("SELECT id_usuario FROM usuarios WHERE rol = 'empleado'");
            while ($u = $usuarios->fetch_assoc()) {
                $stmt = $conexion->prepare("INSERT INTO comentarios (id_usuario, mensaje) VALUES (?, ?)");
                $stmt->bind_param("is", $u['id_usuario'], $mensaje);
                $stmt->execute();
                $stmt->close();
            }
            $mensaje_enviado = "Mensaje enviado a todos los empleados.";
        } else {
            $id_destino = intval($id_destino);
            $stmt = $conexion->prepare("INSERT INTO comentarios (id_usuario, mensaje) VALUES (?, ?)");
            $stmt->bind_param("is", $id_destino, $mensaje);
            if ($stmt->execute()) $mensaje_enviado = "Mensaje enviado correctamente.";
            else $error_envio = "Error al enviar mensaje.";
            $stmt->close();
        }
    } else {
        $error_envio = "El mensaje no puede estar vacío.";
    }
}

/* ===== Listas ===== */
$sql = "SELECT c.id_comentario, c.mensaje, c.fecha_envio, u.nombre_completo 
        FROM comentarios c 
        JOIN usuarios u ON c.id_usuario = u.id_usuario 
        ORDER BY c.fecha_envio DESC";
$resultado = $conexion->query($sql);

$empleados = $conexion->query("SELECT id_usuario, nombre_completo FROM usuarios WHERE rol = 'empleado'");
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Comentarios de Empleados - Luvadak</title>
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover"/>

  <!-- Bootstrap + Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet"/>

  <style>
    :root{
      --bg:#f8fafc; --panel:#ffffff; --text:#0f172a; --muted:#667085; --border:#e6e9f2;
      --brand:#7c3aed; --brand2:#00d4ff; --ring:rgba(124,58,237,.22);
      --radius:14px; --radius-sm:10px; --radius-lg:18px; --shadow:0 10px 26px rgba(16,24,40,.08);
      --safe-top: env(safe-area-inset-top); --safe-bottom: env(safe-area-inset-bottom);
    }

    /* Base y anti-overflow */
    *,*::before,*::after{ box-sizing:border-box; }
    html, body { height:100%; }
    body{
      overflow-x:hidden;
      background:
        radial-gradient(1000px 520px at -10% -10%, rgba(124,58,237,.10), transparent 45%),
        radial-gradient(900px 480px at 110% 0%, rgba(0,212,255,.10), transparent 45%),
        var(--bg);
      color:var(--text);
      font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, sans-serif;
      -webkit-font-smoothing:antialiased; -moz-osx-font-smoothing:grayscale;
    }
    .wrap{ max-width:1100px; margin:calc(18px + var(--safe-top)) auto calc(18px + var(--safe-bottom)); padding:0 12px; }

    /* Tipografías fluidas */
    .fs-fluid-sm{ font-size:clamp(.9rem, .85rem + .2vw, 1rem); }
    .fs-fluid-md{ font-size:clamp(1rem, .95rem + .4vw, 1.15rem); }
    .fs-fluid-lg{ font-size:clamp(1.05rem, 1rem + .8vw, 1.35rem); }

    /* Hero (sticky en móvil para acceso rápido) */
    .hero{
      position:sticky; top:0; z-index:10;
      display:flex; align-items:center; gap:12px;
      background:linear-gradient(180deg, rgba(255,255,255,.96), rgba(255,255,255,.98));
      border:1px solid var(--border); border-radius:var(--radius-lg);
      padding:12px 14px; box-shadow:var(--shadow); margin-bottom:12px;
      backdrop-filter:saturate(120%) blur(6px);
    }
    .hero .icon{
      width:44px;height:44px;border-radius:12px;display:grid;place-items:center;color:#fff;
      background:linear-gradient(135deg, var(--brand), var(--brand2));
      box-shadow:0 12px 24px rgba(124,58,237,.25);
      font-size:1.25rem; flex:0 0 44px;
    }
    .hero .title{ font-weight:800; }
    .hero .sub{ color:var(--muted); font-weight:500 }

    /* Grid adaptable */
    .grid{ display:grid; gap:16px }
    @media (min-width:992px){
      .grid{ grid-template-columns: 1fr 1.2fr; align-items:start }
    }

    .block{
      border:1px solid var(--border);
      border-radius:var(--radius);
      background:var(--panel);
      box-shadow:var(--shadow);
      overflow:hidden;
      min-width:0;
    }
    .block-header{
      padding:12px 14px; border-bottom:1px solid var(--border);
      font-weight:800; letter-spacing:.2px; background:#fff;
      display:flex; align-items:center; gap:10px; justify-content:space-between;
      position:sticky; top:64px; z-index:5; /* debajo del hero en móvil */
    }
    @media (min-width:992px){ .block-header{ position:static } }

    .block-body{ padding:14px }
    .block-footer{ padding:10px 14px; border-top:1px solid var(--border); background:#fafbff }

    /* Formulario: inputs/tap targets grandes */
    .form-label{ font-weight:700; }
    .form-control, .form-select{
      border:1px solid var(--border); border-radius:12px; transition:.2s; min-height:48px;
      padding:.6rem .9rem; font-size:1rem;
    }
    .form-control:focus, .form-select:focus{ border-color:#d5d9e3; box-shadow:0 0 0 3px var(--ring); }
    .compose .row > [class*="col-"]{ margin-bottom:10px; }
    @media (min-width:768px){ .compose .row > [class*="col-"]{ margin-bottom:0 } }

    /* Botones accesibles */
    .btn{ border-radius:999px; font-weight:800; border:1px solid var(--border); letter-spacing:.2px; }
    .btn:focus-visible{ outline:3px solid rgba(124,58,237,.35); outline-offset:2px; }
    .btn-primary{
      background:linear-gradient(135deg, var(--brand), var(--brand2));
      border-color:transparent; color:#fff; box-shadow:0 10px 22px rgba(124,58,237,.28);
    }
    .btn-primary:hover{ filter:brightness(1.06) }
    .btn-secondary{ background:#fff; color:#0f172a; border-color:var(--border) }
    .btn-secondary:hover{ background:#f6f7fb }
    .btn-danger{
      background:linear-gradient(135deg, #ef4444, #f97316);
      border-color:transparent; color:#fff; box-shadow:0 8px 18px rgba(239,68,68,.25);
    }
    .btn-danger:hover{ filter:brightness(1.05) }

    /* Tamaños responsivos de botón */
    .btn-touch{ min-height:44px; padding:.7rem 1rem; font-size:1rem; }
    @media (min-width:992px){ .btn-touch{ padding:.55rem .9rem; font-size:.975rem } }

    /* Buscador */
    .search{ position:relative; width:100%; max-width:520px; margin-left:auto; }
    .search .form-control{ padding-left:38px }
    .search .icon{ position:absolute; left:10px; top:50%; transform:translateY(-50%); color:var(--brand); font-size:1.05rem; }

    /* Comentarios */
    .comment{
      border:1px solid #e6ebff;
      background:linear-gradient(180deg,#f3f6ff,#ffffff);
      border-radius:14px; padding:12px; max-width:100%;
      display:grid; grid-template-columns:1fr auto; gap:10px; align-items:flex-start;
    }
    .comment + .comment{ margin-top:10px }
    .comment .content{ min-width:0; }
    .name{ font-weight:800; color:#111827 }
    .meta{ color:#6b7280; font-size:.9rem; display:flex; align-items:center; gap:6px; flex-wrap:wrap }
    .msg{
      margin:.35rem 0 0; white-space:pre-wrap; overflow-wrap:anywhere; word-break:break-word;
      font-size:1rem;
    }

    /* Acciones: botón grande y fijo a la derecha; en móvil se alinea abajo */
    .actions{ display:flex; align-items:center; gap:8px; }
    .btn-icon{
      display:inline-flex; align-items:center; justify-content:center;
      width:44px; height:44px; border-radius:12px; padding:0;
    }
    .btn-icon i{ font-size:1.05rem }

    /* Scroll interno para la bandeja con padding adaptable */
    .inbox .block-body{ max-height:70vh; overflow:auto; }
    @media (max-width:575.98px){ .inbox .block-body{ max-height:none } }

    .empty{
      border:1px dashed #dbe0ef; border-radius:14px; padding:18px; text-align:center;
      color:#6b7280; background:#f9fbff;
    }

    /* Mejoras de accesibilidad y movimiento reducido */
    @media (prefers-reduced-motion: reduce){
      *{ animation-duration:0.01ms !important; animation-iteration-count:1 !important; transition-duration:0.01ms !important; scroll-behavior:auto !important; }
    }
  </style>
</head>
<body>
  <div class="wrap">
    <!-- Hero -->
    <div class="hero">
      <div class="icon" aria-hidden="true"><i class="bi bi-chat-dots-fill"></i></div>
      <div class="flex-grow-1">
        <div class="title fs-fluid-lg">Comentarios de Empleados</div>
        <div class="sub fs-fluid-sm">Envía anuncios y gestiona los mensajes recibidos</div>
      </div>
      <div class="d-none d-sm-block">
        <a href="dashboard_admin.php" class="btn btn-secondary btn-touch">
          <i class="bi bi-arrow-left-circle me-1"></i> Volver al Panel
        </a>
      </div>
      <div class="d-sm-none ms-auto">
        <a href="dashboard_admin.php" class="btn btn-secondary btn-icon" aria-label="Volver al Panel">
          <i class="bi bi-arrow-left-circle"></i>
        </a>
      </div>
    </div>

    <?php if (isset($mensaje_enviado)): ?>
      <div class="alert alert-success border-0 shadow-sm"><?= $mensaje_enviado ?></div>
    <?php elseif (isset($error_envio)): ?>
      <div class="alert alert-danger border-0 shadow-sm"><?= $error_envio ?></div>
    <?php endif; ?>

    <!-- Grid 2 bloques -->
    <div class="grid">
      <!-- Enviar -->
      <section class="block compose">
        <div class="block-header">
          <span class="fs-fluid-md"><i class="bi bi-send-fill me-1"></i> Enviar nuevo mensaje</span>
        </div>
        <div class="block-body">
          <form method="POST" class="vstack gap-3">
            <div class="row g-2">
              <div class="col-12 col-md-5">
                <label class="form-label">Destino</label>
                <select name="id_usuario" class="form-select" required>
                  <option value="todos">📢 Todos los empleados</option>
                  <?php while ($e = $empleados->fetch_assoc()): ?>
                    <option value="<?= $e['id_usuario'] ?>"><?= htmlspecialchars($e['nombre_completo']) ?></option>
                  <?php endwhile; ?>
                </select>
              </div>
              <div class="col-12 col-md-7">
                <label class="form-label">Mensaje</label>
                <input name="mensaje" class="form-control" placeholder="Escribe tu mensaje…" required>
              </div>
            </div>
            <div class="d-flex gap-2 justify-content-end">
              <button class="btn btn-primary btn-touch">
                <i class="bi bi-megaphone-fill me-1"></i> Enviar
              </button>
              <button type="reset" class="btn btn-secondary btn-touch d-none d-md-inline-flex">
                <i class="bi bi-eraser-fill me-1"></i> Limpiar
              </button>
            </div>
          </form>
        </div>
        <div class="block-footer">
          <small class="text-muted">Consejo: Sé claro y breve. Los mensajes aparecerán al empleado de más nuevo a más antiguo.</small>
        </div>
      </section>

      <!-- Bandeja -->
      <section class="block inbox">
        <div class="block-header">
          <span class="fs-fluid-md"><i class="bi bi-inbox-fill me-1"></i> Bandeja de comentarios</span>
          <div class="search d-none d-sm-block">
            <i class="bi bi-search icon"></i>
            <input id="filtro" type="text" class="form-control" placeholder="Filtrar por nombre o texto…">
          </div>
        </div>

        <!-- Buscador visible arriba en móviles -->
        <div class="px-3 pt-2 d-sm-none">
          <div class="search">
            <i class="bi bi-search icon"></i>
            <input id="filtro_m" type="text" class="form-control" placeholder="Filtrar por nombre o texto…">
          </div>
        </div>

        <div class="block-body">
          <?php if ($resultado->num_rows > 0): ?>
            <div id="lista" class="vstack">
              <?php while ($row = $resultado->fetch_assoc()): ?>
                <div class="comment item">
                  <div class="content">
                    <div class="meta">
                      <i class="bi bi-clock"></i>
                      <?= date("d/m/Y H:i", strtotime($row['fecha_envio'])) ?>
                    </div>
                    <div class="name"><?= htmlspecialchars($row['nombre_completo']) ?></div>
                    <div class="msg"><?= nl2br(htmlspecialchars($row['mensaje'])) ?></div>
                  </div>
                  <div class="actions">
                    <a href="?eliminar=<?= (int)$row['id_comentario'] ?>"
                       class="btn btn-danger btn-icon"
                       onclick="return confirm('¿Eliminar este comentario?');"
                       title="Eliminar" aria-label="Eliminar comentario">
                       <i class="bi bi-trash-fill"></i>
                    </a>
                  </div>
                </div>
              <?php endwhile; ?>
            </div>
          <?php else: ?>
            <div class="empty">
              <div class="mb-1" style="font-size:1.05rem">No hay comentarios registrados aún.</div>
              <div class="text-muted">Cuando los empleados envíen mensajes, aparecerán aquí.</div>
            </div>
          <?php endif; ?>
        </div>
      </section>
    </div>
  </div>

  <script>
    // Filtro por texto (nombre + mensaje) — mantiene la lógica pero soporta input móvil y desktop
    const filtroDesktop = document.getElementById('filtro');
    const filtroMobile = document.getElementById('filtro_m');
    const items = document.querySelectorAll('#lista .item');

    function applyFilter(q){
      if(!items.length) return;
      const query = (q || '').toLowerCase().trim();
      items.forEach(el => {
        const txt = el.textContent.toLowerCase();
        el.style.display = txt.includes(query) ? '' : 'none';
      });
    }

    if (filtroDesktop) filtroDesktop.addEventListener('input', (e)=>applyFilter(e.target.value));
    if (filtroMobile) filtroMobile.addEventListener('input', (e)=>applyFilter(e.target.value));
  </script>
</body>
</html>
