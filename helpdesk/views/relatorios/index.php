<?php $tituloPagina = 'Relatórios'; ?>

<div class="card">
    <form method="GET" action="<?= APP_URL ?>/relatorios" class="form-row" style="align-items:end;">
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
        <div class="form-group mb-0"><button type="submit" class="btn btn-primary">Filtrar</button></div>
    </form>
</div>

<div class="card">
    <h3 class="card-title">
        <?= count($chamados) ?> chamado(s)
        <span class="flex gap-2">
            <a class="btn btn-success btn-sm" href="<?= APP_URL ?>/relatorios/excel?<?= http_build_query($filtros) ?>">⬇ Exportar Excel (CSV)</a>
            <a class="btn btn-secondary btn-sm" href="<?= APP_URL ?>/relatorios/pdf?<?= http_build_query($filtros) ?>" target="_blank">⬇ Exportar PDF</a>
        </span>
    </h3>
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr><th>Código</th><th>Título</th><th>Setor</th><th>Prioridade</th><th>Status</th><th>Solicitante</th><th>Criado em</th><th>Fechado em</th></tr>
            </thead>
            <tbody>
                <?php foreach ($chamados as $c): ?>
                <tr>
                    <td><?= e($c['codigo']) ?></td>
                    <td><?= e($c['titulo']) ?></td>
                    <td><?= e($c['setor_nome']) ?></td>
                    <td><?= badgePrioridade($c['prioridade']) ?></td>
                    <td><?= badgeStatus($c['status']) ?></td>
                    <td><?= e($c['solicitante_nome']) ?></td>
                    <td><?= formatDate($c['criado_em']) ?></td>
                    <td><?= formatDate($c['fechado_em']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
