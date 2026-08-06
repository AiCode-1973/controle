<?php
$titulo     = $titulo ?? APP_NAME;
$menu_ativo = $menu_ativo ?? '';

$overdue_count = 0;
if (isset($pdo)) {
    $overdue_count = (int)$pdo->query(
        "SELECT COUNT(*) FROM tarefas WHERE data_prazo < CURDATE() AND status != 'concluida'"
    )->fetchColumn();
}

$usuario_inicial = strtoupper(mb_substr($_SESSION['usuario_nome'] ?? 'U', 0, 1));
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= sanitize($titulo) ?> | <?= APP_NAME ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_PATH ?>/assets/css/style.css">
</head>
<body>
<div id="wrapper">
    <?php include __DIR__ . '/sidebar.php'; ?>
    <div id="page-content-wrapper">

        <header class="topbar">
            <button class="topbar-toggle" id="sidebarToggle" title="Menu">
                <i class="fas fa-bars"></i>
            </button>
            <span class="topbar-title"><?= sanitize($titulo) ?></span>
            <div class="topbar-actions">
                <?php if ($overdue_count > 0): ?>
                <a href="<?= BASE_PATH ?>/" class="topbar-btn bell-active" title="<?= $overdue_count ?> tarefa(s) com prazo vencido">
                    <i class="fas fa-bell"></i>
                    <span class="badge rounded-pill bg-danger" style="font-size:.6rem;position:absolute;margin-top:-8px;margin-left:8px">
                        <?= $overdue_count ?>
                    </span>
                </a>
                <?php endif; ?>
                <a href="<?= BASE_PATH ?>/perfil.php" class="topbar-user" title="Meu perfil">
                    <div class="topbar-avatar"><?= $usuario_inicial ?></div>
                    <span class="d-none d-md-inline"><?= sanitize($_SESSION['usuario_nome'] ?? '') ?></span>
                </a>
                <a href="<?= BASE_PATH ?>/logout.php" class="topbar-logout" title="Sair">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
            </div>
        </header>

        <main class="page-body">
            <?php showFlash(); ?>
