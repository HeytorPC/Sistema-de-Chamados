<?php $tituloPagina = 'Chamados'; ?>

<div class="card">
    <form method="GET" action="<?= APP_URL ?>/chamados" class="form-row" style="align-items:end;">
        <div class="form-group mb-0">
            <label>Buscar</label>
            <input type="text" name="busca" class="form-control" placeholder="Código, título ou descrição" value="<?= e($filtros['busca'] ?? '') ?>">
        </div>
        <div class="form-group mb-0">
            <label>Status</label>
            <select name="status" class="form-control">
                <option value="">Todos</option>
                <?php foreach (['novo'=>'Novo','em_andamento'=>'Em andamento','em_espera'=>'Em espera','fechado'=>'Fechado','cancelado'=>'Cancelado'] as $val=>$label): ?>
                    <option value="<?= $val ?>" <?= ($filtros['status'] ?? '') === $val ? 'selected' : '' ?>><?= $label ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group mb-0">
            <label>Prioridade</label>
            <select name="prioridade" class="form-control">
                <option value="">Todas</option>
                <?php foreach (['baixa'=>'Baixa','media'=>'Média','alta'=>'Alta','urgente'=>'Urgente'] as $val=>$label): ?>
                    <option value="<?= $val ?>" <?= ($filtros['prioridade'] ?? '') === $val ? 'selected' : '' ?>><?= $label ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group mb-0">
            <label>Setor</label>
            <select name="setor_id" class="form-control">
                <option value="">Todos</option>
                <?php foreach ($setores as $s): ?>
                    <option value="<?= $s['id'] ?>" <?= (string)($filtros['setor_id'] ?? '') === (string)$s['id'] ? 'selected' : '' ?>><?= e($s['nome']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group mb-0">
            <button type="submit" class="btn btn-primary">Filtrar</button>
        </div>
    </form>
</div>

<div class="card">
    <h3 class="card-title">
        <?= $resultado['total'] ?> chamado(s) encontrado(s)
        <a href="<?= APP_URL ?>/chamados/novo" class="btn btn-primary btn-sm">+ Novo Chamado</a>
    </h3>
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Código</th><th>Título</th><th>Setor</th><th>Solicitante</th>
                    <th>Responsável</th><th>Prioridade</th><th>Status</th><th>Criado em</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($resultado['dados'] as $c): ?>
                <tr onclick="window.location='<?= APP_URL ?>/chamados/<?= $c['id'] ?>'" style="cursor:pointer">
                    <td><strong><?= e($c['codigo']) ?></strong></td>
                    <td><?= e($c['titulo']) ?></td>
                    <td><?= e($c['setor_nome']) ?></td>
                    <td><?= e($c['solicitante_nome']) ?></td>
                    <td><?= e($c['responsavel_nome'] ?? '—') ?></td>
                    <td><?= badgePrioridade($c['prioridade']) ?></td>
                    <td><?= badgeStatus($c['status']) ?></td>
                    <td><?= formatDate($c['criado_em']) ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($resultado['dados'])): ?>
                    <tr><td colspan="8" class="text-muted">Nenhum chamado encontrado com os filtros aplicados.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($resultado['paginas'] > 1): ?>
        <div class="flex gap-2 mt-3">
            <?php for ($p = 1; $p <= $resultado['paginas']; $p++): ?>
                <?php
                    $query = array_filter(array_merge($filtros, ['pagina' => $p]));
                    $url = APP_URL . '/chamados?' . http_build_query($query);
                ?>
                <a href="<?= $url ?>" class="btn btn-sm <?= $p === $resultado['pagina_atual'] ? 'btn-primary' : 'btn-outline' ?>"><?= $p ?></a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
</div>
