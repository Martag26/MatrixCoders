<?php /* Módulo de Cursos */
$cursosStats = $cursosStats ?? ['total'=>0,'activos'=>0,'inactivos'=>0,'gratis'=>0,'total_matriculas'=>0];
$perPage     = $perPage ?? 12;
?>

<div class="crm-page-header">
  <div>
    <h1>Gestión de Cursos</h1>
    <p>Administra el catálogo, activa/desactiva y asigna instructores. Total: <strong><?= number_format($totalRows) ?></strong></p>
  </div>
  <div class="crm-page-actions">
    <!-- View toggle -->
    <div style="display:flex;background:var(--crm-bg);border:1px solid var(--crm-border);border-radius:9px;padding:3px;gap:2px">
      <button id="btnCards" onclick="setView('cards')" title="Vista tarjetas"
        style="padding:5px 10px;border:none;border-radius:7px;cursor:pointer;background:var(--crm-primary);color:#fff;display:flex;align-items:center;gap:5px;font-size:12px;font-weight:500">
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
        Tarjetas
      </button>
      <button id="btnTable" onclick="setView('table')" title="Vista tabla"
        style="padding:5px 10px;border:none;border-radius:7px;cursor:pointer;background:transparent;color:var(--crm-muted);display:flex;align-items:center;gap:5px;font-size:12px;font-weight:500">
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
        Tabla
      </button>
    </div>
  </div>
</div>

<!-- Stats strip -->
<div style="display:grid;grid-template-columns:repeat(5,1fr);gap:10px;margin-bottom:20px">
  <?php
  $avgAlumnos = $cursosStats['total'] > 0 ? round($cursosStats['total_matriculas'] / $cursosStats['total'], 1) : 0;
  $cStrips = [
    ['label'=>'Total cursos',      'value'=>$cursosStats['total'],           'color'=>'var(--crm-primary)', 'icon'=>'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253'],
    ['label'=>'Activos',           'value'=>$cursosStats['activos'],         'color'=>'var(--crm-success)', 'icon'=>'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
    ['label'=>'Inactivos',         'value'=>$cursosStats['inactivos'],       'color'=>'var(--crm-danger)',  'icon'=>'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z'],
    ['label'=>'Gratuitos',         'value'=>$cursosStats['gratis'],          'color'=>'var(--crm-info)',    'icon'=>'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z'],
    ['label'=>'Media alumnos/curso','value'=>$avgAlumnos,                    'color'=>'var(--crm-warning)', 'icon'=>'M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2M9 7a4 4 0 100 8 4 4 0 000-8z'],
  ];
  foreach ($cStrips as $s): ?>
  <div class="crm-card" style="padding:14px 16px;display:flex;align-items:center;gap:12px">
    <div style="width:34px;height:34px;border-radius:9px;background:<?= $s['color'] ?>18;display:flex;align-items:center;justify-content:center;flex-shrink:0">
      <svg width="16" height="16" fill="none" stroke="<?= $s['color'] ?>" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="<?= $s['icon'] ?>"/></svg>
    </div>
    <div>
      <div style="font-size:20px;font-weight:700;color:var(--crm-text);line-height:1"><?= is_float($s['value']) ? number_format($s['value'],1) : number_format((int)$s['value']) ?></div>
      <div style="font-size:11px;color:var(--crm-muted);margin-top:2px"><?= $s['label'] ?></div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<!-- Toolbar -->
<form method="GET" action="<?= $crmFormBase ?>" id="filtroForm">
  <?= $crmFormHidden ?>
  <input type="hidden" name="sec" value="cursos">
  <div class="crm-toolbar">
    <div class="crm-search-wrap" style="flex:1;min-width:180px">
      <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path stroke-linecap="round" d="M21 21l-4.35-4.35"/></svg>
      <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Buscar curso…" class="crm-search-input" id="searchCurso">
    </div>
    <?php if (!empty($categorias)): ?>
    <select name="cat" class="crm-filter-select" onchange="this.form.submit()">
      <option value="">Todas las categorías</option>
      <?php foreach ($categorias as $c): ?>
        <option value="<?= htmlspecialchars($c) ?>" <?= $cat===$c?'selected':'' ?>><?= htmlspecialchars($c) ?></option>
      <?php endforeach; ?>
    </select>
    <?php endif; ?>
    <?php if (!empty($niveles)): ?>
    <select name="nivel" class="crm-filter-select" onchange="this.form.submit()">
      <option value="">Todos los niveles</option>
      <?php foreach ($niveles as $n): ?>
        <option value="<?= htmlspecialchars($n) ?>" <?= $nivel===$n?'selected':'' ?>><?= ucfirst(htmlspecialchars($n)) ?></option>
      <?php endforeach; ?>
    </select>
    <?php endif; ?>
    <select name="per" class="crm-filter-select" onchange="this.form.submit()" title="Cursos por página">
      <?php foreach ([6,12,24,48] as $n): ?>
        <option value="<?= $n ?>" <?= $perPage===$n?'selected':'' ?>><?= $n ?> por página</option>
      <?php endforeach; ?>
    </select>
    <?php if ($q || $cat || $nivel): ?>
    <a href="<?= $crmBase ?>cursos" class="crm-btn crm-btn-secondary">
      <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 18L18 6M6 6l12 12"/></svg>
      Limpiar
    </a>
    <?php endif; ?>
  </div>
</form>

<?php if (empty($cursos)): ?>
<div class="crm-empty">
  <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
  <h3>Sin cursos</h3>
  <p>No se encontraron cursos con ese filtro.</p>
</div>
<?php else: ?>

<!-- ===== CARDS VIEW ===== -->
<div class="crm-courses-grid" id="viewCards">
  <?php foreach ($cursos as $c):
    $activo = (bool)($c['activo'] ?? 1);
    $descuento = $c['descuento_activo'] ?? 0;
    $precioFinal = $c['precio'] * (1 - $descuento/100);
    $nivelColors = ['principiante'=>'#10b981','estudiante'=>'#3b82f6','profesional'=>'#7c3aed'];
    $nivelColor = $nivelColors[$c['nivel'] ?? ''] ?? '#6b7280';
  ?>
  <div class="crm-course-card <?= !$activo ? 'inactive' : '' ?>" id="card-<?= $c['id'] ?>">
    <div class="crm-course-thumb">
      <?php if (!empty($c['imagen'])): ?>
        <img src="<?= BASE_URL ?>/img/<?= htmlspecialchars($c['imagen']) ?>" alt="<?= htmlspecialchars($c['titulo']) ?>" loading="lazy">
      <?php else: ?>
        <div class="crm-course-thumb-placeholder">
          <svg width="36" height="36" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
        </div>
      <?php endif; ?>

      <!-- Status + badges top -->
      <div class="crm-course-badges">
        <?php if (!$activo): ?>
          <span class="crm-badge" style="background:rgba(107,114,128,.85);color:#fff;font-size:9px">INACTIVO</span>
        <?php endif; ?>
        <?php if ($descuento > 0): ?>
          <span class="crm-badge descuento">-<?= round($descuento) ?>%</span>
        <?php endif; ?>
        <?php if (!empty($c['destacado'])): ?>
          <span class="crm-badge" style="background:rgba(245,158,11,.9);color:#fff">⭐</span>
        <?php endif; ?>
      </div>

      <!-- Toggle -->
      <div class="crm-course-toggle">
        <label class="crm-toggle-switch" title="<?= $activo?'Desactivar':'Activar' ?> curso">
          <input type="checkbox" <?= $activo?'checked':'' ?> onchange="toggleCurso(<?= $c['id'] ?>, this)">
          <span class="crm-toggle-slider"></span>
        </label>
      </div>
    </div>

    <div class="crm-course-body">
      <div style="display:flex;align-items:center;gap:6px;margin-bottom:4px">
        <?php if (!empty($c['categoria'])): ?>
          <span style="font-size:10px;font-weight:600;color:var(--crm-primary);text-transform:uppercase;letter-spacing:.5px"><?= htmlspecialchars($c['categoria']) ?></span>
        <?php endif; ?>
        <?php if (!empty($c['nivel'])): ?>
          <span style="font-size:9px;font-weight:700;padding:1px 7px;border-radius:99px;background:<?= $nivelColor ?>20;color:<?= $nivelColor ?>;text-transform:capitalize"><?= htmlspecialchars($c['nivel']) ?></span>
        <?php endif; ?>
      </div>
      <h3 class="crm-course-title"><?= htmlspecialchars($c['titulo']) ?></h3>

      <!-- Enrollment bar -->
      <?php $maxAlumnos = max(1, $cursosStats['total_matriculas'] / max(1,$cursosStats['total'])*3);
            $barPct = min(100, round(($c['alumnos']/max(1,$maxAlumnos))*100)); ?>
      <div style="margin:8px 0">
        <div style="display:flex;justify-content:space-between;font-size:11px;color:var(--crm-muted);margin-bottom:3px">
          <span><?= $c['alumnos'] ?? 0 ?> alumnos</span>
          <?php if (!empty($c['instructor_nombre'])): ?>
            <span><?= htmlspecialchars($c['instructor_nombre']) ?></span>
          <?php endif; ?>
        </div>
        <div style="height:3px;background:var(--crm-border);border-radius:99px;overflow:hidden">
          <div style="height:100%;width:<?= $barPct ?>%;background:var(--crm-primary);border-radius:99px"></div>
        </div>
      </div>

      <div class="crm-course-price">
        <?php if ($descuento > 0): ?>
          <span class="original"><?= number_format($c['precio'],2) ?>€</span>
          <span style="color:var(--crm-danger);font-weight:700"><?= number_format($precioFinal,2) ?>€</span>
        <?php else: ?>
          <?= ($c['precio'] ?? 0) > 0
              ? '<span style="font-weight:700">'.number_format($c['precio'],2).'€</span>'
              : '<span style="color:var(--crm-success);font-weight:700">Gratis</span>' ?>
        <?php endif; ?>
      </div>
    </div>

    <div class="crm-course-actions">
      <a href="<?= $crmBase ?>editor&id=<?= $c['id'] ?>" class="crm-btn crm-btn-secondary crm-btn-sm" style="flex:1;justify-content:center">
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
        Editar
      </a>
      <button class="crm-btn-icon" title="Asignar instructor" onclick='openModalInstructor(<?= $c['id'] ?>, <?= (int)($c['instructor_id']??0) ?>)'>
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
      </button>
      <a href="<?= BASE_URL ?>/index.php?url=detallecurso&id=<?= $c['id'] ?>" target="_blank" class="crm-btn-icon" title="Ver en sitio">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
      </a>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<!-- ===== TABLE VIEW ===== -->
<div class="crm-table-wrap" id="viewTable" style="display:none">
  <table class="crm-table">
    <thead>
      <tr>
        <th style="width:40px">#</th>
        <th>Curso</th>
        <th>Categoría / Nivel</th>
        <th>Instructor</th>
        <th style="text-align:center">Alumnos</th>
        <th style="text-align:center">Precio</th>
        <th style="text-align:center">Estado</th>
        <th style="text-align:right">Acciones</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($cursos as $c):
        $activo = (bool)($c['activo'] ?? 1);
        $descuento = $c['descuento_activo'] ?? 0;
        $precioFinal = $c['precio'] * (1 - $descuento/100);
        $nivelColors = ['principiante'=>'#10b981','estudiante'=>'#3b82f6','profesional'=>'#7c3aed'];
        $nivelColor = $nivelColors[$c['nivel'] ?? ''] ?? '#6b7280';
      ?>
      <tr id="trow-<?= $c['id'] ?>" <?= !$activo ? 'style="opacity:.55"' : '' ?>>
        <td style="color:var(--crm-muted);font-size:12px"><?= $c['id'] ?></td>
        <td>
          <div style="display:flex;align-items:center;gap:10px">
            <?php if (!empty($c['imagen'])): ?>
              <img src="<?= BASE_URL ?>/img/<?= htmlspecialchars($c['imagen']) ?>" alt="" style="width:40px;height:30px;object-fit:cover;border-radius:6px;flex-shrink:0">
            <?php else: ?>
              <div style="width:40px;height:30px;border-radius:6px;background:var(--crm-border);display:flex;align-items:center;justify-content:center;flex-shrink:0">
                <svg width="14" height="14" fill="none" stroke="var(--crm-muted)" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5"/></svg>
              </div>
            <?php endif; ?>
            <span style="font-weight:600;font-size:13px"><?= htmlspecialchars($c['titulo']) ?></span>
          </div>
        </td>
        <td>
          <div style="display:flex;flex-direction:column;gap:3px">
            <?php if (!empty($c['categoria'])): ?>
              <span style="font-size:11px;color:var(--crm-primary);font-weight:600"><?= htmlspecialchars($c['categoria']) ?></span>
            <?php endif; ?>
            <?php if (!empty($c['nivel'])): ?>
              <span style="font-size:10px;padding:1px 7px;border-radius:99px;background:<?= $nivelColor ?>15;color:<?= $nivelColor ?>;font-weight:700;width:fit-content;text-transform:capitalize"><?= htmlspecialchars($c['nivel']) ?></span>
            <?php endif; ?>
          </div>
        </td>
        <td style="font-size:12.5px;color:var(--crm-muted)"><?= htmlspecialchars($c['instructor_nombre'] ?? '—') ?></td>
        <td style="text-align:center">
          <span style="font-weight:700;font-size:14px"><?= $c['alumnos'] ?? 0 ?></span>
        </td>
        <td style="text-align:center">
          <?php if ($descuento > 0): ?>
            <span style="text-decoration:line-through;color:var(--crm-muted);font-size:11px"><?= number_format($c['precio'],2) ?>€</span><br>
            <span style="color:var(--crm-danger);font-weight:700;font-size:13px"><?= number_format($precioFinal,2) ?>€</span>
          <?php else: ?>
            <?= ($c['precio'] ?? 0) > 0
                ? '<span style="font-weight:600;font-size:13px">'.number_format($c['precio'],2).'€</span>'
                : '<span style="color:var(--crm-success);font-weight:700;font-size:13px">Gratis</span>' ?>
          <?php endif; ?>
        </td>
        <td style="text-align:center">
          <label class="crm-toggle-switch" title="<?= $activo?'Desactivar':'Activar' ?>">
            <input type="checkbox" <?= $activo?'checked':'' ?> onchange="toggleCursoRow(<?= $c['id'] ?>, this)">
            <span class="crm-toggle-slider"></span>
          </label>
        </td>
        <td style="text-align:right">
          <div style="display:flex;gap:5px;justify-content:flex-end">
            <a href="<?= $crmBase ?>editor&id=<?= $c['id'] ?>" class="crm-btn-icon" title="Editar">
              <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
            </a>
            <button class="crm-btn-icon" title="Instructor" onclick='openModalInstructor(<?= $c['id'] ?>, <?= (int)($c['instructor_id']??0) ?>)'>
              <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            </button>
            <a href="<?= BASE_URL ?>/index.php?url=detallecurso&id=<?= $c['id'] ?>" target="_blank" class="crm-btn-icon" title="Ver">
              <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
            </a>
          </div>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<!-- Pagination -->
<?php if ($totalPags > 1): ?>
<div style="display:flex;align-items:center;justify-content:space-between;margin-top:20px">
  <span style="font-size:13px;color:var(--crm-muted)">Mostrando <?= count($cursos) ?> de <?= number_format($totalRows) ?> cursos</span>
  <div class="crm-pag-btns">
    <?php if ($page > 1): ?>
      <a class="crm-pag-btn" href="<?= $crmBase ?>cursos&pag=<?= $page-1 ?>&per=<?= $perPage ?>&q=<?= urlencode($q) ?>&cat=<?= urlencode($cat) ?>&nivel=<?= urlencode($nivel) ?>">‹</a>
    <?php endif; ?>
    <?php for ($i = max(1,$page-2); $i <= min($totalPags,$page+2); $i++): ?>
      <a class="crm-pag-btn <?= $i===$page?'active':'' ?>" href="<?= $crmBase ?>cursos&pag=<?= $i ?>&per=<?= $perPage ?>&q=<?= urlencode($q) ?>&cat=<?= urlencode($cat) ?>&nivel=<?= urlencode($nivel) ?>"><?= $i ?></a>
    <?php endfor; ?>
    <?php if ($page < $totalPags): ?>
      <a class="crm-pag-btn" href="<?= $crmBase ?>cursos&pag=<?= $page+1 ?>&per=<?= $perPage ?>&q=<?= urlencode($q) ?>&cat=<?= urlencode($cat) ?>&nivel=<?= urlencode($nivel) ?>">›</a>
    <?php endif; ?>
  </div>
</div>
<?php endif; ?>
<?php endif; ?>

<!-- Modal: Asignar instructor -->
<div class="modal fade crm-modal" id="modalInstructor" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Asignar instructor</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="instrCursoId">
        <div class="crm-form-group">
          <label class="crm-label">Instructor</label>
          <select class="crm-select" id="instrSelect">
            <option value="">— Sin instructor —</option>
            <?php foreach ($instructores as $ins): ?>
              <option value="<?= $ins['id'] ?>"><?= htmlspecialchars($ins['nombre']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <div class="modal-footer">
        <button class="crm-btn crm-btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button class="crm-btn crm-btn-primary" onclick="guardarInstructor()">Guardar</button>
      </div>
    </div>
  </div>
</div>

<script>
const CRM_VIEW_KEY = 'mc_crm_courses_view';

function setView(v) {
  localStorage.setItem(CRM_VIEW_KEY, v);
  const isCards = v === 'cards';
  document.getElementById('viewCards').style.display = isCards ? '' : 'none';
  document.getElementById('viewTable').style.display = isCards ? 'none' : '';
  document.getElementById('btnCards').style.background = isCards ? 'var(--crm-primary)' : 'transparent';
  document.getElementById('btnCards').style.color      = isCards ? '#fff' : 'var(--crm-muted)';
  document.getElementById('btnTable').style.background = isCards ? 'transparent' : 'var(--crm-primary)';
  document.getElementById('btnTable').style.color      = isCards ? 'var(--crm-muted)' : '#fff';
}
// Restore saved view on load
(function(){ setView(localStorage.getItem(CRM_VIEW_KEY) || 'cards'); })();

async function toggleCurso(id, checkbox) {
  const res = await CRM.api('toggle_curso', { id });
  if (res.ok) {
    CRM.toast(res.mensaje, res.activo ? 'success' : 'info');
    const card = document.getElementById('card-' + id);
    if (card) card.classList.toggle('inactive', !res.activo);
  } else {
    CRM.toast(res.error, 'error');
    checkbox.checked = !checkbox.checked;
  }
}

async function toggleCursoRow(id, checkbox) {
  const res = await CRM.api('toggle_curso', { id });
  if (res.ok) {
    CRM.toast(res.mensaje, res.activo ? 'success' : 'info');
    const row = document.getElementById('trow-' + id);
    if (row) row.style.opacity = res.activo ? '' : '.55';
  } else {
    CRM.toast(res.error, 'error');
    checkbox.checked = !checkbox.checked;
  }
}

function openModalInstructor(cursoId, instrId) {
  document.getElementById('instrCursoId').value = cursoId;
  document.getElementById('instrSelect').value  = instrId || '';
  openModal('modalInstructor');
}

async function guardarInstructor() {
  const cursoId      = document.getElementById('instrCursoId').value;
  const instructorId = document.getElementById('instrSelect').value;
  const res = await CRM.api('asignar_instructor', { curso_id: cursoId, instructor_id: instructorId });
  if (res.ok) { CRM.toast(res.mensaje, 'success'); closeModal('modalInstructor'); setTimeout(()=>location.reload(),800); }
  else        { CRM.toast(res.error, 'error'); }
}

document.getElementById('searchCurso')?.addEventListener('input', CRM.debounce(() => {
  document.getElementById('filtroForm').submit();
}, 600));
</script>
