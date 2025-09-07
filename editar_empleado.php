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

$sql = "SELECT id_usuario, nombre_completo, correo, usuario, rol FROM usuarios WHERE id_usuario = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Empleado no encontrado.";
    exit();
}

$empleado = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre  = $_POST['nombre_completo'] ?? '';
    $correo  = $_POST['correo'] ?? '';
    $usuario = $_POST['usuario'] ?? '';
    $rol     = $_POST['rol'] ?? 'empleado';

    $updateSql = "UPDATE usuarios SET nombre_completo = ?, correo = ?, usuario = ?, rol = ? WHERE id_usuario = ?";
    $stmtUpdate = $conexion->prepare($updateSql);
    $stmtUpdate->bind_param("ssssi", $nombre, $correo, $usuario, $rol, $id);

    if ($stmtUpdate->execute()) {
        header("Location: ver_empleados.php?msg=empleado_actualizado");
        exit();
    } else {
        echo "Error al actualizar: " . $stmtUpdate->error;
    }
}
$inicial = mb_strtoupper(mb_substr($empleado['nombre_completo'], 0, 1, 'UTF-8'), 'UTF-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Editar Empleado - Luvadak</title>
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
      margin:38px auto;
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

    /* ===== Layout 2 bloques ===== */
    .blocks{ display:flex; flex-direction:column; gap:18px; }
    @media (min-width:992px){ .blocks{ flex-direction:row } }

    .block{
      background:var(--panel);
      border:1px solid var(--border);
      border-radius:var(--radius);
      box-shadow:var(--shadow);
      overflow:hidden;
      display:flex; flex-direction:column;
    }
    .block-header{
      padding:14px 18px; border-bottom:1px solid var(--border); background:#fff;
      font-weight:600;
    }
    .block-body{ padding:18px }
    .block-footer{ padding:14px 18px; border-top:1px solid var(--border); background:#fafbff }

    /* Col anchos */
    .block.profile{ flex:1; max-width:420px }
    .block.form{ flex:1.4 }

    /* Perfil */
    .avatar{
      width:66px; height:66px; border-radius:999px; display:grid; place-items:center;
      font-weight:800; font-size:1.3rem; color:#fff; user-select:none;
      background:linear-gradient(135deg, var(--brand), #00d4ff);
      box-shadow:0 8px 20px rgba(124,58,237,.18);
    }
    .meta{ color:var(--muted) }
    .badge-role{
      display:inline-block; padding:4px 10px; border-radius:999px;
      font-weight:700; font-size:.78rem; background:#f4f2ff; color:#3f2ab5; border:1px solid #e8e5ff;
    }

    /* Form */
    .form-label{ font-weight:600 }
    .form-control, .form-select{
      border:1px solid var(--border); border-radius:var(--radius-sm);
      padding:10px 12px; transition:border .2s, box-shadow .2s;
    }
    .form-control:focus, .form-select:focus{
      border-color:#d4d8f0; box-shadow:0 0 0 3px var(--ring);
    }

    /* Botones */
    .btn{ border-radius:var(--radius-sm); font-weight:600 }
    .btn-primary{ background:var(--brand); border-color:var(--brand) }
    .btn-primary:hover{ filter:brightness(1.04) }
    .btn-secondary{ background:#fff; color:var(--text); border:1px solid var(--border) }
    .btn-secondary:hover{ background:#f5f6fb }
  </style>
</head>
<body>
  <div class="page">
    <div class="title"><span class="dot"></span> Editar empleado</div>

    <div class="blocks">
      <!-- ===== Bloque 1: Perfil / Resumen ===== -->
      <section class="block profile">
        <div class="block-header">Perfil</div>
        <div class="block-body">
          <div class="d-flex align-items-center gap-3">
            <div class="avatar"><?= htmlspecialchars($inicial) ?></div>
            <div>
              <div class="fw-bold"><?= htmlspecialchars($empleado['nombre_completo']) ?></div>
              <?php if(!empty($empleado['correo'])): ?>
                <div class="meta"><?= htmlspecialchars($empleado['correo']) ?></div>
              <?php endif; ?>
              <div class="mt-2">
                <span class="badge-role"><?= $empleado['rol']==='admin' ? 'Administrador' : 'Empleado' ?></span>
              </div>
            </div>
          </div>

          <hr class="my-4">
          <div class="meta">
            ID: <?= (int)$empleado['id_usuario'] ?><br>
            Usuario: <strong><?= htmlspecialchars($empleado['usuario']) ?></strong>
          </div>
        </div>
        <div class="block-footer d-flex justify-content-between">
          <a href="ver_empleados.php" class="btn btn-secondary">⬅️ Volver</a>
          <a href="cambiar_contrasena.php?id=<?= (int)$empleado['id_usuario'] ?>" class="btn btn-primary">🔒 Cambiar contraseña</a>
        </div>
      </section>

      <!-- ===== Bloque 2: Formulario de edición ===== -->
      <section class="block form">
        <div class="block-header">Editar datos</div>
        <div class="block-body">
          <form method="POST" novalidate>
            <div class="row g-3">
              <div class="col-12">
                <label for="nombre_completo" class="form-label">👤 Nombre completo</label>
                <input type="text" class="form-control" id="nombre_completo" name="nombre_completo"
                       value="<?= htmlspecialchars($empleado['nombre_completo']) ?>" required>
              </div>

              <div class="col-md-6">
                <label for="correo" class="form-label">📧 Correo</label>
                <input type="email" class="form-control" id="correo" name="correo"
                       value="<?= htmlspecialchars($empleado['correo']) ?>" required>
              </div>

              <div class="col-md-6">
                <label for="usuario" class="form-label">🧾 Usuario</label>
                <input type="text" class="form-control" id="usuario" name="usuario"
                       value="<?= htmlspecialchars($empleado['usuario']) ?>" required>
              </div>

              <div class="col-md-6">
                <label for="rol" class="form-label">🔐 Rol</label>
                <select class="form-select" id="rol" name="rol" required>
                  <option value="empleado" <?= $empleado['rol'] === 'empleado' ? 'selected' : '' ?>>Empleado</option>
                  <option value="admin" <?= $empleado['rol'] === 'admin' ? 'selected' : '' ?>>Administrador</option>
                </select>
              </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
              <a href="ver_empleados.php" class="btn btn-secondary">Cancelar</a>
              <button type="submit" class="btn btn-primary">Guardar cambios</button>
            </div>
          </form>
        </div>
      </section>
    </div>
  </div>
</body>
</html>

<?php
$stmt->close();
$conexion->close();
?>
