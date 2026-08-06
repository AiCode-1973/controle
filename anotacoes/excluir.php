<?php
require_once __DIR__ . '/../conexao.php';
require_once __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('/projetos/index.php');
verifyCsrf();

$id         = (int)($_POST['id'] ?? 0);
$projeto_id = (int)($_POST['projeto_id'] ?? 0);

if ($id) {
    $pdo->prepare('DELETE FROM anotacoes WHERE id = ?')->execute([$id]);
}

redirect('/projetos/ver.php?id=' . $projeto_id);
