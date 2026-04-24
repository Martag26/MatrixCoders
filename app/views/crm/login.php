<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Matrix CRM — Acceso</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= BASE_URL ?>/css/crm.css">
<style>
  html, body {
    height: 100%;
    margin: 0;
    background: var(--crm-sidebar-bg, #12101e);
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: 'Inter', sans-serif;
  }

  .crm-login-wrap {
    width: 100%;
    max-width: 420px;
    padding: 24px;
  }

  .crm-login-brand {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 36px;
    justify-content: center;
  }

  .crm-login-brand-icon {
    width: 44px;
    height: 44px;
    background: var(--crm-primary, #7c3aed);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    flex-shrink: 0;
  }

  .crm-login-brand-text {
    display: flex;
    flex-direction: column;
  }

  .crm-login-brand-name {
    font-size: 20px;
    font-weight: 800;
    color: #fff;
    letter-spacing: -0.3px;
  }

  .crm-login-brand-sub {
    font-size: 12px;
    color: rgba(255,255,255,0.45);
    font-weight: 500;
  }

  .crm-login-card {
    background: var(--crm-card-bg, #1e1b2e);
    border: 1px solid var(--crm-border, rgba(255,255,255,0.07));
    border-radius: 18px;
    padding: 36px 32px;
    box-shadow: 0 24px 64px rgba(0,0,0,0.45);
  }

  .crm-login-card h1 {
    font-size: 22px;
    font-weight: 800;
    color: var(--crm-text, #f1f0fb);
    margin: 0 0 6px;
    letter-spacing: -0.3px;
  }

  .crm-login-card p {
    font-size: 13.5px;
    color: var(--crm-muted, rgba(241,240,251,0.45));
    margin: 0 0 28px;
  }

  .crm-login-group {
    margin-bottom: 18px;
  }

  .crm-login-group label {
    display: block;
    font-size: 12.5px;
    font-weight: 600;
    color: var(--crm-muted, rgba(241,240,251,0.5));
    margin-bottom: 7px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }

  .crm-login-group input {
    width: 100%;
    background: rgba(255,255,255,0.05);
    border: 1px solid var(--crm-border, rgba(255,255,255,0.09));
    border-radius: 10px;
    padding: 11px 14px;
    color: var(--crm-text, #f1f0fb);
    font-size: 14px;
    font-family: inherit;
    outline: none;
    transition: border-color .15s, box-shadow .15s;
    box-sizing: border-box;
  }

  .crm-login-group input:focus {
    border-color: var(--crm-primary, #7c3aed);
    box-shadow: 0 0 0 3px rgba(124,58,237,0.2);
  }

  .crm-login-btn {
    width: 100%;
    padding: 12px;
    background: var(--crm-primary, #7c3aed);
    color: #fff;
    border: none;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 700;
    font-family: inherit;
    cursor: pointer;
    transition: background .15s, transform .1s;
    margin-top: 8px;
  }

  .crm-login-btn:hover {
    background: var(--crm-primary-dark, #6d28d9);
  }

  .crm-login-btn:active {
    transform: scale(0.99);
  }

  .crm-login-error {
    background: rgba(239,68,68,0.12);
    border: 1px solid rgba(239,68,68,0.3);
    border-radius: 10px;
    padding: 11px 14px;
    color: #f87171;
    font-size: 13px;
    margin-bottom: 18px;
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .crm-login-footer {
    text-align: center;
    margin-top: 24px;
    font-size: 12.5px;
    color: rgba(255,255,255,0.25);
  }

  .crm-login-footer a {
    color: var(--crm-primary, #7c3aed);
    text-decoration: none;
  }
</style>
</head>
<body>

<div class="crm-login-wrap">

  <!-- Brand -->
  <div class="crm-login-brand">
    <div class="crm-login-brand-icon">
      <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2V9M9 21H5a2 2 0 01-2-2V9m0 0h18"/>
      </svg>
    </div>
    <div class="crm-login-brand-text">
      <span class="crm-login-brand-name">Matrix CRM</span>
      <span class="crm-login-brand-sub">Área de administración</span>
    </div>
  </div>

  <!-- Card -->
  <div class="crm-login-card">
    <h1>Bienvenido</h1>
    <p>Accede con tu cuenta de administrador o moderador.</p>

    <?php if (!empty($loginError)): ?>
    <div class="crm-login-error">
      <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path stroke-linecap="round" d="M12 8v4m0 4h.01"/></svg>
      <?= htmlspecialchars($loginError) ?>
    </div>
    <?php endif; ?>

    <form method="POST" action="/matrixcoders/admin/index.php" autocomplete="on">
      <input type="hidden" name="crm_login" value="1">

      <div class="crm-login-group">
        <label for="email">Correo electrónico</label>
        <input type="email" id="email" name="email" required autofocus
               placeholder="admin@example.com"
               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
      </div>

      <div class="crm-login-group">
        <label for="password">Contraseña</label>
        <input type="password" id="password" name="password" required
               placeholder="••••••••">
      </div>

      <button type="submit" class="crm-login-btn">Entrar al CRM</button>
    </form>
  </div>

  <div class="crm-login-footer">
    <a href="<?= BASE_URL ?>/index.php">Volver al sitio principal</a>
  </div>

</div>

</body>
</html>
