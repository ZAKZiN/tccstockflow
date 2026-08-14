<?php include __DIR__ . '/../layouts/header.php'; ?>

            <header class="topbar animate-fade-up">
                <h2>Fiado / Contas a Receber</h2>
            </header>

            <div class="glass-panel animate-fade-up delay-200" style="padding: 1.5rem;">
                
                <div style="display: flex; gap: 1rem; margin-bottom: 1.5rem; max-width: 400px;">
                    <div class="input-icon-wrapper" style="flex:1;">
                        <i class="ph ph-magnifying-glass"></i>
                        <input type="text" id="searchInput" placeholder="Buscar por Cliente ou Venda...">
                    </div>
                </div>

                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Cód. Conta</th>
                                <th>Venda Ref.</th>
                                <th>Cliente</th>
                                <th>Valor Total</th>
                                <th>Status</th>
                                <th>Data da Venda</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($contas)): ?>
                                <tr>
                                    <td colspan="7" style="text-align: center; padding: 3rem; color: var(--text-secondary);">
                                        Nenhuma conta a receber encontrada.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach($contas as $c): ?>
                                    <tr class="fiado-row">
                                        <td>#<?= str_pad($c['id_conta'], 4, '0', STR_PAD_LEFT) ?></td>
                                        <td class="fiado-venda"><a href="#">Venda #<?= $c['id_venda'] ?></a></td>
                                        <td class="fiado-cliente" style="font-weight: 500; color: var(--text-primary);"><?= htmlspecialchars($c['cliente']) ?></td>
                                        <td style="color: var(--danger); font-weight: 600;">R$ <?= number_format($c['valor_total'], 2, ',', '.') ?></td>
                                        <td>
                                            <?php if($c['status'] === 'Pendente'): ?>
                                                <span class="badge badge-warning">Pendente</span>
                                            <?php else: ?>
                                                <span class="badge badge-success">Pago</span>
                                            <?php endif; ?>
                                        </td>
                                        <td style="color: var(--text-secondary);"><?= date('d/m/Y H:i', strtotime($c['criado_em'])) ?></td>
                                        <td>
                                            <?php if($c['status'] === 'Pendente'): ?>
                                                <a href="/fiado/pagar/<?= $c['id_conta'] ?>" class="btn" style="background-color: var(--success-bg); color: var(--success); padding: 0.25rem 0.5rem; font-size: 0.85rem;" onclick="confirmarBaixa(event, this.href, '<?= htmlspecialchars($c['cliente']) ?>')">
                                                    <i class="ph ph-check-circle"></i> Dar Baixa
                                                </a>
                                            <?php else: ?>
                                                - 
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
        // Filtro da tabela
        document.getElementById('searchInput').addEventListener('input', function(e) {
            const term = e.target.value.toLowerCase();
            document.querySelectorAll('.fiado-row').forEach(row => {
                const cliente = row.querySelector('.fiado-cliente').innerText.toLowerCase();
                const venda = row.querySelector('.fiado-venda').innerText.toLowerCase();
                if (cliente.includes(term) || venda.includes(term)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });

        // Confirmação com SweetAlert
        function confirmarBaixa(event, url, cliente) {
            event.preventDefault();
            Swal.fire({
                title: 'Confirmar Baixa',
                text: `Deseja dar baixa na dívida de ${cliente}?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#10b981',
                cancelButtonColor: '#ef4444',
                confirmButtonText: 'Sim, Dar Baixa',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = url;
                }
            });
        }
    </script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
