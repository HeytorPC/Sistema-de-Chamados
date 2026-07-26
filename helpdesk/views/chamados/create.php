<?php $tituloPagina = 'Novo Chamado'; use App\Core\Csrf; ?>

<div class="card" style="max-width:760px;">
    <h3 class="card-title">Abrir novo chamado</h3>
    <form method="POST" action="<?= APP_URL ?>/chamados" enctype="multipart/form-data">
        <?= Csrf::field() ?>

        <div class="form-group">
            <label for="titulo">Título *</label>
            <input type="text" id="titulo" name="titulo" class="form-control" required maxlength="200" placeholder="Resuma o problema em poucas palavras">
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="setor_id">Setor responsável *</label>
                <select id="setor_id" name="setor_id" class="form-control" required onchange="carregarCategorias(this.value)">
                    <option value="">Selecione...</option>
                    <?php foreach ($setores as $s): ?>
                        <option value="<?= $s['id'] ?>"><?= e($s['nome']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="categoria_id">Categoria</label>
                <select id="categoria_id" name="categoria_id" class="form-control">
                    <option value="">Selecione o setor primeiro</option>
                </select>
            </div>
            <div class="form-group">
                <label for="prioridade">Prioridade *</label>
                <select id="prioridade" name="prioridade" class="form-control" required>
                    <option value="baixa">Baixa</option>
                    <option value="media" selected>Média</option>
                    <option value="alta">Alta</option>
                    <option value="urgente">Urgente</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label for="descricao">Descrição detalhada *</label>
            <textarea id="descricao" name="descricao" class="form-control" rows="6" required placeholder="Descreva o problema com o máximo de detalhes possível..."></textarea>
        </div>

        <div class="form-group">
            <label for="anexos">Anexos (imagens ou documentos)</label>
            <input type="file" id="anexos" name="anexos[]" class="form-control" multiple>
            <div class="form-hint">Formatos aceitos: jpg, png, gif, pdf, doc(x), xls(x), txt, zip — máx. 10MB por arquivo.</div>
        </div>

        <div class="flex gap-2">
            <button type="submit" class="btn btn-primary">Abrir Chamado</button>
            <a href="<?= APP_URL ?>/chamados" class="btn btn-outline">Cancelar</a>
        </div>
    </form>
</div>

<script>
function carregarCategorias(setorId) {
    const select = document.getElementById('categoria_id');
    select.innerHTML = '<option value="">Carregando...</option>';
    if (!setorId) {
        select.innerHTML = '<option value="">Selecione o setor primeiro</option>';
        return;
    }
    fetch('<?= APP_URL ?>/chamados/categorias-por-setor/' + setorId)
        .then(r => r.json())
        .then(data => {
            select.innerHTML = '<option value="">Nenhuma categoria específica</option>';
            data.categorias.forEach(cat => {
                const opt = document.createElement('option');
                opt.value = cat.id;
                opt.textContent = cat.nome;
                select.appendChild(opt);
            });
        });
}
</script>
