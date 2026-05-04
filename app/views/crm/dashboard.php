<?php
$colores = ['#7c3aed','#3b82f6','#10b981','#f59e0b','#ef4444','#8b5cf6'];
$diasEs  = ['Domingo','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'];
$mesesEs = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
$fechaEs = $diasEs[date('w')] . ', ' . date('d') . ' de ' . $mesesEs[(int)date('n') - 1] . ' de ' . date('Y');
$rolLabel = match(true) {
  ($esSuperAdmin) => 'Superadmin',
  ($esAdmin)      => 'Administrador',
  ($esModerador)  => 'Moderador',
  default         => 'Usuario',
};
?>

<!-- Page header -->
<div class="crm-page-header">
  <div>
    <h1>Dashboard</h1>
    <p>Bienvenido/a, <strong><?= htmlspecialchars($usuario['nombre'] ?? '') ?></strong> &mdash; <?= $rolLabel ?></p>
  </div>
  <div class="crm-page-actions">
    <span style="font-size:12px;color:var(--crm-muted);background:var(--crm-bg);border:1px solid var(--crm-border);padding:6px 14px;border-radius:8px;display:flex;align-items:center;gap:6px">
      <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
      <?= $fechaEs ?>
    </span>
  </div>
</div>

<!-- Primary stat cards -->
<div class="crm-stats-grid">
  <div class="crm-stat-card blue">
    <div class="crm-stat-header">
      <div class="crm-stat-icon blue">
        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path stroke-linecap="round" d="M23 21v-2a4 4 0 00-3-3.87m-4-12a4 4 0 010 7.75"/></svg>
      </div>
      <span class="crm-stat-trend up">
        <svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M5 15l7-7 7 7"/></svg>
        +<?= $nuevosEsteMes ?? 0 ?> este mes
      </span>
    </div>
    <div class="crm-stat-value"><?= number_format($totalUsuarios) ?></div>
    <div class="crm-stat-label">Usuarios registrados</div>
    <div style="margin-top:10px;height:3px;background:rgba(59,130,246,.15);border-radius:99px">
      <div style="height:100%;width:<?= min(100, ($nuevosEsteMes / max(1,$totalUsuarios)) * 500) ?>%;background:var(--crm-info);border-radius:99px"></div>
    </div>
  </div>

  <div class="crm-stat-card purple">
    <div class="crm-stat-header">
      <div class="crm-stat-icon purple">
        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
      </div>
      <span class="crm-stat-trend neu"><?= $cursosActivos ?? $totalCursos ?> activos</span>
    </div>
    <div class="crm-stat-value"><?= number_format($totalCursos) ?></div>
    <div class="crm-stat-label">Cursos publicados</div>
    <div style="margin-top:10px;height:3px;background:rgba(124,58,237,.15);border-radius:99px">
      <div style="height:100%;width:<?= $totalCursos > 0 ? round(($cursosActivos/$totalCursos)*100) : 0 ?>%;background:var(--crm-primary);border-radius:99px"></div>
    </div>
  </div>

  <div class="crm-stat-card green">
    <div class="crm-stat-header">
      <div class="crm-stat-icon green">
        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
      </div>
      <span class="crm-stat-trend up">matriculaciones</span>
    </div>
    <div class="crm-stat-value"><?= number_format($totalMatriculas ?? 0) ?></div>
    <div class="crm-stat-label">Total matriculaciones</div>
    <div style="font-size:11px;color:var(--crm-muted);margin-top:4px">
      ~<?= $totalCursos > 0 ? round(($totalMatriculas ?? 0) / $totalCursos, 1) : 0 ?> por curso
    </div>
  </div>

  <div class="crm-stat-card orange">
    <div class="crm-stat-header">
      <div class="crm-stat-icon orange">
        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
      </div>
      <span class="crm-stat-trend up">activas</span>
    </div>
    <div class="crm-stat-value"><?= number_format($totalCampanas) ?></div>
    <div class="crm-stat-label">Campañas activas</div>
    <div style="font-size:11px;color:var(--crm-muted);margin-top:4px">en vigor ahora</div>
  </div>
</div>

<!-- Charts row -->
<div class="crm-charts-grid">
  <div class="crm-chart-card" style="border-top:3px solid #7c3aed">
    <div class="crm-chart-header">
      <div style="display:flex;align-items:center;gap:10px">
        <div style="width:32px;height:32px;border-radius:8px;background:rgba(124,58,237,.1);display:flex;align-items:center;justify-content:center;color:var(--crm-primary);flex-shrink:0">
          <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13"/></svg>
        </div>
        <div>
          <h3 class="crm-chart-title">Cursos más populares</h3>
          <p class="crm-chart-sub">Top 6 por matriculaciones</p>
        </div>
      </div>
      <span style="font-size:11px;color:var(--crm-muted);background:var(--crm-bg);padding:3px 8px;border-radius:6px;border:1px solid var(--crm-border)"><?= count($topCursos) ?> cursos</span>
    </div>
    <div style="height:230px;position:relative"><canvas id="chartTopCursos"></canvas></div>
  </div>

  <div class="crm-chart-card" style="border-top:3px solid #3b82f6">
    <div class="crm-chart-header">
      <div style="display:flex;align-items:center;gap:10px">
        <div style="width:32px;height:32px;border-radius:8px;background:rgba(59,130,246,.1);display:flex;align-items:center;justify-content:center;color:var(--crm-info);flex-shrink:0">
          <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
        </div>
        <div>
          <h3 class="crm-chart-title">Roles de usuarios</h3>
          <p class="crm-chart-sub">Distribución por rol</p>
        </div>
      </div>
      <span style="font-size:11px;color:var(--crm-muted);background:var(--crm-bg);padding:3px 8px;border-radius:6px;border:1px solid var(--crm-border)"><?= $totalUsuarios ?> total</span>
    </div>
    <div style="height:230px;position:relative"><canvas id="chartRoles"></canvas></div>
  </div>
</div>

<!-- Second charts row -->
<div class="crm-charts-grid" style="grid-template-columns:2fr 1fr">
  <div class="crm-chart-card" style="border-top:3px solid #10b981">
    <div class="crm-chart-header">
      <div style="display:flex;align-items:center;gap:10px">
        <div style="width:32px;height:32px;border-radius:8px;background:rgba(16,185,129,.1);display:flex;align-items:center;justify-content:center;color:var(--crm-success);flex-shrink:0">
          <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
        </div>
        <div>
          <h3 class="crm-chart-title">Nuevos registros</h3>
          <p class="crm-chart-sub">Evolución últimos 6 meses</p>
        </div>
      </div>
      <?php
        $totalReg6m = array_sum(array_column($actividad6m, 'total'));
        $mediaReg   = count($actividad6m) > 0 ? round($totalReg6m / count($actividad6m), 1) : 0;
      ?>
      <span style="font-size:11px;color:var(--crm-success);background:rgba(16,185,129,.1);padding:3px 8px;border-radius:6px;font-weight:600">~<?= $mediaReg ?>/mes</span>
    </div>
    <div style="height:190px;position:relative"><canvas id="chartRegistros"></canvas></div>
  </div>

  <div class="crm-chart-card" style="border-top:3px solid #f59e0b">
    <div class="crm-chart-header">
      <div style="display:flex;align-items:center;gap:10px">
        <div style="width:32px;height:32px;border-radius:8px;background:rgba(245,158,11,.1);display:flex;align-items:center;justify-content:center;color:var(--crm-warning);flex-shrink:0">
          <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
        </div>
        <div>
          <h3 class="crm-chart-title">Niveles de cursos</h3>
          <p class="crm-chart-sub">Distribución por nivel</p>
        </div>
      </div>
    </div>
    <div style="height:190px;position:relative"><canvas id="chartNiveles"></canvas></div>
  </div>
</div>

<!-- Bottom row -->
<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">

  <!-- Actividad reciente -->
  <div class="crm-card">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px">
      <h3 class="crm-card-title" style="margin:0;display:flex;align-items:center;gap:6px">
        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path stroke-linecap="round" d="M12 8v4l3 3"/></svg>
        Actividad reciente
      </h3>
      <a href="<?= $crmBase ?>logs" style="font-size:12px;color:var(--crm-primary);text-decoration:none">Ver todos →</a>
    </div>
    <div class="crm-activity-list">
      <?php if (empty($recientes)): ?>
        <div class="crm-empty" style="padding:20px 0">
          <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 8v4l3 3"/></svg>
          <p>Sin actividad reciente</p>
        </div>
      <?php else: ?>
        <?php foreach (array_slice($recientes, 0, 8) as $act):
          $dotClass = match($act['tipo'] ?? 'info') {
            'usuario' => 'success', 'curso' => 'info',
            'campana' => 'warning', 'incidencia' => 'danger', default => 'info'
          };
        ?>
        <div class="crm-activity-item">
          <div class="crm-activity-dot <?= $dotClass ?>"></div>
          <div class="crm-activity-text">
            <div class="crm-activity-msg"><?= htmlspecialchars($act['titulo']) ?></div>
            <div class="crm-activity-time">
              <?= $act['usuario_nombre'] ? htmlspecialchars($act['usuario_nombre']).' · ' : '' ?>
              <?= date('d/m/Y H:i', strtotime($act['creado_en'])) ?>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>

  <!-- Right column -->
  <div style="display:flex;flex-direction:column;gap:16px">

    <!-- Usuarios por rol -->
    <div class="crm-card">
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px">
        <h3 class="crm-card-title" style="font-size:13px;margin:0;display:flex;align-items:center;gap:6px">
          <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
          Usuarios por rol
        </h3>
        <?php if ($esAdmin): ?>
        <a href="<?= $crmBase ?>usuarios" style="font-size:11px;color:var(--crm-primary);text-decoration:none">Gestionar →</a>
        <?php endif; ?>
      </div>
      <?php
      $rolColors = ['Superadmin'=>'#7c3aed','Administrador'=>'#3b82f6','Moderador'=>'#f59e0b','Instructor'=>'#10b981','Alumno'=>'#6b7280'];
      foreach ($porRol as $r):
        $pct = $totalUsuarios > 0 ? round(($r['total']/$totalUsuarios)*100) : 0;
        $color = $rolColors[$r['etiqueta']] ?? '#7c3aed';
      ?>
      <div style="margin-bottom:10px">
        <div style="display:flex;justify-content:space-between;font-size:12.5px;margin-bottom:5px">
          <span style="color:var(--crm-text);display:flex;align-items:center;gap:6px">
            <span style="width:8px;height:8px;border-radius:50%;background:<?= $color ?>;display:inline-block"></span>
            <?= htmlspecialchars($r['etiqueta']) ?>
          </span>
          <span style="font-weight:700;color:var(--crm-text)"><?= $r['total'] ?> <span style="color:var(--crm-muted);font-weight:400">(<?= $pct ?>%)</span></span>
        </div>
        <div style="height:4px;background:var(--crm-border);border-radius:99px;overflow:hidden">
          <div style="height:100%;width:<?= $pct ?>%;background:<?= $color ?>;border-radius:99px;transition:width .4s"></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Acciones rápidas -->
    <div class="crm-card">
      <h3 class="crm-card-title" style="font-size:13px;margin-bottom:12px;display:flex;align-items:center;gap:6px">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
        Acciones rápidas
      </h3>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:6px">
        <?php if ($esAdmin): ?>
        <a href="<?= $crmBase ?>usuarios" class="crm-btn crm-btn-secondary crm-btn-sm" style="justify-content:flex-start">
          <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
          Usuarios
        </a>
        <a href="<?= $crmBase ?>cursos" class="crm-btn crm-btn-secondary crm-btn-sm" style="justify-content:flex-start">
          <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13"/></svg>
          Cursos
        </a>
        <a href="<?= $crmBase ?>campanas" class="crm-btn crm-btn-secondary crm-btn-sm" style="justify-content:flex-start">
          <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15"/></svg>
          Campañas
        </a>
        <?php endif; ?>
        <a href="<?= $crmBase ?>comunicacion" class="crm-btn crm-btn-secondary crm-btn-sm" style="justify-content:flex-start">
          <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8"/></svg>
          Soporte
        </a>
        <a href="<?= $crmBase ?>logs" class="crm-btn crm-btn-secondary crm-btn-sm" style="justify-content:flex-start">
          <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/></svg>
          Logs
        </a>
      </div>
    </div>
  </div>
</div>

<!-- Recent users -->
<?php if ($esAdmin && !empty($recentUsers)): ?>
<div class="crm-card" style="margin-top:16px">
  <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px">
    <h3 class="crm-card-title" style="margin:0;display:flex;align-items:center;gap:6px">
      <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
      Últimos usuarios registrados
    </h3>
    <a href="<?= $crmBase ?>usuarios" style="font-size:12px;color:var(--crm-primary);text-decoration:none">Ver todos →</a>
  </div>
  <div class="crm-table-wrap" style="margin:0">
    <table class="crm-table">
      <thead>
        <tr>
          <th>Usuario</th>
          <th>Rol</th>
          <th>Matriculaciones</th>
          <th>Registrado</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($recentUsers as $ru):
          $iniRu = mb_strtoupper(mb_substr($ru['nombre'], 0, 1, 'UTF-8'), 'UTF-8');
          $rolRu = match(true) {
            !empty($ru['es_superadmin']) => ['label'=>'Superadmin','class'=>'superadmin'],
            $ru['rol']==='ADMINISTRADOR' => ['label'=>'Admin','class'=>'admin'],
            $ru['rol']==='MODERADOR'     => ['label'=>'Moderador','class'=>'moderador'],
            $ru['rol']==='INSTRUCTOR'    => ['label'=>'Instructor','class'=>'instructor'],
            default                      => ['label'=>'Alumno','class'=>'alumno'],
          };
        ?>
        <tr>
          <td>
            <div class="crm-user-row">
              <div class="crm-user-row-avatar <?= $rolRu['class'] === 'admin' ? 'blue' : '' ?>"><?= $iniRu ?></div>
              <div>
                <div class="crm-user-row-name"><?= htmlspecialchars($ru['nombre']) ?></div>
                <div class="crm-user-row-email"><?= htmlspecialchars($ru['email']) ?></div>
              </div>
            </div>
          </td>
          <td><span class="crm-badge <?= $rolRu['class'] ?>"><?= $rolRu['label'] ?></span></td>
          <td><span style="font-weight:600"><?= $ru['cursos_count'] ?></span> cursos</td>
          <td style="font-size:12px;color:var(--crm-muted)"><?= date('d/m/Y', strtotime($ru['creado_en'])) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<script>
(function(){
  /* ─── Data ─── */
  const topCursosLabels = <?= json_encode(array_map(fn($c)=>mb_strimwidth($c['titulo'],0,22,'…'), $topCursos), JSON_UNESCAPED_UNICODE) ?>;
  const topCursosData   = <?= json_encode(array_column($topCursos,'total')) ?>;
  const rolesLabels     = <?= json_encode(array_column($porRol,'etiqueta'), JSON_UNESCAPED_UNICODE) ?>;
  const rolesData       = <?= json_encode(array_column($porRol,'total')) ?>;
  const registrosMeses  = <?= json_encode(array_column($actividad6m,'mes'), JSON_UNESCAPED_UNICODE) ?>;
  const registrosTotals = <?= json_encode(array_column($actividad6m,'total')) ?>;
  const nivelesLabels   = <?= json_encode(array_column($porNivel,'nivel'), JSON_UNESCAPED_UNICODE) ?>;
  const nivelesData     = <?= json_encode(array_column($porNivel,'total')) ?>;

  /* ─── Global defaults ─── */
  Chart.defaults.font.family  = "'Inter','Segoe UI',sans-serif";
  Chart.defaults.font.size    = 11;
  Chart.defaults.color        = '#6b7280';
  Chart.defaults.animation.duration = 900;

  const GRID  = 'rgba(0,0,0,.05)';
  const PAL   = ['#7c3aed','#6366f1','#3b82f6','#0ea5e9','#10b981','#f59e0b','#ef4444','#ec4899'];
  const TOTAL_ROLES = rolesData.reduce((a,b)=>a+b,0);

  /* ─── Tooltip shared style ─── */
  const tooltipBase = {
    backgroundColor:'rgba(17,22,37,.95)',
    borderColor:'rgba(124,58,237,.3)',
    borderWidth:1,
    cornerRadius:10,
    padding:{x:14,y:10},
    titleColor:'#fff',
    bodyColor:'rgba(255,255,255,.75)',
    titleFont:{weight:'700',size:12},
    bodyFont:{size:11},
    displayColors:false,
    boxShadow:'0 8px 24px rgba(0,0,0,.3)',
  };

  /* ─── Helper: gradient fill ─── */
  function vertGradient(ctx, color, alpha1=0.45, alpha2=0.02) {
    const g = ctx.createLinearGradient(0,0,0,ctx.canvas.height);
    g.addColorStop(0, color.replace(')',`,${alpha1})`).replace('rgb','rgba'));
    g.addColorStop(1, color.replace(')',`,${alpha2})`).replace('rgb','rgba'));
    return g;
  }
  function hexToRgb(h){ const r=parseInt(h.slice(1,3),16),g=parseInt(h.slice(3,5),16),b=parseInt(h.slice(5,7),16); return `rgb(${r},${g},${b})`; }

  /* ──────────────────────────────────────────
     CHART 1 — Top cursos (bar con degradados)
  ────────────────────────────────────────── */
  const ctx1 = document.getElementById('chartTopCursos').getContext('2d');
  const barGradients = topCursosData.map((_,i) => {
    const g = ctx1.createLinearGradient(0,0,0,260);
    const c = PAL[i % PAL.length];
    const rgb = hexToRgb(c);
    g.addColorStop(0, rgb.replace('rgb','rgba').replace(')',`,.9)`));
    g.addColorStop(1, rgb.replace('rgb','rgba').replace(')',`,.5)`));
    return g;
  });

  new Chart(ctx1, {
    type:'bar',
    data:{
      labels: topCursosLabels,
      datasets:[{
        data: topCursosData,
        backgroundColor: barGradients,
        borderRadius: { topLeft:8,topRight:8 },
        borderSkipped: false,
        borderColor: PAL.map(c=>c),
        borderWidth: 0,
        hoverBackgroundColor: PAL.map((c,i) => barGradients[i]),
        hoverBorderWidth: 2,
        hoverBorderColor: PAL,
      }]
    },
    options:{
      responsive:true, maintainAspectRatio:false,
      plugins:{
        legend:{display:false},
        tooltip:{...tooltipBase, callbacks:{
          title: ctx => ctx[0].label,
          label: ctx => `  ${ctx.parsed.y} alumno${ctx.parsed.y!==1?'s':''}`,
        }}
      },
      scales:{
        x:{ grid:{display:false}, ticks:{maxRotation:30,font:{size:10}} },
        y:{ grid:{color:GRID,drawBorder:false}, beginAtZero:true, ticks:{stepSize:1,precision:0} }
      }
    }
  });

  /* ──────────────────────────────────────────
     CHART 2 — Roles (doughnut con texto central)
  ────────────────────────────────────────── */
  const centerTextPlugin = {
    id:'centerText',
    afterDraw(chart){
      if (chart.config.type !== 'doughnut') return;
      const {width,height,ctx} = chart;
      ctx.save();
      const x = chart.getDatasetMeta(0).data[0]?.x || width/2;
      const y = chart.getDatasetMeta(0).data[0]?.y || height/2;
      ctx.textAlign='center'; ctx.textBaseline='middle';
      ctx.font='800 22px Inter,sans-serif'; ctx.fillStyle='#1a1625';
      ctx.fillText(TOTAL_ROLES, x, y-6);
      ctx.font='600 10px Inter,sans-serif'; ctx.fillStyle='#6b7280';
      ctx.fillText('USUARIOS', x, y+10);
      ctx.restore();
    }
  };

  new Chart(document.getElementById('chartRoles'), {
    type:'doughnut',
    data:{
      labels: rolesLabels,
      datasets:[{
        data: rolesData,
        backgroundColor: PAL,
        borderWidth: 3,
        borderColor:'rgba(15,15,30,.6)',
        hoverBorderWidth: 3,
        hoverOffset: 8,
      }]
    },
    options:{
      responsive:true, maintainAspectRatio:false,
      cutout:'72%',
      plugins:{
        legend:{ position:'bottom', labels:{ padding:16, font:{size:11}, color:'#6b7280', boxWidth:10, boxHeight:10, borderRadius:3 } },
        tooltip:{...tooltipBase, callbacks:{
          label: ctx => `  ${ctx.label}: ${ctx.parsed} (${TOTAL_ROLES > 0 ? Math.round(ctx.parsed/TOTAL_ROLES*100):0}%)`,
        }}
      }
    },
    plugins:[centerTextPlugin]
  });

  /* ──────────────────────────────────────────
     CHART 3 — Registros (line con gradiente)
  ────────────────────────────────────────── */
  const ctx3 = document.getElementById('chartRegistros').getContext('2d');
  const lineGrad = ctx3.createLinearGradient(0,0,0,220);
  lineGrad.addColorStop(0, 'rgba(124,58,237,.35)');
  lineGrad.addColorStop(0.6,'rgba(124,58,237,.08)');
  lineGrad.addColorStop(1, 'rgba(124,58,237,0)');

  const maxReg = Math.max(...registrosTotals, 1);
  new Chart(ctx3, {
    type:'line',
    data:{
      labels: registrosMeses.map(m=>{ const [y,mo]=m.split('-'); return new Date(y,mo-1).toLocaleDateString('es-ES',{month:'short',year:'2-digit'}); }),
      datasets:[{
        data: registrosTotals,
        borderColor:'#7c3aed',
        borderWidth: 2.5,
        backgroundColor: lineGrad,
        fill: true,
        tension: .45,
        pointBackgroundColor:'#fff',
        pointBorderColor:'#7c3aed',
        pointBorderWidth: 2.5,
        pointRadius: ctx => ctx.raw === maxReg ? 7 : 4,
        pointHoverRadius: 7,
      }]
    },
    options:{
      responsive:true, maintainAspectRatio:false,
      plugins:{
        legend:{display:false},
        tooltip:{...tooltipBase, callbacks:{
          label: ctx => `  ${ctx.parsed.y} registro${ctx.parsed.y!==1?'s':''}`,
        }}
      },
      scales:{
        x:{ grid:{display:false}, ticks:{font:{size:10}} },
        y:{ grid:{color:GRID,drawBorder:false}, beginAtZero:true, ticks:{stepSize:1,precision:0} }
      }
    }
  });

  /* ──────────────────────────────────────────
     CHART 4 — Niveles (horizontal bar mejorado)
  ────────────────────────────────────────── */
  const nivelColors = {'principiante':'#10b981','estudiante':'#3b82f6','profesional':'#7c3aed','Sin nivel':'#6b7280'};
  const nivelBg = nivelesLabels.map(l => {
    const c = nivelColors[l] || '#6b7280';
    const ctx4tmp = document.getElementById('chartNiveles').getContext('2d');
    const g = ctx4tmp.createLinearGradient(0,0,300,0);
    g.addColorStop(0, c+'dd');
    g.addColorStop(1, c+'66');
    return g;
  });

  new Chart(document.getElementById('chartNiveles'), {
    type:'bar',
    data:{
      labels: nivelesLabels,
      datasets:[{
        data: nivelesData,
        backgroundColor: nivelBg,
        borderRadius:{ topRight:7,bottomRight:7 },
        borderSkipped:false,
        borderWidth:0,
      }]
    },
    options:{
      indexAxis:'y',
      responsive:true, maintainAspectRatio:false,
      plugins:{
        legend:{display:false},
        tooltip:{...tooltipBase, callbacks:{
          label: ctx => `  ${ctx.parsed.x} curso${ctx.parsed.x!==1?'s':''}`,
        }}
      },
      scales:{
        x:{ grid:{color:GRID,drawBorder:false}, beginAtZero:true, ticks:{stepSize:1,precision:0} },
        y:{ grid:{display:false}, ticks:{font:{size:11}} }
      }
    }
  });
})();
</script>
