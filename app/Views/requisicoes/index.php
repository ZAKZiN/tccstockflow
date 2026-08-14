<?php include __DIR__ . '/../layouts/header.php'; ?>

            <header class="topbar animate-fade-up">
                <h2>Gestão de Requisições</h2>
                <div style="display: flex; gap: 1rem;">
                    <button class="btn btn-primary" onclick="window.print()" style="background-color: var(--bg-card); color: var(--text-primary); border: 1px solid var(--border-subtle);"><i class="ph ph-printer"></i> Imprimir Relatório</button>
                    <a href="?export=csv" class="btn" style="background-color: var(--bg-card); color: var(--text-primary); border: 1px solid var(--border-subtle);"><i class="ph ph-file-csv"></i> Exportar CSV</a>
                    <a href="/requisicoes/nova" class="btn btn-primary animate-fade-up delay-100"><i class="ph ph-plus"></i> Nova Requisição</a>
                </div>
            </header>

            <div class="glass-panel animate-fade-up delay-200" style="padding: 1.5rem;">
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Cód.</th>
                                <th>Material</th>
                                <th>Qtd</th>
                                <th>Solicitante</th>
                                <th>Setor</th>
                                <th>Status</th>
                                <th>Data</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($requisicoes)): ?>
                                <tr>
                                    <td colspan="8" style="text-align: center; padding: 3rem; color: var(--text-secondary);">
                                        Nenhuma requisição encontrada.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach($requisicoes as $req): ?>
                                    <tr>
                                        <td>#<?= str_pad($req['id_requisicao'], 4, '0', STR_PAD_LEFT) ?></td>
                                        <td style="font-weight: 500; color: var(--text-primary);"><?= htmlspecialchars($req['material']) ?></td>
                                        <td><?= $req['quantidade'] ?></td>
                                        <td><?= htmlspecialchars($req['solicitante']) ?></td>
                                        <td><?= htmlspecialchars($req['nome_setor']) ?></td>
                                        <td>
                                            <?php 
                                                $s = $req['status'];
                                                $badgeClass = 'badge-warning';
                                                if(strpos($s, 'Aprovado') !== false || strpos($s, 'Efetuada') !== false || strpos($s, 'Despachado') !== false) $badgeClass = 'badge-success';
                                                if($s == 'Recusado') $badgeClass = 'badge-danger';
                                            ?>
                                            <span class="badge <?= $badgeClass ?>"><?= $s ?></span>
                                        </td>
                                        <td style="color: var(--text-secondary);"><?= date('d/m/Y', strtotime($req['data_solicitacao'])) ?></td>
                                        <td>
                                            <?php if($req['status'] === 'Pendente Coordenador' && $_SESSION['usuario_nivel'] === 'Coordenador'): ?>
                                                <a href="/requisicoes/aprovar/<?= $req['id_requisicao'] ?>" class="btn" style="background-color: var(--success-bg); color: var(--success); padding: 0.25rem 0.5rem; font-size: 0.75rem;">Aprovar</a>
                                                <a href="/requisicoes/recusar/<?= $req['id_requisicao'] ?>" class="btn" style="background-color: var(--danger-bg); color: var(--danger); padding: 0.25rem 0.5rem; font-size: 0.75rem;">Recusar</a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
    <script>
        // Data para a impressão
        const dateStr = new Date().toLocaleString('pt-BR');
        document.querySelector('.topbar h2').setAttribute('data-date', dateStr);
    </script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
