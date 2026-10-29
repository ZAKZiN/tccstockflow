<?php

namespace App\Core;

use PDO;
use PDOException;

class Database {
    private static $instance = null;

    public static function getConnection() {
        if (self::$instance === null) {
            try {
                $dbPath = getenv('DB_SQLITE_PATH') ?: (__DIR__ . '/../../database.sqlite');
                $isNew = !file_exists($dbPath) || filesize($dbPath) === 0;

                self::$instance = new PDO("sqlite:" . $dbPath);
                self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$instance->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

                // Integridade referencial e prevenção contra bloqueios de concorrência (SQLITE_BUSY)
                self::$instance->exec('PRAGMA foreign_keys = ON;');
                self::$instance->exec('PRAGMA journal_mode = WAL;');
                self::$instance->exec('PRAGMA busy_timeout = 5000;');

                if ($isNew) {
                    $sql = file_get_contents(__DIR__ . '/../../database.sql');
                    self::$instance->exec($sql);
                } else {
                    self::ensureSchemaUpgrades(self::$instance);
                }
            } catch (PDOException $exception) {
                error_log("Erro crítico de conexão SQLite: " . $exception->getMessage());
                http_response_code(500);
                die("Erro interno ao conectar ao banco de dados. Contate o administrador.");
            }
        }

        return self::$instance;
    }

    /**
     * Garante compatibilidade automática caso já exista um arquivo database.sqlite anterior
     */
    private static function ensureSchemaUpgrades(PDO $db): void {
        $db->exec("
            CREATE TABLE IF NOT EXISTS login_attempts (
                id_attempt INTEGER PRIMARY KEY AUTOINCREMENT,
                ip_address VARCHAR(45) NOT NULL,
                login VARCHAR(60) NOT NULL,
                tentado_em INTEGER NOT NULL
            );
            CREATE INDEX IF NOT EXISTS idx_login_attempts_ip_login ON login_attempts(ip_address, login);
        ");

        $colsCaixaMov = array_column($db->query("PRAGMA table_info(caixa_movimentacoes)")->fetchAll(), 'name');
        if (!in_array('metodo_pagamento', $colsCaixaMov, true)) {
            $db->exec("ALTER TABLE caixa_movimentacoes ADD COLUMN metodo_pagamento VARCHAR(50) DEFAULT 'Dinheiro';");
        }

        $colsAudit = array_column($db->query("PRAGMA table_info(audit_logs)")->fetchAll(), 'name');
        if (!in_array('id_usuario', $colsAudit, true)) {
            $db->exec("ALTER TABLE audit_logs ADD COLUMN id_usuario INT NULL;");
        }
        if (!in_array('usuario_nome', $colsAudit, true)) {
            $db->exec("ALTER TABLE audit_logs ADD COLUMN usuario_nome VARCHAR(120) NULL;");
        }
    }
}
