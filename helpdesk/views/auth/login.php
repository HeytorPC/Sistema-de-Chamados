<?php use App\Core\Csrf; ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/style.css">
</head>
<body>
<div class="login-page">
    <div class="login-box">
        <div class="logo-icon">HD</div>
        <h1><?= APP_NAME ?></h1>
        <p class="subtitle">Acesse sua conta para abrir e acompanhar chamados</p>

        <?php foreach (getFlashes() as $flash): ?>
            <div class="alert alert-<?= $flash['type'] === 'error' ? 'error' : 'success' ?>"><?= e($flash['message']) ?></div>
        <?php endforeach; ?>

        <form method="POST" action="<?= APP_URL ?>/login">
            <?= Csrf::field() ?>
            <div class="form-group">
                <label for="email">E-mail</label>
                <input type="email" class="form-control" id="email" name="email" required autofocus placeholder="voce@empresa.com">
            </div>
            <div class="form-group">
                <label for="senha">Senha</label>
                <input type="password" class="form-control" id="senha" name="senha" required placeholder="••••••••">
            </div>
            <button type="submit" class="btn btn-primary btn-block">Entrar</button>
        </form>

        <p class="text-muted mt-3" style="text-align:center; font-size:.78rem;">
            Acesso restrito a colaboradores autorizados.
        </p>
    </div>
</div>
</body>
</html>
