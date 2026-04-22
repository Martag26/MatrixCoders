<?php /* Módulo de Cursos */ ?>

<div class="crm-page-header">
  <div>
    <h1>Gestión de Cursos</h1>
    <p>Administra el catálogo, activa/desactiva cursos y asigna instructores. Total: <strong><?= $totalRows ?></strong></p>
  </div>
</div>

<!-- Toolbar -->
<form method="GET" action="<?= $crmFormBase ?>" id="filtroForm">
  <?= $crmFormHidden ?>
  <input type="hidden" name="sec" value="cursos">
  <div class="crm-toolbar">
    <div class="crm-search-wrap">
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
        <option value="<?= htmlspecialchars($n) ?>" <?= $nivel===$n?'selected':'' ?>><?= htmlspecialchars($n) ?></option>
      <?php endforeach; ?>
    </select>
    <?php endif; ?>
    <?php if ($q || $cat || $nivel): ?>
    <a href="<?= $crmBase ?>cursos" class="crm-btn crm-btn-secondary">Limpiar</a>
    <?php endif; ?>
  </div>
</form>

<!-- Cards grid -->
<?php if (empty($cursos)): ?>
<div class="crm-empty">
  <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
  <h3>Sin cursos</h3>
  <p>No se encontraron cursos con ese filtro.</p>
</div>
<?php else: ?>

<div class="crm-courses-grid" id="cursosGrid">
  <?php foreach ($cursos as $c):
    $activo = (bool)($c['activo'] ?? 1);
    $descuento = $c['descuento_activo'] ?? 0;
    $precioFinal = $c['precio'] * (1 - $descuento/100);
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

      <!-- Badges top-left -->
      <div class="crm-course-badges">
        <?php if ($descuento > 0): ?>
          <span class="crm-badge descuento">-<?= round($descuento) ?>%</span>
        <?php endif; ?>
        <?php if (!empty($c['destacado'])): ?>
          <span class="crm-badge" style="background:rgba(245,158,11,.9);color:#fff">⭐ Destacado</span>
        <?php endif; ?>
      </div>

      <!-- Toggle top-right -->
      <div class="crm-course-toggle">
        <label class="crm-toggle-switch" title="<?= $activo?'Desactivar':'Activar' ?> curso">
          <input type="checkbox" <?= $activo?'checked':'' ?> onchange="toggleCurso(<?= $c['id'] ?>, this)">
          <span class="crm-toggle-slider"></span>
        </label>
      </div>
    </div>

    <div class="crm-course-body">
      <?php if (!empty($c['categoria'])): ?>
        <div class="crm-course-cat"><?= htmlspecialchars($c['categoria']) ?></div>
      <?php endif; ?>
      <h3 class="crm-course-title"><?= htmlspecialchars($c['titulo']) ?></h3>
      <div class="crm-course-meta">
        <span class="crm-course-meta-item">
          <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
          <?= $c['alumnos'] ?? 0 ?> alumnos
        </span>
        <?php if (!empty($c['nivel'])): ?>
        <span class="crm-course-meta-item">
          <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
          <?= htmlspecialchars($c['nivel']) ?>
        </span>
        <?php endif; ?>
        <?php if (!empty($c['instructor_nombre'])): ?>
        <span class="crm-course-meta-item">
          <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
          <?= htmlspecialchars($c['instructor_nombre']) ?>
        </span>
        <?php endif; ?>
      </div>
      <div class="crm-course-price">
        <?php if ($descuento > 0): ?>
          <span class="original"><?= number_format($c['precio'],2) ?>€</span>
          <span style="color:var(--crm-danger)"><?= number_format($precioFinal,2) ?>€</span>
        <?php else: ?>
          <?= $c['precio'] > 0 ? number_format($c['precio'],2).'€' : '<span style="color:var(--crm-success)">Gratis</span>' ?>
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

<!-- Pagination -->
<?php if ($totalPags > 1): ?>
<div style="display:flex;align-items:center;justify-content:space-between;margin-top:20px">
  <span style="font-size:13px;color:var(--crm-muted)">Mostrando <?= count($cursos) ?> de <?= $totalRows ?> cursos</span>
  <div class="crm-pag-btns">
    <?php if ($page > 1): ?>
      <a class="crm-pag-btn" href="<?= $crmBase ?>cursos&pag=<?= $page-1 ?>&q=<?= urlencode($q) ?>&cat=<?= urlencode($cat) ?>&nivel=<?= urlencode($nivel) ?>">‹</a>
    <?php endif; ?>
    <?php for ($i = max(1,$page-2); $i <= min($totalPags,$page+2); $i++): ?>
      <a class="crm-pag-btn <?= $i===$page?'active':'' ?>" href="<?= $crmBase ?>cursos&pag=<?= $i ?>&q=<?= urlencode($q) ?>&cat=<?= urlencode($cat) ?>&nivel=<?= urlencode($nivel) ?>"><?= $i ?></a>
    <?php endfor; ?>
    <?php if ($page < $totalPags): ?>
      <a class="crm-pag-btn" href="<?= $crmBase ?>cursos&pag=<?= $page+1 ?>&q=<?= urlencode($q) ?>&cat=<?= urlencode($cat) ?>&nivel=<?= urlencode($nivel) ?>">›</a>
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

function openModalInstructor(cursoId, instrId) {
  document.getElementById('instrCursoId').value = cursoId;
  document.getElementById('instrSelect').value  = instrId || '';
  openModal('modalInstructor');
}

async function guardarInstructor() {
  const cursoId     = document.getElementById('instrCursoId').value;
  const instructorId = document.getElementById('instrSelect').value;
  const res = await CRM.api('asignar_instructor', { curso_id: cursoId, instructor_id: instructorId });
  if (res.ok) { CRM.toast(res.mensaje, 'success'); closeModal('modalInstructor'); setTimeout(()=>location.reload(),800); }
  else        { CRM.toast(res.error, 'error'); }
}

document.getElementById('searchCurso')?.addEventListener('input', CRM.debounce(e => {
  document.getElementById('filtroForm').submit();
}, 600));
</script>
