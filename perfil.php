<?php
require_once __DIR__ . '/conexao.php';
require_once __DIR__ . '/includes/auth.php';
$titulo     = 'Meu perfil';
$menu_ativo = 'perfil';
$erros      = [];

$stmt = $pdo->prepare('SELECT * FROM usuarios WHERE id = ?');
$stmt->execute([$_SESSION['usuario_id']]);
$usuario = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $nome           = trim($_POST['nome'] ?? '');
    $email          = trim($_POST['email'] ?? '');
    $senha_atual    = $_POST['senha_atual'] ?? '';
    $senha_nova     = $_POST['senha_nova'] ?? '';
    $senha_confirma = $_POST['senha_confirma'] ?? '';

    if ($nome  === '') $erros[] = 'Nome é obrigatório.';
    if ($email === '') $erros[] = 'E-mail é obrigatório.';

    $trocar_senha = $senha_atual !== '' || $senha_nova !== '';
    if ($trocar_senha) {
        if (!password_verify($senha_atual, $usuario['senha'])) {
            $erros[] = 'Senha atual incorreta.';
        } elseif (strlen($senha_nova) < 6) {
            $erros[] = 'Nova senha deve ter pelo menos 6 caracteres.';
        } elseif ($senha_nova !== $senha_confirma) {
            $erros[] = 'A confirmação de senha não confere.';
        }
    }

    if (empty($erros)) {
        if ($trocar_senha) {
            $pdo->prepare('UPDATE usuarios SET nome=?, email=?, senha=? WHERE id=?')
                ->execute([$nome, $email, password_hash($senha_nova, PASSWORD_DEFAULT), $_SESSION['usuario_id']]);
        } else {
            $pdo->prepare('UPDATE usuarios SET nome=?, email=? WHERE id=?')
                ->execute([$nome, $email, $_SESSION['usuario_id']]);
        }
        $_SESSION['usuario_nome'] = $nome;
        flash('success', 'Perfil atualizado com sucesso!');
        redirect('/perfil.php');
    }
    $usuario['nome']  = $nome;
    $usuario['email'] = $email;
}

include __DIR__ . '/includes/header.php';
?>
<div class="card" style="max-width:520px">
    <div class="card-header">Dados pessoais</div>
    <div class="card-body">
        <?php foreach ($erros as $e): ?>
            <div class="alert alert-danger py-2"><?= sanitize($e) ?></div>
        <?php endforeach; ?>
        <form method="post">
            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
            <div class="mb-3">
                <label class="form-label">Nome</label>
                <input type="text" name="nome" class="form-control"
                       value="<?= sanitize($usuario['nome']) ?>" required>
            </div>
            <div class="mb-4">
                <label class="form-label">E-mail</label>
                <input type="email" name="email" class="form-control"
                       value="<?= sanitize($usuario['email']) ?>" required>
            </div>
            <hr>
            <p class="text-muted small mb-3">Preencha apenas se quiser alterar a senha.</p>
            <div class="mb-3">
                <label class="form-label">Senha atual</label>
                <input type="password" name="senha_atual" class="form-control" autocomplete="current-password">
            </div>
            <div class="mb-3">
                <label class="form-label">Nova senha</label>
                <input type="password" name="senha_nova" class="form-control" autocomplete="new-password">
            </div>
            <div class="mb-4">
                <label class="form-label">Confirmar nova senha</label>
                <input type="password" name="senha_confirma" class="form-control" autocomplete="new-password">
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-1"></i>Salvar
            </button>
        </form>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
