<?php
require_once __DIR__ . '/../conexao.php';
require_once __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('/links/index.php');
verifyCsrf();

$id = (int)($_POST['id'] ?? 0);
if ($id) {
    $pdo->prepare('DELETE FROM links WHERE id = ?')->execute([$id]);
    flash('success', 'Link excluído.');
}

redirect('/links/index.php');
