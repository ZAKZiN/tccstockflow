<?php

namespace App\Core;

use PDO;
use PDOException;

class Database {
    private static $instance = null;

    public static function getConnection() {
        if (self::$instance === null) {
            try {
                $host = $_ENV['DB_HOST'] ?? 'localhost';
                $port = $_ENV['DB_PORT'] ?? '5432';
                $db = $_ENV['DB_NAME'] ?? 'postgres';
                $user = $_ENV['DB_USER'] ?? 'postgres';
                $pass = $_ENV['DB_PASS'] ?? '';
                $sslmode = $_ENV['DB_SSLMODE'] ?? 'prefer';

                $dsn = "pgsql:host=$host;port=$port;dbname=$db;sslmode=$sslmode";
                
                self::$instance = new PDO($dsn, $user, $pass);
                self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$instance->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            } catch(PDOException $exception) {
                echo "Erro de conexão com o Banco de Dados PostgreSQL: " . $exception->getMessage();
                exit;
            }
        }

        return self::$instance;
    }
}
