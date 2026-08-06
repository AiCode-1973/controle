<?php $menu_ativo = $menu_ativo ?? ''; ?>
<div id="sidebar-wrapper">
    <div class="sidebar-heading">
        <div class="sidebar-logo-icon"><i class="fas fa-code"></i></div>
        <span class="sidebar-logo-text"><?= APP_NAME ?></span>
    </div>

    <nav class="sidebar-nav">
        <div class="sidebar-section-label">Principal</div>
        <a href="<?= BASE_PATH ?>/" class="sidebar-link <?= $menu_ativo === 'dashboard' ? 'active' : '' ?>">
            <i class="fas fa-chart-pie"></i> Dashboard
        </a>
        <a href="<?= BASE_PATH ?>/clientes/index.php" class="sidebar-link <?= $menu_ativo === 'clientes' ? 'active' : '' ?>">
            <i class="fas fa-users"></i> Clientes
        </a>
        <a href="<?= BASE_PATH ?>/projetos/index.php" class="sidebar-link <?= $menu_ativo === 'projetos' ? 'active' : '' ?>">
            <i class="fas fa-layer-group"></i> Projetos
        </a>
        <a href="<?= BASE_PATH ?>/financeiro/index.php" class="sidebar-link <?= $menu_ativo === 'financeiro' ? 'active' : '' ?>">
            <i class="fas fa-wallet"></i> Financeiro
        </a>
        <a href="<?= BASE_PATH ?>/prompts/index.php" class="sidebar-link <?= $menu_ativo === 'prompts' ? 'active' : '' ?>">
            <i class="fas fa-robot"></i> Prompts
        </a>
        <a href="<?= BASE_PATH ?>/links/index.php" class="sidebar-link <?= $menu_ativo === 'links' ? 'active' : '' ?>">
            <i class="fas fa-bookmark"></i> Meus Links
        </a>

        <div class="sidebar-separator"></div>
        <div class="sidebar-section-label">Configurações</div>
        <a href="<?= BASE_PATH ?>/usuarios/index.php" class="sidebar-link <?= $menu_ativo === 'usuarios' ? 'active' : '' ?>">
            <i class="fas fa-user-shield"></i> Usuários
        </a>
        <a href="<?= BASE_PATH ?>/perfil.php" class="sidebar-link <?= $menu_ativo === 'perfil' ? 'active' : '' ?>">
            <i class="fas fa-user-circle"></i> Meu perfil
        </a>
    </nav>
</div>
