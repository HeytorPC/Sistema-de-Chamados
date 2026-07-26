<?php $tituloPagina = 'Setores'; use App\Core\Csrf; ?>

<div class="card">
    <h3 class="card-title">
        Setores
        <a href="<?= APP_URL ?>/setores/novo" class="btn btn-primary btn-sm">+ Novo Setor</a>
    </h3>
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr><th>Nome</th><th>SLA (horas)</th><th>Usuários</th><th>Chamados Abertos</th><th>Status</th><th>Ações</th></tr>
            </thead>
            <tbody>
                <?php foreach ($setores as $s): ?>
                <tr>
                    <td><strong><?= e($s['nome']) ?></strong><br><span class="text-muted" style="font-size:.8rem;"><?= e($s['descricao']) ?></span></td>
                    <td><?= (int)$s['sla_horas'] ?>h</td>
                    <td><?= (int)$s['total_usuarios'] ?></td>
                    <td><?= (int)$s['chamados_abertos'] ?></td>
                    <td><?= $s['ativo'] ? '<span class="badge badge-green">Ativo</span>' : '<span class="badge badge-red">Inativo</span>' ?></td>
                    <td class="flex gap-2">
                        <a href="<?= APP_URL ?>/setores/<?= $s['id'] ?>/editar" class="btn btn-outline btn-sm">Editar</a>
                        <?php if ($s['ativo']): ?>
                        <form method="POST" action="<?= APP_URL ?>/setores/<?= $s['id'] ?>/excluir" onsubmit="return confirm('Desativar este setor?')">
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
