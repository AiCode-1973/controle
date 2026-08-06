<?php
require_once __DIR__ . '/conexao.php';
require_once __DIR__ . '/includes/auth.php';
$titulo     = 'Dashboard';
$menu_ativo = 'dashboard';

$stats = $pdo->query("SELECT status, COUNT(*) as total FROM projetos GROUP BY status")
             ->fetchAll(PDO::FETCH_KEY_PAIR);

$financeiro = $pdo->query("SELECT SUM(valor_total) as total, SUM(valor_pago) as pago FROM projetos")->fetch();

$projetos = $pdo->query("
    SELECT p.id, p.nome, p.status, p.valor_total, p.data_inicio, c.nome as cliente_nome
    FROM projetos p
    LEFT JOIN clientes c ON c.id = p.cliente_id
    ORDER BY p.criado_em DESC LIMIT 6
")->fetchAll();

$tarefas_pendentes = $pdo->query("SELECT COUNT(*) FROM tarefas WHERE status != 'concluida'")->fetchColumn();

include __DIR__ . '/includes/header.php';
?>
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card text-white bg-primary h-100">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <div class="fs-1 fw-bold"><?= $stats['em_andamento'] ?? 0 ?></div>
                    <div class="small">Em andamento</div>
                </div>
                <i class="fas fa-spinner fa-2x opacity-50"></i>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card text-white bg-success h-100">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <div class="fs-1 fw-bold"><?= $stats['concluido'] ?? 0 ?></div>
                    <div class="small">Concluídos</div>
                </div>
                <i class="fas fa-check-circle fa-2x opacity-50"></i>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card text-dark bg-warning h-100">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <div class="fs-1 fw-bold"><?= $tarefas_pendentes ?></div>
                    <div class="small">Tarefas pendentes</div>
                </div>
                <i class="fas fa-tasks fa-2x opacity-50"></i>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-bold text-success"><?= formatMoeda((float)($financeiro['pago'] ?? 0)) ?></div>
                        <div class="text-muted small">de <?= formatMoeda((float)($financeiro['total'] ?? 0)) ?></div>
                    </div>
                    <i class="fas fa-dollar-sign fa-2x text-success opacity-50"></i>
                </div>
                <?php
                $total = (float)($financeiro['total'] ?? 0);
                $pago  = (float)($financeiro['pago'] ?? 0);
                $pct   = $total > 0 ? round(($pago / $total) * 100) : 0;
                ?>
                <div class="progress mt-2" style="height:5px">
                    <div class="progress-bar bg-success" style="width:<?= $pct ?>%"></div>
                </div>
                <div class="text-muted small mt-1">Recebido</div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-project-diagram me-2"></i>Projetos recentes</span>
        <a href="<?= BASE_PATH ?>/projetos/index.php" class="btn btn-sm btn-outline-primary">Ver todos</a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Projeto</th>
                    <th>Cliente</th>
                    <th>Status</th>
                    <th>Valor</th>
                    <th>Início</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($projetos as $p): ?>
                <tr>
                    <td><?= sanitize($p['nome']) ?></td>
                    <td><?= sanitize($p['cliente_nome'] ?? '—') ?></td>
                    <td><?= statusLabel($p['status']) ?></td>
                    <td><?= formatMoeda((float)$p['valor_total']) ?></td>
                    <td><?= formatData($p['data_inicio']) ?></td>
                    <td>
                        <a href="<?= BASE_PATH ?>/projetos/ver.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-eye"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($projetos)): ?>
                <tr><td colspan="6" class="text-center text-muted py-4">Nenhum projeto cadastrado.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
