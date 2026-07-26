<?php

namespace App\Core;

/**
 * Classe base para todos os Controllers.
 * Fornece renderização de views, redirecionamento e respostas JSON.
 */
abstract class Controller
{
    /**
     * Renderiza uma view dentro do layout padrão.
     */
    protected function view(string $view, array $data = []): void
    {
        extract($data);
        $viewFile = VIEWS_PATH . '/' . $view . '.php';

        if (!file_exists($viewFile)) {
            http_response_code(500);
            die("View não encontrada: {$view}");
        }

        require VIEWS_PATH . '/layouts/header.php';
        require $viewFile;
        require VIEWS_PATH . '/layouts/footer.php';
    }

    /**
     * Renderiza uma view sem o layout (ex: tela de login).
     */
    protected function viewOnly(string $view, array $data = []): void
    {
        extract($data);
        require VIEWS_PATH . '/' . $view . '.php';
    }

    protected function redirect(string $path): void
    {
        header('Location: ' . APP_URL . $path);
        exit;
    }

    protected function json(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    protected function input(string $key, $default = null)
    {
        $value = $_POST[$key] ?? $_GET[$key] ?? $default;
        return is_string($value) ? trim($value) : $value;
    }

    protected function requireLogin(): void
    {
        if (!Auth::check()) {
            $this->redirect('/login');
        }
    }

    protected function requireRole(array $roles): void
    {
        $this->requireLogin();
        if (!in_array(Auth::user()['perfil'], $roles, true)) {
            http_response_code(403);
            die('Acesso negado: você não tem permissão para acessar este recurso.');
        }
    }
}
