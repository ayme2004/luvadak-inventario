<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'admin') {
  header("Location: login.php");
  exit();
}

include("conexion.php");

if (isset($_POST['registrar'])) {
  $nombre    = trim($_POST['nombre_completo']);
  $dni       = trim($_POST['dni']);
  $telefono  = trim($_POST['telefono']);
  $correo    = trim($_POST['correo']);
  $direccion = trim($_POST['direccion']);

  $stmt = $conexion->prepare("INSERT INTO clientes (nombre_completo, dni, telefono, correo, direccion) VALUES (?, ?, ?, ?, ?)");
  $stmt->bind_param("sssss", $nombre, $dni, $telefono, $correo, $direccion);
  if ($stmt->execute()) {
    echo "<script>alert('✅ Cliente registrado correctamente'); window.location='clientes.php';</script>";
    exit();
  } else {
    echo "<script>alert('❌ Error al registrar cliente');</script>";
  }
}

$busqueda = isset($_GET['buscar']) ? trim($_GET['buscar']) : "";
$sql = "SELECT * FROM clientes";
if (!empty($busqueda)) {
  $sql .= " WHERE nombre_completo LIKE '%" . $conexion->real_escape_string($busqueda) . "%'";
}
$sql .= " ORDER BY fecha_registro DESC";
$clientes = $conexion->query($sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Gestión de Clientes - Luvadak</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    :root{
      --bg:#f8fafc;
      --panel:#ffffff;
      --text:#0f172a;
      --muted:#667085;
      --border:#e6e9f2;
      --brand:#7c3aed;
      --ring:rgba(124,58,237,.22);
      --radius:12px;
      --radius-sm:10px;
      --shadow:0 2px 10px rgba(16,24,40,.06);
    }
    body{
      background:
        radial-gradient(900px 520px at 110% -10%, rgba(124,58,237,.06), transparent 45%),
        var(--bg);
      color:var(--text);
      font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, sans-serif;
    }

    .page{ max-width: 1200px; margin: 34px auto; padding: 0 16px }
    .page-title{
      display:flex; align-items:center; gap:.6rem;
      font-weight:700; font-size:1.35rem; margin-bottom:16px;
    }
    .page-title .dot{
      width:10px; height:10px; border-radius:999px; background:var(--brand);
      box-shadow:0 0 0 6px rgba(124,58,237,.12);
    }

    .blocks{ display:flex; flex-direction:column; gap:18px }
    @media (min-width: 992px){ .blocks{ flex-direction:row } }

    .block{
      border:1px solid var(--border);
      border-radius:var(--radius);
      background:var(--panel);
      box-shadow:var(--shadow);
      overflow:hidden;
      display:flex; flex-direction:column;
    }
    .block-header{
      background:#fff; border-bottom:1px solid var(--border);
      padding:14px 18px; font-weight:600;
      display:flex; align-items:center; justify-content:space-between; gap:10px; flex-wrap:wrap;
    }
    .block-body{ padding:16px 18px; }
    .block-footer{ background:#fafbff; border-top:1px solid var(--border); padding:12px 18px }

    .block.list{ flex: 1.4 }
    .block.form { flex: 1 }

    .search-wrap{ position:relative }
    .search-wrap input{
      height:44px; padding-left:42px;
      border:1px solid var(--border); border-radius:var(--radius-sm);
      box-shadow:none; width:100%;
    }
    .search-wrap input:focus{
      border-color:#d5d9e3; box-shadow:0 0 0 2px var(--ring);
    }
    .search-wrap .icon{
      position:absolute; left:12px; top:50%; transform:translateY(-50%);
      font-size:1.05rem; color:var(--brand);
    }

    .table{ border:1px solid var(--border); border-radius:10px; overflow:hidden; background:#fff }
    .table thead{ background:#f6f7fb }
    .table thead th{ font-weight:700; color:#111827; border:0; white-space:nowrap }
    .table tbody td{ border-color:#eef1f6 }
    .table-hover tbody tr:hover{ background:#fafbff }

    .btn{ border-radius:10px; font-weight:600; border:1px solid var(--border) }
    .btn-primary{ background:var(--brand); border-color:var(--brand); color:#fff }
    .btn-primary:hover{ filter:brightness(1.03) }
    .btn-danger{ background:#e11d48; border-color:#e11d48 }
    .btn-danger:hover{ background:#be123c; border-color:#be123c }
    .btn-secondary{ background:#fff; color:#0f172a; border-color:#e4e7ee }
    .btn-secondary:hover{ background:#f5f6fb }
    .btn-whatsapp{ background:#25D366; color:#fff; border-color:#22c45e }
    .btn-whatsapp:hover{ background:#1EBE54; color:#fff }

    .form-label{ font-weight:600 }
    .form-control{
      border:1px solid var(--border); border-radius:var(--radius-sm);
      padding:10px 12px; transition:border .2s, box-shadow .2s;
    }
    .form-control:focus{ border-color:#d5d9e3; box-shadow:0 0 0 3px var(--ring) }

    .toolbar{ display:flex; gap:.5rem; flex-wrap:wrap; justify-content:flex-end }
    .muted{ color:var(--muted) }

    /* ======= Responsive tabla apilable (≤768px) ======= */
    @media (max-width: 768px){
      .table,
      .table thead,
      .table tbody,
      .table th,
      .table td,
      .table tr{ display:block; width:100% }

      .table thead{ display:none }
      .table tbody tr{
        margin-bottom:12px; border:1px solid var(--border);
        border-radius:12px; padding:12px; box-shadow:var(--shadow);
      }
      .table tbody td{
        border:none; padding:8px 0; text-align:left; white-space:normal; word-break:break-word;
      }
      .table tbody td::before{
        content: attr(data-label);
        display:block; font-weight:700; color:var(--muted); margin-bottom:2px;
      }
      .block-header .toolbar{ width:100%; justify-content:flex-start }
    }
  </style>
</head>
<body>
  <div class="page">
    <div class="page-title"><span class="dot"></span> Gestión de Clientes</div>

    <div class="blocks">
      <!-- ===== Bloque izquierdo: Listado + búsqueda ===== -->
      <section class="block list">
        <div class="block-header">
          <span>📋 Listado</span>
          <div class="toolbar">
            <a href="dashboard_admin.php" class="btn btn-secondary">← Volver al Panel</a>
          </div>
        </div>

        <div class="block-body">
          <form method="GET" class="mb-3">
            <div class="search-wrap">
              <span class="icon">🔍</span>
              <input type="text" name="buscar" class="form-control" placeholder="Buscar por nombre..."
                     value="<?= htmlspecialchars($busqueda); ?>">
            </div>
          </form>

          <div class="table-responsive">
            <table class="table table-bordered table-hover text-center align-middle">
              <thead>
                <tr>
                  <th>👤 Nombre</th>
                  <th>DNI</th>
                  <th>📱 Teléfono</th>
                  <th>📧 Correo</th>
                  <th>📍 Dirección</th>
                  <th>📆 Registro</th>
                  <th>⚙️ Opciones</th>
                </tr>
              </thead>
              <tbody>
              <?php if ($clientes->num_rows === 0): ?>
                <tr><td colspan="7" class="muted py-4">🚫 No se encontraron clientes.</td></tr>
              <?php else: ?>
                <?php while ($fila = $clientes->fetch_assoc()): ?>
                  <tr>
                    <td data-label="Nombre"><?= htmlspecialchars($fila['nombre_completo']); ?></td>
                    <td data-label="DNI"><?= htmlspecialchars($fila['dni']); ?></td>
                    <td data-label="Teléfono"><?= htmlspecialchars($fila['telefono']); ?></td>
                    <td data-label="Correo"><?= htmlspecialchars($fila['correo']); ?></td>
                    <td data-label="Dirección"><?= htmlspecialchars($fila['direccion']); ?></td>
                    <td data-label="Registro"><?= date("d/m/Y", strtotime($fila['fecha_registro'])); ?></td>
                    <td data-label="Opciones">
                      <div class="d-flex flex-wrap gap-1 justify-content-center">
                        <a href="historial_cliente.php?id=<?= (int)$fila['id_cliente'] ?>" class="btn btn-sm btn-primary">📑 Historial</a>
                        <a href="exportar_pdf_cliente.php?id=<?= (int)$fila['id_cliente'] ?>" class="btn btn-sm btn-danger">📄 PDF</a>
                        <?php if (!empty($fila['telefono'])): ?>
                          <a target="_blank" href="https://wa.me/51<?= preg_replace('/\D/', '', $fila['telefono']) ?>" class="btn btn-sm btn-whatsapp">💬 WhatsApp</a>
                        <?php endif; ?>
                      </div>
                    </td>
                  </tr>
                <?php endwhile; ?>
              <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </section>

      <!-- ===== Bloque derecho: Registro rápido ===== -->
      <section class="block form">
        <div class="block-header">📝 Registrar Nuevo Cliente</div>
        <div class="block-body">
          <form method="POST" class="row g-3">
            <div class="col-12">
              <label class="form-label">Nombre completo</label>
              <input type="text" name="nombre_completo" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">DNI</label>
              <input type="text" name="dni" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Teléfono</label>
              <input type="text" name="telefono" class="form-control">
            </div>
            <div class="col-md-6">
              <label class="form-label">Correo</label>
              <input type="email" name="correo" class="form-control">
            </div>
            <div class="col-md-6">
              <label class="form-label">Dirección</label>
              <input type="text" name="direccion" class="form-control">
            </div>
            <div class="col-12 d-grid">
              <button type="submit" name="registrar" class="btn btn-primary">✅ Registrar Cliente</button>
            </div>
          </form>
        </div>
        <div class="block-footer">
          <small class="muted">Consejo: completa DNI y teléfono para habilitar contacto por WhatsApp.</small>
        </div>
      </section>
    </div>
  </div>
</body>
</html>
