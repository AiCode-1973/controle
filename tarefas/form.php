<?php
require_once __DIR__ . '/../conexao.php';
require_once __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('/projetos/index.php');
verifyCsrf();

$projeto_id = (int)($_POST['projeto_id'] ?? 0);
$titulo     = trim($_POST['titulo'] ?? '');
$descricao  = trim($_POST['descricao'] ?? '');
$prioridade = in_array($_POST['prioridade'] ?? '', ['baixa', 'media', 'alta'], true)
                ? $_POST['prioridade'] : 'media';
$data_prazo = $_POST['data_prazo'] ?: null;

if ($projeto_id && $titulo !== '') {
    $stmt = $pdo->prepare('
        INSERT INTO tarefas (projeto_id, titulo, descricao, prioridade, data_prazo)
        VALUES (?, ?, ?, ?, ?)
    ');
    $stmt->execute([$projeto_id, $titulo, $descricao, $prioridade, $data_prazo]);
}

redirect('/projetos/ver.php?id=' . $projeto_id);
