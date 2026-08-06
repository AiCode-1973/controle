<?php
require_once __DIR__ . '/config.php';

$host    = '186.209.113.107';
$db      = 'dema5738_controle';
$user    = 'dema5738_controle';
$pass    = 'Dema@1973';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    die('Erro de conexão com o banco de dados.');
}
