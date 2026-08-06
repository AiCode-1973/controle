<?php
require_once __DIR__ . '/../conexao.php';
require_once __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('/ias/index.php');
verifyCsrf();

$id = (int)($_POST['id'] ?? 0);
if ($id) {
    $stmt = $pdo->prepare('SELECT favorito FROM ias WHERE id = ?');
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if ($row) {
        $pdo->prepare('UPDATE ias SET favorito = ? WHERE id = ?')
            ->execute([$row['favorito'] ? 0 : 1, $id]);
    }
}

redirect('/ias/index.php');
