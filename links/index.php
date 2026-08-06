<?php
require_once __DIR__ . '/../conexao.php';
require_once __DIR__ . '/../includes/auth.php';
$titulo     = 'Meus Links';
$menu_ativo = 'links';

$tipo_filtro      = $_GET['tipo'] ?? '';
$categoria_filtro = $_GET['cat']  ?? '';
$busca            = trim($_GET['q'] ?? '');
$favoritos        = isset($_GET['fav']);

$where  = ['1=1'];
$params = [];

if ($tipo_filtro !== '') {
    $where[]  = 'tipo = ?';
    $params[] = $tipo_filtro;
}
if ($categoria_filtro !== '') {
    $where[]  = 'categoria = ?';
    $params[] = $categoria_filtro;
}
if ($busca !== '') {
    $where[]  = '(titulo LIKE ? OR url LIKE ? OR descricao LIKE ?)';
    $params   = array_merge($params, ["%$busca%", "%$busca%", "%$busca%"]);
}
if ($favoritos) {
    $where[] = 'favorito = 1';
}

$stmt = $pdo->prepare('SELECT * FROM links WHERE ' . implode(' AND ', $where) . ' ORDER BY favorito DESC, criado_em DESC');
$stmt->execute($params);
$links = $stmt->fetchAll();

$categorias = $pdo->query("SELECT DISTINCT categoria FROM links WHERE categoria IS NOT NULL AND categoria != '' ORDER BY categoria")->fetchAll(PDO::FETCH_COLUMN);

// Mapa de ícones e cores por tipo
$tipos_info = [
    'web'       => ['icon' => 'fas fa-globe',        'color' => '#0d6efd', 'label' => 'Web'],
    'instagram' => ['icon' => 'fab fa-instagram',    'color' => '#e1306c', 'label' => 'Instagram'],
    'linkedin'  => ['icon' => 'fab fa-linkedin',     'color' => '#0a66c2', 'label' => 'LinkedIn'],
    'youtube'   => ['icon' => 'fab fa-youtube',      'color' => '#ff0000', 'label' => 'YouTube'],
    'github'    => ['icon' => 'fab fa-github',       'color' => '#24292f', 'label' => 'GitHub'],
    'whatsapp'  => ['icon' => 'fab fa-whatsapp',     'color' => '#25d366', 'label' => 'WhatsApp'],
    'facebook'  => ['icon' => 'fab fa-facebook',     'color' => '#1877f2', 'label' => 'Facebook'],
    'twitter'   => ['icon' => 'fab fa-x-twitter',    'color' => '#000',    'label' => 'X / Twitter'],
    'outro'     => ['icon' => 'fas fa-link',         'color' => '#6c757d', 'label' => 'Outro'],
];

include __DIR__ . '/../includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <form method="get" class="d-flex gap-2 align-items-center flex-wrap">
        <div class="input-group" style="max-width:220px">
            <span class="input-group-text"><i class="fas fa-search"></i></span>
            <input type="text" name="q" class="form-control" placeholder="Buscar..." value="<?= sanitize($busca) ?>">
        </div>
        <select name="cat" class="form-select" style="max-width:160px" onchange="this.form.submit()">
            <option value="">Todas as pastas</option>
            <?php foreach ($categorias as $cat): ?>
                <option value="<?= sanitize($cat) ?>" <?= $categoria_filtro === $cat ? 'selected' : '' ?>>
                    <?= sanitize($cat) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <a href="?fav=1" class="btn <?= $favoritos ? 'btn-warning' : 'btn-outline-warning' ?>" title="Favoritos">
            <i class="fas fa-star"></i>
        </a>
        <?php if ($busca || $categoria_filtro || $favoritos || $tipo_filtro): ?>
            <a href="index.php" class="btn btn-outline-secondary"><i class="fas fa-times"></i></a>
        <?php endif; ?>
    </form>
    <a href="form.php" class="btn btn-primary"><i class="fas fa-plus me-1"></i>Novo link</a>
</div>

<!-- Filtro rápido por tipo -->
<div class="d-flex gap-2 mb-3 flex-wrap">
    <a href="index.php<?= $busca ? '?q='.urlencode($busca) : '' ?>"
       class="btn btn-sm <?= !$tipo_filtro ? 'btn-dark' : 'btn-outline-secondary' ?>">Todos</a>
    <?php foreach ($tipos_info as $tipo => $info): ?>
    <a href="?tipo=<?= $tipo ?>"
       class="btn btn-sm"
       style="<?= $tipo_filtro === $tipo
            ? "background:{$info['color']};color:#fff;border-color:{$info['color']}"
            : "border-color:{$info['color']};color:{$info['color']}" ?>">
        <i class="<?= $info['icon'] ?> me-1"></i><?= $info['label'] ?>
    </a>
    <?php endforeach; ?>
</div>

<?php if (empty($links)): ?>
<div class="card">
    <div class="card-body text-center text-muted py-5">
        <i class="fas fa-link fa-3x d-block mb-3 opacity-25"></i>
        <p class="mb-0">Nenhum link encontrado.</p>
    </div>
</div>
<?php else: ?>
<div class="row g-3">
    <?php foreach ($links as $l):
        $info = $tipos_info[$l['tipo']] ?? $tipos_info['outro'];
    ?>
    <div class="col-md-6 col-xl-4">
        <div class="card h-100" style="border-top:3px solid <?= $info['color'] ?>">
            <div class="card-body">
                <div class="d-flex align-items-start gap-3">
                    <!-- Ícone do tipo -->
                    <div style="width:42px;height:42px;border-radius:10px;flex-shrink:0;
                                background:<?= $info['color'] ?>18;
                                display:flex;align-items:center;justify-content:center">
                        <i class="<?= $info['icon'] ?>" style="color:<?= $info['color'] ?>;font-size:1.2rem"></i>
                    </div>
                    <div class="flex-grow-1 min-w-0">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="fw-semibold text-truncate me-2" style="font-size:.88rem">
                                <?= sanitize($l['titulo']) ?>
                            </div>
                            <form method="post" action="favorito.php">
                                <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                                <input type="hidden" name="id" value="<?= $l['id'] ?>">
                                <button type="submit" class="btn btn-sm p-0 border-0 bg-transparent">
                                    <i class="fas fa-star" style="color:<?= $l['favorito'] ? '#f59e0b' : '#d1d5db' ?>"></i>
                                </button>
                            </form>
                        </div>
                        <?php if ($l['categoria']): ?>
                            <span class="badge mt-1" style="background:#f0f4fa;color:#64748b;font-size:.65rem">
                                <i class="fas fa-folder me-1"></i><?= sanitize($l['categoria']) ?>
                            </span>
                        <?php endif; ?>
                        <?php if ($l['descricao']): ?>
                            <p class="text-muted mt-1 mb-0" style="font-size:.78rem;line-height:1.4">
                                <?= sanitize($l['descricao']) ?>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- URL truncada -->
                <div class="mt-2 text-truncate" style="font-size:.74rem;color:var(--text-3)">
                    <i class="fas fa-link me-1"></i><?= sanitize($l['url']) ?>
                </div>
            </div>

            <!-- Ações -->
            <div class="card-footer d-flex gap-2 py-2">
                <a href="<?= sanitize($l['url']) ?>" target="_blank" rel="noopener noreferrer"
                   class="btn btn-sm flex-grow-1"
                   style="background:<?= $info['color'] ?>;color:#fff;border:none">
                    <i class="fas fa-external-link-alt me-1"></i>Abrir
                </a>
                <button type="button" class="btn btn-sm btn-outline-secondary"
                        onclick="navigator.clipboard.writeText('<?= addslashes(sanitize($l['url'])) ?>');this.innerHTML='<i class=\'fas fa-check text-success\'></i>';setTimeout(()=>this.innerHTML='<i class=\'fas fa-copy\'></i>',2000)"
                        title="Copiar URL">
                    <i class="fas fa-copy"></i>
                </button>
                <a href="form.php?id=<?= $l['id'] ?>" class="btn btn-sm btn-outline-secondary" title="Editar">
                    <i class="fas fa-pen"></i>
                </a>
                <form method="post" action="excluir.php" onsubmit="return confirm('Excluir este link?')">
                    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                    <input type="hidden" name="id" value="<?= $l['id'] ?>">
                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Excluir">
                        <i class="fas fa-trash"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
