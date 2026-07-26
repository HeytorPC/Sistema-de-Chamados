<?php $tituloPagina = 'Usuários'; use App\Core\Csrf; ?>

<div class="card">
    <h3 class="card-title">
        Usuários cadastrados
        <a href="<?= APP_URL ?>/usuarios/novo" class="btn btn-primary btn-sm">+ Novo Usuário</a>
    </h3>
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr><th>Nome</th><th>E-mail</th><th>Perfil</th><th>Setor</th><th>Status</th><th>Ações</th></tr>
            </thead>
            <tbody>
                <?php foreach ($usuarios as $u): ?>
                <tr>
                    <td><?= e($u['nome']) ?></td>
                    <td><?= e($u['email']) ?></td>
                    <td style="text-transform:capitalize;"><?= e($u['perfil']) ?></td>
                    <td><?= e($u['setor_nome'] ?? '—') ?></td>
                    <td><?= $u['ativo'] ? '<span class="badge badge-green">Ativo</span>' : '<span class="badge badge-red">Inativo</span>' ?></td>
                    <td class="flex gap-2">
                        <a href="<?= APP_URL ?>/usuarios/<?= $u['id'] ?>/editar" class="btn btn-outline btn-sm">Editar</a>
                        <?php if ($u['ativo']): ?>
                        <form method="POST" action="<?= APP_URL ?>/usuarios/<?= $u['id'] ?>/excluir" onsubmit="return confirm('Desativar este usuário?')">
                            <?= Csrf::field() ?>
                            <button type="submit" class="btn btn-danger btn-sm">Desativar</button>
                        </form>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
