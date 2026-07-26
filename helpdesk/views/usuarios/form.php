<?php
$tituloPagina = $usuario ? 'Editar Usuário' : 'Novo Usuário';
use App\Core\Csrf;
$acaoUrl = $usuario ? APP_URL . '/usuarios/' . $usuario['id'] : APP_URL . '/usuarios';
?>

<div class="card" style="max-width:640px;">
    <h3 class="card-title"><?= $tituloPagina ?></h3>
    <form method="POST" action="<?= $acaoUrl ?>">
        <?= Csrf::field() ?>

        <div class="form-group">
            <label>Nome completo *</label>
            <input type="text" name="nome" class="form-control" required value="<?= e($usuario['nome'] ?? '') ?>">
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>E-mail *</label>
                <input type="email" name="email" class="form-control" required value="<?= e($usuario['email'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label><?= $usuario ? 'Nova senha (deixe em branco para manter)' : 'Senha *' ?></label>
                <input type="password" name="senha" class="form-control" <?= $usuario ? '' : 'required' ?>>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Perfil *</label>
                <select name="perfil" class="form-control" required>
                    <?php foreach (['colaborador'=>'Colaborador','atendente'=>'Atendente','administrador'=>'Administrador'] as $val=>$label): ?>
                        <option value="<?= $val ?>" <?= ($usuario['perfil'] ?? '') === $val ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Setor</label>
                <select name="setor_id" class="form-control">
                    <option value="">Nenhum</option>
                    <?php foreach ($setores as $s): ?>
                        <option value="<?= $s['id'] ?>" <?= (string)($usuario['setor_id'] ?? '') === (string)$s['id'] ? 'selected' : '' ?>><?= e($s['nome']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <?php if ($usuario): ?>
        <div class="form-group form-check">
            <input type="checkbox" id="ativo" name="ativo" value="1" <?= $usuario['ativo'] ? 'checked' : '' ?>>
            <label for="ativo" class="mb-0">Usuário ativo</label>
        </div>
        <?php endif; ?>

        <div class="flex gap-2">
            <button type="submit" class="btn btn-primary">Salvar</button>
            <a href="<?= APP_URL ?>/usuarios" class="btn btn-outline">Cancelar</a>
        </div>
    </form>
</div>
