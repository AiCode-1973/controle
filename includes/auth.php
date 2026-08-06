<?php
require_once __DIR__ . '/../config.php';
if (!isset($_SESSION['usuario_id'])) {
    redirect('/login.php');
}
