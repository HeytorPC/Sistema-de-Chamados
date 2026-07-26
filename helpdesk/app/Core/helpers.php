<?php
/**
 * Funções auxiliares globais.
 */

/** Escapa saída para prevenir XSS. Usar SEMPRE ao imprimir dados do usuário nas views. */
function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function formatDate(?string $datetime, string $format = 'd/m/Y H:i'): string
{
    if (!$datetime) {
        return '-';
    }
    return (new DateTime($datetime))->format($format);
}

function badgeStatus(string $status): string
{
    $map = [
        'novo'         => ['label' => 'Novo',         'class' => 'badge-blue'],
        'em_andamento' => ['label' => 'Em andamento',  'class' => 'badge-indigo'],
        'em_espera'    => ['label' => 'Em espera',     'class' => 'badge-amber'],
        'fechado'      => ['label' => 'Fechado',       'class' => 'badge-green'],
        'cancelado'    => ['label' => 'Cancelado',     'class' => 'badge-red'],
    ];
    $item = $map[$status] ?? ['label' => $status, 'class' => 'badge-gray'];
    return '<span class="badge ' . $item['class'] . '">' . e($item['label']) . '</span>';
}

function badgePrioridade(string $prioridade): string
{
    $map = [
        'baixa'   => ['label' => 'Baixa',   'class' => 'badge-green'],
        'media'   => ['label' => 'Média',   'class' => 'badge-blue'],
        'alta'    => ['label' => 'Alta',    'class' => 'badge-amber'],
        'urgente' => ['label' => 'Urgente', 'class' => 'badge-red'],
    ];
    $item = $map[$prioridade] ?? ['label' => $prioridade, 'class' => 'badge-gray'];
    return '<span class="badge ' . $item['class'] . '">' . e($item['label']) . '</span>';
}

function old(string $key, $default = '')
{
    return e($_SESSION['old'][$key] ?? $default);
}

/** Trunca uma string com segurança, sem depender da extensão mbstring. */
function truncar(string $texto, int $tamanho = 200): string
{
    if (function_exists('mb_strlen') && function_exists('mb_substr')) {
        return mb_strlen($texto) > $tamanho ? mb_substr($texto, 0, $tamanho) . '...' : $texto;
    }
    return strlen($texto) > $tamanho ? substr($texto, 0, $tamanho) . '...' : $texto;
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function getFlashes(): array
{
    $flashes = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $flashes;
}
