<?php
require_once __DIR__ . '/../conexao.php';
require_once __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('/projetos/index.php');
verifyCsrf();

$id         = (int)($_POST['id'] ?? 0);
$projeto_id = (int)($_POST['projeto_id'] ?? 0);

if ($id) {
    $stmt = $pdo->prepare('SELECT status FROM tarefas WHERE id = ?');
    $stmt->execute([$id]);
    $tarefa = $stmt->fetch();

    if ($tarefa) {
        $novo_status  = $tarefa['status'] === 'concluida' ? 'pendente' : 'concluida';
        $concluido_em = $novo_status === 'concluida' ? date('Y-m-d H:i:s') : null;
        $pdo->prepare('UPDATE tarefas SET status = ?, concluido_em = ? WHERE id = ?')
            ->execute([$novo_status, $concluido_em, $id]);
    }
}

redirect('/projetos/ver.php?id=' . $projeto_id);
