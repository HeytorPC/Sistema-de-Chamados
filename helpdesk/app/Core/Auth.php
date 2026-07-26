<?php

namespace App\Core;

use App\Models\Usuario;

/**
 * Classe responsável por autenticação e controle de sessão do usuário.
 */
class Auth
{
    public static function attempt(string $email, string $senha): bool
    {
        $usuarioModel = new Usuario();
        $usuario = $usuarioModel->findByEmail($email);

        if (!$usuario || !$usuario['ativo']) {
            return false;
        }

        if (!password_verify($senha, $usuario['senha_hash'])) {
            return false;
        }

        // Regenera o ID de sessão para prevenir session fixation
        session_regenerate_id(true);

        $_SESSION['usuario'] = [
            'id'       => $usuario['id'],
            'nome'     => $usuario['nome'],
            'email'    => $usuario['email'],
            'perfil'   => $usuario['perfil'],
            'setor_id' => $usuario['setor_id'],
            'avatar'   => $usuario['avatar'],
        ];

        $usuarioModel->update($usuario['id'], ['ultimo_login' => date('Y-m-d H:i:s')]);

        return true;
    }

    public static function check(): bool
    {
        return isset($_SESSION['usuario']);
    }

    public static function user(): ?array
    {
        return $_SESSION['usuario'] ?? null;
    }

    public static function id(): ?int
    {
        return $_SESSION['usuario']['id'] ?? null;
    }

    public static function isAdmin(): bool
    {
        return self::check() && $_SESSION['usuario']['perfil'] === 'administrador';
    }

    public static function isAtendente(): bool
    {
        return self::check() && in_array($_SESSION['usuario']['perfil'], ['administrador', 'atendente'], true);
    }

    public static function logout(): void
    {
        $_SESSION = [];
        session_destroy();
    }

    /** Atualiza os dados de sessão do usuário (ex: após editar perfil) */
    public static function refresh(array $usuario): void
    {
        $_SESSION['usuario'] = array_merge($_SESSION['usuario'], [
            'nome'     => $usuario['nome'],
            'email'    => $usuario['email'],
            'avatar'   => $usuario['avatar'] ?? $_SESSION['usuario']['avatar'],
            'setor_id' => $usuario['setor_id'],
        ]);
    }
}
