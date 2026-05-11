<label class="field-label">Tu respuesta</label>
<textarea class="te-textarea" id="txt-te" placeholder="Escribe aquí tu solución, razonamiento o descripción del trabajo realizado…"><?= htmlspecialchars($entrega['respuesta'] ?? '') ?></textarea>

<div class="file-drop" id="drop-te"
     ondragover="this.classList.add('drag-over');event.preventDefault()"
     ondragleave="this.classList.remove('drag-over')"
     ondrop="handleDrop(event)">
    <input type="file" id="file-te"
           accept=".pdf,.doc,.docx,.zip,.rar,.txt,.png,.jpg,.jpeg,.mp4,.py,.js,.html,.css,.php"
           onchange="showFile(this)">
    <svg width="22" height="22" fill="none" stroke="#f59e0b" stroke-width="1.5" viewBox="0 0 24 24" style="display:block;margin:0 auto 6px"><path stroke-linecap="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
    <p>Arrastra un archivo o <strong style="color:#f59e0b">haz clic</strong> para adjuntar</p>
    <p style="font-size:.75rem;margin-top:2px">PDF, DOC, ZIP, imágenes, código… Máx. 50 MB</p>
    <p id="fname-te" class="file-selected" style="display:none"></p>
</div>

<button class="btn-entregar" id="btn-entregar" onclick="entregar()">
    Enviar tarea →
</button>
