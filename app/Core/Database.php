<?php

namespace App\Core;

use PDO;
use PDOException;

class Database {
    private static $instance = null;

    public static function getConnection() {
        if (self::$instance === null) {
            $host = $_ENV['DB_HOST'] ?? '127.0.0.1';
            $port = $_ENV['DB_PORT'] ?? '5432';
            $db_name = $_ENV['DB_NAME'] ?? 'postgres';
            $username = $_ENV['DB_USER'] ?? 'postgres';
            $password = $_ENV['DB_PASS'] ?? '';
            $sslmode = $_ENV['DB_SSLMODE'] ?? 'require';

            try {
                // Configuração da string de conexão para SQLite Local
                $dsn = 'sqlite:' . __DIR__ . '/../../database.sqlite';
                self::$instance = new PDO($dsn);
                
                // Configurando o PDO para lançar exceções em caso de erro
                self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                // Configurando o retorno padrão como array associativo
                self::$instance->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                
            } catch(PDOException $exception) {
                echo "Erro de conexão: " . $exception->getMessage();
                exit;
            }
        }

        return self::$instance;
    }
}
