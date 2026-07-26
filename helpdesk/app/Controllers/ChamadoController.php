<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Upload;
use App\Models\Anexo;
use App\Models\Categoria;
use App\Models\Chamado;
use App\Models\Comentario;
use App\Models\Historico;
use App\Models\Setor;
use App\Models\Usuario;

class ChamadoController extends Controller
{
    private Chamado $chamadoModel;
    private Historico $historicoModel;

    public function __construct()
    {
        $this->chamadoModel = new Chamado();
        $this->historicoModel = new Historico();
    }

    /** GET /chamados - lista com filtros e paginação */
    public function index(): void
    {
        $this->requireLogin();

        $filtros = [
            'status'       => $this->input('status'),
            'prioridade'   => $this->input('prioridade'),
            'setor_id'     => $this->input('setor_id'),
            'categoria_id' => $this->input('categoria_id'),
            'busca'        => $this->input('busca'),
        ];

        // Colaboradores só enxergam os próprios chamados
        if (!Auth::isAtendente()) {
            $filtros['solicitante_id'] = Auth::id();
        }

        $pagina = (int) $this->input('pagina', 1);
        $resultado = $this->chamadoModel->listarComFiltros(array_filter($filtros), max($pagina, 1));

        $setorModel = new Setor();
        $categoriaModel = new Categoria();

        $this->view('chamados/index', [
            'resultado' => $resultado,
            'filtros'   => $filtros,
            'setores'   => $setorModel->ativos(),
            'categorias' => $categoriaModel->ativas(),
        ]);
    }

    /** GET /chamados/novo */
    public function novoForm(): void
    {
        $this->requireLogin();
        $setorModel = new Setor();
        $this->view('chamados/create', [
            'setores' => $setorModel->ativos(),
        ]);
    }

    /** GET /chamados/categorias-por-setor/{id} - AJAX */
    public function categoriasPorSetor(string $setorId): void
    {
        $this->requireLogin();
        $categoriaModel = new Categoria();
        $this->json(['categorias' => $categoriaModel->porSetor((int) $setorId)]);
    }

    /** POST /chamados */
    public function store(): void
    {
        $this->requireLogin();
        Csrf::validateRequest();

        $titulo = $this->input('titulo');
        $descricao = $this->input('descricao');
        $setorId = (int) $this->input('setor_id');
        $categoriaId = $this->input('categoria_id') ?: null;
        $prioridade = $this->input('prioridade', 'media');

        if (!$titulo || !$descricao || !$setorId) {
            flash('error', 'Preencha todos os campos obrigatórios.');
            $this->redirect('/chamados/novo');
        }

        $setorModel = new Setor();
        $setor = $setorModel->find($setorId);
        $slaPrevisto = date('Y-m-d H:i:s', strtotime('+' . ($setor['sla_horas'] ?? 24) . ' hours'));

        $codigo = $this->chamadoModel->gerarCodigo();

        $chamadoId = $this->chamadoModel->create([
            'codigo'         => $codigo,
            'titulo'         => $titulo,
            'descricao'      => $descricao,
            'categoria_id'   => $categoriaId,
            'prioridade'     => $prioridade,
            'status'         => 'novo',
            'solicitante_id' => Auth::id(),
            'setor_id'       => $setorId,
            'sla_previsto'   => $slaPrevisto,
        ]);

        $this->historicoModel->registrar($chamadoId, Auth::id(), 'criado', "Chamado {$codigo} aberto por " . Auth::user()['nome']);

        // Upload de anexos iniciais (opcional)
        $this->processarAnexos($chamadoId, 'anexos');

        flash('success', "Chamado {$codigo} aberto com sucesso!");
        $this->redirect('/chamados/' . $chamadoId);
    }

    /** GET /chamados/{id} */
    public function show(string $id): void
    {
        $this->requireLogin();
        $id = (int) $id;

        $chamado = $this->chamadoModel->buscarCompleto($id);
        if (!$chamado) {
            http_response_code(404);
            die('Chamado não encontrado.');
        }

        // Colaborador só pode ver os próprios chamados
        if (!Auth::isAtendente() && (int) $chamado['solicitante_id'] !== Auth::id()) {
            http_response_code(403);
            die('Você não tem permissão para visualizar este chamado.');
        }

        $comentarioModel = new Comentario();
        $anexoModel = new Anexo();
        $setorModel = new Setor();
        $usuarioModel = new Usuario();

        $this->view('chamados/show', [
            'chamado'     => $chamado,
            'comentarios' => $comentarioModel->porChamado($id, Auth::isAtendente()),
            'anexos'      => $anexoModel->porChamado($id),
            'historico'   => $this->historicoModel->porChamado($id),
            'setores'     => $setorModel->ativos(),
            'atendentes'  => $usuarioModel->atendentesPorSetor((int) $chamado['setor_id']),
        ]);
    }

    /** POST /chamados/{id}/status - alterar status */
    public function alterarStatus(string $id): void
    {
        $this->requireRole(['administrador', 'atendente']);
        Csrf::validateRequest();
        $id = (int) $id;

        $novoStatus = $this->input('status');
        $resolucao = $this->input('resolucao');

        $statusValidos = ['novo', 'em_andamento', 'em_espera', 'fechado', 'cancelado'];
        if (!in_array($novoStatus, $statusValidos, true)) {
            flash('error', 'Status inválido.');
            $this->redirect('/chamados/' . $id);
        }

        // Regra de negócio: fechamento exige o campo "Como foi resolvido"
        if ($novoStatus === 'fechado' && empty($resolucao)) {
            flash('error', 'Para fechar o chamado é obrigatório informar "Como foi resolvido".');
            $this->redirect('/chamados/' . $id);
        }

        $dadosUpdate = ['status' => $novoStatus];
        if ($novoStatus === 'fechado') {
            $dadosUpdate['resolucao'] = $resolucao;
            $dadosUpdate['fechado_em'] = date('Y-m-d H:i:s');
        }

        $this->chamadoModel->update($id, $dadosUpdate);

        $labelStatus = [
            'novo' => 'Novo', 'em_andamento' => 'Em andamento', 'em_espera' => 'Em espera',
            'fechado' => 'Fechado', 'cancelado' => 'Cancelado',
        ][$novoStatus];

        $this->historicoModel->registrar($id, Auth::id(), 'status_alterado', "Status alterado para: {$labelStatus}");
        if ($novoStatus === 'fechado') {
            $this->historicoModel->registrar($id, Auth::id(), 'fechado', 'Chamado fechado. Resolução: ' . truncar($resolucao, 200));
        }

        flash('success', 'Status atualizado com sucesso.');
        $this->redirect('/chamados/' . $id);
    }

    /** POST /chamados/{id}/atribuir - definir responsável */
    public function atribuir(string $id): void
    {
        $this->requireRole(['administrador', 'atendente']);
        Csrf::validateRequest();
        $id = (int) $id;

        $responsavelId = (int) $this->input('responsavel_id');
        $usuarioModel = new Usuario();
        $responsavel = $usuarioModel->find($responsavelId);

        if (!$responsavel) {
            flash('error', 'Responsável inválido.');
            $this->redirect('/chamados/' . $id);
        }

        $this->chamadoModel->update($id, ['responsavel_id' => $responsavelId]);
        $this->historicoModel->registrar($id, Auth::id(), 'atribuido', "Chamado atribuído a {$responsavel['nome']}");

        flash('success', 'Chamado atribuído com sucesso.');
        $this->redirect('/chamados/' . $id);
    }

    /** POST /chamados/{id}/encaminhar - mover para outro setor */
    public function encaminhar(string $id): void
    {
        $this->requireRole(['administrador', 'atendente']);
        Csrf::validateRequest();
        $id = (int) $id;

        $novoSetorId = (int) $this->input('setor_id');
        $observacao = $this->input('observacao', '');

        $setorModel = new Setor();
        $setor = $setorModel->find($novoSetorId);
        if (!$setor) {
            flash('error', 'Setor inválido.');
            $this->redirect('/chamados/' . $id);
        }

        // Ao encaminhar, remove o responsável atual (novo setor deve assumir) e recalcula o SLA
        $slaPrevisto = date('Y-m-d H:i:s', strtotime('+' . $setor['sla_horas'] . ' hours'));
        $this->chamadoModel->update($id, [
            'setor_id'       => $novoSetorId,
            'responsavel_id' => null,
            'sla_previsto'   => $slaPrevisto,
            'status'         => 'em_andamento',
        ]);

        $descricao = "Chamado encaminhado para o setor: {$setor['nome']}";
        if ($observacao) {
            $descricao .= '. Observação: ' . $observacao;
        }
        $this->historicoModel->registrar($id, Auth::id(), 'encaminhado', $descricao);

        flash('success', 'Chamado encaminhado com sucesso.');
        $this->redirect('/chamados/' . $id);
    }

    /** POST /chamados/{id}/comentarios */
    public function comentar(string $id): void
    {
        $this->requireLogin();
        Csrf::validateRequest();
        $id = (int) $id;

        $mensagem = $this->input('mensagem');
        $interno = Auth::isAtendente() && $this->input('interno') ? 1 : 0;

        if (!$mensagem) {
            flash('error', 'Digite uma mensagem para comentar.');
            $this->redirect('/chamados/' . $id);
        }

        $comentarioModel = new Comentario();
        $comentarioId = $comentarioModel->create([
            'chamado_id' => $id,
            'usuario_id' => Auth::id(),
            'mensagem'   => $mensagem,
            'interno'    => $interno,
        ]);

        $this->processarAnexos($id, 'anexos_comentario', $comentarioId);

        $this->historicoModel->registrar($id, Auth::id(), 'comentario', Auth::user()['nome'] . ' adicionou um comentário' . ($interno ? ' (interno)' : ''));

        flash('success', 'Comentário adicionado.');
        $this->redirect('/chamados/' . $id);
    }

    /**
     * Processa upload de um ou mais anexos vinculados a um chamado (e opcionalmente a um comentário).
     */
    private function processarAnexos(int $chamadoId, string $campo, ?int $comentarioId = null): void
    {
        if (empty($_FILES[$campo]['name'][0]) && empty($_FILES[$campo]['name'])) {
            return;
        }

        $anexoModel = new Anexo();
        $arquivos = Upload::normalizarMultiplos($_FILES[$campo]);

        foreach ($arquivos as $arquivo) {
            try {
                $dados = Upload::processar($arquivo, 'chamados/' . $chamadoId);
                $anexoModel->create([
                    'chamado_id'      => $chamadoId,
                    'comentario_id'   => $comentarioId,
                    'usuario_id'      => Auth::id(),
                    'nome_original'   => $dados['nome_original'],
                    'nome_armazenado' => $dados['nome_armazenado'],
                    'tipo_mime'       => $dados['tipo_mime'],
                    'tamanho'         => $dados['tamanho'],
                ]);
                $this->historicoModel->registrar($chamadoId, Auth::id(), 'anexo', 'Anexo adicionado: ' . $dados['nome_original']);
            } catch (\RuntimeException $e) {
                flash('error', $e->getMessage());
            }
        }
    }
}
