<?php
require_once __DIR__ . '/../conexao.php';
require_once __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('/links/index.php');
verifyCsrf();

$id = (int)($_POST['id'] ?? 0);
if ($id) {
    $stmt = $pdo->prepare('SELECT favorito FROM links WHERE id = ?');
    $stmt->execute([$id]);
    $l = $stmt->fetch();
    if ($l) {
        $pdo->prepare('UPDATE links SET favorito = ? WHERE id = ?')
            ->execute([$l['favorito'] ? 0 : 1, $id]);
    }
}

redirect('/links/index.php');
