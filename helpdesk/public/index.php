<?php

/**
 * public/index.php
 * Front Controller - ponto de entrada único da aplicação.
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/Core/helpers.php';

use App\Controllers\AuthController;
use App\Controllers\CategoriaController;
use App\Controllers\ChamadoController;
use App\Controllers\DashboardController;
use App\Controllers\PerfilController;
use App\Controllers\RelatorioController;
use App\Controllers\SetorController;
use App\Controllers\UsuarioController;
use App\Core\Router;

$router = new Router();

// ----- Autenticação -----
$router->get('/', [AuthController::class, 'loginForm']);
$router->get('/login', [AuthController::class, 'loginForm']);
$router->post('/login', [AuthController::class, 'login']);
$router->get('/logout', [AuthController::class, 'logout']);

// ----- Dashboard -----
$router->get('/dashboard', [DashboardController::class, 'index']);

// ----- Chamados -----
$router->get('/chamados', [ChamadoController::class, 'index']);
$router->get('/chamados/novo', [ChamadoController::class, 'novoForm']);
$router->post('/chamados', [ChamadoController::class, 'store']);
$router->get('/chamados/categorias-por-setor/{setorId}', [ChamadoController::class, 'categoriasPorSetor']);
$router->get('/chamados/{id}', [ChamadoController::class, 'show']);
$router->post('/chamados/{id}/status', [ChamadoController::class, 'alterarStatus']);
$router->post('/chamados/{id}/atribuir', [ChamadoController::class, 'atribuir']);
$router->post('/chamados/{id}/encaminhar', [ChamadoController::class, 'encaminhar']);
$router->post('/chamados/{id}/comentarios', [ChamadoController::class, 'comentar']);

// ----- Usuários (admin) -----
$router->get('/usuarios', [UsuarioController::class, 'index']);
$router->get('/usuarios/novo', [UsuarioController::class, 'novoForm']);
$router->post('/usuarios', [UsuarioController::class, 'store']);
$router->get('/usuarios/{id}/editar', [UsuarioController::class, 'editarForm']);
$router->post('/usuarios/{id}', [UsuarioController::class, 'update']);
$router->post('/usuarios/{id}/excluir', [UsuarioController::class, 'destroy']);

// ----- Setores (admin) -----
$router->get('/setores', [SetorController::class, 'index']);
$router->get('/setores/novo', [SetorController::class, 'novoForm']);
$router->post('/setores', [SetorController::class, 'store']);
$router->get('/setores/{id}/editar', [SetorController::class, 'editarForm']);
$router->post('/setores/{id}', [SetorController::class, 'update']);
$router->post('/setores/{id}/excluir', [SetorController::class, 'destroy']);

// ----- Categorias (admin) -----
$router->get('/categorias', [CategoriaController::class, 'index']);
$router->post('/categorias', [CategoriaController::class, 'store']);
$router->post('/categorias/{id}', [CategoriaController::class, 'update']);
$router->post('/categorias/{id}/excluir', [CategoriaController::class, 'destroy']);

// ----- Perfil -----
$router->get('/perfil', [PerfilController::class, 'index']);
$router->post('/perfil', [PerfilController::class, 'update']);

// ----- Relatórios -----
$router->get('/relatorios', [RelatorioController::class, 'index']);
$router->get('/relatorios/excel', [RelatorioController::class, 'exportarExcel']);
$router->get('/relatorios/pdf', [RelatorioController::class, 'exportarPdf']);

// Calcula dinamicamente o "caminho base" da aplicação a partir do próprio
// script (SCRIPT_NAME), removendo-o da URI recebida. Isso faz o roteador
// funcionar tanto em http://localhost/dashboard (DocumentRoot = public/)
// quanto em http://localhost/qualquer-subpasta/public/dashboard (XAMPP).
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
$uri = $_SERVER['REQUEST_URI'] ?? '/';
$uri = strtok($uri, '?');

if ($scriptDir !== '/' && $scriptDir !== '' && str_starts_with($uri, $scriptDir)) {
    $uri = substr($uri, strlen($scriptDir));
}

if ($uri === '' || $uri === false) {
    $uri = '/';
}

$method = $_SERVER['REQUEST_METHOD'];

$router->dispatch($method, $uri);
