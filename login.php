<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Iniciar sesión - Luvadak</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Tipografía similar al mockup -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">

  <style>
    :root{
      --brand:#1363ff;
      --brand-600:#0b49c8;
      --ink:#0f172a;
      --muted:#64748b;
      --chip-bg:#f1f5f9;
      --bg:#f6faff;
      --surface:#ffffff;
      --shadow:0 10px 30px rgba(16,24,40,.08);
      --radius:22px;
    }

    html,body{height:100%}
    body{
      background: radial-gradient(1200px 600px at 15% 10%, #e8f0ff 0%, transparent 60%) , var(--bg);
      font-family: "Inter", system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
      color: var(--ink);
    }

    .auth-shell{
      width:min(1100px, 92vw);
      background: linear-gradient(180deg, #ffffff 0%, #fafdff 100%);
      border-radius: var(--radius);
      box-shadow: var(--shadow);
      display:grid;
      grid-template-columns: 1.15fr 1fr;
      overflow:hidden;
      border:1px solid #eef2f7;
    }

    /* Izquierda (bienvenida) */
    .left-pane{
      padding: clamp(28px, 5vw, 48px);
      background: radial-gradient(900px 520px at -5% 30%, #eef4ff 0%, transparent 55%),
                  radial-gradient(600px 500px at 70% 120%, #f0f7ff 0%, transparent 60%);
    }
    .brand-badge{
      display:inline-flex; align-items:center; gap:.6rem;
      background:#eef4ff; color:#1e40af;
      padding:.55rem .95rem; border-radius:999px;
      font-weight:600; font-size:.95rem;
      box-shadow: inset 0 0 0 1px #dbe6ff;
    }
    .brand-badge i{
      display:inline-grid; place-items:center;
      width:26px; height:26px; border-radius:999px;
      background:#dbe6ff; box-shadow: inset 0 0 0 1px #c7d7ff;
      font-style:normal;
    }

    .welcome h1{
      font-size: clamp(2rem, 4.2vw, 3.2rem);
      line-height:1.05; letter-spacing:-.02em;
      margin: 22px 0 6px; font-weight:800;
    }
    .welcome p{
      color:var(--muted); max-width:46ch;
      margin: 10px 0 20px;
    }

    .chips{display:flex; flex-wrap:wrap; gap:12px;}
    .chip{
      display:inline-flex; align-items:center; gap:8px;
      background:var(--chip-bg);
      border-radius:14px; padding:10px 14px;
      font-weight:600; color:#0f172a;
      box-shadow: inset 0 0 0 1px #e5eaf1;
    }
    .chip i{
      width:18px; height:18px; display:inline-block;
      border-radius:50%; box-shadow: inset 0 0 0 2px #cbd5e1;
    }
    .copyright{color:#94a3b8; font-size:.9rem; margin-top:32px;}

    /* Derecha (login) */
    .right-pane{
      background: var(--surface);
      position:relative;
      padding: clamp(26px, 4.5vw, 42px);
    }
    .right-pane::before{
      content:""; position:absolute; inset:0 0 0 auto; width:6px;
      background: var(--brand); box-shadow: 0 0 0 1px rgba(19,99,255,.15) inset;
    }

    .form-title{
      font-weight:800; letter-spacing:-.02em;
      display:flex; align-items:center; gap:.5rem;
    }
    .form-title .lock{
      width:28px;height:28px;border-radius:8px;
      display:inline-grid;place-items:center;
      background:#fff3c6; color:#a16207;
      font-size:18px; border:1px solid #fde68a;
    }
    .subtle{color:var(--muted); font-size:.95rem; margin-top:4px; margin-bottom:16px;}

    .form-control{
      border:1px solid #e5e7eb !important;
      padding:.8rem .95rem; border-radius:12px;
      box-shadow: 0 1px 0 rgba(16,24,40,.02);
    }
    .form-control:focus{
      border-color:#cce0ff !important;
      box-shadow: 0 0 0 4px rgba(19,99,255,.12);
    }

    .btn-primary{
      background: var(--brand); border-color: var(--brand);
      padding:.9rem 1rem; border-radius:12px; font-weight:700;
      box-shadow: 0 6px 16px rgba(19,99,255,.25);
    }
    .btn-primary:hover{ background:var(--brand-600); border-color:var(--brand-600); }

    .link{ color:var(--brand); text-decoration:none; font-weight:600; }
    .link:hover{ text-decoration:underline; }

    .remember-row{ display:flex; justify-content:space-between; align-items:center; gap:12px; }

    /* Ícono mostrar/ocultar */
    .toggle-pass{ position:absolute; right:10px; top:70%; transform:translateY(-50%); cursor:pointer; opacity:.6; }

    /* Responsivo */
    @media (max-width: 900px){
      .auth-shell{ grid-template-columns: 1fr; }
      .right-pane::before{ display:none; }
      .left-pane{ padding-bottom:16px; }
    }
  </style>
</head>
<body>
  <div class="d-flex align-items-center justify-content-center min-vh-100 px-3">
    <div class="auth-shell">
      <!-- Bienvenida -->
      <div class="left-pane">
        <div class="brand-badge">
          <i>✨</i> Luvadak
        </div>

        <div class="welcome">
          <h1>Bienvenido de vuelta</h1>
          <p>Accede al panel de administración para gestionar ventas, empleados y reportes.</p>
        </div>

        <div class="chips mb-3">
          <span class="chip"><i></i> Seguridad</span>
          <span class="chip"><i></i> Rapidez</span>
          <span class="chip"><i></i> Reportes</span>
        </div>

        <div class="copyright">© 2025 Luvadak. Todos los derechos reservados.</div>
      </div>

      <!-- Login -->
      <div class="right-pane">
        <h2 class="form-title mb-1"><span class="lock">🔒</span> Iniciar sesión</h2>
        <div class="subtle">Usa tus credenciales de administrador.</div>

        <form action="procesar_login.php" method="POST" class="mt-3">
          <div class="mb-3">
            <label for="usuario" class="form-label">Usuario</label>
            <input class="form-control" type="text" id="usuario" name="usuario" placeholder="Ej: admin01" required>
          </div>

          <div class="mb-3 position-relative">
            <label for="contrasena" class="form-label">Contraseña</label>
            <input class="form-control pe-5" type="password" id="contrasena" name="contrasena" placeholder="Tu contraseña" required>
            <!-- ojo/ocultar -->
            <svg id="togglePass" class="toggle-pass" xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M2.036 12.322a1.012 1.012 0 010-.644C3.423 7.51 7.36 5 12 5c4.64 0 8.577 2.51 9.964 6.678.07.2.07.444 0 .644C20.577 16.49 16.64 19 12 19c-4.64 0-8.577-2.51-9.964-6.678z"/>
              <circle cx="12" cy="12" r="3" stroke-width="1.6"/>
            </svg>
          </div>

          <div class="remember-row mb-3">
            <div class="form-check m-0">
              <input class="form-check-input" type="checkbox" id="remember">
              <label class="form-check-label" for="remember">Recordarme</label>
            </div>
            <a class="link" href="registro.php">¿Sin cuenta? Regístrate</a>
          </div>

          <button class="btn btn-primary w-100" type="submit">
            ➡️ Entrar
          </button>
        </form>
      </div>
    </div>
  </div>

  <script>
    // Mostrar/ocultar contraseña
    const passInput = document.getElementById('contrasena');
    const toggler = document.getElementById('togglePass');
    toggler?.addEventListener('click', () => {
      const isPwd = passInput.type === 'password';
      passInput.type = isPwd ? 'text' : 'password';
      toggler.style.opacity = isPwd ? 1 : .6;
    });
  </script>
</body>
</html>