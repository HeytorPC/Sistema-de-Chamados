<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Csrf;
use App\Models\Setor;

class SetorController extends Controller
{
    private Setor $model;

    public function __construct()
    {
        $this->model = new Setor();
    }

    public function index(): void
    {
        $this->requireRole(['administrador']);
        $this->view('setores/index', ['setores' => $this->model->comContagemChamados()]);
    }

    public function novoForm(): void
    {
        $this->requireRole(['administrador']);
        $this->view('setores/form', ['setor' => null]);
    }

    public function editarForm(string $id): void
    {
        $this->requireRole(['administrador']);
        $setor = $this->model->find((int) $id);
        if (!$setor) {
            http_response_code(404);
            die('Setor não encontrado.');
        }
        $this->view('setores/form', ['setor' => $setor]);
    }

    public function store(): void
    {
        $this->requireRole(['administrador']);
        Csrf::validateRequest();

        $nome = $this->input('nome');
        if (!$nome) {
            flash('error', 'Informe o nome do setor.');
            $this->redirect('/setores/novo');
        }

        $this->model->create([
            'nome' => $nome,
            'descricao' => $this->input('descricao'),
            'sla_horas' => (int) $this->input('sla_horas', 24),
            'ativo' => 1,
        ]);

        flash('success', 'Setor cadastrado com sucesso.');
        $this->redirect('/setores');
    }

    public function update(string $id): void
    {
        $this->requireRole(['administrador']);
        Csrf::validateRequest();

        $this->model->update((int) $id, [
            'nome' => $this->input('nome'),
            'descricao' => $this->input('descricao'),
            'sla_horas' => (int) $this->input('sla_horas', 24),
            'ativo' => $this->input('ativo') ? 1 : 0,
        ]);

        flash('success', 'Setor atualizado com sucesso.');
        $this->redirect('/setores');
    }

    public function destroy(string $id): void
    {
        $this->requireRole(['administrador']);
        Csrf::validateRequest();
        $this->model->update((int) $id, ['ativo' => 0]);
        flash('success', 'Setor desativado.');
        $this->redirect('/setores');
    }
}
