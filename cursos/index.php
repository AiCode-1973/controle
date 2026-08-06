<?php
require_once __DIR__ . '/../conexao.php';
require_once __DIR__ . '/../includes/auth.php';
$titulo     = 'Meus Cursos';
$menu_ativo = 'cursos';

$status_filtro    = $_GET['status'] ?? '';
$categoria_filtro = $_GET['cat']    ?? '';
$busca            = trim($_GET['q'] ?? '');

$where  = ['1=1'];
$params = [];
if ($status_filtro    !== '') { $where[] = 'status = ?';    $params[] = $status_filtro; }
if ($categoria_filtro !== '') { $where[] = 'categoria = ?'; $params[] = $categoria_filtro; }
if ($busca !== '') { $where[] = '(titulo LIKE ? OR categoria LIKE ?)'; $params[] = "%$busca%"; $params[] = "%$busca%"; }

$stmt = $pdo->prepare('SELECT * FROM cursos WHERE ' . implode(' AND ', $where) . ' ORDER BY favorito DESC, status ASC, titulo ASC');
$stmt->execute($params);
$cursos = $stmt->fetchAll();

$categorias = $pdo->query("SELECT DISTINCT categoria FROM cursos WHERE categoria IS NOT NULL AND categoria != '' ORDER BY categoria")->fetchAll(PDO::FETCH_COLUMN);

// Totais para os cards de resumo
$totais = $pdo->query("SELECT status, COUNT(*) as n, SUM(preco) as total_gasto FROM cursos GROUP BY status")->fetchAll();
$resumo = array_column($totais, null, 'status');
$total_investido = $pdo->query("SELECT SUM(preco) FROM cursos")->fetchColumn();

$plataformas_info = [
    'udemy'       => ['label' => 'Udemy',       'color' => '#a435f0'],
    'coursera'    => ['label' => 'Coursera',    'color' => '#0056d2'],
    'youtube'     => ['label' => 'YouTube',     'color' => '#ff0000'],
    'alura'       => ['label' => 'Alura',       'color' => '#0f172a'],
    'rocketseat'  => ['label' => 'Rocketseat',  'color' => '#8257e5'],
    'dio'         => ['label' => 'DIO',         'color' => '#ee2d78'],
    'hotmart'     => ['label' => 'Hotmart',     'color' => '#ff4c00'],
    'kiwify'      => ['label' => 'Kiwify',      'color' => '#00c07f'],
    'eduzz'       => ['label' => 'Eduzz',       'color' => '#0267ff'],
    'origamid'    => ['label' => 'Origamid',    'color' => '#dd5252'],
    'outro'       => ['label' => 'Outro',       'color' => '#6c757d'],
];

$status_info = [
    'nao_iniciado' => ['label' => 'Não iniciado', 'color' => '#94a3b8', 'bg' => '#f1f5f9'],
    'em_andamento' => ['label' => 'Em andamento', 'color' => '#0d6efd', 'bg' => '#eff6ff'],
    'concluido'    => ['label' => 'Concluído',    'color' => '#10b981', 'bg' => '#f0fdf4'],
    'pausado'      => ['label' => 'Pausado',      'color' => '#f59e0b', 'bg' => '#fffbeb'],
];

include __DIR__ . '/../includes/header.php';
?>
<!-- Resumo estatístico -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card text-center" style="border-top:3px solid #0d6efd">
            <div class="card-body py-3">
                <div class="fw-bold fs-3 text-primary"><?= $resumo['em_andamento']['n'] ?? 0 ?></div>
                <div class="small text-muted">Em andamento</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card text-center" style="border-top:3px solid #10b981">
            <div class="card-body py-3">
                <div class="fw-bold fs-3" style="color:#10b981"><?= $resumo['concluido']['n'] ?? 0 ?></div>
                <div class="small text-muted">Concluídos</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card text-center" style="border-top:3px solid #94a3b8">
            <div class="card-body py-3">
                <div class="fw-bold fs-3 text-secondary"><?= $resumo['nao_iniciado']['n'] ?? 0 ?></div>
                <div class="small text-muted">Não iniciados</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card text-center" style="border-top:3px solid #a435f0">
            <div class="card-body py-3">
                <div class="fw-bold" style="font-size:1.1rem;color:#a435f0"><?= 'R$ ' . number_format((float)($total_investido ?? 0), 2, ',', '.') ?></div>
                <div class="small text-muted">Total investido</div>
            </div>
        </div>
    </div>
</div>

<!-- Filtros e ação -->
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div class="d-flex gap-2 flex-wrap align-items-center">
        <form method="get" class="d-flex gap-2">
            <div class="input-group" style="max-width:220px">
                <span class="input-group-text"><i class="fas fa-search"></i></span>
                <input type="text" name="q" class="form-control" placeholder="Buscar..." value="<?= sanitize($busca) ?>">
            </div>
            <select name="cat" class="form-select" style="max-width:160px" onchange="this.form.submit()">
                <option value="">Todas as áreas</option>
                <?php foreach ($categorias as $cat): ?>
                    <option value="<?= sanitize($cat) ?>" <?= $categoria_filtro === $cat ? 'selected' : '' ?>>
                        <?= sanitize($cat) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
        <!-- Filtro rápido por status -->
        <a href="cursos/index.php" class="btn btn-sm <?= !$status_filtro ? 'btn-dark' : 'btn-outline-secondary' ?>">Todos</a>
        <?php foreach ($status_info as $st => $si): ?>
        <a href="?status=<?= $st ?>" class="btn btn-sm"
           style="<?= $status_filtro === $st ? "background:{$si['color']};color:#fff;border-color:{$si['color']}" : "border-color:{$si['color']};color:{$si['color']}" ?>">
            <?= $si['label'] ?>
        </a>
        <?php endforeach; ?>
        <?php if ($busca || $categoria_filtro || $status_filtro): ?>
            <a href="index.php" class="btn btn-sm btn-outline-secondary"><i class="fas fa-times"></i></a>
        <?php endif; ?>
    </div>
    <a href="form.php" class="btn btn-primary"><i class="fas fa-plus me-1"></i>Novo curso</a>
</div>

<?php if (empty($cursos)): ?>
<div class="card">
    <div class="card-body text-center text-muted py-5">
        <i class="fas fa-graduation-cap fa-3x d-block mb-3 opacity-25"></i>
        <p class="mb-0">Nenhum curso encontrado.</p>
    </div>
</div>
<?php else: ?>
<div class="row g-3">
    <?php foreach ($cursos as $c):
        $plat = $plataformas_info[$c['plataforma']] ?? $plataformas_info['outro'];
        $st   = $status_info[$c['status']]          ?? $status_info['nao_iniciado'];
        $prog = (int)$c['progresso'];
    ?>
    <div class="col-md-6 col-xl-4">
        <div class="card h-100" style="border-top:3px solid <?= $plat['color'] ?>">
            <div class="card-body">
                <!-- Cabeçalho -->
                <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                    <div class="flex-grow-1">
                        <div class="fw-semibold lh-sm mb-1" style="font-size:.88rem">
                            <?= sanitize($c['titulo']) ?>
                        </div>
                        <div class="d-flex flex-wrap gap-1">
                            <span class="badge" style="background:<?= $plat['color'] ?>22;color:<?= $plat['color'] ?>;font-size:.62rem">
                                <?= $plat['label'] ?>
                            </span>
                            <?php if ($c['categoria']): ?>
                            <span class="badge" style="background:#f0f4fa;color:#64748b;font-size:.62rem">
                                <?= sanitize($c['categoria']) ?>
                            </span>
                            <?php endif; ?>
                            <?php if ($c['certificado']): ?>
                            <span class="badge" style="background:#fef3c7;color:#b45309;font-size:.62rem">
                                <i class="fas fa-certificate me-1"></i>Certificado
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <form method="post" action="favorito.php">
                        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                        <input type="hidden" name="id" value="<?= $c['id'] ?>">
                        <button type="submit" class="btn btn-sm p-0 border-0 bg-transparent">
                            <i class="fas fa-star" style="color:<?= $c['favorito'] ? '#f59e0b' : '#d1d5db' ?>"></i>
                        </button>
                    </form>
                </div>

                <!-- Status + progresso -->
                <div class="mb-2">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="badge" style="background:<?= $st['bg'] ?>;color:<?= $st['color'] ?>;font-size:.7rem">
                            <?= $st['label'] ?>
                        </span>
                        <span style="font-size:.72rem;color:var(--text-3)"><?= $prog ?>%</span>
                    </div>
                    <div class="progress" style="height:5px">
                        <div class="progress-bar" style="width:<?= $prog ?>%;background:<?= $plat['color'] ?>"></div>
                    </div>
                </div>

                <!-- Nota (estrelas) -->
                <?php if ($c['nota'] > 0): ?>
                <div class="mb-2">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <i class="fas fa-star" style="font-size:.7rem;color:<?= $i <= $c['nota'] ? '#f59e0b' : '#d1d5db' ?>"></i>
                    <?php endfor; ?>
                </div>
                <?php endif; ?>

                <!-- Datas + preço -->
                <div class="d-flex justify-content-between" style="font-size:.72rem;color:var(--text-3)">
                    <span><?= $c['data_inicio'] ? '<i class="fas fa-play me-1"></i>' . formatData($c['data_inicio']) : '' ?></span>
                    <?php if ($c['preco'] > 0): ?>
                    <span><i class="fas fa-tag me-1"></i>R$ <?= number_format((float)$c['preco'], 2, ',', '.') ?></span>
                    <?php endif; ?>
                </div>

                <?php if ($c['observacao']): ?>
                <p class="mt-2 mb-0 text-muted" style="font-size:.76rem;line-height:1.4">
                    <?= sanitize(mb_substr($c['observacao'], 0, 100)) ?><?= mb_strlen($c['observacao']) > 100 ? '…' : '' ?>
                </p>
                <?php endif; ?>
            </div>

            <!-- Ações -->
            <div class="card-footer d-flex gap-2 py-2">
                <?php if ($c['url']): ?>
                <a href="<?= sanitize($c['url']) ?>" target="_blank" rel="noopener noreferrer"
                   class="btn btn-sm flex-grow-1 fw-semibold"
                   style="background:<?= $plat['color'] ?>;color:#fff;border:none">
                    <i class="fas fa-play me-1"></i>Acessar
                </a>
                <?php endif; ?>
                <a href="form.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-outline-secondary" title="Editar">
                    <i class="fas fa-pen"></i>
                </a>
                <form method="post" action="excluir.php" onsubmit="return confirm('Excluir este curso?')">
                    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                    <input type="hidden" name="id" value="<?= $c['id'] ?>">
                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                </form>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
