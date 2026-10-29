<?php

namespace App\Helpers;

use App\Core\Database;

class AuditHelper {
    public static function log(string $tabela, int $idRegistro, string $acao, string $detalhes): void {
        try {
            $db = Database::getConnection();
            $usuarioNome = $_SESSION['usuario_nome'] ?? 'Sistema';
            $idUsuario = $_SESSION['usuario_id'] ?? null;
            $detalhesCompletos = "[Usuário: {$usuarioNome}] " . $detalhes;

            $stmt = $db->prepare("
                INSERT INTO audit_logs (tabela_afetada, id_registro, acao, detalhes, id_usuario, usuario_nome, criado_em)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $tabela,
                $idRegistro,
                $acao,
                $detalhesCompletos,
                $idUsuario,
                $usuarioNome,
                date('Y-m-d H:i:s')
            ]);
        } catch (\Throwable $e) {
            error_log('Falha ao gravar audit_log: ' . $e->getMessage());
        }
    }
}

