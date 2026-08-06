<?php
require_once __DIR__ . '/../conexao.php';
require_once __DIR__ . '/../includes/auth.php';

$id         = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$titulo     = $id ? 'Editar projeto' : 'Novo projeto';
$menu_ativo = 'projetos';
$erros      = [];

$dados = [
    'cliente_id'     => '',
    'nome'           => '',
    'descricao'      => '',
    'url_projeto'    => '',
    'status'         => 'em_andamento',
    'valor_total'    => '',
    'valor_pago'     => '',
    'data_inicio'    => '',
    'data_previsao'  => '',
    'data_conclusao' => '',
];

if ($id) {
    $stmt = $pdo->prepare('SELECT * FROM projetos WHERE id = ?');
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if ($row) {
        $dados = array_merge($dados, $row);
        $dados['valor_total'] = $dados['valor_total'] ? number_format((float)$dados['valor_total'], 2, '.', '') : '';
        $dados['valor_pago']  = $dados['valor_pago']  ? number_format((float)$dados['valor_pago'],  2, '.', '') : '';
    }
}

$clientes = $pdo->query('SELECT id, nome FROM clientes ORDER BY nome')->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $dados = [
        'cliente_id'     => (int)($_POST['cliente_id'] ?? 0) ?: null,
        'nome'           => trim($_POST['nome'] ?? ''),
        'descricao'      => trim($_POST['descricao'] ?? ''),
        'url_projeto'    => trim($_POST['url_projeto'] ?? ''),
        'status'         => in_array($_POST['status'] ?? '', ['em_andamento','concluido','pausado','cancelado'], true)
                                ? $_POST['status'] : 'em_andamento',
        'valor_total'    => (float)str_replace(',', '.', $_POST['valor_total'] ?? '0'),
        'valor_pago'     => (float)str_replace(',', '.', $_POST['valor_pago']  ?? '0'),
        'data_inicio'    => $_POST['data_inicio']    ?: null,
        'data_previsao'  => $_POST['data_previsao']  ?: null,
        'data_conclusao' => $_POST['data_conclusao'] ?: null,
    ];

    if ($dados['nome'] === '') $erros[] = 'Nome é obrigatório.';

    if (empty($erros)) {
        $cols = ['cliente_id','nome','descricao','url_projeto','status','valor_total','valor_pago',
                 'data_inicio','data_previsao','data_conclusao'];
        $vals = array_map(fn($c) => $dados[$c], $cols);

        if ($id) {
            $set  = implode(', ', array_map(fn($c) => "$c = ?", $cols));
            $stmt = $pdo->prepare("UPDATE projetos SET $set WHERE id = ?");
            $stmt->execute([...$vals, $id]);
        } else {
            $colnames     = implode(', ', $cols);
            $placeholders = implode(', ', array_fill(0, count($cols), '?'));
            $stmt         = $pdo->prepare("INSERT INTO projetos ($colnames) VALUES ($placeholders)");
            $stmt->execute($vals);
            $id = (int)$pdo->lastInsertId();
        }
        flash('success', 'Projeto salvo com sucesso!');
        redirect('/projetos/ver.php?id=' . $id);
    }
}

include __DIR__ . '/../includes/header.php';
?>
<div class="card">
    <div class="card-body">
        <?php foreach ($erros as $e): ?>
            <div class="alert alert-danger py-2"><?= sanitize($e) ?></div>
        <?php endforeach; ?>
        <form method="post">
            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label">Nome do projeto <span class="text-danger">*</span></label>
                    <input type="text" name="nome" class="form-control"
                           value="<?= sanitize((string)$dados['nome']) ?>" required autofocus>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="em_andamento" <?= $dados['status'] === 'em_andamento' ? 'selected' : '' ?>>Em andamento</option>
                        <option value="concluido"    <?= $dados['status'] === 'concluido'    ? 'selected' : '' ?>>Concluído</option>
                        <option value="pausado"      <?= $dados['status'] === 'pausado'      ? 'selected' : '' ?>>Pausado</option>
                        <option value="cancelado"    <?= $dados['status'] === 'cancelado'    ? 'selected' : '' ?>>Cancelado</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Cliente</label>
                    <select name="cliente_id" class="form-select">
                        <option value="">— Selecione —</option>
                        <?php foreach ($clientes as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= $dados['cliente_id'] == $c['id'] ? 'selected' : '' ?>>
                                <?= sanitize($c['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Valor total (R$)</label>
                    <input type="number" name="valor_total" class="form-control" step="0.01" min="0"
                           value="<?= sanitize((string)$dados['valor_total']) ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Valor pago (R$)</label>
                    <input type="number" name="valor_pago" class="form-control" step="0.01" min="0"
                           value="<?= sanitize((string)$dados['valor_pago']) ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Data de início</label>
                    <input type="date" name="data_inicio" class="form-control"
                           value="<?= sanitize((string)($dados['data_inicio'] ?? '')) ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Previsão de entrega</label>
                    <input type="date" name="data_previsao" class="form-control"
                           value="<?= sanitize((string)($dados['data_previsao'] ?? '')) ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Data de conclusão</label>
                    <input type="date" name="data_conclusao" class="form-control"
                           value="<?= sanitize((string)($dados['data_conclusao'] ?? '')) ?>">
                </div>
                <div class="col-12">
                    <label class="form-label">URL do projeto</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-globe"></i></span>
                        <input type="url" name="url_projeto" class="form-control"
                               placeholder="https://" value="<?= sanitize((string)($dados['url_projeto'] ?? '')) ?>">
                    </div>
                </div>
                <div class="col-12">
                    <label class="form-label">Descrição</label>
                    <textarea name="descricao" class="form-control" rows="4"><?= sanitize((string)$dados['descricao']) ?></textarea>
                </div>
                <div class="col-12 d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Salvar</button>
                    <a href="index.php" class="btn btn-secondary">Cancelar</a>
                </div>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
