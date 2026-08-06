<?php
require_once __DIR__ . '/../conexao.php';
require_once __DIR__ . '/../includes/auth.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) redirect('/projetos/index.php');

$stmt = $pdo->prepare('
    SELECT p.*, c.nome AS cliente_nome
    FROM projetos p
    LEFT JOIN clientes c ON c.id = p.cliente_id
    WHERE p.id = ?
');
$stmt->execute([$id]);
$projeto = $stmt->fetch();
if (!$projeto) redirect('/projetos/index.php');

$tarefas = $pdo->prepare('SELECT * FROM tarefas WHERE projeto_id = ? ORDER BY prioridade DESC, data_prazo');
$tarefas->execute([$id]);
$tarefas = $tarefas->fetchAll();

$horas_stmt = $pdo->prepare('SELECT * FROM horas WHERE projeto_id = ? ORDER BY data DESC');
$horas_stmt->execute([$id]);
$horas_lista = $horas_stmt->fetchAll();
$total_horas = array_sum(array_column($horas_lista, 'quantidade'));

$anotacoes = $pdo->prepare('SELECT * FROM anotacoes WHERE projeto_id = ? ORDER BY criado_em DESC');
$anotacoes->execute([$id]);
$anotacoes = $anotacoes->fetchAll();

$arquivos = $pdo->prepare('SELECT * FROM arquivos WHERE projeto_id = ? ORDER BY criado_em DESC');
$arquivos->execute([$id]);
$arquivos = $arquivos->fetchAll();

$pendente = (float)$projeto['valor_total'] - (float)$projeto['valor_pago'];

$status_texto = [
    'em_andamento' => 'Em andamento',
    'concluido'    => 'Concluído',
    'pausado'      => 'Pausado',
    'cancelado'    => 'Cancelado',
];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório: <?= sanitize($projeto['nome']) ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { font-size: 13px; }
        .rel-header { background: #0d6efd; color: #fff; padding: 1.25rem 1.5rem; border-radius: 0.5rem; }
        .section-title {
            border-left: 4px solid #0d6efd;
            padding-left: 0.5rem;
            margin: 1.5rem 0 0.75rem;
            font-weight: 700;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #495057;
        }
        @media print {
            .no-print { display: none !important; }
            .rel-header { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            body { margin: 0; padding: 0.5rem; }
        }
    </style>
</head>
<body class="p-3">

<div class="d-flex justify-content-between align-items-center mb-3 no-print">
    <a href="ver.php?id=<?= $id ?>" class="btn btn-sm btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i>Voltar
    </a>
    <button onclick="window.print()" class="btn btn-sm btn-primary">
        <i class="fas fa-print me-1"></i>Imprimir / Salvar PDF
    </button>
</div>

<div class="rel-header mb-4">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
        <div>
            <h4 class="mb-1"><?= sanitize($projeto['nome']) ?></h4>
            <span class="opacity-75">Cliente: <?= sanitize($projeto['cliente_nome'] ?? '—') ?></span>
        </div>
        <div class="text-end">
            <span class="badge bg-white text-dark fs-6">
                <?= $status_texto[$projeto['status']] ?? '—' ?>
            </span>
            <div class="small opacity-75 mt-1">Gerado em <?= date('d/m/Y \à\s H:i') ?></div>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-6">
        <div class="p-3 bg-light rounded h-100">
            <div class="section-title mt-0">Datas</div>
            <dl class="row mb-0">
                <dt class="col-5">Início</dt>       <dd class="col-7"><?= formatData($projeto['data_inicio']) ?></dd>
                <dt class="col-5">Previsão</dt>     <dd class="col-7"><?= formatData($projeto['data_previsao']) ?></dd>
                <dt class="col-5">Conclusão</dt>    <dd class="col-7"><?= formatData($projeto['data_conclusao']) ?></dd>
            </dl>
        </div>
    </div>
    <div class="col-md-6">
        <div class="p-3 bg-light rounded h-100">
            <div class="section-title mt-0">Financeiro</div>
            <dl class="row mb-0">
                <dt class="col-5">Total</dt>
                <dd class="col-7"><?= formatMoeda((float)$projeto['valor_total']) ?></dd>
                <dt class="col-5">Pago</dt>
                <dd class="col-7 text-success"><?= formatMoeda((float)$projeto['valor_pago']) ?></dd>
                <dt class="col-5">Pendente</dt>
                <dd class="col-7 text-danger"><?= formatMoeda($pendente) ?></dd>
                <dt class="col-5">Horas</dt>
                <dd class="col-7"><?= number_format($total_horas, 1, ',', '') ?>h</dd>
            </dl>
        </div>
    </div>
</div>

<?php if ($projeto['descricao']): ?>
<div class="section-title">Descrição</div>
<p class="text-muted"><?= nl2br(sanitize($projeto['descricao'])) ?></p>
<?php endif; ?>

<div class="section-title">Tarefas (<?= count($tarefas) ?>)</div>
<table class="table table-sm table-bordered">
    <thead class="table-light">
        <tr>
            <th>Tarefa</th><th>Status</th><th>Prioridade</th><th>Prazo</th><th>Concluída em</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($tarefas as $t): ?>
        <tr class="<?= $t['status'] === 'concluida' ? 'table-success' : '' ?>">
            <td><?= sanitize($t['titulo']) ?></td>
            <td><?= match($t['status']) {
                'pendente'     => 'Pendente',
                'em_andamento' => 'Em andamento',
                'concluida'    => 'Concluída',
                default        => '—'
            } ?></td>
            <td><?= ucfirst($t['prioridade']) ?></td>
            <td><?= formatData($t['data_prazo']) ?></td>
            <td><?= $t['concluido_em'] ? date('d/m/Y', strtotime($t['concluido_em'])) : '—' ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($tarefas)): ?>
        <tr><td colspan="5" class="text-center text-muted">Nenhuma tarefa.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<?php if (!empty($horas_lista)): ?>
<div class="section-title">Horas trabalhadas (<?= number_format($total_horas, 1, ',', '') ?>h)</div>
<table class="table table-sm table-bordered">
    <thead class="table-light">
        <tr><th>Data</th><th>Descrição</th><th>Horas</th></tr>
    </thead>
    <tbody>
        <?php foreach ($horas_lista as $h): ?>
        <tr>
            <td><?= formatData($h['data']) ?></td>
            <td><?= sanitize($h['descricao']) ?></td>
            <td><?= number_format((float)$h['quantidade'], 1, ',', '') ?>h</td>
        </tr>
        <?php endforeach; ?>
    </tbody>
    <tfoot class="table-light">
        <tr><th colspan="2">Total</th><th><?= number_format($total_horas, 1, ',', '') ?>h</th></tr>
    </tfoot>
</table>
<?php endif; ?>

<?php if (!empty($anotacoes)): ?>
<div class="section-title">Anotações (<?= count($anotacoes) ?>)</div>
<?php foreach ($anotacoes as $an): ?>
<div class="border rounded p-2 mb-2 bg-light">
    <p class="mb-1"><?= nl2br(sanitize($an['texto'])) ?></p>
    <small class="text-muted"><?= date('d/m/Y H:i', strtotime($an['criado_em'])) ?></small>
</div>
<?php endforeach; ?>
<?php endif; ?>

<?php if (!empty($arquivos)): ?>
<div class="section-title">Arquivos (<?= count($arquivos) ?>)</div>
<ul class="list-unstyled">
    <?php foreach ($arquivos as $a): ?>
    <li><i class="fas fa-file me-1 text-muted"></i><?= sanitize($a['nome_original']) ?></li>
    <?php endforeach; ?>
</ul>
<?php endif; ?>

</body>
</html>
