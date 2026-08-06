<?php
require_once __DIR__ . '/../conexao.php';
require_once __DIR__ . '/../includes/auth.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) redirect('/projetos/index.php');

$stmt = $pdo->prepare('SELECT * FROM arquivos WHERE id = ?');
$stmt->execute([$id]);
$arq = $stmt->fetch();
if (!$arq) redirect('/projetos/index.php');

$caminho = UPLOAD_DIR . $arq['nome_arquivo'];

// Previne path traversal
$real = realpath($caminho);
$base = realpath(UPLOAD_DIR);
if (!$real || !$base || strpos($real, $base . DIRECTORY_SEPARATOR) !== 0) {
    http_response_code(403);
    exit;
}

if (!file_exists($real)) redirect('/projetos/index.php');

header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . rawurlencode($arq['nome_original']) . '"');
header('Content-Length: ' . filesize($real));
header('Cache-Control: no-cache');
readfile($real);
exit;
