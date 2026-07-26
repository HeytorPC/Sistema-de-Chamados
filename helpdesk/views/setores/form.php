<?php
$tituloPagina = $setor ? 'Editar Setor' : 'Novo Setor';
use App\Core\Csrf;
$acaoUrl = $setor ? APP_URL . '/setores/' . $setor['id'] : APP_URL . '/setores';
?>

<div class="card" style="max-width:600px;">
    <h3 class="card-title"><?= $tituloPagina ?></h3>
    <form method="POST" action="<?= $acaoUrl ?>">
        <?= Csrf::field() ?>
        <div class="form-group">
            <label>Nome do setor *</label>
            <input type="text" name="nome" class="form-control" required value="<?= e($setor['nome'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label>Descrição</label>
            <input type="text" name="descricao" class="form-control" value="<?= e($setor['descricao'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label>SLA padrão (horas para atendimento) *</label>
            <input type="number" name="sla_horas" class="form-control" min="1" required value="<?= e((string)($setor['sla_horas'] ?? 24)) ?>">
            <div class="form-hint">Prazo utilizado para calcular a data limite de atendimento dos chamados deste setor.</div>
        </div>
        <?php if ($setor): ?>
        <div class="form-group form-check">
            <input type="checkbox" id="ativo" name="ativo" value="1" <?= $setor['ativo'] ? 'checked' : '' ?>>
            <label for="ativo" class="mb-0">Setor ativo</label>
        </div>
        <?php endif; ?>
        <div class="flex gap-2">
            <button type="submit" class="btn btn-primary">Salvar</button>
            <a href="<?= APP_URL ?>/setores" class="btn btn-outline">Cancelar</a>
        </div>
    </form>
</div>
