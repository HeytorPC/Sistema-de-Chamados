<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Upload;
use App\Models\Usuario;

class PerfilController extends Controller
{
    private Usuario $model;

    public function __construct()
    {
        $this->model = new Usuario();
    }

    public function index(): void
    {
        $this->requireLogin();
        $usuario = $this->model->find(Auth::id());
        $this->view('perfil/index', ['usuario' => $usuario]);
    }

    public function update(): void
    {
        $this->requireLogin();
        Csrf::validateRequest();

        $id = Auth::id();
        $dados = [
            'nome'     => $this->input('nome'),
            'telefone' => $this->input('telefone'),
        ];

        $novaSenha = $this->input('senha');
        if ($novaSenha) {
            if (strlen($novaSenha) < 6) {
                flash('error', 'A nova senha deve ter ao menos 6 caracteres.');
                $this->redirect('/perfil');
            }
            $dados['senha_hash'] = password_hash($novaSenha, PASSWORD_BCRYPT);
        }

        if (!empty($_FILES['avatar']['name'])) {
            try {
                $arquivo = Upload::processar($_FILES['avatar'], 'avatares');
                $dados['avatar'] = $arquivo['nome_armazenado'];
            } catch (\RuntimeException $e) {
                flash('error', $e->getMessage());
                $this->redirect('/perfil');
            }
        }

        $this->model->update($id, $dados);
        $usuarioAtualizado = $this->model->find($id);
        Auth::refresh($usuarioAtualizado);

        flash('success', 'Perfil atualizado com sucesso.');
        $this->redirect('/perfil');
    }
}
