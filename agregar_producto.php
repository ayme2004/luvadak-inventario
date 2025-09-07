<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'admin') {
  header("Location: login.php");
  exit();
}

include("conexion.php");
$categorias = $conexion->query("SELECT id_categoria, nombre_categoria FROM categorias ORDER BY nombre_categoria ASC");
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Agregar Producto - Luvadak</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Bootstrap + Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  <style>
:root{
  --bg:#fafbff; --panel:#ffffff; --card:#ffffff; --text:#0f172a; --muted:#6b7280; --border:#edf0f7;
  --brand:#7c3aed; --brand2:#00d4ff; --ring:rgba(124,58,237,.35);
  --shadow-sm:0 2px 10px rgba(17,24,39,.06);
  --shadow-md:0 10px 26px rgba(17,24,39,.08);
  --shadow-lg:0 18px 48px rgba(17,24,39,.10);
  --radius:14px; --radius-lg:18px;
}
body{
  min-height:100vh;
  font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, sans-serif;
  color:var(--text);
  background:
    radial-gradient(1000px 520px at -10% -10%, rgba(124,58,237,.12), transparent 45%),
    radial-gradient(900px 480px at 110% 0%, rgba(0,212,255,.10), transparent 45%),
    var(--bg);
}
.wrap{ max-width:1100px; margin:28px auto; padding:0 18px }

/* Panel principal */
.glass{
  background: linear-gradient(180deg, rgba(255,255,255,.86), rgba(255,255,255,.92)), var(--panel);
  border:1px solid var(--border); border-radius:var(--radius-lg);
  box-shadow:var(--shadow-md); overflow:hidden;
}
.header{
  padding:18px 20px; border-bottom:1px solid var(--border);
  display:flex; align-items:center; justify-content:space-between; gap:12px;
  background:linear-gradient(180deg,#ffffff, #fbfcff);
}
.header .title{ display:flex; align-items:center; gap:12px; font-weight:800; letter-spacing:.2px }
.header .title i{
  font-size:1.3rem; background:linear-gradient(135deg, var(--brand), var(--brand2));
  -webkit-background-clip:text; background-clip:text; color:transparent;
}
.body{ padding:22px }
.section-title{ font-weight:700; margin:4px 0 12px; color:#334155; letter-spacing:.2px }

/* Inputs */
.form-control, .form-select, textarea.form-control{
  background:#fff; border:1px solid var(--border); color:var(--text);
  border-radius:12px; padding:10px 12px; box-shadow:var(--shadow-sm);
}
.form-control:focus, .form-select:focus, textarea.form-control:focus{
  border-color:#dcd7fe; box-shadow:0 0 0 4px var(--ring);
}
.form-label{ color:#475569; font-weight:600 }
.hint{ color:var(--muted); font-size:.9rem }

/* Chips de talla */
.sizes{ display:flex; gap:8px; flex-wrap:wrap }
.sizes input{ display:none }
.sizes label{
  border:1px solid var(--border); color:var(--text); padding:8px 12px; border-radius:999px;
  cursor:pointer; font-weight:700; min-width:48px; text-align:center;
  background:#fff; box-shadow:var(--shadow-sm); transition:transform .12s ease;
}
.sizes label:hover{ transform:translateY(-1px) }
.sizes input:checked + label{
  background:linear-gradient(135deg, var(--brand), var(--brand2));
  border-color:transparent; color:#fff; box-shadow:0 10px 24px rgba(124,58,237,.28);
}

/* Input groups */
.input-group-text{
  background:#f6f7fe; border:1px solid var(--border); color:#475569;
  border-radius:12px 0 0 12px; box-shadow:var(--shadow-sm)
}
.input-group .form-control{ border-left:none; border-radius:0 12px 12px 0 }

/* Botones */
.btn{
  border-radius:12px; font-weight:700; letter-spacing:.2px; border:1px solid var(--border);
  padding:10px 14px; box-shadow:var(--shadow-sm); background:#fff; color:#0f172a;
}
.btn:hover{ transform:translateY(-1px) }
.btn-primary{
  background:linear-gradient(135deg, var(--brand), var(--brand2));
  border-color:transparent; color:#fff;
  box-shadow:0 12px 28px rgba(0,212,255,.25), 0 8px 20px rgba(124,58,237,.25);
}

/* Dropdown "select" custom */
.btn-select{
  text-align:left; background:#fff; border:1px solid var(--border); box-shadow:var(--shadow-sm);
}
.dropdown-menu.select-menu{
  width:100%; border-radius:12px; border:1px solid var(--border);
  box-shadow:var(--shadow-lg); padding-top:8px; padding-bottom:8px;
  max-height:300px; overflow:auto;
}
.select-search{
  position:sticky; top:0; background:#fff; z-index:2; padding:8px 12px; border-bottom:1px solid var(--border);
}
.select-search .form-control{ box-shadow:none }
.select-actions{
  position:sticky; bottom:0; background:#fff; z-index:2; padding:8px 12px; border-top:1px solid var(--border);
}
.dropdown-item.active, .dropdown-item:active{ background:rgba(124,58,237,.12); color:#0f172a }
  </style>
</head>
<body>
  <div class="wrap">
    <form action="procesar_producto.php" method="POST" enctype="multipart/form-data" class="glass">

      <!-- Header -->
      <div class="header">
        <div class="title">
          <i class="bi bi-bag-plus-fill"></i>
          <div>
            <div style="font-size:1.05rem">Nuevo producto</div>
            <div class="hint">Completa los detalles para publicarlo</div>
          </div>
        </div>

        <div class="d-flex gap-2 flex-wrap justify-content-end">
          <a href="dashboard_admin.php" class="btn btn-outline-light">
            <i class="bi bi-arrow-left"></i> Volver
          </a>
          <button type="reset" class="btn btn-outline-light">
            <i class="bi bi-x-circle"></i> Cancelar
          </button>
          <button type="submit" class="btn btn-primary">
            <i class="bi bi-check2-circle"></i> Registrar
          </button>
        </div>
      </div>

      <!-- Body -->
      <div class="body">
        <div class="row g-4">
          <!-- Col izquierda -->
          <div class="col-12 col-lg-7">
            <div class="section-title"><i class="bi bi-info-circle"></i> Información básica</div>

            <div class="mb-3">
              <label class="form-label">Nombre</label>
              <input type="text" name="nombre_producto" class="form-control" maxlength="120" placeholder="Ej: Polera oversize" required>
            </div>

            <div class="mb-3">
              <label class="form-label">Descripción</label>
              <textarea name="descripcion" class="form-control" rows="4" maxlength="255" placeholder="Detalles, composición, cuidados…"></textarea>
            </div>

            <div class="row g-3">
              <div class="col-12 col-sm-6">
                <label class="form-label">Talla</label>
                <div class="sizes">
                  <?php foreach (["XS","S","M","L","XL"] as $t): ?>
                    <input type="radio" id="t_<?= $t ?>" name="talla" value="<?= $t ?>" required>
                    <label for="t_<?= $t ?>"><?= $t ?></label>
                  <?php endforeach; ?>
                </div>
              </div>
              <div class="col-12 col-sm-6">
                <label class="form-label">Color</label>
                <div class="input-group">
                  <span class="input-group-text">
                    <input id="colorPicker" type="color" value="#000000" style="width:28px;border:none;background:transparent;padding:0">
                  </span>
                  <input id="colorText" type="text" name="color" class="form-control" placeholder="Ej: #111827" required>
                </div>
                <div class="hint mt-1">El texto se autocompleta al elegir en el picker.</div>
              </div>
            </div>
          </div>

          <!-- Col derecha -->
          <div class="col-12 col-lg-5">
            <div class="section-title"><i class="bi bi-cash-coin"></i> Precio & stock</div>

            <div class="mb-3">
              <label class="form-label">Precio (S/)</label>
              <div class="input-group">
                <span class="input-group-text">S/</span>
                <input type="number" name="precio" step="0.01" min="0" class="form-control" placeholder="49.90" required>
              </div>
            </div>

            <div class="mb-3">
              <label class="form-label">Stock inicial</label>
              <div class="input-group">
                <span class="input-group-text"><i class="bi bi-box-seam"></i></span>
                <input type="number" name="stock" min="0" class="form-control" placeholder="15" required>
              </div>
            </div>

            <!-- Selector de categoría con búsqueda -->
            <div class="mb-3">
              <label class="form-label">Categoría</label>

              <!-- Valor real -->
              <input type="hidden" name="id_categoria" id="catValue" required>

              <!-- Botón que simula el select -->
              <button class="btn btn-select w-100 d-flex justify-content-between align-items-center form-control"
                      type="button" data-bs-toggle="dropdown" aria-expanded="false" id="catBtn">
                <span id="catLabel" class="text-muted">Selecciona categoría</span>
                <i class="bi bi-chevron-down ms-2"></i>
              </button>

              <!-- Menú con buscador -->
              <ul class="dropdown-menu select-menu" id="catMenu" aria-labelledby="catBtn">
                <li class="select-search">
                  <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" class="form-control" id="catSearch" placeholder="Buscar categoría...">
                  </div>
                </li>
                <?php
                  $categorias->data_seek(0);
                  while ($cat = $categorias->fetch_assoc()):
                ?>
                  <li>
                    <button type="button" class="dropdown-item d-flex justify-content-between align-items-center"
                            data-id="<?= $cat['id_categoria'] ?>">
                      <span><?= htmlspecialchars($cat['nombre_categoria']) ?></span>
                    </button>
                  </li>
                <?php endwhile; ?>
                <li class="select-actions d-flex gap-2">
                  <button type="button" class="btn btn-sm btn-outline-secondary flex-fill" id="catClear">
                    <i class="bi bi-x-circle"></i> Limpiar
                  </button>
                  <button type="button" class="btn btn-sm btn-primary flex-fill" data-bs-toggle="dropdown">
                    <i class="bi bi-check2"></i> Listo
                  </button>
                </li>
              </ul>

              <div class="invalid-feedback">Selecciona una categoría.</div>
            </div>
          </div>
        </div>
      </div>
    </form>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Color picker -> input texto
    const colorPicker = document.getElementById('colorPicker');
    const colorText   = document.getElementById('colorText');
    colorPicker.addEventListener('input', e => { colorText.value = e.target.value; });

    // ====== Dropdown Categoría con búsqueda ======
    const catValue  = document.getElementById('catValue');
    const catLabel  = document.getElementById('catLabel');
    const catMenu   = document.getElementById('catMenu');
    const catSearch = document.getElementById('catSearch');
    const catClear  = document.getElementById('catClear');

    // Selección
    catMenu.querySelectorAll('.dropdown-item[data-id]').forEach(btn=>{
      btn.addEventListener('click', ()=>{
        const id = btn.getAttribute('data-id');
        const label = btn.textContent.trim();
        catValue.value = id;
        catLabel.textContent = label;
        catLabel.classList.remove('text-muted');

        // Visualmente marcar activo
        catMenu.querySelectorAll('.dropdown-item').forEach(i=>i.classList.remove('active'));
        btn.classList.add('active');
      });
    });

    // Buscar en vivo
    catSearch.addEventListener('input', ()=>{
      const q = catSearch.value.trim().toLowerCase();
      catMenu.querySelectorAll('.dropdown-item[data-id]').forEach(btn=>{
        const txt = btn.textContent.toLowerCase();
        btn.parentElement.style.display = txt.includes(q) ? '' : 'none';
      });
    });

    // Limpiar selección
    catClear.addEventListener('click', ()=>{
      catValue.value = '';
      catLabel.textContent = 'Selecciona categoría';
      catLabel.classList.add('text-muted');
      catMenu.querySelectorAll('.dropdown-item').forEach(i=>i.classList.remove('active'));
    });

    // Validación simple al enviar
    document.querySelector('form').addEventListener('submit', (e)=>{
      if(!catValue.value){
        e.preventDefault();
        document.getElementById('catBtn').classList.add('is-invalid');
        // Abrir menú para que el usuario elija
        const dropdown = bootstrap.Dropdown.getOrCreateInstance(document.getElementById('catBtn'));
        dropdown.show();
        catSearch.focus();
      } else {
        document.getElementById('catBtn').classList.remove('is-invalid');
      }
    });

    // Accesibilidad básica con teclado
    document.getElementById('catBtn').addEventListener('keydown', (e)=>{
      if(e.key === 'Enter' || e.key === ' '){
        e.preventDefault();
        bootstrap.Dropdown.getOrCreateInstance(e.currentTarget).toggle();
        setTimeout(()=>catSearch.focus(), 100);
      }
    });
  </script>
</body>
</html>
