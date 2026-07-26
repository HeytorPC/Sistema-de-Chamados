<?php $tituloPagina = 'Categorias'; use App\Core\Csrf; ?>

<div class="card">
    <h3 class="card-title">Nova Categoria</h3>
    <form method="POST" action="<?= APP_URL ?>/categorias" class="form-row" style="align-items:end;">
        <?= Csrf::field() ?>
        <div class="form-group mb-0">
            <label>Nome *</label>
            <input type="text" name="nome" class="form-control" required>
        </div>
        <div class="form-group mb-0">
            <label>Setor</label>
            <select name="setor_id" class="form-control">
                <option value="">Nenhum (geral)</option>
                <?php foreach ($setores as $s): ?>
                    <option value="<?= $s['id'] ?>"><?= e($s['nome']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group mb-0">
            <button type="submit" class="btn btn-primary">Adicionar</button>
        </div>
    </form>
</div>

<div class="card">
    <h3 class="card-title">Categorias cadastradas</h3>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Nome</th><th>Setor</th><th>Status</th><th>Ações</th></tr></thead>
            <tbody>
                <?php foreach ($categorias as $c): ?>
                <tr>
                    <td><?= e($c['nome']) ?></td>
                    <td><?= e($c['setor_nome'] ?? '—') ?></td>
                    <td><?= $c['ativo'] ? '<span class="badge badge-green">Ativa</span>' : '<span class="badge badge-red">Inativa</span>' ?></td>
                    <td class="flex gap-2">
                        <?php if ($c['ativo']): ?>
                        <form method="POST" action="<?= APP_URL ?>/categorias/<?= $c['id'] ?>/excluir" onsubmit="return confirm('Desativar esta categoria?')">
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
