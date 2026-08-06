<?php
function statusLabel(string $status): string {
    return match($status) {
        'em_andamento' => '<span class="badge bg-primary">Em andamento</span>',
        'concluido'    => '<span class="badge bg-success">Concluído</span>',
        'pausado'      => '<span class="badge bg-warning text-dark">Pausado</span>',
        'cancelado'    => '<span class="badge bg-danger">Cancelado</span>',
        default        => '<span class="badge bg-secondary">—</span>',
    };
}

function statusTarefaLabel(string $status): string {
    return match($status) {
        'pendente'     => '<span class="badge bg-secondary">Pendente</span>',
        'em_andamento' => '<span class="badge bg-primary">Em andamento</span>',
        'concluida'    => '<span class="badge bg-success">Concluída</span>',
        default        => '<span class="badge bg-secondary">—</span>',
    };
}

function prioridadeLabel(string $prioridade): string {
    return match($prioridade) {
        'baixa' => '<span class="badge bg-success">Baixa</span>',
        'media' => '<span class="badge bg-warning text-dark">Média</span>',
        'alta'  => '<span class="badge bg-danger">Alta</span>',
        default => '<span class="badge bg-secondary">—</span>',
    };
}

function formatMoeda(float $valor): string {
    return 'R$ ' . number_format($valor, 2, ',', '.');
}

function formatData(?string $data): string {
    if (!$data || $data === '0000-00-00') return '—';
    $d = DateTime::createFromFormat('Y-m-d', $data);
    return $d ? $d->format('d/m/Y') : '—';
}

function redirect(string $path): void {
    header('Location: ' . BASE_PATH . $path);
    exit;
}

function sanitize(string $value): string {
    return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
}

function csrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrf(): void {
    $token = $_POST['csrf_token'] ?? '';
    if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        http_response_code(403);
        die('Token de segurança inválido.');
    }
}

function flash(string $tipo, string $msg): void {
    $_SESSION['flash'] = ['tipo' => $tipo, 'msg' => $msg];
}

function showFlash(): void {
    if (!isset($_SESSION['flash'])) return;
    $f = $_SESSION['flash'];
    unset($_SESSION['flash']);
    echo '<div class="alert alert-' . sanitize($f['tipo']) . ' alert-dismissible fade show" role="alert">';
    echo sanitize($f['msg']);
    echo '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
    echo '</div>';
}
