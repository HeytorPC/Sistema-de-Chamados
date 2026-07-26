<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Database;
use App\Models\Categoria;
use App\Models\Setor;

class CategoriaController extends Controller
{
    private Categoria $model;

    public function __construct()
    {
        $this->model = new Categoria();
    }

    public function index(): void
    {
        $this->requireRole(['administrador']);
        $setorModel = new Setor();
        $sql = "SELECT c.*, s.nome AS setor_nome FROM categorias c LEFT JOIN setores s ON s.id = c.setor_id ORDER BY s.nome, c.nome";
        $categorias = Database::getConnection()->query($sql)->fetchAll();
        $this->view('categorias/index', ['categorias' => $categorias, 'setores' => $setorModel->ativos()]);
    }

    public function store(): void
    {
        $this->requireRole(['administrador']);
        Csrf::validateRequest();

        $this->model->create([
            'nome' => $this->input('nome'),
            'setor_id' => $this->input('setor_id') ?: null,
            'ativo' => 1,
        ]);

        flash('success', 'Categoria cadastrada com sucesso.');
        $this->redirect('/categorias');
    }

    public function update(string $id): void
    {
        $this->requireRole(['administrador']);
        Csrf::validateRequest();

        $this->model->update((int) $id, [
            'nome' => $this->input('nome'),
            'setor_id' => $this->input('setor_id') ?: null,
            'ativo' => $this->input('ativo') ? 1 : 0,
        ]);

        flash('success', 'Categoria atualizada com sucesso.');
        $this->redirect('/categorias');
    }

    public function destroy(string $id): void
    {
        $this->requireRole(['administrador']);
        Csrf::validateRequest();
        $this->model->update((int) $id, ['ativo' => 0]);
        flash('success', 'Categoria desativada.');
        $this->redirect('/categorias');
    }
}
