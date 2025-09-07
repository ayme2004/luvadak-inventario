<?php
include("conexion.php");

$empleados = $conexion->query("SELECT id_usuario, nombre_completo FROM usuarios WHERE rol = 'empleado'");
$lista_empleados = [];
while ($row = $empleados->fetch_assoc()) {
    $lista_empleados[] = $row;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Reporte por Empleado - Luvadak</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <!-- Bootstrap + Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  <style>
    :root{
      --bg:#f8fafc;
      --panel:#ffffff;
      --text:#0b1220;
      --muted:#667085;
      --border:#e6e9f2;
      --brand:#7c3aed;
      --ring:rgba(124,58,237,.22);
      --radius:12px;
      --shadow-1:0 1px 3px rgba(16,24,40,.06);
    }
    body{
      background:
        radial-gradient(900px 520px at 110% -10%, rgba(124,58,237,.06), transparent 45%),
        var(--bg);
      color:var(--text);
      font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, sans-serif;
    }

    .page-title{
      display:flex; align-items:center; gap:.6rem;
      font-weight:700; font-size:1.4rem; margin:28px 0 20px;
    }
    .page-title i{ color:var(--brand) }

    .block{
      border:1px solid var(--border);
      border-radius:var(--radius);
      background:var(--panel);
      box-shadow:var(--shadow-1);
      overflow:hidden;
      flex:1;
      display:flex; flex-direction:column;
      min-height:280px;
    }
    .block .block-header{
      background:#fff;
      border-bottom:1px solid var(--border);
      padding:14px 18px;
      font-weight:600; color:#111827;
    }
    .block .block-body{ padding:16px 18px; flex:1 }
    .block .block-footer{
      background:#fafbff;
      border-top:1px solid var(--border);
      padding:12px 18px;
    }

    /* Layout en horizontal */
    .blocks-row{
      display:flex;
      flex-direction:column;
      gap:20px;
    }
    @media (min-width:992px){
      .blocks-row{
        flex-direction:row;
        align-items:flex-start;
      }
    }

    .search-wrap{ position:relative }
    .search-wrap .form-control{
      height:44px;
      border:1px solid var(--border);
      border-radius:var(--radius);
      padding-left:40px;
      background:#fff;
    }
    .search-wrap .form-control:focus{
      border-color:#d5d9e3;
      box-shadow:0 0 0 2px var(--ring);
    }
    .search-wrap .icon{
      position:absolute; left:12px; top:50%; transform:translateY(-50%);
      font-size:1.05rem; color:var(--brand);
    }

    .table{
      border:1px solid var(--border);
      border-radius:10px;
      overflow:hidden;
      background:#fff;
    }
    .table thead{ background:#f6f7fb }
    .table thead th{ font-weight:700; color:#111827; border:0 }
    .table tbody td{ border-color:#eef1f6 }
    .table-hover tbody tr:hover{ background:#fafbff }

    .alert{
      background:#fff8e6;
      border:1px solid #f3e5c1;
      color:#8a6d1f;
      border-radius:10px;
    }

    .btn{
      border-radius:10px; font-weight:600;
      border:1px solid var(--border);
    }
    .btn-primary{ background:var(--brand); border-color:var(--brand); color:#fff }
    .btn-pdf{ background:#e11d48; border-color:#e11d48; color:#fff }
    .btn-secondary{ background:#fff; color:#0f172a; border-color:#e4e7ee }
  </style>

  <script>
    function filtrarEmpleados() {
      const input = document.getElementById("filtro");
      const filtro = input.value.toLowerCase();
      const filas = document.querySelectorAll(".fila-empleado");
      filas.forEach(fila => {
        const nombre = fila.querySelector("td").textContent.toLowerCase();
        fila.style.display = nombre.includes(filtro) ? "" : "none";
      });
    }
  </script>
</head>
<body>
  <div class="container">
    <div class="page-title">
      <i class="bi bi-people-fill"></i>
      <span>Reporte por Empleado</span>
    </div>

    <div class="blocks-row">
      <!-- Bloque búsqueda -->
      <section class="block">
        <div class="block-header">Búsqueda</div>
        <div class="block-body d-flex align-items-center">
          <div class="search-wrap w-100">
            <i class="bi bi-search icon"></i>
            <input type="text" id="filtro" class="form-control"
                   placeholder="Buscar empleado por nombre…" onkeyup="filtrarEmpleados()">
          </div>
        </div>
      </section>

      <!-- Bloque resultados -->
      <section class="block">
        <div class="block-header">Resultados</div>
        <div class="block-body">
          <?php if (count($lista_empleados) > 0): ?>
            <div class="table-responsive">
              <table class="table table-bordered table-hover text-center align-middle">
                <thead>
                  <tr>
                    <th><i class="bi bi-person-circle"></i> Nombre del Empleado</th>
                    <th style="width:220px"><i class="bi bi-gear"></i> Acciones</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($lista_empleados as $emp): ?>
                  <tr class="fila-empleado">
                    <td><?= htmlspecialchars($emp['nombre_completo']) ?></td>
                    <td>
                      <a href="reporte_por_empleado.php?id_empleado=<?= $emp['id_usuario'] ?>" class="btn btn-sm btn-primary me-2">
                        <i class="bi bi-bar-chart-line-fill"></i> Ver
                      </a>
                      <a href="reporte_empleado_pdf.php?id_empleado=<?= $emp['id_usuario'] ?>" class="btn btn-sm btn-pdf" target="_blank">
                        <i class="bi bi-file-earmark-pdf-fill"></i> PDF
                      </a>
                    </td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php else: ?>
            <div class="alert d-flex align-items-center" role="alert">
              <i class="bi bi-exclamation-triangle-fill me-2"></i>
              <div>No hay empleados registrados en el sistema.</div>
            </div>
          <?php endif; ?>
        </div>
        <div class="block-footer text-center">
          <a href="dashboard_admin.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left-circle"></i> Volver al Panel
          </a>
        </div>
      </section>
    </div>
  </div>
</body>
</html>
