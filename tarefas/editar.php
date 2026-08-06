<?php
require_once __DIR__ . '/../conexao.php';
require_once __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('/projetos/index.php');
verifyCsrf();

$id         = (int)($_POST['id'] ?? 0);
$projeto_id = (int)($_POST['projeto_id'] ?? 0);
$titulo     = trim($_POST['titulo'] ?? '');
$descricao  = trim($_POST['descricao'] ?? '');
$status     = in_array($_POST['status'] ?? '', ['pendente','em_andamento','concluida'], true)
                ? $_POST['status'] : 'pendente';
$prioridade = in_array($_POST['prioridade'] ?? '', ['baixa','media','alta'], true)
                ? $_POST['prioridade'] : 'media';
$data_prazo = $_POST['data_prazo'] ?: null;

if ($id && $titulo !== '') {
    $concluido_em = null;
    if ($status === 'concluida') {
        // Preserva data de conclusão existente se já estava concluída
        $atual = $pdo->prepare('SELECT status, concluido_em FROM tarefas WHERE id = ?');
        $atual->execute([$id]);
        $row = $atual->fetch();
        $concluido_em = ($row && $row['concluido_em']) ? $row['concluido_em'] : date('Y-m-d H:i:s');
    }

    $pdo->prepare('
        UPDATE tarefas
        SET titulo=?, descricao=?, status=?, prioridade=?, data_prazo=?, concluido_em=?
        WHERE id=?
    ')->execute([$titulo, $descricao, $status, $prioridade, $data_prazo, $concluido_em, $id]);
}

redirect('/projetos/ver.php?id=' . $projeto_id);
