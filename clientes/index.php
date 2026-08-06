<?php
require_once __DIR__ . '/../conexao.php';
require_once __DIR__ . '/../includes/auth.php';
$titulo     = 'Clientes';
$menu_ativo = 'clientes';

$clientes = $pdo->query("
    SELECT c.*, COUNT(p.id) as total_projetos
    FROM clientes c
    LEFT JOIN projetos p ON p.cliente_id = c.id
    GROUP BY c.id
    ORDER BY c.nome
")->fetchAll();

include __DIR__ . '/../includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h6 class="mb-0 text-muted"><?= count($clientes) ?> cliente(s)</h6>
    <a href="form.php" class="btn btn-primary"><i class="fas fa-plus me-1"></i>Novo cliente</a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Nome</th>
                    <th>Empresa</th>
                    <th>Telefone</th>
                    <th>E-mail</th>
                    <th>Projetos</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($clientes as $c): ?>
                <tr>
                    <td class="fw-semibold"><?= sanitize($c['nome']) ?></td>
                    <td><?= sanitize($c['empresa'] ?? '—') ?></td>
                    <td><?= sanitize($c['telefone'] ?? '—') ?></td>
                    <td><?= sanitize($c['email'] ?? '—') ?></td>
                    <td><span class="badge bg-secondary"><?= $c['total_projetos'] ?></span></td>
                    <td class="text-end">
                        <a href="form.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form method="post" action="excluir.php" class="d-inline"
                              onsubmit="return confirm('Excluir cliente <?= sanitize($c['nome']) ?>?')">
                            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                            <input type="hidden" name="id" value="<?= $c['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($clientes)): ?>
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">
                        <i class="fas fa-users fa-2x d-block mb-2 opacity-25"></i>
                        Nenhum cliente cadastrado.
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
