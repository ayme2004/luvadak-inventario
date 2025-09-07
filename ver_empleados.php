<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'admin') {
  header("Location: login.php");
  exit();
}

include("conexion.php");

$busqueda = isset($_GET['buscar']) ? trim($_GET['buscar']) : "";

$sql = "SELECT id_usuario, nombre_completo, correo, usuario, rol, fecha_registro
        FROM usuarios
        WHERE nombre_completo LIKE ? OR correo LIKE ? OR usuario LIKE ?
        ORDER BY id_usuario ASC";
$stmt = $conexion->prepare($sql);
$param = "%{$busqueda}%";
$stmt->bind_param("sss", $param, $param, $param);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Gestión de Empleados - Luvadak</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <!-- Bootstrap + Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  <style>
    :root{
      --bg:#f8fafc; --panel:#ffffff; --text:#0f172a; --muted:#667085; --border:#e6e9f2;
      --brand:#7c3aed; --brand2:#00d4ff; --ring:rgba(124,58,237,.22);
      --radius:14px; --radius-sm:10px; --radius-lg:18px;
      --shadow:0 10px 26px rgba(16,24,40,.08);
    }
    body{
      background:
        radial-gradient(900px 520px at -10% -10%, rgba(124,58,237,.10), transparent 45%),
        radial-gradient(900px 520px at 110% 0%, rgba(0,212,255,.10), transparent 45%),
        var(--bg);
      color:var(--text);
      font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, sans-serif;
    }
    .wrap{ max-width:1200px; margin:28px auto; padding:0 16px }

    /* Hero */
    .hero{
      display:flex; gap:12px; align-items:center; flex-wrap:wrap;
      background:linear-gradient(180deg, rgba(255,255,255,.90), rgba(255,255,255,.98));
      border:1px solid var(--border); border-radius:var(--radius-lg);
      padding:16px; box-shadow:var(--shadow); margin-bottom:18px;
    }
    .hero .icon{
      width:46px;height:46px;border-radius:12px;display:grid;place-items:center;color:#fff;
      background:linear-gradient(135deg, var(--brand), var(--brand2));
      box-shadow:0 12px 24px rgba(124,58,237,.25);
      font-size:1.25rem;
    }
    .hero .title{ font-weight:800; font-size:1.25rem }
    .hero .sub{ color:var(--muted); font-size:.95rem }

    /* Block */
    .block{
      border:1px solid var(--border);
      border-radius:var(--radius);
      background:var(--panel);
      box-shadow:var(--shadow);
      overflow:hidden;
      margin-bottom:16px;
    }
    .block-header{
      background:#fff; border-bottom:1px solid var(--border);
      padding:14px 18px; font-weight:800; display:flex; align-items:center; gap:10px; justify-content:space-between;
      flex-wrap:wrap;
    }
    .block-body{ padding:16px 18px }

    /* Filtros grid */
    .filters{ display:grid; grid-template-columns:1fr auto; gap:10px; align-items:center }
    @media (max-width: 768px){ .filters{ grid-template-columns:1fr; } }

    .form-control, .form-select{
      border:1px solid var(--border); border-radius:var(--radius-sm);
    }
    .form-control:focus, .form-select:focus{
      border-color:#d5d9e3; box-shadow:0 0 0 3px var(--ring);
    }

    /* Tabla */
    .table-card{ border:1px solid var(--border); border-radius:12px; overflow:hidden; background:#fff }
    thead.sticky th{
      position:sticky; top:0; z-index:1; background:#f6f7fb; border-bottom:1px solid var(--border);
      white-space:nowrap;
    }
    .role-badge{
      padding:4px 10px; border-radius:999px; font-weight:700; font-size:.78rem;
      background:linear-gradient(135deg,#f0ecff,#e6f9ff); color:#111827; border:1px solid #edf0ff;
    }

    /* Botones */
    .btn{ border-radius:999px; font-weight:700; border:1px solid var(--border) }
    .btn-primary{
      background:linear-gradient(135deg, var(--brand), var(--brand2));
      border-color:transparent; color:#fff;
      box-shadow:0 10px 22px rgba(124,58,237,.28);
    }
    .btn-primary:hover{ filter:brightness(1.05) }
    .btn-secondary{ background:#fff; color:#0f172a; border-color:var(--border) }
    .btn-secondary:hover{ background:#f6f7fb }
    .btn-success, .btn-warning, .btn-info, .btn-danger{ border:none }

    .empty{
      border:1px dashed #dbe0ef; border-radius:14px; padding:18px; text-align:center;
      color:#6b7280; background:#f9fbff;
    }

    /* --------- Responsive: tabla apilable en móvil --------- */
    @media (max-width: 768px){
      .table-card table,
      .table-card thead,
      .table-card tbody,
      .table-card th,
      .table-card td,
      .table-card tr{ display:block; width:100% }

      .table-card thead{ display:none }
      .table-card tbody tr{
        border:1px solid var(--border);
        border-radius:12px;
        margin-bottom:12px;
        padding:12px;
        box-shadow:var(--shadow);
      }
      .table-card tbody td{
        border:0;
        text-align:left;
        padding:6px 0;
        white-space:normal;
      }
      .table-card tbody td::before{
        content:attr(data-label);
        display:block;
        font-weight:700;
        color:var(--muted);
        margin-bottom:2px;
      }
      /* Acciones: que no se desborde */
      .actions-wrap{ justify-content:flex-start !important; gap:.4rem }
    }
  </style>
</head>
<body>
  <div class="wrap">

    <!-- Hero -->
    <div class="hero">
      <div class="icon"><i class="bi bi-people-fill"></i></div>
      <div class="flex-grow-1">
        <div class="title">Gestión de Empleados</div>
        <div class="sub">Busca, agrega y administra la información del personal</div>
      </div>
      <div class="d-none d-sm-flex gap-2">
        <a href="agregar_empleado.php" class="btn btn-success"><i class="bi bi-plus-circle me-1"></i> Agregar</a>
        <a href="dashboard_admin.php" class="btn btn-secondary"><i class="bi bi-arrow-left me-1"></i> Volver</a>
      </div>
    </div>

    <!-- Filtros -->
    <section class="block">
      <div class="block-header"><i class="bi bi-funnel-fill"></i> Filtros</div>
      <div class="block-body">
        <form method="GET" class="filters">
          <input type="text" class="form-control" name="buscar"
                 placeholder="🔍 Buscar por nombre, correo o usuario"
                 value="<?= htmlspecialchars($busqueda) ?>" />
          <button type="submit" class="btn btn-primary"><i class="bi bi-search me-1"></i> Buscar</button>
        </form>
      </div>
    </section>

    <!-- Acciones rápidas (solo móvil) -->
    <div class="d-grid gap-2 d-sm-none mb-2">
      <a href="agregar_empleado.php" class="btn btn-success"><i class="bi bi-plus-circle me-1"></i> Agregar</a>
      <a href="dashboard_admin.php" class="btn btn-secondary"><i class="bi bi-arrow-left me-1"></i> Volver</a>
    </div>

    <!-- Resultados -->
    <section class="block">
      <div class="block-header"><i class="bi bi-list-check"></i> Resultados</div>
      <div class="block-body">
        <?php if ($result->num_rows > 0): ?>
          <div class="table-responsive table-card">
            <table class="table table-hover align-middle text-center m-0">
              <thead class="sticky">
                <tr>
                  <th style="width:70px">#</th>
                  <th>👤 Nombre</th>
                  <th>📧 Correo</th>
                  <th>🧾 Usuario</th>
                  <th>🔐 Rol</th>
                  <th>📅 Registro</th>
                  <th style="width:220px">⚙️ Acciones</th>
                </tr>
              </thead>
              <tbody>
                <?php while($row = $result->fetch_assoc()): ?>
                  <tr>
                    <td data-label="#"> <?= (int)$row['id_usuario'] ?> </td>
                    <td data-label="Nombre"> <?= htmlspecialchars($row['nombre_completo']) ?> </td>
                    <td data-label="Correo"> <?= htmlspecialchars($row['correo']) ?> </td>
                    <td data-label="Usuario"> <?= htmlspecialchars($row['usuario']) ?> </td>
                    <td data-label="Rol"><span class="role-badge"><?= htmlspecialchars(ucfirst($row['rol'])) ?></span></td>
                    <td data-label="Registro"> <?= date("d/m/Y", strtotime($row['fecha_registro'])) ?> </td>
                    <td data-label="Acciones">
                      <div class="d-flex justify-content-center gap-1 flex-wrap actions-wrap">
                        <a href="editar_empleado.php?id=<?= $row['id_usuario'] ?>" class="btn btn-warning btn-sm" title="Editar">
                          <i class="bi bi-pencil-fill"></i>
                        </a>
                        <a href="cambiar_contrasena.php?id=<?= $row['id_usuario'] ?>" class="btn btn-info btn-sm text-white" title="Cambiar contraseña">
                          <i class="bi bi-key-fill"></i>
                        </a>
                        <a href="eliminar_empleado.php?id=<?= $row['id_usuario'] ?>"
                           class="btn btn-danger btn-sm"
                           onclick="return confirm('¿Seguro que deseas eliminar este empleado?');"
                           title="Eliminar">
                          <i class="bi bi-trash-fill"></i>
                        </a>
                      </div>
                    </td>
                  </tr>
                <?php endwhile; ?>
              </tbody>
            </table>
          </div>
        <?php else: ?>
          <div class="empty">
            <div class="mb-1" style="font-size:1.05rem">
              <i class="bi bi-exclamation-triangle-fill me-1"></i>No se encontraron empleados.
            </div>
            <div class="text-muted">Prueba con otro término de búsqueda.</div>
          </div>
        <?php endif; ?>
      </div>
    </section>

  </div>
</body>
</html>
<?php
$stmt->close();
$conexion->close();
?>
