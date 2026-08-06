<?php
require_once __DIR__ . '/../conexao.php';
require_once __DIR__ . '/../includes/auth.php';
$titulo     = 'Prompts';
$menu_ativo = 'prompts';

$categoria_filtro = trim($_GET['cat'] ?? '');
$busca            = trim($_GET['q']   ?? '');
$favoritos        = isset($_GET['fav']);

$where  = ['1=1'];
$params = [];

if ($categoria_filtro !== '') {
    $where[]  = 'categoria = ?';
    $params[] = $categoria_filtro;
}
if ($busca !== '') {
    $where[]  = '(titulo LIKE ? OR conteudo LIKE ?)';
    $params[] = "%$busca%";
    $params[] = "%$busca%";
}
if ($favoritos) {
    $where[] = 'favorito = 1';
}

$sql = 'SELECT * FROM prompts WHERE ' . implode(' AND ', $where) . ' ORDER BY favorito DESC, atualizado_em DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$prompts = $stmt->fetchAll();

$categorias = $pdo->query("SELECT DISTINCT categoria FROM prompts WHERE categoria IS NOT NULL AND categoria != '' ORDER BY categoria")->fetchAll(PDO::FETCH_COLUMN);

include __DIR__ . '/../includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <!-- Filtros -->
    <form method="get" class="d-flex gap-2 align-items-center flex-wrap">
        <div class="input-group" style="max-width:260px">
            <span class="input-group-text"><i class="fas fa-search"></i></span>
            <input type="text" name="q" class="form-control" placeholder="Buscar..." value="<?= sanitize($busca) ?>">
        </div>
        <select name="cat" class="form-select" style="max-width:180px" onchange="this.form.submit()">
            <option value="">Todas as categorias</option>
            <?php foreach ($categorias as $cat): ?>
                <option value="<?= sanitize($cat) ?>" <?= $categoria_filtro === $cat ? 'selected' : '' ?>>
                    <?= sanitize($cat) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <a href="?fav=1" class="btn <?= $favoritos ? 'btn-warning' : 'btn-outline-warning' ?>" title="Favoritos">
            <i class="fas fa-star"></i>
        </a>
        <?php if ($busca || $categoria_filtro || $favoritos): ?>
            <a href="index.php" class="btn btn-outline-secondary"><i class="fas fa-times"></i></a>
        <?php endif; ?>
    </form>

    <a href="form.php" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i>Novo prompt
    </a>
</div>

<?php if (empty($prompts)): ?>
<div class="card">
    <div class="card-body text-center text-muted py-5">
        <i class="fas fa-robot fa-3x d-block mb-3 opacity-25"></i>
        <p class="mb-0">Nenhum prompt encontrado.</p>
    </div>
</div>
<?php else: ?>
<div class="row g-3">
    <?php foreach ($prompts as $p): ?>
    <div class="col-md-6 col-xl-4">
        <div class="card h-100 prompt-card">
            <div class="card-body d-flex flex-column">
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div class="flex-grow-1 me-2">
                        <div class="fw-semibold" style="font-size:.88rem"><?= sanitize($p['titulo']) ?></div>
                        <?php if ($p['categoria']): ?>
                            <span class="badge mt-1" style="background:#e8f0fe;color:#1a56db;font-size:.65rem">
                                <?= sanitize($p['categoria']) ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    <form method="post" action="favorito.php" class="d-inline">
                        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                        <input type="hidden" name="id" value="<?= $p['id'] ?>">
                        <button type="submit" class="btn btn-sm p-0 border-0 bg-transparent"
                                title="<?= $p['favorito'] ? 'Remover dos favoritos' : 'Adicionar aos favoritos' ?>">
                            <i class="fas fa-star" style="color:<?= $p['favorito'] ? '#f59e0b' : '#d1d5db' ?>"></i>
                        </button>
                    </form>
                </div>

                <!-- Conteúdo (preview) -->
                <div class="prompt-preview flex-grow-1 mb-3"
                     style="background:#f8fafd;border-radius:6px;padding:.65rem .75rem;
                            font-size:.78rem;line-height:1.6;color:#374151;
                            max-height:120px;overflow:hidden;position:relative;cursor:pointer"
                     onclick="verPrompt(<?= $p['id'] ?>, '<?= addslashes(sanitize($p['titulo'])) ?>', `<?= addslashes($p['conteudo']) ?>`)">
                    <?= nl2br(sanitize(mb_substr($p['conteudo'], 0, 200))) ?>
                    <?php if (mb_strlen($p['conteudo']) > 200): ?>
                    <div style="position:absolute;bottom:0;left:0;right:0;height:30px;
                                background:linear-gradient(transparent,#f8fafd)"></div>
                    <?php endif; ?>
                </div>

                <!-- Ações -->
                <div class="d-flex gap-2 mt-auto">
                    <button type="button" class="btn btn-primary btn-sm flex-grow-1"
                            onclick="copiarPrompt(`<?= addslashes($p['conteudo']) ?>`, this)">
                        <i class="fas fa-copy me-1"></i>Copiar
                    </button>
                    <a href="form.php?id=<?= $p['id'] ?>" class="btn btn-outline-secondary btn-sm" title="Editar">
                        <i class="fas fa-pen"></i>
                    </a>
                    <form method="post" action="excluir.php" onsubmit="return confirm('Excluir este prompt?')">
                        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                        <input type="hidden" name="id" value="<?= $p['id'] ?>">
                        <button type="submit" class="btn btn-outline-danger btn-sm" title="Excluir">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                </div>
            </div>
            <div class="card-footer d-flex justify-content-between" style="font-size:.7rem;color:var(--text-3)">
                <span><i class="fas fa-clock me-1"></i><?= date('d/m/Y H:i', strtotime($p['atualizado_em'])) ?></span>
                <span><?= mb_strlen($p['conteudo']) ?> caracteres</span>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Modal visualizar prompt completo -->
<div class="modal fade" id="modalPrompt" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title" id="modalPromptTitulo"></h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <pre id="modalPromptConteudo"
                     style="white-space:pre-wrap;font-size:.84rem;line-height:1.7;
                            background:#f8fafd;border-radius:8px;padding:1rem;
                            border:1px solid var(--border);margin:0"></pre>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Fechar</button>
                <button type="button" class="btn btn-primary btn-sm" id="btnCopiarModal">
                    <i class="fas fa-copy me-1"></i>Copiar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function copiarPrompt(texto, btn) {
    navigator.clipboard.writeText(texto).then(() => {
        const original = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check me-1"></i>Copiado!';
        btn.classList.replace('btn-primary', 'btn-success');
        setTimeout(() => {
            btn.innerHTML = original;
            btn.classList.replace('btn-success', 'btn-primary');
        }, 2000);
    });
}

function verPrompt(id, titulo, conteudo) {
    document.getElementById('modalPromptTitulo').textContent = titulo;
    document.getElementById('modalPromptConteudo').textContent = conteudo;
    document.getElementById('btnCopiarModal').onclick = function() {
        copiarPrompt(conteudo, this);
    };
    new bootstrap.Modal(document.getElementById('modalPrompt')).show();
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
