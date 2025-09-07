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

/* ===== Mensajes (según tu tabla actual) ===== */
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
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  <style>
    :root{
      --bg:#f8fafc; --panel:#ffffff; --text:#0f172a; --muted:#667085; --border:#e6e9f2;
      --brand:#7c3aed; --brand2:#00d4ff; --ring:rgba(124,58,237,.22);
      --radius:14px; --radius-sm:10px; --shadow:0 10px 26px rgba(16,24,40,.06);
    }

    /* Base */
    *,*::before,*::after{ box-sizing:border-box; }
    body{
      overflow-x:hidden;
      background:
        radial-gradient(900px 520px at 110% -10%, rgba(124,58,237,.06), transparent 45%),
        var(--bg);
      color:var(--text);
      font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, sans-serif;
    }

    /* Header */
    .hero{ max-width:1200px; margin:28px auto 12px; padding:0 16px; }
    .hero-bar{
      display:flex; align-items:center; gap:12px;
      background:linear-gradient(180deg, rgba(255,255,255,.86), rgba(255,255,255,.96));
      border:1px solid var(--border); border-radius:16px; padding:14px 16px; box-shadow:var(--shadow);
    }
    .hero-icon{
      width:36px;height:36px;border-radius:12px;display:grid;place-items:center;color:#fff;
      background:linear-gradient(135deg,var(--brand),var(--brand2)); box-shadow:0 10px 24px rgba(124,58,237,.22);
    }
    .hero-title{ font-weight:800; font-size:1.2rem }

    /* Layout 2 bloques */
    .page{ max-width:1200px; margin:0 auto 40px; padding:0 16px }
    .blocks{ display:flex; flex-direction:column; gap:18px }
    @media (min-width:992px){ .blocks{ flex-direction:row } }

    .block{
      border:1px solid var(--border); border-radius:var(--radius); background:var(--panel);
      box-shadow:var(--shadow); overflow:hidden; display:flex; flex-direction:column; min-width:0;
    }
    .block-header{ padding:14px 18px; border-bottom:1px solid var(--border); font-weight:700 }
    .block-body{ padding:16px 18px }
    .block-footer{ padding:12px 18px; border-top:1px solid var(--border); background:#fafbff }

    .block.compose{ flex:1; }
    .block.inbox{ flex:1.25; }

    /* Formulario */
    .form-label{ font-weight:600 }
    .form-control{
      border:1px solid var(--border); border-radius:var(--radius-sm); transition:.2s;
    }
    .form-control:focus{ border-color:#d5d9e3; box-shadow:0 0 0 3px var(--ring); }
    textarea{ resize:none; }

    /* Botones */
    .btn{ border-radius:999px; font-weight:700; padding:.65rem 1.4rem; border:none; transition:all .25s ease;
          display:inline-flex; align-items:center; gap:.35rem; font-size:.95rem; }
    .btn-primary{ background:linear-gradient(135deg, var(--brand), var(--brand2)); color:#fff; box-shadow:0 4px 14px rgba(124,58,237,.25); }
    .btn-primary:hover{ filter:brightness(1.05); transform:translateY(-2px); box-shadow:0 6px 18px rgba(124,58,237,.35); }
    .btn:disabled{ opacity:.65; cursor:not-allowed; }

    .counter{ color:var(--muted); font-size:.9rem }

    /* === Tarjetas de mensajes (diseño ordenado) === */
    .msg{
      border:1px solid #e6ebff;
      background:linear-gradient(180deg,#f3f6ff,#ffffff);
      border-radius:14px;
      padding:14px 16px;
      max-width:100%;
      overflow:hidden;
      box-shadow:0 2px 6px rgba(16,24,40,.05);
    }
    .msg-header{
      display:flex;
      justify-content:space-between;
      align-items:center;
      gap:10px;
      margin-bottom:6px;
    }
    .msg-from{
      font-weight:700;
      color:#111827;
      display:flex; align-items:center; gap:6px;
    }
    .msg-time{
      color:#6b7280;
      font-size:.85rem;
      display:flex; align-items:center; gap:6px;
      white-space:nowrap;
    }
    .msg-text{
      margin-top:.25rem;
      color:#1f2937;
      font-size:.95rem;
      line-height:1.45;
      white-space:pre-wrap;      /* respeta saltos */
      overflow-wrap:anywhere;    /* parte palabras larguísimas */
      word-break:break-word;     /* respaldo */
    }

    /* Scroll interno opcional para la bandeja */
    .inbox .block-body{ max-height:65vh; overflow:auto; }
  </style>
</head>
<body>

  <!-- Header -->
  <div class="hero">
    <div class="hero-bar">
      <div class="hero-icon"><i class="bi bi-chat-dots-fill"></i></div>
      <div>
        <div class="hero-title">Comentarios a Administración</div>
        <div class="text-muted">Envía sugerencias o dudas y revisa las respuestas del administrador</div>
      </div>
      <div class="ms-auto">
        <a href="dashboard_empleado.php" class="btn btn-secondary" style="border:1px solid var(--border); background:#fff; color:var(--text);">
          <i class="bi bi-arrow-left"></i> Volver
        </a>
      </div>
    </div>
  </div>

  <div class="page">
    <div class="blocks">

      <!-- Bloque: Enviar -->
      <section class="block compose">
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
              <button id="btnSend" type="submit" class="btn btn-primary" disabled>
                <i class="bi bi-send-fill"></i> Enviar
              </button>
            </div>
          </form>
        </div>
        <div class="block-footer">
          <small class="text-muted">Consejo: sé específico para recibir una respuesta más útil.</small>
        </div>
      </section>

      <!-- Bloque: Mensajes -->
      <section class="block inbox">
        <div class="block-header"><i class="bi bi-inbox-fill me-1"></i> Mensajes del Administrador</div>
        <div class="block-body">
          <?php if ($comentarios_admin->num_rows > 0): ?>
            <div class="vstack gap-3">
              <?php while ($c = $comentarios_admin->fetch_assoc()): ?>
                <div class="msg">
                  <div class="msg-header">
                    <div class="msg-from">
                      <i class="bi bi-person-circle"></i>
                      Administrador
                    </div>
                    <div class="msg-time">
                      <i class="bi bi-clock"></i>
                      <?= date("d/m/Y H:i", strtotime($c['fecha_envio'])) ?>
                    </div>
                  </div>
                  <div class="msg-text"><?= nl2br(htmlspecialchars($c['mensaje'])) ?></div>
                </div>
              <?php endwhile; ?>
            </div>
          <?php else: ?>
            <div class="text-center p-4 border rounded-3" style="border-color:var(--border); background:#f9fbff;">
              <div class="mb-1" style="font-size:1.2rem">No hay comentarios registrados aún.</div>
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
  </script>
</body>
</html>
<?php
$list->close();
$conexion->close();
?>
