<?php
require_once __DIR__ . '/../conexao.php';
require_once __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('/projetos/index.php');
verifyCsrf();

$id = (int)($_POST['id'] ?? 0);
if ($id) {
    // Remove arquivos físicos antes de excluir o projeto
    $stmt = $pdo->prepare('SELECT nome_arquivo FROM arquivos WHERE projeto_id = ?');
    $stmt->execute([$id]);
    foreach ($stmt->fetchAll() as $a) {
        $caminho = UPLOAD_DIR . $a['nome_arquivo'];
        if (file_exists($caminho)) {
            unlink($caminho);
        }
    }
    $pdo->prepare('DELETE FROM projetos WHERE id = ?')->execute([$id]);
    flash('success', 'Projeto excluído.');
}

redirect('/projetos/index.php');
