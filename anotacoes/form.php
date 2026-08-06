<?php
require_once __DIR__ . '/../conexao.php';
require_once __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('/projetos/index.php');
verifyCsrf();

$projeto_id = (int)($_POST['projeto_id'] ?? 0);
$texto      = trim($_POST['texto'] ?? '');

if ($projeto_id && $texto !== '') {
    $pdo->prepare('INSERT INTO anotacoes (projeto_id, texto) VALUES (?, ?)')
        ->execute([$projeto_id, $texto]);
}

redirect('/projetos/ver.php?id=' . $projeto_id);
