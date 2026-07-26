<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Csrf;
use App\Models\Setor;
use App\Models\Usuario;

class UsuarioController extends Controller
{
    private Usuario $model;

    public function __construct()
    {
        $this->model = new Usuario();
    }

    public function index(): void
    {
        $this->requireRole(['administrador']);
        $this->view('usuarios/index', ['usuarios' => $this->model->allComSetor()]);
    }

    public function novoForm(): void
    {
        $this->requireRole(['administrador']);
        $setorModel = new Setor();
        $this->view('usuarios/form', ['usuario' => null, 'setores' => $setorModel->ativos()]);
    }

    public function editarForm(string $id): void
    {
        $this->requireRole(['administrador']);
        $usuario = $this->model->find((int) $id);
        if (!$usuario) {
            http_response_code(404);
            die('Usuário não encontrado.');
        }
        $setorModel = new Setor();
        $this->view('usuarios/form', ['usuario' => $usuario, 'setores' => $setorModel->ativos()]);
    }

    public function store(): void
    {
        $this->requireRole(['administrador']);
        Csrf::validateRequest();

        $nome = $this->input('nome');
        $email = $this->input('email');
        $senha = $this->input('senha');
        $perfil = $this->input('perfil', 'colaborador');
        $setorId = $this->input('setor_id') ?: null;

        if (!$nome || !$email || !$senha) {
            flash('error', 'Preencha nome, e-mail e senha.');
            $this->redirect('/usuarios/novo');
        }

        if ($this->model->emailExiste($email)) {
            flash('error', 'Este e-mail já está cadastrado.');
            $this->redirect('/usuarios/novo');
        }

        $this->model->create([
            'nome'       => $nome,
            'email'      => $email,
            'senha_hash' => password_hash($senha, PASSWORD_BCRYPT),
            'perfil'     => $perfil,
            'setor_id'   => $setorId,
            'ativo'      => 1,
        ]);

        flash('success', 'Usuário cadastrado com sucesso.');
        $this->redirect('/usuarios');
    }

    public function update(string $id): void
    {
        $this->requireRole(['administrador']);
        Csrf::validateRequest();
        $id = (int) $id;

        $nome = $this->input('nome');
        $email = $this->input('email');
        $perfil = $this->input('perfil');
        $setorId = $this->input('setor_id') ?: null;
        $ativo = $this->input('ativo') ? 1 : 0;

        if ($this->model->emailExiste($email, $id)) {
            flash('error', 'Este e-mail já está em uso por outro usuário.');
            $this->redirect('/usuarios/' . $id . '/editar');
        }

        $dados = [
            'nome' => $nome, 'email' => $email, 'perfil' => $perfil,
            'setor_id' => $setorId, 'ativo' => $ativo,
        ];

        $novaSenha = $this->input('senha');
        if ($novaSenha) {
            $dados['senha_hash'] = password_hash($novaSenha, PASSWORD_BCRYPT);
        }

        $this->model->update($id, $dados);

        flash('success', 'Usuário atualizado com sucesso.');
        $this->redirect('/usuarios');
    }

    public function destroy(string $id): void
    {
        $this->requireRole(['administrador']);
        Csrf::validateRequest();
        // Soft delete: apenas inativa, para preservar integridade histórica
        $this->model->update((int) $id, ['ativo' => 0]);
        flash('success', 'Usuário desativado.');
        $this->redirect('/usuarios');
    }
}
