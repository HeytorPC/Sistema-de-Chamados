<?php
$tituloPagina = $chamado['codigo'];
use App\Core\Auth;
use App\Core\Csrf;
?>

<div style="display:grid; grid-template-columns: 2fr 1fr; gap:22px;" class="show-grid">
<style>@media (max-width: 1100px){ .show-grid{ grid-template-columns: 1fr !important; } }</style>

<div>
    <div class="card">
        <h3 class="card-title">
            <span><?= e($chamado['codigo']) ?> — <?= e($chamado['titulo']) ?></span>
            <?= badgeStatus($chamado['status']) ?>
        </h3>
        <p style="white-space:pre-wrap;"><?= e($chamado['descricao']) ?></p>

        <div class="flex gap-3" style="flex-wrap:wrap;">
            <?= badgePrioridade($chamado['prioridade']) ?>
            <span class="text-muted">Setor: <strong><?= e($chamado['setor_nome']) ?></strong></span>
            <span class="text-muted">Categoria: <strong><?= e($chamado['categoria_nome'] ?? 'Não definida') ?></strong></span>
            <span class="text-muted">Aberto em: <strong><?= formatDate($chamado['criado_em']) ?></strong></span>
            <?php if ($chamado['sla_previsto']): ?>
                <span class="text-muted">SLA previsto: <strong><?= formatDate($chamado['sla_previsto']) ?></strong></span>
            <?php endif; ?>
        </div>

        <?php if ($chamado['resolucao']): ?>
            <div class="alert alert-success mt-3">
                <strong>Como foi resolvido:</strong><br><?= nl2br(e($chamado['resolucao'])) ?>
            </div>
        <?php endif; ?>
    </div>

    <?php if (!empty($anexos)): ?>
    <div class="card">
        <h3 class="card-title">Anexos</h3>
        <div class="flex gap-2" style="flex-wrap:wrap;">
            <?php foreach ($anexos as $a): ?>
                <a href="<?= UPLOAD_URL ?>/<?= e($a['nome_armazenado']) ?>" target="_blank" class="btn btn-outline btn-sm">
                    📎 <?= e($a['nome_original']) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <div class="card">
        <h3 class="card-title">Comentários</h3>

        <?php foreach ($comentarios as $com): ?>
            <div class="comment <?= $com['interno'] ? 'interno' : '' ?>">
                <div class="avatar"><?= strtoupper(substr($com['usuario_nome'], 0, 1)) ?></div>
                <div class="comment-bubble">
                    <div class="flex justify-between">
                        <strong style="font-size:.85rem;"><?= e($com['usuario_nome']) ?></strong>
                        <span class="text-muted" style="font-size:.75rem;"><?= formatDate($com['criado_em']) ?></span>
                    </div>
                    <?php if ($com['interno']): ?><span class="badge badge-amber" style="margin:4px 0;">Nota interna</span><?php endif; ?>
                    <p style="margin:6px 0 0; white-space:pre-wrap;"><?= e($com['mensagem']) ?></p>
                </div>
            </div>
        <?php endforeach; ?>
        <?php if (empty($comentarios)): ?><p class="text-muted">Nenhum comentário ainda.</p><?php endif; ?>

        <form method="POST" action="<?= APP_URL ?>/chamados/<?= $chamado['id'] ?>/comentarios" enctype="multipart/form-data" class="mt-3">
            <?= Csrf::field() ?>
            <div class="form-group">
                <textarea name="mensagem" class="form-control" rows="3" required placeholder="Escreva um comentário..."></textarea>
            </div>
            <div class="form-group">
                <input type="file" name="anexos_comentario[]" class="form-control" multiple>
            </div>
            <?php if (Auth::isAtendente()): ?>
                <div class="form-group form-check">
                    <input type="checkbox" id="interno" name="interno" value="1">
                    <label for="interno" class="mb-0">Nota interna (não visível ao solicitante)</label>
                </div>
            <?php endif; ?>
            <button type="submit" class="btn btn-primary">Comentar</button>
        </form>
    </div>
</div>

<div>
    <?php if (Auth::isAtendente()): ?>
    <div class="card">
        <h3 class="card-title">Ações</h3>

        <form method="POST" action="<?= APP_URL ?>/chamados/<?= $chamado['id'] ?>/status" id="formStatus">
            <?= Csrf::field() ?>
            <div class="form-group">
                <label>Alterar status</label>
                <select name="status" class="form-control" onchange="toggleResolucao(this.value)">
                    <?php foreach (['novo'=>'Novo','em_andamento'=>'Em andamento','em_espera'=>'Em espera','fechado'=>'Fechado','cancelado'=>'Cancelado'] as $val=>$label): ?>
                        <option value="<?= $val ?>" <?= $chamado['status'] === $val ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group" id="grupoResolucao" style="display:none;">
                <label>Como foi resolvido? *</label>
                <textarea name="resolucao" class="form-control" rows="3" placeholder="Obrigatório para fechar o chamado"></textarea>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Atualizar Status</button>
        </form>

        <hr style="border-color:var(--borda); margin:18px 0;">

        <form method="POST" action="<?= APP_URL ?>/chamados/<?= $chamado['id'] ?>/atribuir">
            <?= Csrf::field() ?>
            <div class="form-group">
                <label>Atribuir responsável</label>
                <select name="responsavel_id" class="form-control" required>
                    <option value="">Selecione...</option>
                    <?php foreach ($atendentes as $a): ?>
                        <option value="<?= $a['id'] ?>" <?= (int)$chamado['responsavel_id'] === (int)$a['id'] ? 'selected' : '' ?>><?= e($a['nome']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-secondary btn-block">Atribuir</button>
        </form>

        <hr style="border-color:var(--borda); margin:18px 0;">

        <form method="POST" action="<?= APP_URL ?>/chamados/<?= $chamado['id'] ?>/encaminhar">
            <?= Csrf::field() ?>
            <div class="form-group">
                <label>Encaminhar para outro setor</label>
                <select name="setor_id" class="form-control" required>
                    <?php foreach ($setores as $s): ?>
                        <option value="<?= $s['id'] ?>" <?= (int)$chamado['setor_id'] === (int)$s['id'] ? 'selected' : '' ?>><?= e($s['nome']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <input type="text" name="observacao" class="form-control" placeholder="Observação (opcional)">
            </div>
            <button type="submit" class="btn btn-warning btn-block">Encaminhar</button>
        </form>
    </div>
    <?php endif; ?>

    <div class="card">
        <h3 class="card-title">Detalhes</h3>
        <p><strong>Solicitante:</strong><br><?= e($chamado['solicitante_nome']) ?><br><span class="text-muted"><?= e($chamado['solicitante_email']) ?></span></p>
        <p><strong>Responsável:</strong><br><?= e($chamado['responsavel_nome'] ?? 'Ainda não atribuído') ?></p>
    </div>

    <div class="card">
        <h3 class="card-title">Histórico</h3>
        <ul class="timeline">
            <?php foreach ($historico as $h): ?>
                <li>
                    <div><?= e($h['descricao']) ?></div>
                    <div class="meta"><?= e($h['usuario_nome']) ?> — <?= formatDate($h['criado_em']) ?></div>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>
</div>

<script>
function toggleResolucao(status) {
    document.getElementById('grupoResolucao').style.display = status === 'fechado' ? 'block' : 'none';
}
toggleResolucao(document.querySelector('#formStatus select[name="status"]')?.value);
</script>
