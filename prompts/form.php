<?php
require_once __DIR__ . '/../conexao.php';
require_once __DIR__ . '/../includes/auth.php';

$id         = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$titulo     = $id ? 'Editar prompt' : 'Novo prompt';
$menu_ativo = 'prompts';
$erros      = [];
$dados      = ['titulo' => '', 'conteudo' => '', 'categoria' => '', 'favorito' => 0];

if ($id) {
    $stmt = $pdo->prepare('SELECT * FROM prompts WHERE id = ?');
    $stmt->execute([$id]);
    $dados = $stmt->fetch() ?: $dados;
}

$categorias = $pdo->query("SELECT DISTINCT categoria FROM prompts WHERE categoria IS NOT NULL AND categoria != '' ORDER BY categoria")->fetchAll(PDO::FETCH_COLUMN);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $dados = [
        'titulo'    => trim($_POST['titulo']    ?? ''),
        'conteudo'  => trim($_POST['conteudo']  ?? ''),
        'categoria' => trim($_POST['categoria'] ?? ''),
        'favorito'  => isset($_POST['favorito']) ? 1 : 0,
    ];

    if ($dados['titulo']   === '') $erros[] = 'Título é obrigatório.';
    if ($dados['conteudo'] === '') $erros[] = 'Conteúdo é obrigatório.';

    if (empty($erros)) {
        if ($id) {
            $pdo->prepare('UPDATE prompts SET titulo=?, conteudo=?, categoria=?, favorito=? WHERE id=?')
                ->execute([$dados['titulo'], $dados['conteudo'], $dados['categoria'], $dados['favorito'], $id]);
        } else {
            $pdo->prepare('INSERT INTO prompts (titulo, conteudo, categoria, favorito) VALUES (?, ?, ?, ?)')
                ->execute([$dados['titulo'], $dados['conteudo'], $dados['categoria'], $dados['favorito']]);
        }
        flash('success', 'Prompt salvo!');
        redirect('/prompts/index.php');
    }
}

include __DIR__ . '/../includes/header.php';
?>
<div class="card" style="max-width:720px">
    <div class="card-body">
        <?php foreach ($erros as $e): ?>
            <div class="alert alert-danger py-2"><?= sanitize($e) ?></div>
        <?php endforeach; ?>
        <form method="post">
            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
            <div class="mb-3">
                <label class="form-label">Título <span class="text-danger">*</span></label>
                <input type="text" name="titulo" class="form-control"
                       value="<?= sanitize((string)$dados['titulo']) ?>" required autofocus
                       placeholder="Ex: Revisar código PHP, Criar README...">
            </div>
            <div class="row mb-3 g-2 align-items-end">
                <div class="col-md-7">
                    <label class="form-label">Categoria</label>
                    <input type="text" name="categoria" class="form-control"
                           value="<?= sanitize((string)$dados['categoria']) ?>"
                           placeholder="Ex: Código, Redação, SEO..."
                           list="lista-categorias">
                    <datalist id="lista-categorias">
                        <?php foreach ($categorias as $cat): ?>
                            <option value="<?= sanitize($cat) ?>">
                        <?php endforeach; ?>
                    </datalist>
                </div>
                <div class="col-md-5">
                    <div class="form-check ms-1 mb-2">
                        <input type="checkbox" name="favorito" id="favorito" class="form-check-input"
                               value="1" <?= $dados['favorito'] ? 'checked' : '' ?>>
                        <label for="favorito" class="form-check-label">
                            <i class="fas fa-star text-warning me-1"></i>Marcar como favorito
                        </label>
                    </div>
                </div>
            </div>
            <div class="mb-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <label class="form-label mb-0">Conteúdo <span class="text-danger">*</span></label>
                    <span class="text-muted" style="font-size:.72rem" id="char-count">0 caracteres</span>
                </div>
                <textarea name="conteudo" id="conteudo" class="form-control font-monospace"
                          rows="14" required
                          placeholder="Cole ou escreva o seu prompt aqui..."
                          style="font-size:.83rem;line-height:1.7"><?= sanitize((string)$dados['conteudo']) ?></textarea>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Salvar</button>
                <a href="index.php" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>

<script>
const ta = document.getElementById('conteudo');
const cc = document.getElementById('char-count');
function updateCount() { cc.textContent = ta.value.length + ' caracteres'; }
ta.addEventListener('input', updateCount);
updateCount();
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
