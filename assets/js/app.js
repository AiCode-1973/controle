document.addEventListener('DOMContentLoaded', function () {
    // Toggle sidebar
    const toggle  = document.getElementById('sidebarToggle');
    const wrapper = document.getElementById('wrapper');
    if (toggle && wrapper) {
        toggle.addEventListener('click', function () {
            wrapper.classList.toggle('toggled');
        });
    }

    // Bloqueia clique direito
    document.addEventListener('contextmenu', e => e.preventDefault());

    // Bloqueia atalhos de desenvolvedor e cópia de página
    document.addEventListener('keydown', function (e) {
        const k = e.key.toLowerCase();

        // F12 — DevTools
        if (e.key === 'F12') { e.preventDefault(); return; }

        if (e.ctrlKey || e.metaKey) {
            // Ctrl+U — ver código-fonte
            // Ctrl+S — salvar página
            // Ctrl+P — imprimir (exceto páginas de relatório)
            if (['u', 's'].includes(k)) { e.preventDefault(); return; }

            if (e.shiftKey) {
                // Ctrl+Shift+I, Ctrl+Shift+J, Ctrl+Shift+C — DevTools
                if (['i', 'j', 'c'].includes(k)) { e.preventDefault(); return; }
            }
        }
    });

    // Bloqueia arrastar imagens e texto
    document.addEventListener('dragstart', e => {
        if (e.target.tagName !== 'INPUT' && e.target.tagName !== 'TEXTAREA') {
            e.preventDefault();
        }
    });
});
