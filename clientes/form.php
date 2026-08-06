<?php
require_once __DIR__ . '/../conexao.php';
require_once __DIR__ . '/../includes/auth.php';

$id         = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$titulo     = $id ? 'Editar cliente' : 'Novo cliente';
$menu_ativo = 'clientes';
$erros      = [];
$dados      = ['nome' => '', 'empresa' => '', 'telefone' => '', 'email' => '', 'observacao' => ''];

if ($id) {
    $stmt = $pdo->prepare('SELECT * FROM clientes WHERE id = ?');
    $stmt->execute([$id]);
    $dados = $stmt->fetch() ?: $dados;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $dados = [
        'nome'       => trim($_POST['nome'] ?? ''),
        'empresa'    => trim($_POST['empresa'] ?? ''),
        'telefone'   => trim($_POST['telefone'] ?? ''),
        'email'      => trim($_POST['email'] ?? ''),
        'observacao' => trim($_POST['observacao'] ?? ''),
    ];

    if ($dados['nome'] === '') $erros[] = 'Nome é obrigatório.';

    if (empty($erros)) {
        if ($id) {
            $stmt = $pdo->prepare('UPDATE clientes SET nome=?, empresa=?, telefone=?, email=?, observacao=? WHERE id=?');
            $stmt->execute([$dados['nome'], $dados['empresa'], $dados['telefone'], $dados['email'], $dados['observacao'], $id]);
        } else {
            $stmt = $pdo->prepare('INSERT INTO clientes (nome, empresa, telefone, email, observacao) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([$dados['nome'], $dados['empresa'], $dados['telefone'], $dados['email'], $dados['observacao']]);
        }
        flash('success', 'Cliente salvo com sucesso!');
        redirect('/clientes/index.php');
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
            <div class="mb-3">
                <label class="form-label">Nome <span class="text-danger">*</span></label>
                <input type="text" name="nome" class="form-control" value="<?= sanitize((string)$dados['nome']) ?>" required autofocus>
            </div>
            <div class="mb-3">
                <label class="form-label">Empresa</label>
                <input type="text" name="empresa" class="form-control" value="<?= sanitize((string)$dados['empresa']) ?>">
            </div>
            <div class="row mb-3">
                <div class="col">
                    <label class="form-label">Telefone</label>
                    <input type="text" name="telefone" class="form-control" value="<?= sanitize((string)$dados['telefone']) ?>">
                </div>
                <div class="col">
                    <label class="form-label">E-mail</label>
                    <input type="email" name="email" class="form-control" value="<?= sanitize((string)$dados['email']) ?>">
                </div>
            </div>
            <div class="mb-4">
                <label class="form-label">Observação</label>
                <textarea name="observacao" class="form-control" rows="3"><?= sanitize((string)$dados['observacao']) ?></textarea>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Salvar</button>
                <a href="index.php" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
