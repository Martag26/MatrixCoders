<?php /* Módulo de Campañas */ ?>

<div class="crm-page-header">
  <div>
    <h1>Campañas</h1>
    <p>Crea y gestiona campañas de descuentos, avisos y promociones. Total: <strong><?= $totalRows ?></strong></p>
  </div>
  <div class="crm-page-actions">
    <button class="crm-btn crm-btn-primary" onclick="openModal('modalCampana'); modoModal='crear'">
      <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 4v16m8-8H4"/></svg>
      Nueva campaña
    </button>
  </div>
</div>

<!-- Toolbar -->
<form method="GET" action="" id="filtroForm">
  <input type="hidden" name="url" value="crm">
  <input type="hidden" name="sec" value="campanas">
  <div class="crm-toolbar">
    <div class="crm-search-wrap">
      <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path stroke-linecap="round" d="M21 21l-4.35-4.35"/></svg>
      <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Buscar campaña…" class="crm-search-input" id="searchCampana">
    </div>
    <select name="tipo" class="crm-filter-select" onchange="this.form.submit()">
      <option value="">Todos los tipos</option>
      <option value="oferta"  <?= $tipo==='oferta' ?'selected':'' ?>>Oferta</option>
      <option value="aviso"   <?= $tipo==='aviso'  ?'selected':'' ?>>Aviso</option>
      <option value="evento"  <?= $tipo==='evento' ?'selected':'' ?>>Evento</option>
      <option value="novedad" <?= $tipo==='novedad'?'selected':'' ?>>Novedad</option>
    </select>
    <select name="estado" class="crm-filter-select" onchange="this.form.submit()">
      <option value="">Todos los estados</option>
      <option value="activa"   <?= $estado==='activa'  ?'selected':'' ?>>Activas</option>
      <option value="inactiva" <?= $estado==='inactiva'?'selected':'' ?>>Inactivas</option>
    </select>
    <?php if ($q || $tipo || $estado): ?>
    <a href="?url=crm&sec=campanas" class="crm-btn crm-btn-secondary">Limpiar</a>
    <?php endif; ?>
  </div>
</form>

<?php if (empty($campanas)): ?>
<div class="crm-empty">
  <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
  <h3>Sin campañas</h3>
  <p>Aún no hay campañas. Crea la primera haciendo clic en "Nueva campaña".</p>
  <button class="crm-btn crm-btn-primary" onclick="openModal('modalCampana'); modoModal='crear'">Nueva campaña</button>
</div>
<?php else: ?>

<div class="crm-campaigns-grid">
  <?php foreach ($campanas as $cam):
    $ahora = date('Y-m-d');
    $esActiva = $cam['activa'] && (!$cam['fecha_fin'] || $cam['fecha_fin'] >= $ahora);
    $esPasada = $cam['fecha_fin'] && $cam['fecha_fin'] < $ahora;
  ?>
  <div class="crm-campaign-card <?= $cam['tipo'] ?>">
    <div class="crm-campaign-header">
      <div style="display:flex;flex-direction:column;gap:6px;flex:1">
        <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
          <span class="crm-badge <?= $cam['tipo'] ?>"><?= ucfirst($cam['tipo']) ?></span>
          <?php if ($cam['descuento_pct'] > 0): ?>
            <span class="crm-discount-pill">-<?= round($cam['descuento_pct']) ?>%</span>
          <?php endif; ?>
          <span class="crm-badge <?= $esActiva?'activo':($esPasada?'inactivo':'inactivo') ?>">
            <?= $esActiva?'Activa':($esPasada?'Expirada':'Inactiva') ?>
          </span>
        </div>
        <h3 class="crm-campaign-title"><?= htmlspecialchars($cam['titulo']) ?></h3>
      </div>
      <div class="crm-campaign-actions">
        <button class="crm-btn-icon" title="Editar" onclick='abrirEditar(<?= htmlspecialchars(json_encode($cam), JSON_UNESCAPED_UNICODE) ?>)'>
          <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
        </button>
        <button class="crm-btn-icon danger" title="Eliminar" onclick="eliminarCampana(<?= $cam['id'] ?>, '<?= addslashes($cam['titulo']) ?>')">
          <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
        </button>
      </div>
    </div>

    <p class="crm-campaign-body"><?= htmlspecialchars(mb_strimwidth($cam['cuerpo'], 0, 120, '…')) ?></p>

    <?php if (!empty($cam['cursos_vinculados'])): ?>
    <div style="display:flex;flex-wrap:wrap;gap:4px;margin-bottom:4px">
      <?php foreach ($cam['cursos_vinculados'] as $cv): ?>
        <span style="background:var(--crm-bg);border:1px solid var(--crm-border);border-radius:99px;font-size:11px;padding:2px 8px;color:var(--crm-text)">
          📚 <?= htmlspecialchars(mb_strimwidth($cv['titulo'],0,30,'…')) ?>
          <?php if ($cv['descuento']>0): ?><strong>-<?= round($cv['descuento']) ?>%</strong><?php endif; ?>
        </span>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <div class="crm-campaign-footer">
      <div class="crm-campaign-dates">
        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        <?= $cam['fecha_inicio'] ? date('d/m/Y', strtotime($cam['fecha_inicio'])) : '—' ?>
        →
        <?= $cam['fecha_fin']    ? date('d/m/Y', strtotime($cam['fecha_fin']))    : 'Sin fin' ?>
      </div>
      <div style="font-size:11.5px;color:var(--crm-muted)"><?= $cam['cursos_count'] ?> curso(s) vinculado(s)</div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<!-- Pagination -->
<?php if ($totalPags > 1): ?>
<div style="display:flex;align-items:center;justify-content:space-between;margin-top:20px">
  <span style="font-size:13px;color:var(--crm-muted)">Mostrando <?= count($campanas) ?> de <?= $totalRows ?> campañas</span>
  <div class="crm-pag-btns">
    <?php if ($page > 1): ?>
      <a class="crm-pag-btn" href="?url=crm&sec=campanas&pag=<?= $page-1 ?>&q=<?= urlencode($q) ?>&tipo=<?= urlencode($tipo) ?>&estado=<?= urlencode($estado) ?>">‹</a>
    <?php endif; ?>
    <?php for ($i = max(1,$page-2); $i <= min($totalPags,$page+2); $i++): ?>
      <a class="crm-pag-btn <?= $i===$page?'active':'' ?>" href="?url=crm&sec=campanas&pag=<?= $i ?>&q=<?= urlencode($q) ?>&tipo=<?= urlencode($tipo) ?>&estado=<?= urlencode($estado) ?>"><?= $i ?></a>
    <?php endfor; ?>
    <?php if ($page < $totalPags): ?>
      <a class="crm-pag-btn" href="?url=crm&sec=campanas&pag=<?= $page+1 ?>&q=<?= urlencode($q) ?>&tipo=<?= urlencode($tipo) ?>&estado=<?= urlencode($estado) ?>">›</a>
    <?php endif; ?>
  </div>
</div>
<?php endif; ?>
<?php endif; ?>

<!-- ===== MODAL CREAR/EDITAR ===== -->
<div class="modal fade crm-modal" id="modalCampana" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalCampanaTitulo">Nueva campaña</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="campId">
        <div class="crm-form-row">
          <div class="crm-form-group">
            <label class="crm-label">Título *</label>
            <input type="text" class="crm-input" id="campTitulo" placeholder="Ej: Oferta Black Friday">
          </div>
          <div class="crm-form-group">
            <label class="crm-label">Tipo</label>
            <select class="crm-select" id="campTipo">
              <option value="oferta">Oferta / Descuento</option>
              <option value="aviso">Aviso</option>
              <option value="evento">Evento</option>
              <option value="novedad">Novedad</option>
            </select>
          </div>
        </div>
        <div class="crm-form-group">
          <label class="crm-label">Mensaje / Descripción *</label>
          <textarea class="crm-textarea" id="campCuerpo" rows="3" placeholder="Describe la campaña…"></textarea>
        </div>
        <div class="crm-form-row">
          <div class="crm-form-group">
            <label class="crm-label">Fecha inicio</label>
            <input type="date" class="crm-input" id="campInicio">
          </div>
          <div class="crm-form-group">
            <label class="crm-label">Fecha fin</label>
            <input type="date" class="crm-input" id="campFin">
          </div>
        </div>
        <div class="crm-form-row">
          <div class="crm-form-group">
            <label class="crm-label">Descuento (%)</label>
            <input type="number" class="crm-input" id="campDescuento" min="0" max="100" step="1" value="0" placeholder="0 = sin descuento">
          </div>
          <div class="crm-form-group">
            <label class="crm-label">Estado</label>
            <select class="crm-select" id="campActiva">
              <option value="1">Activa</option>
              <option value="0">Inactiva</option>
            </select>
          </div>
        </div>
        <div class="crm-form-group">
          <label class="crm-label">Cursos vinculados</label>
          <div id="campCursosList" style="display:flex;flex-wrap:wrap;gap:6px;max-height:160px;overflow-y:auto;border:1px solid var(--crm-border);border-radius:8px;padding:10px">
            <?php foreach ($cursos as $c): ?>
            <label style="display:flex;align-items:center;gap:5px;font-size:12.5px;cursor:pointer;padding:4px 8px;border:1px solid var(--crm-border);border-radius:99px;white-space:nowrap">
              <input type="checkbox" value="<?= $c['id'] ?>" class="camp-curso-check" style="accent-color:var(--crm-primary)">
              <?= htmlspecialchars(mb_strimwidth($c['titulo'],0,35,'…')) ?>
            </label>
            <?php endforeach; ?>
          </div>
        </div>
        <div class="crm-form-group">
          <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px;font-weight:600">
            <input type="checkbox" id="campNotificar" style="accent-color:var(--crm-primary)">
            Notificar a todos los usuarios sobre esta campaña
          </label>
        </div>
      </div>
      <div class="modal-footer">
        <button class="crm-btn crm-btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button class="crm-btn crm-btn-primary" id="btnGuardarCampana" onclick="guardarCampana()">Crear campaña</button>
      </div>
    </div>
  </div>
</div>

<script>
let modoModal = 'crear';

function abrirEditar(cam) {
  modoModal = 'editar';
  document.getElementById('campId').value        = cam.id;
  document.getElementById('campTitulo').value    = cam.titulo;
  document.getElementById('campCuerpo').value    = cam.cuerpo;
  document.getElementById('campTipo').value      = cam.tipo;
  document.getElementById('campInicio').value    = cam.fecha_inicio || '';
  document.getElementById('campFin').value       = cam.fecha_fin    || '';
  document.getElementById('campDescuento').value = cam.descuento_pct || 0;
  document.getElementById('campActiva').value    = cam.activa;
  document.getElementById('modalCampanaTitulo').textContent = 'Editar campaña';
  document.getElementById('btnGuardarCampana').textContent  = 'Guardar cambios';
  // uncheck all, then check linked
  document.querySelectorAll('.camp-curso-check').forEach(cb => cb.checked = false);
  (cam.cursos_vinculados || []).forEach(cv => {
    document.querySelectorAll(`.camp-curso-check[value="${cv.curso_id || cv.id}"]`)
      .forEach(cb => cb.checked = true);
  });
  openModal('modalCampana');
}

document.getElementById('modalCampana').addEventListener('show.bs.modal', () => {
  if (modoModal === 'crear') {
    document.getElementById('campId').value        = '';
    document.getElementById('campTitulo').value    = '';
    document.getElementById('campCuerpo').value    = '';
    document.getElementById('campTipo').value      = 'oferta';
    document.getElementById('campInicio').value    = new Date().toISOString().split('T')[0];
    document.getElementById('campFin').value       = '';
    document.getElementById('campDescuento').value = 0;
    document.getElementById('campActiva').value    = '1';
    document.getElementById('modalCampanaTitulo').textContent = 'Nueva campaña';
    document.getElementById('btnGuardarCampana').textContent  = 'Crear campaña';
    document.querySelectorAll('.camp-curso-check').forEach(cb => cb.checked = false);
  }
});

async function guardarCampana() {
  const cursos = [...document.querySelectorAll('.camp-curso-check:checked')].map(cb => cb.value);
  const data = {
    titulo:        document.getElementById('campTitulo').value,
    cuerpo:        document.getElementById('campCuerpo').value,
    tipo:          document.getElementById('campTipo').value,
    fecha_inicio:  document.getElementById('campInicio').value,
    fecha_fin:     document.getElementById('campFin').value,
    descuento_pct: parseFloat(document.getElementById('campDescuento').value) || 0,
    activa:        document.getElementById('campActiva').value,
    notificar:     document.getElementById('campNotificar').checked,
    cursos,
  };
  const action = modoModal === 'editar' ? 'editar_campana' : 'crear_campana';
  if (modoModal === 'editar') data.id = document.getElementById('campId').value;

  const res = await CRM.api(action, data);
  if (res.ok) { CRM.toast(res.mensaje, 'success'); closeModal('modalCampana'); setTimeout(()=>location.reload(),800); }
  else        { CRM.toast(res.error, 'error'); }
}

async function eliminarCampana(id, titulo) {
  const ok = await CRM.confirm(`¿Eliminar la campaña "${titulo}"?`, { title: 'Eliminar campaña', okLabel: 'Eliminar' });
  if (!ok) return;
  const res = await CRM.api('eliminar_campana', { id });
  if (res.ok) { CRM.toast(res.mensaje, 'success'); setTimeout(()=>location.reload(),800); }
  else        { CRM.toast(res.error, 'error'); }
}

document.getElementById('searchCampana')?.addEventListener('input', CRM.debounce(e => {
  document.getElementById('filtroForm').submit();
}, 600));
</script>
