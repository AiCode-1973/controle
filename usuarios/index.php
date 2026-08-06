<?php
require_once __DIR__ . '/../conexao.php';
require_once __DIR__ . '/../includes/auth.php';
$titulo     = 'Usuários';
$menu_ativo = 'usuarios';

$usuarios = $pdo->query('SELECT * FROM usuarios ORDER BY nome')->fetchAll();

include __DIR__ . '/../includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h6 class="mb-0 text-muted"><?= count($usuarios) ?> usuário(s)</h6>
    <a href="form.php" class="btn btn-primary"><i class="fas fa-plus me-1"></i>Novo usuário</a>
</div>
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Nome</th>
                    <th>E-mail</th>
                    <th>Desde</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($usuarios as $u): ?>
                <tr>
                    <td>
                        <?= sanitize($u['nome']) ?>
                        <?php if ($u['id'] === (int)$_SESSION['usuario_id']): ?>
                            <span class="badge bg-secondary ms-1">você</span>
                        <?php endif; ?>
                    </td>
                    <td><?= sanitize($u['email']) ?></td>
                    <td><?= date('d/m/Y', strtotime($u['criado_em'])) ?></td>
                    <td class="text-end">
                        <a href="form.php?id=<?= $u['id'] ?>" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-edit"></i>
                        </a>
                        <?php if ($u['id'] !== (int)$_SESSION['usuario_id']): ?>
                        <form method="post" action="excluir.php" class="d-inline"
                              onsubmit="return confirm('Excluir usuário <?= sanitize($u['nome']) ?>?')">
                            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                            <input type="hidden" name="id" value="<?= $u['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
