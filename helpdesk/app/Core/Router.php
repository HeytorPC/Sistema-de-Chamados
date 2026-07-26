<?php

namespace App\Core;

/**
 * Router simples baseado em array de rotas (método + URI) -> [Controller, ação].
 * Suporta parâmetros dinâmicos no formato {id}.
 */
class Router
{
    private array $routes = [];

    public function get(string $uri, array $action): void
    {
        $this->addRoute('GET', $uri, $action);
    }

    public function post(string $uri, array $action): void
    {
        $this->addRoute('POST', $uri, $action);
    }

    private function addRoute(string $method, string $uri, array $action): void
    {
        $this->routes[] = compact('method', 'uri', 'action');
    }

    public function dispatch(string $method, string $uri): void
    {
        $uri = strtok($uri, '?'); // remove query string
        $uri = rtrim($uri, '/');
        if ($uri === '') {
            $uri = '/';
        }

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            $pattern = preg_replace('/\{[a-zA-Z_]+\}/', '([^/]+)', $route['uri']);
            $pattern = '#^' . $pattern . '$#';

            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches);
                [$controllerClass, $action] = $route['action'];
                $controller = new $controllerClass();
                call_user_func_array([$controller, $action], $matches);
                return;
            }
        }

        http_response_code(404);
        require VIEWS_PATH . '/errors/404.php';
    }
}
