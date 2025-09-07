<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'admin') {
    header("Location: login.php");
    exit();
}

include("conexion.php");

if (!isset($_GET['id'])) {
    header("Location: ver_empleados.php");
    exit();
}

$id = intval($_GET['id']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nuevaContrasena     = $_POST['nueva_contrasena'] ?? '';
    $confirmarContrasena = $_POST['confirmar_contrasena'] ?? '';

    if ($nuevaContrasena !== $confirmarContrasena) {
        $error = "❌ Las contraseñas no coinciden.";
    } else {
        $hash = password_hash($nuevaContrasena, PASSWORD_DEFAULT);
        $sql  = "UPDATE usuarios SET contrasena = ? WHERE id_usuario = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("si", $hash, $id);

        if ($stmt->execute()) {
            $mensaje = "✅ Contraseña actualizada correctamente.";
        } else {
            $error = "❌ Error al actualizar la contraseña: " . $stmt->error;
        }
        $stmt->close();
    }
}

$sql  = "SELECT nombre_completo, correo FROM usuarios WHERE id_usuario = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Usuario no encontrado.";
    exit();
}

$usuario = $result->fetch_assoc();
$inicial = mb_strtoupper(mb_substr($usuario['nombre_completo'], 0, 1, 'UTF-8'), 'UTF-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Cambiar Contraseña - <?= htmlspecialchars($usuario['nombre_completo']) ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />

  <style>
    :root{
      --bg:#f8fafc;
      --panel:#ffffff;
      --text:#0f172a;
      --muted:#667085;
      --border:#e6e9f2;
      --brand:#7c3aed;               /* lila */
      --ring:rgba(124,58,237,.24);
      --success:#16a34a;
      --danger:#dc2626;
      --radius:14px;
      --radius-sm:12px;
      --shadow:0 2px 10px rgba(16,24,40,.06);
    }
    body{
      background:
        radial-gradient(900px 520px at 110% -10%, rgba(124,58,237,.06), transparent 45%),
        var(--bg);
      color:var(--text);
      font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, sans-serif;
    }

    .page{
      max-width:1100px;
      margin:40px auto;
      padding:0 16px;
    }
    .title{
      display:flex; align-items:center; gap:.6rem;
      font-weight:700; font-size:1.35rem; margin-bottom:16px;
    }
    .title .dot{
      width:10px; height:10px; border-radius:999px; background:var(--brand);
      box-shadow:0 0 0 6px rgba(124,58,237,.12);
    }

    /* ===== Layout: 2 bloques en horizontal ===== */
    .blocks{
      display:flex; flex-direction:column; gap:18px;
    }
    @media (min-width: 992px){
      .blocks{ flex-direction:row }
    }

    .block{
      background:var(--panel);
      border:1px solid var(--border);
      border-radius:var(--radius);
      box-shadow:var(--shadow);
      flex:1; display:flex; flex-direction:column; overflow:hidden;
    }
    .block-header{
      padding:14px 18px; border-bottom:1px solid var(--border); background:#fff;
      font-weight:600;
    }
    .block-body{ padding:18px }
    .block-footer{ padding:14px 18px; border-top:1px solid var(--border); background:#fafbff }

    /* ===== Perfil / Resumen ===== */
    .avatar{
      width:64px; height:64px; border-radius:999px; display:grid; place-items:center;
      font-weight:800; font-size:1.3rem; color:#fff; user-select:none;
      background:linear-gradient(135deg, var(--brand), #00d4ff);
      box-shadow:0 8px 20px rgba(124,58,237,.18);
    }
    .meta{ color:var(--muted) }

    /* ===== Form ===== */
    .form-label{ font-weight:600 }
    .form-control{
      border:1px solid var(--border); border-radius:var(--radius-sm);
      padding:10px 12px; transition:border .2s, box-shadow .2s;
    }
    .form-control:focus{
      border-color:#d4d8f0; box-shadow:0 0 0 3px var(--ring);
    }
    .input-with-icon{ position:relative }
    .input-with-icon .toggle{
      position:absolute; right:10px; top:50%; transform:translateY(-50%);
      border:none; background:transparent; color:#6b7280; cursor:pointer;
    }

    /* ===== Strength meter ===== */
    .strength{
      height:8px; border-radius:999px; background:#eef2f7; overflow:hidden;
    }
    .strength > span{
      display:block; height:100%; width:0%;
      background:linear-gradient(90deg, #ff6b6b, #ffd166, #6ee7b7);
      transition:width .25s;
    }
    .hint{ color:var(--muted); font-size:.92rem }

    /* ===== Alerts ===== */
    .alert-soft.success{
      border:1px solid rgba(22,163,74,.22);
      background:rgba(22,163,74,.08); color:#166534; border-radius:12px;
    }
    .alert-soft.error{
      border:1px solid rgba(220,38,38,.22);
      background:rgba(220,38,38,.08); color:#7f1d1d; border-radius:12px;
    }

    /* Botones */
    .btn{ border-radius:var(--radius-sm); font-weight:600; }
    .btn-primary{ background:var(--brand); border-color:var(--brand) }
    .btn-primary:hover{ filter:brightness(1.04) }
    .btn-secondary{ background:#fff; color:var(--text); border:1px solid var(--border) }
    .btn-secondary:hover{ background:#f5f6fb }
  </style>
</head>
<body>
  <div class="page">
    <div class="title"><span class="dot"></span> Cambiar contraseña</div>

    <div class="blocks">
      <!-- ===== Bloque 1: Perfil / Resumen ===== -->
      <section class="block" style="max-width:420px">
        <div class="block-header">Empleado</div>
        <div class="block-body">
          <div class="d-flex align-items-center gap-3">
            <div class="avatar"><?= htmlspecialchars($inicial) ?></div>
            <div>
              <div class="fw-bold"><?= htmlspecialchars($usuario['nombre_completo']) ?></div>
              <?php if(!empty($usuario['correo'])): ?>
                <div class="meta"><?= htmlspecialchars($usuario['correo']) ?></div>
              <?php endif; ?>
              <div class="meta">ID: <?= (int)$id ?></div>
            </div>
          </div>

          <hr class="my-4">
          <div class="hint">
            Crea una contraseña segura: al menos 8 caracteres, mezcla de mayúsculas, minúsculas, números y símbolos.
          </div>
        </div>
        <div class="block-footer">
          <a href="ver_empleados.php" class="btn btn-secondary">
            ⬅️ Volver a la lista
          </a>
        </div>
      </section>

      <!-- ===== Bloque 2: Formulario ===== -->
      <section class="block">
        <div class="block-header">Actualizar contraseña</div>
        <div class="block-body">
          <?php if (isset($error)): ?>
            <div class="alert-soft error mb-3"><?= htmlspecialchars($error) ?></div>
          <?php endif; ?>

          <?php if (isset($mensaje)): ?>
            <div class="alert-soft success mb-3"><?= htmlspecialchars($mensaje) ?></div>
          <?php endif; ?>

          <form method="POST" id="formPwd" novalidate>
            <div class="mb-3">
              <label for="nueva_contrasena" class="form-label">Nueva Contraseña</label>
              <div class="input-with-icon">
                <input type="password" class="form-control" id="nueva_contrasena" name="nueva_contrasena" required>
                <button type="button" class="toggle" data-target="nueva_contrasena" aria-label="Mostrar/Ocultar">
                  👁
                </button>
              </div>
              <div class="strength mt-2"><span id="meter"></span></div>
            </div>

            <div class="mb-3">
              <label for="confirmar_contrasena" class="form-label">Confirmar Contraseña</label>
              <div class="input-with-icon">
                <input type="password" class="form-control" id="confirmar_contrasena" name="confirmar_contrasena" required>
                <button type="button" class="toggle" data-target="confirmar_contrasena" aria-label="Mostrar/Ocultar">
                  👁
                </button>
              </div>
              <small id="matchHint" class="hint"></small>
            </div>

            <div class="d-flex justify-content-end gap-2">
              <a href="ver_empleados.php" class="btn btn-secondary">Cancelar</a>
              <button type="submit" class="btn btn-primary">Guardar Cambios</button>
            </div>
          </form>
        </div>
      </section>
    </div>
  </div>

  <script>
    // Mostrar/ocultar contraseña
    document.querySelectorAll('.toggle').forEach(btn=>{
      btn.addEventListener('click', ()=>{
        const id = btn.getAttribute('data-target');
        const input = document.getElementById(id);
        input.type = input.type === 'password' ? 'text' : 'password';
      });
    });

    // Medidor de fortaleza + coincidencia
    const pwd = document.getElementById('nueva_contrasena');
    const confirm = document.getElementById('confirmar_contrasena');
    const meter = document.getElementById('meter');
    const matchHint = document.getElementById('matchHint');

    function scorePassword(v){
      let s = 0;
      if (!v) return 0;
      // Longitud
      s += Math.min(10, v.length) * 6;
      // Diversidad de tipos
      if (/[a-z]/.test(v)) s += 10;
      if (/[A-Z]/.test(v)) s += 10;
      if (/\d/.test(v))   s += 10;
      if (/[^A-Za-z0-9]/.test(v)) s += 14;
      // Penalización por repeticiones
      if (/([A-Za-z0-9])\1\1/.test(v)) s -= 10;
      return Math.max(0, Math.min(100, s));
    }

    function updateUI(){
      const val = pwd.value;
      const sc  = scorePassword(val);
      meter.style.width = sc + '%';

      if (confirm.value.length){
        const ok = val === confirm.value;
        matchHint.textContent = ok ? '✔ Coinciden' : '✖ No coinciden';
        matchHint.style.color = ok ? '#166534' : '#b91c1c';
      } else {
        matchHint.textContent = '';
      }
    }

    pwd.addEventListener('input', updateUI);
    confirm.addEventListener('input', updateUI);
  </script>
</body>
</html>
<?php
$stmt->close();
$conexion->close();
?>
