<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="ph ph-shield-check"></i> Auditoria e Logs do Sistema</h1>
</div>

<div class="card glass-panel animate-fade-up">
    <div class="card-body">
        <p class="text-muted">Abaixo estão registrados todos os eventos automáticos e manuais de alto nível (criação, edição e exclusão) capturados pelos gatilhos de segurança do banco de dados.</p>
        
        <div class="table-responsive mt-4">
            <table class="table table-hover align-middle">
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
                            <td colspan="6" class="text-center py-4 text-muted">Nenhum log de auditoria encontrado.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($logs as $log): ?>
                            <tr>
                                <td>#<?= str_pad($log['id_audit'], 4, '0', STR_PAD_LEFT) ?></td>
                                <td><span class="badge" style="background-color: var(--primary);"><?= htmlspecialchars($log['tabela_afetada']) ?></span></td>
                                <td><?= htmlspecialchars($log['id_registro']) ?></td>
                                <td><strong style="color: var(--text-primary);"><?= htmlspecialchars($log['acao']) ?></strong></td>
                                <td style="max-width: 300px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="<?= htmlspecialchars($log['detalhes']) ?>">
                                    <?= htmlspecialchars($log['detalhes']) ?>
                                </td>
                                <td><?= date('d/m/Y H:i:s', strtotime($log['criado_em'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
