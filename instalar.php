<?php
require_once __DIR__ . '/conexao.php';

try {
    $sql = file_get_contents(__DIR__ . '/banco.sql');
    foreach (explode(';', $sql) as $query) {
        $query = trim($query);
        if ($query !== '') {
            $pdo->exec($query);
        }
    }

    $total = $pdo->query('SELECT COUNT(*) FROM usuarios')->fetchColumn();
    $criado = false;
    if ($total == 0) {
        $pdo->prepare('INSERT INTO usuarios (nome, email, senha) VALUES (?, ?, ?)')
            ->execute(['Administrador', 'admin@controle.com', password_hash('Admin@1973', PASSWORD_DEFAULT)]);
        $criado = true;
    }
} catch (Exception $e) {
    die('Erro na instalação: ' . htmlspecialchars($e->getMessage()));
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Instalação | <?= APP_NAME ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="bg-light d-flex align-items-center" style="min-height:100vh">
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow">
                <div class="card-body p-4 text-center">
                    <h4 class="text-success mb-3">✔ Instalação concluída!</h4>
                    <p>Tabelas criadas com sucesso.</p>
                    <?php if ($criado): ?>
                    <div class="alert alert-info text-start">
                        <strong>Usuário criado:</strong><br>
                        E-mail: <code>admin@controle.com</code><br>
                        Senha: <code>Admin@1973</code>
                    </div>
                    <?php endif; ?>
                    <div class="alert alert-danger text-start">
                        <strong>Importante:</strong> apague o arquivo <code>instalar.php</code> agora!
                    </div>
                    <a href="login.php" class="btn btn-primary">Ir para o Login</a>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
