<?php $tituloPagina = 'Meu Perfil'; use App\Core\Csrf; ?>

<div class="card" style="max-width:600px;">
    <h3 class="card-title">Meus dados</h3>
    <form method="POST" action="<?= APP_URL ?>/perfil" enctype="multipart/form-data">
        <?= Csrf::field() ?>

        <div class="form-group">
            <label>Nome completo</label>
            <input type="text" name="nome" class="form-control" required value="<?= e($usuario['nome']) ?>">
        </div>

        <div class="form-group">
            <label>E-mail</label>
            <input type="email" class="form-control" value="<?= e($usuario['email']) ?>" disabled>
            <div class="form-hint">Para alterar o e-mail, contate um administrador.</div>
        </div>

        <div class="form-group">
            <label>Telefone</label>
            <input type="text" name="telefone" class="form-control" value="<?= e($usuario['telefone'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label>Foto de perfil</label>
            <input type="file" name="avatar" class="form-control">
        </div>

        <div class="form-group">
            <label>Nova senha (deixe em branco para manter a atual)</label>
            <input type="password" name="senha" class="form-control" minlength="6">
        </div>

        <button type="submit" class="btn btn-primary">Salvar alterações</button>
    </form>
</div>
