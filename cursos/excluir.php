<?php
require_once __DIR__ . '/../conexao.php';
require_once __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('/cursos/index.php');
verifyCsrf();

$id = (int)($_POST['id'] ?? 0);
if ($id) {
    $pdo->prepare('DELETE FROM cursos WHERE id = ?')->execute([$id]);
    flash('success', 'Curso excluído.');
}

redirect('/cursos/index.php');
