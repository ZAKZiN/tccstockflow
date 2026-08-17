<?php

namespace App\Core;

use PDO;
use PDOException;

class Database {
    private static $instance = null;

    public static function getConnection() {
        if (self::$instance === null) {
            try {
                $dbPath = __DIR__ . '/../../database.sqlite';
                $isNew = !file_exists($dbPath);

                self::$instance = new PDO("sqlite:" . $dbPath);
                
                // Configurando o PDO para lançar exceções em caso de erro
                self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                // Configurando o retorno padrão como array associativo
                self::$instance->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                
                // Habilitar Foreign Keys no SQLite
                self::$instance->exec('PRAGMA foreign_keys = ON;');

                // Auto-Inicialização: Se o banco acabou de ser criado, roda o SQL
                if ($isNew) {
                    $sql = file_get_contents(__DIR__ . '/../../database.sql');
                    self::$instance->exec($sql);
                }
                
            } catch(PDOException $exception) {
                echo "Erro de conexão com o Banco de Dados SQLite: " . $exception->getMessage();
                exit;
            }
        }

        return self::$instance;
    }
}
