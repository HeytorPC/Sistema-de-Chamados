<?php
use App\Core\Auth;
$rotaAtual = strtok($_SERVER['REQUEST_URI'], '?');
function navAtiva(string $prefixo, string $rotaAtual): string {
    return str_starts_with($rotaAtual, $prefixo) ? 'active' : '';
}
?>
<aside class="sidebar">
    <div class="sidebar-header">
        <div class="logo-icon">HD</div>
        <h1><?= APP_NAME ?></h1>
    </div>

    <nav class="sidebar-nav">
        <a href="<?= APP_URL ?>/dashboard" class="<?= navAtiva('/dashboard', $rotaAtual) ?>">📊 Dashboard</a>
        <a href="<?= APP_URL ?>/chamados" class="<?= navAtiva('/chamados', $rotaAtual) ?>">🎫 Chamados</a>
        <a href="<?= APP_URL ?>/chamados/novo" class="<?= navAtiva('/chamados/novo', $rotaAtual) ?>">➕ Novo Chamado</a>

        <?php if (Auth::isAtendente()): ?>
            <a href="<?= APP_URL ?>/relatorios" class="<?= navAtiva('/relatorios', $rotaAtual) ?>">📈 Relatórios</a>
        <?php endif; ?>

        <?php if (Auth::isAdmin()): ?>
            <div class="nav-section">Administração</div>
            <a href="<?= APP_URL ?>/usuarios" class="<?= navAtiva('/usuarios', $rotaAtual) ?>">👥 Usuários</a>
            <a href="<?= APP_URL ?>/setores" class="<?= navAtiva('/setores', $rotaAtual) ?>">🏢 Setores</a>
            <a href="<?= APP_URL ?>/categorias" class="<?= navAtiva('/categorias', $rotaAtual) ?>">🏷️ Categorias</a>
        <?php endif; ?>

        <div class="nav-section">Conta</div>
        <a href="<?= APP_URL ?>/perfil" class="<?= navAtiva('/perfil', $rotaAtual) ?>">⚙️ Meu Perfil</a>
        <a href="<?= APP_URL ?>/logout">🚪 Sair</a>
    </nav>

    <div class="sidebar-footer text-muted" style="font-size:.75rem;">
        &copy; <?= date('Y') ?> <?= APP_NAME ?>
    </div>
</aside>
