<?php $menu_ativo = $menu_ativo ?? ''; ?>
<div id="sidebar-wrapper" class="bg-dark text-white">
    <div class="sidebar-heading py-3 px-3 border-bottom border-secondary">
        <i class="fas fa-code me-2 text-primary"></i>
        <span class="fw-bold small"><?= APP_NAME ?></span>
    </div>
    <nav class="py-2">
        <a href="<?= BASE_PATH ?>/" class="sidebar-link <?= $menu_ativo === 'dashboard' ? 'active' : '' ?>">
            <i class="fas fa-tachometer-alt"></i> Dashboard
        </a>
        <a href="<?= BASE_PATH ?>/clientes/index.php" class="sidebar-link <?= $menu_ativo === 'clientes' ? 'active' : '' ?>">
            <i class="fas fa-users"></i> Clientes
        </a>
        <a href="<?= BASE_PATH ?>/projetos/index.php" class="sidebar-link <?= $menu_ativo === 'projetos' ? 'active' : '' ?>">
            <i class="fas fa-project-diagram"></i> Projetos
        </a>
        <a href="<?= BASE_PATH ?>/financeiro/index.php" class="sidebar-link <?= $menu_ativo === 'financeiro' ? 'active' : '' ?>">
            <i class="fas fa-dollar-sign"></i> Financeiro
        </a>
        <div class="sidebar-separator"></div>
        <a href="<?= BASE_PATH ?>/usuarios/index.php" class="sidebar-link <?= $menu_ativo === 'usuarios' ? 'active' : '' ?>">
            <i class="fas fa-user-cog"></i> Usuários
        </a>
        <a href="<?= BASE_PATH ?>/perfil.php" class="sidebar-link <?= $menu_ativo === 'perfil' ? 'active' : '' ?>">
            <i class="fas fa-user-circle"></i> Meu perfil
        </a>
    </nav>
</div>
