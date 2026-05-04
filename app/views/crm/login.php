<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Matrix CRM — Acceso</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
*, *::before, *::after { box-sizing: border-box; }

html, body {
  height: 100%;
  margin: 0;
  background: #12101e;
  display: flex;
  align-items: center;
  justify-content: center;
  font-family: 'Inter', system-ui, sans-serif;
}

.login-wrap {
  width: 100%;
  max-width: 400px;
  padding: 24px;
}

/* Brand */
.login-brand {
  display: flex;
  align-items: center;
  gap: 12px;
  justify-content: center;
  margin-bottom: 32px;
}
.login-brand-icon {
  width: 42px; height: 42px;
  background: #7c3aed;
  border-radius: 11px;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0;
  box-shadow: 0 6px 20px rgba(124,58,237,.4);
}
.login-brand-name {
  font-size: 18px; font-weight: 800;
  color: #fff; letter-spacing: -.2px;
}
.login-brand-sub {
  font-size: 11px; color: rgba(255,255,255,.35);
  font-weight: 500; text-transform: uppercase; letter-spacing: .8px;
}

/* Card */
.login-card {
  background: #1e1b2e;
  border: 1px solid rgba(255,255,255,.08);
  border-radius: 16px;
  padding: 32px 28px;
  box-shadow: 0 24px 60px rgba(0,0,0,.5);
}
.login-card h1 {
  font-size: 20px; font-weight: 800;
  color: #f1f0fb; margin: 0 0 6px;
  letter-spacing: -.3px;
}
.login-card p {
  font-size: 13.5px; color: rgba(241,240,251,.45);
  margin: 0 0 26px; line-height: 1.5;
}

/* Groups */
.login-group { margin-bottom: 16px; }

.login-label {
  display: block;
  font-size: 12px; font-weight: 600;
  color: rgba(241,240,251,.55);
  margin-bottom: 7px;
  text-transform: uppercase; letter-spacing: .5px;
}

.login-input-wrap { position: relative; }

.login-input-icon {
  position: absolute; left: 11px; top: 50%; transform: translateY(-50%);
  color: rgba(255,255,255,.25); pointer-events: none;
  display: flex; align-items: center;
}

.login-input {
  width: 100%;
  padding: 11px 14px 11px 36px;
  background: rgba(255,255,255,.06);
  border: 1px solid rgba(255,255,255,.1);
  border-radius: 9px;
  font-size: 14px; font-family: inherit;
  color: #f1f0fb;
  caret-color: #a78bfa;
  outline: none;
  transition: border-color .15s, box-shadow .15s, background .15s;
}
.login-input::placeholder { color: rgba(255,255,255,.25); }
.login-input:focus {
  border-color: #7c3aed;
  background: rgba(255,255,255,.09);
  box-shadow: 0 0 0 3px rgba(124,58,237,.18);
}
.login-input.with-toggle { padding-right: 40px; }

.login-toggle {
  position: absolute; right: 11px; top: 50%; transform: translateY(-50%);
  background: none; border: none; cursor: pointer;
  color: rgba(255,255,255,.3); padding: 0;
  display: flex; align-items: center;
  transition: color .15s;
}
.login-toggle:hover { color: rgba(255,255,255,.6); }

/* Error */
.login-error {
  display: flex; align-items: flex-start; gap: 9px;
  background: rgba(239,68,68,.1);
  border: 1px solid rgba(239,68,68,.25);
  border-radius: 9px;
  padding: 11px 13px;
  margin-bottom: 18px;
  font-size: 13px; color: #f87171; font-weight: 500;
  line-height: 1.4;
}
.login-error svg { flex-shrink: 0; margin-top: 1px; }

/* Button */
.login-btn {
  width: 100%; margin-top: 8px;
  padding: 12px;
  background: #7c3aed;
  color: #fff; border: none;
  border-radius: 9px;
  font-size: 14px; font-weight: 700; font-family: inherit;
  cursor: pointer;
  transition: background .15s, box-shadow .15s, transform .1s;
  box-shadow: 0 4px 16px rgba(124,58,237,.3);
}
.login-btn:hover { background: #6d28d9; box-shadow: 0 6px 20px rgba(124,58,237,.45); }
.login-btn:active { transform: scale(.99); }

/* Footer */
.login-footer {
  text-align: center;
  margin-top: 20px;
  font-size: 12.5px;
  color: rgba(255,255,255,.2);
}
.login-footer a {
  color: rgba(124,58,237,.8); text-decoration: none;
  transition: color .15s;
}
.login-footer a:hover { color: #a78bfa; }
</style>
</head>
<body>

<div class="login-wrap">

  <div class="login-brand">
    <div class="login-brand-icon">
      <svg width="20" height="20" fill="none" stroke="#fff" stroke-width="2.2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2V9M9 21H5a2 2 0 01-2-2V9m0 0h18"/>
      </svg>
    </div>
    <div>
      <div class="login-brand-name">Matrix CRM</div>
      <div class="login-brand-sub">Área de administración</div>
    </div>
  </div>

  <div class="login-card">
    <h1>Bienvenido</h1>
    <p>Accede con tu cuenta de administrador o moderador.</p>

    <?php if (!empty($loginError)): ?>
    <div class="login-error">
      <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path stroke-linecap="round" d="M12 8v4m0 4h.01"/></svg>
      <?= htmlspecialchars($loginError) ?>
    </div>
    <?php endif; ?>

    <form method="POST" action="/matrixcoders/admin/index.php" autocomplete="on">
      <input type="hidden" name="crm_login" value="1">

      <div class="login-group">
        <label class="login-label" for="email">Correo electrónico</label>
        <div class="login-input-wrap">
          <span class="login-input-icon">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
          </span>
          <input type="email" id="email" name="email" class="login-input" required autofocus
                 placeholder="admin@example.com"
                 value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        </div>
      </div>

      <div class="login-group">
        <label class="login-label" for="password">Contraseña</label>
        <div class="login-input-wrap">
          <span class="login-input-icon">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2"/><path stroke-linecap="round" d="M7 11V7a5 5 0 0110 0v4"/></svg>
          </span>
          <input type="password" id="password" name="password" class="login-input with-toggle" required
                 placeholder="••••••••">
          <button type="button" class="login-toggle" onclick="togglePass()" title="Mostrar contraseña">
            <svg id="eyeIcon" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
          </button>
        </div>
      </div>

      <button type="submit" class="login-btn">Entrar al CRM</button>
    </form>
  </div>

  <div class="login-footer">
    <a href="<?= BASE_URL ?>/index.php">← Volver al sitio principal</a>
  </div>

</div>

<script>
function togglePass() {
  const input = document.getElementById('password');
  const icon  = document.getElementById('eyeIcon');
  const show  = input.type === 'password';
  input.type  = show ? 'text' : 'password';
  icon.innerHTML = show
    ? '<path stroke-linecap="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>'
    : '<path stroke-linecap="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>';
}
</script>

</body>
</html>
