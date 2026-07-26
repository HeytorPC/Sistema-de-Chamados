<?php
/**
 * config/config.php
 * Configurações globais da aplicação.
 */

// ----- Sessão -----
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_httponly' => true,
        'cookie_samesite' => 'Lax',
    ]);
}

// ----- Ambiente -----
define('APP_NAME', 'Help Desk Corporativo');
define('APP_ENV', 'production'); // development | production
define('APP_URL', 'http://localhost/helpdesk/public');

// ----- Banco de dados -----
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'helpdesk');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// ----- Caminhos -----
define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');
define('VIEWS_PATH', BASE_PATH . '/views');
define('UPLOAD_PATH', BASE_PATH . '/public/assets/uploads');
define('UPLOAD_URL', APP_URL . '/assets/uploads');

// ----- Upload -----
define('UPLOAD_MAX_SIZE', 10 * 1024 * 1024); // 10MB
define('UPLOAD_ALLOWED_EXT', ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt', 'zip']);

// ----- Exibição de erros -----
if (APP_ENV === 'development') {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}

date_default_timezone_set('America/Sao_Paulo');

// ----- Autoload simples das classes do app (PSR-4 like) -----
spl_autoload_register(function ($class) {
    // Remove namespace raiz "App\"
    $prefix = 'App\\';
    if (strpos($class, $prefix) === 0) {
        $relative = substr($class, strlen($prefix));
        $file = APP_PATH . '/' . str_replace('\\', '/', $relative) . '.php';
        if (file_exists($file)) {
            require_once $file;
        }
    }
});
