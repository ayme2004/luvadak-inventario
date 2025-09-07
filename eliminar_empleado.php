<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'admin') {
  header("Location: login.php");
  exit();
}

include("conexion.php");

/* ========= Validar ID ========= */
if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
  header("Location: ver_empleados.php");
  exit();
}
$id = (int)$_GET['id'];

/* ========= Obtener empleado para mostrar confirmación ========= */
$sql = "SELECT id_usuario, nombre_completo, correo, usuario, rol FROM usuarios WHERE id_usuario = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows === 0) {
  header("Location: ver_empleados.php?msg=no_encontrado");
  exit();
}
$emp = $res->fetch_assoc();
$stmt->close();

/* ========= CSRF ========= */
if (empty($_SESSION['csrf'])) {
  $_SESSION['csrf'] = bin2hex(random_bytes(32));
}
$csrf = $_SESSION['csrf'];

/* ========= POST: eliminar ========= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $token = $_POST['csrf'] ?? '';
  if (!hash_equals($_SESSION['csrf'], $token)) {
    http_response_code(400);
    echo "Token inválido.";
    exit();
  }

  $del = $conexion->prepare("DELETE FROM usuarios WHERE id_usuario = ?");
  $del->bind_param("i", $id);
  if ($del->execute()) {
    $del->close();
    unset($_SESSION['csrf']);
    header("Location: ver_empleados.php?msg=empleado_eliminado");
    exit();
  } else {
    $error = "Error al eliminar: " . $del->error;
    $del->close();
  }
}

/* Inicial para avatar */
$inicial = mb_strtoupper(mb_substr($emp['nombre_completo'], 0, 1, 'UTF-8'), 'UTF-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Eliminar Empleado - <?= htmlspecialchars($emp['nombre_completo']) ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    :root{
      --bg:#f8fafc; --panel:#ffffff; --text:#0f172a; --muted:#667085; --border:#e6e9f2;
      --brand:#7c3aed; --ring:rgba(124,58,237,.24);
      --danger:#dc2626; --danger-2:#b91c1c; --warn:#f59e0b;
      --radius:14px; --radius-sm:12px; --shadow:0 2px 10px rgba(16,24,40,.06);
    }
    body{
      background: radial-gradient(900px 520px at 110% -10%, rgba(124,58,237,.06), transparent 45%), var(--bg);
      color:var(--text); font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, sans-serif;
    }
    .page{ max-width:1100px; margin:38px auto; padding:0 16px }
    .title{ display:flex; align-items:center; gap:.6rem; font-weight:700; font-size:1.35rem; margin-bottom:16px }
    .title .dot{ width:10px; height:10px; border-radius:999px; background:var(--brand); box-shadow:0 0 0 6px rgba(124,58,237,.12) }

    .blocks{ display:flex; flex-direction:column; gap:18px }
    @media (min-width:992px){ .blocks{ flex-direction:row } }

    .block{
      background:var(--panel); border:1px solid var(--border); border-radius:var(--radius);
      box-shadow:var(--shadow); overflow:hidden; display:flex; flex-direction:column;
    }
    .block-header{ padding:14px 18px; border-bottom:1px solid var(--border); background:#fff; font-weight:600 }
    .block-body{ padding:18px }
    .block-footer{ padding:14px 18px; border-top:1px solid var(--border); background:#fafbff }

    .block.profile{ flex:1; max-width:420px }
    .block.confirm{ flex:1.4 }

    .avatar{
      width:66px; height:66px; border-radius:999px; display:grid; place-items:center;
      font-weight:800; font-size:1.3rem; color:#fff; user-select:none;
      background:linear-gradient(135deg, var(--brand), #00d4ff);
      box-shadow:0 8px 20px rgba(124,58,237,.18);
    }
    .meta{ color:var(--muted) }
    .badge-role{
      display:inline-block; padding:4px 10px; border-radius:999px; font-weight:700; font-size:.78rem;
      background:#f4f2ff; color:#3f2ab5; border:1px solid #e8e5ff;
    }

    .alert-soft{
      border-radius:12px; padding:14px 16px; border:1px solid #fde68a; background:#fffbeb; color:#92400e;
    }
    .alert-danger-soft{
      border-radius:12px; padding:14px 16px; border:1px solid rgba(220,38,38,.22);
      background:rgba(220,38,38,.08); color:#7f1d1d;
    }

    .confirm-list{ margin:0; padding-left:18px; color:#1f2937 }
    .confirm-list li{ margin-bottom:6px }

    .btn{ border-radius:var(--radius-sm); font-weight:600 }
    .btn-danger{ background:var(--danger); border-color:var(--danger) }
    .btn-danger:hover{ background:var(--danger-2); border-color:var(--danger-2) }
    .btn-secondary{ background:#fff; color:var(--text); border:1px solid var(--border) }
    .btn-secondary:hover{ background:#f5f6fb }
  </style>
</head>
<body>
  <div class="page">
    <div class="title"><span class="dot"></span> Eliminar empleado</div>

    <div class="blocks">
      <!-- Bloque 1: Perfil -->
      <section class="block profile">
        <div class="block-header">Perfil</div>
        <div class="block-body">
          <div class="d-flex align-items-center gap-3">
            <div class="avatar"><?= htmlspecialchars($inicial) ?></div>
            <div>
              <div class="fw-bold"><?= htmlspecialchars($emp['nombre_completo']) ?></div>
              <?php if(!empty($emp['correo'])): ?>
                <div class="meta"><?= htmlspecialchars($emp['correo']) ?></div>
              <?php endif; ?>
              <div class="mt-2">
                <span class="badge-role"><?= $emp['rol']==='admin' ? 'Administrador' : 'Empleado' ?></span>
              </div>
            </div>
          </div>
          <hr class="my-4">
          <div class="meta">
            ID: <?= (int)$emp['id_usuario'] ?><br>
            Usuario: <strong><?= htmlspecialchars($emp['usuario']) ?></strong>
          </div>
        </div>
        <div class="block-footer">
          <a href="ver_empleados.php" class="btn btn-secondary">⬅️ Volver</a>
        </div>
      </section>

      <!-- Bloque 2: Confirmación -->
      <section class="block confirm">
        <div class="block-header">Confirmar eliminación</div>
        <div class="block-body">
          <?php if(isset($error)): ?>
            <div class="alert-danger-soft mb-3"><?= htmlspecialchars($error) ?></div>
          <?php endif; ?>

          <div class="alert-soft mb-3">
            <strong>⚠ Acción irreversible.</strong> Esta operación eliminará al empleado del sistema.
          </div>

          <ul class="confirm-list">
            <li>El usuario perderá acceso al panel.</li>
            <li>Los registros históricos vinculados podrían quedar huérfanos si tu base no usa cascada lógica.</li>
            <li>Considera desactivar en lugar de eliminar si necesitas conservar trazabilidad.</li>
          </ul>

          <form method="POST" class="mt-4">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
            <div class="d-flex flex-wrap gap-2 justify-content-end">
              <a href="ver_empleados.php" class="btn btn-secondary">Cancelar</a>
              <button type="submit" class="btn btn-danger">Eliminar definitivamente</button>
            </div>
          </form>
        </div>
      </section>
    </div>
  </div>
</body>
</html>
