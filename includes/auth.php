<?php
require_once __DIR__ . '/../config.php';
if (!isset($_SESSION['usuario_id'])) {
    redirect('/login.php');
}

// Encerra sessão por inatividade
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > SESSION_TIMEOUT) {
    session_unset();
    session_destroy();
    redirect('/login.php');
}
$_SESSION['last_activity'] = time();
