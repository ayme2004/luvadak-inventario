<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}
require_once "conexion.php";

$usuarioSesion = $_SESSION['usuario'];

/* ====== Leer perfil (prepared) ====== */
$sql = $conexion->prepare("
    SELECT id_usuario, nombre_completo, correo, usuario, rol, estado, fecha_registro, foto_url
    FROM usuarios
    WHERE usuario = ?
    LIMIT 1
");
$sql->bind_param("s", $usuarioSesion);
$sql->execute();
$perfil = $sql->get_result()->fetch_assoc();
$sql->close();

if (!$perfil) { die("Usuario no encontrado."); }

/* Guarda rol en sesión por si lo necesitan otros paneles */
$_SESSION['rol'] = $perfil['rol'] ?? ($_SESSION['rol'] ?? '');

/* Helpers */
function is_local_path($p){ return $p && str_starts_with($p, 'uploads/'); }

/* ====== CSRF ====== */
if (empty($_SESSION['csrf'])) {
  $_SESSION['csrf'] = bin2hex(random_bytes(32));
}
$csrf = $_SESSION['csrf'];

/* ====== Acciones (PRG) ====== */
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $token = $_POST['csrf'] ?? '';

    // Flash + redirect
    $flash = function(string $tipo, string $msg){
        $_SESSION['flash_tipo'] = $tipo;
        $_SESSION['flash_msg']  = $msg;
    };

    if (!hash_equals($_SESSION['csrf'], $token)) {
        http_response_code(400);
        $flash('danger', 'Token inválido. Recarga la página.');
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }

    $accion = $_POST['accion'] ?? '';

    /* Borrar foto */
    if ($accion === 'borrar') {
        if (!empty($perfil['foto_url']) && is_local_path($perfil['foto_url'])) {
            $fisico = __DIR__ . "/" . $perfil['foto_url'];
            if (file_exists($fisico)) @unlink($fisico);
        }
        $upd = $conexion->prepare("UPDATE usuarios SET foto_url = NULL WHERE id_usuario = ?");
        $upd->bind_param("i", $perfil['id_usuario']);
        $upd->execute();
        $upd->close();

        $flash('warning', 'Se eliminó tu foto de perfil.');
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }

    /* Subir/guardar foto */
    if ($accion === 'guardar') {
        if (!empty($_FILES['foto']['name'])) {
            $file = $_FILES['foto'];
            $permitidos = ['image/jpeg', 'image/png', 'image/webp'];

            if ($file['error'] === UPLOAD_ERR_OK) {
                if (!in_array($file['type'], $permitidos)) {
                    $flash('danger', 'Formato no válido. Solo JPG, PNG o WEBP.');
                } elseif ($file['size'] > 2 * 1024 * 1024) {
                    $flash('danger', 'La imagen supera 2MB.');
                } else {
                    $dir = __DIR__ . "/uploads/fotos";
                    if (!is_dir($dir)) { @mkdir($dir, 0775, true); }

                    // Borrar la anterior si era local
                    if (!empty($perfil['foto_url']) && is_local_path($perfil['foto_url'])) {
                        $ant = __DIR__ . "/" . $perfil['foto_url'];
                        if (file_exists($ant)) @unlink($ant);
                    }

                    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                    $seguro = "foto_{$perfil['id_usuario']}_" . time() . "." . $ext;
                    $dest = $dir . "/" . $seguro;

                    if (move_uploaded_file($file['tmp_name'], $dest)) {
                        $foto_url = "uploads/fotos/" . $seguro;
                        $upd = $conexion->prepare("UPDATE usuarios SET foto_url = ? WHERE id_usuario = ?");
                        $upd->bind_param("si", $foto_url, $perfil['id_usuario']);
                        $upd->execute();
                        $upd->close();

                        $flash('success', 'Foto actualizada correctamente ✅');
                    } else {
                        $flash('danger', 'No se pudo guardar la imagen.');
                    }
                }
            } else {
                $flash('danger', 'Error al subir la imagen.');
            }
        } else {
            // No seleccionaron archivo, solo damos feedback
            $flash('info', 'No seleccionaste ninguna imagen.');
        }

        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }

    // Sin acción válida
    $flash('info', 'Acción no reconocida.');
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

/* ====== Flash messages en GET ====== */
$mensaje = "";
$tipoMsg = "info";
if (!empty($_SESSION['flash_msg'])) {
    $mensaje = $_SESSION['flash_msg'];
    $tipoMsg = $_SESSION['flash_tipo'] ?? 'info';
    unset($_SESSION['flash_msg'], $_SESSION['flash_tipo']);
}

/* ====== Fuente de foto ====== */
$src = (!empty($perfil['foto_url']) && file_exists(__DIR__ . "/" . $perfil['foto_url']))
    ? $perfil['foto_url']
    : "https://api.dicebear.com/7.x/initials/svg?seed=" . urlencode($perfil['usuario']);

/* Badges */
$rolBadge = ucfirst($perfil['rol']);
$estadoBadge = ucfirst($perfil['estado']);

/* ====== Dashboard por rol ====== */
$rolLower = strtolower($perfil['rol']);
$DASHBOARD_URL = ($rolLower === 'admin') ? 'dashboard_admin.php' : 'dashboard_empleado.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Mi Perfil</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">

<script>
  // Sincroniza tema con el resto del sistema
  (function() {
    const saved = localStorage.getItem('theme') || 'dark';
    if(saved === 'light') document.documentElement.classList.add('light');
  })();
</script>

<style>
:root{
  --bg:#f8fafc; --panel:#ffffff; --card:#ffffff; --text:#0f172a; --muted:#667085; --border:#e6e9f2;
  --brand:#7c3aed; --brand-2:#00d4ff; --ring:rgba(124,58,237,.22);
  --radius:14px; --radius-lg:18px; --shadow:0 2px 12px rgba(16,24,40,.08);
}

/* ===== Base ===== */
html,body{height:100%}
body{
  font-family:'Inter',system-ui,-apple-system,Segoe UI,Roboto,sans-serif;
  background:
    radial-gradient(1000px 520px at -10% -10%, rgba(124,58,237,.10), transparent 45%),
    radial-gradient(900px 480px at 110% 0%, rgba(0,212,255,.10), transparent 45%),
    var(--bg);
  color:var(--text);
}
.page{ max-width:1200px; margin:34px auto; padding:0 16px }

/* ===== Hero ===== */
.hero{
  display:flex; align-items:center; gap:12px; flex-wrap:wrap;
  background:linear-gradient(180deg, rgba(255,255,255,.92), rgba(255,255,255,.98));
  border:1px solid var(--border); border-radius:var(--radius-lg);
  padding:14px 16px; box-shadow:var(--shadow); margin-bottom:16px;
}
.hero .icon{
  width:38px;height:38px;border-radius:12px;display:grid;place-items:center;color:#fff;
  background:linear-gradient(135deg,var(--brand),var(--brand-2));
  box-shadow:0 10px 24px rgba(124,58,237,.22);
}
.hero h1{ margin:0; font-weight:800; font-size:1.15rem }
.hero .sub{ color:var(--muted) }
.hero .badges{ margin-left:auto; display:flex; gap:8px; flex-wrap:wrap }

/* ===== Layout ===== */
.blocks{ display:flex; flex-direction:column; gap:18px }
@media (min-width: 992px){ .blocks{ flex-direction:row } }

.block{
  border:1px solid var(--border); background:var(--panel);
  border-radius:var(--radius-lg); box-shadow:var(--shadow);
  overflow:hidden; display:flex; flex-direction:column;
}
.block-header{ padding:14px 16px; border-bottom:1px solid var(--border); font-weight:800 }
.block-body{ padding:16px 16px }
.block-footer{ padding:12px 16px; border-top:1px solid var(--border); background:#fafbff }

.block.left{ flex:1; max-width:380px }
.block.right{ flex:1.6 }

/* ===== Tarjeta usuario ===== */
.profile-card .avatar{
  width:110px; height:110px; object-fit:cover; border-radius:999px; border:2px solid #e6e9f2;
  box-shadow:0 8px 24px rgba(2,6,23,.08)
}
.badge-soft{
  display:inline-flex; align-items:center; gap:6px; padding:6px 10px; border-radius:999px;
  border:1px solid var(--border); background:#f6f7fb; color:#0f172a; font-weight:700; font-size:.82rem;
}

/* ===== Info grid ===== */
.info .card{ background:#fff; border:1px solid var(--border); border-radius:12px }
.info .label{ color:var(--muted); font-weight:600; font-size:.85rem; margin-bottom:4px }
.info .value{ font-weight:800 }

/* ===== Uploader ===== */
.drop{
  border:2px dashed #e7eaf6; border-radius:14px; padding:22px; text-align:center;
  background:#ffffff; transition:.15s; cursor:pointer;
}
.drop:hover{ background:#fafbff }
.drop.drag{ outline:4px solid var(--ring); background:rgba(124,58,237,.06) }
.drop i{
  font-size:1.8rem; margin-bottom:8px; display:block; opacity:.9;
  background:linear-gradient(135deg, var(--brand), var(--brand-2));
  -webkit-background-clip:text; background-clip:text; color:transparent;
}
.hint{ color:var(--muted) }

/* ===== Botones ===== */
.btn{ border-radius:999px; font-weight:700; padding:.6rem 1rem; border:1px solid var(--border) }
.btn-primary{
  background:linear-gradient(135deg,var(--brand),var(--brand-2));
  border-color:transparent; color:#fff; box-shadow:0 6px 16px rgba(124,58,237,.22);
}
.btn-primary:hover{ filter:brightness(1.04); transform:translateY(-2px) }
.btn-outline{ background:#fff; color:#0f172a }
.btn-outline:hover{ background:#f9f9ff; transform:translateY(-2px) }
.btn-danger-modern{
  background: linear-gradient(135deg, #ef4444, #f97316);
  color:#fff; border: none; box-shadow: 0 6px 16px rgba(239,68,68,.22);
}
.btn-danger-modern:hover{ filter:brightness(1.05); transform:translateY(-2px) }

/* ===== Responsive extras ===== */
.buttons-row{ display:flex; flex-wrap:wrap; gap:10px }
@media (max-width: 991.98px){
  .block.left{ max-width:100% }
  .block.right{ flex:1 }
}
@media (max-width: 767.98px){
  .page{ margin:22px auto }
  .hero h1{ font-size:1.05rem }
  .hero .badges{ width:100%; margin-left:0 }
  .profile-card .avatar{ width:90px; height:90px }
  .drop{ padding:16px }
  .hint{ font-size:.9rem }
  /* Botones ocupan todo el ancho en móvil */
  .buttons-row .btn{ flex:1 1 100% }
}
@media (min-width: 768px) and (max-width: 991.98px){
  /* En tablet: dos columnas de botones */
  .buttons-row .btn{ flex:1 1 calc(50% - 10px) }
}
</style>
</head>
<body>

<div class="page">
  <!-- Hero -->
  <div class="hero">
    <div class="icon"><i class="fa-solid fa-user"></i></div>
    <div>
      <h1>Mi Perfil</h1>
      <div class="sub">Gestiona tu información y foto de perfil</div>
    </div>
    <div class="badges">
      <span class="badge-soft"><i class="fa-solid fa-id-badge"></i> ID <?= (int)$perfil['id_usuario'] ?></span>
      <span class="badge-soft"><i class="fa-solid fa-user-shield"></i> <?= htmlspecialchars(ucfirst($perfil['rol'])) ?></span>
      <span class="badge-soft"><i class="fa-solid fa-circle-dot"></i> <?= htmlspecialchars(ucfirst($perfil['estado'])) ?></span>
    </div>
  </div>

  <!-- 2 Bloques -->
  <div class="blocks">
    <!-- Izquierda: tarjeta usuario -->
    <section class="block left">
      <div class="block-header">Tu perfil</div>
      <div class="block-body profile-card">
        <div class="text-center mb-3">
          <img class="avatar" id="avatarPreview" src="<?= htmlspecialchars($src) ?>" alt="Foto de perfil">
        </div>
        <h5 class="text-center fw-800 mb-1"><?= htmlspecialchars($perfil['nombre_completo']) ?></h5>
        <p class="text-center text-muted mb-3">@<?= htmlspecialchars($perfil['usuario']) ?></p>

        <div class="vstack gap-2 info">
          <div class="card p-3">
            <div class="label">Correo</div>
            <div class="value"><?= htmlspecialchars($perfil['correo']) ?></div>
          </div>
          <div class="card p-3">
            <div class="label">Registro</div>
            <div class="value"><?= date('d/m/Y', strtotime($perfil['fecha_registro'])) ?></div>
          </div>
        </div>

        <?php if(!empty($mensaje)): ?>
          <div class="alert alert-<?= $tipoMsg ?> mt-3 mb-0"><?= $mensaje ?></div>
        <?php endif; ?>
      </div>
    </section>

    <!-- Derecha: detalles + uploader + botones -->
    <section class="block right">
      <div class="block-header">Actualizar foto</div>
      <div class="block-body">
        <form method="post" enctype="multipart/form-data" id="formFoto" class="vstack gap-3">
          <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">

          <div id="drop" class="drop">
            <i class="fa-solid fa-cloud-arrow-up"></i>
            <div class="fw-semibold">Arrastra tu imagen aquí o haz clic</div>
            <div class="hint">Formatos: JPG, PNG o WEBP · Máx 2MB</div>
            <input type="file" name="foto" id="fileInput" accept="image/*" class="d-none">
          </div>

          <!-- Botonera responsive -->
          <div class="buttons-row">
            <button type="submit" name="accion" value="guardar" class="btn btn-primary">
              <i class="fa-regular fa-floppy-disk me-1"></i> Guardar
            </button>

            <button type="submit" name="accion" value="borrar" class="btn btn-danger-modern">
              <i class="fa-regular fa-trash-can me-1"></i> Borrar foto
            </button>

            <a href="<?= htmlspecialchars($DASHBOARD_URL) ?>" class="btn btn-outline">
              <i class="fa-solid fa-arrow-left-long me-1"></i> Volver
            </a>

            <a href="logout.php" class="btn btn-outline">
              <i class="fas fa-sign-out-alt me-1"></i> Cerrar sesión
            </a>
          </div>
        </form>

        <hr class="my-4">

        <div class="info">
          <div class="row g-3">
            <div class="col-md-6">
              <div class="card p-3">
                <div class="label">Usuario</div>
                <div class="value"><?= htmlspecialchars($perfil['usuario']) ?></div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="card p-3">
                <div class="label">Rol · Estado</div>
                <div class="value"><?= htmlspecialchars($rolBadge) ?> · <?= htmlspecialchars($estadoBadge) ?></div>
              </div>
            </div>
          </div>
        </div>

      </div>
      <div class="block-footer">
        <small class="text-muted">Consejo: usa imágenes cuadradas para mejores resultados.</small>
      </div>
    </section>
  </div>
</div>

<script>
// Dropzone + preview
const drop = document.getElementById('drop');
const fileInput = document.getElementById('fileInput');
const avatarPreview = document.getElementById('avatarPreview');

['dragenter','dragover'].forEach(evt =>
  drop.addEventListener(evt, e => { e.preventDefault(); e.stopPropagation(); drop.classList.add('drag'); })
);
['dragleave','drop'].forEach(evt =>
  drop.addEventListener(evt, e => { e.preventDefault(); e.stopPropagation(); drop.classList.remove('drag'); })
);
drop.addEventListener('click', ()=> fileInput.click());
drop.addEventListener('drop', e => {
  const files = e.dataTransfer.files;
  if(files && files[0]) setPreview(files[0]);
  fileInput.files = files;
});
fileInput.addEventListener('change', e => {
  if(e.target.files && e.target.files[0]) setPreview(e.target.files[0]);
});

function setPreview(file){
  const ok = ['image/jpeg','image/png','image/webp'].includes(file.type);
  const small = file.size <= 2*1024*1024;
  if(!ok){ alert('Formato no válido (JPG, PNG, WEBP).'); return; }
  if(!small){ alert('La imagen supera 2MB.'); return; }
  const reader = new FileReader();
  reader.onload = e => { avatarPreview.src = e.target.result; };
  reader.readAsDataURL(file);
}
</script>
</body>
</html>
