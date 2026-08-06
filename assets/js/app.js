document.addEventListener('DOMContentLoaded', function () {
    // Toggle sidebar
    const toggle  = document.getElementById('sidebarToggle');
    const wrapper = document.getElementById('wrapper');
    if (toggle && wrapper) {
        toggle.addEventListener('click', function () {
            wrapper.classList.toggle('toggled');
        });
    }

    // ── Toast de aviso de proteção ──────────────────────────
    function showBlockToast(msg) {
        const t = document.createElement('div');
        t.textContent = '🔒 ' + msg;
        Object.assign(t.style, {
            position: 'fixed', bottom: '24px', right: '24px',
            background: '#1a2332', color: '#fff',
            padding: '10px 18px', borderRadius: '8px',
            fontSize: '13px', fontWeight: '600',
            boxShadow: '0 4px 16px rgba(0,0,0,.3)',
            zIndex: '99999', opacity: '0',
            transition: 'opacity .2s',
            pointerEvents: 'none',
        });
        document.body.appendChild(t);
        requestAnimationFrame(() => { t.style.opacity = '1'; });
        setTimeout(() => {
            t.style.opacity = '0';
            setTimeout(() => t.remove(), 250);
        }, 2200);
    }

    // ── Bloqueia clique direito com toast ───────────────────
    document.addEventListener('contextmenu', function (e) {
        e.preventDefault();
        e.stopPropagation();
        showBlockToast('Ação não permitida neste sistema.');
        return false;
    });

    // ── Bloqueia atalhos de teclado ─────────────────────────
    document.addEventListener('keydown', function (e) {
        const k = e.key.toLowerCase();
        let bloqueado = false;

        if (e.key === 'F12') bloqueado = true;
        if (e.key === 'PrintScreen') bloqueado = true;

        if (e.ctrlKey || e.metaKey) {
            if (['u', 's', 'p'].includes(k)) bloqueado = true;

            // Ctrl+A bloqueia apenas fora de inputs/textareas
            if (k === 'a') {
                const tag = document.activeElement?.tagName;
                if (!['INPUT', 'TEXTAREA', 'SELECT'].includes(tag)) bloqueado = true;
            }

            if (e.shiftKey && ['i', 'j', 'c', 'k'].includes(k)) bloqueado = true;
        }

        if (bloqueado) {
            e.preventDefault();
            e.stopPropagation();
            showBlockToast('Ação bloqueada por política de segurança.');
            return false;
        }
    });

    // ── Bloqueia arrastar conteúdo ──────────────────────────
    document.addEventListener('dragstart', function (e) {
        const tag = e.target.tagName;
        if (!['INPUT', 'TEXTAREA'].includes(tag)) {
            e.preventDefault();
        }
    });

    // ── Limpa clipboard ao perder foco (tab) ───────────────
    document.addEventListener('visibilitychange', function () {
        if (document.visibilityState === 'hidden') {
            navigator.clipboard?.writeText('').catch(() => {});
        }
    });
});
