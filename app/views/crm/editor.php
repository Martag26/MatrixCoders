<?php /* Editor de curso con drag & drop */ ?>

<a href="<?= $crmBase ?>cursos" class="crm-back-link">
  <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M15 19l-7-7 7-7"/></svg>
  Volver a Cursos
</a>

<div class="crm-page-header">
  <div>
    <h1>Editor de Curso</h1>
    <p><?= htmlspecialchars($curso['titulo']) ?> · ID #<?= $curso['id'] ?></p>
  </div>
  <div class="crm-page-actions">
    <button class="crm-btn crm-btn-success" onclick="guardarTodo()">
      <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M5 13l4 4L19 7"/></svg>
      Guardar cambios
    </button>
  </div>
</div>

<div class="crm-editor-layout">

  <!-- LEFT: Course info form -->
  <div style="display:flex;flex-direction:column;gap:16px">

    <!-- Basic info -->
    <div class="crm-editor-panel">
      <div class="crm-editor-panel-header">
        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <h3>Información básica</h3>
      </div>
      <div class="crm-editor-panel-body">
        <div class="crm-form-group">
          <label class="crm-label">Título del curso *</label>
          <input type="text" class="crm-input" id="cTitulo" value="<?= htmlspecialchars($curso['titulo']) ?>" placeholder="Título del curso">
        </div>
        <div class="crm-form-group">
          <label class="crm-label">Descripción</label>
          <textarea class="crm-textarea" id="cDesc" rows="3"><?= htmlspecialchars($curso['descripcion'] ?? '') ?></textarea>
        </div>
        <div class="crm-form-row">
          <div class="crm-form-group">
            <label class="crm-label">Precio (€)</label>
            <input type="number" class="crm-input" id="cPrecio" value="<?= $curso['precio'] ?>" min="0" step="0.01">
          </div>
          <div class="crm-form-group">
            <label class="crm-label">Nivel</label>
            <select class="crm-select" id="cNivel">
              <option value="">Sin especificar</option>
              <?php foreach (['Principiante','Intermedio','Avanzado','Experto'] as $nv): ?>
                <option value="<?= $nv ?>" <?= ($curso['nivel']==$nv)?'selected':'' ?>><?= $nv ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="crm-form-row">
          <div class="crm-form-group">
            <label class="crm-label">Categoría</label>
            <input type="text" class="crm-input" id="cCategoria" value="<?= htmlspecialchars($curso['categoria'] ?? '') ?>" placeholder="Ej: Programación">
          </div>
          <div class="crm-form-group">
            <label class="crm-label">Instructor asignado</label>
            <select class="crm-select" id="cInstructor">
              <option value="">— Sin asignar —</option>
              <?php foreach ($instructores as $ins): ?>
                <option value="<?= $ins['id'] ?>" <?= ($curso['instructor_id']==$ins['id'])?'selected':'' ?>><?= htmlspecialchars($ins['nombre']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="crm-form-group">
          <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px;font-weight:600">
            <input type="checkbox" id="cDestacado" <?= !empty($curso['destacado'])?'checked':'' ?> style="accent-color:var(--crm-primary)">
            Marcar como destacado
          </label>
        </div>
      </div>
    </div>

    <!-- Exam panel -->
    <div class="crm-editor-panel">
      <div class="crm-editor-panel-header">
        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01m-.01 4h.01"/></svg>
        <h3>Examen del curso</h3>
        <button type="button" class="crm-btn crm-btn-secondary crm-btn-sm" style="margin-left:auto" onclick="agregarPregunta()">+ Pregunta</button>
      </div>
      <div class="crm-editor-panel-body">
        <div class="crm-form-row" style="margin-bottom:12px">
          <div class="crm-form-group">
            <label class="crm-label">Título del examen</label>
            <input type="text" class="crm-input" id="exTitulo" value="<?= htmlspecialchars($examen['titulo'] ?? '') ?>" placeholder="Ej: Examen final">
          </div>
          <div class="crm-form-group">
            <label class="crm-label">Nota mínima (0-10)</label>
            <input type="number" class="crm-input" id="exNota" value="<?= $examen['nota_minima'] ?? 5 ?>" min="0" max="10" step="0.5">
          </div>
        </div>
        <div class="crm-form-group">
          <label class="crm-label">Descripción del examen</label>
          <textarea class="crm-textarea" id="exDesc" rows="2" placeholder="Instrucciones o descripción..."><?= htmlspecialchars($examen['descripcion'] ?? '') ?></textarea>
        </div>
        <div id="preguntasList">
          <?php foreach ($preguntas as $pi => $p): ?>
          <div class="crm-exam-question" data-pregunta="<?= $pi ?>">
            <div class="crm-exam-question-header">
              <div class="crm-exam-q-num"><?= $pi+1 ?></div>
              <input type="text" class="crm-input" style="flex:1" value="<?= htmlspecialchars($p['enunciado']) ?>" placeholder="Enunciado de la pregunta">
              <button class="crm-btn-icon danger" onclick="this.closest('.crm-exam-question').remove()" title="Eliminar pregunta">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 18L18 6M6 6l12 12"/></svg>
              </button>
            </div>
            <div class="opciones-list">
              <?php foreach ($p['opciones'] as $oi => $o): ?>
              <div class="crm-option-row">
                <input type="radio" name="correcta_<?= $pi ?>" class="crm-option-radio" <?= $o['correcta']?'checked':'' ?> value="<?= $oi ?>">
                <input type="text" class="crm-input" style="flex:1" value="<?= htmlspecialchars($o['texto']) ?>" placeholder="Opción <?= chr(65+$oi) ?>">
                <button class="crm-btn-icon danger" onclick="this.closest('.crm-option-row').remove()" title="Eliminar opción">
                  <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
              </div>
              <?php endforeach; ?>
            </div>
            <button type="button" class="crm-btn crm-btn-secondary crm-btn-sm" style="margin-top:6px" onclick="agregarOpcion(this)">+ Opción</button>
          </div>
          <?php endforeach; ?>
        </div>
        <button type="button" class="crm-btn crm-btn-secondary crm-btn-sm" style="width:100%;margin-top:8px;justify-content:center" onclick="agregarPregunta()">
          <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 4v16m8-8H4"/></svg>
          Añadir pregunta
        </button>
      </div>
    </div>

  </div>

  <!-- RIGHT: Curriculum -->
  <div class="crm-editor-panel" style="align-self:start;position:sticky;top:80px">
    <div class="crm-editor-panel-header">
      <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
      <h3>Contenido del curso</h3>
      <button type="button" class="crm-btn crm-btn-primary crm-btn-sm" style="margin-left:auto" onclick="nuevaUnidad()">+ Unidad</button>
    </div>
    <div class="crm-editor-panel-body" style="padding:12px">
      <div class="crm-curriculum" id="curriculum">
        <?php foreach ($unidades as $u): ?>
        <div class="crm-unit" data-unidad-id="<?= $u['id'] ?>">
          <div class="crm-unit-header">
            <span class="crm-unit-handle" title="Arrastrar">
              <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="9" y1="4" x2="9" y2="20"/><line x1="15" y1="4" x2="15" y2="20"/></svg>
            </span>
            <span class="crm-unit-title" onclick="toggleUnit(this)"><?= htmlspecialchars($u['titulo']) ?></span>
            <div class="crm-unit-actions">
              <button class="crm-btn-icon crm-btn-sm" title="Editar título unidad" onclick="editarUnidad(this, <?= $u['id'] ?>)">
                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
              </button>
              <button class="crm-btn-icon danger crm-btn-sm" title="Eliminar unidad" onclick="eliminarUnidad(<?= $u['id'] ?>, this)">
                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
              </button>
            </div>
          </div>

          <div class="crm-unit-lessons sortable-lessons" data-unidad-id="<?= $u['id'] ?>">
            <?php foreach ($u['lecciones'] as $l): ?>
            <div class="crm-lesson" data-leccion-id="<?= $l['id'] ?>">
              <span class="crm-lesson-handle">
                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="9" y1="4" x2="9" y2="20"/><line x1="15" y1="4" x2="15" y2="20"/></svg>
              </span>
              <svg class="crm-lesson-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polygon points="5 3 19 12 5 21 5 3"/></svg>
              <span class="crm-lesson-title"><?= htmlspecialchars($l['titulo']) ?></span>
              <div class="crm-lesson-actions">
                <button class="crm-btn-icon crm-btn-sm" title="Editar lección" onclick='editarLeccion(<?= $l['id'] ?>, <?= htmlspecialchars(json_encode(['titulo'=>$l['titulo'],'video_url'=>$l['video_url']??'']), JSON_UNESCAPED_UNICODE) ?>)'>
                  <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                </button>
                <button class="crm-btn-icon danger crm-btn-sm" title="Eliminar lección" onclick="eliminarLeccion(<?= $l['id'] ?>, this)">
                  <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
              </div>
            </div>
            <?php endforeach; ?>
          </div>

          <div class="crm-add-lesson" onclick="nuevaLeccion(<?= $u['id'] ?>)">
            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 4v16m8-8H4"/></svg>
            Añadir lección
          </div>
        </div>
        <?php endforeach; ?>

        <?php if (empty($unidades)): ?>
        <div id="emptyMsg" style="text-align:center;padding:30px;color:var(--crm-muted);font-size:13px">
          No hay unidades. Haz clic en "+ Unidad" para empezar.
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Editar lección -->
<div class="modal fade crm-modal" id="modalLeccion" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalLeccionTitle">Editar lección</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="lecId">
        <input type="hidden" id="lecUnidadId">
        <div class="crm-form-group">
          <label class="crm-label">Título *</label>
          <input type="text" class="crm-input" id="lecTitulo" placeholder="Título de la lección">
        </div>
        <div class="crm-form-group">
          <label class="crm-label">URL del vídeo</label>
          <input type="url" class="crm-input" id="lecVideo" placeholder="https://youtube.com/watch?v=...">
          <div class="crm-form-hint">Soporta YouTube, Vimeo u otras plataformas.</div>
        </div>
      </div>
      <div class="modal-footer">
        <button class="crm-btn crm-btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button class="crm-btn crm-btn-primary" id="btnLeccion" onclick="guardarLeccion()">Guardar</button>
      </div>
    </div>
  </div>
</div>

<script>
const CURSO_ID = <?= $curso['id'] ?>;

/* ====== SortableJS for units & lessons ====== */
const curriculum = document.getElementById('curriculum');
Sortable.create(curriculum, {
  animation: 150, handle: '.crm-unit-handle', ghostClass: 'sortable-ghost', chosenClass: 'sortable-chosen',
  onEnd: guardarOrden
});

document.querySelectorAll('.sortable-lessons').forEach(el => {
  Sortable.create(el, {
    animation: 150, handle: '.crm-lesson-handle',
    group: 'lecciones', ghostClass: 'sortable-ghost',
    onEnd: guardarOrden
  });
});

async function guardarOrden() {
  const unidades = [...curriculum.querySelectorAll('.crm-unit')].map(u => ({
    id: u.dataset.unidadId,
    lecciones: [...u.querySelectorAll('.crm-lesson')].map(l => ({ id: l.dataset.leccionId }))
  }));
  await CRM.api('guardar_unidades', { unidades });
}

/* ====== Unidades ====== */
async function nuevaUnidad() {
  const titulo = prompt('Título de la nueva unidad:');
  if (!titulo?.trim()) return;
  const res = await CRM.api('crear_unidad', { curso_id: CURSO_ID, titulo });
  if (res.ok) {
    document.getElementById('emptyMsg')?.remove();
    const div = document.createElement('div');
    div.className = 'crm-unit';
    div.dataset.unidadId = res.id;
    div.innerHTML = `
      <div class="crm-unit-header">
        <span class="crm-unit-handle"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="9" y1="4" x2="9" y2="20"/><line x1="15" y1="4" x2="15" y2="20"/></svg></span>
        <span class="crm-unit-title" onclick="toggleUnit(this)">${CRM.escapeHtml(titulo)}</span>
        <div class="crm-unit-actions">
          <button class="crm-btn-icon crm-btn-sm" onclick="editarUnidad(this, ${res.id})">
            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
          </button>
          <button class="crm-btn-icon danger crm-btn-sm" onclick="eliminarUnidad(${res.id}, this)">
            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 18L18 6M6 6l12 12"/></svg>
          </button>
        </div>
      </div>
      <div class="crm-unit-lessons sortable-lessons" data-unidad-id="${res.id}"></div>
      <div class="crm-add-lesson" onclick="nuevaLeccion(${res.id})">
        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 4v16m8-8H4"/></svg>
        Añadir lección
      </div>`;
    curriculum.appendChild(div);
    Sortable.create(div.querySelector('.sortable-lessons'), {
      animation: 150, handle: '.crm-lesson-handle', group: 'lecciones', ghostClass: 'sortable-ghost', onEnd: guardarOrden
    });
    CRM.toast('Unidad creada', 'success');
  } else CRM.toast(res.error, 'error');
}

async function editarUnidad(btn, id) {
  const span = btn.closest('.crm-unit-header').querySelector('.crm-unit-title');
  const titulo = prompt('Nuevo título de la unidad:', span.textContent);
  if (!titulo?.trim()) return;
  span.textContent = titulo;
  CRM.toast('Título actualizado (se guardará con el orden)', 'info');
}

async function eliminarUnidad(id, btn) {
  if (!CRM.confirm('¿Eliminar esta unidad y todas sus lecciones?')) return;
  const res = await CRM.api('eliminar_unidad', { id });
  if (res.ok) { btn.closest('.crm-unit').remove(); CRM.toast(res.mensaje, 'success'); }
  else CRM.toast(res.error, 'error');
}

function toggleUnit(span) {
  const lessons = span.closest('.crm-unit').querySelector('.crm-unit-lessons');
  if (lessons) lessons.style.display = lessons.style.display === 'none' ? '' : 'none';
}

/* ====== Lecciones ====== */
let leccionMode = 'create';
function nuevaLeccion(unidadId) {
  leccionMode = 'create';
  document.getElementById('lecId').value       = '';
  document.getElementById('lecUnidadId').value = unidadId;
  document.getElementById('lecTitulo').value   = '';
  document.getElementById('lecVideo').value    = '';
  document.getElementById('modalLeccionTitle').textContent = 'Nueva lección';
  document.getElementById('btnLeccion').textContent = 'Crear lección';
  openModal('modalLeccion');
}

function editarLeccion(id, data) {
  leccionMode = 'edit';
  document.getElementById('lecId').value      = id;
  document.getElementById('lecTitulo').value  = data.titulo;
  document.getElementById('lecVideo').value   = data.video_url || '';
  document.getElementById('modalLeccionTitle').textContent = 'Editar lección';
  document.getElementById('btnLeccion').textContent = 'Guardar cambios';
  openModal('modalLeccion');
}

async function guardarLeccion() {
  const titulo   = document.getElementById('lecTitulo').value.trim();
  const videoUrl = document.getElementById('lecVideo').value.trim();
  const id       = document.getElementById('lecId').value;
  const unidadId = document.getElementById('lecUnidadId').value;
  if (!titulo) { CRM.toast('El título es obligatorio', 'error'); return; }

  if (leccionMode === 'edit') {
    const res = await CRM.api('editar_leccion', { id, titulo, video_url: videoUrl });
    if (res.ok) {
      document.querySelector(`.crm-lesson[data-leccion-id="${id}"] .crm-lesson-title`).textContent = titulo;
      CRM.toast(res.mensaje, 'success'); closeModal('modalLeccion');
    } else CRM.toast(res.error, 'error');
  } else {
    const res = await CRM.api('crear_leccion', { unidad_id: unidadId, titulo, video_url: videoUrl });
    if (res.ok) {
      const cont = document.querySelector(`.sortable-lessons[data-unidad-id="${unidadId}"]`);
      const div = document.createElement('div');
      div.className = 'crm-lesson';
      div.dataset.leccionId = res.id;
      div.innerHTML = `
        <span class="crm-lesson-handle"><svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="9" y1="4" x2="9" y2="20"/><line x1="15" y1="4" x2="15" y2="20"/></svg></span>
        <svg class="crm-lesson-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polygon points="5 3 19 12 5 21 5 3"/></svg>
        <span class="crm-lesson-title">${CRM.escapeHtml(titulo)}</span>
        <div class="crm-lesson-actions">
          <button class="crm-btn-icon crm-btn-sm" onclick='editarLeccion(${res.id}, {"titulo":"${titulo.replace(/"/g,'&quot;')}","video_url":"${videoUrl.replace(/"/g,'&quot;')}"})'>
            <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
          </button>
          <button class="crm-btn-icon danger crm-btn-sm" onclick="eliminarLeccion(${res.id}, this)">
            <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 18L18 6M6 6l12 12"/></svg>
          </button>
        </div>`;
      cont.appendChild(div);
      CRM.toast('Lección creada', 'success'); closeModal('modalLeccion');
    } else CRM.toast(res.error, 'error');
  }
}

async function eliminarLeccion(id, btn) {
  if (!CRM.confirm('¿Eliminar esta lección?')) return;
  const res = await CRM.api('eliminar_leccion', { id });
  if (res.ok) { btn.closest('.crm-lesson').remove(); CRM.toast(res.mensaje, 'success'); }
  else CRM.toast(res.error, 'error');
}

/* ====== Exam builder ====== */
let pregCount = document.querySelectorAll('.crm-exam-question').length;

function agregarPregunta() {
  const pi = pregCount++;
  const div = document.createElement('div');
  div.className = 'crm-exam-question';
  div.dataset.pregunta = pi;
  div.innerHTML = `
    <div class="crm-exam-question-header">
      <div class="crm-exam-q-num">${pi+1}</div>
      <input type="text" class="crm-input" style="flex:1" placeholder="Enunciado de la pregunta">
      <button class="crm-btn-icon danger" onclick="this.closest('.crm-exam-question').remove()">
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 18L18 6M6 6l12 12"/></svg>
      </button>
    </div>
    <div class="opciones-list">
      ${[0,1,2,3].map(i=>`
      <div class="crm-option-row">
        <input type="radio" name="correcta_${pi}" class="crm-option-radio" value="${i}">
        <input type="text" class="crm-input" style="flex:1" placeholder="Opción ${String.fromCharCode(65+i)}">
        <button class="crm-btn-icon danger" onclick="this.closest('.crm-option-row').remove()">
          <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
      </div>`).join('')}
    </div>
    <button type="button" class="crm-btn crm-btn-secondary crm-btn-sm" style="margin-top:6px" onclick="agregarOpcion(this)">+ Opción</button>`;
  document.getElementById('preguntasList').appendChild(div);
  div.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function agregarOpcion(btn) {
  const bloque = btn.closest('.crm-exam-question');
  const pi = bloque.dataset.pregunta;
  const lista = bloque.querySelector('.opciones-list');
  const oi = lista.querySelectorAll('.crm-option-row').length;
  const row = document.createElement('div');
  row.className = 'crm-option-row';
  row.innerHTML = `
    <input type="radio" name="correcta_${pi}" class="crm-option-radio" value="${oi}">
    <input type="text" class="crm-input" style="flex:1" placeholder="Opción ${String.fromCharCode(65+oi)}">
    <button class="crm-btn-icon danger" onclick="this.closest('.crm-option-row').remove()">
      <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 18L18 6M6 6l12 12"/></svg>
    </button>`;
  lista.appendChild(row);
}

/* ====== Save everything ====== */
async function guardarTodo() {
  const btn = document.querySelector('[onclick="guardarTodo()"]');
  btn.disabled = true;
  btn.textContent = 'Guardando…';

  // 1. Update course info
  const r1 = await CRM.api('actualizar_curso', {
    id:         CURSO_ID,
    titulo:     document.getElementById('cTitulo').value,
    descripcion:document.getElementById('cDesc').value,
    precio:     parseFloat(document.getElementById('cPrecio').value) || 0,
    nivel:      document.getElementById('cNivel').value,
    categoria:  document.getElementById('cCategoria').value,
    destacado:  document.getElementById('cDestacado').checked ? 1 : 0,
  });

  // 2. Assign instructor
  await CRM.api('asignar_instructor', {
    curso_id:       CURSO_ID,
    instructor_id:  document.getElementById('cInstructor').value || null,
  });

  // 3. Save order
  await guardarOrden();

  // 4. Save exam
  const preguntas = [...document.querySelectorAll('.crm-exam-question')].map(pEl => {
    const inputs = pEl.querySelectorAll('input[type=text]');
    const enunciado = inputs[0]?.value || '';
    const opciones = [...pEl.querySelectorAll('.crm-option-row')].map((oEl, i) => ({
      texto:    oEl.querySelector('input[type=text]')?.value || '',
      correcta: oEl.querySelector('input[type=radio]')?.checked ? 1 : 0,
    }));
    return { enunciado, opciones };
  });

  const exTitulo = document.getElementById('exTitulo').value.trim();
  if (exTitulo && preguntas.length > 0) {
    await CRM.api('guardar_examen', {
      curso_id:    CURSO_ID,
      titulo:      exTitulo,
      descripcion: document.getElementById('exDesc').value,
      nota_minima: parseFloat(document.getElementById('exNota').value) || 5,
      preguntas,
    });
  }

  btn.disabled = false;
  btn.textContent = 'Guardar cambios';

  if (r1.ok) CRM.toast('Cambios guardados correctamente', 'success');
  else CRM.toast(r1.error || 'Error al guardar', 'error');
}
</script>
