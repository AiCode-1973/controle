<?php
require_once __DIR__ . '/../conexao.php';
require_once __DIR__ . '/../includes/auth.php';
$titulo     = 'Minhas IAs';
$menu_ativo = 'ias';

$categoria_filtro = $_GET['cat']   ?? '';
$busca            = trim($_GET['q'] ?? '');
$favoritos        = isset($_GET['fav']);

$where  = ['1=1'];
$params = [];
if ($categoria_filtro !== '') { $where[] = 'categoria = ?'; $params[] = $categoria_filtro; }
if ($busca !== '') { $where[] = '(nome LIKE ? OR descricao LIKE ?)'; $params[] = "%$busca%"; $params[] = "%$busca%"; }
if ($favoritos) { $where[] = 'favorito = 1'; }

$stmt = $pdo->prepare('SELECT * FROM ias WHERE ' . implode(' AND ', $where) . ' ORDER BY favorito DESC, nome ASC');
$stmt->execute($params);
$ias = $stmt->fetchAll();

// Identidade visual por marca
$marcas = [
    'chatgpt'    => ['label' => 'ChatGPT',     'color' => '#10a37f', 'bg' => '#f0fdf9', 'logo' => 'assets/logos/chatgpt.svg'],
    'claude'     => ['label' => 'Claude',      'color' => '#d97706', 'bg' => '#fffbeb'],
    'gemini'     => ['label' => 'Gemini',      'color' => '#1a73e8', 'bg' => '#eff6ff'],
    'copilot'    => ['label' => 'Copilot',     'color' => '#0078d4', 'bg' => '#eff6ff'],
    'perplexity' => ['label' => 'Perplexity',  'color' => '#20b2aa', 'bg' => '#f0fdfa'],
    'midjourney' => ['label' => 'Midjourney',  'color' => '#000',    'bg' => '#f8f8f8'],
    'dalle'      => ['label' => 'DALL·E',      'color' => '#412991', 'bg' => '#f5f0ff'],
    'grok'       => ['label' => 'Grok',        'color' => '#1da1f2', 'bg' => '#eff9ff'],
    'deepseek'   => ['label' => 'DeepSeek',    'color' => '#4f6ef7', 'bg' => '#f0f2ff'],
    'llama'      => ['label' => 'Llama',       'color' => '#6366f1', 'bg' => '#eef2ff'],
    'runway'     => ['label' => 'Runway',      'color' => '#000',    'bg' => '#f8f8f8'],
    'elevenlabs' => ['label' => 'ElevenLabs',  'color' => '#f97316', 'bg' => '#fff7ed'],
    'cursor'     => ['label' => 'Cursor',      'color' => '#6366f1', 'bg' => '#eef2ff'],
    'outro'      => ['label' => 'Outro',       'color' => '#6c757d', 'bg' => '#f8f9fa'],
];

$categorias_info = [
    'chat'     => ['icon' => 'fas fa-comments',    'label' => 'Chat'],
    'imagem'   => ['icon' => 'fas fa-image',        'label' => 'Imagem'],
    'codigo'   => ['icon' => 'fas fa-code',         'label' => 'Código'],
    'video'    => ['icon' => 'fas fa-video',        'label' => 'Vídeo'],
    'audio'    => ['icon' => 'fas fa-microphone',   'label' => 'Áudio'],
    'pesquisa' => ['icon' => 'fas fa-search',       'label' => 'Pesquisa'],
    'outro'    => ['icon' => 'fas fa-robot',        'label' => 'Outro'],
];

$planos_info = [
    'gratuito' => ['label' => 'Gratuito', 'class' => 'bg-success'],
    'pago'     => ['label' => 'Pago',     'class' => 'bg-danger'],
    'freemium' => ['label' => 'Freemium', 'class' => 'bg-info text-dark'],
    'creditos' => ['label' => 'Créditos', 'class' => 'bg-warning text-dark'],
];

include __DIR__ . '/../includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <form method="get" class="d-flex gap-2 align-items-center flex-wrap">
        <div class="input-group" style="max-width:220px">
            <span class="input-group-text"><i class="fas fa-search"></i></span>
            <input type="text" name="q" class="form-control" placeholder="Buscar IA..." value="<?= sanitize($busca) ?>">
        </div>
        <a href="?fav=1" class="btn <?= $favoritos ? 'btn-warning' : 'btn-outline-warning' ?>" title="Favoritos">
            <i class="fas fa-star"></i>
        </a>
        <?php if ($busca || $categoria_filtro || $favoritos): ?>
            <a href="index.php" class="btn btn-outline-secondary"><i class="fas fa-times"></i></a>
        <?php endif; ?>
    </form>
    <a href="form.php" class="btn btn-primary"><i class="fas fa-plus me-1"></i>Adicionar IA</a>
</div>

<!-- Filtro por categoria -->
<div class="d-flex gap-2 mb-4 flex-wrap">
    <a href="index.php" class="btn btn-sm <?= !$categoria_filtro ? 'btn-dark' : 'btn-outline-secondary' ?>">Todas</a>
    <?php foreach ($categorias_info as $cat => $ci): ?>
    <a href="?cat=<?= $cat ?>" class="btn btn-sm <?= $categoria_filtro === $cat ? 'btn-primary' : 'btn-outline-secondary' ?>">
        <i class="<?= $ci['icon'] ?> me-1"></i><?= $ci['label'] ?>
    </a>
    <?php endforeach; ?>
</div>

<?php if (empty($ias)): ?>
<div class="card">
    <div class="card-body text-center text-muted py-5">
        <i class="fas fa-robot fa-3x d-block mb-3 opacity-25"></i>
        <p class="mb-0">Nenhuma IA cadastrada ainda.</p>
    </div>
</div>
<?php else: ?>
<div class="row g-3">
    <?php foreach ($ias as $ia):
        $m   = $marcas[$ia['marca']] ?? $marcas['outro'];
        $cat = $categorias_info[$ia['categoria']] ?? $categorias_info['outro'];
        $pln = $planos_info[$ia['plano']] ?? $planos_info['freemium'];
    ?>
    <div class="col-md-6 col-xl-4">
        <div class="card h-100" style="border-top:3px solid <?= $m['color'] ?>">
            <div class="card-body">
                <div class="d-flex align-items-start gap-3">
                    <!-- Logo/avatar da marca -->
                    <div style="width:48px;height:48px;border-radius:12px;flex-shrink:0;
                                background:<?= $m['bg'] ?>;border:1px solid <?= $m['color'] ?>22;
                                display:flex;align-items:center;justify-content:center;
                                font-size:1.3rem;font-weight:800;color:<?= $m['color'] ?>">
                        <?= strtoupper(mb_substr($ia['nome'], 0, 1)) ?>
                    </div>
                    <div class="flex-grow-1 min-w-0">
                        <div class="d-flex justify-content-between align-items-start gap-1">
                            <div>
                                <div class="fw-bold" style="font-size:.9rem;color:<?= $m['color'] ?>">
                                    <?= sanitize($ia['nome']) ?>
                                </div>
                                <div class="d-flex gap-1 mt-1 flex-wrap">
                                    <span class="badge <?= $pln['class'] ?>" style="font-size:.62rem">
                                        <?= $pln['label'] ?>
                                    </span>
                                    <span class="badge" style="background:#f0f4fa;color:#64748b;font-size:.62rem">
                                        <i class="<?= $cat['icon'] ?> me-1"></i><?= $cat['label'] ?>
                                    </span>
                                </div>
                            </div>
                            <form method="post" action="favorito.php">
                                <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                                <input type="hidden" name="id" value="<?= $ia['id'] ?>">
                                <button type="submit" class="btn btn-sm p-0 border-0 bg-transparent">
                                    <i class="fas fa-star" style="color:<?= $ia['favorito'] ? '#f59e0b' : '#d1d5db' ?>"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <?php if ($ia['descricao']): ?>
                <p class="mt-2 mb-0 text-muted" style="font-size:.78rem;line-height:1.5">
                    <?= sanitize($ia['descricao']) ?>
                </p>
                <?php endif; ?>

                <div class="mt-2 text-truncate" style="font-size:.72rem;color:var(--text-3)">
                    <i class="fas fa-link me-1"></i><?= sanitize($ia['url']) ?>
                </div>

                <?php if (!empty($ia['acesso_login'])): ?>
                <div class="mt-2 pt-2 border-top">
                    <div class="small fw-semibold text-muted mb-1">
                        <i class="fas fa-key me-1 text-warning"></i>Acesso
                    </div>
                    <?php
                    $ac = [
                        'Login' => $ia['acesso_login'] ?? '',
                        'Senha' => $ia['acesso_senha'] ?? '',
                    ];
                    foreach ($ac as $label => $valor): if (!$valor) continue;
                        $eid = 'ia_' . $ia['id'] . '_' . strtolower($label); ?>
                    <div class="d-flex align-items-center justify-content-between py-1" style="font-size:.76rem">
                        <span class="text-muted me-2" style="min-width:40px"><?= $label ?></span>
                        <span class="font-monospace flex-grow-1"
                              id="<?= $eid ?>"
                              style="<?= $label === 'Senha' ? 'filter:blur(4px);user-select:none' : '' ?>">
                            <?= sanitize($valor) ?>
                        </span>
                        <?php if ($label === 'Senha'): ?>
                        <button type="button" class="btn btn-sm p-0 ms-1 border-0 bg-transparent text-muted"
                                onclick="var e=document.getElementById('<?= $eid ?>');e.style.filter=e.style.filter?'':'blur(4px)'"
                                title="Mostrar/ocultar"><i class="fas fa-eye"></i></button>
                        <?php endif; ?>
                        <button type="button" class="btn btn-sm p-0 ms-1 border-0 bg-transparent text-muted"
                                onclick="navigator.clipboard.writeText('<?= addslashes(sanitize($valor)) ?>');this.innerHTML='<i class=\'fas fa-check text-success\'></i>';setTimeout(()=>this.innerHTML='<i class=\'fas fa-copy\'></i>',1500)"
                                title="Copiar"><i class="fas fa-copy"></i></button>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <div class="card-footer d-flex gap-2 py-2">
                <a href="<?= sanitize($ia['url']) ?>" target="_blank" rel="noopener noreferrer"
                   class="btn btn-sm flex-grow-1 fw-semibold"
                   style="background:<?= $m['color'] ?>;color:#fff;border:none">
                    <i class="fas fa-external-link-alt me-1"></i>Abrir
                </a>
                <a href="form.php?id=<?= $ia['id'] ?>" class="btn btn-sm btn-outline-secondary" title="Editar">
                    <i class="fas fa-pen"></i>
                </a>
                <form method="post" action="excluir.php" onsubmit="return confirm('Remover esta IA?')">
                    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                    <input type="hidden" name="id" value="<?= $ia['id'] ?>">
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
