<?php
require_once __DIR__ . '/../conexao.php';
require_once __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('/projetos/index.php');
verifyCsrf();

$projeto_id = (int)($_POST['projeto_id'] ?? 0);
$descricao  = trim($_POST['descricao'] ?? '');
$data       = $_POST['data'] ?? date('Y-m-d');
$quantidade = (float)str_replace(',', '.', $_POST['quantidade'] ?? '0');

if ($projeto_id && $descricao !== '' && $quantidade > 0) {
    $pdo->prepare('INSERT INTO horas (projeto_id, descricao, data, quantidade) VALUES (?, ?, ?, ?)')
        ->execute([$projeto_id, $descricao, $data, $quantidade]);
}

redirect('/projetos/ver.php?id=' . $projeto_id);
