        </main><!-- page-body -->
    </div><!-- page-content-wrapper -->
</div><!-- wrapper -->

<!-- Watermark com nome do usuário -->
<div id="wm" aria-hidden="true"></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= BASE_PATH ?>/assets/js/app.js"></script>
<script>
(function() {
    // Gera watermark diagonal com o nome do usuário
    const nome = <?= json_encode($_SESSION['usuario_nome'] ?? 'Usuário') ?>;
    const wm   = document.getElementById('wm');
    let html   = '';
    for (let i = 0; i < 8; i++) {
        let row = '';
        for (let j = 0; j < 6; j++) row += `<span>${nome}</span>`;
        html += `<div class="wm-row">${row}</div>`;
    }
    wm.innerHTML = html;
})();
</script>
</body>
</html>
