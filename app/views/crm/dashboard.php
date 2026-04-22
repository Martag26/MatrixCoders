<?php
$colores = ['#7c3aed','#3b82f6','#10b981','#f59e0b','#ef4444','#8b5cf6'];
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
    <span style="font-size:12px;color:var(--crm-muted);background:var(--crm-bg);border:1px solid var(--crm-border);padding:6px 12px;border-radius:8px">
      <?= date('l, d \d\e F Y', strtotime('now')) ?>
    </span>
  </div>
</div>

<!-- Stat cards -->
<div class="crm-stats-grid">
  <div class="crm-stat-card blue">
    <div class="crm-stat-header">
      <div class="crm-stat-icon blue">
        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path stroke-linecap="round" d="M23 21v-2a4 4 0 00-3-3.87m-4-12a4 4 0 010 7.75"/></svg>
      </div>
      <span class="crm-stat-trend up">registrados</span>
    </div>
    <div class="crm-stat-value"><?= number_format($totalUsuarios) ?></div>
    <div class="crm-stat-label">Usuarios totales</div>
    <div style="font-size:11px;color:var(--crm-muted);margin-top:4px"><?= $nuevosEsteMes ?? 0 ?> nuevos este mes</div>
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
    <div style="font-size:11px;color:var(--crm-muted);margin-top:4px"><?= $totalMatriculas ?? 0 ?> matriculaciones</div>
  </div>

  <div class="crm-stat-card green">
    <div class="crm-stat-header">
      <div class="crm-stat-icon green">
        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
      </div>
      <span class="crm-stat-trend up">activas</span>
    </div>
    <div class="crm-stat-value"><?= number_format($totalCampanas) ?></div>
    <div class="crm-stat-label">Campañas activas</div>
    <div style="font-size:11px;color:var(--crm-muted);margin-top:4px">descuentos en vigor</div>
  </div>

  <div class="crm-stat-card orange">
    <div class="crm-stat-header">
      <div class="crm-stat-icon orange">
        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
      </div>
      <span class="crm-stat-trend <?= $incidenciasAbiertas > 0 ? 'down' : 'up' ?>"><?= $incidenciasAbiertas > 0 ? 'abiertas' : 'ok' ?></span>
    </div>
    <div class="crm-stat-value"><?= number_format($incidenciasAbiertas ?? $totalMensajes) ?></div>
    <div class="crm-stat-label">Incidencias abiertas</div>
    <div style="font-size:11px;color:var(--crm-muted);margin-top:4px"><?= $totalMensajes ?> mensajes totales</div>
  </div>
</div>

<!-- Charts row -->
<div class="crm-charts-grid">
  <div class="crm-chart-card">
    <div class="crm-chart-header">
      <div>
        <h3 class="crm-chart-title">Cursos con más alumnos</h3>
        <p class="crm-chart-sub">Top 6 por matriculaciones</p>
      </div>
    </div>
    <canvas id="chartTopCursos" class="crm-chart-canvas" height="200"></canvas>
  </div>

  <div class="crm-chart-card">
    <div class="crm-chart-header">
      <div>
        <h3 class="crm-chart-title">Distribución de usuarios</h3>
        <p class="crm-chart-sub">Por rol en la plataforma</p>
      </div>
    </div>
    <canvas id="chartRoles" class="crm-chart-canvas" height="200"></canvas>
  </div>
</div>

<!-- Second charts row -->
<div class="crm-charts-grid" style="grid-template-columns:2fr 1fr">
  <div class="crm-chart-card">
    <div class="crm-chart-header">
      <div>
        <h3 class="crm-chart-title">Nuevos registros</h3>
        <p class="crm-chart-sub">Últimos 6 meses</p>
      </div>
    </div>
    <canvas id="chartRegistros" class="crm-chart-canvas" height="160"></canvas>
  </div>

  <div class="crm-chart-card">
    <div class="crm-chart-header">
      <div>
        <h3 class="crm-chart-title">Cursos por nivel</h3>
        <p class="crm-chart-sub">Distribución</p>
      </div>
    </div>
    <canvas id="chartNiveles" class="crm-chart-canvas" height="160"></canvas>
  </div>
</div>

<!-- Bottom row -->
<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px" class="crm-charts-grid">

  <!-- Actividad reciente -->
  <div class="crm-card">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px">
      <h3 class="crm-card-title" style="margin:0">
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

  <!-- Panel de roles + acciones rápidas -->
  <div style="display:flex;flex-direction:column;gap:16px">
    <div class="crm-card">
      <h3 class="crm-card-title" style="font-size:13px;margin-bottom:14px">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
        Usuarios por rol
      </h3>
      <?php foreach ($porRol as $r):
        $pct = $totalUsuarios > 0 ? round(($r['total']/$totalUsuarios)*100) : 0;
      ?>
      <div style="margin-bottom:10px">
        <div style="display:flex;justify-content:space-between;font-size:12.5px;margin-bottom:4px">
          <span style="color:var(--crm-text)"><?= htmlspecialchars($r['etiqueta']) ?></span>
          <span style="font-weight:700;color:var(--crm-text)"><?= $r['total'] ?> <span style="color:var(--crm-muted);font-weight:400">(<?= $pct ?>%)</span></span>
        </div>
        <div style="height:5px;background:var(--crm-border);border-radius:99px;overflow:hidden">
          <div style="height:100%;width:<?= $pct ?>%;background:var(--crm-primary);border-radius:99px;transition:width .3s"></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <div class="crm-card">
      <h3 class="crm-card-title" style="font-size:13px;margin-bottom:12px">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
        Acciones rápidas
      </h3>
      <div style="display:flex;flex-direction:column;gap:6px">
        <?php if ($esAdmin): ?>
        <a href="<?= $crmBase ?>usuarios" class="crm-btn crm-btn-secondary crm-btn-sm" style="justify-content:flex-start">
          <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 4v16m8-8H4"/></svg>
          Gestionar usuarios
        </a>
        <a href="<?= $crmBase ?>cursos" class="crm-btn crm-btn-secondary crm-btn-sm" style="justify-content:flex-start">
          <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13"/></svg>
          Gestionar cursos
        </a>
        <a href="<?= $crmBase ?>campanas" class="crm-btn crm-btn-secondary crm-btn-sm" style="justify-content:flex-start">
          <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 4v16m8-8H4"/></svg>
          Nueva campaña
        </a>
        <?php endif; ?>
        <a href="<?= $crmBase ?>comunicacion" class="crm-btn crm-btn-secondary crm-btn-sm" style="justify-content:flex-start">
          <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72"/></svg>
          Ver comunicación
        </a>
        <a href="<?= $crmBase ?>logs" class="crm-btn crm-btn-secondary crm-btn-sm" style="justify-content:flex-start">
          <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2"/></svg>
          Ver logs
        </a>
      </div>
    </div>
  </div>
</div>

<script>
(function(){
  const topCursosLabels = <?= json_encode(array_map(fn($c)=>mb_strimwidth($c['titulo'],0,24,'…'), $topCursos), JSON_UNESCAPED_UNICODE) ?>;
  const topCursosData   = <?= json_encode(array_column($topCursos,'total')) ?>;
  const rolesLabels     = <?= json_encode(array_column($porRol,'etiqueta'), JSON_UNESCAPED_UNICODE) ?>;
  const rolesData       = <?= json_encode(array_column($porRol,'total')) ?>;
  const registrosMeses  = <?= json_encode(array_column($actividad6m,'mes'), JSON_UNESCAPED_UNICODE) ?>;
  const registrosTotals = <?= json_encode(array_column($actividad6m,'total')) ?>;
  const nivelesLabels   = <?= json_encode(array_column($porNivel,'nivel'), JSON_UNESCAPED_UNICODE) ?>;
  const nivelesData     = <?= json_encode(array_column($porNivel,'total')) ?>;

  Chart.defaults.font.family = "'Inter', sans-serif";
  Chart.defaults.color = 'rgba(241,240,251,0.45)';

  const palette = ['#7c3aed','#3b82f6','#10b981','#f59e0b','#ef4444','#8b5cf6','#ec4899','#14b8a6'];
  const gridColor = 'rgba(255,255,255,0.06)';

  new Chart(document.getElementById('chartTopCursos'), {
    type: 'bar',
    data: { labels: topCursosLabels, datasets:[{ data: topCursosData, backgroundColor: '#7c3aed', borderRadius: 6, borderSkipped: false }] },
    options: { responsive:true, plugins:{ legend:{display:false} }, scales:{ x:{grid:{display:false}}, y:{grid:{color:gridColor}, beginAtZero:true, ticks:{stepSize:1}} } }
  });

  new Chart(document.getElementById('chartRoles'), {
    type: 'doughnut',
    data: { labels: rolesLabels, datasets:[{ data: rolesData, backgroundColor: palette, borderWidth: 0, hoverOffset: 6 }] },
    options: { responsive:true, cutout:'65%', plugins:{ legend:{ position:'bottom', labels:{padding:14,font:{size:12},color:'rgba(241,240,251,0.6)'} } } }
  });

  new Chart(document.getElementById('chartRegistros'), {
    type: 'line',
    data: {
      labels: registrosMeses.map(m=>{ const [y,mo]=m.split('-'); return new Date(y,mo-1).toLocaleDateString('es-ES',{month:'short',year:'2-digit'}); }),
      datasets:[{ data: registrosTotals, borderColor:'#7c3aed', backgroundColor:'rgba(124,58,237,.12)', fill:true, tension:.4, pointBackgroundColor:'#7c3aed', pointRadius:4 }]
    },
    options:{ responsive:true, plugins:{legend:{display:false}}, scales:{x:{grid:{display:false}}, y:{grid:{color:gridColor}, beginAtZero:true, ticks:{stepSize:1}}} }
  });

  new Chart(document.getElementById('chartNiveles'), {
    type: 'bar',
    data: { labels: nivelesLabels, datasets:[{ data: nivelesData, backgroundColor: palette, borderRadius: 6 }] },
    options:{ indexAxis:'y', responsive:true, plugins:{legend:{display:false}}, scales:{x:{grid:{color:gridColor}, beginAtZero:true, ticks:{stepSize:1}}, y:{grid:{display:false}}} }
  });
})();
</script>
