<?php
require_once __DIR__ . '/../conexao.php';
require_once __DIR__ . '/../includes/auth.php';

$id         = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$titulo     = $id ? 'Editar link' : 'Novo link';
$menu_ativo = 'links';
$erros      = [];
$dados      = ['titulo' => '', 'url' => '', 'descricao' => '', 'categoria' => '', 'tipo' => 'web', 'favorito' => 0];

if ($id) {
    $stmt = $pdo->prepare('SELECT * FROM links WHERE id = ?');
    $stmt->execute([$id]);
    $dados = $stmt->fetch() ?: $dados;
}

$categorias = $pdo->query("SELECT DISTINCT categoria FROM links WHERE categoria IS NOT NULL AND categoria != '' ORDER BY categoria")->fetchAll(PDO::FETCH_COLUMN);

$tipos = [
    'web'       => ['icon' => 'fas fa-globe',     'label' => 'Web'],
    'instagram' => ['icon' => 'fab fa-instagram', 'label' => 'Instagram'],
    'linkedin'  => ['icon' => 'fab fa-linkedin',  'label' => 'LinkedIn'],
    'youtube'   => ['icon' => 'fab fa-youtube',   'label' => 'YouTube'],
    'github'    => ['icon' => 'fab fa-github',    'label' => 'GitHub'],
    'whatsapp'  => ['icon' => 'fab fa-whatsapp',  'label' => 'WhatsApp'],
    'facebook'  => ['icon' => 'fab fa-facebook',  'label' => 'Facebook'],
    'twitter'   => ['icon' => 'fab fa-x-twitter', 'label' => 'X / Twitter'],
    'outro'     => ['icon' => 'fas fa-link',      'label' => 'Outro'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $dados = [
        'titulo'    => trim($_POST['titulo']    ?? ''),
        'url'       => trim($_POST['url']       ?? ''),
        'descricao' => trim($_POST['descricao'] ?? ''),
        'categoria' => trim($_POST['categoria'] ?? ''),
        'tipo'      => array_key_exists($_POST['tipo'] ?? '', $tipos) ? $_POST['tipo'] : 'web',
        'favorito'  => isset($_POST['favorito']) ? 1 : 0,
    ];

    if ($dados['titulo'] === '') $erros[] = 'Título é obrigatório.';
    if ($dados['url']    === '') $erros[] = 'URL é obrigatória.';

    if (empty($erros)) {
        if ($id) {
            $pdo->prepare('UPDATE links SET titulo=?, url=?, descricao=?, categoria=?, tipo=?, favorito=? WHERE id=?')
                ->execute([$dados['titulo'], $dados['url'], $dados['descricao'], $dados['categoria'], $dados['tipo'], $dados['favorito'], $id]);
        } else {
            $pdo->prepare('INSERT INTO links (titulo, url, descricao, categoria, tipo, favorito) VALUES (?, ?, ?, ?, ?, ?)')
                ->execute([$dados['titulo'], $dados['url'], $dados['descricao'], $dados['categoria'], $dados['tipo'], $dados['favorito']]);
        }
        flash('success', 'Link salvo!');
        redirect('/links/index.php');
    }
}

include __DIR__ . '/../includes/header.php';
?>
<div class="card" style="max-width:600px">
    <div class="card-body">
        <?php foreach ($erros as $e): ?>
            <div class="alert alert-danger py-2"><?= sanitize($e) ?></div>
        <?php endforeach; ?>
        <form method="post">
            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

            <!-- Tipo (botões visuais) -->
            <div class="mb-3">
                <label class="form-label">Tipo</label>
                <div class="d-flex flex-wrap gap-2">
                    <?php foreach ($tipos as $key => $t): ?>
                    <label class="tipo-btn">
                        <input type="radio" name="tipo" value="<?= $key ?>"
                               <?= ($dados['tipo'] === $key) ? 'checked' : '' ?> hidden>
                        <span class="btn btn-sm tipo-label <?= ($dados['tipo'] === $key) ? 'active' : 'btn-outline-secondary' ?>">
                            <i class="<?= $t['icon'] ?> me-1"></i><?= $t['label'] ?>
                        </span>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Título <span class="text-danger">*</span></label>
                <input type="text" name="titulo" class="form-control"
                       value="<?= sanitize((string)$dados['titulo']) ?>"
                       placeholder="Nome descritivo do link" required autofocus>
            </div>
            <div class="mb-3">
                <label class="form-label">URL <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-link"></i></span>
                    <input type="url" name="url" class="form-control"
                           value="<?= sanitize((string)$dados['url']) ?>"
                           placeholder="https://" required>
                </div>
            </div>
            <div class="row mb-3 g-2">
                <div class="col-md-7">
                    <label class="form-label">Pasta / Categoria</label>
                    <input type="text" name="categoria" class="form-control"
                           value="<?= sanitize((string)$dados['categoria']) ?>"
                           placeholder="Ex: Trabalho, Pessoal, Redes Sociais..."
                           list="lista-cat">
                    <datalist id="lista-cat">
                        <?php foreach ($categorias as $cat): ?>
                            <option value="<?= sanitize($cat) ?>">
                        <?php endforeach; ?>
                    </datalist>
                </div>
                <div class="col-md-5 d-flex align-items-end pb-1">
                    <div class="form-check ms-1">
                        <input type="checkbox" name="favorito" id="favorito" class="form-check-input"
                               value="1" <?= $dados['favorito'] ? 'checked' : '' ?>>
                        <label for="favorito" class="form-check-label">
                            <i class="fas fa-star text-warning me-1"></i>Favorito
                        </label>
                    </div>
                </div>
            </div>
            <div class="mb-4">
                <label class="form-label">Descrição <span class="text-muted small">(opcional)</span></label>
                <input type="text" name="descricao" class="form-control"
                       value="<?= sanitize((string)$dados['descricao']) ?>"
                       placeholder="Breve descrição do link...">
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Salvar</button>
                <a href="index.php" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>

<style>
.tipo-btn input:checked + .tipo-label {
    background: var(--blue-500);
    color: #fff;
    border-color: var(--blue-500);
}
</style>

<?php include __DIR__ . '/../includes/footer.php'; ?>
