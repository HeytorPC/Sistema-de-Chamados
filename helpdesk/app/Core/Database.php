<?php

namespace App\Core;

use PDO;
use PDOException;

/**
 * Classe responsável pela conexão única (Singleton) com o banco via PDO.
 */
class Database
{
    private static ?PDO $instance = null;

    private function __construct()
    {
        // Construtor privado: impede instância direta (Singleton)
    }

    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            try {
                $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
                self::$instance = new PDO($dsn, DB_USER, DB_PASS, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE  => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES    => false,
                ]);
            } catch (PDOException $e) {
                error_log('Erro de conexão com o banco: ' . $e->getMessage());
                die('Erro ao conectar ao banco de dados. Verifique as configurações em config/config.php');
            }
        }

        return self::$instance;
    }
}
