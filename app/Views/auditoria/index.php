<?php include __DIR__ . '/../layouts/header.php'; ?>

<header class="topbar animate-fade-up">
    <h2><i class="ph ph-shield-check"></i> Auditoria e Logs do Sistema</h2>
</header>

<div class="glass-panel animate-fade-up" style="padding: 1.5rem;">
    <p style="color: var(--text-secondary); margin-bottom: 1.25rem; font-size: 0.9rem;">
        Registro cronológico de eventos críticos de segurança, autenticação, movimentações financeiras e gatilhos automáticos de banco de dados.
    </p>
    
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tabela Afetada</th>
                    <th>Registro ID</th>
                    <th>Ação / Evento</th>
                    <th>Detalhes</th>
                    <th>Data e Hora</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($logs)): ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 2.5rem; color: var(--text-secondary);">Nenhum log de auditoria encontrado.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach($logs as $log): ?>
                        <tr>
                            <td>#<?= str_pad((string)$log['id_audit'], 4, '0', STR_PAD_LEFT) ?></td>
                            <td><span class="badge badge-primary"><?= htmlspecialchars($log['tabela_afetada']) ?></span></td>
                            <td><?= htmlspecialchars((string)$log['id_registro']) ?></td>
                            <td><strong style="color: var(--text-primary);"><?= htmlspecialchars($log['acao']) ?></strong></td>
                            <td style="max-width: 380px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="<?= htmlspecialchars($log['detalhes'] ?? '') ?>">
                                <?= htmlspecialchars($log['detalhes'] ?? '') ?>
                            </td>
                            <td style="color: var(--text-secondary);"><?= date('d/m/Y H:i:s', strtotime($log['criado_em'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
