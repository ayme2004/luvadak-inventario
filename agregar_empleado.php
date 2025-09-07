<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'admin') {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Agregar Empleado - Luvadak</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    :root{
      --bg:#f8fafc;
      --panel:#ffffff;
      --text:#0f172a;
      --muted:#667085;
      --border:#e6e9f2;
      --brand:#7c3aed;
      --ring:rgba(124,58,237,.25);
      --radius:14px;
      --shadow:0 2px 10px rgba(16,24,40,.06);
    }
    body{
      background:
        radial-gradient(900px 520px at 110% -10%, rgba(124,58,237,.06), transparent 45%),
        var(--bg);
      font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, sans-serif;
      color:var(--text);
    }

    /* ===== Card principal ===== */
    .form-card{
      max-width:950px;
      margin:40px auto;
      background:var(--panel);
      padding:30px;
      border-radius:var(--radius);
      border:1px solid var(--border);
      box-shadow:var(--shadow);
    }
    .form-card h3{
      font-weight:700; text-align:center; margin-bottom:25px;
      color:var(--text);
    }

    /* ===== Inputs ===== */
    .form-label{ font-weight:600; color:var(--text) }
    .form-control, .form-select{
      border:1px solid var(--border);
      border-radius:var(--radius);
      padding:10px 12px;
      transition:border .2s, box-shadow .2s;
    }
    .form-control:focus, .form-select:focus{
      border-color:#d4d8f0;
      box-shadow:0 0 0 3px var(--ring);
    }

    /* ===== Botones ===== */
    .btn{
      border-radius:var(--radius);
      font-weight:600;
      transition:.2s;
    }
    .btn-success{
      background:var(--brand);
      border-color:var(--brand);
    }
    .btn-success:hover{ filter:brightness(1.05) }
    .btn-secondary{
      background:#fff; color:var(--text); border:1px solid var(--border);
    }
    .btn-secondary:hover{ background:#f5f6fb }

    /* ===== Responsive ===== */
    @media (max-width: 768px){
      .row-cols-md-2{ flex-direction:column; }
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="form-card">
      <h3>➕ Agregar Nuevo Empleado</h3>
      <form action="procesar_agregar_empleado.php" method="POST">
        <div class="row row-cols-1 row-cols-md-2 g-4">
          <!-- Columna izquierda -->
          <div class="col">
            <div class="mb-3">
              <label for="nombre_completo" class="form-label">👤 Nombre completo</label>
              <input type="text" class="form-control" id="nombre_completo" name="nombre_completo" required>
            </div>
            <div class="mb-3">
              <label for="correo" class="form-label">📧 Correo</label>
              <input type="email" class="form-control" id="correo" name="correo" required>
            </div>
            <div class="mb-3">
              <label for="usuario" class="form-label">🧾 Usuario</label>
              <input type="text" class="form-control" id="usuario" name="usuario" required>
            </div>
          </div>

          <!-- Columna derecha -->
          <div class="col">
            <div class="mb-3">
              <label for="contrasena" class="form-label">🔒 Contraseña</label>
              <input type="password" class="form-control" id="contrasena" name="contrasena" required>
            </div>
            <div class="mb-3">
              <label for="rol" class="form-label">⚙️ Rol</label>
              <select class="form-select" id="rol" name="rol" required>
                <option value="empleado">Empleado</option>
                <option value="admin">Administrador</option>
              </select>
            </div>
            <div class="d-flex flex-wrap gap-2 mt-4">
              <a href="ver_empleados.php" class="btn btn-secondary flex-fill">⬅️ Cancelar</a>
              <a href="dashboard_admin.php" class="btn btn-secondary flex-fill">⬅️ Volver</a>
              <button type="submit" class="btn btn-success flex-fill">💾 Guardar</button>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>
</body>
</html>
