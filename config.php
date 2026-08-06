<?php
define('APP_NAME', 'Controle de Projetos');
define('BASE_PATH', '/controle');
define('UPLOAD_DIR', __DIR__ . '/arquivos/uploads/');
define('MAX_UPLOAD_SIZE', 10 * 1024 * 1024); // 10 MB

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/includes/funcoes.php';
