<?php
require_once __DIR__ . '/../conexao.php';
require_once __DIR__ . '/../includes/auth.php';

$id         = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$titulo     = $id ? 'Editar IA' : 'Adicionar IA';
$menu_ativo = 'ias';
$erros      = [];
$dados      = ['nome' => '', 'url' => '', 'descricao' => '', 'categoria' => 'chat', 'marca' => 'outro', 'plano' => 'freemium', 'favorito' => 0];

if ($id) {
    $stmt = $pdo->prepare('SELECT * FROM ias WHERE id = ?');
    $stmt->execute([$id]);
    $dados = $stmt->fetch() ?: $dados;
}

$marcas_lista = [
    'chatgpt'    => ['label' => 'ChatGPT',    'color' => '#10a37f'],
    'claude'     => ['label' => 'Claude',     'color' => '#d97706'],
    'gemini'     => ['label' => 'Gemini',     'color' => '#1a73e8'],
    'copilot'    => ['label' => 'Copilot',    'color' => '#0078d4'],
    'perplexity' => ['label' => 'Perplexity', 'color' => '#20b2aa'],
    'midjourney' => ['label' => 'Midjourney', 'color' => '#333'],
    'dalle'      => ['label' => 'DALL·E',     'color' => '#412991'],
    'grok'       => ['label' => 'Grok',       'color' => '#1da1f2'],
    'deepseek'   => ['label' => 'DeepSeek',   'color' => '#4f6ef7'],
    'llama'      => ['label' => 'Llama',      'color' => '#6366f1'],
    'runway'     => ['label' => 'Runway',     'color' => '#111'],
    'elevenlabs' => ['label' => 'ElevenLabs', 'color' => '#f97316'],
    'cursor'     => ['label' => 'Cursor',     'color' => '#6366f1'],
    'outro'      => ['label' => 'Outro',      'color' => '#6c757d'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $validas_cat   = ['chat','imagem','codigo','video','audio','pesquisa','outro'];
    $validas_marca = array_keys($marcas_lista);
    $validas_plano = ['gratuito','pago','freemium','creditos'];

    $dados = [
        'nome'      => trim($_POST['nome']      ?? ''),
        'url'       => trim($_POST['url']       ?? ''),
        'descricao' => trim($_POST['descricao'] ?? ''),
        'categoria' => in_array($_POST['categoria'] ?? '', $validas_cat,   true) ? $_POST['categoria'] : 'chat',
        'marca'     => in_array($_POST['marca']     ?? '', $validas_marca, true) ? $_POST['marca']     : 'outro',
        'plano'     => in_array($_POST['plano']     ?? '', $validas_plano, true) ? $_POST['plano']     : 'freemium',
        'favorito'  => isset($_POST['favorito']) ? 1 : 0,
    ];

    if ($dados['nome'] === '') $erros[] = 'Nome é obrigatório.';
    if ($dados['url']  === '') $erros[] = 'URL é obrigatória.';

    if (empty($erros)) {
        if ($id) {
            $pdo->prepare('UPDATE ias SET nome=?,url=?,descricao=?,categoria=?,marca=?,plano=?,favorito=? WHERE id=?')
                ->execute([$dados['nome'],$dados['url'],$dados['descricao'],$dados['categoria'],$dados['marca'],$dados['plano'],$dados['favorito'],$id]);
        } else {
            $pdo->prepare('INSERT INTO ias (nome,url,descricao,categoria,marca,plano,favorito) VALUES (?,?,?,?,?,?,?)')
                ->execute([$dados['nome'],$dados['url'],$dados['descricao'],$dados['categoria'],$dados['marca'],$dados['plano'],$dados['favorito']]);
        }
        flash('success', 'IA salva!');
        redirect('/ias/index.php');
    }
}

include __DIR__ . '/../includes/header.php';
?>
<div class="card" style="max-width:640px">
    <div class="card-body">
        <?php foreach ($erros as $e): ?>
            <div class="alert alert-danger py-2"><?= sanitize($e) ?></div>
        <?php endforeach; ?>
        <form method="post">
            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

            <!-- Seleção de marca -->
            <div class="mb-3">
                <label class="form-label">Marca / Plataforma</label>
                <div class="d-flex flex-wrap gap-2">
                    <?php foreach ($marcas_lista as $key => $m): ?>
                    <label>
                        <input type="radio" name="marca" value="<?= $key ?>"
                               <?= ($dados['marca'] === $key) ? 'checked' : '' ?> hidden
                               onchange="document.getElementById('nome').value || (document.getElementById('nome').value='<?= $m['label'] ?>')">
                        <span class="btn btn-sm marca-btn"
                              style="border:2px solid <?= $m['color'] ?>;color:<?= $m['color'] ?>;
                                     <?= ($dados['marca'] === $key) ? "background:{$m['color']};color:#fff" : '' ?>">
                            <?= $m['label'] ?>
                        </span>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-7">
                    <label class="form-label">Nome <span class="text-danger">*</span></label>
                    <input type="text" name="nome" id="nome" class="form-control"
                           value="<?= sanitize((string)$dados['nome']) ?>" required autofocus
                           placeholder="Ex: ChatGPT 4o, Claude 3.5...">
                </div>
                <div class="col-md-5">
                    <label class="form-label">Plano</label>
                    <select name="plano" class="form-select">
                        <option value="gratuito"  <?= $dados['plano'] === 'gratuito'  ? 'selected' : '' ?>>✅ Gratuito</option>
                        <option value="freemium"  <?= $dados['plano'] === 'freemium'  ? 'selected' : '' ?>>🔵 Freemium</option>
                        <option value="pago"      <?= $dados['plano'] === 'pago'      ? 'selected' : '' ?>>💳 Pago</option>
                        <option value="creditos"  <?= $dados['plano'] === 'creditos'  ? 'selected' : '' ?>>🪙 Créditos</option>
                    </select>
                </div>
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

            <div class="mb-3">
                <label class="form-label">Categoria</label>
                <div class="d-flex flex-wrap gap-2">
                    <?php
                    $cats = ['chat'=>'💬 Chat','imagem'=>'🖼️ Imagem','codigo'=>'💻 Código',
                             'video'=>'🎬 Vídeo','audio'=>'🎤 Áudio','pesquisa'=>'🔍 Pesquisa','outro'=>'🤖 Outro'];
                    foreach ($cats as $key => $label): ?>
                    <label>
                        <input type="radio" name="categoria" value="<?= $key ?>"
                               <?= ($dados['categoria'] === $key) ? 'checked' : '' ?> hidden>
                        <span class="btn btn-sm <?= ($dados['categoria'] === $key) ? 'btn-primary' : 'btn-outline-secondary' ?> cat-btn">
                            <?= $label ?>
                        </span>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Descrição <span class="text-muted small">(opcional)</span></label>
                <input type="text" name="descricao" class="form-control"
                       value="<?= sanitize((string)$dados['descricao']) ?>"
                       placeholder="Para que você usa esta IA?">
            </div>

            <div class="mb-4">
                <div class="form-check">
                    <input type="checkbox" name="favorito" id="favorito" class="form-check-input"
                           value="1" <?= $dados['favorito'] ? 'checked' : '' ?>>
                    <label for="favorito" class="form-check-label">
                        <i class="fas fa-star text-warning me-1"></i>Favorito
                    </label>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Salvar</button>
                <a href="index.php" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>

<script>
// Destaca botão de marca selecionado
document.querySelectorAll('input[name="marca"]').forEach(radio => {
    radio.addEventListener('change', () => {
        document.querySelectorAll('.marca-btn').forEach(b => {
            b.style.background = '';
            b.style.color = b.style.borderColor;
        });
        const btn = radio.nextElementSibling;
        btn.style.background = btn.style.borderColor;
        btn.style.color = '#fff';
    });
});
// Destaca botão de categoria selecionado
document.querySelectorAll('input[name="categoria"]').forEach(radio => {
    radio.addEventListener('change', () => {
        document.querySelectorAll('.cat-btn').forEach(b => {
            b.className = b.className.replace('btn-primary','btn-outline-secondary');
        });
        radio.nextElementSibling.className = radio.nextElementSibling.className.replace('btn-outline-secondary','btn-primary');
    });
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
