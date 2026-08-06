<?php
require_once __DIR__ . '/../conexao.php';
require_once __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('/projetos/index.php');
verifyCsrf();

$projeto_id = (int)($_POST['projeto_id'] ?? 0);
if (!$projeto_id) redirect('/projetos/index.php');

if (!isset($_FILES['arquivo']) || $_FILES['arquivo']['error'] !== UPLOAD_ERR_OK) {
    flash('danger', 'Erro no upload. Verifique o arquivo e tente novamente.');
    redirect('/projetos/ver.php?id=' . $projeto_id);
}

$file = $_FILES['arquivo'];

if ($file['size'] > MAX_UPLOAD_SIZE) {
    flash('danger', 'Arquivo muito grande. Máximo permitido: 10 MB.');
    redirect('/projetos/ver.php?id=' . $projeto_id);
}

// Whitelist de extensões permitidas
$allowed = ['pdf','doc','docx','xls','xlsx','ppt','pptx','png','jpg','jpeg','gif','webp','zip','rar','txt','csv','md'];
$ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
if (!in_array($ext, $allowed, true)) {
    flash('danger', 'Tipo de arquivo não permitido.');
    redirect('/projetos/ver.php?id=' . $projeto_id);
}

if (!is_dir(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
}

$novo_nome = bin2hex(random_bytes(16)) . '.' . $ext;
$destino   = UPLOAD_DIR . $novo_nome;

if (!move_uploaded_file($file['tmp_name'], $destino)) {
    flash('danger', 'Falha ao salvar o arquivo no servidor.');
    redirect('/projetos/ver.php?id=' . $projeto_id);
}

$pdo->prepare('INSERT INTO arquivos (projeto_id, nome_original, nome_arquivo, tamanho) VALUES (?, ?, ?, ?)')
    ->execute([$projeto_id, $file['name'], $novo_nome, $file['size']]);

redirect('/projetos/ver.php?id=' . $projeto_id);
