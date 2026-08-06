<?php
require_once __DIR__ . '/../conexao.php';
require_once __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('/prompts/index.php');
verifyCsrf();

$id = (int)($_POST['id'] ?? 0);
if ($id) {
    $stmt = $pdo->prepare('SELECT favorito FROM prompts WHERE id = ?');
    $stmt->execute([$id]);
    $p = $stmt->fetch();
    if ($p) {
        $pdo->prepare('UPDATE prompts SET favorito = ? WHERE id = ?')
            ->execute([$p['favorito'] ? 0 : 1, $id]);
    }
}

redirect('/prompts/index.php');
