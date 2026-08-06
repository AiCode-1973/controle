<?php
require_once __DIR__ . '/../conexao.php';
require_once __DIR__ . '/../includes/auth.php';

$id         = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$titulo     = $id ? 'Editar usuário' : 'Novo usuário';
$menu_ativo = 'usuarios';
$erros      = [];
$dados      = ['nome' => '', 'email' => ''];

if ($id) {
    $stmt = $pdo->prepare('SELECT id, nome, email FROM usuarios WHERE id = ?');
    $stmt->execute([$id]);
    $dados = $stmt->fetch() ?: $dados;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $nome  = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if ($nome  === '') $erros[] = 'Nome é obrigatório.';
    if ($email === '') $erros[] = 'E-mail é obrigatório.';
    if (!$id && strlen($senha) < 6) $erros[] = 'Senha deve ter pelo menos 6 caracteres.';
    if ($id && $senha !== '' && strlen($senha) < 6) $erros[] = 'Nova senha deve ter pelo menos 6 caracteres.';

    if (empty($erros)) {
        $check = $pdo->prepare('SELECT id FROM usuarios WHERE email = ? AND id != ?');
        $check->execute([$email, $id]);
        if ($check->fetch()) $erros[] = 'Este e-mail já está em uso.';
    }

    if (empty($erros)) {
        if ($id) {
            if ($senha !== '') {
                $pdo->prepare('UPDATE usuarios SET nome=?, email=?, senha=? WHERE id=?')
                    ->execute([$nome, $email, password_hash($senha, PASSWORD_DEFAULT), $id]);
            } else {
                $pdo->prepare('UPDATE usuarios SET nome=?, email=? WHERE id=?')
                    ->execute([$nome, $email, $id]);
            }
        } else {
            $pdo->prepare('INSERT INTO usuarios (nome, email, senha) VALUES (?, ?, ?)')
                ->execute([$nome, $email, password_hash($senha, PASSWORD_DEFAULT)]);
        }
        flash('success', 'Usuário salvo!');
        redirect('/usuarios/index.php');
    }
    $dados = ['nome' => $nome, 'email' => $email];
}

include __DIR__ . '/../includes/header.php';
?>
<div class="card" style="max-width:480px">
    <div class="card-body">
        <?php foreach ($erros as $e): ?>
            <div class="alert alert-danger py-2"><?= sanitize($e) ?></div>
        <?php endforeach; ?>
        <form method="post">
            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
            <div class="mb-3">
                <label class="form-label">Nome <span class="text-danger">*</span></label>
                <input type="text" name="nome" class="form-control"
                       value="<?= sanitize((string)$dados['nome']) ?>" required autofocus>
            </div>
            <div class="mb-3">
                <label class="form-label">E-mail <span class="text-danger">*</span></label>
                <input type="email" name="email" class="form-control"
                       value="<?= sanitize((string)$dados['email']) ?>" required>
            </div>
            <div class="mb-4">
                <label class="form-label">
                    Senha
                    <?= $id
                        ? '<span class="text-muted small">(deixe em branco para manter)</span>'
                        : '<span class="text-danger">*</span>' ?>
                </label>
                <input type="password" name="senha" class="form-control"
                       <?= !$id ? 'required' : '' ?> autocomplete="new-password">
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Salvar</button>
                <a href="index.php" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
