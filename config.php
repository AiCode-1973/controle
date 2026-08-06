<?php
define('APP_NAME', 'Controle de Projetos');
define('BASE_PATH', '');
define('UPLOAD_DIR', __DIR__ . '/arquivos/uploads/');
define('MAX_UPLOAD_SIZE', 10 * 1024 * 1024);
define('SESSION_TIMEOUT', 7200); // 2 horas

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Headers de segurança via PHP (funciona mesmo sem mod_headers)
if (!headers_sent()) {
    header('X-Frame-Options: DENY');
    header('X-Content-Type-Options: nosniff');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: no-referrer');
    header('Cache-Control: no-store, no-cache, must-revalidate, private');
    header('Pragma: no-cache');
}

require_once __DIR__ . '/includes/funcoes.php';
