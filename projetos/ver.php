<?php
require_once __DIR__ . '/../conexao.php';
require_once __DIR__ . '/../includes/auth.php';
$menu_ativo = 'projetos';

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

$titulo = $projeto['nome'];

$tarefas = $pdo->prepare('SELECT * FROM tarefas WHERE projeto_id = ? ORDER BY prioridade DESC, criado_em DESC');
$tarefas->execute([$id]);
$tarefas = $tarefas->fetchAll();

$arquivos = $pdo->prepare('SELECT * FROM arquivos WHERE projeto_id = ? ORDER BY criado_em DESC');
$arquivos->execute([$id]);
$arquivos = $arquivos->fetchAll();

$anotacoes = $pdo->prepare('SELECT * FROM anotacoes WHERE projeto_id = ? ORDER BY criado_em DESC');
$anotacoes->execute([$id]);
$anotacoes = $anotacoes->fetchAll();

$horas_stmt = $pdo->prepare('SELECT * FROM horas WHERE projeto_id = ? ORDER BY data DESC');
$horas_stmt->execute([$id]);
$horas_lista = $horas_stmt->fetchAll();
$total_horas = array_sum(array_column($horas_lista, 'quantidade'));

include __DIR__ . '/../includes/header.php';
?>
<div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-2">
    <div>
        <h4 class="mb-1"><?= sanitize($projeto['nome']) ?></h4>
        <span class="text-muted">Cliente: <?= sanitize($projeto['cliente_nome'] ?? '—') ?></span>
    </div>
    <div class="d-flex align-items-center gap-2">
        <?= statusLabel($projeto['status']) ?>
        <a href="form.php?id=<?= $id ?>" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-edit me-1"></i>Editar
        </a>
        <a href="relatorio.php?id=<?= $id ?>" class="btn btn-sm btn-outline-info" target="_blank">
            <i class="fas fa-file-alt me-1"></i>Relatório
        </a>
        <a href="index.php" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>Voltar
        </a>
    </div>
</div>

<div class="row g-3 mb-3">
    <!-- Informações -->
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header">Informações</div>
            <div class="card-body">
                <dl class="row mb-0 small">
                    <dt class="col-5">Início</dt>
                    <dd class="col-7"><?= formatData($projeto['data_inicio']) ?></dd>
                    <dt class="col-5">Previsão</dt>
                    <dd class="col-7"><?= formatData($projeto['data_previsao']) ?></dd>
                    <dt class="col-5">Conclusão</dt>
                    <dd class="col-7"><?= formatData($projeto['data_conclusao']) ?></dd>
                    <dt class="col-5">Total</dt>
                    <dd class="col-7"><?= formatMoeda((float)$projeto['valor_total']) ?></dd>
                    <dt class="col-5">Pago</dt>
                    <dd class="col-7 text-success"><?= formatMoeda((float)$projeto['valor_pago']) ?></dd>
                    <dt class="col-5">Pendente</dt>
                    <dd class="col-7 text-danger">
                        <?= formatMoeda((float)$projeto['valor_total'] - (float)$projeto['valor_pago']) ?>
                    </dd>
                </dl>
                <?php if ($projeto['descricao']): ?>
                    <hr>
                    <p class="mb-0 small text-muted"><?= nl2br(sanitize($projeto['descricao'])) ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Tarefas -->
    <div class="col-md-8">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Tarefas <span class="badge bg-secondary"><?= count($tarefas) ?></span></span>
                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#formTarefa">
                    <i class="fas fa-plus me-1"></i>Adicionar
                </button>
            </div>
            <div class="collapse" id="formTarefa">
                <div class="p-3 border-bottom bg-light">
                    <form method="post" action="<?= BASE_PATH ?>/tarefas/form.php">
                        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                        <input type="hidden" name="projeto_id" value="<?= $id ?>">
                        <div class="row g-2 mb-2">
                            <div class="col-md-6">
                                <input type="text" name="titulo" class="form-control form-control-sm"
                                       placeholder="Título *" required>
                            </div>
                            <div class="col-md-3">
                                <select name="prioridade" class="form-select form-select-sm">
                                    <option value="baixa">Baixa</option>
                                    <option value="media" selected>Média</option>
                                    <option value="alta">Alta</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <input type="date" name="data_prazo" class="form-control form-control-sm">
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <textarea name="descricao" class="form-control form-control-sm" rows="2"
                                      placeholder="Descrição (opcional)"></textarea>
                            <button type="submit" class="btn btn-sm btn-primary px-3">Salvar</button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="list-group list-group-flush" style="max-height:360px;overflow-y:auto">
                <?php foreach ($tarefas as $t): ?>
                <div class="list-group-item py-2">
                    <div class="d-flex justify-content-between align-items-start gap-2">
                        <div class="d-flex align-items-start gap-2 flex-grow-1">
                            <form method="post" action="<?= BASE_PATH ?>/tarefas/status.php" class="mt-1">
                                <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                                <input type="hidden" name="id" value="<?= $t['id'] ?>">
                                <input type="hidden" name="projeto_id" value="<?= $id ?>">
                                <button type="submit" class="btn btn-sm p-0 border-0 bg-transparent" title="Alternar status">
                                    <i class="fas fa-<?= $t['status'] === 'concluida' ? 'check-circle text-success' : 'circle text-muted' ?> fa-lg"></i>
                                </button>
                            </form>
                            <div>
                                <div class="<?= $t['status'] === 'concluida' ? 'text-decoration-line-through text-muted' : '' ?>">
                                    <?= sanitize($t['titulo']) ?>
                                </div>
                                <div class="d-flex gap-1 mt-1 flex-wrap">
                                    <?= statusTarefaLabel($t['status']) ?>
                                    <?= prioridadeLabel($t['prioridade']) ?>
                                    <?php if ($t['data_prazo']): ?>
                                        <span class="badge bg-light text-dark border">
                                            <i class="fas fa-calendar me-1"></i><?= formatData($t['data_prazo']) ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <form method="post" action="<?= BASE_PATH ?>/tarefas/excluir.php"
                              onsubmit="return confirm('Excluir tarefa?')">
                            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                            <input type="hidden" name="id" value="<?= $t['id'] ?>">
                            <input type="hidden" name="projeto_id" value="<?= $id ?>">
                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                <i class="fas fa-times"></i>
                            </button>
                        </form>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php if (empty($tarefas)): ?>
                <div class="list-group-item text-center text-muted py-4">Nenhuma tarefa.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <!-- Arquivos -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">Arquivos</div>
            <div class="card-body">
                <form method="post" action="<?= BASE_PATH ?>/arquivos/upload.php" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                    <input type="hidden" name="projeto_id" value="<?= $id ?>">
                    <div class="input-group mb-3">
                        <input type="file" name="arquivo" class="form-control form-control-sm" required>
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="fas fa-upload me-1"></i>Enviar
                        </button>
                    </div>
                </form>
                <ul class="list-group list-group-flush">
                    <?php foreach ($arquivos as $a): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span class="text-truncate me-2 small" style="max-width:200px"
                              title="<?= sanitize($a['nome_original']) ?>">
                            <i class="fas fa-file me-1 text-muted"></i><?= sanitize($a['nome_original']) ?>
                        </span>
                        <div class="d-flex gap-1">
                            <a href="<?= BASE_PATH ?>/arquivos/download.php?id=<?= $a['id'] ?>"
                               class="btn btn-sm btn-outline-secondary" title="Baixar">
                                <i class="fas fa-download"></i>
                            </a>
                            <form method="post" action="<?= BASE_PATH ?>/arquivos/excluir.php"
                                  onsubmit="return confirm('Excluir arquivo?')">
                                <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                                <input type="hidden" name="id" value="<?= $a['id'] ?>">
                                <input type="hidden" name="projeto_id" value="<?= $id ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="fas fa-times"></i>
                                </button>
                            </form>
                        </div>
                    </li>
                    <?php endforeach; ?>
                    <?php if (empty($arquivos)): ?>
                    <li class="list-group-item text-center text-muted small px-0">Nenhum arquivo enviado.</li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>

    <!-- Anotações -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">Anotações</div>
            <div class="card-body">
                <form method="post" action="<?= BASE_PATH ?>/anotacoes/form.php">
                    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                    <input type="hidden" name="projeto_id" value="<?= $id ?>">
                    <div class="d-flex gap-2 mb-3">
                        <textarea name="texto" class="form-control form-control-sm" rows="2"
                                  placeholder="Nova anotação..." required></textarea>
                        <button type="submit" class="btn btn-sm btn-primary px-3">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </form>
                <div style="max-height:300px;overflow-y:auto">
                    <?php foreach ($anotacoes as $an): ?>
                    <div class="border rounded p-2 mb-2 bg-light">
                        <div class="d-flex justify-content-between align-items-start gap-1">
                            <p class="mb-1 small"><?= nl2br(sanitize($an['texto'])) ?></p>
                            <form method="post" action="<?= BASE_PATH ?>/anotacoes/excluir.php">
                                <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                                <input type="hidden" name="id" value="<?= $an['id'] ?>">
                                <input type="hidden" name="projeto_id" value="<?= $id ?>">
                                <button type="submit" class="btn btn-sm btn-link text-danger p-0"
                                        onclick="return confirm('Excluir?')">
                                    <i class="fas fa-times"></i>
                                </button>
                            </form>
                        </div>
                        <small class="text-muted"><?= date('d/m/Y H:i', strtotime($an['criado_em'])) ?></small>
                    </div>
                    <?php endforeach; ?>
                    <?php if (empty($anotacoes)): ?>
                    <p class="text-center text-muted small mb-0">Nenhuma anotação.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="row g-3 mt-0">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>
                    <i class="fas fa-clock me-1"></i>Horas trabalhadas
                    <span class="badge bg-primary ms-1"><?= number_format($total_horas, 1, ',', '') ?>h total</span>
                </span>
                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#formHoras">
                    <i class="fas fa-plus me-1"></i>Registrar
                </button>
            </div>
            <div class="collapse" id="formHoras">
                <div class="p-3 border-bottom bg-light">
                    <form method="post" action="<?= BASE_PATH ?>/horas/form.php">
                        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                        <input type="hidden" name="projeto_id" value="<?= $id ?>">
                        <div class="row g-2 align-items-end">
                            <div class="col-md-5">
                                <label class="form-label form-label-sm mb-1">Descrição *</label>
                                <input type="text" name="descricao" class="form-control form-control-sm"
                                       placeholder="Atividade realizada" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label form-label-sm mb-1">Data</label>
                                <input type="date" name="data" class="form-control form-control-sm"
                                       value="<?= date('Y-m-d') ?>" required>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label form-label-sm mb-1">Horas</label>
                                <input type="number" name="quantidade" class="form-control form-control-sm"
                                       placeholder="Ex: 2.5" step="0.5" min="0.5" required>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-sm btn-primary w-100">Salvar</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0 small">
                    <thead class="table-light">
                        <tr><th>Data</th><th>Descrição</th><th>Horas</th><th></th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($horas_lista as $h): ?>
                        <tr>
                            <td><?= formatData($h['data']) ?></td>
                            <td><?= sanitize($h['descricao']) ?></td>
                            <td>
                                <span class="badge bg-light text-dark border">
                                    <?= number_format((float)$h['quantidade'], 1, ',', '') ?>h
                                </span>
                            </td>
                            <td>
                                <form method="post" action="<?= BASE_PATH ?>/horas/excluir.php"
                                      onsubmit="return confirm('Excluir?')">
                                    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                                    <input type="hidden" name="id" value="<?= $h['id'] ?>">
                                    <input type="hidden" name="projeto_id" value="<?= $id ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($horas_lista)): ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted py-3">Nenhuma hora registrada.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                    <?php if (!empty($horas_lista)): ?>
                    <tfoot class="table-light fw-bold">
                        <tr>
                            <td colspan="2" class="text-end">Total:</td>
                            <td colspan="2"><?= number_format($total_horas, 1, ',', '') ?> horas</td>
                        </tr>
                    </tfoot>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
