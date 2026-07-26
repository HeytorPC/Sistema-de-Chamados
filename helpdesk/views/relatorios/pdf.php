<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Relatório de Chamados</title>
<style>
    body { font-family: Arial, sans-serif; color: #111; font-size: 12px; }
    h1 { font-size: 18px; }
    table { width: 100%; border-collapse: collapse; margin-top: 14px; }
    th, td { border: 1px solid #999; padding: 6px 8px; text-align: left; }
    th { background: #eee; }
    .meta { color: #555; font-size: 11px; margin-bottom: 10px; }
    @media print { .no-print { display: none; } }
</style>
</head>
<body>
    <button class="no-print" onclick="window.print()">Imprimir / Salvar como PDF</button>
    <h1>Relatório de Chamados - <?= APP_NAME ?></h1>
    <p class="meta">Gerado em <?= date('d/m/Y H:i') ?> — Total: <?= count($chamados) ?> chamado(s)</p>

    <table>
        <thead>
            <tr><th>Código</th><th>Título</th><th>Setor</th><th>Prioridade</th><th>Status</th><th>Solicitante</th><th>Criado em</th><th>Fechado em</th></tr>
        </thead>
        <tbody>
            <?php foreach ($chamados as $c): ?>
            <tr>
                <td><?= e($c['codigo']) ?></td>
                <td><?= e($c['titulo']) ?></td>
                <td><?= e($c['setor_nome']) ?></td>
                <td><?= e($c['prioridade']) ?></td>
                <td><?= e($c['status']) ?></td>
                <td><?= e($c['solicitante_nome']) ?></td>
                <td><?= formatDate($c['criado_em']) ?></td>
                <td><?= formatDate($c['fechado_em']) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <script>window.onload = () => window.print();</script>
</body>
</html>
