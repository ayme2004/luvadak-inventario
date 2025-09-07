<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'empleado') {
    header("Location: login.php");
    exit();
}

include("conexion.php");

/* ===== Obtener id_usuario actual ===== */
$usuario = $_SESSION['usuario'] ?? '';
$stmt = $conexion->prepare("SELECT id_usuario FROM usuarios WHERE usuario = ?");
$stmt->bind_param("s", $usuario);
$stmt->execute();
$res = $stmt->get_result();
$id_usuario = $res->fetch_assoc()['id_usuario'] ?? null;
$stmt->close();

if (!$id_usuario) {
    header("Location: login.php");
    exit();
}

/* ===== CSRF ===== */
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}
$csrf = $_SESSION['csrf'];

/* ===== POST: insertar comentario ===== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf'] ?? '';
    if (!hash_equals($_SESSION['csrf'], $token)) {
        http_response_code(400);
        $error = "Token inválido. Recarga la página.";
    } else {
        $mensaje = trim($_POST['mensaje'] ?? '');
        if ($mensaje !== '') {
            $insert = $conexion->prepare("INSERT INTO comentarios (id_usuario, mensaje) VALUES (?, ?)");
            $insert->bind_param("is", $id_usuario, $mensaje);
            if ($insert->execute()) {
                header("Location: " . strtok($_SERVER["REQUEST_URI"], '?') . "?sent=1");
                exit();
            } else {
                $error = "❌ Error al enviar el comentario.";
            }
            $insert->close();
        } else {
            $error = "⚠️ El mensaje no puede estar vacío.";
        }
    }
}

/* ===== Mensajes ===== */
$list = $conexion->prepare("
    SELECT mensaje, fecha_envio
    FROM comentarios
    WHERE id_usuario = ?
    ORDER BY fecha_envio DESC
");
$list->bind_param("i", $id_usuario);
$list->execute();
$comentarios_admin = $list->get_result();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Comentarios a Administración - Luvadak</title>
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  <style>
    :root{
      --bg:#f7f8fb; --panel:#ffffff; --text:#0f172a; --muted:#667085; --border:#e7ebf3;
      --brand:#6d5dfc; --brand2:#22d3ee; --ring:rgba(109,93,252,.35);
      --radius:14px; --radius-sm:12px; --shadow-sm:0 1px 6px rgba(15,23,42,.06); --shadow-md:0 10px 26px rgba(15,23,42,.08);
    }

    *{box-sizing:border-box}
    html,body{height:100%}
    body{
      background:var(--bg);
      color:var(--text);
      font-family:Inter, system-ui, -apple-system, Segoe UI, Roboto, sans-serif;
      -webkit-font-smoothing:antialiased; -moz-osx-font-smoothing:grayscale;
    }

    .hero{ max-width:1200px; margin:20px auto 10px; padding:0 16px; }
    .hero-bar{
      display:flex; align-items:center; gap:12px; flex-wrap:wrap;
      background:var(--panel); border:1px solid var(--border); border-radius:16px;
      padding:12px 14px; box-shadow:var(--shadow-sm);
    }
    .hero-icon{ width:38px;height:38px;border-radius:12px;display:grid;place-items:center;color:#fff;
      background:linear-gradient(135deg,var(--brand),var(--brand2)); }
    .hero-title{ font-weight:800; font-size:clamp(1.05rem,1.2vw+1rem,1.2rem) }
    .hero-sub{ color:var(--muted) }

    .page{ max-width:1200px; margin:0 auto 34px; padding:0 16px }
    .blocks{ display:flex; flex-direction:column; gap:16px }
    @media (min-width:992px){ .blocks{ flex-direction:row } }

    .block{
      border:1px solid var(--border); border-radius:16px; background:var(--panel);
      box-shadow:var(--shadow-sm); overflow:hidden; display:flex; flex-direction:column; min-width:0;
    }
    .block-header{ padding:12px 14px; border-bottom:1px solid var(--border); font-weight:700 }
    .block-body{ padding:14px 14px 16px }
    .block-footer{ padding:12px 14px; border-top:1px solid var(--border); background:#fafbff }

    .compose{ flex:1 }
    .inbox{ flex:1.25 }

    /* ===== Mobile tabs (solo en móvil) ===== */
    .mobile-tabs{ display:none; }
    @media (max-width:991.98px){
      .mobile-tabs{ display:block; position:sticky; top:8px; z-index:20; margin:6px 0 2px }
      .seg{ display:grid; grid-template-columns:1fr 1fr; gap:6px; background:#f1f4ff; border:1px solid var(--border); border-radius:999px; padding:6px }
      .seg button{
        border:none; border-radius:999px; padding:.55rem .9rem; font-weight:700; color:#334155; background:transparent;
      }
      .seg button.active{ color:#fff; background:linear-gradient(135deg,var(--brand),var(--brand2)); box-shadow:0 6px 14px rgba(34,211,238,.18) }
      .mobile-pane{ display:none }
      .mobile-pane.show{ display:block }
    }
    @media (min-width:992px){
      .mobile-pane{ display:block !important }
    }

    /* ===== Inputs y botones ===== */
    .form-control{
      border:1px solid var(--border); border-radius:var(--radius-sm); padding:.65rem .9rem; background:#fff;
      transition:border-color .15s, box-shadow .15s;
    }
    .form-control:focus{ border-color:#dcd7fe; box-shadow:0 0 0 4px var(--ring) }
    textarea{ resize:none }

    .btn{
      border-radius:12px; font-weight:700; border:1px solid var(--border);
      min-height:44px; padding:.6rem 1rem; box-shadow:var(--shadow-sm);
      transition:transform .12s ease, background .2s, border-color .2s, box-shadow .2s;
      display:inline-flex; align-items:center; gap:.4rem;
    }
    .btn:focus-visible{ outline:3px solid var(--ring); outline-offset:2px }
    .btn-primary{
      color:#fff; border-color:transparent;
      background:linear-gradient(135deg,var(--brand),var(--brand2)); box-shadow:0 10px 26px rgba(34,211,238,.18);
    }
    .btn-primary:hover{ transform:translateY(-1px); filter:brightness(1.03) }
    .btn-secondary{ background:#fff; color:#0f172a }
    .btn-secondary:hover{ background:#f6f7fb; transform:translateY(-1px) }
    .btn:disabled{ opacity:.6; cursor:not-allowed }

    .btn-group-fluid{ display:flex; gap:10px; flex-wrap:wrap }
    .btn-group-fluid .btn{ flex:1 1 160px }

    .counter{ color:var(--muted); font-size:.9rem }

    /* ===== Mensajes (tarjetas limpias) ===== */
    .msg{
      border:1px solid #e7ebff; background:#fff; border-radius:14px; padding:12px 14px; box-shadow:var(--shadow-sm);
    }
    .msg-header{ display:flex; justify-content:space-between; align-items:center; gap:10px; margin-bottom:6px }
    .msg-from{ font-weight:700; display:flex; align-items:center; gap:6px; color:#0f172a }
    .msg-time{ color:#6b7280; font-size:.86rem; display:flex; align-items:center; gap:6px; white-space:nowrap }
    .msg-text{ color:#1f2937; font-size:.95rem; line-height:1.5; white-space:pre-wrap; overflow-wrap:anywhere }

    /* Scroll interno de la bandeja */
    .inbox .block-body{ max-height:65vh; overflow:auto }
  </style>
</head>
<body>

  <!-- Header -->
  <div class="hero">
    <div class="hero-bar">
      <div class="hero-icon"><i class="bi bi-chat-dots-fill"></i></div>
      <div class="flex-grow-1">
        <div class="hero-title">Comentarios a Administración</div>
        <div class="hero-sub">Envía sugerencias o dudas y revisa las respuestas del administrador</div>
      </div>
      <a href="dashboard_empleado.php" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Volver
      </a>
    </div>
  </div>

  <div class="page">
    <!-- Conmutador móvil -->
    <div class="mobile-tabs d-lg-none">
      <div class="seg">
        <button class="active" data-show="compose">Escribir</button>
        <button data-show="inbox">Mensajes</button>
      </div>
    </div>

    <div class="blocks">
      <!-- Enviar -->
      <section id="compose" class="block compose mobile-pane show">
        <div class="block-header"><i class="bi bi-pencil-square me-1"></i> Enviar nuevo mensaje</div>
        <div class="block-body">
          <?php if (isset($_GET['sent'])): ?>
            <div class="alert alert-success rounded-3">✅ Comentario enviado correctamente.</div>
          <?php elseif (isset($error)): ?>
            <div class="alert alert-danger rounded-3"><?= htmlspecialchars($error) ?></div>
          <?php endif; ?>

          <form method="POST" id="formMsg" novalidate>
            <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
            <label for="mensaje" class="form-label">Tu mensaje</label>
            <textarea id="mensaje" name="mensaje" rows="5" class="form-control"
                      placeholder="Escribe aquí… (Ctrl/Cmd + Enter para enviar)" maxlength="500" required></textarea>

            <div class="d-flex justify-content-between align-items-center mt-2">
              <small class="counter"><span id="cc">0</span>/500</small>
              <div class="btn-group-fluid" style="max-width:360px">
                <button id="btnSend" type="submit" class="btn btn-primary" disabled>
                  <i class="bi bi-send-fill"></i> Enviar
                </button>
              </div>
            </div>
          </form>
        </div>
        <div class="block-footer">
          <small class="text-muted">Consejo: sé específico para recibir una respuesta más útil.</small>
        </div>
      </section>

      <!-- Mensajes -->
      <section id="inbox" class="block inbox mobile-pane">
        <div class="block-header"><i class="bi bi-inbox-fill me-1"></i> Mensajes del Administrador</div>
        <div class="block-body">
          <?php if ($comentarios_admin->num_rows > 0): ?>
            <div class="vstack gap-3">
              <?php while ($c = $comentarios_admin->fetch_assoc()): ?>
                <div class="msg">
                  <div class="msg-header">
                    <div class="msg-from"><i class="bi bi-person-circle"></i> Administrador</div>
                    <div class="msg-time"><i class="bi bi-clock"></i> <?= date("d/m/Y H:i", strtotime($c['fecha_envio'])) ?></div>
                  </div>
                  <div class="msg-text"><?= nl2br(htmlspecialchars($c['mensaje'])) ?></div>
                </div>
              <?php endwhile; ?>
            </div>
          <?php else: ?>
            <div class="text-center p-4 border rounded-3" style="border-color:var(--border); background:#f9fbff;">
              <div class="mb-1" style="font-size:1.05rem">No hay comentarios registrados aún.</div>
              <div class="text-muted">Cuando el administrador te responda, aparecerá aquí.</div>
            </div>
          <?php endif; ?>
        </div>
      </section>
    </div>
  </div>

  <script>
    // Contador + auto-resize + atajo Ctrl/Cmd+Enter
    const ta = document.getElementById('mensaje');
    const cc = document.getElementById('cc');
    const btn = document.getElementById('btnSend');

    function update() {
      const len = ta.value.length;
      cc.textContent = len;
      ta.style.height = 'auto';
      ta.style.height = Math.min(360, ta.scrollHeight) + 'px';
      btn.disabled = (len === 0);
    }
    if (ta) {
      ta.addEventListener('input', update);
      window.addEventListener('load', update);
      ta.addEventListener('keydown', e => {
        if ((e.ctrlKey || e.metaKey) && e.key === 'Enter' && !btn.disabled) {
          e.preventDefault();
          btn.click();
        }
      });
    }

    // Tabs móviles
    const segBtns = document.querySelectorAll('.seg button');
    segBtns.forEach(b=>{
      b.addEventListener('click', ()=>{
        segBtns.forEach(i=>i.classList.remove('active'));
        b.classList.add('active');
        document.querySelectorAll('.mobile-pane').forEach(p=>p.classList.remove('show'));
        document.getElementById(b.dataset.show).classList.add('show');
      });
    });
  </script>
</body>
</html>
<?php
$list->close();
$conexion->close();
?>
