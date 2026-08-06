<?php
$titulo     = $titulo ?? APP_NAME;
$menu_ativo = $menu_ativo ?? '';

$overdue_count = 0;
if (isset($pdo)) {
    $overdue_count = (int)$pdo->query(
        "SELECT COUNT(*) FROM tarefas WHERE data_prazo < CURDATE() AND status != 'concluida'"
    )->fetchColumn();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= sanitize($titulo) ?> | <?= APP_NAME ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_PATH ?>/assets/css/style.css">
</head>
<body>
<div class="d-flex" id="wrapper">
    <?php include __DIR__ . '/sidebar.php'; ?>
    <div id="page-content-wrapper" class="w-100">
        <nav class="navbar navbar-light bg-white border-bottom px-3 py-2">
            <button class="btn btn-sm btn-outline-secondary me-3" id="sidebarToggle">
                <i class="fas fa-bars"></i>
            </button>
            <span class="fw-semibold"><?= sanitize($titulo) ?></span>
            <div class="ms-auto d-flex align-items-center gap-2">
                <?php if ($overdue_count > 0): ?>
                <a href="<?= BASE_PATH ?>/" class="btn btn-sm btn-warning position-relative me-1"
                   title="<?= $overdue_count ?> tarefa(s) com prazo vencido">
                    <i class="fas fa-bell"></i>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                          style="font-size:0.6rem"><?= $overdue_count ?></span>
                </a>
                <?php endif; ?>
                <span class="text-muted small d-none d-md-inline">
                    <i class="fas fa-user me-1"></i><?= sanitize($_SESSION['usuario_nome'] ?? '') ?>
                </span>
                <a href="<?= BASE_PATH ?>/logout.php" class="btn btn-sm btn-outline-danger" title="Sair">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
            </div>
        </nav>
        <div class="container-fluid p-4">
            <?php showFlash(); ?>
