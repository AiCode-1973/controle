<?php
require_once __DIR__ . '/../conexao.php';
require_once __DIR__ . '/../includes/auth.php';

$id         = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$titulo     = $id ? 'Editar curso' : 'Novo curso';
$menu_ativo = 'cursos';
$erros      = [];
$dados = [
    'titulo'        => '', 'url'           => '', 'plataforma'    => 'outro',
    'categoria'     => '', 'status'        => 'nao_iniciado', 'progresso'     => 0,
    'nota'          => 0,  'preco'         => '', 'certificado'   => 0,
    'data_inicio'   => '', 'data_conclusao'=> '', 'observacao'    => '', 'favorito' => 0,
];

if ($id) {
    $stmt = $pdo->prepare('SELECT * FROM cursos WHERE id = ?');
    $stmt->execute([$id]);
    $dados = $stmt->fetch() ?: $dados;
    $dados['preco'] = $dados['preco'] ? number_format((float)$dados['preco'], 2, '.', '') : '';
}

$categorias_existentes = $pdo->query("SELECT DISTINCT categoria FROM cursos WHERE categoria IS NOT NULL AND categoria != '' ORDER BY categoria")->fetchAll(PDO::FETCH_COLUMN);

$plataformas = [
    'udemy'=>'Udemy','coursera'=>'Coursera','youtube'=>'YouTube','alura'=>'Alura',
    'rocketseat'=>'Rocketseat','dio'=>'DIO','hotmart'=>'Hotmart','kiwify'=>'Kiwify',
    'eduzz'=>'Eduzz','origamid'=>'Origamid','outro'=>'Outro',
];
$status_opts = [
    'nao_iniciado'=>'Não iniciado','em_andamento'=>'Em andamento',
    'concluido'=>'Concluído','pausado'=>'Pausado',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $dados = [
        'titulo'        => trim($_POST['titulo']        ?? ''),
        'url'           => trim($_POST['url']           ?? ''),
        'plataforma'    => array_key_exists($_POST['plataforma'] ?? '', $plataformas) ? $_POST['plataforma'] : 'outro',
        'categoria'     => trim($_POST['categoria']     ?? ''),
        'status'        => array_key_exists($_POST['status'] ?? '', $status_opts)     ? $_POST['status']     : 'nao_iniciado',
        'progresso'     => min(100, max(0, (int)($_POST['progresso'] ?? 0))),
        'nota'          => min(5,   max(0, (int)($_POST['nota']      ?? 0))),
        'preco'         => (float)str_replace(',', '.', $_POST['preco'] ?? '0'),
        'certificado'   => isset($_POST['certificado']) ? 1 : 0,
        'data_inicio'   => $_POST['data_inicio']    ?: null,
        'data_conclusao'=> $_POST['data_conclusao'] ?: null,
        'observacao'    => trim($_POST['observacao'] ?? ''),
        'favorito'      => isset($_POST['favorito']) ? 1 : 0,
    ];

    if ($dados['titulo'] === '') $erros[] = 'Título é obrigatório.';

    if (empty($erros)) {
        $cols = ['titulo','url','plataforma','categoria','status','progresso','nota','preco','certificado','data_inicio','data_conclusao','observacao','favorito'];
        $vals = array_map(fn($c) => $dados[$c], $cols);
        if ($id) {
            $set  = implode(', ', array_map(fn($c) => "$c=?", $cols));
            $pdo->prepare("UPDATE cursos SET $set WHERE id=?")->execute([...$vals, $id]);
        } else {
            $colnames = implode(',', $cols);
            $ph       = implode(',', array_fill(0, count($cols), '?'));
            $pdo->prepare("INSERT INTO cursos ($colnames) VALUES ($ph)")->execute($vals);
        }
        flash('success', 'Curso salvo!');
        redirect('/cursos/index.php');
    }
}

include __DIR__ . '/../includes/header.php';
?>
<div class="card" style="max-width:680px">
    <div class="card-body">
        <?php foreach ($erros as $e): ?>
            <div class="alert alert-danger py-2"><?= sanitize($e) ?></div>
        <?php endforeach; ?>
        <form method="post">
            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

            <div class="mb-3">
                <label class="form-label">Título do curso <span class="text-danger">*</span></label>
                <input type="text" name="titulo" class="form-control"
                       value="<?= sanitize((string)$dados['titulo']) ?>" required autofocus
                       placeholder="Ex: PHP do Zero ao Avançado">
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-5">
                    <label class="form-label">Plataforma</label>
                    <select name="plataforma" class="form-select">
                        <?php foreach ($plataformas as $val => $label): ?>
                        <option value="<?= $val ?>" <?= $dados['plataforma'] === $val ? 'selected' : '' ?>>
                            <?= $label ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <?php foreach ($status_opts as $val => $label): ?>
                        <option value="<?= $val ?>" <?= $dados['status'] === $val ? 'selected' : '' ?>>
                            <?= $label ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Área / Categoria</label>
                    <input type="text" name="categoria" class="form-control"
                           value="<?= sanitize((string)$dados['categoria']) ?>"
                           placeholder="Ex: PHP, Design..."
                           list="lista-cat">
                    <datalist id="lista-cat">
                        <?php foreach ($categorias_existentes as $cat): ?>
                            <option value="<?= sanitize($cat) ?>">
                        <?php endforeach; ?>
                    </datalist>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">URL do curso</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-link"></i></span>
                    <input type="url" name="url" class="form-control"
                           value="<?= sanitize((string)$dados['url']) ?>" placeholder="https://">
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label class="form-label">Progresso: <strong id="prog_val"><?= (int)$dados['progresso'] ?>%</strong></label>
                    <input type="range" name="progresso" id="progresso" class="form-range"
                           min="0" max="100" step="5" value="<?= (int)$dados['progresso'] ?>"
                           oninput="document.getElementById('prog_val').textContent=this.value+'%'">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Preço (R$)</label>
                    <input type="number" name="preco" class="form-control" step="0.01" min="0"
                           value="<?= sanitize((string)$dados['preco']) ?>" placeholder="0,00">
                </div>
                <div class="col-md-5">
                    <label class="form-label">Avaliação</label>
                    <div class="d-flex gap-1 pt-1" id="estrelas">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                        <label style="cursor:pointer;font-size:1.4rem;color:<?= $i <= (int)$dados['nota'] ? '#f59e0b' : '#d1d5db' ?>"
                               id="star_<?= $i ?>" onclick="setNota(<?= $i ?>)">★</label>
                        <?php endfor; ?>
                        <input type="hidden" name="nota" id="nota_val" value="<?= (int)$dados['nota'] ?>">
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label class="form-label">Data de início</label>
                    <input type="date" name="data_inicio" class="form-control"
                           value="<?= sanitize((string)($dados['data_inicio'] ?? '')) ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Data de conclusão</label>
                    <input type="date" name="data_conclusao" class="form-control"
                           value="<?= sanitize((string)($dados['data_conclusao'] ?? '')) ?>">
                </div>
                <div class="col-md-4 d-flex align-items-end gap-3 pb-1">
                    <div class="form-check">
                        <input type="checkbox" name="certificado" id="certificado" class="form-check-input"
                               value="1" <?= $dados['certificado'] ? 'checked' : '' ?>>
                        <label for="certificado" class="form-check-label">
                            <i class="fas fa-certificate text-warning me-1"></i>Certificado
                        </label>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" name="favorito" id="favorito" class="form-check-input"
                               value="1" <?= $dados['favorito'] ? 'checked' : '' ?>>
                        <label for="favorito" class="form-check-label">
                            <i class="fas fa-star text-warning me-1"></i>Favorito
                        </label>
                    </div>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label">Observações</label>
                <textarea name="observacao" class="form-control" rows="3"
                          placeholder="Anotações sobre o curso..."><?= sanitize((string)$dados['observacao']) ?></textarea>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Salvar</button>
                <a href="index.php" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>

<script>
function setNota(n) {
    document.getElementById('nota_val').value = n;
    for (let i = 1; i <= 5; i++) {
        document.getElementById('star_' + i).style.color = i <= n ? '#f59e0b' : '#d1d5db';
    }
}
// Hover nas estrelas
for (let i = 1; i <= 5; i++) {
    const el = document.getElementById('star_' + i);
    el.addEventListener('mouseenter', () => {
        for (let j = 1; j <= 5; j++) {
            document.getElementById('star_' + j).style.color = j <= i ? '#fbbf24' : '#d1d5db';
        }
    });
    el.addEventListener('mouseleave', () => {
        const v = parseInt(document.getElementById('nota_val').value);
        for (let j = 1; j <= 5; j++) {
            document.getElementById('star_' + j).style.color = j <= v ? '#f59e0b' : '#d1d5db';
        }
    });
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
