<?php
/* Editor de curso — múltiples instructores, modals, drag & drop */
$instructoresAsignados = $instructoresAsignados ?? [];
$instructoresMap = [];
foreach ($instructores as $ins) { $instructoresMap[$ins['id']] = $ins['nombre']; }
?>

<a href="<?= $crmBase ?>cursos" class="crm-back-link">
  <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M15 19l-7-7 7-7"/></svg>
  Volver a Cursos
</a>

<div class="crm-page-header">
  <div>
    <h1 style="display:flex;align-items:center;gap:10px">
      Editor de Curso
      <span style="font-size:12px;font-weight:500;color:var(--crm-muted);background:var(--crm-bg);border:1px solid var(--crm-border);padding:3px 10px;border-radius:99px">ID #<?= $curso['id'] ?></span>
    </h1>
    <p style="color:var(--crm-muted)"><?= htmlspecialchars($curso['titulo']) ?></p>
  </div>
  <div class="crm-page-actions" style="gap:8px">
    <a href="<?= BASE_URL ?>/index.php?url=detallecurso&id=<?= $curso['id'] ?>" target="_blank" class="crm-btn crm-btn-secondary">
      <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
      Vista previa
    </a>
    <button class="crm-btn crm-btn-success" id="btnGuardarTodo" onclick="guardarTodo()">
      <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M5 13l4 4L19 7"/></svg>
      Guardar todo
    </button>
  </div>
</div>

<div class="crm-editor-layout">

  <!-- LEFT: course forms -->
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
              <?php foreach (['principiante','estudiante','profesional'] as $nv): ?>
                <option value="<?= $nv ?>" <?= strtolower($curso['nivel'] ?? '')===$nv?'selected':'' ?>><?= ucfirst($nv) ?></option>
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
            <label class="crm-label">
              <input type="checkbox" id="cDestacado" <?= !empty($curso['destacado'])?'checked':'' ?> style="accent-color:var(--crm-primary);margin-right:6px">
              Marcar como destacado
            </label>
          </div>
        </div>
      </div>
    </div>

    <!-- Multiple instructors -->
    <div class="crm-editor-panel">
      <div class="crm-editor-panel-header">
        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path stroke-linecap="round" d="M23 21v-2a4 4 0 00-3-3.87m-4-12a4 4 0 010 7.75"/></svg>
        <h3>Instructores asignados</h3>
        <?php if (!empty($instructores)): ?>
        <button type="button" class="crm-btn crm-btn-secondary crm-btn-sm" style="margin-left:auto" onclick="openModal('modalAddInstructor')">
          <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 4v16m8-8H4"/></svg>
          Añadir
        </button>
        <?php endif; ?>
      </div>
      <div class="crm-editor-panel-body" style="padding:12px">
        <?php if (empty($instructores)): ?>
          <p style="font-size:13px;color:var(--crm-muted);text-align:center;padding:12px">No hay instructores (rol EDITOR) registrados.</p>
        <?php else: ?>
          <div id="instructoresAsignadosList" style="display:flex;flex-direction:column;gap:8px">
            <?php foreach ($instructoresAsignados as $iid):
              $nombre = $instructoresMap[$iid] ?? 'Instructor #'.$iid;
              $initial = mb_strtoupper(mb_substr($nombre, 0, 1, 'UTF-8'), 'UTF-8');
            ?>
            <div class="crm-instructor-item" data-id="<?= $iid ?>" style="display:flex;align-items:center;gap:10px;padding:8px 10px;background:var(--crm-bg);border-radius:8px;border:1px solid var(--crm-border)">
              <div style="width:32px;height:32px;border-radius:50%;background:var(--crm-primary)20;color:var(--crm-primary);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:13px;flex-shrink:0"><?= $initial ?></div>
              <span style="flex:1;font-size:13px;font-weight:600;color:var(--crm-text)"><?= htmlspecialchars($nombre) ?></span>
              <button class="crm-btn-icon danger crm-btn-sm" onclick="quitarInstructor(this, <?= $iid ?>)" title="Quitar instructor">
                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 18L18 6M6 6l12 12"/></svg>
              </button>
            </div>
            <?php endforeach; ?>
            <?php if (empty($instructoresAsignados)): ?>
            <p style="font-size:13px;color:var(--crm-muted);text-align:center;padding:12px" id="noInstructorMsg">Sin instructores asignados.</p>
            <?php endif; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Exam panel -->
    <div class="crm-editor-panel">
      <div class="crm-editor-panel-header">
        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01m-.01 4h.01"/></svg>
        <h3>Examen del curso</h3>
        <button type="button" class="crm-btn crm-btn-secondary crm-btn-sm" style="margin-left:auto" onclick="agregarPregunta()">
          <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 4v16m8-8H4"/></svg>
          Pregunta
        </button>
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
          <textarea class="crm-textarea" id="exDesc" rows="2" placeholder="Instrucciones..."><?= htmlspecialchars($examen['descripcion'] ?? '') ?></textarea>
        </div>
        <div id="preguntasList">
          <?php foreach ($preguntas as $pi => $p): ?>
          <div class="crm-exam-question" data-pregunta="<?= $pi ?>">
            <div class="crm-exam-question-header">
              <div class="crm-exam-q-num"><?= $pi+1 ?></div>
              <input type="text" class="crm-input" style="flex:1" value="<?= htmlspecialchars($p['enunciado']) ?>" placeholder="Enunciado de la pregunta">
              <button class="crm-btn-icon danger" onclick="pedirEliminarPregunta(this)" title="Eliminar">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 18L18 6M6 6l12 12"/></svg>
              </button>
            </div>
            <div class="opciones-list">
              <?php foreach ($p['opciones'] as $oi => $o): ?>
              <div class="crm-option-row">
                <input type="radio" name="correcta_<?= $pi ?>" class="crm-option-radio" <?= $o['correcta']?'checked':'' ?> value="<?= $oi ?>">
                <input type="text" class="crm-input" style="flex:1" value="<?= htmlspecialchars($o['texto']) ?>" placeholder="Opción <?= chr(65+$oi) ?>">
                <button class="crm-btn-icon danger" onclick="this.closest('.crm-option-row').remove()">
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
      <button type="button" class="crm-btn crm-btn-primary crm-btn-sm" style="margin-left:auto" onclick="openModalNuevaUnidad()">
        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 4v16m8-8H4"/></svg>
        Unidad
      </button>
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
            <span style="font-size:11px;color:var(--crm-muted);margin-right:4px"><?= count($u['lecciones']) ?> lec.</span>
            <div class="crm-unit-actions">
              <button class="crm-btn-icon crm-btn-sm" title="Editar unidad" onclick="openModalEditarUnidad(this, <?= $u['id'] ?>)">
                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
              </button>
              <button class="crm-btn-icon danger crm-btn-sm" title="Eliminar unidad" onclick="pedirEliminarUnidad(<?= $u['id'] ?>, this)">
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
              <?php if (!empty($l['video_url'])): ?>
              <svg width="10" height="10" fill="none" stroke="var(--crm-success)" stroke-width="2" viewBox="0 0 24 24" title="Tiene vídeo"><path stroke-linecap="round" d="M5 13l4 4L19 7"/></svg>
              <?php endif; ?>
              <div class="crm-lesson-actions">
                <button class="crm-btn-icon crm-btn-sm" title="Editar lección" onclick='editarLeccion(<?= $l['id'] ?>, <?= htmlspecialchars(json_encode(['titulo'=>$l['titulo'],'video_url'=>$l['video_url']??'']), JSON_UNESCAPED_UNICODE) ?>)'>
                  <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                </button>
                <button class="crm-btn-icon danger crm-btn-sm" title="Eliminar lección" onclick="pedirEliminarLeccion(<?= $l['id'] ?>, this)">
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

<!-- ===== MODAL: Nueva unidad ===== -->
<div class="modal fade crm-modal" id="modalNuevaUnidad" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Nueva unidad</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="crm-form-group">
          <label class="crm-label">Título *</label>
          <input type="text" class="crm-input" id="nuevaUnidadTitulo" placeholder="Ej: Introducción al tema">
        </div>
      </div>
      <div class="modal-footer">
        <button class="crm-btn crm-btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button class="crm-btn crm-btn-primary" onclick="crearNuevaUnidad()">Crear unidad</button>
      </div>
    </div>
  </div>
</div>

<!-- ===== MODAL: Editar unidad ===== -->
<div class="modal fade crm-modal" id="modalEditarUnidad" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Editar unidad</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="editUnidadId">
        <div class="crm-form-group">
          <label class="crm-label">Título *</label>
          <input type="text" class="crm-input" id="editUnidadTitulo">
        </div>
      </div>
      <div class="modal-footer">
        <button class="crm-btn crm-btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button class="crm-btn crm-btn-primary" onclick="guardarEdicionUnidad()">Guardar</button>
      </div>
    </div>
  </div>
</div>

<!-- ===== MODAL: Editar lección ===== -->
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

<!-- ===== MODAL: Añadir instructor ===== -->
<div class="modal fade crm-modal" id="modalAddInstructor" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Añadir instructor</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="crm-form-group">
          <label class="crm-label">Instructor</label>
          <select class="crm-select" id="addInstrSelect">
            <option value="">— Seleccionar —</option>
            <?php foreach ($instructores as $ins): ?>
              <option value="<?= $ins['id'] ?>"><?= htmlspecialchars($ins['nombre']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <div class="modal-footer">
        <button class="crm-btn crm-btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button class="crm-btn crm-btn-primary" onclick="añadirInstructor()">Añadir</button>
      </div>
    </div>
  </div>
</div>

<script>
const CURSO_ID = <?= $curso['id'] ?>;
const INSTR_MAP = <?= json_encode($instructoresMap, JSON_UNESCAPED_UNICODE) ?>;

/* ====== SortableJS ====== */
const curriculum = document.getElementById('curriculum');
Sortable.create(curriculum, {
  animation: 150, handle: '.crm-unit-handle', ghostClass: 'sortable-ghost', chosenClass: 'sortable-chosen', onEnd: guardarOrden
});
document.querySelectorAll('.sortable-lessons').forEach(el => {
  Sortable.create(el, { animation: 150, handle: '.crm-lesson-handle', group: 'lecciones', ghostClass: 'sortable-ghost', onEnd: guardarOrden });
});

async function guardarOrden() {
  const unidades = [...curriculum.querySelectorAll('.crm-unit')].map(u => ({
    id: u.dataset.unidadId,
    lecciones: [...u.querySelectorAll('.crm-lesson')].map(l => ({ id: l.dataset.leccionId }))
  }));
  await CRM.api('guardar_unidades', { unidades });
}

/* ====== Unidades ====== */
function openModalNuevaUnidad() {
  document.getElementById('nuevaUnidadTitulo').value = '';
  openModal('modalNuevaUnidad');
}

async function crearNuevaUnidad() {
  const titulo = document.getElementById('nuevaUnidadTitulo').value.trim();
  if (!titulo) { CRM.toast('El título es obligatorio', 'error'); return; }
  const res = await CRM.api('crear_unidad', { curso_id: CURSO_ID, titulo });
  if (res.ok) {
    closeModal('modalNuevaUnidad');
    document.getElementById('emptyMsg')?.remove();
    const div = document.createElement('div');
    div.className = 'crm-unit';
    div.dataset.unidadId = res.id;
    div.innerHTML = `
      <div class="crm-unit-header">
        <span class="crm-unit-handle"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="9" y1="4" x2="9" y2="20"/><line x1="15" y1="4" x2="15" y2="20"/></svg></span>
        <span class="crm-unit-title" onclick="toggleUnit(this)">${CRM.escapeHtml(titulo)}</span>
        <span style="font-size:11px;color:var(--crm-muted);margin-right:4px">0 lec.</span>
        <div class="crm-unit-actions">
          <button class="crm-btn-icon crm-btn-sm" onclick="openModalEditarUnidad(this, ${res.id})">
            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
          </button>
          <button class="crm-btn-icon danger crm-btn-sm" onclick="pedirEliminarUnidad(${res.id}, this)">
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
    Sortable.create(div.querySelector('.sortable-lessons'), { animation: 150, handle: '.crm-lesson-handle', group: 'lecciones', ghostClass: 'sortable-ghost', onEnd: guardarOrden });
    CRM.toast('Unidad creada', 'success');
  } else CRM.toast(res.error, 'error');
}

let _editarUnidadBtn = null;
function openModalEditarUnidad(btn, id) {
  _editarUnidadBtn = btn;
  const span = btn.closest('.crm-unit-header').querySelector('.crm-unit-title');
  document.getElementById('editUnidadId').value  = id;
  document.getElementById('editUnidadTitulo').value = span.textContent;
  openModal('modalEditarUnidad');
}

async function guardarEdicionUnidad() {
  const titulo = document.getElementById('editUnidadTitulo').value.trim();
  if (!titulo) { CRM.toast('El título es obligatorio', 'error'); return; }
  if (_editarUnidadBtn) {
    _editarUnidadBtn.closest('.crm-unit-header').querySelector('.crm-unit-title').textContent = titulo;
  }
  closeModal('modalEditarUnidad');
  CRM.toast('Título actualizado (se guardará con el orden)', 'info');
}

async function pedirEliminarUnidad(id, btn) {
  const ok = await CRM.confirm('¿Eliminar esta unidad y todas sus lecciones? Esta acción es irreversible.', { title: 'Eliminar unidad', okLabel: 'Eliminar unidad' });
  if (!ok) return;
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
      const el = document.querySelector(`.crm-lesson[data-leccion-id="${id}"] .crm-lesson-title`);
      if (el) el.textContent = titulo;
      CRM.toast(res.mensaje, 'success'); closeModal('modalLeccion');
    } else CRM.toast(res.error, 'error');
  } else {
    const res = await CRM.api('crear_leccion', { unidad_id: unidadId, titulo, video_url: videoUrl });
    if (res.ok) {
      const cont = document.querySelector(`.sortable-lessons[data-unidad-id="${unidadId}"]`);
      const div = document.createElement('div');
      div.className = 'crm-lesson';
      div.dataset.leccionId = res.id;
      const hasVideo = videoUrl ? `<svg width="10" height="10" fill="none" stroke="var(--crm-success)" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M5 13l4 4L19 7"/></svg>` : '';
      div.innerHTML = `
        <span class="crm-lesson-handle"><svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="9" y1="4" x2="9" y2="20"/><line x1="15" y1="4" x2="15" y2="20"/></svg></span>
        <svg class="crm-lesson-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polygon points="5 3 19 12 5 21 5 3"/></svg>
        <span class="crm-lesson-title">${CRM.escapeHtml(titulo)}</span>
        ${hasVideo}
        <div class="crm-lesson-actions">
          <button class="crm-btn-icon crm-btn-sm" onclick='editarLeccion(${res.id}, {"titulo":"${titulo.replace(/"/g,'&quot;')}","video_url":"${videoUrl.replace(/"/g,'&quot;')}"})'>
            <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
          </button>
          <button class="crm-btn-icon danger crm-btn-sm" onclick="pedirEliminarLeccion(${res.id}, this)">
            <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 18L18 6M6 6l12 12"/></svg>
          </button>
        </div>`;
      cont.appendChild(div);
      CRM.toast('Lección creada', 'success'); closeModal('modalLeccion');
    } else CRM.toast(res.error, 'error');
  }
}

async function pedirEliminarLeccion(id, btn) {
  const ok = await CRM.confirm('¿Eliminar esta lección?', { title: 'Eliminar lección', okLabel: 'Eliminar' });
  if (!ok) return;
  const res = await CRM.api('eliminar_leccion', { id });
  if (res.ok) { btn.closest('.crm-lesson').remove(); CRM.toast(res.mensaje, 'success'); }
  else CRM.toast(res.error, 'error');
}

/* ====== Instructores múltiples ====== */
function getInstructoresActuales() {
  return [...document.querySelectorAll('#instructoresAsignadosList .crm-instructor-item')].map(el => parseInt(el.dataset.id));
}

function quitarInstructor(btn, id) {
  btn.closest('.crm-instructor-item').remove();
  const list = document.getElementById('instructoresAsignadosList');
  if (!list.querySelector('.crm-instructor-item')) {
    const p = document.createElement('p');
    p.id = 'noInstructorMsg';
    p.style = 'font-size:13px;color:var(--crm-muted);text-align:center;padding:12px';
    p.textContent = 'Sin instructores asignados.';
    list.appendChild(p);
  }
}

async function añadirInstructor() {
  const id = parseInt(document.getElementById('addInstrSelect').value);
  if (!id) { CRM.toast('Selecciona un instructor', 'error'); return; }
  const current = getInstructoresActuales();
  if (current.includes(id)) { CRM.toast('Ya está asignado', 'info'); closeModal('modalAddInstructor'); return; }
  const nombre = INSTR_MAP[id] || 'Instructor';
  const initial = nombre.charAt(0).toUpperCase();
  document.getElementById('noInstructorMsg')?.remove();
  const div = document.createElement('div');
  div.className = 'crm-instructor-item';
  div.dataset.id = id;
  div.style = 'display:flex;align-items:center;gap:10px;padding:8px 10px;background:var(--crm-bg);border-radius:8px;border:1px solid var(--crm-border)';
  div.innerHTML = `
    <div style="width:32px;height:32px;border-radius:50%;background:var(--crm-primary)20;color:var(--crm-primary);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:13px;flex-shrink:0">${initial}</div>
    <span style="flex:1;font-size:13px;font-weight:600;color:var(--crm-text)">${CRM.escapeHtml(nombre)}</span>
    <button class="crm-btn-icon danger crm-btn-sm" onclick="quitarInstructor(this, ${id})">
      <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 18L18 6M6 6l12 12"/></svg>
    </button>`;
  document.getElementById('instructoresAsignadosList').appendChild(div);
  closeModal('modalAddInstructor');
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
      <button class="crm-btn-icon danger" onclick="pedirEliminarPregunta(this)">
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

async function pedirEliminarPregunta(btn) {
  const ok = await CRM.confirm('¿Eliminar esta pregunta?', { title: 'Eliminar pregunta', okLabel: 'Eliminar' });
  if (ok) btn.closest('.crm-exam-question').remove();
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
  const btn = document.getElementById('btnGuardarTodo');
  btn.disabled = true;
  const origText = btn.innerHTML;
  btn.innerHTML = '<svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="animation:spin 1s linear infinite"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4"/></svg> Guardando…';

  const r1 = await CRM.api('actualizar_curso', {
    id: CURSO_ID,
    titulo:      document.getElementById('cTitulo').value,
    descripcion: document.getElementById('cDesc').value,
    precio:      parseFloat(document.getElementById('cPrecio').value) || 0,
    nivel:       document.getElementById('cNivel').value,
    categoria:   document.getElementById('cCategoria').value,
    destacado:   document.getElementById('cDestacado').checked ? 1 : 0,
  });

  // Save multiple instructors
  await CRM.api('asignar_instructor', {
    curso_id: CURSO_ID,
    instructor_ids: getInstructoresActuales(),
  });

  await guardarOrden();

  const preguntas = [...document.querySelectorAll('.crm-exam-question')].map(pEl => {
    const inputs   = pEl.querySelectorAll('input[type=text]');
    const enunciado = inputs[0]?.value || '';
    const opciones  = [...pEl.querySelectorAll('.crm-option-row')].map(oEl => ({
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
  btn.innerHTML = origText;
  if (r1.ok) CRM.toast('✓ Cambios guardados correctamente', 'success');
  else CRM.toast(r1.error || 'Error al guardar', 'error');
}

/* Spin animation for loading */
const style = document.createElement('style');
style.textContent = '@keyframes spin { to { transform: rotate(360deg); } }';
document.head.appendChild(style);
</script>
