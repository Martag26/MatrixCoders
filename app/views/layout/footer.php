<?php if (!empty($_SESSION['usuario_id'])): ?>
<link rel="stylesheet" href="<?= BASE_URL ?>/css/chatbot-widget.css">

<!-- ── Floating Oráculo widget ── -->
<button id="cbFloatBtn" title="Oráculo — Asistente IA" aria-label="Abrir asistente">
    <span class="cb-float-dot" id="cbFloatDot"></span>
    <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
    </svg>
</button>

<div id="cbFloatPanel">
    <div class="cbw-header">
        <div class="cbw-header-left">
            <div class="cbw-avatar-header">🤖</div>
            <div>
                <p class="cbw-title">Oráculo</p>
                <p class="cbw-subtitle">Asistente IA · MatrixCoders</p>
            </div>
        </div>
        <div class="cbw-header-actions">
            <button class="cbw-header-btn" id="cbwReiniciar" title="Nueva conversación">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 .49-4"/>
                </svg>
            </button>
            <button class="cbw-header-btn" id="cbwCerrar" title="Cerrar">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>
    </div>

    <div class="cbw-messages" id="cbwMessages">
        <div class="cbw-msg">
            <div class="cbw-av bot">🤖</div>
            <div class="cbw-bubble bot">
                ¡Hola! Soy <strong>Oráculo</strong>. ¿En qué puedo ayudarte hoy?
            </div>
        </div>
    </div>

    <div class="cbw-input-area">
        <textarea id="cbwInput" class="cbw-input" placeholder="Escribe tu pregunta…" rows="1"
                  onkeydown="if(event.key==='Enter'&&!event.shiftKey){event.preventDefault();cbwEnviar()}"></textarea>
        <button class="cbw-send" id="cbwSendBtn" onclick="cbwEnviar()" title="Enviar">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/>
            </svg>
        </button>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
<script>
(function () {
    const BASE_URL  = '<?= BASE_URL ?>';
    const floatBtn  = document.getElementById('cbFloatBtn');
    const panel     = document.getElementById('cbFloatPanel');
    const cerrarBtn = document.getElementById('cbwCerrar');
    const reinicBtn = document.getElementById('cbwReiniciar');
    const messages  = document.getElementById('cbwMessages');
    const input     = document.getElementById('cbwInput');
    const sendBtn   = document.getElementById('cbwSendBtn');
    const floatDot  = document.getElementById('cbFloatDot');
    let panelOpen   = false;
    let hasNewMsg   = false;

    function togglePanel() {
        panelOpen = !panelOpen;
        panel.classList.toggle('open', panelOpen);
        floatBtn.classList.toggle('active', panelOpen);
        if (panelOpen) {
            floatDot.style.display = 'none';
            hasNewMsg = false;
            setTimeout(() => input.focus(), 250);
        }
    }

    floatBtn.addEventListener('click', togglePanel);
    cerrarBtn.addEventListener('click', () => { panelOpen = true; togglePanel(); });

    function addMsg(text, tipo) {
        const wrap   = document.createElement('div'); wrap.className = 'cbw-msg ' + tipo;
        const av     = document.createElement('div'); av.className = 'cbw-av ' + tipo; av.textContent = tipo === 'bot' ? '🤖' : '👤';
        const bubble = document.createElement('div'); bubble.className = 'cbw-bubble ' + tipo;
        if (tipo === 'bot') bubble.innerHTML = marked.parse(text);
        else bubble.textContent = text;
        wrap.appendChild(av); wrap.appendChild(bubble);
        messages.appendChild(wrap);
        messages.scrollTop = messages.scrollHeight;
        if (!panelOpen && tipo === 'bot') { floatDot.style.display = 'block'; hasNewMsg = true; }
        return wrap;
    }

    function addThinking() {
        const w = document.createElement('div'); w.className = 'cbw-msg'; w.id = 'cbw-thinking';
        w.innerHTML = '<div class="cbw-av bot">🤖</div><div class="cbw-bubble bot cbw-thinking">Pensando…</div>';
        messages.appendChild(w); messages.scrollTop = messages.scrollHeight;
    }

    async function cbwEnviar() {
        const q = input.value.trim();
        if (!q || sendBtn.disabled) return;
        input.value = ''; input.style.height = 'auto';
        sendBtn.disabled = true;
        addMsg(q, 'user');
        addThinking();
        try {
            const fd = new FormData(); fd.append('pregunta', q);
            const res = await fetch(BASE_URL + '/index.php?url=chatbot', { method: 'POST', body: fd }).then(r => r.json());
            document.getElementById('cbw-thinking')?.remove();
            if (res.ok) addMsg(res.respuesta, 'bot');
            else addMsg('Error: ' + (res.error || 'no se pudo procesar'), 'bot');
        } catch(e) {
            document.getElementById('cbw-thinking')?.remove();
            addMsg('Error de conexión. Inténtalo de nuevo.', 'bot');
        } finally {
            sendBtn.disabled = false;
            input.focus();
        }
    }

    window.cbwEnviar = cbwEnviar;

    reinicBtn.addEventListener('click', async function () {
        this.disabled = true;
        try {
            const fd = new FormData(); fd.append('accion', 'reiniciar');
            await fetch(BASE_URL + '/index.php?url=chatbot', { method: 'POST', body: fd });
            messages.innerHTML = '';
            const div = document.createElement('div'); div.className = 'cbw-divider'; div.textContent = 'Nueva conversación';
            messages.appendChild(div);
            addMsg('Conversación reiniciada. ¿En qué puedo ayudarte?', 'bot');
        } finally { this.disabled = false; }
    });

    input.addEventListener('input', () => { input.style.height = 'auto'; input.style.height = Math.min(input.scrollHeight, 90) + 'px'; });
})();
</script>
<?php endif; ?>

<!--
    Layout parcial: pie de página (footer) de la aplicación.
    Se incluye en todas las vistas y muestra información corporativa,
    enlaces rápidos de navegación, datos de contacto y redes sociales.
-->
<footer class="footer">
    <div class="footer-wrap">

        <!-- Columna 1: logo y descripción de la plataforma -->
        <div>
            <img class="footer-logo" src="<?= BASE_URL ?>/img/logo.png" alt="logo">
            <p>
                Plataforma de aprendizaje en línea especializada en desarrollo de software. Aprende a tu ritmo con cursos
                prácticos, materiales descargables y certificaciones que te ayudarán a crecer profesionalmente.
            </p>
        </div>

        <!-- Columna 2: enlaces rápidos de navegación interna -->
        <div class="footer-links">
            <h4>Enlaces rápidos</h4>
            <ul>
                <li><a href="#">Sobre nosotros</a></li>
                <li><a href="/index.php?r=cursos/index">Cursos</a></li>
                <li><a href="#">Blog</a></li>
                <li><a href="#">Contacto</a></li>
            </ul>
        </div>

        <!-- Columna 3: información de contacto de la empresa -->
        <div class="footer-contact">
            <h4>Contáctanos</h4>
            <p><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0;margin-top:2px"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg><span>Calle Innovación 123, Madrid, España</span></p>
            <p><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0;margin-top:2px"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg><span>+34 600 123 456</span></p>
            <p><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0;margin-top:2px"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg><span>soporte@matrixcoders.com</span></p>
            <p><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0;margin-top:2px"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg><span>info@matrixcoders.com</span></p>
        </div>
    </div>

    <!-- Barra inferior: iconos de redes sociales -->
    <div class="footer-bottom">
        <a href="#" aria-label="twitter">𝕏</a>
        <a href="#" aria-label="instagram">IG</a>
        <a href="#" aria-label="linkedin">in</a>
        <a href="#" aria-label="web">🌐</a>
    </div>
</footer>
