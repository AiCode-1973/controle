<?php
require_once __DIR__ . '/../conexao.php';
require_once __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('/usuarios/index.php');
verifyCsrf();

$id = (int)($_POST['id'] ?? 0);
// Impede auto-exclusão
if ($id && $id !== (int)$_SESSION['usuario_id']) {
    $pdo->prepare('DELETE FROM usuarios WHERE id = ?')->execute([$id]);
    flash('success', 'Usuário excluído.');
}

redirect('/usuarios/index.php');
