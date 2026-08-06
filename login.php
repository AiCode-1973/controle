<?php
require_once __DIR__ . '/conexao.php';

if (isset($_SESSION['usuario_id'])) {
    redirect('/');
}

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if ($email !== '' && $senha !== '') {
        $stmt = $pdo->prepare('SELECT id, nome, senha FROM usuarios WHERE email = ?');
        $stmt->execute([$email]);
        $usuario = $stmt->fetch();

        if ($usuario && password_verify($senha, $usuario['senha'])) {
            session_regenerate_id(true);
            $_SESSION['usuario_id']   = $usuario['id'];
            $_SESSION['usuario_nome'] = $usuario['nome'];
            redirect('/');
        }
    }
    $erro = 'E-mail ou senha inválidos.';
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | <?= APP_NAME ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; }

        html, body {
            height: 100%;
            margin: 0;
            font-family: 'Segoe UI', system-ui, sans-serif;
        }

        .login-wrapper {
            display: flex;
            min-height: 100vh;
        }

        /* ── Painel direito (apresentação) ── */
        .login-panel-right {
            flex: 1;
            background: linear-gradient(135deg, #0d6efd 0%, #0a3d8f 60%, #061f4a 100%);
            color: #fff;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 3rem 2.5rem;
            position: relative;
            overflow: hidden;
        }

        .login-panel-right::before {
            content: '';
            position: absolute;
            width: 420px; height: 420px;
            border-radius: 50%;
            background: rgba(255,255,255,0.05);
            top: -100px; right: -100px;
        }

        .login-panel-right::after {
            content: '';
            position: absolute;
            width: 280px; height: 280px;
            border-radius: 50%;
            background: rgba(255,255,255,0.05);
            bottom: -60px; left: -60px;
        }

        .right-content { position: relative; z-index: 1; max-width: 420px; }

        .right-logo {
            width: 64px; height: 64px;
            background: rgba(255,255,255,0.15);
            border-radius: 16px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.75rem;
            margin-bottom: 1.75rem;
            backdrop-filter: blur(8px);
        }

        .right-content h1 {
            font-size: 1.9rem;
            font-weight: 700;
            line-height: 1.25;
            margin-bottom: 1rem;
        }

        .right-content p {
            font-size: 0.95rem;
            opacity: 0.8;
            line-height: 1.7;
            margin-bottom: 2rem;
        }

        .feature-list {
            list-style: none;
            padding: 0; margin: 0;
        }

        .feature-list li {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.55rem 0;
            font-size: 0.9rem;
            opacity: 0.9;
            border-bottom: 1px solid rgba(255,255,255,0.08);
        }

        .feature-list li:last-child { border-bottom: none; }

        .feature-list li i {
            width: 32px; height: 32px;
            background: rgba(255,255,255,0.12);
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
            font-size: 0.8rem;
        }

        /* ── Painel esquerdo (formulário) ── */
        .login-panel-left {
            width: 460px;
            flex-shrink: 0;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 3rem 3rem;
            background: #fff;
        }

        .login-brand {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            margin-bottom: 2.5rem;
        }

        .login-brand-icon {
            width: 40px; height: 40px;
            background: #0d6efd;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            color: #fff;
            font-size: 1rem;
        }

        .login-brand span {
            font-weight: 700;
            font-size: 1.05rem;
            color: #212529;
        }

        .login-title {
            font-size: 1.6rem;
            font-weight: 700;
            color: #212529;
            margin-bottom: 0.4rem;
        }

        .login-subtitle {
            color: #6c757d;
            font-size: 0.9rem;
            margin-bottom: 2rem;
        }

        .form-label {
            font-weight: 600;
            font-size: 0.85rem;
            color: #495057;
            margin-bottom: 0.35rem;
        }

        .form-control {
            border-radius: 10px;
            border: 1.5px solid #dee2e6;
            padding: 0.65rem 1rem;
            font-size: 0.9rem;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .form-control:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 3px rgba(13,110,253,0.12);
        }

        .input-group-icon {
            position: relative;
        }

        .input-group-icon i {
            position: absolute;
            left: 0.9rem;
            top: 50%;
            transform: translateY(-50%);
            color: #adb5bd;
            font-size: 0.85rem;
            z-index: 5;
        }

        .input-group-icon .form-control {
            padding-left: 2.5rem;
        }

        .btn-login {
            background: linear-gradient(135deg, #0d6efd, #0a3d8f);
            border: none;
            border-radius: 10px;
            padding: 0.7rem;
            font-weight: 600;
            font-size: 0.95rem;
            letter-spacing: 0.3px;
            transition: opacity 0.2s, transform 0.1s;
        }

        .btn-login:hover  { opacity: 0.92; transform: translateY(-1px); }
        .btn-login:active { transform: translateY(0); }

        .login-footer {
            margin-top: 2.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid #f0f0f0;
            text-align: center;
            color: #adb5bd;
            font-size: 0.78rem;
        }

        /* ── Responsivo ── */
        @media (max-width: 768px) {
            .login-panel-right { display: none; }
            .login-panel-left  { width: 100%; padding: 2rem 1.5rem; }
        }
    </style>
</head>
<body>
<div class="login-wrapper">

    <!-- Painel esquerdo: apresentação -->
    <div class="login-panel-right">
        <div class="right-content">
            <div class="right-logo">
                <i class="fas fa-layer-group"></i>
            </div>

            <h1>Controle total dos seus projetos de sistemas</h1>

            <p>
                Gerencie clientes, projetos, tarefas e finanças em um único lugar.
                Acompanhe o progresso, registre horas trabalhadas e gere relatórios
                de forma rápida e eficiente.
            </p>

            <ul class="feature-list">
                <li>
                    <i class="fas fa-project-diagram"></i>
                    Gestão completa de projetos e clientes
                </li>
                <li>
                    <i class="fas fa-tasks"></i>
                    Controle de tarefas com prioridade e prazos
                </li>
                <li>
                    <i class="fas fa-dollar-sign"></i>
                    Acompanhamento financeiro por projeto
                </li>
                <li>
                    <i class="fas fa-clock"></i>
                    Registro de horas trabalhadas
                </li>
                <li>
                    <i class="fas fa-file-alt"></i>
                    Relatórios detalhados com exportação para PDF
                </li>
                <li>
                    <i class="fas fa-bell"></i>
                    Alertas automáticos de prazos vencidos
                </li>
            </ul>
        </div>
    </div>

    <!-- Painel direito: formulário -->
    <div class="login-panel-left">
        <div class="login-brand">
            <div class="login-brand-icon"><i class="fas fa-code"></i></div>
            <span><?= APP_NAME ?></span>
        </div>

        <h2 class="login-title">Bem-vindo de volta</h2>
        <p class="login-subtitle">Acesse sua conta para gerenciar seus projetos</p>

        <?php if ($erro): ?>
            <div class="alert alert-danger d-flex align-items-center gap-2 py-2 mb-3" style="border-radius:10px;font-size:.875rem">
                <i class="fas fa-exclamation-circle"></i>
                <?= sanitize($erro) ?>
            </div>
        <?php endif; ?>

        <form method="post" autocomplete="off">
            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

            <div class="mb-3">
                <label class="form-label">E-mail</label>
                <div class="input-group-icon">
                    <i class="fas fa-envelope"></i>
                    <input type="email" name="email" class="form-control"
                           placeholder="seu@email.com" required autofocus>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label">Senha</label>
                <div class="input-group-icon">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="senha" class="form-control"
                           placeholder="••••••••" required>
                </div>
            </div>

            <button type="submit" class="btn btn-login btn-primary w-100 text-white">
                Entrar <i class="fas fa-arrow-right ms-1"></i>
            </button>
        </form>

        <div class="login-footer">
            &copy; <?= date('Y') ?> <?= APP_NAME ?> &mdash; Todos os direitos reservados
        </div>
    </div>

</div>
</body>
</html>

