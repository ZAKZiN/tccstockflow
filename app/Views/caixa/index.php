<?php include __DIR__ . '/../layouts/header.php'; ?>

            <header class="topbar animate-fade-up">
                <h2>Controle de Caixa</h2>
            </header>

            <?php if(!$caixaAberto): ?>
                <div class="glass-panel animate-fade-up delay-200" style="padding: 3rem; text-align: center; max-width: 500px; margin: 0 auto;">
                    <i class="ph ph-lock-key" style="font-size: 4rem; color: var(--text-secondary); margin-bottom: 1rem;"></i>
                    <h3 style="margin-bottom: 1rem;">Caixa Fechado</h3>
                    <p style="color: var(--text-secondary); margin-bottom: 2rem;">Você precisa abrir o caixa para iniciar as vendas deste turno.</p>
                    
                    <div class="input-group" style="text-align: left;">
                        <label>Fundo de Troco Inicial (R$)</label>
                        <input type="number" step="0.01" id="saldoInicial" value="0.00" class="form-control">
                    </div>
                    <button class="btn btn-primary" onclick="abrirCaixa()" style="width: 100%;"><i class="ph ph-check-circle"></i> Abrir Caixa</button>
                </div>
            <?php else: ?>
                <div class="stats-grid animate-fade-up delay-100">
                    <div class="stat-card glass-panel" style="border-left: 4px solid var(--accent-color);">
                        <div class="stat-icon icon-blue">
                            <i class="ph ph-coins"></i>
                        </div>
                        <div class="stat-info">
                            <h3>R$ <?= number_format($caixaAberto['saldo_atual'], 2, ',', '.') ?></h3>
                            <p>Saldo em Dinheiro na Gaveta</p>
                        </div>
                    </div>
                    
                    <div class="stat-card glass-panel" style="justify-content: center; flex-direction: column; gap: 0.5rem;">
                        <div style="display: flex; gap: 1rem; width: 100%;">
                            <button class="btn btn-primary" onclick="lancar('Suprimento')" style="flex:1;"><i class="ph ph-plus-circle"></i> Suprimento</button>
                            <button class="btn" onclick="lancar('Sangria')" style="flex:1; background-color: var(--warning-bg); color: var(--warning);"><i class="ph ph-minus-circle"></i> Sangria</button>
                        </div>
                        <button class="btn" onclick="fecharCaixa(<?= $caixaAberto['id_caixa'] ?>)" style="width: 100%; background-color: var(--danger-bg); color: var(--danger); margin-top: 0.5rem;"><i class="ph ph-lock-key"></i> Fechar Caixa</button>
                    </div>
                </div>

                <div class="glass-panel animate-fade-up delay-200" style="padding: 1.5rem;">
                    <h4 style="margin-bottom: 1.5rem;">Movimentações do Turno</h4>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Horário</th>
                                    <th>Tipo</th>
                                    <th>Descrição</th>
                                    <th>Valor</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($movimentacoes as $mov): ?>
                                    <tr>
                                        <td style="color: var(--text-secondary);"><?= date('H:i:s', strtotime($mov['data_movimentacao'])) ?></td>
                                        <td>
                                            <?php if($mov['tipo'] == 'Sangria'): ?>
                                                <span class="badge badge-warning">Sangria</span>
                                            <?php elseif($mov['tipo'] == 'Suprimento'): ?>
                                                <span class="badge badge-primary" style="background-color: var(--accent-light); color: var(--accent-color);">Suprimento</span>
                                            <?php elseif($mov['tipo'] == 'Abertura'): ?>
                                                <span class="badge badge-success">Abertura</span>
                                            <?php else: ?>
                                                <span class="badge badge-success">Venda</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($mov['descricao']) ?></td>
                                        <td style="font-weight: 600; color: <?= $mov['tipo'] == 'Sangria' ? 'var(--danger)' : 'var(--success)' ?>">
                                            <?= $mov['tipo'] == 'Sangria' ? '-' : '+' ?> R$ <?= number_format($mov['valor'], 2, ',', '.') ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>

            <script>
                async function abrirCaixa() {
                    const saldo = document.getElementById('saldoInicial').value;
                    const csrf = '<?= $_SESSION['csrf_token'] ?? '' ?>';
                    const res = await fetch('/caixa/abrir', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: `saldo_inicial=${saldo}&csrf_token=${csrf}`
                    });
                    const data = await res.json();
                    if (data.success) {
                        Swal.fire('Sucesso', 'Caixa aberto com sucesso!', 'success').then(() => window.location.reload());
                    } else {
                        Swal.fire('Erro', data.message, 'error');
                    }
                }

                function lancar(tipo) {
                    Swal.fire({
                        title: `Lançar ${tipo}`,
                        html: `
                            <input type="number" id="swal-input1" class="swal2-input" placeholder="Valor (R$)" step="0.01">
                            <input type="text" id="swal-input2" class="swal2-input" placeholder="Motivo / Descrição">
                        `,
                        focusConfirm: false,
                        showCancelButton: true,
                        preConfirm: () => {
                            return [
                                document.getElementById('swal-input1').value,
                                document.getElementById('swal-input2').value
                            ]
                        }
                    }).then(async (result) => {
                        if (result.isConfirmed) {
                            const val = result.value[0];
                            const desc = result.value[1];
                            if(!val || !desc) return Swal.fire('Erro', 'Preencha todos os campos', 'warning');
                            
                            const csrf = '<?= $_SESSION['csrf_token'] ?? '' ?>';
                            const res = await fetch('/caixa/lancar', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                                body: `id_caixa=<?= $caixaAberto['id_caixa'] ?? 0 ?>&tipo=${tipo}&valor=${val}&descricao=${encodeURIComponent(desc)}&csrf_token=${csrf}`
                            });
                            const data = await res.json();
                            if (data.success) window.location.reload();
                            else Swal.fire('Erro', data.message, 'error');
                        }
                    });
                }

                function fecharCaixa(id) {
                    Swal.fire({
                        title: 'Fechar Caixa',
                        text: "Deseja encerrar este turno e fechar o caixa?",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Sim, fechar!',
                        confirmButtonColor: '#ef4444'
                    }).then(async (result) => {
                        if (result.isConfirmed) {
                            const csrf = '<?= $_SESSION['csrf_token'] ?? '' ?>';
                            const res = await fetch('/caixa/fechar', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                                body: `id_caixa=${id}&csrf_token=${csrf}`
                            });
                            const data = await res.json();
                            if (data.success) {
                                Swal.fire({
                                    title: 'Caixa Fechado',
                                    text: 'O caixa foi encerrado com sucesso.',
                                    icon: 'success',
                                    showCancelButton: true,
                                    confirmButtonText: 'OK',
                                    cancelButtonText: '<i class="ph ph-printer"></i> Relatório Z'
                                }).then((result) => {
                                    if (result.dismiss === Swal.DismissReason.cancel) {
                                        window.open('/caixa/relatorio/' + data.id_caixa, '_blank', 'width=400,height=600');
                                    }
                                    window.location.reload();
                                });
                            } else {
                                Swal.fire('Erro', data.message, 'error');
                            }
                        }
                    });
                }
            </script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
