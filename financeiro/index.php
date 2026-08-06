<?php
require_once __DIR__ . '/../conexao.php';
require_once __DIR__ . '/../includes/auth.php';
$titulo     = 'Financeiro';
$menu_ativo = 'financeiro';

$projetos = $pdo->query("
    SELECT p.*, c.nome AS cliente_nome
    FROM projetos p
    LEFT JOIN clientes c ON c.id = p.cliente_id
    ORDER BY p.criado_em DESC
")->fetchAll();

$totais = $pdo->query("
    SELECT
        SUM(valor_total)                AS total,
        SUM(valor_pago)                 AS pago,
        SUM(valor_total - valor_pago)   AS pendente
    FROM projetos
")->fetch();

include __DIR__ . '/../includes/header.php';
?>
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 bg-light">
            <div class="card-body text-center py-4">
                <div class="text-muted mb-1 small">Total contratado</div>
                <div class="fs-3 fw-bold"><?= formatMoeda((float)($totais['total'] ?? 0)) ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0" style="background:#d1f5e0">
            <div class="card-body text-center py-4">
                <div class="text-muted mb-1 small">Recebido</div>
                <div class="fs-3 fw-bold text-success"><?= formatMoeda((float)($totais['pago'] ?? 0)) ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0" style="background:#fde8e8">
            <div class="card-body text-center py-4">
                <div class="text-muted mb-1 small">Pendente</div>
                <div class="fs-3 fw-bold text-danger"><?= formatMoeda((float)($totais['pendente'] ?? 0)) ?></div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">Detalhamento por projeto</div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Projeto</th>
                    <th>Cliente</th>
                    <th>Status</th>
                    <th>Total</th>
                    <th>Pago</th>
                    <th>Pendente</th>
                    <th style="min-width:100px">% Pago</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($projetos as $p):
                    $pendente = (float)$p['valor_total'] - (float)$p['valor_pago'];
                    $pct      = $p['valor_total'] > 0
                        ? round(((float)$p['valor_pago'] / (float)$p['valor_total']) * 100)
                        : 0;
                ?>
                <tr>
                    <td>
                        <a href="<?= BASE_PATH ?>/projetos/ver.php?id=<?= $p['id'] ?>"
                           class="text-decoration-none fw-semibold">
                            <?= sanitize($p['nome']) ?>
                        </a>
                    </td>
                    <td><?= sanitize($p['cliente_nome'] ?? '—') ?></td>
                    <td><?= statusLabel($p['status']) ?></td>
                    <td><?= formatMoeda((float)$p['valor_total']) ?></td>
                    <td class="text-success"><?= formatMoeda((float)$p['valor_pago']) ?></td>
                    <td class="<?= $pendente > 0 ? 'text-danger' : 'text-muted' ?>">
                        <?= formatMoeda($pendente) ?>
                    </td>
                    <td>
                        <div class="progress mb-1" style="height:6px">
                            <div class="progress-bar bg-success" style="width:<?= $pct ?>%"></div>
                        </div>
                        <small class="text-muted"><?= $pct ?>%</small>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($projetos)): ?>
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">Nenhum projeto cadastrado.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
