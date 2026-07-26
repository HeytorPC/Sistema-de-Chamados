<?php

namespace App\Core;

/**
 * Proteção contra Cross-Site Request Forgery (CSRF).
 */
class Csrf
{
    public static function token(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function field(): string
    {
        return '<input type="hidden" name="csrf_token" value="' . self::token() . '">';
    }

    public static function verify(?string $token): bool
    {
        if (empty($token) || empty($_SESSION['csrf_token'])) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }

    /** Aborta a requisição caso o token seja inválido. Usar no início de ações POST. */
    public static function validateRequest(): void
    {
        $token = $_POST['csrf_token'] ?? '';
        if (!self::verify($token)) {
            http_response_code(419);
            die('Token de segurança inválido ou expirado (CSRF). Recarregue a página e tente novamente.');
        }
    }
}
