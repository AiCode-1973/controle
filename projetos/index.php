<?php
require_once __DIR__ . '/../conexao.php';
require_once __DIR__ . '/../includes/auth.php';
$titulo     = 'Projetos';
$menu_ativo = 'projetos';

$status_filtro = $_GET['status'] ?? '';
$params = [];
$where  = '';
if ($status_filtro && in_array($status_filtro, ['em_andamento', 'concluido', 'pausado', 'cancelado'], true)) {
    $where  = 'WHERE p.status = ?';
    $params = [$status_filtro];
}

$stmt = $pdo->prepare("
    SELECT p.*, c.nome as cliente_nome,
           (SELECT COUNT(*) FROM tarefas WHERE projeto_id = p.id) as total_tarefas,
           (SELECT COUNT(*) FROM tarefas WHERE projeto_id = p.id AND status = 'concluida') as tarefas_concluidas
    FROM projetos p
    LEFT JOIN clientes c ON c.id = p.cliente_id
    $where
    ORDER BY p.criado_em DESC
");
$stmt->execute($params);
$projetos = $stmt->fetchAll();

include __DIR__ . '/../includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div class="d-flex gap-1 flex-wrap">
        <a href="index.php" class="btn btn-sm <?= !$status_filtro ? 'btn-dark' : 'btn-outline-secondary' ?>">Todos</a>
        <a href="?status=em_andamento" class="btn btn-sm <?= $status_filtro === 'em_andamento' ? 'btn-primary' : 'btn-outline-primary' ?>">Em andamento</a>
        <a href="?status=concluido"    class="btn btn-sm <?= $status_filtro === 'concluido'    ? 'btn-success' : 'btn-outline-success' ?>">Concluídos</a>
        <a href="?status=pausado"      class="btn btn-sm <?= $status_filtro === 'pausado'      ? 'btn-warning' : 'btn-outline-warning' ?>">Pausados</a>
        <a href="?status=cancelado"    class="btn btn-sm <?= $status_filtro === 'cancelado'    ? 'btn-danger'  : 'btn-outline-danger' ?>">Cancelados</a>
    </div>
    <a href="form.php" class="btn btn-primary"><i class="fas fa-plus me-1"></i>Novo projeto</a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Projeto</th>
                    <th>Cliente</th>
                    <th>Status</th>
                    <th>Tarefas</th>
                    <th>Início</th>
                    <th>Previsão</th>
                    <th>Valor</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($projetos as $p):
                    $pct = $p['total_tarefas'] > 0
                        ? round(($p['tarefas_concluidas'] / $p['total_tarefas']) * 100)
                        : 0;
                ?>
                <tr>
                    <td>
                        <a href="ver.php?id=<?= $p['id'] ?>" class="fw-semibold text-decoration-none">
                            <?= sanitize($p['nome']) ?>
                        </a>
                    </td>
                    <td><?= sanitize($p['cliente_nome'] ?? '—') ?></td>
                    <td><?= statusLabel($p['status']) ?></td>
                    <td>
                        <div class="d-flex align-items-center gap-1">
                            <div class="progress flex-grow-1" style="height:5px;min-width:50px">
                                <div class="progress-bar" style="width:<?= $pct ?>%"></div>
                            </div>
                            <small class="text-muted"><?= $p['tarefas_concluidas'] ?>/<?= $p['total_tarefas'] ?></small>
                        </div>
                    </td>
                    <td><?= formatData($p['data_inicio']) ?></td>
                    <td><?= formatData($p['data_previsao']) ?></td>
                    <td><?= formatMoeda((float)$p['valor_total']) ?></td>
                    <td class="text-end text-nowrap">
                        <a href="ver.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-primary" title="Abrir">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="form.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-secondary" title="Editar">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form method="post" action="excluir.php" class="d-inline"
                              onsubmit="return confirm('Excluir projeto e todos os seus dados?')">
                            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                            <input type="hidden" name="id" value="<?= $p['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Excluir">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($projetos)): ?>
                <tr>
                    <td colspan="8" class="text-center text-muted py-4">
                        <i class="fas fa-project-diagram fa-2x d-block mb-2 opacity-25"></i>
                        Nenhum projeto encontrado.
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
