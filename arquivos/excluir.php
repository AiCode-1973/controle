<?php
require_once __DIR__ . '/../conexao.php';
require_once __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('/projetos/index.php');
verifyCsrf();

$id         = (int)($_POST['id'] ?? 0);
$projeto_id = (int)($_POST['projeto_id'] ?? 0);

if ($id) {
    $stmt = $pdo->prepare('SELECT nome_arquivo FROM arquivos WHERE id = ?');
    $stmt->execute([$id]);
    $arq = $stmt->fetch();

    if ($arq) {
        $caminho = UPLOAD_DIR . $arq['nome_arquivo'];
        if (file_exists($caminho)) {
            unlink($caminho);
        }
        $pdo->prepare('DELETE FROM arquivos WHERE id = ?')->execute([$id]);
    }
}

redirect('/projetos/ver.php?id=' . $projeto_id);
