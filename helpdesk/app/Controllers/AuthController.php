<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;

class AuthController extends Controller
{
    public function loginForm(): void
    {
        if (Auth::check()) {
            $this->redirect('/dashboard');
        }
        $this->viewOnly('auth/login');
    }

    public function login(): void
    {
        Csrf::validateRequest();

        $email = $this->input('email');
        $senha = $this->input('senha');

        if (!$email || !$senha) {
            flash('error', 'Informe e-mail e senha.');
            $this->redirect('/login');
        }

        if (Auth::attempt($email, $senha)) {
            $this->redirect('/dashboard');
        }

        flash('error', 'E-mail ou senha inválidos, ou usuário inativo.');
        $this->redirect('/login');
    }

    public function logout(): void
    {
        Auth::logout();
        $this->redirect('/login');
    }
}
