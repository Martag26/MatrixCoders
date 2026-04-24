<?php /* Perfil del usuario CRM */ ?>

<div class="crm-page-header">
  <div><h1>Mi Perfil</h1><p>Gestiona tu información personal en el CRM.</p></div>
</div>

<?php
$rolKey = match(true) {
  ($esSuperAdmin)  => 'superadmin',
  ($esAdmin)       => 'admin',
  ($esModerador)   => 'moderador',
  default          => 'usuario',
};
$rolLabel = match($rolKey) {
  'superadmin' => 'Superadmin', 'admin' => 'Administrador', 'moderador' => 'Moderador', default => 'Usuario'
};
$initial = mb_strtoupper(mb_substr($usuarioPerfil['nombre']??'U',0,1,'UTF-8'), 'UTF-8');
?>

<div style="display:grid;grid-template-columns:320px 1fr;gap:20px;align-items:start" class="crm-charts-grid">

  <!-- Left: avatar card -->
  <div class="crm-card" style="text-align:center">
    <div class="crm-profile-avatar-big" style="margin:0 auto 16px;width:90px;height:90px;font-size:32px">
      <?php if (!empty($usuarioPerfil['foto'])): ?>
        <img src="<?= BASE_URL ?>/uploads/<?= htmlspecialchars($usuarioPerfil['foto']) ?>" alt="foto">
      <?php else: ?>
        <?= $initial ?>
      <?php endif; ?>
    </div>
    <div style="font-size:18px;font-weight:700;margin-bottom:4px"><?= htmlspecialchars($usuarioPerfil['nombre']??'') ?></div>
    <div style="font-size:13px;color:var(--crm-muted);margin-bottom:12px"><?= htmlspecialchars($usuarioPerfil['email']??'') ?></div>
    <span class="crm-badge <?= $rolKey ?>" style="font-size:12px;padding:5px 12px"><?= $rolLabel ?></span>
    <hr class="crm-divider">
    <div style="text-align:left;font-size:13px;color:var(--crm-muted)">
      <div style="display:flex;justify-content:space-between;margin-bottom:6px">
        <span>Idioma</span><strong style="color:var(--crm-text)"><?= htmlspecialchars($usuarioPerfil['idioma']??'es') ?></strong>
      </div>
      <div style="display:flex;justify-content:space-between;margin-bottom:6px">
        <span>Privacidad</span><strong style="color:var(--crm-text)"><?= ucfirst($usuarioPerfil['privacidad']??'publico') ?></strong>
      </div>
      <div style="display:flex;justify-content:space-between">
        <span>Registrado</span><strong style="color:var(--crm-text)"><?= date('d/m/Y', strtotime($usuarioPerfil['creado_en']??'now')) ?></strong>
      </div>
    </div>
  </div>

  <!-- Right: forms -->
  <div style="display:flex;flex-direction:column;gap:16px">

    <!-- Edit profile -->
    <div class="crm-card">
      <h3 class="crm-card-title">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
        Editar información
      </h3>
      <div class="crm-form-row">
        <div class="crm-form-group">
          <label class="crm-label">Nombre completo *</label>
          <input type="text" class="crm-input" id="pNombre" value="<?= htmlspecialchars($usuarioPerfil['nombre']??'') ?>">
        </div>
        <div class="crm-form-group">
          <label class="crm-label">Email</label>
          <input type="email" class="crm-input" value="<?= htmlspecialchars($usuarioPerfil['email']??'') ?>" disabled style="background:var(--crm-bg)">
          <div class="crm-form-hint">El email no se puede cambiar desde aquí.</div>
        </div>
      </div>
      <div class="crm-form-group">
        <label class="crm-label">Biografía / Descripción</label>
        <textarea class="crm-textarea" id="pBio" rows="3" placeholder="Cuéntanos algo sobre ti…"><?= htmlspecialchars($usuarioPerfil['bio']??'') ?></textarea>
      </div>
      <button class="crm-btn crm-btn-primary" onclick="guardarPerfil()">
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M5 13l4 4L19 7"/></svg>
        Guardar cambios
      </button>
    </div>

    <!-- Change password -->
    <div class="crm-card">
      <h3 class="crm-card-title">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path stroke-linecap="round" d="M7 11V7a5 5 0 0110 0v4"/></svg>
        Cambiar contraseña
      </h3>
      <div class="crm-form-row">
        <div class="crm-form-group">
          <label class="crm-label">Contraseña actual</label>
          <input type="password" class="crm-input" id="passActual" placeholder="Tu contraseña actual">
        </div>
        <div class="crm-form-group">
          <label class="crm-label">Nueva contraseña</label>
          <input type="password" class="crm-input" id="passNueva" placeholder="Mínimo 6 caracteres">
        </div>
      </div>
      <div class="crm-form-group">
        <label class="crm-label">Confirmar nueva contraseña</label>
        <input type="password" class="crm-input" id="passConf" placeholder="Repite la nueva contraseña">
      </div>
      <button class="crm-btn crm-btn-warning" onclick="cambiarPassword()">
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2"/><path stroke-linecap="round" d="M7 11V7a5 5 0 0110 0v4"/></svg>
        Cambiar contraseña
      </button>
    </div>

  </div>
</div>

<script>
async function guardarPerfil() {
  const res = await CRM.api('actualizar_perfil', {
    nombre: document.getElementById('pNombre').value,
    bio:    document.getElementById('pBio').value,
  });
  if (res.ok) CRM.toast(res.mensaje, 'success');
  else CRM.toast(res.error, 'error');
}

async function cambiarPassword() {
  const res = await CRM.api('cambiar_contrasena', {
    actual:    document.getElementById('passActual').value,
    nueva:     document.getElementById('passNueva').value,
    confirmar: document.getElementById('passConf').value,
  });
  if (res.ok) {
    CRM.toast(res.mensaje, 'success');
    ['passActual','passNueva','passConf'].forEach(id => document.getElementById(id).value = '');
  } else CRM.toast(res.error, 'error');
}
</script>
