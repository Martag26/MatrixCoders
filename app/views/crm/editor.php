<?php
function extractYouTubeIdPhp(string $url): ?string {
    if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([^&?\/\s]{11})/', $url, $m)) {
        return $m[1];
    }
    return null;
}

$instructoresMap = [];
foreach ($instructores as $ins) { $instructoresMap[$ins['id']] = $ins['nombre']; }
$instructoresAsignados = $instructoresAsignados ?? [];
$examenTest     = $examenTest     ?? null;
$examenPractico = $examenPractico ?? null;
$preguntas      = $preguntas      ?? [];
$tareasPracticas = $tareasPracticas ?? [];
$apuntes        = $apuntes        ?? [];
$tiposTarea     = ['texto'=>'Texto / Redacción','codigo'=>'Código / Programación','diseno'=>'Diseño / Maquetación','proyecto'=>'Proyecto completo'];
?>

<style>
.crm-editor-tabs-nav{display:flex;gap:3px;padding:5px;background:var(--crm-bg);border:1px solid var(--crm-border);border-radius:13px;margin-bottom:22px;flex-wrap:wrap}
.crm-editor-tab{display:flex;align-items:center;gap:7px;padding:9px 20px;border:none;border-radius:9px;cursor:pointer;font-size:13.5px;font-weight:600;font-family:inherit;color:var(--crm-muted);background:transparent;transition:all .15s;white-space:nowrap}
.crm-editor-tab:hover{background:rgba(255,255,255,.06);color:var(--crm-text)}
.crm-editor-tab.active{background:var(--crm-primary);color:#fff;box-shadow:0 2px 8px rgba(124,58,237,.35)}
.crm-editor-tab svg{flex-shrink:0}

/* ── Unit collapse ── */
.crm-unit-lessons{overflow:hidden;transition:max-height .28s cubic-bezier(.4,0,.2,1),opacity .2s;}
.crm-unit-lessons.collapsed{max-height:0!important;opacity:0;pointer-events:none;}
.crm-unit-chevron{display:inline-flex;align-items:center;justify-content:center;width:22px;height:22px;border-radius:6px;color:var(--crm-muted);transition:transform .25s,background .15s;cursor:pointer;flex-shrink:0;}
.crm-unit-chevron:hover{background:rgba(255,255,255,.08);}
.crm-unit.is-open .crm-unit-chevron{transform:rotate(0deg);}
.crm-unit:not(.is-open) .crm-unit-chevron{transform:rotate(-90deg);}
.crm-add-lesson{display:none;}
.crm-unit.is-open .crm-add-lesson{display:flex;}
.crm-tab-panel{display:none}
.crm-tab-panel.active{display:block}
.crm-info-grid{display:grid;grid-template-columns:1fr 340px;gap:16px;align-items:start}
@media(max-width:1000px){.crm-info-grid{grid-template-columns:1fr}}
.crm-img-upload-area{border:2px dashed var(--crm-border);border-radius:12px;padding:20px;text-align:center;cursor:pointer;transition:all .2s;background:var(--crm-bg-alt,var(--crm-bg));position:relative}
.crm-img-upload-area:hover,.crm-img-upload-area.drag-over{border-color:var(--crm-primary);background:rgba(124,58,237,.05)}
.crm-img-upload-area input[type=file]{position:absolute;inset:0;opacity:0;cursor:pointer;width:100%;height:100%}
.crm-img-preview{width:100%;max-height:180px;object-fit:cover;border-radius:8px;display:block;margin-bottom:10px}
.crm-exam-subtabs-nav{display:flex;gap:3px;margin-bottom:18px;border-bottom:1px solid var(--crm-border);padding-bottom:0}
.crm-exam-subtab{padding:10px 20px;border:none;background:transparent;font-family:inherit;font-size:13px;font-weight:600;color:var(--crm-muted);cursor:pointer;border-bottom:3px solid transparent;margin-bottom:-1px;transition:all .15s;border-radius:8px 8px 0 0}
.crm-exam-subtab:hover{color:var(--crm-text);background:rgba(255,255,255,.04)}
.crm-exam-subtab.active{color:var(--crm-primary);border-bottom-color:var(--crm-primary)}
.crm-tarea-card{background:var(--crm-bg);border:1px solid var(--crm-border);border-radius:11px;padding:16px;margin-bottom:10px;position:relative}
.crm-tarea-card-head{display:flex;align-items:center;gap:10px;margin-bottom:12px}
.crm-tarea-num{width:26px;height:26px;border-radius:7px;background:var(--crm-primary)20;color:var(--crm-primary);display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:800;flex-shrink:0}
.crm-apunte-block{background:var(--crm-bg);border:1px solid var(--crm-border);border-radius:11px;padding:16px;margin-bottom:10px}
.crm-apunte-block-head{display:flex;align-items:center;gap:8px;margin-bottom:10px}
.crm-sortable-handle{cursor:grab;color:var(--crm-muted);flex-shrink:0;line-height:0}
.crm-sortable-handle:active{cursor:grabbing}
.sortable-ghost{opacity:.4;background:rgba(124,58,237,.08)!important}
.sortable-chosen{box-shadow:0 4px 20px rgba(0,0,0,.25)!important}
.crm-video-preview{margin-top:8px;border-radius:8px;overflow:hidden;display:none}
.crm-video-preview img{width:100%;display:block}
.crm-video-platform{display:inline-flex;align-items:center;gap:4px;font-size:11px;font-weight:600;padding:2px 7px;border-radius:99px;margin-top:4px}
.crm-video-platform.yt{background:#ff000018;color:#cc0000}
.crm-video-platform.vimeo{background:#00adef18;color:#00adef}
.crm-active-toggle-row{display:flex;align-items:center;justify-content:space-between;padding:12px 14px;background:var(--crm-bg);border:1px solid var(--crm-border);border-radius:10px;margin-bottom:10px}
</style>

<a href="<?= $crmBase ?>cursos" class="crm-back-link">
  <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M15 19l-7-7 7-7"/></svg>
  Volver a Cursos
</a>

<div class="crm-page-header" style="margin-bottom:16px">
  <div>
    <h1 style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
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

<!-- Tab navigation -->
<div class="crm-editor-tabs-nav">
  <button class="crm-editor-tab active" data-tab="info" onclick="switchTab('info')">
    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    Información
  </button>
  <button class="crm-editor-tab" data-tab="contenido" onclick="switchTab('contenido')">
    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polygon points="5 3 19 12 5 21 5 3"/></svg>
    Contenido
  </button>
  <button class="crm-editor-tab" data-tab="evaluacion" onclick="switchTab('evaluacion')">
    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01m0 4h.01"/></svg>
    Evaluación
  </button>
  <button class="crm-editor-tab" data-tab="apuntes" onclick="switchTab('apuntes')">
    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
    Apuntes
  </button>
  <button class="crm-editor-tab" data-tab="resultados" onclick="switchTab('resultados'); cargarResultados()">
    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
    Resultados
  </button>
</div>

<!-- ================================================================ -->
<!-- TAB 1: INFORMACIÓN                                                -->
<!-- ================================================================ -->
<div id="tab-info" class="crm-tab-panel active">
  <div class="crm-info-grid">

    <!-- Left col: form fields -->
    <div style="display:flex;flex-direction:column;gap:14px">

      <div class="crm-editor-panel">
        <div class="crm-editor-panel-header">
          <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          <h3>Información básica</h3>
        </div>
        <div class="crm-editor-panel-body">
          <div class="crm-active-toggle-row">
            <div>
              <div style="font-size:13px;font-weight:600;color:var(--crm-text)">Estado del curso</div>
              <div style="font-size:11.5px;color:var(--crm-muted);margin-top:1px">Visible para alumnos en el catálogo</div>
            </div>
            <label class="crm-toggle-switch" title="Activar/desactivar curso">
              <input type="checkbox" id="cActivo" <?= !empty($curso['activo']) ? 'checked' : '' ?>>
              <span class="crm-toggle-slider"></span>
            </label>
          </div>

          <div class="crm-form-group">
            <label class="crm-label">Título del curso *</label>
            <input type="text" class="crm-input" id="cTitulo" value="<?= htmlspecialchars($curso['titulo']) ?>" placeholder="Título del curso">
          </div>
          <div class="crm-form-group">
            <label class="crm-label">Descripción</label>
            <textarea class="crm-textarea" id="cDesc" rows="4" placeholder="Describe el curso, qué aprenderán los alumnos..."><?= htmlspecialchars($curso['descripcion'] ?? '') ?></textarea>
          </div>
          <div class="crm-form-row">
            <div class="crm-form-group">
              <label class="crm-label">Precio (€)</label>
              <input type="number" class="crm-input" id="cPrecio" value="<?= $curso['precio'] ?>" min="0" step="0.01" placeholder="0.00">
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
            <div class="crm-form-group" style="display:flex;align-items:flex-end;padding-bottom:2px">
              <label class="crm-label" style="display:flex;align-items:center;gap:8px;cursor:pointer">
                <input type="checkbox" id="cDestacado" <?= !empty($curso['destacado'])?'checked':'' ?> style="accent-color:var(--crm-primary);width:16px;height:16px">
                <span>Marcar como destacado ⭐</span>
              </label>
            </div>
          </div>
        </div>
      </div>

      <!-- Instructors -->
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
            <div id="instructoresAsignadosList" style="display:flex;flex-direction:column;gap:6px">
              <?php foreach ($instructoresAsignados as $iid):
                $nombre = $instructoresMap[$iid] ?? 'Instructor #'.$iid;
                $initial = mb_strtoupper(mb_substr($nombre, 0, 1, 'UTF-8'), 'UTF-8');
              ?>
              <div class="crm-instructor-item" data-id="<?= $iid ?>" style="display:flex;align-items:center;gap:10px;padding:8px 10px;background:var(--crm-bg);border-radius:8px;border:1px solid var(--crm-border)">
                <div style="width:32px;height:32px;border-radius:50%;background:rgba(124,58,237,.15);color:var(--crm-primary);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:13px;flex-shrink:0"><?= $initial ?></div>
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
    </div>

    <!-- Right col: image + quick stats -->
    <div style="display:flex;flex-direction:column;gap:14px">

      <div class="crm-editor-panel">
        <div class="crm-editor-panel-header">
          <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path stroke-linecap="round" d="M21 15l-5-5L5 21"/></svg>
          <h3>Imagen del curso</h3>
        </div>
        <div class="crm-editor-panel-body">
          <?php if (!empty($curso['imagen'])): ?>
          <img id="imgPreview" src="<?= BASE_URL ?>/img/<?= htmlspecialchars($curso['imagen']) ?>" alt="Portada" class="crm-img-preview" id="imgPreview">
          <?php else: ?>
          <img id="imgPreview" src="" alt="" class="crm-img-preview" style="display:none">
          <?php endif; ?>

          <div class="crm-img-upload-area" id="imgDropArea">
            <input type="file" id="imgFileInput" accept="image/jpeg,image/png,image/webp" onchange="handleImageSelect(this.files[0])">
            <svg width="28" height="28" fill="none" stroke="var(--crm-primary)" stroke-width="1.5" viewBox="0 0 24 24" style="margin:0 auto 8px;display:block"><path stroke-linecap="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
            <p style="font-size:13px;color:var(--crm-muted);margin:0 0 2px">Arrastra una imagen o <span style="color:var(--crm-primary);font-weight:600">haz clic</span></p>
            <p style="font-size:11px;color:var(--crm-muted);margin:0">JPG, PNG o WebP · Máx. 5 MB</p>
          </div>
          <div id="imgUploadStatus" style="margin-top:6px;font-size:12px;color:var(--crm-muted)"></div>
        </div>
      </div>

      <!-- Quick stats -->
      <div class="crm-editor-panel">
        <div class="crm-editor-panel-header">
          <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline stroke-linecap="round" points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
          <h3>Resumen del curso</h3>
        </div>
        <div class="crm-editor-panel-body" style="padding:8px 12px">
          <?php
          $numUnidades  = count($unidades);
          $numLecciones = array_sum(array_map(fn($u) => count($u['lecciones']), $unidades));
          $numPreguntas = count($preguntas);
          $numTareas    = count($tareasPracticas);
          $numApuntes   = count($apuntes);
          $stats = [
            ['label'=>'Unidades', 'val'=>$numUnidades, 'color'=>'var(--crm-primary)'],
            ['label'=>'Lecciones / Videos', 'val'=>$numLecciones, 'color'=>'var(--crm-info)'],
            ['label'=>'Preguntas test', 'val'=>$numPreguntas, 'color'=>'var(--crm-warning)'],
            ['label'=>'Tareas prácticas', 'val'=>$numTareas, 'color'=>'var(--crm-danger)'],
            ['label'=>'Secciones apuntes', 'val'=>$numApuntes, 'color'=>'var(--crm-success)'],
          ];
          foreach ($stats as $s): ?>
          <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid var(--crm-border)">
            <span style="font-size:12.5px;color:var(--crm-muted)"><?= $s['label'] ?></span>
            <span style="font-size:14px;font-weight:700;color:<?= $s['color'] ?>"><?= $s['val'] ?></span>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ================================================================ -->
<!-- TAB 2: CONTENIDO (VÍDEOS)                                         -->
<!-- ================================================================ -->
<div id="tab-contenido" class="crm-tab-panel">
  <div class="crm-editor-panel">
    <div class="crm-editor-panel-header">
      <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polygon points="5 3 19 12 5 21 5 3"/></svg>
      <h3>Contenido del curso</h3>
      <span style="font-size:11px;color:var(--crm-muted);margin-left:4px"><?= $numUnidades ?> unidades · <?= $numLecciones ?> lecciones</span>
      <button type="button" class="crm-btn crm-btn-primary crm-btn-sm" style="margin-left:auto" onclick="openModalNuevaUnidad()">
        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 4v16m8-8H4"/></svg>
        Unidad
      </button>
    </div>
    <div class="crm-editor-panel-body" style="padding:14px">
      <div class="crm-curriculum" id="curriculum">
        <?php foreach ($unidades as $u): ?>
        <div class="crm-unit is-open" data-unidad-id="<?= $u['id'] ?>">
          <div class="crm-unit-header" onclick="toggleUnit(this.closest('.crm-unit'))" style="cursor:pointer">
            <span class="crm-unit-handle" title="Arrastrar" onclick="event.stopPropagation()">
              <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="9" y1="4" x2="9" y2="20"/><line x1="15" y1="4" x2="15" y2="20"/></svg>
            </span>
            <span class="crm-unit-chevron">
              <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M19 9l-7 7-7-7"/></svg>
            </span>
            <span class="crm-unit-title"><?= htmlspecialchars($u['titulo']) ?></span>
            <span style="font-size:11px;color:var(--crm-muted);margin-right:4px"><?= count($u['lecciones']) ?> lec.</span>
            <div class="crm-unit-actions" onclick="event.stopPropagation()">
              <button class="crm-btn-icon crm-btn-sm" title="Editar unidad" onclick="openModalEditarUnidad(this, <?= $u['id'] ?>)">
                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
              </button>
              <button class="crm-btn-icon danger crm-btn-sm" title="Eliminar unidad" onclick="pedirEliminarUnidad(<?= $u['id'] ?>, this)">
                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
              </button>
            </div>
          </div>
          <div class="crm-unit-lessons sortable-lessons" data-unidad-id="<?= $u['id'] ?>">
            <?php foreach ($u['lecciones'] as $l):
              $ytId = extractYouTubeIdPhp($l['video_url'] ?? '');
            ?>
            <div class="crm-lesson" data-leccion-id="<?= $l['id'] ?>">
              <span class="crm-lesson-handle">
                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="9" y1="4" x2="9" y2="20"/><line x1="15" y1="4" x2="15" y2="20"/></svg>
              </span>
              <?php if ($ytId): ?>
                <img src="https://img.youtube.com/vi/<?= $ytId ?>/default.jpg" alt="" style="width:36px;height:27px;object-fit:cover;border-radius:4px;flex-shrink:0">
              <?php else: ?>
                <svg class="crm-lesson-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polygon points="5 3 19 12 5 21 5 3"/></svg>
              <?php endif; ?>
              <span class="crm-lesson-title"><?= htmlspecialchars($l['titulo']) ?></span>
              <?php if (!empty($l['video_url'])): ?>
              <span class="crm-video-platform <?= $ytId ? 'yt' : 'vimeo' ?>"><?= $ytId ? 'YouTube' : 'Vídeo' ?></span>
              <?php endif; ?>
              <div class="crm-lesson-actions">
                <button class="crm-btn-icon crm-btn-sm" title="Editar lección" onclick='editarLeccion(<?= $l['id'] ?>, <?= htmlspecialchars(json_encode(['titulo'=>$l['titulo'],'video_url'=>$l['video_url']??'','apuntes'=>$l['apuntes']??'']), JSON_UNESCAPED_UNICODE) ?>)'>
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
        <div id="emptyMsg" style="text-align:center;padding:40px;color:var(--crm-muted);font-size:13px">
          <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="margin-bottom:10px;opacity:.4;display:block;margin-left:auto;margin-right:auto"><polygon points="5 3 19 12 5 21 5 3"/></svg>
          No hay unidades. Haz clic en "+ Unidad" para empezar.
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<!-- ================================================================ -->
<!-- TAB 3: EVALUACIÓN                                                 -->
<!-- ================================================================ -->
<div id="tab-evaluacion" class="crm-tab-panel">

  <div class="crm-exam-subtabs-nav">
    <button class="crm-exam-subtab active" data-etab="test" onclick="switchExamTab('test')">
      <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="display:inline-block;vertical-align:middle;margin-right:5px"><path stroke-linecap="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
      Examen Tipo Test
      <?php if (count($preguntas) > 0): ?><span style="margin-left:6px;background:var(--crm-primary)25;color:var(--crm-primary);font-size:10px;font-weight:700;padding:1px 6px;border-radius:99px"><?= count($preguntas) ?></span><?php endif; ?>
    </button>
    <button class="crm-exam-subtab" data-etab="practico" onclick="switchExamTab('practico')">
      <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="display:inline-block;vertical-align:middle;margin-right:5px"><path stroke-linecap="round" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
      Examen Práctico
      <?php if (count($tareasPracticas) > 0): ?><span style="margin-left:6px;background:rgba(239,68,68,.15);color:var(--crm-danger);font-size:10px;font-weight:700;padding:1px 6px;border-radius:99px"><?= count($tareasPracticas) ?></span><?php endif; ?>
    </button>
  </div>

  <!-- Sub-panel: Test -->
  <div id="etab-test" class="crm-exam-subpanel">
    <div class="crm-editor-panel">
      <div class="crm-editor-panel-header">
        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01m0 4h.01"/></svg>
        <h3>Examen tipo test</h3>
        <button type="button" class="crm-btn crm-btn-secondary crm-btn-sm" style="margin-left:auto" onclick="agregarPregunta()">
          <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 4v16m8-8H4"/></svg>
          Pregunta
        </button>
      </div>
      <div class="crm-editor-panel-body">
        <div class="crm-form-row" style="margin-bottom:12px">
          <div class="crm-form-group">
            <label class="crm-label">Título del examen</label>
            <input type="text" class="crm-input" id="exTitulo" value="<?= htmlspecialchars($examenTest['titulo'] ?? '') ?>" placeholder="Ej: Evaluación final">
          </div>
          <div class="crm-form-group">
            <label class="crm-label">Nota mínima (0-10)</label>
            <input type="number" class="crm-input" id="exNota" value="<?= $examenTest['nota_minima'] ?? 5 ?>" min="0" max="10" step="0.5">
          </div>
        </div>
        <div class="crm-form-group" style="margin-bottom:16px">
          <label class="crm-label">Instrucciones del examen</label>
          <textarea class="crm-textarea" id="exDesc" rows="2" placeholder="Instrucciones para el alumno..."><?= htmlspecialchars($examenTest['descripcion'] ?? '') ?></textarea>
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

  <!-- Sub-panel: Práctico -->
  <div id="etab-practico" class="crm-exam-subpanel" style="display:none">
    <div class="crm-editor-panel">
      <div class="crm-editor-panel-header">
        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
        <h3>Examen práctico</h3>
        <button type="button" class="crm-btn crm-btn-secondary crm-btn-sm" style="margin-left:auto" onclick="agregarTarea()">
          <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 4v16m8-8H4"/></svg>
          Tarea
        </button>
      </div>
      <div class="crm-editor-panel-body">
        <div class="crm-form-row" style="margin-bottom:12px">
          <div class="crm-form-group">
            <label class="crm-label">Título del examen práctico</label>
            <input type="text" class="crm-input" id="exPracTitulo" value="<?= htmlspecialchars($examenPractico['titulo'] ?? '') ?>" placeholder="Ej: Proyecto final integrador">
          </div>
          <div class="crm-form-group">
            <label class="crm-label">Nota mínima (0-10)</label>
            <input type="number" class="crm-input" id="exPracNota" value="<?= $examenPractico['nota_minima'] ?? 5 ?>" min="0" max="10" step="0.5">
          </div>
        </div>
        <div class="crm-form-group" style="margin-bottom:18px">
          <label class="crm-label">Descripción general</label>
          <textarea class="crm-textarea" id="exPracDesc" rows="2" placeholder="Describe el objetivo y alcance del examen práctico..."><?= htmlspecialchars($examenPractico['descripcion'] ?? '') ?></textarea>
        </div>

        <div id="tareasList">
          <?php foreach ($tareasPracticas as $ti => $t): ?>
          <div class="crm-tarea-card" data-tarea="<?= $ti ?>">
            <div class="crm-tarea-card-head">
              <div class="crm-tarea-num"><?= $ti+1 ?></div>
              <input type="text" class="crm-input" style="flex:1" value="<?= htmlspecialchars($t['titulo']) ?>" placeholder="Título de la tarea">
              <select class="crm-select" style="width:180px;flex-shrink:0">
                <?php foreach ($tiposTarea as $tv => $tl): ?>
                <option value="<?= $tv ?>" <?= ($t['tipo']??'texto')===$tv?'selected':'' ?>><?= $tl ?></option>
                <?php endforeach; ?>
              </select>
              <input type="number" class="crm-input" style="width:80px;flex-shrink:0;text-align:center" value="<?= $t['puntos'] ?? 10 ?>" min="1" max="100" step="0.5" title="Puntos">
              <button class="crm-btn-icon danger crm-btn-sm" onclick="pedirEliminarTarea(this)" title="Eliminar tarea">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 18L18 6M6 6l12 12"/></svg>
              </button>
            </div>
            <div class="crm-form-group">
              <label class="crm-label">Enunciado / Descripción</label>
              <textarea class="crm-textarea" rows="3" placeholder="Describe qué debe hacer el alumno..."><?= htmlspecialchars($t['enunciado'] ?? '') ?></textarea>
            </div>
            <div class="crm-form-group" style="margin-bottom:0">
              <label class="crm-label">Criterios de evaluación <span style="color:var(--crm-muted);font-weight:400">(opcional)</span></label>
              <textarea class="crm-textarea" rows="2" placeholder="¿Qué se valora? Ej: funcionalidad, código limpio, documentación..."><?= htmlspecialchars($t['criterios'] ?? '') ?></textarea>
            </div>
          </div>
          <?php endforeach; ?>
        </div>

        <button type="button" class="crm-btn crm-btn-secondary crm-btn-sm" style="width:100%;margin-top:8px;justify-content:center" onclick="agregarTarea()">
          <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 4v16m8-8H4"/></svg>
          Añadir tarea práctica
        </button>
      </div>
    </div>
  </div>
</div>

<!-- ================================================================ -->
<!-- TAB 4: APUNTES                                                    -->
<!-- ================================================================ -->
<div id="tab-apuntes" class="crm-tab-panel">
  <div class="crm-editor-panel">
    <div class="crm-editor-panel-header">
      <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
      <h3>Apuntes del curso</h3>
      <button type="button" class="crm-btn crm-btn-primary crm-btn-sm" style="margin-left:auto" onclick="agregarApunte()">
        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 4v16m8-8H4"/></svg>
        Sección
      </button>
    </div>
    <div class="crm-editor-panel-body" style="padding:14px">
      <p style="font-size:12.5px;color:var(--crm-muted);margin-bottom:14px">Organiza los materiales de estudio en secciones. Los alumnos los podrán consultar desde su área personal.</p>

      <div id="apuntesList" class="sortable-apuntes">
        <?php foreach ($apuntes as $ai => $a): ?>
        <div class="crm-apunte-block" data-apunte="<?= $ai ?>">
          <div class="crm-apunte-block-head">
            <span class="crm-sortable-handle" title="Arrastrar para ordenar">
              <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="9" y1="4" x2="9" y2="20"/><line x1="15" y1="4" x2="15" y2="20"/></svg>
            </span>
            <input type="text" class="crm-input" style="flex:1" value="<?= htmlspecialchars($a['titulo'] ?? '') ?>" placeholder="Título de la sección">
            <button class="crm-btn-icon danger crm-btn-sm" onclick="pedirEliminarApunte(this)" title="Eliminar sección">
              <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            </button>
          </div>
          <textarea class="crm-textarea" rows="5" placeholder="Contenido de los apuntes, notas, resúmenes, referencias..."><?= htmlspecialchars($a['contenido'] ?? '') ?></textarea>
        </div>
        <?php endforeach; ?>
        <?php if (empty($apuntes)): ?>
        <div id="emptyApuntesMsg" style="text-align:center;padding:40px;color:var(--crm-muted)">
          <svg width="36" height="36" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="margin:0 auto 10px;display:block;opacity:.4"><path stroke-linecap="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13"/></svg>
          <p style="font-size:13px">Sin secciones todavía. Haz clic en "+ Sección" para añadir.</p>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<!-- ================================================================ -->
<!-- TAB 5: RESULTADOS                                                 -->
<!-- ================================================================ -->
<div id="tab-resultados" class="crm-tab-panel">
  <div class="crm-editor-panel">
    <div class="crm-editor-panel-header">
      <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
      <h3>Resultados de alumnos</h3>
      <button class="crm-btn crm-btn-secondary crm-btn-sm" style="margin-left:auto" onclick="cargarResultados(true)">
        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
        Actualizar
      </button>
    </div>
    <div class="crm-editor-panel-body" style="padding:0">
      <div id="resultadosLoading" style="text-align:center;padding:40px;color:var(--crm-muted)">
        <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="animation:spin 1s linear infinite;display:block;margin:0 auto 10px"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4"/></svg>
        Cargando resultados…
      </div>
      <div id="resultadosContent" style="display:none">
        <div id="resultadosStats" style="display:grid;grid-template-columns:repeat(4,1fr);gap:10px;padding:14px;border-bottom:1px solid var(--crm-border)"></div>
        <div class="crm-table-wrap" style="margin:0">
          <table class="crm-table" id="resultadosTable">
            <thead>
              <tr>
                <th>Alumno</th>
                <th style="text-align:center">Examen test</th>
                <th style="text-align:center">Práctico</th>
                <th style="text-align:center">Certificado</th>
                <th style="text-align:right">Acciones</th>
              </tr>
            </thead>
            <tbody id="resultadosTbody"></tbody>
          </table>
        </div>
      </div>
      <div id="resultadosEmpty" style="display:none;text-align:center;padding:40px;color:var(--crm-muted)">
        <svg width="36" height="36" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="margin:0 auto 10px;display:block;opacity:.4"><path stroke-linecap="round" d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
        <p style="font-size:13px">Ningún alumno matriculado todavía.</p>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Revisar práctica -->
<div class="modal fade crm-modal" id="modalRevisarPractica" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalRevisarTitle">Revisar entrega práctica</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="modalRevisarBody" style="max-height:70vh;overflow-y:auto"></div>
      <div class="modal-footer">
        <button class="crm-btn crm-btn-secondary" data-bs-dismiss="modal">Cerrar</button>
        <button class="crm-btn crm-btn-primary" onclick="guardarCalificaciones()">Guardar calificaciones</button>
      </div>
    </div>
  </div>
</div>

<!-- ================================================================ -->
<!-- MODALS                                                            -->
<!-- ================================================================ -->

<div class="modal fade crm-modal" id="modalNuevaUnidad" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Nueva unidad</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <div class="crm-form-group">
          <label class="crm-label">Título *</label>
          <input type="text" class="crm-input" id="nuevaUnidadTitulo" placeholder="Ej: Módulo 1 — Introducción">
        </div>
      </div>
      <div class="modal-footer">
        <button class="crm-btn crm-btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button class="crm-btn crm-btn-primary" onclick="crearNuevaUnidad()">Crear unidad</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade crm-modal" id="modalEditarUnidad" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Editar unidad</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
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

<div class="modal fade crm-modal" id="modalLeccion" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalLeccionTitle">Editar lección</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" style="max-height:80vh;overflow-y:auto">
        <input type="hidden" id="lecId">
        <input type="hidden" id="lecUnidadId">

        <!-- Basic fields -->
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:14px">
          <div class="crm-form-group" style="margin:0">
            <label class="crm-label">Título de la lección *</label>
            <input type="text" class="crm-input" id="lecTitulo" placeholder="Título de la lección">
          </div>
          <div class="crm-form-group" style="margin:0">
            <label class="crm-label">
              URL del vídeo
              <span id="lecPlatformBadge" style="margin-left:6px"></span>
            </label>
            <input type="url" class="crm-input" id="lecVideo" placeholder="https://youtube.com/watch?v=...">
            <div class="crm-video-preview" id="lecVideoPreview"></div>
          </div>
        </div>

        <!-- Apuntes del instructor -->
        <div class="crm-form-group" style="margin-bottom:14px">
          <label class="crm-label">
            Apuntes del instructor
            <span style="font-weight:400;color:var(--crm-muted);font-size:11px">(visible para el alumno en la lección)</span>
          </label>
          <textarea class="crm-textarea" id="lecApuntes" rows="4" placeholder="Escribe notas, conceptos clave, referencias o explicaciones adicionales para esta lección…"></textarea>
        </div>

        <!-- Recursos -->
        <div style="border:1px solid var(--crm-border);border-radius:10px;padding:14px">
          <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
            <div>
              <div style="font-size:13px;font-weight:700;color:var(--crm-text)">Recursos descargables</div>
              <div style="font-size:11.5px;color:var(--crm-muted)">Apuntes PDF, actividades no evaluables, enlaces de referencia…</div>
            </div>
            <button type="button" class="crm-btn crm-btn-secondary crm-btn-sm" onclick="mostrarFormRecurso()">
              <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 4v16m8-8H4"/></svg>
              Añadir
            </button>
          </div>

          <!-- Form añadir recurso -->
          <div id="formRecurso" style="display:none;background:var(--crm-bg);border-radius:9px;padding:12px;margin-bottom:12px">
            <div style="display:grid;grid-template-columns:1fr 130px;gap:10px;margin-bottom:8px">
              <div class="crm-form-group" style="margin:0">
                <label class="crm-label">Nombre del recurso *</label>
                <input type="text" class="crm-input" id="recNombre" placeholder="Ej: Apuntes Tema 1.pdf">
              </div>
              <div class="crm-form-group" style="margin:0">
                <label class="crm-label">Tipo</label>
                <select class="crm-select" id="recTipo" onchange="toggleRecursoUrl()">
                  <option value="link">Enlace</option>
                  <option value="pdf">PDF</option>
                  <option value="doc">Documento</option>
                  <option value="zip">Archivo ZIP</option>
                  <option value="actividad">Actividad</option>
                  <option value="video">Vídeo extra</option>
                </select>
              </div>
            </div>
            <div class="crm-form-group" style="margin-bottom:8px" id="recUrlGroup">
              <label class="crm-label">URL</label>
              <input type="url" class="crm-input" id="recUrl" placeholder="https://…">
            </div>
            <div class="crm-form-group" style="margin-bottom:8px" id="recArchivoGroup" style="display:none">
              <label class="crm-label">Subir archivo</label>
              <input type="file" class="crm-input" id="recArchivo" style="padding:6px">
            </div>
            <div class="crm-form-group" style="margin-bottom:8px">
              <label class="crm-label">Descripción <span style="font-weight:400;color:var(--crm-muted)">(opcional)</span></label>
              <input type="text" class="crm-input" id="recDesc" placeholder="Breve descripción del recurso">
            </div>
            <div style="display:flex;gap:8px">
              <button class="crm-btn crm-btn-primary crm-btn-sm" onclick="subirRecurso()">Añadir recurso</button>
              <button class="crm-btn crm-btn-secondary crm-btn-sm" onclick="document.getElementById('formRecurso').style.display='none'">Cancelar</button>
            </div>
          </div>

          <!-- Lista de recursos -->
          <div id="recursosLeccionList">
            <div id="recursosLoading" style="text-align:center;padding:12px;color:var(--crm-muted);font-size:12px">Cargando recursos…</div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button class="crm-btn crm-btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button class="crm-btn crm-btn-primary" id="btnLeccion" onclick="guardarLeccion()">Guardar lección</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade crm-modal" id="modalAddInstructor" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Añadir instructor</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
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
const CURSO_ID  = <?= $curso['id'] ?>;
const INSTR_MAP = <?= json_encode($instructoresMap, JSON_UNESCAPED_UNICODE) ?>;

/* ===== Tabs ===== */
function switchTab(name) {
  document.querySelectorAll('.crm-tab-panel').forEach(p => p.classList.remove('active'));
  document.querySelectorAll('.crm-editor-tab').forEach(t => t.classList.remove('active'));
  document.getElementById('tab-' + name).classList.add('active');
  document.querySelector(`[data-tab="${name}"]`).classList.add('active');
}

function switchExamTab(name) {
  document.querySelectorAll('.crm-exam-subpanel').forEach(p => p.style.display = 'none');
  document.querySelectorAll('.crm-exam-subtab').forEach(t => t.classList.remove('active'));
  document.getElementById('etab-' + name).style.display = '';
  document.querySelector(`[data-etab="${name}"]`).classList.add('active');
}

/* ===== Image upload ===== */
const imgDropArea = document.getElementById('imgDropArea');
imgDropArea.addEventListener('dragover', e => { e.preventDefault(); imgDropArea.classList.add('drag-over'); });
imgDropArea.addEventListener('dragleave', () => imgDropArea.classList.remove('drag-over'));
imgDropArea.addEventListener('drop', e => {
  e.preventDefault(); imgDropArea.classList.remove('drag-over');
  const f = e.dataTransfer.files[0];
  if (f) handleImageSelect(f);
});

function handleImageSelect(file) {
  if (!file) return;
  const preview = document.getElementById('imgPreview');
  const status  = document.getElementById('imgUploadStatus');
  const reader  = new FileReader();
  reader.onload = e => { preview.src = e.target.result; preview.style.display = 'block'; };
  reader.readAsDataURL(file);
  status.textContent = 'Subiendo…';
  status.style.color = 'var(--crm-muted)';
  const fd = new FormData();
  fd.append('imagen', file);
  fd.append('curso_id', CURSO_ID);
  fetch(`${window.CRM_API_URL}&action=subir_imagen_curso`, { method: 'POST', body: fd })
    .then(r => r.json())
    .then(res => {
      if (res.ok) { status.textContent = '✓ Imagen actualizada'; status.style.color = 'var(--crm-success)'; }
      else        { status.textContent = '✗ ' + res.error;       status.style.color = 'var(--crm-danger)'; }
    });
}

/* ===== SortableJS ===== */
const curriculum = document.getElementById('curriculum');
if (curriculum) {
  Sortable.create(curriculum, { animation: 150, handle: '.crm-unit-handle', ghostClass: 'sortable-ghost', chosenClass: 'sortable-chosen', onEnd: guardarOrden });
  document.querySelectorAll('.sortable-lessons').forEach(el => {
    Sortable.create(el, { animation: 150, handle: '.crm-lesson-handle', group: 'lecciones', ghostClass: 'sortable-ghost', onEnd: guardarOrden });
  });
}

const apuntesList = document.getElementById('apuntesList');
if (apuntesList) {
  Sortable.create(apuntesList, { animation: 150, handle: '.crm-sortable-handle', ghostClass: 'sortable-ghost', chosenClass: 'sortable-chosen' });
}

/* ===== Curriculum order ===== */
async function guardarOrden() {
  const unidades = [...curriculum.querySelectorAll('.crm-unit')].map(u => ({
    id: u.dataset.unidadId,
    lecciones: [...u.querySelectorAll('.crm-lesson')].map(l => ({ id: l.dataset.leccionId }))
  }));
  await CRM.api('guardar_unidades', { unidades });
}

/* ===== Units ===== */
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
    div.className = 'crm-unit is-open';
    div.dataset.unidadId = res.id;
    div.innerHTML = `
      <div class="crm-unit-header" onclick="toggleUnit(this.closest('.crm-unit'))" style="cursor:pointer">
        <span class="crm-unit-handle" onclick="event.stopPropagation()"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="9" y1="4" x2="9" y2="20"/><line x1="15" y1="4" x2="15" y2="20"/></svg></span>
        <span class="crm-unit-chevron"><svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M19 9l-7 7-7-7"/></svg></span>
        <span class="crm-unit-title">${CRM.escapeHtml(titulo)}</span>
        <span style="font-size:11px;color:var(--crm-muted);margin-right:4px">0 lec.</span>
        <div class="crm-unit-actions" onclick="event.stopPropagation()">
          <button class="crm-btn-icon crm-btn-sm" onclick="openModalEditarUnidad(this, ${res.id})">
            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
          </button>
          <button class="crm-btn-icon danger crm-btn-sm" onclick="pedirEliminarUnidad(${res.id}, this)">
            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 18L18 6M6 6l12 12"/></svg>
          </button>
        </div>
      </div>
      <div class="crm-unit-lessons sortable-lessons" data-unidad-id="${res.id}" style="max-height:none"></div>
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
  document.getElementById('editUnidadId').value    = id;
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
  CRM.toast('Título actualizado', 'info');
}

async function pedirEliminarUnidad(id, btn) {
  const ok = await CRM.confirm('¿Eliminar esta unidad y todas sus lecciones?', { title: 'Eliminar unidad', okLabel: 'Eliminar' });
  if (!ok) return;
  const res = await CRM.api('eliminar_unidad', { id });
  if (res.ok) { btn.closest('.crm-unit').remove(); CRM.toast(res.mensaje, 'success'); }
  else CRM.toast(res.error, 'error');
}

function toggleUnit(unit) {
  const lessons = unit.querySelector('.crm-unit-lessons');
  if (!lessons) return;
  const open = unit.classList.contains('is-open');
  if (open) {
    lessons.style.maxHeight = lessons.scrollHeight + 'px';
    requestAnimationFrame(() => { lessons.classList.add('collapsed'); lessons.style.maxHeight = '0'; });
    unit.classList.remove('is-open');
  } else {
    lessons.classList.remove('collapsed');
    lessons.style.maxHeight = lessons.scrollHeight + 'px';
    unit.classList.add('is-open');
    lessons.addEventListener('transitionend', () => { if (unit.classList.contains('is-open')) lessons.style.maxHeight = 'none'; }, { once: true });
  }
}
/* Init heights for all open units */
document.querySelectorAll('.crm-unit.is-open .crm-unit-lessons').forEach(el => { el.style.maxHeight = 'none'; });

/* ===== Lessons ===== */
let leccionMode = 'create';

function extractYouTubeId(url) {
  const m = url.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/)([^&?/\s]{11})/);
  return m ? m[1] : null;
}

function detectVideoPlatform(url) {
  if (!url) return null;
  if (url.match(/youtube\.com|youtu\.be/)) return 'YouTube';
  if (url.match(/vimeo\.com/)) return 'Vimeo';
  return 'Vídeo';
}

document.getElementById('lecVideo')?.addEventListener('input', function() {
  const url     = this.value.trim();
  const ytId    = extractYouTubeId(url);
  const preview = document.getElementById('lecVideoPreview');
  const badge   = document.getElementById('lecPlatformBadge');
  const platform = detectVideoPlatform(url);
  if (ytId) {
    preview.style.display = 'block';
    preview.innerHTML = `<img src="https://img.youtube.com/vi/${ytId}/mqdefault.jpg" alt="Preview">`;
    badge.innerHTML = `<span class="crm-video-platform yt">YouTube</span>`;
  } else if (platform === 'Vimeo') {
    preview.style.display = 'none'; preview.innerHTML = '';
    badge.innerHTML = `<span class="crm-video-platform vimeo">Vimeo</span>`;
  } else {
    preview.style.display = 'none'; preview.innerHTML = '';
    badge.innerHTML = '';
  }
});

function resetLeccionModal() {
  document.getElementById('lecVideoPreview').style.display = 'none';
  document.getElementById('lecVideoPreview').innerHTML     = '';
  document.getElementById('lecPlatformBadge').innerHTML    = '';
  document.getElementById('lecApuntes').value              = '';
  document.getElementById('formRecurso').style.display    = 'none';
  document.getElementById('recursosLeccionList').innerHTML = '<div id="recursosLoading" style="text-align:center;padding:12px;color:var(--crm-muted);font-size:12px">Cargando recursos…</div>';
}

function nuevaLeccion(unidadId) {
  leccionMode = 'create';
  document.getElementById('lecId').value       = '';
  document.getElementById('lecUnidadId').value = unidadId;
  document.getElementById('lecTitulo').value   = '';
  document.getElementById('lecVideo').value    = '';
  document.getElementById('modalLeccionTitle').textContent = 'Nueva lección';
  document.getElementById('btnLeccion').textContent = 'Crear lección';
  resetLeccionModal();
  document.getElementById('recursosLoading').textContent = 'Guarda primero la lección para añadir recursos.';
  openModal('modalLeccion');
}

function editarLeccion(id, data) {
  leccionMode = 'edit';
  document.getElementById('lecId').value       = id;
  document.getElementById('lecTitulo').value   = data.titulo;
  document.getElementById('lecVideo').value    = data.video_url || '';
  document.getElementById('lecApuntes').value  = data.apuntes || '';
  const ytId    = extractYouTubeId(data.video_url || '');
  const preview = document.getElementById('lecVideoPreview');
  const badge   = document.getElementById('lecPlatformBadge');
  if (ytId) {
    preview.style.display = 'block';
    preview.innerHTML = `<img src="https://img.youtube.com/vi/${ytId}/mqdefault.jpg" alt="Preview">`;
    badge.innerHTML = `<span class="crm-video-platform yt">YouTube</span>`;
  } else { preview.style.display = 'none'; preview.innerHTML = ''; badge.innerHTML = ''; }
  document.getElementById('modalLeccionTitle').textContent = 'Editar lección';
  document.getElementById('btnLeccion').textContent = 'Guardar cambios';
  resetLeccionModal();
  document.getElementById('lecApuntes').value = data.apuntes || '';
  cargarRecursosLeccion(id);
  openModal('modalLeccion');
}

async function guardarLeccion() {
  const titulo   = document.getElementById('lecTitulo').value.trim();
  const videoUrl = document.getElementById('lecVideo').value.trim();
  const apuntes  = document.getElementById('lecApuntes').value;
  const id       = document.getElementById('lecId').value;
  const unidadId = document.getElementById('lecUnidadId').value;
  if (!titulo) { CRM.toast('El título es obligatorio', 'error'); return; }

  const ytId   = extractYouTubeId(videoUrl);
  const thumbEl = ytId ? `<img src="https://img.youtube.com/vi/${ytId}/default.jpg" alt="" style="width:36px;height:27px;object-fit:cover;border-radius:4px;flex-shrink:0">` :
                         `<svg class="crm-lesson-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polygon points="5 3 19 12 5 21 5 3"/></svg>`;
  const platEl  = videoUrl ? `<span class="crm-video-platform ${ytId?'yt':'vimeo'}">${ytId?'YouTube':'Vídeo'}</span>` : '';

  if (leccionMode === 'edit') {
    const res = await CRM.api('editar_leccion', { id, titulo, video_url: videoUrl, apuntes });
    if (res.ok) {
      const el = document.querySelector(`.crm-lesson[data-leccion-id="${id}"]`);
      if (el) {
        el.querySelector('.crm-lesson-title').textContent = titulo;
        const oldThumb = el.querySelector('img, .crm-lesson-icon');
        if (oldThumb) oldThumb.outerHTML = thumbEl;
        const oldPlat  = el.querySelector('.crm-video-platform');
        if (oldPlat) oldPlat.outerHTML = platEl;
        else if (platEl) el.querySelector('.crm-lesson-title').insertAdjacentHTML('afterend', platEl);
      }
      CRM.toast(res.mensaje, 'success'); closeModal('modalLeccion');
    } else CRM.toast(res.error, 'error');
  } else {
    const res = await CRM.api('crear_leccion', { unidad_id: unidadId, titulo, video_url: videoUrl });
    if (res.ok) {
      const cont = document.querySelector(`.sortable-lessons[data-unidad-id="${unidadId}"]`);
      const div  = document.createElement('div');
      div.className = 'crm-lesson';
      div.dataset.leccionId = res.id;
      div.innerHTML = `
        <span class="crm-lesson-handle"><svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="9" y1="4" x2="9" y2="20"/><line x1="15" y1="4" x2="15" y2="20"/></svg></span>
        ${thumbEl}
        <span class="crm-lesson-title">${CRM.escapeHtml(titulo)}</span>
        ${platEl}
        <div class="crm-lesson-actions">
          <button class="crm-btn-icon crm-btn-sm" onclick='editarLeccion(${res.id}, {"titulo":"${titulo.replace(/"/g,'&quot;')}","video_url":"${videoUrl.replace(/"/g,'&quot;')}"})'>
            <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
          </button>
          <button class="crm-btn-icon danger crm-btn-sm" onclick="pedirEliminarLeccion(${res.id}, this)">
            <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 18L18 6M6 6l12 12"/></svg>
          </button>
        </div>`;
      cont.appendChild(div);
      const countSpan = cont.closest('.crm-unit').querySelector('span[style*="crm-muted"]');
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

/* ===== Instructors ===== */
function getInstructoresActuales() {
  return [...document.querySelectorAll('#instructoresAsignadosList .crm-instructor-item')].map(el => parseInt(el.dataset.id));
}

function quitarInstructor(btn, id) {
  btn.closest('.crm-instructor-item').remove();
  const list = document.getElementById('instructoresAsignadosList');
  if (!list.querySelector('.crm-instructor-item')) {
    const p = document.createElement('p');
    p.id    = 'noInstructorMsg';
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
  const nombre  = INSTR_MAP[id] || 'Instructor';
  const initial = nombre.charAt(0).toUpperCase();
  document.getElementById('noInstructorMsg')?.remove();
  const div = document.createElement('div');
  div.className  = 'crm-instructor-item';
  div.dataset.id = id;
  div.style = 'display:flex;align-items:center;gap:10px;padding:8px 10px;background:var(--crm-bg);border-radius:8px;border:1px solid var(--crm-border)';
  div.innerHTML = `
    <div style="width:32px;height:32px;border-radius:50%;background:rgba(124,58,237,.15);color:var(--crm-primary);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:13px;flex-shrink:0">${initial}</div>
    <span style="flex:1;font-size:13px;font-weight:600;color:var(--crm-text)">${CRM.escapeHtml(nombre)}</span>
    <button class="crm-btn-icon danger crm-btn-sm" onclick="quitarInstructor(this, ${id})">
      <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 18L18 6M6 6l12 12"/></svg>
    </button>`;
  document.getElementById('instructoresAsignadosList').appendChild(div);
  closeModal('modalAddInstructor');
}

/* ===== Test exam builder ===== */
let pregCount = document.querySelectorAll('.crm-exam-question').length;

function agregarPregunta() {
  const pi  = pregCount++;
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
  const pi     = bloque.dataset.pregunta;
  const lista  = bloque.querySelector('.opciones-list');
  const oi     = lista.querySelectorAll('.crm-option-row').length;
  const row    = document.createElement('div');
  row.className = 'crm-option-row';
  row.innerHTML = `
    <input type="radio" name="correcta_${pi}" class="crm-option-radio" value="${oi}">
    <input type="text" class="crm-input" style="flex:1" placeholder="Opción ${String.fromCharCode(65+oi)}">
    <button class="crm-btn-icon danger" onclick="this.closest('.crm-option-row').remove()">
      <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 18L18 6M6 6l12 12"/></svg>
    </button>`;
  lista.appendChild(row);
}

/* ===== Practical exam builder ===== */
let tareaCount = document.querySelectorAll('.crm-tarea-card').length;
const TIPOS_TAREA = <?= json_encode($tiposTarea, JSON_UNESCAPED_UNICODE) ?>;

function agregarTarea() {
  const ti  = tareaCount++;
  const div = document.createElement('div');
  div.className = 'crm-tarea-card';
  div.dataset.tarea = ti;
  const optsHtml = Object.entries(TIPOS_TAREA).map(([v,l]) => `<option value="${v}">${l}</option>`).join('');
  div.innerHTML = `
    <div class="crm-tarea-card-head">
      <div class="crm-tarea-num">${ti+1}</div>
      <input type="text" class="crm-input" style="flex:1" placeholder="Título de la tarea">
      <select class="crm-select" style="width:180px;flex-shrink:0">${optsHtml}</select>
      <input type="number" class="crm-input" style="width:80px;flex-shrink:0;text-align:center" value="10" min="1" max="100" step="0.5" title="Puntos">
      <button class="crm-btn-icon danger crm-btn-sm" onclick="pedirEliminarTarea(this)">
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 18L18 6M6 6l12 12"/></svg>
      </button>
    </div>
    <div class="crm-form-group">
      <label class="crm-label">Enunciado / Descripción</label>
      <textarea class="crm-textarea" rows="3" placeholder="Describe qué debe hacer el alumno..."></textarea>
    </div>
    <div class="crm-form-group" style="margin-bottom:0">
      <label class="crm-label">Criterios de evaluación <span style="color:var(--crm-muted);font-weight:400">(opcional)</span></label>
      <textarea class="crm-textarea" rows="2" placeholder="¿Qué se valora? Ej: funcionalidad, código limpio, documentación..."></textarea>
    </div>`;
  document.getElementById('tareasList').appendChild(div);
  div.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

async function pedirEliminarTarea(btn) {
  const ok = await CRM.confirm('¿Eliminar esta tarea?', { title: 'Eliminar tarea', okLabel: 'Eliminar' });
  if (ok) btn.closest('.crm-tarea-card').remove();
}

/* ===== Apuntes builder ===== */
let apunteCount = document.querySelectorAll('.crm-apunte-block').length;

function agregarApunte() {
  document.getElementById('emptyApuntesMsg')?.remove();
  const ai  = apunteCount++;
  const div = document.createElement('div');
  div.className = 'crm-apunte-block';
  div.dataset.apunte = ai;
  div.innerHTML = `
    <div class="crm-apunte-block-head">
      <span class="crm-sortable-handle" title="Arrastrar para ordenar">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="9" y1="4" x2="9" y2="20"/><line x1="15" y1="4" x2="15" y2="20"/></svg>
      </span>
      <input type="text" class="crm-input" style="flex:1" placeholder="Título de la sección">
      <button class="crm-btn-icon danger crm-btn-sm" onclick="pedirEliminarApunte(this)">
        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
      </button>
    </div>
    <textarea class="crm-textarea" rows="5" placeholder="Contenido de los apuntes, notas, resúmenes, referencias..."></textarea>`;
  document.getElementById('apuntesList').appendChild(div);
  div.querySelector('input').focus();
}

async function pedirEliminarApunte(btn) {
  const ok = await CRM.confirm('¿Eliminar esta sección de apuntes?', { title: 'Eliminar sección', okLabel: 'Eliminar' });
  if (!ok) return;
  btn.closest('.crm-apunte-block').remove();
  if (!document.querySelector('.crm-apunte-block')) {
    document.getElementById('apuntesList').innerHTML = '<div id="emptyApuntesMsg" style="text-align:center;padding:40px;color:var(--crm-muted)"><p style="font-size:13px">Sin secciones todavía.</p></div>';
  }
}

/* ===== Save everything ===== */
async function guardarTodo() {
  const btn = document.getElementById('btnGuardarTodo');
  btn.disabled = true;
  btn.innerHTML = '<svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="animation:spin 1s linear infinite"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4"/></svg> Guardando…';

  // 1. Basic info
  const r1 = await CRM.api('actualizar_curso', {
    id:          CURSO_ID,
    titulo:      document.getElementById('cTitulo').value,
    descripcion: document.getElementById('cDesc').value,
    precio:      parseFloat(document.getElementById('cPrecio').value) || 0,
    nivel:       document.getElementById('cNivel').value,
    categoria:   document.getElementById('cCategoria').value,
    destacado:   document.getElementById('cDestacado').checked ? 1 : 0,
    activo:      document.getElementById('cActivo').checked ? 1 : 0,
  });

  // 2. Instructors
  await CRM.api('asignar_instructor', { curso_id: CURSO_ID, instructor_ids: getInstructoresActuales() });

  // 3. Curriculum order
  await guardarOrden();

  // 4. Test exam
  const preguntas = [...document.querySelectorAll('.crm-exam-question')].map(pEl => {
    const inputs    = pEl.querySelectorAll('input[type=text]');
    const enunciado = inputs[0]?.value || '';
    const opciones  = [...pEl.querySelectorAll('.crm-option-row')].map(oEl => ({
      texto:    oEl.querySelector('input[type=text]')?.value || '',
      correcta: oEl.querySelector('input[type=radio]')?.checked ? 1 : 0,
    }));
    return { enunciado, opciones };
  });
  const exTitulo = document.getElementById('exTitulo').value.trim();
  if (exTitulo) {
    await CRM.api('guardar_examen', {
      curso_id:    CURSO_ID,
      titulo:      exTitulo,
      descripcion: document.getElementById('exDesc').value,
      nota_minima: parseFloat(document.getElementById('exNota').value) || 5,
      preguntas,
    });
  }

  // 5. Practical exam
  const tareas = [...document.querySelectorAll('.crm-tarea-card')].map(tEl => {
    const inputs = tEl.querySelectorAll('input[type=text], input[type=number]');
    const txts   = tEl.querySelectorAll('textarea');
    const sel    = tEl.querySelector('select');
    return {
      titulo:    inputs[0]?.value || '',
      tipo:      sel?.value || 'texto',
      puntos:    parseFloat(inputs[1]?.value) || 10,
      enunciado: txts[0]?.value || '',
      criterios: txts[1]?.value || '',
    };
  });
  const exPracTitulo = document.getElementById('exPracTitulo').value.trim();
  await CRM.api('guardar_examen_practico', {
    curso_id:    CURSO_ID,
    titulo:      exPracTitulo,
    descripcion: document.getElementById('exPracDesc').value,
    nota_minima: parseFloat(document.getElementById('exPracNota').value) || 5,
    tareas,
  });

  // 6. Apuntes
  const apuntes = [...document.querySelectorAll('.crm-apunte-block')].map(aEl => ({
    titulo:    aEl.querySelector('input[type=text]')?.value || '',
    contenido: aEl.querySelector('textarea')?.value || '',
  }));
  await CRM.api('guardar_apuntes', { curso_id: CURSO_ID, apuntes });

  btn.disabled = false;
  btn.innerHTML = '<svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M5 13l4 4L19 7"/></svg> Guardar todo';
  if (r1.ok) CRM.toast('✓ Todos los cambios guardados correctamente', 'success');
  else CRM.toast(r1.error || 'Error al guardar', 'error');
}

/* ===== Lesson resources ===== */
const TIPO_ICONS = { pdf:'📄', doc:'📝', zip:'🗜️', link:'🔗', actividad:'✏️', video:'▶️' };

function toggleRecursoUrl() {
  const tipo = document.getElementById('recTipo').value;
  const needsFile = ['pdf','doc','zip'].includes(tipo);
  document.getElementById('recUrlGroup').style.display    = needsFile ? 'none' : '';
  document.getElementById('recArchivoGroup').style.display = needsFile ? '' : 'none';
}

function mostrarFormRecurso() {
  const f = document.getElementById('formRecurso');
  f.style.display = f.style.display === 'none' ? '' : 'none';
  if (f.style.display !== 'none') {
    document.getElementById('recNombre').value  = '';
    document.getElementById('recUrl').value     = '';
    document.getElementById('recDesc').value    = '';
    document.getElementById('recTipo').value    = 'link';
    toggleRecursoUrl();
  }
}

async function cargarRecursosLeccion(leccionId) {
  const list = document.getElementById('recursosLeccionList');
  list.innerHTML = '<div style="text-align:center;padding:12px;color:var(--crm-muted);font-size:12px">Cargando…</div>';
  const res = await fetch(`${window.CRM_API_URL}&action=get_recursos_leccion&leccion_id=${leccionId}`).then(r=>r.json());
  renderRecursos(res.recursos || []);
}

function renderRecursos(recursos) {
  const list = document.getElementById('recursosLeccionList');
  if (!recursos.length) {
    list.innerHTML = '<p style="font-size:12px;color:var(--crm-muted);text-align:center;padding:12px">Sin recursos añadidos todavía.</p>';
    return;
  }
  list.innerHTML = recursos.map(r => `
    <div style="display:flex;align-items:center;gap:10px;padding:8px 10px;background:var(--crm-bg);border-radius:8px;border:1px solid var(--crm-border);margin-bottom:6px">
      <span style="font-size:16px;flex-shrink:0">${TIPO_ICONS[r.tipo] || '📎'}</span>
      <div style="flex:1;min-width:0">
        <div style="font-size:13px;font-weight:600;color:var(--crm-text)">${CRM.escapeHtml(r.nombre)}</div>
        ${r.descripcion ? `<div style="font-size:11px;color:var(--crm-muted)">${CRM.escapeHtml(r.descripcion)}</div>` : ''}
      </div>
      <a href="${r.url_o_ruta}" target="_blank" class="crm-btn-icon" title="Ver/Descargar">
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
      </a>
      <button class="crm-btn-icon danger" onclick="eliminarRecurso(${r.id})" title="Eliminar">
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 18L18 6M6 6l12 12"/></svg>
      </button>
    </div>`).join('');
}

async function subirRecurso() {
  const leccionId = document.getElementById('lecId').value;
  if (!leccionId) { CRM.toast('Guarda primero la lección', 'info'); return; }
  const nombre = document.getElementById('recNombre').value.trim();
  const tipo   = document.getElementById('recTipo').value;
  const desc   = document.getElementById('recDesc').value.trim();
  if (!nombre) { CRM.toast('El nombre es obligatorio', 'error'); return; }

  const fd = new FormData();
  fd.append('leccion_id', leccionId);
  fd.append('nombre', nombre);
  fd.append('tipo', tipo);
  fd.append('descripcion', desc);

  const needsFile = ['pdf','doc','zip'].includes(tipo);
  if (needsFile) {
    const file = document.getElementById('recArchivo').files[0];
    if (!file) { CRM.toast('Selecciona un archivo', 'error'); return; }
    fd.append('archivo', file);
  } else {
    const url = document.getElementById('recUrl').value.trim();
    if (!url) { CRM.toast('La URL es obligatoria', 'error'); return; }
    fd.append('url', url);
  }

  const res = await fetch(`${window.CRM_API_URL}&action=subir_recurso_leccion`, { method: 'POST', body: fd }).then(r=>r.json());
  if (res.ok) {
    CRM.toast('Recurso añadido', 'success');
    document.getElementById('formRecurso').style.display = 'none';
    cargarRecursosLeccion(leccionId);
  } else CRM.toast(res.error, 'error');
}

async function eliminarRecurso(id) {
  const ok = await CRM.confirm('¿Eliminar este recurso?', { title: 'Eliminar recurso', okLabel: 'Eliminar' });
  if (!ok) return;
  const res = await CRM.api('eliminar_recurso', { id });
  if (res.ok) {
    CRM.toast(res.mensaje, 'success');
    cargarRecursosLeccion(document.getElementById('lecId').value);
  } else CRM.toast(res.error, 'error');
}

/* ===== Resultados tab ===== */
let resultadosLoaded = false;
async function cargarResultados(force = false) {
  if (resultadosLoaded && !force) return;
  resultadosLoaded = true;
  document.getElementById('resultadosLoading').style.display  = '';
  document.getElementById('resultadosContent').style.display  = 'none';
  document.getElementById('resultadosEmpty').style.display    = 'none';

  const res = await fetch(`${window.CRM_API_URL}&action=get_resultados_curso&curso_id=${CURSO_ID}`).then(r=>r.json());
  document.getElementById('resultadosLoading').style.display = 'none';
  if (!res.ok) { CRM.toast(res.error, 'error'); return; }

  const alumnos = res.alumnos || [];
  if (!alumnos.length) { document.getElementById('resultadosEmpty').style.display = ''; return; }

  const aprobadosTest    = alumnos.filter(a => a.test?.aprobado).length;
  const entregadosPrac   = alumnos.filter(a => (a.practico?.total || 0) > 0).length;
  const pendientesRevision = alumnos.filter(a => (a.practico?.total || 0) > 0 && (a.practico?.revisadas || 0) < (a.practico?.total || 0)).length;

  document.getElementById('resultadosStats').innerHTML = [
    ['Matriculados', alumnos.length, 'var(--crm-primary)'],
    ['Aprobaron test', aprobadosTest, 'var(--crm-success)'],
    ['Entregaron práctico', entregadosPrac, 'var(--crm-info)'],
    ['Pendiente revisión', pendientesRevision, 'var(--crm-warning)'],
  ].map(([lbl, val, color]) => `
    <div class="crm-card" style="padding:12px 14px;text-align:center">
      <div style="font-size:22px;font-weight:800;color:${color}">${val}</div>
      <div style="font-size:11px;color:var(--crm-muted);margin-top:2px">${lbl}</div>
    </div>`).join('');

  document.getElementById('resultadosTbody').innerHTML = alumnos.map(al => {
    const test = al.test;
    const prac = al.practico;
    const totalT = prac?.total_tareas || 0;

    const testCell = test
      ? `<span class="crm-badge ${test.aprobado ? 'activo' : 'inactivo'}" title="${test.realizado_en}">
           ${test.aprobado ? '✓ Aprobado' : '✗ Suspenso'} · ${test.nota}/10
         </span>`
      : `<span style="font-size:11px;color:var(--crm-muted)">No realizado</span>`;

    const pracCell = totalT === 0
      ? `<span style="font-size:11px;color:var(--crm-muted)">Sin examen práctico</span>`
      : (prac?.total || 0) === 0
        ? `<span style="font-size:11px;color:var(--crm-muted)">Sin entregar</span>`
        : `<span class="crm-badge ${(prac.revisadas || 0) >= totalT ? 'activo' : 'warning'}" style="background:${(prac.revisadas||0)>=totalT?'':'rgba(245,158,11,.15)'};color:${(prac.revisadas||0)>=totalT?'':'var(--crm-warning)'}">
             ${prac.revisadas || 0}/${totalT} revisadas
             ${prac.nota_media !== null ? '· ' + parseFloat(prac.nota_media).toFixed(1) + '/10' : ''}
           </span>`;

    const accionPrac = totalT > 0 && (prac?.total || 0) > 0
      ? `<button class="crm-btn crm-btn-secondary crm-btn-sm" onclick="verEntregasPractica(${al.id}, '${CRM.escapeHtml(al.nombre)}')">Ver entrega</button>`
      : '';

    return `<tr>
      <td>
        <div style="display:flex;align-items:center;gap:8px">
          <div style="width:30px;height:30px;border-radius:50%;background:rgba(124,58,237,.15);color:var(--crm-primary);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:12px;flex-shrink:0">${al.nombre.charAt(0).toUpperCase()}</div>
          <div>
            <div style="font-size:13px;font-weight:600">${CRM.escapeHtml(al.nombre)}</div>
            <div style="font-size:11px;color:var(--crm-muted)">${CRM.escapeHtml(al.email)}</div>
          </div>
        </div>
      </td>
      <td style="text-align:center">${testCell}</td>
      <td style="text-align:center">${pracCell}</td>
      <td style="text-align:center">
        ${test?.aprobado ? '<span class="crm-badge activo">✓</span>' : '<span style="font-size:11px;color:var(--crm-muted)">—</span>'}
      </td>
      <td style="text-align:right">${accionPrac}</td>
    </tr>`;
  }).join('');

  document.getElementById('resultadosContent').style.display = '';
}

let _revisionAlumnoId = null;
async function verEntregasPractica(alumnoId, nombre) {
  _revisionAlumnoId = alumnoId;
  document.getElementById('modalRevisarTitle').textContent = `Entregas de ${nombre}`;
  const body = document.getElementById('modalRevisarBody');
  body.innerHTML = '<div style="text-align:center;padding:20px;color:var(--crm-muted)">Cargando…</div>';
  openModal('modalRevisarPractica');

  const res = await fetch(`${window.CRM_API_URL}&action=get_entregas_alumno&alumno_id=${alumnoId}&curso_id=${CURSO_ID}`).then(r=>r.json());
  if (!res.ok) { body.innerHTML = `<p style="color:var(--crm-danger)">${res.error}</p>`; return; }

  const entregas = res.entregas || [];
  body.innerHTML = entregas.map((e, i) => `
    <div style="border:1px solid var(--crm-border);border-radius:10px;padding:16px;margin-bottom:12px">
      <div style="display:flex;align-items:center;gap:8px;margin-bottom:10px">
        <div style="width:24px;height:24px;border-radius:6px;background:var(--crm-primary)20;color:var(--crm-primary);display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:800">${i+1}</div>
        <div style="font-size:13px;font-weight:700;flex:1">${CRM.escapeHtml(e.tarea_titulo || 'Tarea')}</div>
        <span class="crm-badge ${e.revisado ? 'activo' : 'warning'}">${e.revisado ? 'Revisada' : 'Pendiente'}</span>
      </div>
      <div style="font-size:12.5px;color:var(--crm-text);background:var(--crm-bg);border-radius:8px;padding:10px;margin-bottom:10px;min-height:60px;white-space:pre-wrap;line-height:1.6">${CRM.escapeHtml(e.respuesta_texto || 'Sin respuesta de texto.')}</div>
      ${e.archivo ? `<a href="${e.archivo}" target="_blank" style="font-size:12px;color:var(--crm-info)">📎 Descargar archivo adjunto</a>` : ''}
      <div style="display:grid;grid-template-columns:100px 1fr;gap:10px;margin-top:12px">
        <div>
          <label style="font-size:11px;color:var(--crm-muted);display:block;margin-bottom:4px">Nota (0-10)</label>
          <input type="number" class="crm-input entrega-nota" data-id="${e.id}" value="${e.nota !== null ? e.nota : ''}" min="0" max="10" step="0.5" placeholder="—">
        </div>
        <div>
          <label style="font-size:11px;color:var(--crm-muted);display:block;margin-bottom:4px">Feedback</label>
          <input type="text" class="crm-input entrega-feedback" data-id="${e.id}" value="${CRM.escapeHtml(e.feedback || '')}" placeholder="Comentario al alumno…">
        </div>
      </div>
    </div>`).join('') || '<p style="color:var(--crm-muted);text-align:center;padding:20px">Sin entregas todavía.</p>';
}

async function guardarCalificaciones() {
  const notas    = [...document.querySelectorAll('.entrega-nota')];
  const feedbacks = [...document.querySelectorAll('.entrega-feedback')];
  let saved = 0;
  for (let i = 0; i < notas.length; i++) {
    const id       = notas[i].dataset.id;
    const nota     = notas[i].value !== '' ? notas[i].value : null;
    const feedback = feedbacks[i].value;
    const res = await CRM.api('revisar_practica', { entrega_id: id, nota, feedback });
    if (res.ok) saved++;
  }
  CRM.toast(`${saved} entrega(s) calificada(s)`, 'success');
  closeModal('modalRevisarPractica');
  cargarResultados(true);
}

const _spinStyle = document.createElement('style');
_spinStyle.textContent = '@keyframes spin { to { transform: rotate(360deg); } }';
document.head.appendChild(_spinStyle);
</script>
