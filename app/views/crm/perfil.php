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
$initial   = mb_strtoupper(mb_substr($usuarioPerfil['nombre'] ?? 'U', 0, 1, 'UTF-8'), 'UTF-8');
$diasCuenta = $diasCuenta ?? 1;
$totalCursos = $totalCursos ?? 0;
$totalActividad = $totalActividad ?? 0;
$actividadReciente = $actividadReciente ?? [];
$cuentaFecha = date('d/m/Y', strtotime($usuarioPerfil['creado_en'] ?? 'now'));
?>

<div class="crm-page-header">
  <div>
    <h1>Mi Perfil</h1>
    <p>Gestiona tu información personal y seguridad de cuenta.</p>
  </div>
  <div class="crm-page-actions">
    <a href="<?= $crmBase ?>ajustes" class="crm-btn crm-btn-secondary crm-btn-sm">
      <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><circle cx="12" cy="12" r="3"/></svg>
      Ajustes
    </a>
  </div>
</div>

<!-- Stats strip -->
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:20px">
  <?php foreach ([
    ['Días de cuenta', $diasCuenta, '#7c3aed', 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
    ['Matriculaciones', $totalCursos, '#3b82f6', 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13'],
    ['Acciones CRM', $totalActividad, '#10b981', 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2'],
    ['Cuenta creada', $cuentaFecha, '#f59e0b', 'M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
  ] as [$label, $val, $color, $path]): ?>
  <div class="crm-card" style="text-align:center;padding:16px 12px">
    <div style="width:36px;height:36px;border-radius:10px;background:<?= $color ?>18;display:flex;align-items:center;justify-content:center;margin:0 auto 10px;color:<?= $color ?>">
      <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="<?= $path ?>"/></svg>
    </div>
    <div style="font-size:18px;font-weight:800;color:var(--crm-text)"><?= htmlspecialchars((string)$val) ?></div>
    <div style="font-size:11px;color:var(--crm-muted);margin-top:2px"><?= $label ?></div>
  </div>
  <?php endforeach; ?>
</div>

<!-- Main grid -->
<div style="display:grid;grid-template-columns:300px 1fr;gap:20px;align-items:start">

  <!-- Left: identity card -->
  <div style="display:flex;flex-direction:column;gap:16px">

    <div class="crm-card" style="text-align:center;padding:28px 20px">
      <!-- Avatar with upload button -->
      <div style="position:relative;display:inline-block;margin-bottom:16px">
        <div style="width:88px;height:88px;border-radius:22px;background:var(--crm-primary);color:#fff;font-size:32px;font-weight:800;display:flex;align-items:center;justify-content:center;margin:0 auto;overflow:hidden">
          <?php if (!empty($usuarioPerfil['foto'])): ?>
            <img src="<?= BASE_URL ?>/uploads/<?= htmlspecialchars($usuarioPerfil['foto']) ?>" alt="foto" style="width:100%;height:100%;object-fit:cover">
          <?php else: ?>
            <?= $initial ?>
          <?php endif; ?>
        </div>
        <button onclick="document.getElementById('fotoInput').click()" title="Cambiar foto"
          style="position:absolute;bottom:-4px;right:-4px;width:28px;height:28px;border-radius:50%;background:var(--crm-primary);color:#fff;border:2px solid #fff;display:flex;align-items:center;justify-content:center;cursor:pointer;box-shadow:0 2px 8px rgba(124,58,237,.4)">
          <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><circle cx="12" cy="13" r="3"/></svg>
        </button>
        <input type="file" id="fotoInput" accept="image/*" style="display:none" onchange="subirFoto(this)">
      </div>

      <div style="font-size:17px;font-weight:700;color:var(--crm-text);margin-bottom:4px"><?= htmlspecialchars($usuarioPerfil['nombre'] ?? '') ?></div>
      <div style="font-size:12.5px;color:var(--crm-muted);margin-bottom:12px"><?= htmlspecialchars($usuarioPerfil['email'] ?? '') ?></div>
      <span class="crm-badge <?= $rolKey ?>" style="font-size:11.5px;padding:5px 14px"><?= $rolLabel ?></span>

      <?php if (!empty($usuarioPerfil['bio'])): ?>
        <p style="font-size:12.5px;color:var(--crm-muted);margin:14px 0 0;line-height:1.5;text-align:left;background:var(--crm-bg);padding:10px 12px;border-radius:8px">
          <?= htmlspecialchars($usuarioPerfil['bio']) ?>
        </p>
      <?php endif; ?>
    </div>

    <!-- Account details -->
    <div class="crm-card">
      <h3 class="crm-card-title" style="font-size:13px;margin-bottom:14px">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path stroke-linecap="round" d="M12 8v4m0 4h.01"/></svg>
        Información de cuenta
      </h3>
      <?php foreach ([
        ['Idioma', ucfirst($usuarioPerfil['idioma'] ?? 'Español')],
        ['Privacidad', ucfirst($usuarioPerfil['privacidad'] ?? 'Público')],
        ['Notificaciones', empty($usuarioPerfil['notificaciones']) ? 'Desactivadas' : 'Activadas'],
        ['Registrado', date('d M Y', strtotime($usuarioPerfil['creado_en'] ?? 'now'))],
      ] as [$key, $val]): ?>
      <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid var(--crm-border);font-size:13px">
        <span style="color:var(--crm-muted)"><?= $key ?></span>
        <strong style="color:var(--crm-text)"><?= htmlspecialchars($val) ?></strong>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Recent activity -->
    <?php if (!empty($actividadReciente)): ?>
    <div class="crm-card">
      <h3 class="crm-card-title" style="font-size:13px;margin-bottom:12px">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path stroke-linecap="round" d="M12 8v4l3 3"/></svg>
        Actividad reciente
      </h3>
      <?php foreach ($actividadReciente as $act): ?>
      <div style="display:flex;gap:10px;padding:8px 0;border-bottom:1px solid var(--crm-border);align-items:flex-start">
        <div style="width:6px;height:6px;border-radius:50%;background:var(--crm-primary);flex-shrink:0;margin-top:5px"></div>
        <div>
          <div style="font-size:12.5px;color:var(--crm-text);font-weight:500"><?= htmlspecialchars($act['titulo']) ?></div>
          <div style="font-size:11px;color:var(--crm-muted);margin-top:1px"><?= date('d/m/Y H:i', strtotime($act['creado_en'])) ?></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

  </div>

  <!-- Right: forms -->
  <div style="display:flex;flex-direction:column;gap:16px">

    <!-- Edit profile -->
    <div class="crm-card">
      <h3 class="crm-card-title">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
        Información personal
      </h3>
      <div class="crm-form-row">
        <div class="crm-form-group">
          <label class="crm-label">Nombre completo <span style="color:var(--crm-danger)">*</span></label>
          <input type="text" class="crm-input" id="pNombre" value="<?= htmlspecialchars($usuarioPerfil['nombre'] ?? '') ?>" placeholder="Tu nombre completo">
        </div>
        <div class="crm-form-group">
          <label class="crm-label">Email</label>
          <input type="email" class="crm-input" value="<?= htmlspecialchars($usuarioPerfil['email'] ?? '') ?>" disabled style="background:var(--crm-bg);color:var(--crm-muted)">
          <div class="crm-form-hint">El email no se puede cambiar desde aquí.</div>
        </div>
      </div>
      <div class="crm-form-group">
        <label class="crm-label">Biografía / Descripción</label>
        <textarea class="crm-textarea" id="pBio" rows="3" placeholder="Cuéntanos algo sobre ti… cargo, especialidad, experiencia."><?= htmlspecialchars($usuarioPerfil['bio'] ?? '') ?></textarea>
        <div class="crm-form-hint">Visible para otros administradores.</div>
      </div>
      <div style="display:flex;gap:10px;align-items:center">
        <button class="crm-btn crm-btn-primary" id="btnGuardarPerfil" onclick="guardarPerfil()">
          <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M5 13l4 4L19 7"/></svg>
          Guardar cambios
        </button>
        <span id="perfilStatus" style="font-size:12.5px;display:none"></span>
      </div>
    </div>

    <!-- Change password -->
    <div class="crm-card">
      <h3 class="crm-card-title">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path stroke-linecap="round" d="M7 11V7a5 5 0 0110 0v4"/></svg>
        Cambiar contraseña
      </h3>

      <!-- Password strength tips -->
      <div style="background:rgba(59,130,246,.06);border:1px solid rgba(59,130,246,.15);border-radius:8px;padding:10px 14px;margin-bottom:16px;font-size:12px;color:var(--crm-muted);display:flex;gap:8px">
        <svg width="15" height="15" fill="none" stroke="var(--crm-info)" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0;margin-top:1px"><circle cx="12" cy="12" r="10"/><path stroke-linecap="round" d="M12 8v4m0 4h.01"/></svg>
        <span>Usa mínimo 8 caracteres con letras, números y símbolos para una contraseña segura.</span>
      </div>

      <div class="crm-form-row">
        <div class="crm-form-group">
          <label class="crm-label">Contraseña actual</label>
          <div style="position:relative">
            <input type="password" class="crm-input" id="passActual" placeholder="Tu contraseña actual" style="padding-right:38px">
            <button type="button" onclick="togglePass('passActual',this)" style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--crm-muted);padding:0">
              <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
            </button>
          </div>
        </div>
        <div class="crm-form-group">
          <label class="crm-label">Nueva contraseña</label>
          <div style="position:relative">
            <input type="password" class="crm-input" id="passNueva" placeholder="Mínimo 6 caracteres" style="padding-right:38px" oninput="checkPassStrength(this.value)">
            <button type="button" onclick="togglePass('passNueva',this)" style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--crm-muted);padding:0">
              <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
            </button>
          </div>
          <!-- Strength bar -->
          <div id="passStrengthWrap" style="margin-top:6px;display:none">
            <div style="height:3px;background:var(--crm-border);border-radius:99px;overflow:hidden">
              <div id="passStrengthBar" style="height:100%;border-radius:99px;transition:width .3s,background .3s;width:0%"></div>
            </div>
            <span id="passStrengthLabel" style="font-size:11px;color:var(--crm-muted);margin-top:3px;display:block"></span>
          </div>
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

    <!-- Sessions / Security info -->
    <div class="crm-card">
      <h3 class="crm-card-title">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
        Seguridad de la cuenta
      </h3>
      <div style="display:flex;flex-direction:column;gap:0">
        <div style="display:flex;justify-content:space-between;align-items:center;padding:12px 0;border-bottom:1px solid var(--crm-border)">
          <div>
            <div style="font-size:13.5px;font-weight:600;color:var(--crm-text)">Sesión actual</div>
            <div style="font-size:11.5px;color:var(--crm-muted);margin-top:1px"><?= htmlspecialchars($_SERVER['HTTP_USER_AGENT'] ?? 'Navegador desconocido') ?></div>
          </div>
          <span class="crm-badge activo">Activa</span>
        </div>
        <div style="display:flex;justify-content:space-between;align-items:center;padding:12px 0;border-bottom:1px solid var(--crm-border)">
          <div>
            <div style="font-size:13.5px;font-weight:600;color:var(--crm-text)">Autenticación en dos pasos</div>
            <div style="font-size:11.5px;color:var(--crm-muted);margin-top:1px">Añade una capa extra de seguridad</div>
          </div>
          <span class="crm-badge inactivo">No configurado</span>
        </div>
        <div style="display:flex;justify-content:space-between;align-items:center;padding:12px 0">
          <div>
            <div style="font-size:13.5px;font-weight:600;color:var(--crm-text)">IP de acceso</div>
            <div style="font-size:11.5px;color:var(--crm-muted);margin-top:1px"><?= htmlspecialchars($_SERVER['REMOTE_ADDR'] ?? '—') ?></div>
          </div>
          <span style="font-size:11px;color:var(--crm-muted);background:var(--crm-bg);padding:3px 8px;border-radius:6px;border:1px solid var(--crm-border)">Local</span>
        </div>
      </div>
    </div>

  </div>
</div>

<script>
async function guardarPerfil() {
  const btn = document.getElementById('btnGuardarPerfil');
  const status = document.getElementById('perfilStatus');
  btn.disabled = true;
  btn.innerHTML = '<div class="crm-spinner" style="width:14px;height:14px;border-width:2px"></div> Guardando…';

  const res = await CRM.api('actualizar_perfil', {
    nombre: document.getElementById('pNombre').value.trim(),
    bio:    document.getElementById('pBio').value.trim(),
  });

  btn.disabled = false;
  btn.innerHTML = '<svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M5 13l4 4L19 7"/></svg> Guardar cambios';

  if (res.ok) {
    CRM.toast(res.mensaje, 'success');
    status.style.color = 'var(--crm-success)';
    status.textContent = '✓ Guardado';
    status.style.display = 'block';
    setTimeout(() => status.style.display = 'none', 3000);
  } else {
    CRM.toast(res.error, 'error');
  }
}

async function cambiarPassword() {
  const actual = document.getElementById('passActual').value;
  const nueva  = document.getElementById('passNueva').value;
  const conf   = document.getElementById('passConf').value;

  if (nueva !== conf) { CRM.toast('Las contraseñas no coinciden', 'error'); return; }
  if (nueva.length < 6) { CRM.toast('La contraseña debe tener mínimo 6 caracteres', 'error'); return; }

  const res = await CRM.api('cambiar_contrasena', { actual, nueva, confirmar: conf });
  if (res.ok) {
    CRM.toast(res.mensaje, 'success');
    ['passActual','passNueva','passConf'].forEach(id => document.getElementById(id).value = '');
    document.getElementById('passStrengthWrap').style.display = 'none';
  } else {
    CRM.toast(res.error, 'error');
  }
}

function togglePass(id, btn) {
  const input = document.getElementById(id);
  const isPass = input.type === 'password';
  input.type = isPass ? 'text' : 'password';
  btn.style.color = isPass ? 'var(--crm-primary)' : 'var(--crm-muted)';
}

function checkPassStrength(v) {
  const wrap = document.getElementById('passStrengthWrap');
  const bar  = document.getElementById('passStrengthBar');
  const lbl  = document.getElementById('passStrengthLabel');
  if (!v) { wrap.style.display='none'; return; }
  wrap.style.display = 'block';
  let score = 0;
  if (v.length >= 8) score++;
  if (/[A-Z]/.test(v)) score++;
  if (/[0-9]/.test(v)) score++;
  if (/[^A-Za-z0-9]/.test(v)) score++;
  const levels = [
    [25, '#ef4444', 'Muy débil'],
    [50, '#f59e0b', 'Débil'],
    [75, '#3b82f6', 'Moderada'],
    [100,'#10b981', 'Fuerte'],
  ];
  const [w, c, t] = levels[Math.min(score, 3)];
  bar.style.width = w+'%'; bar.style.background = c;
  lbl.textContent = t; lbl.style.color = c;
}

async function subirFoto(input) {
  const file = input.files[0];
  if (!file) return;
  const fd = new FormData();
  fd.append('foto', file);
  CRM.toast('Subida de foto no implementada aún — configura el endpoint de subida.', 'info');
  input.value = '';
}
</script>
