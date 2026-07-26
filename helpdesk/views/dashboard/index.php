<?php $tituloPagina = 'Dashboard'; ?>

<div class="stats-grid">
    <div class="stat-card">
        <div class="valor"><?= (int)($stats['total'] ?? 0) ?></div>
        <div class="rotulo">Total de Chamados</div>
    </div>
    <div class="stat-card azul">
        <div class="valor"><?= (int)($stats['hoje'] ?? 0) ?></div>
        <div class="rotulo">Abertos Hoje</div>
    </div>
    <div class="stat-card amarelo">
        <div class="valor"><?= (int)($stats['em_andamento'] ?? 0) ?></div>
        <div class="rotulo">Em Andamento</div>
    </div>
    <div class="stat-card" style="border-left-color:#F59E0B">
        <div class="valor"><?= (int)($stats['em_espera'] ?? 0) ?></div>
        <div class="rotulo">Em Espera</div>
    </div>
    <div class="stat-card verde">
        <div class="valor"><?= (int)($stats['fechados'] ?? 0) ?></div>
        <div class="rotulo">Fechados</div>
    </div>
    <div class="stat-card vermelho">
        <div class="valor"><?= (int)($stats['urgentes'] ?? 0) ?></div>
        <div class="rotulo">Urgentes em Aberto</div>
    </div>
    <div class="stat-card">
        <div class="valor"><?= $stats['tempo_medio_atendimento'] ?>h</div>
        <div class="rotulo">Tempo Médio de Atendimento</div>
    </div>
    <div class="stat-card">
        <div class="valor"><?= $stats['tempo_medio_resolucao'] ?>h</div>
        <div class="rotulo">Tempo Médio de Resolução</div>
    </div>
</div>

<div class="charts-grid">
    <div class="card">
        <h3 class="card-title">Chamados por Dia (últimos 14 dias)</h3>
        <canvas id="chartPorDia" height="200"></canvas>
    </div>
    <div class="card">
        <h3 class="card-title">Chamados por Setor</h3>
        <canvas id="chartPorSetor" height="200"></canvas>
    </div>
    <div class="card">
        <h3 class="card-title">Chamados por Prioridade</h3>
        <canvas id="chartPorPrioridade" height="200"></canvas>
    </div>
    <div class="card">
        <h3 class="card-title">Chamados por Status</h3>
        <canvas id="chartPorStatus" height="200"></canvas>
    </div>
</div>

<div class="card">
    <h3 class="card-title">
        Chamados Recentes
        <a href="<?= APP_URL ?>/chamados" class="btn btn-outline btn-sm">Ver todos</a>
    </h3>
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr><th>Código</th><th>Título</th><th>Setor</th><th>Prioridade</th><th>Status</th><th>Criado em</th></tr>
            </thead>
            <tbody>
                <?php foreach ($recentes as $c): ?>
                <tr onclick="window.location='<?= APP_URL ?>/chamados/<?= $c['id'] ?>'" style="cursor:pointer">
                    <td><?= e($c['codigo']) ?></td>
                    <td><?= e($c['titulo']) ?></td>
                    <td><?= e($c['setor_nome']) ?></td>
                    <td><?= badgePrioridade($c['prioridade']) ?></td>
                    <td><?= badgeStatus($c['status']) ?></td>
                    <td><?= formatDate($c['criado_em']) ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($recentes)): ?>
                    <tr><td colspan="6" class="text-muted">Nenhum chamado encontrado.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
const paletaCores = ['#4F46E5', '#3B82F6', '#10B981', '#F59E0B', '#EF4444'];

new Chart(document.getElementById('chartPorDia'), {
    type: 'line',
    data: {
        labels: <?= json_encode(array_map(fn($d) => date('d/m', strtotime($d['dia'])), $porDia)) ?>,
        datasets: [{
            label: 'Chamados',
            data: <?= json_encode(array_map(fn($d) => (int)$d['total'], $porDia)) ?>,
            borderColor: '#4F46E5',
            backgroundColor: 'rgba(79,70,229,.2)',
            tension: .35,
            fill: true,
        }]
    },
    options: { plugins: { legend: { display: false } }, scales: { x: { ticks: { color: '#9CA3AF' } }, y: { ticks: { color: '#9CA3AF' }, beginAtZero: true } } }
});

new Chart(document.getElementById('chartPorSetor'), {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_column($porSetor, 'setor')) ?>,
        datasets: [{ label: 'Chamados', data: <?= json_encode(array_map('intval', array_column($porSetor, 'total'))) ?>, backgroundColor: '#3B82F6' }]
    },
    options: { plugins: { legend: { display: false } }, scales: { x: { ticks: { color: '#9CA3AF' } }, y: { ticks: { color: '#9CA3AF' }, beginAtZero: true } } }
});

new Chart(document.getElementById('chartPorPrioridade'), {
    type: 'doughnut',
    data: {
        labels: <?= json_encode(array_column($porPrioridade, 'prioridade')) ?>,
        datasets: [{ data: <?= json_encode(array_map('intval', array_column($porPrioridade, 'total'))) ?>, backgroundColor: paletaCores }]
    },
    options: { plugins: { legend: { labels: { color: '#E5E7EB' } } } }
});

new Chart(document.getElementById('chartPorStatus'), {
    type: 'pie',
    data: {
        labels: <?= json_encode(array_column($porStatus, 'status')) ?>,
        datasets: [{ data: <?= json_encode(array_map('intval', array_column($porStatus, 'total'))) ?>, backgroundColor: paletaCores }]
    },
    options: { plugins: { legend: { labels: { color: '#E5E7EB' } } } }
});
</script>
