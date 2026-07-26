<?php
use App\Core\Auth;
$usuario = Auth::user();
$rotaAtual = strtok($_SERVER['REQUEST_URI'], '?');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?></title>
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/style.css">
    <link rel="icon" type="image/png" href="assets/img/favicon.png">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.4/chart.umd.min.js"></script>
</head>
<body>
<div class="app-layout">

    <?php require VIEWS_PATH . '/layouts/sidebar.php'; ?>

    <div class="main-content">
        <div class="topbar">
            <div class="flex items-center gap-2">
                <button class="menu-toggle" onclick="document.querySelector('.sidebar').classList.toggle('open')">&#9776;</button>
                <h2><?= e($tituloPagina ?? 'Help Desk') ?></h2>
            </div>
            <div class="user-chip">
                <div class="avatar"><?= strtoupper(substr($usuario['nome'] ?? '?', 0, 1)) ?></div>
                <div>
                    <div style="font-weight:600; font-size:.88rem;"><?= e($usuario['nome'] ?? '') ?></div>
                    <div class="text-muted" style="font-size:.75rem; text-transform:capitalize;"><?= e($usuario['perfil'] ?? '') ?></div>
                </div>
            </div>
        </div>

        <div class="page-content">
            <?php foreach (getFlashes() as $flash): ?>
                <div class="alert alert-<?= $flash['type'] === 'error' ? 'error' : 'success' ?>"><?= e($flash['message']) ?></div>
            <?php endforeach; ?>
